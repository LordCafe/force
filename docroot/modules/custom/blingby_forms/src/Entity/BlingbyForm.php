<?php

namespace Drupal\blingby_forms\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines the Blingby Form entity.
 *
 * @ingroup bForm
 *
 * @ContentEntityType(
 *   id = "bForm",
 *   label = @Translation("Blingby Form"),
 *   base_table = "bform",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 * )
 */
class BlingbyForm extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the tile entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('User'))
      ->setDescription(t('The ID of user that created the form.'))
      ->setReadOnly(TRUE);

    $fields['assigned'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('Type.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Title.'))
      ->setReadOnly(TRUE);

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Thankyou Message'))
      ->setDescription(t('Thankyou Message.'))
      ->setReadOnly(TRUE);

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Fields'))
      ->setDescription(t('Fields.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  public function toArray($iframe = false) {

    $fields = $this->get('data')->getString();
    $fields = Json::decode($fields);

    if ($iframe) {
      foreach ($fields as &$field) {
        if (!empty($field['options'])) {
          $field['options'] = array_filter(explode("\n", trim($field['options'])));
          $field['options'] = array_map('trim', $field['options']);
        }
      }
    }

    return [
      'id' => $this->id(),
      'assigned' => $this->get('assigned')->getString(),
      'title' => $this->get('title')->getString(),
      'message' => $this->get('message')->getString(),
      'fields' => $fields,
    ];
  }

}