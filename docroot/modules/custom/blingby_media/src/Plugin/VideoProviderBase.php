<?php

namespace Drupal\blingby_media\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;;

/**
 * Base class for Task plugin plugins.
 */
abstract class VideoProviderBase extends PluginBase implements VideoProviderInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static( $configuration, $plugin_id, $plugin_definition);
  }

  abstract public function getForm($item = false);

  abstract public function getLibrary();
}