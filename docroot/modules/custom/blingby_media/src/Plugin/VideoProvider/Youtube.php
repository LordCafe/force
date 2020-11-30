<?php
namespace Drupal\blingby_media\Plugin\VideoProvider;

use Drupal\blingby_media\Plugin\VideoProviderBase;

/**
 * Class Youtube.
 *
 * @VideoProvider(
 *   id = "youtube",
 *   label = "Youtube",
 *   upload = 0,
 * )
 */
class Youtube extends VideoProviderBase {

	public function getForm($item = false) {

		$default_value = '';
		if ($item) {
			$default_value = isset($item->provider_id) ? $item->provider_id : '';
		}

		return [
			'provider_id' => [
				'#type' => 'textfield',
				'#title' => 'YouTube ID',
				'#default_value' => $default_value,
				'#required' => true,
			]
		];
	}

	public function updateValues(&$values) {
		$values['file'] = 0;

		$url = $values['provider_id'];

		if (filter_var($url, FILTER_VALIDATE_URL)) {
			parse_str(parse_url( $url, PHP_URL_QUERY ), $query );

			if (isset($query['v'])) {
				$values['provider_id'] = $query['v'];
			} elseif(strpos($url, 'youtu.be') !== FALSE) {
				$url = explode('youtu.be/', $url);
				$values['provider_id'] = end($url);
			}
		}



	}

	public function validate($provider_id) {
		return $provider_id;
	}


	public function getLibrary() {
    return 'blingby_media/provider.youtube';
	}
}