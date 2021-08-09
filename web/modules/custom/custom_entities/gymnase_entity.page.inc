<?php

/**
 * @file
 * Contains gymnase_entity.page.inc.
 *
 * Page callback for Gymnase entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Gymnase entity templates.
 *
 * Default template: gymnase_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_gymnase_entity(array &$variables) {
  // Fetch GymnaseEntity Entity Object.
  $gymnase_entity = $variables['elements']['#gymnase_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
