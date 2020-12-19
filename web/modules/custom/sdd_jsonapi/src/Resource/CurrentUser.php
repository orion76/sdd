<?php

namespace Drupal\sdd_jsonapi\Resource;

use Drupal\Core\Access\CsrfRequestHeaderAccessCheck;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
class CurrentUser extends EntityQueryResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $tokenGenerator;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, CsrfTokenGenerator $token_generator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $account;
    $this->tokenGenerator = $token_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('csrf_token')
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
    $user_storage = $this->entityTypeManager->getStorage('user');
    $uid = $this->currentUser->id();
    $current_user = $user_storage->load($uid);

    $data = $this->createCollectionDataFromEntities([$current_user], TRUE);
    //    $obj=&$data[0];
    /** @var $obj ResourceObjectData */

    $meta['token'] = $this->tokenGenerator->get(CsrfRequestHeaderAccessCheck::TOKEN_KEY);

    return $this->createJsonapiResponse($data, $request,200,[],NULL,$meta);

  }


  /**
   * {@inheritdoc}
   */
  public function getRouteResourceTypes(Route $route, string $route_name): array {
    $resource_type = $this->getResourceTypesByEntityTypeId('user');
    //    $temp=reset($resource_type);
    /** @var $temp ConfigurableResourceType */
    //    $temp->getFields();
    return $resource_type;
  }


}
