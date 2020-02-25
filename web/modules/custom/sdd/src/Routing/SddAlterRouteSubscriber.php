<?php


namespace Drupal\sdd\Routing;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class SddAlterRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Remove the /search route.
    $collection->remove('tracker.page');
    $collection->remove('tracker.users_recent_content');
  }
}
