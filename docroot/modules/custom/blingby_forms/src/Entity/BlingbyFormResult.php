<?php

namespace Drupal\blingby_forms\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Defines the Blingby Form Result entity.
 *
 * @ingroup bFormResult
 *
 * @ContentEntityType(
 *   id = "bFormResult",
 *   label = @Translation("Blingby Form Result"),
 *   base_table = "bformresult",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 * )
 */
class BlingbyFormResult extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the tile entity.'))
      ->setReadOnly(TRUE);

    $fields['fid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Form'))
      ->setDescription(t('The ID of form that this answer belongs to.'))
      ->setReadOnly(TRUE);

    $fields['pid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pixel'))
      ->setDescription(t('The ID of the pixel that this answer belongs to.'))
      ->setReadOnly(TRUE);

    $fields['answers'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Fields'))
      ->setDescription(t('Fields.'))
      ->setReadOnly(TRUE);

    return $fields;
  }
}