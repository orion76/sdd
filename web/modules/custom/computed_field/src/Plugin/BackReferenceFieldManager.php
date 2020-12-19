<?php

namespace Drupal\computed_field\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Computed field plugin plugin manager.
 */
class BackReferenceFieldManager extends DefaultPluginManager {


  /**
   * Constructs a new BackReferenceFieldManager object.
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
      'Plugin/BackReference',
      $namespaces,
      $module_handler,
      'Drupal\computed_field\Plugin\BackReferencePluginInterface',
      'Drupal\computed_field\Annotation\BackReference'
    );

    $this->alterInfo('back_reference_info');
    $this->setCacheBackend($cache_backend, 'back_reference_plugins');
  }

  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    $n=0;
  }

}
