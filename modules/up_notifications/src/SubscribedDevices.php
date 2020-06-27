<?php

namespace Drupal\up_notifications;

use Drupal\Core\Database\Connection;
use Drupal\Core\State\State;

/**
 * Service to access and manage subscribed devices.
 */
class SubscribedDevices {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\State\State $state
   *   The state service.
   */
  public function __construct(Connection $connection, State $state) {
    $this->db = $connection;
    $this->state = $state;
  }

  /**
   * Return subscribed device tokens.
   *
   * @return array
   *   An array of device tokens.
   */
  public function getTokens() {
    $failed_tokens = $this->state->get('up_notifications.failed_tokens', []);
    if ($failed_tokens) {
      $query = $this->db->query('SELECT field_device_tokens_value FROM user__field_device_tokens WHERE field_device_tokens_value NOT IN (:tokens[])', [
        ':tokens[]' => $failed_tokens,
      ]);
    }
    else {
      $query = $this->db->query('SELECT field_device_tokens_value FROM user__field_device_tokens');
    }

    // TODO: Implement some sort of pagination here.
    return $query->fetchCol();
  }

}
