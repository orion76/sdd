<?php

namespace Drupal\computed_field\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Base class for Computed field plugin plugins.
 */
abstract class BackReferencePluginBase extends PluginBase implements BackReferencePluginInterface {


  // Add common methods and abstract methods for your plugin type here.
  abstract static function createDefinition($plugin_settings): BaseFieldDefinition;

}
