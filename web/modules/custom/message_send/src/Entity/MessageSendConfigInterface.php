<?php

namespace Drupal\message_send\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Message send config entities.
 */
interface MessageSendConfigInterface extends ConfigEntityInterface {

  public function getUserViews();
  public function getUserViewsKey() ;
  public function useQueue();

  public function getTemplateId();
  public function getSourceEvents();
  public function getSourceEntityType();

  public function getSourceBundle();

  public function getSourceFields();

  public function getSourceProperties();
}
