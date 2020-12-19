<?php


namespace Drupal\vote_result\Plugin\ComputedField;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\computed_field\Plugin\ComputedFieldPluginBase;
use Drupal\votingapi\VoteInterface;
use Drupal\votingapi\VoteResultInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ComputedField(
 *   id = "vote_result",
 *   label = @Translation("Vote result")
 * )
 *
 * @package Drupal\computed_field\Plugin\ComputedField
 */
class VoteResultValue extends ComputedFieldPluginBase implements ContainerFactoryPluginInterface {

  const VALUE_FUNCTION = 'value_function';
  const VOTE_TYPE = 'vote_type';

  /** @var \Drupal\votingapi\VoteResultStorage */
  protected $voteStorage;

  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->voteStorage = $entityTypeManager->getStorage('vote_result');
  }


  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }


  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity) {
    /** @var $entity VoteInterface */
    $value_function = $definition->getSetting(self::VALUE_FUNCTION);
    $vote_type = $definition->getSetting(self::VOTE_TYPE);

    $properties = [
      'entity_type' => $entity->getVotedEntityType(),
      'entity_id' => $entity->getVotedEntityId(),
      'type' => $vote_type,
      'function' => $value_function,
    ];

    $result = $this->voteStorage->loadByProperties($properties);
    return array_map(function (VoteResultInterface $item) {
      return $item->getValue();
    }, $result);
  }

}
