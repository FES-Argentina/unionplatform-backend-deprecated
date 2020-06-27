<?php

namespace Drupal\up_notifications\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Entity\EntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource for device tokens.
 *
 * @RestResource(
 *   id = "device_token_resource",
 *   label = @Translation("Device token"),
 *   uri_paths = {
 *     "create" = "/user/{user}/device_token"
 *   }
 * )
 */
class DeviceTokenResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new DeviceTokenResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('up_notifications'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);

    $requirements = $route->getRequirements();
    $requirements['user'] = '\d+';
    $route->setRequirements($requirements);

    $parameters = $route->getOption('parameters') ?: [];
    $parameters['user']['type'] = 'entity:user';
    $route->setOption('parameters', $parameters);

    return $route;
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $user
   *   The user.
   * @param string $token
   *   The device token.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(EntityInterface $user, string $token) {
    // TODO: Validate token by attempting to send a message with dry_run=true?
    // see: https://www.drupal.org/project/firebase/issues/3088291
    $token = trim($token);
    if (!$this->currentUser->hasPermission('add own device token') || $this->currentUser->id() != $user->id()) {
      throw new AccessDeniedHttpException();
    }

    try {
      $tokens = $user->field_device_tokens->getValue();
      if (in_array($token, array_column($tokens, 'value'))) {
        return new ModifiedResourceResponse('', 200);
      }

      $tokens[] = ['value' => $token];
      $user->set('field_device_tokens', $tokens);
      $user->save();

      $this->logger->info('New device token added.');
      return new ModifiedResourceResponse($token, 201);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse(NULL, 500);
    }
  }

}
