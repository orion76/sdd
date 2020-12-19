<?php

namespace Drupal\sdd_jsonapi;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType;
use Drupal\jsonapi_resources\Resource\EntityQueryResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;


abstract class VoteResultBase extends EntityQueryResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\votingapi\VoteStorageInterface
   */
  protected $voteResultStorage;


  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->voteResultStorage = $entity_type_manager->getStorage('vote_result');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
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
    $conditions = $this->getConditions($request, $resource_types);
    $conditions += ['function' => ['vote_sum']];
    $votes = $this->voteResultStorage->loadByProperties($conditions);
    $data = $this->createCollectionDataFromEntities($votes, TRUE);
    return $this->createJsonapiResponse($data, $request);
  }

  abstract function getConditions(Request $request, array $resource_types);


  /**
   * {@inheritdoc}
   */
  public function getRouteResourceTypes(Route $route, string $route_name): array {
    return $this->getResourceTypesByEntityTypeId('vote_result');
  }


}
