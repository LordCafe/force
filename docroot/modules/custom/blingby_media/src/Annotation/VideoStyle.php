<?php
/**
 * @file
 * Provides Video Style base class.
 */

namespace Drupal\blingby_media\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Video Style plugin item annotation object.
 *
 * @see \Drupal\blingby_media\Plugin\VideoStyleManager
 * @see plugin_api
 *
 * @Annotation
 */
class VideoStyle extends Plugin {
  public $id;
  public $label;
}