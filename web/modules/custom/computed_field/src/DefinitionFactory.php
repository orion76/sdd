<?php

namespace Drupal\computed_field;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a field definition class for bundle fields.
 *
 * Core currently doesn't provide one, the hook_entity_bundle_field_info()
 * example uses BaseFieldDefinition, which is wrong. Tracked in #2346347.
 *
 * Note that this class implements both FieldStorageDefinitionInterface and
 * FieldDefinitionInterface. This is a simplification for DX reasons,
 * allowing code to return just the bundle definitions instead of having to
 * return both storage definitions and bundle definitions.
 */
class DefinitionFactory extends BaseFieldDefinition implements DefinitionFactoryInterface {

  const PLUGIN_KEY = 'computed_plugin';

  const ATTRIBUTE_TYPE = 'field_computed';

  const REFERENCE_TYPE = 'entity_reference_computed';


  public static function createBackReference($plugin_id, $plugin_settings = []) {
    $instance = static::create(static::REFERENCE_TYPE);
    $settings = ['id' => $plugin_id, 'settings' => $plugin_settings];

    $instance
      ->setSetting(static::PLUGIN_KEY, $settings);

    return $instance;
  }

  public static function createDefinition($field_type, $plugin_id, $plugin_settings = []) {
    $instance = static::create($field_type);
    $settings = ['id' => $plugin_id, 'settings' => $plugin_settings];

    $instance
      ->setComputed(TRUE)
      ->setSetting(static::PLUGIN_KEY, $settings);

    return $instance;
  }


  public static function createFieldAttribute($plugin_id, $plugin_settings = []) {

    $instance = static::create(static::ATTRIBUTE_TYPE);
    $settings = ['id' => $plugin_id, 'settings' => $plugin_settings];

    $instance
      ->setComputed(TRUE)
      ->setSetting(static::PLUGIN_KEY, $settings);

    return $instance;
  }

  function getComputedSettings() {
    return $this->getSetting(self::PLUGIN_KEY);
  }
}
