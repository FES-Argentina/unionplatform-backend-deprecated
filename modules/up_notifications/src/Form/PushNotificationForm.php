<?php

namespace Drupal\up_notifications\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Push notification edit forms.
 *
 * @ingroup up_notifications
 */
class PushNotificationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Push notification.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Push notification.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.push_notification.canonical', [
      'push_notification' => $entity->id(),
    ]);
  }

}
