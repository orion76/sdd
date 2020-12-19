<?php

namespace Drupal\message_sender\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Message sender plugins.
 */
interface MessageSenderInterface extends PluginInspectionInterface {


  function send($mail_id, $subject, $message, $from, $to, $language);

}
