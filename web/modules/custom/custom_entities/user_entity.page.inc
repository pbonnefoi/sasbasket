<?php

/**
 * @file
 * Contains user_entity.page.inc.
 *
 * Page callback for User entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for User entity templates.
 *
 * Default template: user_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_entity(array &$variables) {
  // Fetch UserEntity Entity Object.
  $user_entity = $variables['elements']['#user_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
