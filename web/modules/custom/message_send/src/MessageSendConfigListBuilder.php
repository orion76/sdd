<?php

namespace Drupal\message_send;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Message send config entities.
 */
class MessageSendConfigListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Message send config');
    $header['id'] = $this->t('Machine name');
    $header['entity_type'] = $this->t('Entity type');
    $header['bundle'] = $this->t('Bundle');
    $header['views_key'] = $this->t('Users');
    $header['template'] = $this->t('Template');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var $entity \Drupal\message_send\Entity\MessageSendConfigInterface */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    $row['entity_type'] = $entity->getSourceEntityType();
    $row['bundle'] = $entity->getSourceBundle();
    $row['views_key'] = $entity->getUserViewsKey();
    $row['template'] = $entity->getTemplateId();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
