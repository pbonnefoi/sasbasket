<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Creneau entity entity.
 *
 * @see \Drupal\custom_entities\Entity\CreneauEntity.
 */
class CreneauEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\custom_entities\Entity\CreneauEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished creneau entity entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published creneau entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit creneau entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete creneau entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add creneau entity entities');
  }


}
