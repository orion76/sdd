<?php


namespace Drupal\vote_result\Plugin\BackReference;


use Drupal\computed_field\Plugin\BackReferencePluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\votingapi\VoteStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_values;
use function t;

/**
 * @BackReference(
 *   id = "user_vote",
 *   label = @Translation("User vote")
 * )
 *
 * @package Drupal\computed_field\Plugin\BackReference
 */
class UserVote extends BackReferencePluginBase implements ContainerFactoryPluginInterface {

  /** @var AccountProxyInterface */
  protected $currentUser;

  /** @var VoteStorageInterface */
  protected $voteStorage;

  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $currentUser,
                              EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $currentUser;
    $this->voteStorage = $entityTypeManager->getStorage('vote');
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
      $container->get('entity_type.manager')
    );
  }


  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity) {
    $vote_type = $definition->getSetting('vote_type');
    $ids = $this->voteStorage->getUserVotes($this->currentUser->id(), $vote_type, $entity->getEntityTypeId(), $entity->id());
    return array_values($ids);
  }

  function getSettingsForm( $values, FormStateInterface $form_state) {
    $elements=[];
    // @TODO добавить настройки для плагина
    return $elements;
  }

  static function createDefinition($plugin_settings): BaseFieldDefinition {
    return BaseFieldDefinition::create('entity_reference_computed')
      ->setLabel(t('User vote'))
      ->setComputed(TRUE);
  }
}
