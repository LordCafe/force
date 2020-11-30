<?php

namespace Drupal\blingby_forms\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_blingby_form' formatter.
 *
 * @FieldFormatter(
 *   id = "field_blingby_form",
 *   label = @Translation("Blingby Form"),
 *   field_types = {
 *     "field_blingby_form"
 *   }
 * )
 */
class BlingbyForm extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    return $element;
  }

}