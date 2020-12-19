<?php

namespace Drupal\dangular_comments\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default comment formatter.
 *
 * @FieldFormatter(
 *   id = "dangular_comments",
 *   module = "comment",
 *   label = @Translation("Dangular comments"),
 *   field_types = {
 *     "comment"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class DangularCommentsFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [] + parent::defaultSettings();
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user')
    );
  }


  /**
   * Constructs a new CommentDefaultFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   */
  public function __construct($plugin_id, $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings, $label,
                              $view_mode,
                              array $third_party_settings,
                              AccountInterface $currentUSer
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $currentUSer;
  }


  protected function getAuthLinks() {
    $links = [];
    $links['user.login'] = Link::createFromRoute('Login', 'user.login');
    $links['user.register'] = Link::createFromRoute('Register', 'user.register');
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];


    $field_name = $this->fieldDefinition->getName();
    $entity = $items->getEntity();
    $entity_type = "{$entity->getEntityTypeId()}--{$entity->bundle()}";
    $elements += [
      '#type' => 'dangular_comments',
      '#entity_type' => $entity_type,
      '#entity_id' => $entity->uuid(),
      '#field_name' => $field_name,
    ];

    if ($this->currentUser->isAnonymous()) {
      $elements['#need_auth'] = [
        '#type' => 'fieldset',
        'links'=>[
          '#theme'=>'item_list',
          '#items'=>$this->getAuthLinks()
        ]
      ];
    }


    return $elements;
  }


}
