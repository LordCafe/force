<?php

namespace Drupal\blingby_image_cropper\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'blingby_image' widget.
 *
 * @FieldWidget(
 *   id = "blingby_image",
 *   module = "blingby_image_cropper",
 *   label = @Translation("Blingby Image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */

class BlingbyImage extends WidgetBase {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $has_image = 0;
    $file_url = '';
    if (!empty($items[$delta]->target_id)) {
      $file = \Drupal::entityManager()->getStorage('file')->load($items[$delta]->target_id);
      $file_url = file_create_url($file->getFileUri());
      $has_image = $items[$delta]->target_id;
    }

    $element['has_image'] = [
      '#type' => 'hidden',
      '#default_value' => $has_image
    ];

    $element['image_content'] = [
      '#type' => 'hidden'
    ];

    $element['image'] = [
      '#type' => 'file',
      '#attributes' => [
        'class' => ['hidden']
      ],
      '#title' => t('Product Image'),
      '#title_display' => 'invisible',
    ];

    $element['image_wrapper'] = [
      '#theme' => 'blingby_image_cropper',
      '#image' => $file_url,
      '#path' => drupal_get_path('module', 'blingby_image_cropper'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    $new_values = [];
    if ($values[0]['has_image']) {
      $new_values['target_id'] = $values[0]['has_image'];
    } else if ($values[0]['image_content']) {
      $filesystem = \Drupal::service('file_system');
      $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $values[0]['image_content']));

      $image = \Drupal::service('blingby.optimizer')->compress($image);

      $image_name = time().'.png';
      $destination = "public://videos/$image_name";

      if (!file_exists($destination)) {
        $uri = \Drupal::service('file_system')->saveData($image, $destination, 0);
        // Create a file entity.
        $file = File::create([
          'uri' => $uri,
          'uid' => \Drupal::currentUser()->id(),
          'status' => FILE_STATUS_PERMANENT,
        ]);

        $file->save();
        $new_values['target_id'] = $file->id();
      } else {
        $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $destination]);
        $file = reset($files);
        $new_values['target_id'] = $file->id();
      }
    }

    return $new_values;
  }
}









