<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use function array_column;
use function get_class;

/**
 * Defines the 'field_computed' entity field type.
 *
 * @FieldType(
 *   id = "field_computed",
 *   label = @Translation("Field computed"),
 *   description = @Translation("An entity field calculate an entity reference."),
 *   category = @Translation("Field"),
 *   default_formatter = "string",
 *   list_class = "\Drupal\computed_field\Plugin\Field\FieldType\ComputedFieldItemList",
 *   computed = "TRUE"
 * )
 */
class ComputedFieldItem extends ComputedItemBase implements FieldItemInterface {

  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    $n = 0;
    //    $this->getDataDefinition()->set
    parent::__construct($definition, $name, $parent);
  }
  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'plugin_id' => NULL,
        'plugin_settings' => [],
      ] + parent::defaultFieldSettings();
  }

  public function getValue() {
    return parent::getValue(); // TODO: Change the autogenerated stub
  }
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $form['#element_validate'][] = [get_class($this), 'fieldSettingsFormValidate'];
    /** @var $field \Drupal\Core\Field\FieldConfigInterface   */
    $field = $form_state->getFormObject()->getEntity();
    $definition=$field->getItemDefinition();
//    $definition->setComp
    $plugins = $this->getPluginManager()->getDefinitions();
    $options = ['' => $this->t('-- Select --')] + array_column($plugins, 'label', 'id');
    $plugin_id = $field->getSetting('plugin_id');

    unset($form['handler']);

    $wrapper_id = Html::getUniqueId('backreference-plugin-settings');

    $form['plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Computed field plugin'),
      '#options' => $options,
      '#default_value' => $plugin_id,
      //      '#required' => TRUE,
    ];
    return $form;
  }

  protected function getPluginManager() {
    if (!$this->pluginManager) {
      $this->pluginManager = \Drupal::service('plugin.manager.computed_field');
    }
    return $this->pluginManager;
  }
  public static function fieldSettingsFormValidate(array $form, FormStateInterface $form_state) {
    $form_state->setValue('computed',TRUE);
  }
}
