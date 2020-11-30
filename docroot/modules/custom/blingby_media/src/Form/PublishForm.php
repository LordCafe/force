<?php

namespace Drupal\blingby_media\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Render\Markup;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * ModalForm class.
 */
class PublishForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_submit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $video = null) {

    $form['#attributes']['class'][] = 'p-4';

    $form['#prefix'] = '<div id="modal_submit_form">';
    $form['#suffix'] = '</div>';


    $form['nid'] = [
      '#type' => 'hidden',
      '#default_value' => $video->id(),
    ];

    $status = $video->isPublished()? 1: 0;

    $form['status'] = [
      '#type' => 'hidden',
      '#default_value' => $status,
    ];

    if ($status) {
      $message = $this->t('Are you sure you want fo unpublish this video?');
    } else {
      $message = $this->t('Are you sure you want to publish this video?');
    }

    $form['content'] = [
      '#markup' => Markup::create('<p>'.$message.'</p>')
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitAjax'],
        'event' => 'click',
      ],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'cancel'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $values = $form_state->getValues();

    $node = \Drupal::entityManager()->getStorage('node')->load($values['nid']);
    $status = $values['status']? 0: 1;
    $node->setPublished($status)->save();

    $response->addCommand(new InvokeCommand(NULL, 'reloadPage', []));
    return $response;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}