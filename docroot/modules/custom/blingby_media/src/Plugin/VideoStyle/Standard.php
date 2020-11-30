<?php
namespace Drupal\blingby_media\Plugin\VideoStyle;

use Drupal\blingby_media\Plugin\VideoStyleBase;

/**
 * Class Standard.
 *
 * @VideoStyle(
 *   id = "standard",
 *   label = "Standard",
 * )
 */
class Standard extends VideoStyleBase {

  public function getImage(){
    $path = drupal_get_path('module', 'blingby_media');
    $path .=  '/assets/img/style-standard.png';
    return $path;
  }

  public function getVideoTemplate() {
    return $this->loadFile('templates/styles/standard/video.html');
  }

  public function getTileTemplate() {
   return $this->loadFile('templates/styles/standard/tile.html');
  }

  public function getLibrary() {
    return 'blingby_media/styles.standard';
  }

  public function loadFile($path) {
    $path = \Drupal::root() . DIRECTORY_SEPARATOR . drupal_get_path('module', 'blingby_media') . DIRECTORY_SEPARATOR . $path;
    return file_get_contents($path);
  }


}