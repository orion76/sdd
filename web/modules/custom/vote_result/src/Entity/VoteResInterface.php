<?php

namespace Drupal\vote_result\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Vote result entities.
 *
 * @ingroup vote_result
 */
interface VoteResInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Vote result name.
   *
   * @return string
   *   Name of the Vote result.
   */
  public function getName();

  /**
   * Sets the Vote result name.
   *
   * @param string $name
   *   The Vote result name.
   *
   * @return \Drupal\vote_result\Entity\VoteResInterface
   *   The called Vote result entity.
   */
  public function setName($name);

  /**
   * Gets the Vote result creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Vote result.
   */
  public function getCreatedTime();

  /**
   * Sets the Vote result creation timestamp.
   *
   * @param int $timestamp
   *   The Vote result creation timestamp.
   *
   * @return \Drupal\vote_result\Entity\VoteResInterface
   *   The called Vote result entity.
   */
  public function setCreatedTime($timestamp);

}
