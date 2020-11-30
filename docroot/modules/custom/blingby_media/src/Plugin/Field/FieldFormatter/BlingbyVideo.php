<?php

namespace Drupal\blingby_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_blingby_video' formatter.
 *
 * @FieldFormatter(
 *   id = "field_blingby_video",
 *   label = @Translation("Blingby Video"),
 *   field_types = {
 *     "field_blingby_video"
 *   }
 * )
 */
class BlingbyVideo extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    return $element;
  }

}