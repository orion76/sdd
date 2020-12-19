<?php


namespace Drupal\backreference_flag\Plugin\BackReference;


use Drupal\computed_field\Plugin\BackReferencePluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_column;
use function t;

/**
 * @BackReference(
 *   id = "flag",
 *   label = @Translation("Flag")
 * )
 *
 * @package Drupal\computed_field\Plugin\BackReference
 */
class Flag extends BackReferencePluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /** @var AccountProxyInterface */
  protected $currentUser;

  /** @var FlagServiceInterface */
  protected $flagService;

  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $currentUser,
                              FlagServiceInterface $flagService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $currentUser;
    $this->flagService = $flagService;
  }


  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('flag')
    );
  }


  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity) {
    $flags = $this->flagService->getAllEntityFlaggings( $entity, $this->currentUser);
    return array_column($flags, 'id');
  }

  function getSettingsForm($values, FormStateInterface $form_state) {
    $elements = [];
    $options = [
        '' => $this->t('-- Select --'),
      ] + array_column($this->flagService->getAllFlags(), 'label', 'id');
    $elements['flag_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Flag type'),
      '#options' => $options,
      '#default_value' => $values['flag_type'],
      //      '#required' => TRUE,
    ];
    return $elements;
  }


  static function createDefinition($plugin_settings = NULL): BaseFieldDefinition {
    return BaseFieldDefinition::create('entity_reference_computed')
      ->setLabel(t('Flags'))
      ->setCardinality(-1)
      ->setDescription('All entity flags')
      ->setComputed(TRUE);
  }
}
