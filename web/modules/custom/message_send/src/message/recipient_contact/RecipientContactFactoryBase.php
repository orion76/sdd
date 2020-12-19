<?php

namespace Drupal\message_send\message\recipient_contact;

use Drupal\Component\Plugin\PluginBase;
use Drupal\user\UserInterface;

/**
 * Base class for Message sender plugins.
 */
abstract class RecipientContactFactoryBase extends PluginBase {


  abstract function create(UserInterface $user): RecipientContactInterface;

  protected function getChannel() {
    return $this->pluginDefinition['channel'];
  }
}
