<?php
namespace Drupal\blingby_media\Plugin\Tiles;

use Drupal\blingby_media\Plugin\TilesBase;

/**
 * Class Contact.
 *
 * @Tiles(
 *   id = "contact",
 *   label = "Contact",
 * )
 */
class Contact extends TilesBase {

  public function getForm($tile = false) {
    return [];
  }

  public function getLibrary() {
    return false;
  }

  public function hideCTA() {
    return true;
  }
}
