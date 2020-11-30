<?php

namespace Drupal\blingby_media\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_blingby_video' widget.
 *
 * @FieldWidget(
 *   id = "field_blingby_video",
 *   module = "blingby_media",
 *   label = @Translation("Blingby Video"),
 *   field_types = {
 *     "field_blingby_video"
 *   }
 * )
 */
class BlingbyVideo extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $plugin_manager = \Drupal::service('plugin.manager.blingby_video_provider');
    $plugins = $plugin_manager->getDefinitions();

    $field_name = $this->fieldDefinition->getName();

    $options = array_combine(array_keys($plugins), array_column($plugins, 'label'));
    $default_provider = isset($items[$delta]->provider) ? $items[$delta]->provider : 'jwplayer';

    $element['wrapper'] = [
      '#type' => 'container'
    ];

    $element['wrapper']['provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Video Provider'),
      '#title_display' => 'hidden',
      '#default_value' => $default_provider,
      '#options' => $options,
      '#ajax' => [
        'callback' => [$this, 'selected'],
        'event' => 'change',
        'wrapper' => 'video-value'
      ]
    ];

    if ($values = $form_state->getUserInput()) {
      if ($values[$field_name][$delta]['wrapper']['provider']) {
        $default_provider = $values[$field_name][$delta]['wrapper']['provider'];
      }
    }

    $plugin = $plugin_manager->createInstance($default_provider);

    $element['wrapper']['values'] = $plugin->getForm($items[$delta]) + [
      '#values' => 'container',
      '#prefix' => '<div id="video-value">',
      '#suffix' => '</div>',
    ];

    return $element;
  }



  public function selected(array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    return $form[$field_name]['widget'][0]['wrapper']['values'];
  }


  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    foreach ($values as $delta => $value) {
      $values[$delta]['provider'] = $value['wrapper']['provider'];
      if (isset($value['wrapper']['values'])) {
        foreach ($value['wrapper']['values'] as $key => $v) {
          if (is_array($v)) {
            if (!empty($v)) {
              $values[$delta][$key] = $v[0];
            }
          } else {
            $values[$delta][$key] = $v;
          }
        }

        if ($values[$delta]['provider']) {
          $plugin_manager = \Drupal::service('plugin.manager.blingby_video_provider');
          $plugin = $plugin_manager->createInstance($values[$delta]['provider']);
          $plugin->updateValues($values[$delta]);
        }
      }
    }

    return $values;
  }


}

