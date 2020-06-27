<?php

namespace Drupal\up_front_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FrontPageController.
 */
class FrontPageController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->imageFactory = $container->get('image.factory');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Builds the image element from settings.
   *
   * @return array|null
   *   A render array for the front page image (and optional link).
   */
  protected function image() {
    $element = NULL;
    $fid = $this->config('up_front_page.settings')->get('logo');

    if ($fid) {
      if ($file = $this->entityTypeManager->getStorage('file')->load($fid)) {
        $file_uri = $file->getFileUri();
        if ($this->imageFactory->get($file_uri)->isValid()) {
          $element = [
            '#theme' => 'image',
            '#uri' => $file_uri,
          ];

          $url = $this->config('up_front_page.settings')->get('url');
          if ($url) {
            $element = [
              '#type' => 'link',
              '#title' => $element,
              '#url' => Url::fromUri($url),
            ];
          }
        }
      }
      else {
        $this->getLogger('up_front_page')
          ->error('The file with id @fid could not be loaded.', ['@fid' => $fid]);
      }
    }

    return $element;
  }

  /**
   * Builds the text element from settings.
   *
   * @return array|null
   *   A render array for the front page text.
   */
  protected function text() {
    $element = NULL;
    $text = $this->config('up_front_page.settings')->get('text');

    if (!empty(trim($text['value']))) {
      $element = [
        '#type' => 'processed_text',
        '#text' => $text['value'],
        '#format' => $text['format'],
      ];
    }

    return $element;
  }

  /**
   * Front page.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function frontPage() {
    return [
      '#theme' => 'up_front_page',
      '#image' => $this->image(),
      '#text' => $this->text(),
      '#name' => $this->config('system.site')->get('name'),
      '#title' => '',
      '#attached' => [
        'library' => ['up_front_page/front'],
      ],
    ];
  }

}
