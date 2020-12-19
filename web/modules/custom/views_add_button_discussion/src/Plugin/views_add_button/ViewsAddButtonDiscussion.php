<?php

namespace Drupal\views_add_button_discussion\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;
use function array_map;
use function implode;

/**
 * Integrates Views Add Button with group entities.
 *
 * @ViewsAddButton(
 *   id = "views_add_button_discussion",
 *   label = @Translation("ViewsAddButtonDiscussion"),
 *   target_entity = "node"
 * )
 */
class ViewsAddButtonDiscussion extends PluginBase implements ViewsAddButtonInterface {

  /**
   * Plugin description.
   *
   * @return string
   *   A string description.
   */
  public function description() {
    return $this->t('Views Add Button URL Generator for Discussion entities');
  }

  /**
   * Check for access to the appropriate "add" route.
   *
   * @param string $entity_type
   *   Entity id as a machine name.
   * @param string $bundle
   *   The bundle string.
   * @param string $context
   *   Entity context string
   *
   * @return bool
   *   Whether we have access.
   */
  public static function checkAccess($entity_type, $bundle, $context) {
    if ($bundle) {
      $accessManager = \Drupal::service('access_manager');
      return $accessManager->checkNamedRoute('node.add', ['node_type' => $bundle], \Drupal::currentUser());
    }
  }

  /**
   * Generate the Add Button Url.
   *
   * @param string $entity_type
   *   Entity id as a machine name.
   * @param string $bundle
   *   The bundle string.
   * @param array $options
   *   Array of options to be used when building the Url, and Link.
   * @param string $context
   *   Entity context string, a comma-separated list of values.
   *
   * @return \Drupal\Core\Url
   *   The Url to use in the Add Button link.
   */
  public static function generateUrl($entity_type, $bundle, array $options, $context = '') {

    $options['query']["pp[field_discussion_source]"] = $context;
    $options['attributes']['target']='_blank';
    $url = Url::fromRoute("node.add", ['node_type' => $bundle], $options);
    return $url;
  }

}
