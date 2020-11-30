<?php
namespace Drupal\blingby_airforce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Asset\AttachedAssets;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Serialization\Json;

class PageController extends ControllerBase {

  public function index() {

    $resultStorage = $this->entityManager()->getStorage('bFormResult');

    // Load First Form
    $results = $resultStorage->loadByProperties(['fid' => 1]);
    $others = $resultStorage->loadByProperties(['fid' => 2]);

    $answers = [];

    foreach ($others as $other) {
      $data = $other->get('answers')->getString();
      $data = Json::decode($data);
      $answers[$other->get('pid')->getString()] = $data;
    }

    $items = [];
    $labels = [
      'height_weight' =>  'Weight',
      'marijuana' =>  'Marijuana',
      'tattoos' =>  'Tattoos',
      'surgery' =>  'Surgery',
      'arrested' =>  'Arrested',
      'bankruptcy' =>  'Bankruptcy',
    ];


    foreach ($results as $result) {
      $id = $result->get('pid')->getString();

      $data = $result->get('answers')->getString();
      $data = Json::decode($data);

      if (isset($answers[$id])) {
        $data  = array_merge($data, $answers[$id]);
      }

      $red = 0;
      $r_details = [];
      $yellow = 0;
      $y_details = [];

      foreach ($data as $key => $value) {
        $color = $this->validate($key, $value);
        if ($color == 'red') {
          $red++;
          $r_details[] = $labels[$key];
        } elseif ($color == 'yellow') {
          $yellow++;
          $y_details[] = $labels[$key];
        }
      }


      $green = 6 - ($red + $yellow);

      $class = '';

      if ($yellow > 0) {
        $class .= ' has-yellow';
      }

      if ($red > 0) {
        $class .= ' has-red';
      }

      $items[] = [
        'id' => $id,
        'idf' => str_pad($id, 4, "0", STR_PAD_LEFT),
        'green' => number_format($green/6*100,2, '.', ','),
        'red' => number_format($red/6*100,2, '.', ','),
        'yellow' => number_format($yellow/6*100,2, '.', ','),
        'y_details' => implode(', ', $y_details),
        'r_details' => implode(', ', $r_details),
        'class' => $class
      ];
    }

    $build = [
      '#theme' => 'blingby_airforce_dashboard',
      '#items' => $items,
      '#attached' => [
        'library' => ['blingby_airforce/dashboard']
      ]
    ];

    return $build;
  }



  public function single($pid) {

    $resultStorage = $this->entityManager()->getStorage('bFormResult');
    $formStorage = $this->entityManager()->getStorage('bForm');

    $item = [
      'id' => $pid,
      'idf' => str_pad($pid, 4, "0", STR_PAD_LEFT),
      'forms' => [
        [
          'id' => 1,
          'label' => 'Form #1',
          'check' => ['height_weight']
        ],
        [
          'id' => 2,
          'label' => 'Form #2',
          'check' => ['marijuana', 'tattoos', 'surgery', 'arrested', 'bankruptcy']
        ],
      ]
    ];

    foreach ($item['forms'] as &$f) {

      $result = $resultStorage->loadByProperties(['fid' => $f['id'], 'pid' => $pid]);
      $result = reset($result);

      if ($result) {
        $answers = $result->get('answers')->getString();
        $answers = Json::decode($answers);

        $formEntity = $formStorage->load($f['id']);

        $questions = $formEntity->get('data')->getString();
        $questions = Json::decode($questions);

        foreach ($questions as $value) {
          $key = $value['key'];
          $data = [
            'text' => $value['label'],
            'value' => $answers[$key]?: '-'
          ];

          if (in_array($key, $f['check'])) {
            $data['color'] = $this->validate($key, $answers[$key]);
            $f['colored'][] = $data;
          } else {
            $f['simple'][] = $data; 
          }
        }
      }
    }

    $build = [
      '#theme' => 'blingby_airforce_single',
      '#item' => $item,
      '#attached' => [
        'library' => ['blingby_airforce/dashboard']
      ]
    ];

    return $build;
  }


  public function validate($key, $value) {

    switch ($key) {

      case 'height_weight':
        $wh = [
          58 => [91, 131],
          59 => [94, 136],
          60 => [97, 141],
          61 => [100, 145],
          62 => [104, 150],
          63 => [107, 155],
          64 => [110, 150],
          65 => [114, 165],
          66 => [117, 170],
          67 => [121, 175],
          68 => [125, 180],
          69 => [128, 186],
          70 => [132, 191],
          71 => [136, 197],
          72 => [140, 202],
          73 => [144, 208],
          74 => [148, 214],
          75 => [152, 220],
          76 => [156, 225],
          77 => [160, 231],
          78 => [164, 237],
          79 => [168, 244],
          80 => [173, 250],
        ];

        $value = explode('/', $value);

        $value[0] = intval($value[0]);
        $value[1] = intval($value[1]);

        if (isset($wh[$value[0]])) {
          $weight = $wh[$value[0]];

          if ( $weight[0] <=  $value[1] && $value[1] <= $weight[1]) {
            // Do nothing
          } else if ($value[1] <= ($weight[1]+11)) {
            return 'yellow';
          } else {
            return 'red';
          }
        } else {
          // Do something?
        }
        break;

      case 'marijuana':
        if (!in_array($value, ['More than 45 Days Ago', 'I have never used marijuana or THC products'])) {
          return 'red';
        }
        break;

      case 'tattoos':
        if ($value == 'yes') {
          return 'red';
        }
        break;

      case 'surgery':
        if ($value == 'yes') {
          return 'yellow';
        }
        break;

      case 'arrested':
        if ($value == 'yes') {
          return 'yellow';
        }
        break;

      case 'bankruptcy':
        if ($value == 'yes') {
          return 'yellow';
        }
        break;          
    }

    return 'green';
  }
}









