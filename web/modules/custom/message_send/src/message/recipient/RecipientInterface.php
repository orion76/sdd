<?php


namespace Drupal\message_send\message\recipient;


interface RecipientInterface {

  public function getId();

  public function getContacts();

  public function setContact($channel, $address);

  public function getLanguage();


}
