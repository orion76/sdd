<?php

namespace Drupal\message_send\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use function explode;

/**
 * Defines the Message send config entity.
 *
 * @ConfigEntityType(
 *   id = "message_send_config",
 *   label = @Translation("Message send config"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\message_send\MessageSendConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\message_send\Form\MessageSendConfigForm",
 *       "edit" = "Drupal\message_send\Form\MessageSendConfigForm",
 *       "delete" = "Drupal\message_send\Form\MessageSendConfigDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\message_send\MessageSendConfigHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "message_send_config",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/message_send_config/{message_send_config}",
 *     "add-form" = "/admin/structure/message_send_config/add",
 *     "edit-form" = "/admin/structure/message_send_config/{message_send_config}/edit",
 *     "delete-form" = "/admin/structure/message_send_config/{message_send_config}/delete",
 *     "collection" = "/admin/structure/message_send_config"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "template",
 *     "source",
 *     "recipient",
 *     "send"
 *   },
 *   lookup_keys = {
 *     "entity_type"
 *   }
 * )
 */
class MessageSendConfig extends ConfigEntityBase implements MessageSendConfigInterface {

  /**
   * The Message send config ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Message send config label.
   *
   * @var string
   */
  protected $label;

  protected $template;

  protected $recipient;

  protected $source;

  protected $send;


  public function getUserViews() {
    return $this->getValue('recipient', ['views'], ['id' => '', 'display' => '']);
  }

  public function getUserViewsKey() {
    $views = $this->getUserViews();
    if (isset($views['id']) && isset($views['display'])) {
      return "{$views['id']}:{$views['display']}";
    }
  }

  public function useQueue() {
    return $this->getValue('send', ['use_queue'], FALSE);
  }

  public function getSourceEntityType() {
    return $this->getValue('source', ['entity_type'], NULL);
  }

  protected function getValue($field, $parents, $default) {
    if (!isset($this->{$field})) {
      return $default;
    }
    $value = NestedArray::getValue($this->{$field}, $parents);
    return !empty($value) ? $value : $default;
  }

  public function getSourceEvents() {
    return $this->getValue('source', ['events'], []);
  }

  public function getSourceBundle() {
    return $this->getValue('source', ['bundle'], NULL);
  }

  public function getSourceFields() {
    return $this->getValue('source', ['fields'], []);
  }

  public function getSourceProperties() {
    return $this->getValue('source', ['properties'], []);
  }

  public function getTemplateId() {
    return $this->getValue('template', ['id'], NULL);
  }
}
