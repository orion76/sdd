<?php

namespace Drupal\message_send\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

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

}
