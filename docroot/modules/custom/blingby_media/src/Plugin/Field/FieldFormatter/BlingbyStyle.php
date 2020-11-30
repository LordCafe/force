<?php

namespace Drupal\blingby_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_blingby_style' formatter.
 *
 * @FieldFormatter(
 *   id = "field_blingby_style",
 *   label = @Translation("Blingby Style"),
 *   field_types = {
 *     "field_blingby_style"
 *   }
 * )
 */
class BlingbyStyle extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    return $element;
  }

}