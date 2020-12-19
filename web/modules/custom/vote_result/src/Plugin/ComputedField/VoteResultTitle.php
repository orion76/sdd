<?php


namespace Drupal\vote_result\Plugin\ComputedField;


use Drupal\computed_field\Plugin\ComputedFieldPluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * @ComputedField(
 *   id = "vote_result_title",
 *   label = @Translation("Vote result title")
 * )
 *
 * @package Drupal\computed_field\Plugin\ComputedField
 */
class VoteResultTitle extends ComputedFieldPluginBase {


  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity) {
    return $entity->label();
  }

}
