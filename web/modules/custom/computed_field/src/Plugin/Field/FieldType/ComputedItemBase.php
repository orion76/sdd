<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use function t;

/**
 * Base class for numeric configurable field types.
 */
class ComputedItemBase extends FieldItemBase implements ComputedFieldItemInterface{

  public function getValue() {
    return parent::getValue(); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return  parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->value);
  }

  /**
   * @inheritDoc
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $value_type = $field_definition->getSetting('value_type');
    $properties['value'] = DataDefinition::create($value_type)->setLabel(t('Value'));
    return $properties;
  }

  /**
   * @inheritDoc
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return ['fields'=>[]];
  }

  function getPlugin() {
    // TODO: Implement getPlugin() method.
  }
}