<?php

namespace Drupal\message_sender\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Message sender plugin manager.
 */
class MessageSenderManager extends DefaultPluginManager {


  /**
   * Constructs a new MessageSenderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/MessageSender',
      $namespaces,
      $module_handler,
      'Drupal\message_sender\Plugin\MessageSenderInterface',
      'Drupal\message_sender\Annotation\MessageSender'
    );

    $this->alterInfo('message_sender_info');
    $this->setCacheBackend($cache_backend, 'message_sender_plugins');
  }

  /**
   * @param $channel
   *
   * @return object
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSender($channel) {
    $definition = $this->getDefinition($channel);
    return $this->createInstance($channel, $definition);
  }

}
