<?php

namespace Drupal\entity_reference\Plugin\views\display;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\display\EntityReference;

/**
 * The plugin that handles an EntityReference display.
 *
 * "entity_reference_display" is a custom property, used with
 * \Drupal\views\Views::getApplicableViews() to retrieve all views with a
 * 'Entity Reference' display.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "entity_ids",
 *   title = @Translation("Entity Ids"),
 *   admin = @Translation("Entity Ids"),
 *   help = @Translation("Selects lis of entity ids."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_menu_links = FALSE
 * )
 */
class EntityIds extends DisplayPluginBase {}
