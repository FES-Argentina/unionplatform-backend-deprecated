<?php

namespace Drupal\up_notifications;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Push notification entity.
 *
 * @see \Drupal\up_notifications\Entity\PushNotification.
 */
class PushNotificationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view push notification entities');

      case 'update':
        if ($entity->getState()->value == 'draft') {
          return AccessResult::allowedIfHasPermission($account, 'edit unpublished push notification entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'edit published push notification entities');

      case 'delete':
        if ($entity->getState()->value == 'draft') {
          return AccessResult::allowedIfHasPermission($account, 'delete unpublished push notification entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'delete published push notification entities');

      case 'send_notification':
        $transitions = $entity->getState()->getTransitions();
        return AccessResult::allowedIf(isset($transitions['send']));
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add push notification entities');
  }

}
