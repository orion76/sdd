<?php


namespace Drupal\message_send\Plugin\RecipientContactFactory;



use Drupal\message_send\message\recipient_contact\RecipientContact;
use Drupal\message_send\message\recipient_contact\RecipientContactFactoryBase;
use Drupal\message_send\message\recipient_contact\RecipientContactFactoryInterface;
use Drupal\message_send\message\recipient_contact\RecipientContactInterface;
use Drupal\user\UserInterface;

/**
 *
 * @RecipientContactFactory(
 *   id = "registration_email",
 *   label = @Translation("Rregistration email"),
 *   channel = "email"
 * )
 */
class RegistrationEmail extends RecipientContactFactoryBase implements RecipientContactFactoryInterface {
  function create(UserInterface $user): RecipientContactInterface {
    return new RecipientContact($this->getChannel(), $user->getEmail());
  }
}
