<?php

namespace Drupal\message_send\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\message_send\MessageSendServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes messages that no longer have references within multivalued fields.
 *
 * When an entity is deleted, messages may be removed that reference that
 * entity. In the case of single-valued fields, it's easy to verify this.
 * However, for multi-valued fields, we have to first check if there are
 * references to any other entities in that field. Only when the last reference
 * is removed should we delete the message. This worker covers this more complex
 * scenario.
 *
 * @QueueWorker(
 *   id = "message_send",
 *   title = @Translation("Message send"),
 *   cron = {"time" = 10}
 * )
 */
class MessageSendWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The message storage handler.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $messageStorage;

  /** @var \Drupal\message_send\MessageSendServiceInterface */
  protected $messageSender;

  /**
   * Constructs a new MessageCheckAndDeleteWorker object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    MessageSendServiceInterface $messageSender) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messageStorage = $entity_type_manager->getStorage('message');
    $this->messageSender = $messageSender;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('message_send.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
/** @var $message \Drupal\message\MessageInterface   */
    $message = $data['message'];
    $context = $data['context'];

    // Reload message and entity.
    $message = $message->load($message->id());

    // Denotes this is being processed from a queue worker.
    $subscribe_options['queue'] = TRUE;
    $this->messageSender->sendMessage( $message,   $context);
  }

}
