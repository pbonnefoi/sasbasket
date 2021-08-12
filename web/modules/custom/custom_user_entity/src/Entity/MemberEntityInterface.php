<?php

namespace Drupal\custom_user_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Member entity entities.
 *
 * @ingroup custom_user_entity
 */
interface MemberEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Member entity name.
   *
   * @return string
   *   Name of the Member entity.
   */
  public function getName();

  /**
   * Sets the Member entity name.
   *
   * @param string $name
   *   The Member entity name.
   *
   * @return \Drupal\custom_user_entity\Entity\MemberEntityInterface
   *   The called Member entity entity.
   */
  public function setName($name);

  /**
   * Gets the Member entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Member entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Member entity creation timestamp.
   *
   * @param int $timestamp
   *   The Member entity creation timestamp.
   *
   * @return \Drupal\custom_user_entity\Entity\MemberEntityInterface
   *   The called Member entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Member entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Member entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\custom_user_entity\Entity\MemberEntityInterface
   *   The called Member entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Member entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Member entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\custom_user_entity\Entity\MemberEntityInterface
   *   The called Member entity entity.
   */
  public function setRevisionUserId($uid);

}
