<?php


namespace Drupal\message_send\message\recipient;


use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\message_send\Entity\MessageSendConfigInterface;
use Drupal\views\Views;
use function array_column;
use function array_combine;

class RecipientsService implements RecipientsServiceInterface {

  /** @var \Drupal\message_send\Entity\MessageSendConfigInterface */
  protected $configStorage;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->configStorage = $entityTypeManager->getStorage('message_send_config');
  }

  protected function getIds($views_id, $display_id, $id_field) {


    $view = Views::getView($views_id);
    //$view->setArguments($args);
    $view->setDisplay($display_id);
    $view->execute();

    $ids = array_column($view->result, $id_field);
    return array_combine($ids, $ids);
  }


  public function getRecipients(MessageSendConfigInterface $config) {
    $views = $config->getUserViews();

    $view = Views::getView($views['id']);
    $view->setDisplay($views['display']);
    $view->execute();

    $ids = array_column($view->result, 'uid');
    return array_combine($ids, $ids);
  }

}
