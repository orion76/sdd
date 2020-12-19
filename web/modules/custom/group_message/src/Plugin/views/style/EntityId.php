<?php

namespace Drupal\views\Plugin\views\style;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;

/**
 * EntityReference style plugin.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "entity_id",
 *   title = @Translation("Entity Id list"),
 *   help = @Translation("Returns results as a PHP array of entity ID."),
 *   register_theme = FALSE,
 *   display_types = {"entity_ids"}
 * )
 */
class EntityId extends StylePluginBase {


  /**
   * {@inheritdoc}
   */
  public function render() {

    $id_field_alias = $this->view->storage->get('base_field');
    $results = [];
    foreach ($this->view->result as $records) {
  $n=0;
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function evenEmpty() {
    return TRUE;
  }

}
