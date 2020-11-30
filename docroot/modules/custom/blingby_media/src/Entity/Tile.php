<?php

namespace Drupal\blingby_media\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Defines the Tile entity.
 *
 * @ingroup tile
 *
 * @ContentEntityType(
 *   id = "tile",
 *   label = @Translation("Tile"),
 *   base_table = "tile",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 * )
 */
class Tile extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the tile entity.'))
      ->setReadOnly(TRUE);

    $fields['vid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Video'))
      ->setDescription(t('The NID of video where this tile belongs.'))
      ->setReadOnly(TRUE);

    $fields['plugin'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plugin'))
      ->setDescription(t('Plugin.'))
      ->setReadOnly(TRUE);


    $fields['time'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Time'))
      ->setDescription(t('Time.'))
      ->setReadOnly(TRUE);

    $fields['fid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Image'))
      ->setDescription(t('The ID of the image file.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Title.'))
      ->setReadOnly(TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('Description.'))
      ->setReadOnly(TRUE);

    $fields['cta'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CTA'))
      ->setDescription(t('CTA.'))
      ->setReadOnly(TRUE);

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('URL'))
      ->setDescription(t('URL.'))
      ->setReadOnly(TRUE);

    $fields['params'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Path'))
      ->setDescription(t('Path.'))
      ->setReadOnly(TRUE);


    return $fields;
  }

  public function toArray() {

    $params = unserialize($this->get('params')->getString());
    $image = FALSE;
    $fid = FALSE;
    $original = FALSE;

    if (!$this->get('fid')->isEmpty()) {
      $fid = (int)$this->get('fid')->getString();
      $file = \Drupal::entityManager()->getStorage('file')->load($this->get('fid')->getString());
      if ($file) {
        $original = $file->url();
        $image = ImageStyle::load('tile_thumb')->buildUrl($file->getFileUri());
      }
    }

    $timef = '';
    if ($time = $this->get('time')->getString()) {
      $hours   = floor($time/3600);
      $minutes = floor(($time - ($hours * 3600)) / 60);
      $seconds = $time - ($hours * 3600) - ($minutes * 60);
      $result  = ($hours < 10 ? "0" . $hours : $hours);
      $result .= ":" . ($minutes < 10 ? "0" . $minutes : $minutes);
      $result .= ":" . ($seconds  < 10 ? "0" . $seconds : $seconds);
      $timef =  $result.':00';
    }


    return [
      'id' => $this->id(),
      'plugin' => $this->get('plugin')->getString(),
      'time' => (int)$this->get('time')->getString(),
      'title' => $this->get('title')->getString(),
      'description' => $this->get('description')->getString(),
      'cta' => $this->get('cta')->getString(),
      'url' => $this->get('url')->getString(),
      'timef' => $timef,
      'fid' => $fid,
      'image' => $image,
      'original' => $original,
      'params' => $params
    ];
  }
}