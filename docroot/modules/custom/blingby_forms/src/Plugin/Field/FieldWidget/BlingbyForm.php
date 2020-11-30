<?php

namespace Drupal\blingby_forms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementation of the 'field_blingby_form' widget.
 *
 * @FieldWidget(
 *   id = "field_blingby_form",
 *   module = "blingby_forms",
 *   label = @Translation("Blingby Form"),
 *   field_types = {
 *     "field_blingby_form"
 *   }
 * )
 */
class BlingbyForm extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $currentUser = \Drupal::currentUser();
    $formStorage = \Drupal::entityManager()->getStorage('bForm');
    $forms = $formStorage->loadByProperties(['assigned' => 'video']);
    $default = isset($items[$delta]->value) ? $items[$delta]->value : '';

    $options = [];

    foreach ($forms as $f) {
      $options[$f->id()] = $f->get('title')->getString();
    }

    $element['value'] = [
      '#title' => $this->t('Form'),
      '#type' => 'select',
      '#default_value' => $default,
      '#empty_value' => '',
      '#empty_label' => 'Select',
      '#options' => $options
    ];

    return $element;
  }

}

