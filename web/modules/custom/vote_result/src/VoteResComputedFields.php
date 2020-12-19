<?php

namespace Drupal\vote_result;

use Drupal\computed_field\DefinitionFactory;
use Drupal\vote_result\Plugin\ComputedField\FlagRead;
use Drupal\vote_result\Plugin\ComputedField\VoteResultValue;
use function t;

class VoteResComputedFields {


  /**
   * @param string $vote_type
   *  values :
   *  - updown
   *  - etc..
   * @param string $result_function
   *  values (plugin @VoteResultFunction):
   *  - vote_average
   *  - vote_count
   *  - rate_count_up
   *  - vote_sum
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition|\Drupal\Core\TypedData\DataDefinition|\Drupal\Core\TypedData\ListDataDefinition
   */
  static function createVoteResult($vote_type, $result_function) {
    $plugin_settings = [
      VoteResultValue::VOTE_TYPE => $vote_type,
      VoteResultValue::VALUE_FUNCTION => $result_function,
    ];
    return DefinitionFactory::createFieldAttribute('vote_result', $plugin_settings)
      ->setLabel(t('Vote result'))
      ->setDescription(t('Vote result'))
      ->setCardinality(1);
  }

  static function UserVote($vote_type) {
    $plugin_settings = [
      VoteResultValue::VOTE_TYPE => $vote_type
    ];
    return DefinitionFactory::createBackReference('user_vote', $plugin_settings)
      ->setLabel(t('Vote result'))
      ->setDescription(t('Vote result'))
      ->setCardinality(1);
  }

}
