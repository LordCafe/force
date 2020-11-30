<?php
/**
 * @file
 * Provides Tiles base class.
 */

namespace Drupal\blingby_media\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Tiles plugin item annotation object.
 *
 * @see \Drupal\blingby_media\Plugin\TilesManager
 * @see plugin_api
 *
 * @Annotation
 */
class Tiles extends Plugin {
  public $id;
  public $label;
}