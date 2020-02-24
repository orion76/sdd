<?php

namespace Drupal\message_send;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\message_send\Entity\MessageSendConfigInterface;
use Drupal\message_send\message\recipient\RecipientsServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntitySubscriber.
 */
class EntitySubscriber implements EventSubscriberInterface, EntitySubscriberInterface {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   *
   * @var \Drupal\message_send\MessageSendServiceInterface
   */
  protected $messageService;

  /** @var RecipientsServiceInterface */
  protected $recipientsService;

  /**
   * EntitySubscriber constructor.
   *
   * @param \Drupal\message_send\MessageSendServiceInterface $messageService
   * @param RecipientsServiceInterface $recipientsService
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   */
  public function __construct(MessageSendServiceInterface $messageService,
                              RecipientsServiceInterface $recipientsService,
                              ConfigFactory $config_factory) {
    $this->messageService = $messageService;
    $this->recipientsService = $recipientsService;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      // @todo replace hooks
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function onCreate(EntityInterface $entity) {
     $this->onCallback(MessageSendService::EVENT_ADD, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(EntityInterface $entity) {
        $this->onCallback(MessageSendService::EVENT_UPDATE, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(EntityInterface $entity) {
        $this->onCallback(MessageSendService::EVENT_DELETE, $entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function onCallback($operation, EntityInterface $entity) {
    $configs = $this->messageService->loadConfigByEvent($operation);
    if (empty($configs)) {
      return;
    }

    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    foreach ($configs as $config) {
      /** @var $config MessageSendConfigInterface */
      if (!$this->messageService->isEntityForMailing($config, $entity)) {
        continue;
      }

      $recipients = $this->recipientsService->getRecipients($config);
      if (empty($recipients)) {
        continue;
      }
      $context = [
        $entity_type => [$entity_id],
      ];

      $this->messageService->sendMessage($config, $recipients, $context);
    }
  }

}
