<?php 

namespace Drupal\blingby_analytics;
use Drupal\Component\Uuid\Php;
use Drupal\blingby_analytics\Entity\Pixel;
use Symfony\Component\HttpFoundation\Request;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

class Service {


  public function generateUniqueCode($unique_code = 0, $video,  Request $request){

    $uuid = \Drupal::service('uuid');
    $pixelStorage = \Drupal::service('entity.manager')->getStorage('pixel');
    $pixel = FALSE;

    if ($unique_code) {
      $pixels = $pixelStorage->loadByProperties([
        'unique_code' => $unique_code
      ]);

      if (!empty($pixels)) {
        $pixel = reset($pixels);
      }
    }

    if (!$pixel) {
      $agent = $request->headers->get('User-Agent', '');
      $pixel = $pixelStorage->create([
        'unique_code' => $uuid->generate(),
        'agent' => $agent,
        'ip' => $request->getClientIp(),
      ]);

      $pixel->save();
      DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
      $dd = new DeviceDetector($agent);
      $dd->discardBotInformation();
      $dd->skipBotDetection();
      $dd->parse();

      $client = $dd->getClient();
      $os = $dd->getOs();

      $pixel->createMeta('OS', $os['name']);
      $pixel->createMeta('OS version', $os['version']);
      $pixel->createMeta('Client', $client['name']);
      $pixel->createMeta('Client version', $client['version']);
      $pixel->createMeta('Device', $dd->getDeviceName());
      $pixel->createMeta('Device Brand', $dd->getBrandName());
    }

    $nodeStorage = \Drupal::service('entity.manager')->getStorage('node');
    $video_node = $nodeStorage->load($video);
    $pixel->createEvent('page_load', $video, $video_node->label(), [], 0);

    $unique_code =$pixel->get('unique_code')->getString();
    $data = $pixel->getMetaData();

    return [$unique_code, $data];
  }
}