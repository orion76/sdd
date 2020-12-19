<?php

namespace Drupal\jsonapi_comment\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Annotation\JsonapiHypermediaLinkProvider;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentsCollectionLinkProvider.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_comment.collection",
 *   link_key = "comments",
 *   deriver = "Drupal\jsonapi_comment\Plugin\Derivative\CommentsCollectionLinkProviderDeriver"
 * )
 *
 * @internal
 */
final class CommentsCollectionLinkProvider extends LinkProviderBase implements ContainerFactoryPluginInterface {

  use CurrentUserAwareTrait;

  /**
   * The public comment field name.
   *
   * @var string
   */
  protected $commentFieldName;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition) {
    assert(!empty($configuration['comment_field_name']) && is_string($configuration['comment_field_name']), 'A `comment_field_name` key is required.');
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->commentFieldName = $configuration['comment_field_name'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $provider = new static($configuration, $plugin_id, $plugin_definition);
    $provider->setCurrentUser($container->get('current_user'));
    return $provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    assert($resource_object instanceof ResourceObject);
    $resource_type = $resource_object->getResourceType();
    $comment_route_name = "jsonapi.{$resource_type->getTypeName()}.jsonapi_comment.{$this->commentFieldName}";
    $comments_url = Url::fromRoute($comment_route_name, ['commented_entity' => $resource_object->getId()]);
    $link_relation_type = $this->getLinkRelationType();
    $link_attributes = [
      'commentFieldName' => $this->commentFieldName,
    ];
    if ($link_relation_type === 'comments') {
      $access = AccessResult::allowedIfHasPermission($this->currentUser, 'access comments');
    }
    else {
      $field = $resource_object->getField($this->commentFieldName);
      $access = AccessResult::allowedIfHasPermission($this->currentUser, 'post comments')
        ->andIf(AccessResult::allowedIf($field->status == CommentItemInterface::OPEN))
        ->addCacheableDependency($resource_object);
    }
    return AccessRestrictedLink::createLink($access, CacheableMetadata::createFromObject($resource_object), $comments_url, $link_relation_type, $link_attributes);
  }

}
