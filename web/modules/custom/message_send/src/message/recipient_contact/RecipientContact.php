<?php


namespace Drupal\message_send\message\recipient_contact;


class RecipientContact implements RecipientContactInterface {

  protected $channel;

  protected $address;

  public function __construct($channel, $address) {
    $this->channel = $channel;
    $this->address = $address;
  }

  public function getChannel() {
    return $this->channel;
  }

  public function getAddress() {
    return $this->address;
  }
}
