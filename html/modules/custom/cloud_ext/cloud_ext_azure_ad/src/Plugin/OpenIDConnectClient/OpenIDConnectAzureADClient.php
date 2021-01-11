<?php

namespace Drupal\cloud_ext_azure_ad\Plugin\OpenIDConnectClient;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Azure AD OpenID Connect client.
 *
 * Used to login with Azure AD.
 *
 * @OpenIDConnectClient(
 *   id = "azure_ad",
 *   label = @Translation("Azure AD")
 * )
 */
class OpenIDConnectAzureADClient extends OpenIDConnectClientBase {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $request_stack,
      $http_client,
      $logger_factory
    );

  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('http_client'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'tenant_id' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['tenant_id'] = [
      '#title' => $this->t('Tenant ID'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['tenant_id'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints() {
    $tenant_id = rawurlencode($this->configuration['tenant_id']);

    return [
      'authorization' => "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize",
      'token' => "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token",
      'userinfo' => 'https://graph.microsoft.com/v1.0/me',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveUserInfo($access_token) {
    $user_info = parent::retrieveUserInfo($access_token);
    if (!$user_info) {
      return FALSE;
    }

    if (!isset($user_info['preferred_username']) && isset($user_info['userPrincipalName'])) {
      $user_info['preferred_username'] = $user_info['userPrincipalName'];
    }

    if (!isset($user_info['email']) && isset($user_info['mail']) && !empty($user_info['mail'])) {
      $user_info['email'] = $user_info['mail'];
    }
    elseif (!isset($user_info['email']) && isset($user_info['userPrincipalName'])) {
      $user_info['email'] = $user_info['userPrincipalName'];
    }

    return $user_info;
  }

}
