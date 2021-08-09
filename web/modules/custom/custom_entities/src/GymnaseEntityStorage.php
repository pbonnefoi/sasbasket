<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\custom_entities\Entity\GymnaseEntityInterface;

/**
 * Defines the storage handler class for Gymnase entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Gymnase entity entities.
 *
 * @ingroup custom_entities
 */
class GymnaseEntityStorage extends SqlContentEntityStorage implements GymnaseEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(GymnaseEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {gymnase_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {gymnase_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(GymnaseEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {gymnase_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('gymnase_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
