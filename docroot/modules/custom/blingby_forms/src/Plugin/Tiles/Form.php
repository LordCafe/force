<?php
namespace Drupal\blingby_forms\Plugin\Tiles;

use Drupal\blingby_media\Plugin\TilesBase;

/**
 * Class Form.
 *
 * @Tiles(
 *   id = "form",
 *   label = "Form",
 * )
 */
class Form extends TilesBase {

  public function getForm($tile = false) {

    $currentUser = \Drupal::currentUser();
    $formStorage = \Drupal::entityManager()->getStorage('bForm');
    $forms = $formStorage->loadByProperties(['assigned' => 'tile']);
    $default = (isset($tile['params']) && isset($tile['params']['form']))  ? $tile['params']['form'] : '';

    $options = [];

    foreach ($forms as $f) {
      $options[$f->id()] = $f->get('title')->getString();
    }


    $form['form'] = [
      '#type' => 'select',
      '#title' => 'Form',
      '#default_value' => $default,
      '#empty_value' => '',
      '#empty_label' => 'Select',
      '#options' => $options
    ];

    return $form;
  }


  public function hideDescription() {
    return true;
  }

  public function hideCTA() {
    return true;
  }

  public function getLibrary() {
    return false;
  }

  public function processValues($values) {
    $values['params']['form'] = $values['form'];
    return $values;
  }

  public function preprocess($tile) {

    if ((isset($tile['params']) && isset($tile['params']['form']))) {
      if (!empty($tile['params']['form'])) {
        $formStorage = \Drupal::entityManager()->getStorage('bForm');
        if ($bform = $formStorage->load($tile['params']['form'])) {
          $tile['form'] = $bform->toArray(true);
        }
      }
    }

    return $tile;
  }

}