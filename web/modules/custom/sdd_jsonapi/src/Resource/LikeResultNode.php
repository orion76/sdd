<?php

namespace Drupal\sdd_jsonapi\Resource;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\sdd_jsonapi\VoteResultBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function reset;

/**
 * Processes a request for the authenticated user's information.
 *
 * @internal
 */
class LikeResultNode extends VoteResultBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\votingapi\VoteStorageInterface
   */
  protected $voteResultStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected $nodeStorage;

  /** @var RouteMatchInterface */
  protected $routeMath;


  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
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

  function getConditions(Request $request, array $resource_types) {
    $node_uuid = $request->attributes->get('node');
    //NotFoundHttpException

    if ($node = $this->nodeStorage->loadByProperties(['uuid' => $node_uuid])) {
      $node = reset($node);
      $conditions = [
        'entity_type' => ['node'],
        'entity_id' => [$node->id()],
      ];
      return $conditions;
    }
    throw new  NotFoundHttpException(sprintf('Not found node with uuid:%s', $node_uuid));
  }
}
