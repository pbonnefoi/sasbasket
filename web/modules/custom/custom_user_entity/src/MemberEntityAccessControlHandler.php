<?php

namespace Drupal\custom_user_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Member entity entity.
 *
 * @see \Drupal\custom_user_entity\Entity\MemberEntity.
 */
class MemberEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\custom_user_entity\Entity\MemberEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished member entity entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published member entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit member entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete member entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add member entity entities');
  }


}
