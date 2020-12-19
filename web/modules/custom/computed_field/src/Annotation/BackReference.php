<?php

namespace Drupal\computed_field\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Computed field plugin item annotation object.
 *
 * @see \Drupal\computed_field\Plugin\ComputedFieldManager
 * @see plugin_api
 *
 * @Annotation
 */
class BackReference extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
