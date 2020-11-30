<?php
namespace Drupal\blingby_media\Plugin\VideoStyle;

use Drupal\blingby_media\Plugin\VideoStyleBase;

/**
 * Class Black.
 *
 * @VideoStyle(
 *   id = "black",
 *   label = "Black",
 * )
 */
class Black extends VideoStyleBase {

  public function getImage(){
    $path = drupal_get_path('module', 'blingby_media');
    $path .=  '/assets/img/style-black.png';
    return $path;
  }

  public function getVideoTemplate() {
    return $this->loadFile('templates/styles/black/video.html');
  }

  public function getTileTemplate() {
   return $this->loadFile('templates/styles/black/tile.html');
  }

  public function getLibrary() {
    return 'blingby_media/styles.black';
  }

  public function loadFile($path) {
    $path = \Drupal::root() . DIRECTORY_SEPARATOR . drupal_get_path('module', 'blingby_media') . DIRECTORY_SEPARATOR . $path;
    return file_get_contents($path);
  }



}