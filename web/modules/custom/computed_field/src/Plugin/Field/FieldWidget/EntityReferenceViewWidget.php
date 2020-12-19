<?php

namespace Drupal\computed_field\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\EntityOwnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function t;

/**
 * Plugin implementation of the 'entity_reference_view_widget' widget.
 *
 * @FieldWidget(
 *   id = "entity_reference_view_widget",
 *   module = "computed_field",
 *   label = @Translation("Entity reference view widget"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceViewWidget extends WidgetBase {

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var EntityDisplayRepositoryInterface */
  protected $entityDisplayRepository;

  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              array $third_party_settings,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityDisplayRepositoryInterface $entity_display_repository
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'size' => 60,
        'placeholder' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type')),
      '#title' => t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $referenced_entities = $items->referencedEntities();
    /** @var $referenced_entity \Drupal\Core\Entity\ContentEntityInterface */
    $referenced_entity = isset($referenced_entities[$delta]) ? $referenced_entities[$delta] : NULL;

    if ($referenced_entity) {
      $element += [
        '#type' => 'value',
        '#value' => $referenced_entity->id(),
      ];

      $elements['target_id'] = $element;

      $elements['view'] = $this->entityTypeManager
        ->getViewBuilder($referenced_entity->getEntityTypeId())
        ->view($referenced_entity, $this->getSetting('view_mode'));
    }


    return $elements;
  }

}
