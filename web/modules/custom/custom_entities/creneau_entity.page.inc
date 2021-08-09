<?php

/**
 * @file
 * Contains creneau_entity.page.inc.
 *
 * Page callback for Creneau entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Creneau entity templates.
 *
 * Default template: creneau_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_creneau_entity(array &$variables) {
  // Fetch CreneauEntity Entity Object.
  $creneau_entity = $variables['elements']['#creneau_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
