<?php


namespace Drupal\message_send\message\recipient;


use Drupal\message_send\Entity\MessageSendConfigInterface;

interface RecipientsServiceInterface {
  public function getRecipients(MessageSendConfigInterface $config);
}
