<?php


namespace Drupal\sdd\Plugin\ComputedField;


use Drupal\computed_field\Plugin\ComputedFieldPluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function file_create_url;

/**
 * @ComputedField(
 *   id = "user_avatar_url",
 *   label = @Translation("User avatar URL")
 * )
 *
 * @package Drupal\computed_field\Plugin\ComputedField
 */
class UserAvatarUrl extends ComputedFieldPluginBase implements ContainerFactoryPluginInterface {

  /** @var $fieldManager EntityFieldManagerInterface */
  private $fieldManager;

  /** @var EntityRepositoryInterface */
  private $entityRepository;

  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityFieldManagerInterface $fieldManager,
                              EntityRepositoryInterface $entityRepository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fieldManager = $fieldManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity.repository')
    );
  }

  function getValue(DataDefinitionInterface $definition, ContentEntityInterface $entity) {
    /** @var $entity \Drupal\user\UserInterface */
    /** @var $picture \Drupal\file\Plugin\Field\FieldType\FileFieldItemList */
    $picture = $entity->get('user_picture');

    if (!$picture->isEmpty()) {
      $uri = $picture->entity->getFileUri();
    }
    else {
      $fields = $this->fieldManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
      $field_settings = $fields['user_picture']->getSetting('default_image');
      $file_uuid = $field_settings['uuid'];
      /** @var $file File */
      try {
        $file = $this->entityRepository->loadEntityByUuid('file', $file_uuid);
      } catch (EntityStorageException $e) {
      }
      $uri = $file->getFileUri();
    }
    $url = $uri ? file_create_url($uri) : '';
    return [$url];
  }

}
