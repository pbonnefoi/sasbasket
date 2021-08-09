<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Gymnase entity entity.
 *
 * @see \Drupal\custom_entities\Entity\GymnaseEntity.
 */
class GymnaseEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\custom_entities\Entity\GymnaseEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished gymnase entity entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published gymnase entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit gymnase entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete gymnase entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add gymnase entity entities');
  }


}
