<?php

namespace Drupal\jsonapi_comment\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\comment\CommentInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentPublishLinkProvide.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_comment.operations",
 *   deriver = "Drupal\jsonapi_comment\Plugin\Derivative\CommentOperationsLinkProviderDeriver",
 * )
 *
 * @internal
 */
final class CommentOperationsLinkProvider extends LinkProviderBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The published status internal field name.
   *
   * @var string
   */
  protected $publishedFieldName;

  /**
   * The link operation.
   *
   * @var string
   */
  protected $commentOperation;

  /**
   * {@inheritdoc}
   */
  protected function __construct(array $configuration, string $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    assert(!empty($configuration['published_field_name']) && is_string($configuration['published_field_name']), "The published_field_name configuration value is required.");
    assert(!empty($configuration['comment_operation']) && is_string($configuration['comment_operation']), "The comment_operation configuration value is required.");
    $this->publishedFieldName = $configuration['published_field_name'];
    $this->commentOperation = $configuration['comment_operation'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $provider = new static($configuration, $plugin_id, $plugin_definition);
    $provider->setEntityRepository($container->get('entity.repository'));
    return $provider;
  }

  /**
   * Sets the entity repository.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  protected function setEntityRepository(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    assert($resource_object instanceof ResourceObject);
    $resource_type = $resource_object->getResourceType();
    $comment_entity = $this->entityRepository->loadEntityByUuid('comment', $resource_object->getId());
    assert($comment_entity instanceof CommentInterface);
    $operation = $this->commentOperation;
    if (in_array($operation, ['update', 'remove'], TRUE)) {
      $entity_operation = $operation === 'update' ? 'update' : 'delete';
      $access_result = $comment_entity->access($entity_operation, NULL, TRUE);
      return AccessRestrictedLink::createLink($access_result, new CacheableMetadata(), $resource_object->toUrl(), $operation);
    }
    else {
      $published = $comment_entity->isPublished();
      $access_result = AccessResult::allowedIf($operation === 'publish' XOR $published)
        ->andIf($comment_entity->access('update', NULL, TRUE))
        ->andIf(AccessResult::allowedIf($published)->orIf($comment_entity->access('approve', NULL, TRUE)))
        ->andIf($comment_entity->{$this->publishedFieldName}->access('edit', NULL, TRUE))
        ->addCacheableDependency($comment_entity);
      $link_attributes = [
        'data' => [
          'type' => $resource_object->getTypeName(),
          'id' => $resource_object->getId(),
          'attributes' => [
            $resource_type->getPublicName($this->publishedFieldName) => (int) !$published,
          ]
        ],
      ];
      return AccessRestrictedLink::createLink($access_result, CacheableMetadata::createFromObject($resource_object), $resource_object->toUrl(), 'update', $link_attributes);
    }
  }

}
