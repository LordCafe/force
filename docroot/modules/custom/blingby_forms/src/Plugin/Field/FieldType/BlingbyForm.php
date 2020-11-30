<?php

namespace Drupal\blingby_forms\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_blingby_form' field type.
 *
 * @FieldType(
 *   id = "field_blingby_form",
 *   label = @Translation("Blingby Form"),
 *   module = "blingby_forms",
 *   description = @Translation("Allow the selection of forms"),
 *   default_widget = "field_blingby_form",
 *   default_formatter = "field_blingby_form"
 * )
 */

class BlingbyForm extends FieldItemBase {

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
      ->setLabel(t('Form Entity'));

    return $properties;
  }

}