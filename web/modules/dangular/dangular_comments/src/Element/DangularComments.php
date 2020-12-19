<?php

namespace Drupal\dangular_comments\Element;

use Drupal\Core\Render\Element\RenderElement;
use function get_class;

/**
 *
 * @RenderElement("dangular_comments")
 */
class DangularComments extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $attached['library'][] = 'dangular_comments/dangular_comments';
    return [
      '#entity_type' => '',
      '#entity_id' => '',
      '#field_name' => '',
      '#theme' => 'dangular_comments',
      '#pre_render' => [
        [$class, 'preRender'],
      ],
      '#attached'=>$attached
    ];
  }


  /**
   * Provides markup for associating a tray trigger with a tray element.
   *
   * A tray is a responsive container that wraps renderable content. Trays
   * present content well on small and large screens alike.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   */
  public static function preRender($element) {


    // Provide attributes necessary for trays.
    $attributes = [
      'entity_type' => $element['#entity_type'],
      'entity_id' => $element['#entity_id'],
      'field_name' => $element['#field_name'],
    ];

    $element['#attributes'] = $attributes;

    return $element;
  }
}
