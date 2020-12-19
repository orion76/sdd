<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal;
use Drupal\computed_field\Plugin\ComputedFieldManager;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Defines a item list class for entity reference fields.
 */
class ComputedFieldItemList extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  protected $valueCalculated;

  /** @var ComputedFieldManager */
  protected $pluginManager;

  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
  }

  protected function getPluginManager() {
    if (empty($this->pluginManager)) {
      $this->pluginManager = Drupal::service('plugin.manager.computed_field');
    }
    return $this->pluginManager;
  }

  /**
   * @return object
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getPlugin() {
    $plugin_id = $this->definition->getSetting('plugin_id');
    return $this->getPluginManager()->createInstance($plugin_id);
  }

  protected function computeValue() {
    $plugin = $this->getPlugin();
    $this->list = [];
    $values = $plugin->getValue($this->definition, $this->getEntity());
    foreach ($values as $delta => $value) {
      $this->list[$delta] = $this->createItem($delta, $value);
    }
  }

}
