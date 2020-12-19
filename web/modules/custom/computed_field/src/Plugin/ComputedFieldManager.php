<?php

namespace Drupal\computed_field\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Computed field plugin plugin manager.
 */
class ComputedFieldManager extends DefaultPluginManager {


  /**
   * Constructs a new ComputedFieldManager object.
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
      'Plugin/ComputedField',
      $namespaces,
      $module_handler,
      'Drupal\computed_field\Plugin\ComputedFieldPluginInterface',
      'Drupal\computed_field\Annotation\ComputedField'
    );

    $this->alterInfo('computed_field_info');
    $this->setCacheBackend($cache_backend, 'computed_field_plugins');
  }

}
