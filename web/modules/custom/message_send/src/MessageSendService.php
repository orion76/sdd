<?php

namespace Drupal\message_send;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\message\Entity\Message;
use Drupal\message\MessageInterface;
use Drupal\message_send\Entity\MessageSendConfigInterface;
use Drupal\message_send\message\recipient\Recipient;
use Drupal\message_send\message\recipient\RecipientInterface;
use Drupal\message_send\message\recipient\RecipientsServiceInterface;
use Drupal\message_send\message\recipient_contact\RecipientContactFactoryInterface;
use Drupal\message_send\message\recipient_contact\RecipientContactFactoryManagerInterface;
use Drupal\message_sender\Plugin\MessageSenderManager;
use Drupal\message_subscribe\Exception\MessageSubscribeException;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use function array_chunk;
use function in_array;

/**
 * Class MessageSendService.
 */
class MessageSendService implements MessageSendServiceInterface {

  const EVENT_ADD = 'add';

  const EVENT_UPDATE = 'update';

  const EVENT_DELETE = 'delete';

  /**
   * The message subscribe queue.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * Logger channel.
   *
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * Debugging enabled.
   *
   * @var bool
   */
  protected $debug = FALSE;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var ConfigEntityStorageInterface */
  protected $configStorage;

  /**
   * The message notification service.
   *
   * @var MessageSenderManager
   */
  protected $senderManager;

  /** @var RecipientsServiceInterface */
  protected $recipientsService;

  /** @var RecipientContactFactoryManagerInterface */
  protected $contactsService;

  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              RecipientsServiceInterface $recipientsService,
                              RecipientContactFactoryManagerInterface $contactsService,
                              MessageSenderManager $senderManager,
                              QueueFactory $queue,
                              LoggerChannelFactoryInterface $loggerFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->recipientsService = $recipientsService;
    $this->contactsService = $contactsService;
    $this->senderManager = $senderManager;
    $this->configStorage = $entityTypeManager->getStorage('message_send_config');
    $this->queue = $queue->get('message_send');
    $this->logger = $loggerFactory->get('message_send');
  }

  public function loadConfigByEvent($event_name) {
    $configs = [];
    foreach ($this->configStorage->loadMultiple() as $config) {
      /** @var $config MessageSendConfigInterface */
      $events = $config->getSourceEvents();
      if (in_array($event_name, $events)) {
        $configs[$config->id()] = $config;
      }
    }
    return $configs;
  }

  public function getTemplateList() {
    $templates = [];

    foreach ($this->entityTypeManager->getStorage('message_template')->loadMultiple() as $template) {
      /** @var $template EntityInterface */
      $templates[$template->id()] = $template->label();
    }

    return $templates;
  }

  protected function ___loadConfigs($entity_type_id) {
    $configs = $this->configStorage->loadByProperties(['content_type' => $entity_type_id]);

    return $configs;
  }

  /**
   * @param $recipients
   * @param $message
   * @param $entity
   * @param $context
   *
   * @throws \Drupal\message_subscribe\Exception\MessageSubscribeException
   */
  protected function addToQueue($recipients, $message, $context) {
    if (empty($message->id())) {
      throw new MessageSubscribeException('Cannot add a non-saved message to the queue.');
    }
    $task = [
      'message' => $message,
      'context' => $context,
    ];

    foreach (array_chunk($recipients, 100) as $recipients_part) {
      $this->queue->createItem($task + ['recipients' => $recipients_part]);
    }
  }


  public function isEntityForMailing(MessageSendConfigInterface $config, EntityInterface $entity) {
    $for_mailing = TRUE;

    if ($entity->getEntityTypeId() !== $config->getSourceEntityType()) {
      return FALSE;
    }

    if ($entity->bundle() !== $config->getSourceBundle()) {
      return FALSE;
    }

    /*
    * @TODO Реализовать проверку-сравнение свойст и полей ($config->properties, $config->fields)
    */
    return $for_mailing;
  }

  public function sendMessageToRecipient(RecipientInterface $recipient, MessageInterface $message) {

    $from = \Drupal::config('system.site')->get('mail');
    /** @var $user UserInterface */
    $user = $this->entityTypeManager->getStorage('user')->load($recipient->getId());
    foreach ($this->contactsService->getFactories() as $contactFactory) {
      /** @var $contactFactory RecipientContactFactoryInterface */

      $contact = $contactFactory->create($user);
      if (empty($contact)) {
        continue;
      }

      /** @var $contact \Drupal\message_send\message\recipient_contact\RecipientContactInterface */
      /** @var $sender \Drupal\message_sender\Plugin\MessageSenderInterface */
      $sender = $this->senderManager->getSender($contact->getChannel());
      if ($message->isNew()) {
        $message->save();
      }
      if ($result = $sender->send($message->id(),
        'From SDD',
        $message,
        $from,
        $contact->getAddress(),
        $recipient->getLanguage())
      ) {
        $result_message = 'Successfully sent message via notifier @notifier to user @uid';
      }
      else {
        $result_message = 'Failed to send message via notifier @notifier to user @uid';
      }
      $this->debug($result_message, ['@sender' => $sender->getPluginId(), '@uid' => $recipient->getId()]);
      return $result;
    }
  }

  /**
   * @param \Drupal\message_send\Entity\MessageSendConfigInterface $config
   * @param $recipients
   * @param array $context
   */
  public function sendMessage(MessageSendConfigInterface $config, $recipients, $context = []) {

    $message = Message::create(['template' => $config->getTemplateId()]);

    if ($config->useQueue()) {
      try {
        $this->addToQueue($recipients, $message, $context);
      } catch (MessageSubscribeException $e) {
        $this->logger->error($e->getMessage());
      }
    }
    else {
      $users = $this->entityTypeManager->getStorage('user')->loadMultiple($recipients);
      foreach (array_values($recipients) as $uid) {
        $recipient = new Recipient($users[$uid]);
        $this->sendMessageToRecipient($recipient, $message);
      }
    }
  }

  /**
   * Wrapper to the logger channel to only log if debugging is enabled.
   *
   * @param string $message
   *   The message to log.
   * @param array $context
   *   The replacement patterns.
   */
  protected function debug($message, array $context = []) {
    if (!$this->debug) {
      return;
    }
    $this->logger->debug($message, $context);
  }
}
