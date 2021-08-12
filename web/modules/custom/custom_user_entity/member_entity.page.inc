<?php

/**
 * @file
 * Contains member_entity.page.inc.
 *
 * Page callback for Member entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Member entity templates.
 *
 * Default template: member_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_member_entity(array &$variables) {
  // Fetch MemberEntity Entity Object.
  $member_entity = $variables['elements']['#member_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
