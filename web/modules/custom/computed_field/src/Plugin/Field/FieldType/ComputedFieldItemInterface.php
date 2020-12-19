<?php

namespace Drupal\computed_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Base class for numeric configurable field types.
 */
interface ComputedFieldItemInterface extends FieldItemInterface {

  function getPlugin();
}
