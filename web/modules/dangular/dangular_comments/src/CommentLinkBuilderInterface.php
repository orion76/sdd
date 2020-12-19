<?php


namespace Drupal\dangular_comments;


use Drupal\Core\Entity\EntityInterface;

interface CommentLinkBuilderInterface {
  public function addSocialAuth(EntityInterface $entity, &$links);
}
