<?php
/**
 * @file
 * Provides VideoProvider base class.
 */

namespace Drupal\blingby_media\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Video Provider plugin item annotation object.
 *
 * @see \Drupal\blingby_media\Plugin\VideoProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class VideoProvider extends Plugin {
  public $id;
  public $label;
  public $upload;
}