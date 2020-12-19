<?php

namespace Drupal\vote_result\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Vote result entities.
 */
class VoteResViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
