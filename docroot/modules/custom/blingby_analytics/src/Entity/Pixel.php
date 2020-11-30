<?php
namespace Drupal\blingby_analytics\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Database;

/**
 * Defines the Pixel entity.
 *
 * @ContentEntityType(
 *   id = "pixel",
 *   label = @Translation("Pixel"),
 *   base_table = "pixel",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 * )
 */

class Pixel extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the tile entity.'))
      ->setReadOnly(TRUE);

    $fields['unique_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Unique Code'))
      ->setDescription(t('Unique Code.'))
      ->setReadOnly(TRUE);

    $fields['agent'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Agent'))
      ->setDescription(t('Agent.'))
      ->setReadOnly(TRUE);


    $fields['ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('User Ip'))
      ->setDescription(t('User Ip.'))
      ->setReadOnly(TRUE);

    return $fields;
  }


  public function createMeta ($meta_name, $meta_value) {
    $db = Database::getConnection();
    $db->merge('pixel_metadata')
      ->key([
        'pid' => $this->id(),
        'meta_name' => $meta_name
      ])
      ->insertFields(array(
        'pid' => $this->id(),
        'meta_name' => $meta_name,
        'meta_value' => $meta_value,
      ))
      ->updateFields(array(
        'meta_value' => $meta_value,
      ))->execute();
  }


  public function createEvent($event, $entity_id, $entity_title = '', $entity_data = [], $timestamp) {
    $db = Database::getConnection();
    $db->insert('pixel_events')->fields([
      'pid' => $this->id(),
      'time' => time(),
      'event' => $event,
      'entity_id' => $entity_id,
      'entity_title' => $entity_title,
      'entity_data' => serialize($entity_data),
      'timestamp' => $timestamp,
    ])->execute();
  }


  public function getMetaData() {

    $data = [];

    $db = Database::getConnection();
    $select = $db->select('pixel_metadata', 'm');
    $select->fields('m',['meta_name', 'meta_value']);
    $select->condition('pid', $this->id());
    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    if (!empty($entries)) {
      foreach ($entries as $entry) {
        $data[$entry['meta_name']] = $entry['meta_value'];
      }
    }

    return $data;
  }

}