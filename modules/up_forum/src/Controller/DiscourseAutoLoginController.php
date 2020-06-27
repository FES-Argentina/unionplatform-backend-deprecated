<?php

namespace Drupal\up_forum\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DiscourseAutoLoginController.
 */
class DiscourseAutoLoginController extends ControllerBase {

  /**
   * Get credentials from the embedding webview.
   */
  public function getCredentials() {
    $discourse_url = $this->config('up_forum.settings')->get('discourse_url');
    if (!empty($discourse_url)) {
      $settings = [
        'discourseURL' => $discourse_url,
      ];

      $build['#attached']['library'][] = 'up_forum/webview-credentials';
      $build['#attached']['drupalSettings']['upForum']['webviewCredentials'] = $settings;
      $build['wait'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Please wait, redirecting...'),
      ];
    }
    else {
      $build['unavailable'] = [
        '#type' => 'markup',
        '#markup' => $this->t('The forum is not available right now...'),
      ];
    }

    return $build;
  }

}
