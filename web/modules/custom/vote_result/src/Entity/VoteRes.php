<?php

namespace Drupal\vote_result\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use function t;

/**
 * Defines the Vote result entity.
 *
 * @ingroup vote_result
 *
 * @ContentEntityType(
 *   id = "vote_res",
 *   label = @Translation("Vote result"),
 *   bundle_label = @Translation("Vote result type"),
 *   handlers = {
 *     "storage" = "Drupal\vote_result\VoteResStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\vote_result\VoteResListBuilder",
 *     "views_data" = "Drupal\vote_result\Entity\VoteResViewsData",
 *
 *     "access" = "Drupal\vote_result\VoteResAccessControlHandler",
 *   },
 *   base_table = "vote_res",
 *   translatable = FALSE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer vote result entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   bundle_entity_type = "vote_res_type",
 *   field_ui_base_route = "entity.vote_res_type.edit_form"
 * )
 */
class VoteRes extends ContentEntityBase implements VoteResInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\Exception\UnsupportedEntityTypeDefinitionException
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('field_computed')
      ->setLabel(t('Name'))
      ->setSetting('plugin_id', 'vote_result_title')
      ->setDescription(t('The name of the Vote result entity.'));

    $fields['status']->setDescription(t('A boolean indicating whether the Vote result is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Type'))
      ->setDescription(t('The type from the voted entity.'))
      ->setDefaultValue('node')
      ->setSettings([
        'max_length' => 64,
      ])
      ->setRequired(TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Voted entity'))
      ->setDescription(t('The ID from the voted entity'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    return $fields;
  }

}
