<?php

/**
 * Implements hook_theme_suggestions_HOOK_alter
 * @param array $suggestions
 * @param array $variables
 */
function radix_sdd_theme_suggestions_views_view_fields_alter(array &$suggestions, array $variables) {
  // If you want you can add a template suggestion for all views
  // based on their ID:
  $suggestions[] = sprintf('views_view_fields__%s', $variables['view']->id());

  // Or you can check the ID, add suggestions, do whatever you want
  // ...
}

/**
 * Implements hook_theme_suggestions_HOOK_alter
 * @param array $suggestions
 * @param array $variables
 */
function radix_sdd_theme_suggestions_views_view_alter(array &$suggestions, array $variables) {
  // If you want you can add a template suggestion for all views
  // based on their ID:
  $suggestions[] = sprintf('views_view__%s', $variables['view']->id());

  // Or you can check the ID, add suggestions, do whatever you want
  // ...
}

function radix_sdd_theme_suggestions_views_view_field_alter(array &$suggestions, array $variables) {
$n=0;
}
