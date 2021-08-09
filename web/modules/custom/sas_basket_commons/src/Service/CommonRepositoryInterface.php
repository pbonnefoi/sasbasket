<?php

namespace Drupal\sas_basket_commons\Service;

use Drupal\node\Entity\Node;

/**
 * Components Repository Interface.
 */
interface CommonRepositoryInterface {
  /**
   * @param string $type
   * @param bool $check_type_langcode
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLastPublishedNode(string $type, $check_type_langcode = FALSE);

}
