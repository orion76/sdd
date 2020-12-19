<?php

namespace Drupal\group\Entity\Access;

use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\group\Entity\GroupContentType;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Group entity.
 *
 * @see \Drupal\group\Entity\Group.
 */
class GroupContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\group\Entity\GroupContentInterface $entity */
    return $entity->getContentPlugin()->checkAccess($entity, $operation, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $group_content_type = GroupContentType::load($entity_bundle);
    if (!isset($context['group'])) {
      /** @var $context_service ContextProviderInterface */
      $context_service = \Drupal::service('group.group_route_context');
      $group_context = $context_service->getRuntimeContexts(['group']);
      /** @var $group \Drupal\Core\Plugin\Context\Context   */
      $group=$group_context['group'];
      $context['group'] = $group->getContextValue();
    }

    return $group_content_type->getContentPlugin()->createAccess($context['group'], $account);
  }

}
