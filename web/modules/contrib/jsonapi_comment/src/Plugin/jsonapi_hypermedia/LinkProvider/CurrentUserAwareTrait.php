<?php

namespace Drupal\jsonapi_comment\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\Core\Session\AccountInterface;

/**
 * Trait CurrentUserAwareTrait.
 *
 * @internal
 */
trait CurrentUserAwareTrait {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Sets the current account.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current account.
   */
  protected function setCurrentUser(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

}
