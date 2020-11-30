<?php
namespace Drupal\blingby_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

class TraceController extends ControllerBase {


  public function save($uc, Request $request){

    $pixelStorage = \Drupal::service('entity.manager')->getStorage('pixel');

    if ($uc) {
      $pixels = $pixelStorage->loadByProperties([
        'unique_code' => $uc,
      ]);

      if (!empty($pixels)) {
        $pixel = reset($pixels);
        $data = $request->request->all();

        if ($data['type'] == 'event') {
          
          $data = array_merge([
            'event' => '',
            'entity_id' => 0,
            'entity_title' => '',
            'entity_data' => [],
            'timestamp' => 0,
          ], $data);

          $pixel->createEvent($data['event'], $data['entity_id'], $data['entity_title'], serialize($data['entity_data']), $data['timestamp']);
        } else if ($data['type'] == 'metadata') {
          $pixel->createMeta($data['meta_name'], $data['meta_value']);
        } else if ($data['type'] == 'form') {

          $form = $data['form'];

          if (isset($form['fields'])  && !empty($form['fields'])) {
            foreach ($form['fields'] as $key => $value) {
              $pixel->createMeta($key, $value);
            }
          }

          $resultStorage = \Drupal::service('entity.manager')->getStorage('bFormResult');
          $formStorage = \Drupal::service('entity.manager')->getStorage('bForm');
          $bForm = $formStorage->load($form['id']);

          $metadata = $pixel->getMetaData();

          if ($bForm) {
            $bForm = $bForm->toArray();
            $answers = [];

            foreach($bForm['fields'] as $field) {
              if (isset($metadata[$field['key']])) {
                $answers[$field['key']] = $metadata[$field['key']];
              }
            }

            $result = [
              'fid' => $form['id'],
              'pid' => $pixel->id(),
              'answers' => Json::encode($answers),
            ];

            $resultStorage->create($result)->save();
          }
        }
      }
    }

    return new JsonResponse(['response' => TRUE ]);
  }

}