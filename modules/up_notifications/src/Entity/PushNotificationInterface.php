<?php

namespace Drupal\up_notifications\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Push notification entities.
 *
 * @ingroup up_notifications
 */
interface PushNotificationInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Push notification name.
   *
   * @return string
   *   Name of the Push notification.
   */
  public function getName();

  /**
   * Sets the Push notification name.
   *
   * @param string $name
   *   The Push notification name.
   *
   * @return \Drupal\up_notifications\Entity\PushNotificationInterface
   *   The called Push notification entity.
   */
  public function setName($name);

  /**
   * Gets the Push notification creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Push notification.
   */
  public function getCreatedTime();

  /**
   * Sets the Push notification creation timestamp.
   *
   * @param int $timestamp
   *   The Push notification creation timestamp.
   *
   * @return \Drupal\up_notifications\Entity\PushNotificationInterface
   *   The called Push notification entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the state of the notification.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The state of the notification.
   */
  public function getState();

  /**
   * Sets the state of the notification.
   *
   * @param string $state_id
   *   The new state ID.
   *
   * @return \Drupal\up_notifications\Entity\PushNotificationInterface
   *   The called push_notification.
   */
  public function setState($state_id);

}
