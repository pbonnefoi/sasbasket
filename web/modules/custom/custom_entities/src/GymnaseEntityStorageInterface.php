<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface GymnaseEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Gymnase entity revision IDs for a specific Gymnase entity.
   *
   * @param \Drupal\custom_entities\Entity\GymnaseEntityInterface $entity
   *   The Gymnase entity entity.
   *
   * @return int[]
   *   Gymnase entity revision IDs (in ascending order).
   */
  public function revisionIds(GymnaseEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Gymnase entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Gymnase entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\custom_entities\Entity\GymnaseEntityInterface $entity
   *   The Gymnase entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(GymnaseEntityInterface $entity);

  /**
   * Unsets the language for all Gymnase entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
