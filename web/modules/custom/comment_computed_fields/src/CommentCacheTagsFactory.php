<?php

namespace Drupal\comment_computed_fields;

use Drupal\Core\Entity\EntityInterface;

class CommentCacheTagsFactory {

  const TAG_PREFIX = 'comment_child';

  static function ChildAdded(EntityInterface $entity) {
    if(!$entity->id()){
      $n=0;
    }
    return static::TAG_PREFIX . ":created:{$entity->id()}";
  }
}
