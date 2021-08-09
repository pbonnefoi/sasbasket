<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\custom_entities\Entity\CreneauEntityInterface;

/**
 * Defines the storage handler class for Creneau entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Creneau entity entities.
 *
 * @ingroup custom_entities
 */
class CreneauEntityStorage extends SqlContentEntityStorage implements CreneauEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(CreneauEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {creneau_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {creneau_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(CreneauEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {creneau_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('creneau_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
