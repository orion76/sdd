<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\computed_field\Plugin\BackReferenceFieldManager;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use function array_column;
use function array_filter;
use function array_keys;
use function array_rand;
use function count;
use function get_class;
use function reset;
use function t;

/**
 * Defines the 'entity_reference' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 *
 * @FieldType(
 *   id = "entity_reference_computed",
 *   label = @Translation("Entity reference computed"),
 *   description = @Translation("An entity field calculate an entity reference."),
 *   category = @Translation("Reference"),
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\computed_field\Plugin\Field\FieldType\BackReferenceItemList",
 * )
 */
class BackReferenceItem extends FieldItemBase implements OptionsProviderInterface, PreconfiguredFieldUiOptionsInterface {

  /** @var BackReferenceFieldManager */
  protected $pluginManager;

  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    $n = 0;
    //    $this->getDataDefinition()->set
    parent::__construct($definition, $name, $parent);
  }

  protected function getPluginManager() {
    if (!$this->pluginManager) {
      $this->pluginManager = \Drupal::service('plugin.manager.back_reference');
    }
    return $this->pluginManager;
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

  public function fieldSettingsFormProcess($element, FormStateInterface $form_state, array &$completeForm) {
    $n = 0;
  unset($completeForm['required']);
  return $element;
  }


  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = [
      '#type' => 'container',

      '#process' => [[$this, 'fieldSettingsFormProcess']],
    ];
    /** @var $field \Drupal\Core\Field\Plugin\DataType\FieldItem */
    $field = $form_state->getFormObject()->getEntity();
    $plugins = $this->getPluginManager()->getDefinitions();
    $options = ['' => $this->t('-- Select --')] + array_column($plugins, 'label', 'id');
    $plugin_id = $field->getSetting('plugin_id');

    $wrapper_id = Html::getUniqueId('backreference-plugin-settings');

    $elements['plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Reference field plugin'),
      '#options' => $options,
      '#default_value' => $plugin_id,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'pluginSettingsFormAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];

    $elements['plugin_settings'] = [
      '#id' => $wrapper_id,
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    if ($plugin_id) {
      /** @var  $plugin \Drupal\computed_field\Plugin\BackReferencePluginInterface */
      $plugin = $this->getPluginManager()->createInstance($plugin_id);
      $elements['plugin_settings'] += $plugin->getSettingsForm($field->getSetting('plugin_settings'), $form_state);
    }

    $n = 0;


    return $elements;
  }

  public static function pluginSettingsFormAjax(array $form, FormStateInterface $form_state) {
    return $form['plugin_settings'];
  }


  /**
   * @inheritDoc
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $settings = $field_definition->getSettings();
    $target_type = $settings['target_type'] ? $settings['target_type'] : 'node';
    $target_type_info = \Drupal::entityTypeManager()->getDefinition($target_type);

    $target_id_data_type = 'string';
    if ($target_type_info->entityClassImplements(FieldableEntityInterface::class)) {
      $id_definition = \Drupal::service('entity_field.manager')
        ->getBaseFieldDefinitions($target_type)[$target_type_info->getKey('id')];
      if ($id_definition->getType() === 'integer') {
        $target_id_data_type = 'integer';
      }
    }

    if ($target_id_data_type === 'integer') {
      $target_id_definition = DataReferenceTargetDefinition::create('integer')
        ->setLabel(new TranslatableMarkup('@label ID', ['@label' => $target_type_info->getLabel()]))
        ->setSetting('unsigned', TRUE);
    }
    else {
      $target_id_definition = DataReferenceTargetDefinition::create('string')
        ->setLabel(new TranslatableMarkup('@label ID', ['@label' => $target_type_info->getLabel()]));
    }
    $target_id_definition->setRequired(TRUE);
    $properties['target_id'] = $target_id_definition;
    return $properties;
  }

  /**
   * @inheritDoc
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getPossibleValues(AccountInterface $account = NULL) {
    return $this->getSettableValues($account);
  }

  /**
   * @inheritDoc
   */
  public function getPossibleOptions(AccountInterface $account = NULL) {
    return $this->getSettableOptions($account);
  }

  /**
   * @inheritDoc
   */
  public function getSettableValues(AccountInterface $account = NULL) {
    // Flatten options first, because "settable options" may contain group
    // arrays.
    $flatten_options = OptGroup::flattenOptions($this->getSettableOptions($account));
    return array_keys($flatten_options);
  }

  /**
   * @inheritDoc
   */
  public function getSettableOptions(AccountInterface $account = NULL) {
    $field_definition = $this->getFieldDefinition();
    if (!$options = \Drupal::service('plugin.manager.entity_reference_selection')
      ->getSelectionHandler($field_definition, $this->getEntity())
      ->getReferenceableEntities()) {
      return [];
    }

    // Rebuild the array by changing the bundle key into the bundle label.
    $target_type = $field_definition->getSetting('target_type');
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($target_type);

    $return = [];
    foreach ($options as $bundle => $entity_ids) {
      // The label does not need sanitizing since it is used as an optgroup
      // which is only supported by select elements and auto-escaped.
      $bundle_label = (string) $bundles[$bundle]['label'];
      $return[$bundle_label] = $entity_ids;
    }

    return count($return) == 1 ? reset($return) : $return;
  }

  /**
   * @inheritDoc
   */
  public static function getPreconfiguredOptions() {
    $options = [];

    // Add all the commonly referenced entity types as distinct pre-configured
    // options.
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $common_references = array_filter($entity_types, function (EntityTypeInterface $entity_type) {
      return $entity_type->isCommonReferenceTarget();
    });

    /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
    foreach ($common_references as $entity_type) {
      $options[$entity_type->id()] = [
        'label' => $entity_type->getLabel(),
        'field_storage_config' => [
          'settings' => [
            'target_type' => $entity_type->id(),
          ],
        ],
      ];
    }

    return $options;
  }


  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'target_id';
  }


  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // Avoid loading the entity by first checking the 'target_id'.
    if ($this->target_id !== NULL) {
      return FALSE;
    }
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    // An associative array keyed by the reference type, target type, and
    // bundle.
    static $recursion_tracker = [];

    $manager = \Drupal::service('plugin.manager.entity_reference_selection');

    // Instead of calling $manager->getSelectionHandler($field_definition)
    // replicate the behavior to be able to override the sorting settings.
    $options = [
      'target_type' => $field_definition->getFieldStorageDefinition()->getSetting('target_type'),
      'handler' => $field_definition->getSetting('handler'),
      'entity' => NULL,
    ] + $field_definition->getSetting('handler_settings') ?: [];

    $entity_type = \Drupal::entityTypeManager()->getDefinition($options['target_type']);
    $options['sort'] = [
      'field' => $entity_type->getKey('id'),
      'direction' => 'DESC',
    ];
    $selection_handler = $manager->getInstance($options);

    // Select a random number of references between the last 50 referenceable
    // entities created.
    if ($referenceable = $selection_handler->getReferenceableEntities(NULL, 'CONTAINS', 50)) {
      $group = array_rand($referenceable);
      $values['target_id'] = array_rand($referenceable[$group]);
      return $values;
    }

    // Attempt to create a sample entity, avoiding recursion.
    $entity_storage = \Drupal::entityTypeManager()->getStorage($options['target_type']);
    if ($entity_storage instanceof ContentEntityStorageInterface) {
      $bundle = static::getRandomBundle($entity_type, $options);

      // Track the generated entity by reference type, target type, and bundle.
      $key = $field_definition->getTargetEntityTypeId() . ':' . $options['target_type'] . ':' . $bundle;

      // If entity generation was attempted but did not finish, do not continue.
      if (isset($recursion_tracker[$key])) {
        return [];
      }

      // Mark this as an attempt at generation.
      $recursion_tracker[$key] = TRUE;

      // Mark the sample entity as being a preview.
      $values['entity'] = $entity_storage->createWithSampleValues($bundle, ['in_preview' => TRUE]);

      // Remove the indicator once the entity is successfully generated.
      unset($recursion_tracker[$key]);
      return $values;
    }
  }

  /**
   * Gets a bundle for a given entity type and selection options.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param array $selection_settings
   *   An array of selection settings.
   *
   * @return string|null
   *   Either the bundle string, or NULL if there is no bundle.
   */
  protected static function getRandomBundle(EntityTypeInterface $entity_type, array $selection_settings) {
    if ($bundle_key = $entity_type->getKey('bundle')) {
      if (!empty($selection_settings['target_bundles'])) {
        $bundle_ids = $selection_settings['target_bundles'];
      }
      else {
        $bundle_ids = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type->id());
      }
      return array_rand($bundle_ids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element['target_type'] = [
      '#type' => 'select',
      '#title' => t('Type of item to reference'),
      '#options' => \Drupal::service('entity_type.repository')->getEntityTypeLabels(TRUE),
      '#default_value' => $this->getSetting('target_type'),
      '#required' => TRUE,
      '#disabled' => $has_data,
      '#size' => 1,
    ];

    return $element;
  }


  public static function fieldSettingsFormValidate(array $form, FormStateInterface $form_state) {
    $form_state->setValue('computed', TRUE);
  }
}
