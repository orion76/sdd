<?php


namespace Drupal\message_sender\Plugin\MessageSender;


use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\message_sender\Plugin\MessageSenderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Email notifier.
 *
 * @MessageSender(
 *   id = "email",
 *   title = @Translation("Email"),
 *   description = @Translation("Send messages via email"),
 * )
 */
class Email extends MessageSenderBase implements ContainerFactoryPluginInterface{

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mail_manager) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $mail_manager=$container->get('plugin.manager.mail');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $mail_manager
    );
  }


  public function deliver($mail_id,$params, $from, $to, $language) {
    $result = $this->mailManager->mail(
      'system',
      $mail_id,
      $to,
      $language,
      $params,
      $from
    );

    return $result['result'];
  }
}
