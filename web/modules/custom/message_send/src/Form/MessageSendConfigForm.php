<?php

namespace Drupal\message_send\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\message_send\Entity\MessageSendConfigInterface;
use Drupal\message_send\MessageSendService;
use Drupal\message_send\MessageSendServiceInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_filter;
use function in_array;

/**
 * Class MessageSendConfigForm.
 */
class MessageSendConfigForm extends EntityForm {

  const SOURCE_WRAPPER = 'message-send-source-wrapper';

  /** @var MessageSendServiceInterface */
  protected $service;

  /** @var EntityFieldManagerInterface */
  protected $fieldManager;

  public function __construct(MessageSendServiceInterface $service, EntityFieldManagerInterface $fieldManager) {

    $this->service = $service;
    $this->fieldManager = $fieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('message_send.service'),
      $container->get('entity_field.manager')
    );
  }

  protected function formTemplate(MessageSendConfigInterface $config) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Template'),
      '#tree' => TRUE,
    ];
    $element['id'] = [
      '#type' => 'select',
      '#title' => 'Template',
      '#default_value' => $config->getTemplateId(),
      '#options' => ['' => $this->t('-- Select --')] + $this->service->getTemplateList(),
    ];
    return $element;
  }

  /**
   * @param \Drupal\message_send\Entity\MessageSendConfigInterface $config
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function formRecipient(MessageSendConfigInterface $config) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recipient'),
      '#tree' => TRUE,
    ];

    $element['views'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $views = $config->getUserViews();

    $element['views']['id'] = [
      '#type' => 'value',
      '#value' => $views['id'],
    ];

    $element['views']['display'] = [
      '#type' => 'value',
      '#value' => $views['display'],
    ];


    $element['views_select'] = [
      '#type' => 'select',
      '#title' => 'Views for users',
      '#default_value' => $config->getUserViewsKey(),
      '#options' => $this->getViewsListForUsers(),
    ];

    return $element;
  }

  protected function formSource(MessageSendConfigInterface $config) {
    $element = [
      '#type' => 'fieldset',
      '#id' => self::SOURCE_WRAPPER,
      '#title' => $this->t('Source'),
      '#tree' => TRUE,
    ];

    $element['events'] = [
      '#type' => 'select',
      '#title' => $this->t('Events'),
      '#default_value' => $config->getSourceEvents(),
      '#options' => $this->getEntityEvents(),
      '#multiple' => TRUE,
    ];

    $entity_type = $config->getSourceEntityType();
    $bundle = $config->getSourceBundle();

    $element['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#default_value' => $entity_type,
      '#options' => $this->getEntityTypes(),
      '#validate' => ['::validatePropertySelect'],
      '#ajax' => [
        'callback' => '::ajaxSource',
      ],
    ];

    if (!empty($entity_type)) {
      $element['bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Bundle'),
        '#default_value' => $bundle,
        '#options' => $this->getBundles($entity_type),
        '#ajax' => [
          'callback' => '::ajaxSource',
          'wrapper' => self::SOURCE_WRAPPER,
        ],
      ];
      $properties_all = ['_add' => $this->t('-- Select --')];

      $properties = $config->getSourceProperties();
      $properties_all += $this->getEntityTypeProperties($entity_type, $properties);

      $element['properties'] = $this->formFilters($properties, $properties_all);
      $element['properties']['#caption'] = $this->t('Properties');

    }

    if (!empty($bundle)) {
      $fields_all = ['_add' => $this->t('-- Select --')];

      $fields = $config->getSourceFields();
      $fields_all += $this->getEntityTypeFields($entity_type, $bundle, $fields);

      $element['fields'] = $this->formFilters($fields, $fields_all);
      $element['fields']['#caption'] = $this->t('Fields');
    }
    return $element;
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var $config MessageSendConfigInterface */
    $config = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $config->label(),
      '#description' => $this->t("Label for the Message send config."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $config->id(),
      '#machine_name' => [
        'exists' => '\Drupal\message_send\Entity\MessageSendConfig::load',
      ],
      '#disabled' => !$config->isNew(),
    ];

    $form['template'] = $this->formTemplate($config);
    $form['recipient'] = $this->formRecipient($config);
    $form['source'] = $this->formSource($config);

    return $form;
  }

  public function validatePropertySelect($form, FormStateInterface $form_state) {
    if ($fields = $form_state->getValue('fields')) {
      $form_state->setValue('fields', $this->filterByValue($fields, 'field_name', '_add'));
    }

    if ($properties = $form_state->getValue('properties')) {
      $form_state->setValue('properties', $this->filterByValue($properties, 'field_name', '_add'));
    }
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * @param $entity_type_id
   * @param $bundle
   * @param $exists_values
   *
   * @return array
   */
  protected function getEntityTypeFields($entity_type_id, $bundle, $exists_values) {
    $fields = [];
    $all = $this->getEntityTypeProperties($entity_type_id, $exists_values);
    foreach ($this->fieldManager->getFieldDefinitions($entity_type_id, $bundle) as $key => $value) {
      if (isset($all[$key])) {
        continue;
      }
      $fields[$key] = $value->getLabel();
    }
    return $fields;
  }

  /**
   * @param $entity_type_id
   * @param $exists_values
   *
   * @return array
   */
  protected function getEntityTypeProperties($entity_type_id, $exists_values) {
    $properties = [];
    foreach ($this->fieldManager->getBaseFieldDefinitions($entity_type_id) as $key => $value) {
      $properties[$key] = $value->getLabel();
    }
    return $properties;
  }

  /**
   * @return array
   */
  protected function getEntityEvents() {
    $types = [
      '' => $this->t('--Pleas select --'),
      MessageSendService::EVENT_ADD => $this->t('Add new'),
      MessageSendService::EVENT_UPDATE => $this->t('Update'),
      MessageSendService::EVENT_DELETE => $this->t('Delete'),
    ];
    return $types;
  }


  /**
   * @return array
   */
  protected function getEntityTypes() {
    $types = ['' => $this->t('--Pleas select --')];
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    foreach ($definitions as $entity_type_id => $entity_type) {
      if ($entity_type->getGroup() === 'content') {
        $types[$entity_type_id] = $entity_type->getLabel();
      }

    }
    return $types;
  }

  /**
   * @param $entity_type_id
   *
   * @return array
   */
  protected function getBundles($entity_type_id) {
    $types = ['' => $this->t('--Pleas select --')];
    /** @var $entity_type \Drupal\Core\Entity\ContentEntityTypeInterface */
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type_id);
    foreach ($bundles as $bundle_id => $data) {
      $types[$bundle_id] = $data['label'];
    }
    return $types;
  }

  public function ajaxSource($form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('[data-drupal-selector="' . self::SOURCE_WRAPPER . '"]', $form['source']));
    return $response;
  }

  protected function formFilters($values, $options) {

    $header = [
      'field_name' => '',
      'label' => 'Label',
      'value' => 'Value',
      'operator' => 'Operator',
    ];
    $rows = [];
    foreach ($values as $value) {
      $field_name = $value['field_name'];
      $value['label'] = $options[$field_name];
      $rows[$field_name] = $this->formFieldFilter($value);
    }

    $new_options = [];
    foreach ($options as $key => $label) {
      if ($key !== '_add' && isset($values[$key])) {
        continue;
      }
      $new_options[$key] = $label;
    }

    $rows['_add'] = $this->formFieldFilterNew($new_options);
    return [
        '#type' => 'table',
        '#header' => $header,
      ] + $rows;
  }

  protected function formFieldFilterNew($options) {
    $element = [];

    $element['field_name'] = [
      '#type' => 'select',
      '#default_value' => '',
      '#options' => $options,
      '#ajax' => [
        'callback' => '::ajaxSource',
      ],
    ];
    $element['label']['#markup'] = $this->t('Add');
    $element['value'] = [
      '#type' => 'textfield',
      '#default_value' => '',
      '#disabled' => TRUE,
    ];

    $element['operator'] = [
      '#type' => 'select',
      '#default_value' => '',
      '#options' => $this->getOperators(),
      '#disabled' => TRUE,
    ];
    return $element;
  }

  protected function formFieldFilter($values) {
    $field_name = $values['field_name'];
    $value = $values['value'];
    $operator = $values['operator'];
    $element = [];

    $element['field_name'] = [
      '#type' => 'value',
      '#value' => $field_name,
    ];
    $element['label']['#markup'] = $values['label'];
    $element['value'] = [
      '#type' => 'textfield',
      '#default_value' => $value,
    ];

    $element['operator'] = [
      '#type' => 'select',
      '#default_value' => $operator,
      '#options' => $this->getOperators(),
    ];
    return $element;
  }

  protected function getOperators() {
    return [
      '' => $this->t('-- Pleas select --'),
      '=' => $this->t('Equal (=)'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $message_send_config = $this->entity;
    $status = $message_send_config->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Message send config.', [
          '%label' => $message_send_config->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Message send config.', [
          '%label' => $message_send_config->label(),
        ]));
    }
    $form_state->setRedirectUrl($message_send_config->toUrl('collection'));
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($views_select = $form_state->getValue(['recipient', 'views_select'])) {
      [$views['id'], $views['display']] = explode(':', $views_select);
      $form_state->setValue(['recipient', 'views'], $views);
      $form_state->unsetValue(['recipient', 'views_select']);
    }

    $fields = $form_state->getValue('fields');
    $form_state->setValue('fields', $this->filterEmpty($fields, 'value'));

    $properties = $form_state->getValue('properties');
    $form_state->setValue('properties', $this->filterEmpty($properties, 'value'));

    parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  protected function filterByValue($values, $field, $value) {
    return array_filter($values, function ($item) use ($field, $value) {
      return !empty($item[$field]) && $item[$field] !== $value;
    });
  }

  protected function filterEmpty($values, $field) {
    return array_filter($values, function ($item) use ($field) {
      return !empty($item[$field]);
    });
  }


  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getViewsListForUsers() {
    $displays = Views::getApplicableViews('entity_ids_display');
    // Filter views that list the entity type we want, and group the separate
    // displays by view.
    $entity_type = $this->entityTypeManager->getDefinition('user');
    $view_storage = $this->entityTypeManager->getStorage('view');

    $options = ['' => 'Select'];
    foreach ($displays as $data) {
      [$view_id, $display_id] = $data;
      $view = $view_storage->load($view_id);
      if (in_array($view->get('base_table'), [$entity_type->getBaseTable(), $entity_type->getDataTable()])) {
        $display = $view->get('display');
        $options[$view_id . ':' . $display_id] = $view_id . ' - ' . $display[$display_id]['display_title'];
      }
    }

    return $options;
  }

}
