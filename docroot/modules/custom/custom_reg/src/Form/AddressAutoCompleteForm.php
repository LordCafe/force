<?php

namespace Drupal\custom_reg\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form to handle Squadron autocomplete.
 */
class AddressAutoCompleteForm extends FormBase {


  public function getFormId() {
    return 'address_list';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $squadronNumber = NULL) {

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#placeholder' => $this->t('Enter Address #'),
      '#autocomplete_route_name' => 'custom_reg.autocomplete.address',
      '#autocomplete_route_parameters' => array('squadronNumber' =>
        $squadronNumber == 'blank' ? '' : $squadronNumber)
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
