<?php

namespace Drupal\message_send\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Message sender item annotation object.
 *
 * @see \Drupal\message_sender\Plugin\MessageSenderManager
 * @see plugin_api
 *
 * @Annotation
 */
class RecipientContactFactory extends Plugin {


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

  public $description;

  public $channel;
}
