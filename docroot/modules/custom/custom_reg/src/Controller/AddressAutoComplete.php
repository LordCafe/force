<?php

namespace Drupal\custom_reg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Uuid\Php;
use Drupal\node\Entity\Node;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class AddressAutoComplete extends ControllerBase {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStroage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function addressList(Request $request) {
    $results = [];
    $input = $request->query->get('q');

    $squadronNumber = $request->query->get('squadronNumber');

    // Get the typed string from the URL, if it exists.
    if (!$input) {
      return new JsonResponse($results);
    }


    $input = Xss::filter($input);
    $squadronNumber = Xss::filter($squadronNumber);


    $location = '';
    $radius = '';
    $country = '';
    if(!empty($squadronNumber)) {
      $database = \Drupal::database();
      $select = $database->select('node_field_data', 'n');
      $select->addField('n', 'nid');
      $res = $select->condition('type', 'squadron')
        ->condition('title', $squadronNumber, '=')
        ->execute()->fetchAllKeyed();
      $nid = array_keys($res)[0];
      if($nid) {
        $node = Node::load($nid);
        $location = $node->get('field_latlong')->getString();
        $radius = $node->get('field_radius')->getString();
        $country = $node->get('field_country')->getString();
      }
    }

    $uuid = Php::generate();
    $key = 'AIzaSyB1-0218Otm0HfAoDIK-QPB0ghhmV3b5tQ';
    $uri = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . $input .'&key=' . $key . '&sessiontoken=' . $uuid;

    if($location) {
       $uri = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' .
       $input .'&key=' . $key . '&location='. $location . '&radius=' . $radius . '&strictbounds&components=country:' . $country . '&sessiontoken=' . $uuid;
   }

    $client = \Drupal::httpClient();
    $req = $client->request('GET', $uri, []);
    $response = $req->getBody()->getContents();
    $resBody = json_decode($response, true)['predictions'];
    $res = [];
    foreach ($resBody as $key => $value) {
      $res[] = ['label' => $value['description'], 'value' => $value['description']];
    }
    return new JsonResponse($res);
  }
}
