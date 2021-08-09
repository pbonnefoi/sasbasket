<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface CreneauEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Creneau entity revision IDs for a specific Creneau entity.
   *
   * @param \Drupal\custom_entities\Entity\CreneauEntityInterface $entity
   *   The Creneau entity entity.
   *
   * @return int[]
   *   Creneau entity revision IDs (in ascending order).
   */
  public function revisionIds(CreneauEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Creneau entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Creneau entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\custom_entities\Entity\CreneauEntityInterface $entity
   *   The Creneau entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CreneauEntityInterface $entity);

  /**
   * Unsets the language for all Creneau entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
