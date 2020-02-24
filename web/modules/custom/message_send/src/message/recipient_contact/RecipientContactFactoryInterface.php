<?php

namespace Drupal\message_send\message\recipient_contact;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\user\UserInterface;

/**
 * Defines an interface for Message sender plugins.
 */
interface RecipientContactFactoryInterface extends PluginInspectionInterface {

  /**
   * @param \Drupal\user\UserInterface $user
   *
   * @return RecipientContactInterface
   */
  function create(UserInterface $user): RecipientContactInterface;

}
