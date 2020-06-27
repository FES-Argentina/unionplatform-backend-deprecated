<?php

namespace Drupal\up_notifications;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Push notification entities.
 *
 * @ingroup up_notifications
 */
class PushNotificationListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new PushNotificationListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    $header['changed'] = $this->t('Last saved');
    $header['subject'] = $this->t('Subject');
    $header['body'] = $this->t('Body');
    $header['state'] = $this->t('State');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\up_notifications\Entity\PushNotification */
    $row['id'] = $entity->id();
    $row['name'] = $entity->label();
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');
    $row['subject'] = $entity->get('field_notification_subject')->value;
    $row['body'] = $entity->get('field_notification_body')->value;
    $row['state'] = $entity->getState()->getLabel();

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this
      ->getStorage()
      ->getQuery()
      ->sort($this->entityType->getKey('id'), 'DESC');

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query
        ->pager($this->limit);
    }
    return $query
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('send_notification') && $entity->hasLinkTemplate('send-notification-form')) {
      $operations['send'] = [
        'title' => $this->t('Send'),
        'weight' => 0,
        'url' => $entity->urlInfo('send-notification-form'),
      ];
    }

    return $operations;
  }

}
