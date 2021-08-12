<?php

namespace Drupal\custom_user_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface MemberEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Member entity revision IDs for a specific Member entity.
   *
   * @param \Drupal\custom_user_entity\Entity\MemberEntityInterface $entity
   *   The Member entity entity.
   *
   * @return int[]
   *   Member entity revision IDs (in ascending order).
   */
  public function revisionIds(MemberEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Member entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Member entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\custom_user_entity\Entity\MemberEntityInterface $entity
   *   The Member entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(MemberEntityInterface $entity);

  /**
   * Unsets the language for all Member entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
