<?php

namespace Drupal\blingby_media\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_blingby_video' field type.
 *
 * @FieldType(
 *   id = "field_blingby_video",
 *   label = @Translation("Blingby Video"),
 *   module = "blingby_media",
 *   description = @Translation("Allow the upload or setup of differente videos."),
 *   default_widget = "field_blingby_video",
 *   default_formatter = "field_blingby_video"
 * )
 */

class BlingbyVideo extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'file' => array(
          'type' => 'text',
          'not null' => FALSE,
        ),
        'provider' => array(
          'type' => 'text',
          'not null' => FALSE,
        ),
        'provider_id' => array(
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
    $value = $this->get('file')->getValue();
    $pid = $this->get('provider_id')->getValue();
    $file_empty = ($value === NULL || $value === '');
    $provider_empty = ($pid === NULL || $pid === '');
    return $file_empty && $provider_empty;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['file'] = DataDefinition::create('string')
      ->setLabel(t('Uploaded File'));

    $properties['provider'] = DataDefinition::create('string')
      ->setLabel(t('Video Provider'));

    $properties['provider_id'] = DataDefinition::create('string')
      ->setLabel(t('Video Provider ID'));

    return $properties;
  }

}