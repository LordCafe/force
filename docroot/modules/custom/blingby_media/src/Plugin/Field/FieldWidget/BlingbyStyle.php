<?php

namespace Drupal\blingby_media\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementation of the 'field_blingby_style' widget.
 *
 * @FieldWidget(
 *   id = "field_blingby_style",
 *   module = "blingby_media",
 *   label = @Translation("Blingby Style"),
 *   field_types = {
 *     "field_blingby_style"
 *   }
 * )
 */
class BlingbyStyle extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $plugin_manager = \Drupal::service('plugin.manager.blingby_video_style');
    $plugins = $plugin_manager->getDefinitions();

    $field_name = $this->fieldDefinition->getName();
    $default = 'airforce';



    $element['value'] = [
      '#type' => 'hidden',
      '#default_value' => $default,
    ];

    $element['style_container'] = [
      '#type' => 'style_container',
      '#attributes' => [
        'id' => 'style-container',
      ],
    ];

    $element['style_container']['title'] = [
      '#markup' => Markup::create(
        '<h3>I-Frame Style</h3>'.
        '<div class="video-style-selected"><a href="#">'.$plugins[$default]['label'].'</a></div>'
      ),
    ];

    $items = [];

    foreach ($plugins as $p) {
      $id = $p['id'];

      if ($id != 'airforce') continue;

      $instance = $plugin_manager->createInstance($id);
      $class = ($id == $default)? 'selected' : '';
      $selected = ($id == $default)? 'checked' : '';
      $item = '<label for="style-' . $id . '" class="'.$class.'">';
        $item .= '<input type="radio" value="' . $id . '" name="style-selector" id="style-' . $id . '" '. $selected.'>';
        $item .= '<img src="/'.$instance->getImage().'" class="img-fluid">';
        $item .= '<h5>'. $p['label'] .'</h5>';
        $item .= '<div class="video-style-overlay"></div>';
      $item .= '</label>';
      $items[] = [ '#markup' => Markup::create($item) ];
    }

    $element['style_container']['options'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];


    $element['#attached']['library'][] = 'blingby_media/style';

    return $element;
  }

}

