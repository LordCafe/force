<?php
namespace Drupal\blingby_media\Plugin\VideoProvider;

use Drupal\blingby_media\Plugin\VideoProviderBase;
use Drupal\Core\Render\Markup;
use Jwplayer\JwplatformAPI;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;

/**
 * Class Jwplayer.
 *
 * @VideoProvider(
 *   id = "jwplayer",
 *   label = "Upload File",
 *   upload = 1,
 * )
 */
class Jwplayer extends VideoProviderBase {

	public function getForm($item = false) {

		$default_value = [];
		$old_file = 0;
		if ($item && $item->file) {
			$default_value[] = $item->file;
			$old_file = $item->file;
		}

		$uploaded = !!$item->provider_id;

		$extra_info  = '<div class="video-status-info '.(($uploaded)? 'completed' : '' ).'">';
			$extra_info .= '<label class="text-danger">Your video has been uploaded but has not yet been processed.</label>';
			$extra_info .= '<label class="text-success">Your video has been uploaded</label>';
		$extra_info .= '</div>';

		return [
			'browse' => [
				'#markup' => Markup::create('<label class="browse-video btn btn-outline-light">Browse</label>')
			],
			'old_file' => [
				'#type' => 'hidden',
				'#value' => $old_file,
			],
			'file' => [
				'#type' => 'managed_file',
				'#title' => 'Video File',
				'#title_display' => 'hidden',
				'#default_value' => $default_value,
				'#upload_location' => 'public://videos/',
				'#upload_validators' => [
					'file_validate_extensions' => ['mp4 webm flv 3gp'],
				],
			],
			'description' => [
				'#markup' => Markup::create($extra_info)
			],
			'provider_id' => [
				'#type' => 'hidden',
				'#default_value' => $item->provider_id?: '',
			]
		];
	}

	public function getLibrary() {
		return 'blingby_media/provider.jwplayer';
	}

	public function updateValues(&$values) {
		if (isset($values['file'])) {
			if ($file = File::load($values['file'])) {
				$file->setPermanent();
				$file->save();
			}

			if ($values['file'] != $values['old_file']) {
				$values['provider_id'] = 0;
			}
		}
	}

	public function validate($provider_id) {
		$api = new JwplatformAPI('VMvUiCki', 'kdxASF5RT26ykpr6vBKCIgud');
		if ($provider_id) {
			$status_response = Json::encode($api->call('/videos/show', ['video_key' => $provider_id]));
			$decoded = Json::decode(trim($status_response));
			if ($decoded['video']['status'] != 'processing') {
				return $provider_id;
			}
		}
		
		return 0;
	}

	public function process_video($fid = false) {

		$file = File::load($fid);

		if (!$file) return FALSE;

		//@TODO change this, this is baaaaadd.
		$api = new JwplatformAPI('VMvUiCki', 'kdxASF5RT26ykpr6vBKCIgud');

		$target_file = \Drupal::service('file_system')->realpath($file->getFileUri());
		$params = [
			'title' => $file->getFilename()
		];

		// Create video metadata
		$create_response = Json::encode($api->call('/videos/create', $params));
		$decoded = Json::decode(trim($create_response), TRUE);
		$upload_link = $decoded['link'];
		$response = $api->upload($target_file, $upload_link);

		if (is_array($response)) {
			return $response['media']['key'];
		} else {
			return FALSE;
		}
	}
}








