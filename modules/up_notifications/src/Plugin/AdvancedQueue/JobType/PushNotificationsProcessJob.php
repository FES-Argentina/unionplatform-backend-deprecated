<?php

namespace Drupal\up_notifications\Plugin\AdvancedQueue\JobType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides the job type to process new notifications.
 *
 * @AdvancedQueueJobType(
 *   id = "up_notifications_process_notifications",
 *   label = @Translation("Process push notifications."),
 *   max_retries = 5,
 *   retry_delay = 60,
 * )
 */
class PushNotificationsProcessJob extends JobTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The factory for configuration objects.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a new PushNotificationsProcessJob object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $payload = $job->getPayload();

    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $notification */
    $notification = $this->entityTypeManager
      ->getStorage('push_notification')
      ->load($payload['notification_id']);
    if (!$notification) {
      return JobResult::failure('Notification not found.', $job->getNumRetries());
    }

    if ($notification->getState()->value != 'sending') {
      return JobResult::failure('Cannot send notification in this state.', $job->getNumRetries());
    }

    try {
      $devices_service = \Drupal::service('up_notifications.devices');
      $tokens = $devices_service->getTokens();

      $message_service = \Drupal::service('firebase.message');
      $message_service->setRecipients($tokens);
      $message_service->setData([
        'title' => $notification->get('field_notification_subject')->value,
        'body' => $notification->get('field_notification_body')->value,
      ]);
      $message_service->setOptions(['priority' => 'high']);
      $response = $message_service->send();

      // Store failed tokens.
      $failures = intval($response['failure']);
      if ($failures) {
        $failed_tokens = \Drupal::state()->get('up_notifications.failed_tokens', []);
        for ($i = $count = 0; $i < count($response['results']) && $count < $failures; ++$i) {
          if (!empty($response['results'][$i]['error'])) {
            $failed_tokens[$tokens[$i]] = $tokens[$i];
            $count++;
          }
        }
        \Drupal::state()->set('up_notifications.failed_tokens', $failed_tokens);
      }

      $notification->getState()->applyTransitionById('complete');
      $notification->save();
    }
    catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }

    return JobResult::success();
  }

}
