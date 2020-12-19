<?php

namespace Drupal\jsonapi_comment\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Annotation\JsonapiHypermediaLinkProvider;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentsCollectionLinkProvider.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_comment.add",
 *   deriver = "Drupal\jsonapi_comment\Plugin\Derivative\CommentReplyLinkProviderDeriver"
 * )
 *
 * @internal
 */
final class CommentReplyLinkProvider extends LinkProviderBase implements ContainerFactoryPluginInterface {

  use CurrentUserAwareTrait;

  /**
   * The JSON:API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $provider = new static($configuration, $plugin_id, $plugin_definition);
    $provider->setCurrentUser($container->get('current_user'));
    $provider->setResourceTypeRepository($container->get('jsonapi.resource_type.repository'));
    return $provider;
  }

  /**
   * Set the JSON:API resource type repository.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   */
  public function setResourceTypeRepository(ResourceTypeRepositoryInterface $resource_type_repository) {
    $this->resourceTypeRepository = $resource_type_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    assert($resource_object instanceof ResourceObject);
    $host_entity = $resource_object->getField('entity_id')->entity;
    assert($host_entity instanceof FieldableEntityInterface);
    $field_name = $resource_object->getField('field_name')->value;
    $comment_field_access = AccessResult::allowedIf($host_entity->hasField($field_name) && $host_entity->{$field_name}->status == CommentItemInterface::OPEN)
      ->addCacheableDependency($host_entity);
    $post_access = AccessResult::allowedIfHasPermission($this->currentUser, 'post comments');
    $public_field_name = $resource_object->getResourceType()->getPublicName($field_name);
    $host_resource_type = $this->resourceTypeRepository->get($host_entity->getEntityTypeId(), $host_entity->bundle());
    $reply_route_name = "jsonapi.{$host_resource_type->getTypeName()}.jsonapi_comment.{$public_field_name}.child_reply";
    $reply_route_parameters = [
      'commented_entity' => $host_entity->uuid(),
      'parent_comment' => $resource_object->getId(),
    ];
    $reply_url = Url::fromRoute($reply_route_name, $reply_route_parameters);
    $cacheability = CacheableMetadata::createFromObject($host_entity)->addCacheableDependency($resource_object);
    return AccessRestrictedLink::createLink($comment_field_access->andIf($post_access), $cacheability, $reply_url, 'comment');
  }

}
