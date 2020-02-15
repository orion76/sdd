<?php

namespace Drupal\jsonapi_comment\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentsCollectionLinkProvider.
 *
 * @internal
 */
final class CommentReplyLinkProviderDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The JSON:API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * CommentsReplyLinkProvider constructor.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   *   The JSON:API resource type repository.
   */
  public function __construct(ResourceTypeRepositoryInterface $resource_type_repository) {
    $this->resourceTypeRepository = $resource_type_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('jsonapi.resource_type.repository')
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
    $derivative_definitions = array_reduce($resource_types, function ($definitions, ResourceType $resource_type) use ($base_plugin_definition) {
      $resource_type_name = $resource_type->getTypeName();
      $definitions[$resource_type_name] = array_merge([
        'link_key' => 'reply',
        'link_relation_type' => 'comment',
        'link_context' => [
          'resource_object' => $resource_type_name,
        ],
      ], $base_plugin_definition);
      return $definitions;
    }, []);
    return $derivative_definitions;
  }

}
