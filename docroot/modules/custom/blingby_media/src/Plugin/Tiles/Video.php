<?php
namespace Drupal\blingby_media\Plugin\Tiles;

use Drupal\blingby_media\Plugin\TilesBase;

/**
 * Class Video.
 *
 * @Tiles(
 *   id = "video",
 *   label = "Video",
 * )
 */
class Video extends TilesBase {

  public function getForm($tile = false) {

    $currentUser = \Drupal::currentUser();
    $nodeStorage = \Drupal::entityManager()->getStorage('node');
    $videos = $nodeStorage->loadByProperties(['uid' => $currentUser->id(), 'type' => 'video']);
    $default = (isset($tile['params']) && isset($tile['params']['video']))  ? $tile['params']['video'] : '';

    $options = [];

    foreach ($videos as $v) {
      $options[$v->id()] = $v->label();
    }

    $form['video'] = [
      '#type' => 'select',
      '#title' => 'Video',
      '#default_value' => $default,
      '#empty_value' => '',
      '#empty_label' => 'Select',
      '#options' => $options
    ];

    return $form;
  }

  public function getLibrary() {
    return false;
  }

  public function hideCTA() {
    return true;
  }  

  public function processValues($values) {
    $values['params']['video'] = $values['video'];
    return $values;
  }

  public function preprocess($tile) {
    if ((isset($tile['params']) && isset($tile['params']['video']))) {
      if (!empty($tile['params']['video'])) {
        $nodeStorage = \Drupal::entityManager()->getStorage('node');
        if ($node = $nodeStorage->load($tile['params']['video'])) {

          $image = FALSE;
          if (!$node->get('field_image')->isEmpty()) {
            $image = file_create_url($node->field_image->entity->getFileUri());
          }

          $tile['video'] = [
            'id' => $node->id(),
            'image' => $image,
          ];
        }
      }
    }

    return $tile;
  }

}