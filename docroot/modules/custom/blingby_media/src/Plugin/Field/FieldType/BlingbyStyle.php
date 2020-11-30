<?php

namespace Drupal\blingby_media\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_blingby_style' field type.
 *
 * @FieldType(
 *   id = "field_blingby_style",
 *   label = @Translation("Blingby Style"),
 *   module = "blingby_media",
 *   description = @Translation("Allow the selection of different styles"),
 *   default_widget = "field_blingby_style",
 *   default_formatter = "field_blingby_style"
 * )
 */

class BlingbyStyle extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return ($value === NULL || $value === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Selected Style'));

    return $properties;
  }

}