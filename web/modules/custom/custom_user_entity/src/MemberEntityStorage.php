<?php

namespace Drupal\custom_user_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\custom_user_entity\Entity\MemberEntityInterface;

/**
 * Defines the storage handler class for Member entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Member entity entities.
 *
 * @ingroup custom_user_entity
 */
class MemberEntityStorage extends SqlContentEntityStorage implements MemberEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(MemberEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {member_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {member_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(MemberEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {member_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('member_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
