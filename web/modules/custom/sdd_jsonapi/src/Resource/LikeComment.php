<?php

namespace Drupal\sdd_jsonapi\Resource;

use Drupal\Core\Access\CsrfRequestHeaderAccessCheck;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType;
use Drupal\jsonapi_resources\Resource\EntityQueryResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Processes a request for the authenticated user's information.
 *
 * @internal
 */
class LikeComment extends EntityQueryResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\votingapi\VoteStorageInterface
   */
  protected $voteStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected $commentStorage;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /** @var RouteMatchInterface */
  protected $routeMath;


  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              AccountInterface $account,
                              RouteMatchInterface $route_match
  ) {
    $this->commentStorage = $entity_type_manager->getStorage('comment');
    $this->voteStorage = $entity_type_manager->getStorage('vote');
    $this->currentUser = $account;
    $this->routeMath = $route_match;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('current_route_match')

    );
  }

  /**
   * Process the resource request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The route resource types.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function process(Request $request, array $resource_types) {
    $parent_uuid = $request->attributes->get('comment');
    $parent = $this->commentStorage->loadByProperties(['uuid' => $parent_uuid]);
    $parent = reset($parent);

    $children = $this->commentStorage->loadByProperties(['pid' => $parent->id()]);
    $ids = array_map(function ($item) {
      return $item->id();
    }, $children);
    $votes = $this->voteStorage->loadByProperties([
      'entity_type' => ['comment'],
      'entity_id' => $ids,
      'user_id' => [''],
      'type' => ['updown'],
    ]);
    $data = $this->createCollectionDataFromEntities($votes, TRUE);
    return $this->createJsonapiResponse($data, $request);
  }


  /**
   * {@inheritdoc}
   */
  public function getRouteResourceTypes(Route $route, string $route_name): array {
    $resource_type = $this->getResourceTypesByEntityTypeId('vote');
    //    $temp=reset($resource_type);
    /** @var $temp ConfigurableResourceType */
    //    $temp->getFields();
    return $resource_type;
  }


}
