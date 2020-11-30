<?php  
/**
 * @file
 * Contains \Drupal\blingby_media\Plugin\QueueWorker\ProcessVideoWorker.
 */

namespace Drupal\blingby_media\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Processes tasks for example module.
 *
 * @QueueWorker(
 *   id = "process_video",
 *   title = @Translation("Example: Process video"),
 *   cron = {"time" = 90}
 * )
 */
class ProcessVideoWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    $node = \Drupal::entityManager()->getStorage('node')->load($item->nid);

    $video_data = $node->get('field_video')->getValue();

    if (!empty($video_data[0]['file'])) {
      $plugin_manager = \Drupal::service('plugin.manager.blingby_video_provider');
      $plugin = $plugin_manager->createInstance($video_data[0]['provider']);
      $provider_id = $plugin->process_video($video_data[0]['file']);

      if ($provider_id) {
        $video_data[0]['provider_id'] = $provider_id;
        $node->set('field_video', $video_data[0]);
        $node->save();
      } else {
        \Drupal::logger('blingby_media')->error('There is a problem with the upload of video @nid',['@nid' => $item->nid]);
      }
    }
  }

}