<?php

namespace Drupal\custom_entities\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Gymnase entity entities.
 *
 * @ingroup custom_entities
 */
interface GymnaseEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Gymnase entity name.
   *
   * @return string
   *   Name of the Gymnase entity.
   */
  public function getName();

  /**
   * Sets the Gymnase entity name.
   *
   * @param string $name
   *   The Gymnase entity name.
   *
   * @return \Drupal\custom_entities\Entity\GymnaseEntityInterface
   *   The called Gymnase entity entity.
   */
  public function setName($name);

  /**
   * Gets the Gymnase entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Gymnase entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Gymnase entity creation timestamp.
   *
   * @param int $timestamp
   *   The Gymnase entity creation timestamp.
   *
   * @return \Drupal\custom_entities\Entity\GymnaseEntityInterface
   *   The called Gymnase entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Gymnase entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Gymnase entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\custom_entities\Entity\GymnaseEntityInterface
   *   The called Gymnase entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Gymnase entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Gymnase entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\custom_entities\Entity\GymnaseEntityInterface
   *   The called Gymnase entity entity.
   */
  public function setRevisionUserId($uid);

}
