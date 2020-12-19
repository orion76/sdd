<?php

namespace Drupal\Tests\message_send\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\message\Entity\Message;
use Drupal\message_send\Entity\MessageSendConfig;
use Drupal\message_send\Entity\MessageSendConfigInterface;
use Drupal\message_send\message\recipient\Recipient;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\message\Kernel\MessageTemplateCreateTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
use function count;

/**
 * Tests actions source plugin.
 *
 * @covers \Drupal\action\Plugin\migrate\source\Action
 * @group action
 */
class MessageSendServiceTest extends EntityKernelTestBase {

  use ContentTypeCreationTrait;
  use MessageTemplateCreateTrait;
  use NodeCreationTrait;

  //  use UserCreationTrait;

  const TEMPLATE_ID = 'template_test';

  const MESSAGE_SEND_CONFIG_ID = 'message_send_config_test';

  protected $source_storage;

  /** @var \Drupal\message_send\MessageSendServiceInterface */
  protected $send_service;

  /** @var \Drupal\user\UserInterface */
  protected $user;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['message', 'message_send', 'message_sender', 'node', 'user', 'filter', 'system'];


  protected function setUp() {
    parent::setUp();
    $this->installConfig(static::$modules);

    $this->config('system.site')->set('mail', 'gpb@yandex.ru')->save();

    $this->send_service = $this->container->get('message_send.service');
    $this->source_storage = $this->container->get('entity_type.manager')->getStorage('message_send_config');
    // Create the node bundles required for testing.
    $this->user = $this->createUser(['name' => 'pasha', 'mail' => 'gpb@yandex.ru']);

  }

  protected function getConfigData() {
    return [

      'label' => 'test',
      'template' => ['id' => 'template_id'],
      'source_entity' => [
        'events' => ['add', 'update'],
        'entity_type' => 'node',
        'bundle' => 'page',
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

  protected function createConfig($id, $data = []) {
    $config = MessageSendConfig::create(['id' => $id] + $data + $this->getConfigData());
    $config->save();
    return $this->config("message_send.message_send_config.$id");
  }

  /**
   * @param $id
   *
   * @return \Drupal\message_send\Entity\MessageSendConfigInterface
   */
  protected function getConfigEntity($id): MessageSendConfigInterface {
    return $this->source_storage->load($id);
  }

  /**
   * @group active
   */
  public function __testSendMessageToRecipient() {

    $recipient = new Recipient($this->user);

    $this->createConfig(self::MESSAGE_SEND_CONFIG_ID, $this->getConfigData());

    $this->createMessageTemplate(self::TEMPLATE_ID);

    $message = Message::create(['template' => self::TEMPLATE_ID, 'uid' => $this->user->id()]);
    $result = $this->send_service->sendMessageToRecipient($recipient, $message);
    $this->assertEqual($result, TRUE);
  }


  /**
   * @group active
   */
  public function __testLoadConfigByEvent() {
    $config_id = 'config_id';
    $this->createConfig($config_id, $this->getConfigData());

    $add_configs = $this->send_service->loadConfigByEvent('add');
    $this->assertEqual(count($add_configs), 1);

    $upd_configs = $this->send_service->loadConfigByEvent('update');
    $this->assertEqual(count($upd_configs), 1);

    $del_configs = $this->send_service->loadConfigByEvent('delete');
    $this->assertEqual(count($del_configs), 0);

  }

  /**
   * @group active
   */
  public function testIsEntityForMailing() {
    $entity = $this->createNode(['bundle' => 'page']);

    $config_id = 'config_id';
    $this->createConfig($config_id, $this->getConfigData());

    $config = $this->getConfigEntity($config_id);
    $result = $this->send_service->isEntityForMailing($config, $entity);
    $this->assertEqual($result, TRUE);

    $entity = $this->createNode(['type' => 'article']);
    $result = $this->send_service->isEntityForMailing($config, $entity);
    $this->assertEqual($result, FALSE);
  }

  /**
   * @dataProvider dataConfigs
   * @group __active
   */
  public function __testMessageSend($config) {

    $config_id = 'config_id';
    $this->createConfig($config_id, $config);
    $config = $this->getConfigEntity($config_id);


    $id = $this->createNode();
    //    $node = $this->loadNode($id);
    //    $this->assertEqual($this->send_service->isEntityForMailing($config, $node), TRUE);
  }

  public function dataConfigs() {
    return [
      [['entity_type' => 'node']],
      [['entity_type' => 'node', 'bundle' => 'page']],
      [['entity_type' => 'node', 'bundle' => 'page', 'published' => TRUE]],
    ];
  }
}
