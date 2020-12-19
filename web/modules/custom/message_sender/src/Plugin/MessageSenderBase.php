<?php

namespace Drupal\message_sender\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Message sender plugins.
 */
abstract class MessageSenderBase extends PluginBase implements MessageSenderInterface {


  abstract protected function deliver($mail_id, $params, $from, $to, $language);

  public function send($mail_id, $subject, $message, $from, $to, $language) {
    $params = [
      'context' => [
        'message' => $message,
        'subject' => $subject,
      ],
    ];
    return $this->deliver($mail_id, $params, $from, $to, $language);
  }
}
