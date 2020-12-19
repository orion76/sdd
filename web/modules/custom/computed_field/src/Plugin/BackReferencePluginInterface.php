<?php

namespace Drupal\computed_field\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Defines an interface for Computed field plugin plugins.
 */
interface BackReferencePluginInterface extends PluginInspectionInterface {


  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity);

  function getSettingsForm($values, FormStateInterface $form_state);

}
