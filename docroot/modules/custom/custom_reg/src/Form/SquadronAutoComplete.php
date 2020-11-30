<?php

namespace Drupal\custom_reg\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form to handle Squadron autocomplete.
 */
class SquadronAutoComplete extends FormBase {


  public function getFormId() {
    return 'squadron_list';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['squadron'] = [
      '#type' => 'textfield',
      '#title' => $this->t('My Autocomplete'),
      '#placeholder' => $this->t('Enter Squadron #'),
      '#autocomplete_route_name' => 'custom_reg.autocomplete.squadron',
    ];

    $form['actions'] = ['#type' => 'actions'];

    $form['save'] = [
      '#type' => 'submit',
      '#attributes' => ['class' => ['save-button']],
      '#value' => $this->t('Done'),
    ];

    $form['cancel'] = [
      '#type' => 'submit',
      '#attributes' => ['class' => ['cancel-button']],
      '#value' => $this->t('Cancel'),
    ];

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Extracts the entity ID from the autocompletion result.
    $article_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($form_state->getValue('article'));
  }
}
