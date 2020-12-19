<?php

namespace Drupal\entity_prepopulate\Entity;

use Drupal;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\HtmlEntityFormController;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Wrapping controller for entity forms that serve as the main page body.
 */
class EntityFormController extends HtmlEntityFormController {


  protected function getFormObject(RouteMatchInterface $route_match, $form_arg) {
    // If no operation is provided, use 'default'.
    $form_arg .= '.default';
    [$entity_type_id, $operation] = explode('.', $form_arg);

    $form_object = $this->entityTypeManager->getFormObject($entity_type_id, $operation);

    $entity = $form_object->getEntityFromRouteMatch($route_match, $entity_type_id);

    $request = Drupal::requestStack()->getCurrentRequest();
    if ($values = $request->get('pp')) {
      $this->setEntityValues($entity, $values);

    }
    $form_object->setEntity($entity);
    return $form_object;
  }

  protected function setEntityValues(EntityInterface $entity, $values) {
    foreach ($values as $field => $value) {
      $entity->{$field} = $value;
    }
  }
}
