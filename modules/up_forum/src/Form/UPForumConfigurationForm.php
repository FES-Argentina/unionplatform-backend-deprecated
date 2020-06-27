<?php

namespace Drupal\up_forum\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the form for the forum settings.
 */
class UPForumConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'up_forum_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'up_forum.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('up_forum.settings');

    $form['discourse_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Discourse URL'),
      '#description' => $this->t('Enter the Discourse URL used for Single Sign On, i.e. https://www.example.com/session/sso.'),
      '#required' => TRUE,
      '#default_value' => $config->get('discourse_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('up_forum.settings')
      ->set('discourse_url', trim($form_state->getValue('discourse_url')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
