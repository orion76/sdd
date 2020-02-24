<?php

namespace Drupal\Tests\message_send\Kernel;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\KernelTestBase;
use Drupal\message_send\Entity\MessageSendConfig;
use function sprintf;
use function strtolower;

/**
 * Tests actions source plugin.
 *
 * @covers \Drupal\action\Plugin\migrate\source\Action
 * @group action
 */
class MessageSendConfigTest extends KernelTestBase {

  protected $storage;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['message', 'message_sender', 'message_send'];


  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('message_send_config');
    $this->storage = $this->container->get('entity_type.manager')->getStorage('message_send_config');
    //    $this->controller = $this->container->get('entity_type.manager')->getStorage('block');

    //    $this->container->get('theme_installer')->install(['stark']);
  }

  protected function getConfigData() {
    return [

      'label' => 'test',
      'template' => ['id' => 'template_id'],
      'source_entity' => [
        'entity_type' => 'entity_type_test',
        'bundle' => 'bundle_test',
        'properties' => [],
        'fields' => [],
      ],
      'recipient' => [
        'views' => [
          'id' => 'views_id_test',
          'display' => 'views_display_test',
        ],
      ],
      'send' => [
        'use_queue' => TRUE,
      ],
    ];
  }

  protected function createConfig($id) {
    $config = MessageSendConfig::create(['id' => $id] + $this->getConfigData());
    $config->save();
    return $this->config("message_send.message_send_config.$id");
  }

  protected function getConfigEntity($id) {
    return $this->storage->load($id);
  }

  /**
   * @group __active
   */
  public function testMessageSendConfigCreate() {

    $id = strtolower($this->randomMachineName());
    $this->createConfig($id);
    $entity = $this->getConfigEntity($id);
    $this->assertEqual($entity instanceof MessageSendConfig, TRUE);
    $this->assertEqual($entity->get('id'), $id);
  }

  /**
   * @group active
   */
  public function testMessageSendMethods() {

    $id = strtolower($this->randomMachineName());
    /** @var $entity MessageSendConfig */
    $this->createConfig($id);
    $entity = $this->getConfigEntity($id);
    $data = $this->getConfigData();
    $this->assertEqual($entity->getTemplateId(), $data['template']['id'], sprintf('Template id equal %0', $data['template']['id']));

    $views_key="{$data['recipient']['views']['id']}:{$data['recipient']['views']['display']}";
    $this->assertEqual($entity->getUserViewsKey(), $views_key, sprintf('Views key equal %0', $views_key));

    $views=$entity->getUserViews();

    $views_id=$data['recipient']['views']['id'];
    $this->assertEqual($views['id'], $views_id, sprintf('Views ID equal %0', $views_id));

    $views_display=$data['recipient']['views']['display'];
    $this->assertEqual($views['display'],$views_display , sprintf('Views Display equal %0', $views_display));


    $this->assertEqual($entity->getSourceEntityType(), $data['source_entity']['entity_type'], sprintf('Source entity type equal %0', $data['source_entity']['entity_type']));
    $this->assertEqual($entity->getSourceBundle(), $data['source_entity']['bundle'], sprintf('Source entity bundle equal %0', $data['source_entity']['bundle']));

  }
}
