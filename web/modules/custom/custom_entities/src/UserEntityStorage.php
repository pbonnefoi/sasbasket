<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\custom_entities\Entity\UserEntityInterface;

/**
 * Defines the storage handler class for User entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * User entity entities.
 *
 * @ingroup custom_entities
 */
class UserEntityStorage extends SqlContentEntityStorage implements UserEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(UserEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {user_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {user_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(UserEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {user_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('user_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
