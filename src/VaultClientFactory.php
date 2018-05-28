<?php

namespace Drupal\vault;

use Drupal\Core\Config\ConfigFactory;
use VaultTransports\Guzzle6Transport;
use Vault\Exceptions;

/**
 * Factory class for Vault client.
 *
 * @package Drupal\vault
 */
class VaultClientFactory {

  /**
   * Creates an Vault Client instance.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory.
   *
   * @return \Drupal\vault\VaultClient
   *   The client.
   */
  public static function createInstance(ConfigFactory $configFactory) {
    $settings = $configFactory->get('vault.settings');

    // @todo move transports into plugins.
    $transport = new Guzzle6Transport(['base_url' => $settings->get('base_url')]);
    $logger = \Drupal::service('logger.channel.vault');
    $client = new VaultClient($transport, $logger);

    // Load up auth strategy.
    $authStrategy = static::loadAuthenticationStrategy($configFactory);
    $authenticated = $client->setAuthenticationStrategy($authStrategy)->authenticate();
    if (!$authenticated) {
      throw new Exceptions\AuthenticationException("Failed to authenticate");
    }

    return $client;
  }

  /**
   * Load the configured authentication strategy.
   */
  public static function loadAuthenticationStrategy(ConfigFactory $configFactory) {
    $vault_config = $configFactory->get('vault.settings');
    $manager = self::loadPluginManager('auth');
    $plugin_name = $vault_config->get('plugin_auth');
    if (empty($plugin_name)) {
      throw new \Exception("No auth plugin configured");
    }
    $plugin = $manager->createInstance($plugin_name, []);
    return $plugin->getAuthenticationStrategy();
  }

  public static function loadPluginManager($plugin_type) {
    $service_id = sprintf('plugin.manager.vault_%s', $plugin_type);
    return \Drupal::service($service_id);
  }

}
