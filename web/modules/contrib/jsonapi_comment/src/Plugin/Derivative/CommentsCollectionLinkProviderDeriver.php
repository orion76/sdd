<?php

namespace Drupal\jsonapi_comment\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentsCollectionLinkProvider.
 *
 * @internal
 */
final class CommentsCollectionLinkProviderDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The JSON:API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * CommentsReplyLinkProvider constructor.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   *   The JSON:API resource type repository.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(ResourceTypeRepositoryInterface $resource_type_repository, EntityFieldManagerInterface $entity_field_manager) {
    $this->resourceTypeRepository = $resource_type_repository;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('jsonapi.resource_type.repository'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $resource_types = array_filter($this->resourceTypeRepository->all(), function (ResourceType $resource_type) {
      return !$resource_type->isInternal() && $resource_type->isLocatable();
    });
    $comment_field_map = $this->entityFieldManager->getFieldMapByFieldType('comment');
    $derivative_definitions = array_reduce($resource_types, function ($definitions, ResourceType $resource_type) use ($base_plugin_definition, $comment_field_map) {
      $entity_type_id = $resource_type->getEntityTypeId();
      if (!isset($comment_field_map[$entity_type_id])) {
        return $definitions;
      }
      foreach ($comment_field_map[$entity_type_id] as $field_name => $field_info) {
        if (!$resource_type->isFieldEnabled($field_name) || !in_array($resource_type->getBundle(), $field_info['bundles'], TRUE)) {
          continue;
        }
        $resource_type_name = $resource_type->getTypeName();
        foreach (['comments', 'comment'] as $link_relation) {
          $definitions["{$resource_type_name}.$field_name.$link_relation"] = array_merge([
            'link_relation_type' => $link_relation,
            'link_context' => [
              'resource_object' => $resource_type_name,
            ],
            'default_configuration' => [
              'comment_field_name' => $resource_type->getPublicName($field_name),
            ],
          ], $base_plugin_definition);
        }
      }
      return $definitions;
    }, []);
    return $derivative_definitions;
  }

}
