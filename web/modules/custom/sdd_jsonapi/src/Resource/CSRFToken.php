<?php

namespace Drupal\sdd_jsonapi\Resource;

use Drupal\Core\Access\CsrfRequestHeaderAccessCheck;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeAttribute;
use Drupal\jsonapi_resources\Resource\ResourceBase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use function assert;
use function reset;

/**
 * Processes a request for the authenticated user's information.
 *
 * @internal
 */
class CSRFToken extends ResourceBase implements ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public function getRouteResourceTypes(Route $route, string $route_name): array {
    $fields = [
      'token' => new ResourceTypeAttribute('token'),
    ];
    $resource_type = new ResourceType('user', 'token', NULL, FALSE, TRUE, TRUE, FALSE, $fields);
    $resource_type->setRelatableResourceTypes([]);
    return [$resource_type];
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
    $current_user = $user_storage->load($this->currentUser->id());
    assert($current_user instanceof UserInterface);
    $resource_type = reset($resource_types);

    $links = new LinkCollection([]);
    $primary_data = new ResourceObject(
      $current_user,
      $resource_type,
      $current_user->uuid(),
      NULL,
      [
        'token' => $this->tokenGenerator->get(CsrfRequestHeaderAccessCheck::TOKEN_KEY),
      ],
      $links
    );
    $top_level_data = new ResourceObjectData([$primary_data], 1);
    $response = $this->createJsonapiResponse($top_level_data, $request);
    $response->addCacheableDependency((new CacheableMetadata())->addCacheContexts(['user']));
    return $response;
  }


}
