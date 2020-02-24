<?php


namespace Drupal\message_send\message\recipient_contact;


interface RecipientContactInterface {
public function getChannel();
public function getAddress();
}
