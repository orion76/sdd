<?php

namespace Drupal\dangular_comments;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Defines a class for building markup for comment links on a commented entity.
 *
 * Comment links include 'log in to post new comment', 'add new comment' etc.
 */
class CommentLinkBuilder implements CommentLinkBuilderInterface {

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;


  /**
   * Whether the \Drupal\user\RoleInterface::AUTHENTICATED_ID can post comments.
   *
   * @var ImmutableConfig
   */
  protected $socialAuthConfig;

  /**
   * Constructs a new CommentLinkBuilder object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory) {
    $this->moduleHandler = $module_handler;
    $this->socialAuthConfig = $config_factory->get('social_auth.settings');
  }

  protected function isSocialAuthExist() {
    return $this->moduleHandler->moduleExists('social_auth');
  }


  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $links
   */
  public function addSocialAuth(EntityInterface $entity, &$links) {

    if ($this->isSocialAuthExist()) {
      $links['social-auth'] = [
        '#theme' => 'login_with',
        '#social_networks' => $this->socialAuthConfig->get('auth'),
      ];
    }
  }

}
