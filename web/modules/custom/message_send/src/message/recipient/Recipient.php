<?php


namespace Drupal\message_send\message\recipient;


use Drupal\message_send\message\recipient_contact\RecipientContact;
use Drupal\user\UserInterface;

class Recipient implements RecipientInterface {

  protected $uid;

  protected $contacts;

  protected $language;

  public function __construct(UserInterface $user) {
    $this->uid = $user->id();
    $this->contacts = $this->extractContacts($user);
    $this->language = $user->language();
  }

  protected function extractContacts(UserInterface $user){
    $contacts=[];

    return $contacts;
  }

  public function getId() {
    return $this->uid;
  }


  public function getContacts() {
    return $this->contacts;
  }

  public function getLanguage() {
    return $this->language;
  }

  public function setContact($channel, $address) {
    $this->contacts[$channel] = new RecipientContact($channel, $address);
  }
}
