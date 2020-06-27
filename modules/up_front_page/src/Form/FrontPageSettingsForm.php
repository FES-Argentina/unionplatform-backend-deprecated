<?php

namespace Drupal\up_front_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FrontPageSettingsForm.
 */
class FrontPageSettingsForm extends ConfigFormBase {

  /**
   * The messenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'front_page_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'up_front_page.settings',
      'system.site',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('up_front_page.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable custom front page'),
      '#description' => $this->t('If enabled, use a simple simple page with the image and short text entered below as the front page.'),
      '#default_value' => $config->get('enabled'),
      '#attributes' => [
        'name' => 'enabled',
      ],
    ];

    $form['options'] = [
      '#type' => 'fieldset',
      '#title' => 'Options',
      '#tree' => TRUE,
    ];
    $logo = $config->get('logo');
    $form['options']['logo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logo'),
      '#description' => $this->t('An image to show in the front page.'),
      '#default_value' => $logo ? [$logo] : [],
      '#upload_location' => 'public://front',
      '#multiple' => FALSE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
    ];
    $form['options']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#description' => $this->t('If not empty the image will be rendered as a link to this URL. Please enter a full URL.'),
      '#default_value' => $config->get('url'),
    ];
    $form['options']['text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Short text'),
      '#description' => $this->t('Enter a short text to display under the image.'),
      '#default_value' => $config->get('text.value'),
      '#format' => $config->get('text.format'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $form_state->getValue('options');
    $settings['logo'] = reset($settings['logo']);
    if ($settings['logo']) {
      $this->setFilePermanentStatus($settings['logo']);
    }
    $settings['enabled'] = $form_state->getValue('enabled');
    $this->config('up_front_page.settings')
      ->setData($settings)
      ->save();

    $site['page']['front'] = $settings['enabled'] ? '/union-platform/front' : '/node';
    $this->config('system.site')
      ->merge($site)
      ->save();

    $this->messenger->addStatus($this->t('Front page set to @front.', ['@front' => $site['page']['front']]));

    parent::submitForm($form, $form_state);
  }

  /**
   * Marks the file with id $fid as permanent.
   */
  public function setFilePermanentStatus($fid) {
    $file = $this->entityTypeManager
      ->getStorage('file')
      ->load($fid);
    $file->setPermanent();
    $file->save();
  }

}
