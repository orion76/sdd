<?php

namespace Drupal\comment_computed_fields\Plugin\Field\FieldType;

use Drupal;
use Drupal\comment\CommentInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TraversableTypedDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;


class CommentChildrenCountItem extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  protected $isCalculated = FALSE;

  /** @var Connection */
  protected $database;

  /** @var \Drupal\comment\CommentStorageInterface */
  protected $storage;

  /** @var AccountInterface */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinition $definition, $name, TypedDataInterface $parent,
                              AccountInterface $current_user,
                              Connection $database,
                              EntityTypeManagerInterface $entity_type_manager = NULL) {
    $definition->setComputed(TRUE);
    parent::__construct($definition, $name, $parent);
    $this->currentUser = $current_user;
    $this->database = $database;
    $this->storage = $entity_type_manager->getStorage('comment');
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance($definition, $name = NULL, TraversableTypedDataInterface $parent = NULL) {
    $container = Drupal::getContainer();
    return new static(
      $definition,
      $name,
      $parent,
      $container->get('current_user'),
      $container->get('database'),
      $container->get('entity_type.manager')

    );
  }


  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $field_name
   * @param $pid
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   */
  protected function threadQuery(EntityInterface $entity, $field_name, $pid, $order = FALSE) {
    $query = $this->database->select('comment_field_data', 'c');
    $query->addField('c', 'cid');
    $query
      ->condition('c.entity_id', $entity->id())
      ->condition('c.pid', $pid)
      ->condition('c.entity_type', $entity->getEntityTypeId())
      ->condition('c.field_name', $field_name)
      ->condition('c.default_langcode', 1)
      ->addTag('entity_access')
      ->addTag('comment_filter')
      ->addMetaData('base_table', 'comment')
      ->addMetaData('entity', $entity)
      ->addMetaData('field_name', $field_name);

    if (!$this->currentUser->hasPermission('administer comments')) {
      $query->condition('c.status', CommentInterface::PUBLISHED);

    }
    if ($order) {
      $query->addExpression('SUBSTRING(c.thread, 1, (LENGTH(c.thread) - 1))', 'torder');
      $query->orderBy('torder', 'ASC');

    }
    return $query;
  }

  public function getCount(EntityInterface $entity, $field_name, $pid) {
    $query = $this->threadQuery($entity, $field_name, $pid);
    $result = $query->execute();
    $ids = [];
    foreach ($result as $item) {
      $ids[$item->cid] = $item->cid;
    }
    return count($ids);
  }

  public function loadThread(EntityInterface $entity, $field_name, $pid) {
    $query = $this->threadQuery($entity, $field_name, $pid);
    $cids = $query->execute()->fetchCol();
    $comments = [];
    if ($cids) {
      $comments = $this->storage->loadMultiple($cids);
    }
    return $comments;
  }


  /**
   * @inheritDoc
   */
  protected function computeValue() {
    if (!$this->valueComputed) {
      $comment = $this->getEntity();
      $entity = $comment->getCommentedEntity();
      if(!$entity){
        return;
      }
      $this->list[0] = $this->createItem(0, $this->getCount($entity, 'field_discussion_comments', $comment->id()));

    }
  }
}
