<?php

namespace Drupal\up_notifications\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\advancedqueue\Job;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for notification events.
 */
class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new ApplicationEventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('up_notifications');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'up_notifications.send.post_transition' => 'onNotificationSend',
      'up_notifications.complete.post_transition' => 'onNotificationComplete',
    ];
  }

  /**
   * Create the job for sending the notification.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event.
   */
  public function onNotificationSend(WorkflowTransitionEvent $event) {
    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $notification */
    $notification = $event->getEntity();
    $payload = [
      'notification_id' => $notification->id(),
    ];

    $job = Job::create('up_notifications_process_notifications', $payload);

    /** @var \Drupal\advancedqueue\Entity\QueueInterface $notifications_queue */
    $notifications_queue = $this->entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load('up_notifications_process');
    $notifications_queue->enqueueJob($job);

    $this->logger->info('Notification @id (@name) has been queued for sending.',
      [
        '@id' => $notification->id(),
        '@name' => $notification->getName(),
      ]
    );
  }

  /**
   * Called when the processing of a sent push_notification is finished.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event.
   */
  public function onNotificationComplete(WorkflowTransitionEvent $event) {
    /** @var \Drupal\up_notifications\Entity\PushNotificationInterface $notification */
    $notification = $event->getEntity();

    $this->logger->info('Notification @id (@name) has been processed.',
      [
        '@id' => $notification->id(),
        '@name' => $notification->getName(),
      ]
    );
  }

}
