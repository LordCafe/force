<?php
namespace Drupal\blingby_media\Plugin\Tiles;

use Drupal\blingby_media\Plugin\TilesBase;

/**
 * Class Standard.
 *
 * @Tiles(
 *   id = "standard",
 *   label = "Standard",
 * )
 */
class Standard extends TilesBase {

	public function getForm($tile = false) {
		return [];
	}

	public function getLibrary() {
    return false;
	}

}