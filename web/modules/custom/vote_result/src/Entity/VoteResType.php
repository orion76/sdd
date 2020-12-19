<?php

namespace Drupal\vote_result\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Vote result type entity.
 *
 * @ConfigEntityType(
 *   id = "vote_res_type",
 *   label = @Translation("Vote result type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\vote_result\VoteResTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\vote_result\Form\VoteResTypeForm",
 *       "edit" = "Drupal\vote_result\Form\VoteResTypeForm",
 *       "delete" = "Drupal\vote_result\Form\VoteResTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\vote_result\VoteResTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "vote_res_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "vote_res",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/vote_res_type/{vote_res_type}",
 *     "add-form" = "/admin/structure/vote_res_type/add",
 *     "edit-form" = "/admin/structure/vote_res_type/{vote_res_type}/edit",
 *     "delete-form" = "/admin/structure/vote_res_type/{vote_res_type}/delete",
 *     "collection" = "/admin/structure/vote_res_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "fields"
 *   }
 * )
 */
class VoteResType extends ConfigEntityBundleBase implements VoteResTypeInterface {

  /**
   * The Vote result type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Vote result type label.
   *
   * @var string
   */
  protected $label;

  protected $fields;

}
