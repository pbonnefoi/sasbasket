<?php

namespace Drupal\custom_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface UserEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of User entity revision IDs for a specific User entity.
   *
   * @param \Drupal\custom_entities\Entity\UserEntityInterface $entity
   *   The User entity entity.
   *
   * @return int[]
   *   User entity revision IDs (in ascending order).
   */
  public function revisionIds(UserEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as User entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   User entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\custom_entities\Entity\UserEntityInterface $entity
   *   The User entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(UserEntityInterface $entity);

  /**
   * Unsets the language for all User entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
