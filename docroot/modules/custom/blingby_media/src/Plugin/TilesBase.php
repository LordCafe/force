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
abstract class TilesBase extends PluginBase implements TilesInterface, ContainerFactoryPluginInterface {
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

  abstract public function getForm($tile = false);

  abstract public function getLibrary();

  public function hideDescription() {
    return false;
  }

  public function hideCTA() {
    return false;
  }

  public function processValues($values = []) {
    return $values;
  }

  public function preprocess($tile) {
    return $tile;
  }

}