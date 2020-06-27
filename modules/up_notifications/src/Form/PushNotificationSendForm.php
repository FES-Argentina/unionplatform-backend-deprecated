<?php

namespace Drupal\up_notifications\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to send a notification.
 *
 * @ingroup up_notifications
 */
class PushNotificationSendForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $notification */
    $notification = $this->getEntity();

    return $this->t('Are you sure you want to send the notification @label?', [
      '@label' => $notification->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure you want to send this notification to all subscribed devices? If you confirm the notification will be processed and sent to all subscribed devices in the next minutes. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->getEntity()->urlInfo('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Send notification');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $notification */
    $notification = $this->getEntity();

    $transitions = $notification->getState()->getTransitions();
    if (isset($transitions['send'])) {
      $notification->getState()
        ->applyTransition($transitions['send']);

      $notification->save();

      $this->messenger()
        ->addStatus(
          $this->t('Notification %title has been queued.', [
            '%title' => $notification->label(),
          ])
        );
    }
    else {
      $this->messenger()
        ->addError(
          $this->t('Notification cannot be sent from current state..')
        );
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
