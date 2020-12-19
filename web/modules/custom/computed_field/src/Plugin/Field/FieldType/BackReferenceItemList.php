<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal;
use Drupal\computed_field\Exception\BackReferenceFieldException;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\computed_field\Plugin\ComputedFieldManager;

/**
 * Defines a item list class for entity reference fields.
 */
class BackReferenceItemList extends EntityReferenceFieldItemList implements EntityReferenceFieldItemListInterface {

  use ComputedItemListTrait;

  protected $valueCalculated;

  /** @var ComputedFieldManager */
  protected $pluginManager;

  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
  }

  protected function getPluginManager() {
    if (empty($this->pluginManager)) {
      $this->pluginManager = Drupal::service('plugin.manager.back_reference');
    }
    return $this->pluginManager;
  }

  /**
   * @return object
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\computed_field\Exception\BackReferenceFieldException
   */
  public function getPlugin() {
    $plugin_id = $this->definition->getSetting('plugin_id');
    if (!$plugin_id) {
      throw new BackReferenceFieldException('Plugin ID is missing');
    }
    return $this->getPluginManager()->createInstance($plugin_id);
  }

  protected function computeValue() {
    $this->list = [];
    try {
      $plugin = $this->getPlugin();
    } catch (Drupal\Component\Plugin\Exception\PluginException $e) {
    } catch (BackReferenceFieldException $e) {
    }

    if ($plugin) {
      $ids = $plugin->getValue($this->definition, $this->getEntity());
      foreach ($ids as $delta => $id) {
        $this->list[$delta] = $this->createItem($delta, $id);
      }
    }
  }
public function defaultValuesForm(array &$form, FormStateInterface $form_state) {
  return NULL;
}

}
