<?php

namespace Drupal\message_send;

use Drupal\Core\Entity\EntityInterface;
use Drupal\message_send\Entity\MessageSendConfigInterface;

/**
 * Interface MessageSendServiceInterface.
 */
interface MessageSendServiceInterface {

  public function getTemplateList();

  public function sendMessage(MessageSendConfigInterface $config, $recipients, $context = []);

  public function isEntityForMailing(MessageSendConfigInterface $config, EntityInterface $entity);

  public function loadConfigByEvent($event_name);
}
