<?php
function radix_sdd_preprocess_node(&$variables){
  /** @var $author_attributes \Drupal\Core\Template\Attribute   */
  $author_attributes=$variables['author_attributes'];
  $author_attributes->addClass('submitted-author');
}
