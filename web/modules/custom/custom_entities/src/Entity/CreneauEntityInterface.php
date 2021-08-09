<?php

namespace Drupal\custom_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Creneau entity entities.
 *
 * @ingroup custom_entities
 */
interface CreneauEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Creneau entity name.
   *
   * @return string
   *   Name of the Creneau entity.
   */
  public function getName();

  /**
   * Sets the Creneau entity name.
   *
   * @param string $name
   *   The Creneau entity name.
   *
   * @return \Drupal\custom_entities\Entity\CreneauEntityInterface
   *   The called Creneau entity entity.
   */
  public function setName($name);

  /**
   * Gets the Creneau entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Creneau entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Creneau entity creation timestamp.
   *
   * @param int $timestamp
   *   The Creneau entity creation timestamp.
   *
   * @return \Drupal\custom_entities\Entity\CreneauEntityInterface
   *   The called Creneau entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Creneau entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Creneau entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\custom_entities\Entity\CreneauEntityInterface
   *   The called Creneau entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Creneau entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Creneau entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\custom_entities\Entity\CreneauEntityInterface
   *   The called Creneau entity entity.
   */
  public function setRevisionUserId($uid);

}
