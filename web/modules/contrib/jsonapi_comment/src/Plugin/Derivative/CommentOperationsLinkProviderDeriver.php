<?php

namespace Drupal\jsonapi_comment\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentsCollectionLinkProvider.
 *
 * @internal
 */
final class CommentOperationsLinkProviderDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The JSON:API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CommentsReplyLinkProvider constructor.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   *   The JSON:API resource type repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ResourceTypeRepositoryInterface $resource_type_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->resourceTypeRepository = $resource_type_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('jsonapi.resource_type.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $resource_types = array_filter($this->resourceTypeRepository->all(), function (ResourceType $resource_type) {
      return $resource_type->getEntityTypeId() === 'comment'
        && !$resource_type->isInternal()
        && $resource_type->isLocatable();
    });
    $entity_type = $this->entityTypeManager->getDefinition('comment');
    $published_field_name = $entity_type->getKey('published');
    $derivative_definitions = array_reduce($resource_types, function ($definitions, ResourceType $resource_type) use ($base_plugin_definition, $published_field_name) {
      foreach (['update', 'remove', 'publish', 'unpublish'] as $operation) {
        $resource_type_name = $resource_type->getTypeName();
        $definitions["$resource_type_name.$operation"] = array_merge([
          'link_key' => in_array($operation, ['update', 'remove'], TRUE) ? 'self' : $operation,
          'link_relation_type' => $operation === 'remove' ? $operation : 'update',
          'link_context' => [
            'resource_object' => $resource_type_name,
          ],
          'default_configuration' => [
            'comment_operation' => $operation,
            'published_field_name' => $published_field_name,
          ],
        ], $base_plugin_definition);
      }
      return $definitions;
    }, []);
    return $derivative_definitions;
  }

}
