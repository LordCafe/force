<?php

namespace Drupal\blingby_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Markup;
use \Drupal\Core\Session\AccountProxy;

/**
 * Implements the tiles form.
 */
class BForm extends FormBase {

  /**
   * Class constructor.
   */
  public function __construct(EntityManager $entity_manager, AccountProxy $current_user) {
    $this->formStorage = $entity_manager->getStorage('bForm');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bform_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bform = false) {

    $storage = $form_state->getStorage();
    $userInput = $form_state->getUserInput();
    $trigger = $form_state->getTriggeringElement();

    if ((isset($userInput['fields']) && isset($userInput['fields']['counter']))) {
      $fields = $userInput['fields']['counter'];
    } else {
      $fields = $bform? count($bform['fields']) : 0;
    }

    $new_field = FALSE;
    if (strpos($trigger['#id'], 'edit-fields-add--') === 0) {
      $new_field = TRUE;
      $fields++;
    }

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $bform? $bform['id']: 0
    ];


    $form['assigned'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => [
        'video' => $this->t('Video Only'),
        'tile' => $this->t('Tile Only'),
      ],
      '#default_value' => ($bform)? $bform['assigned']: 'video',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => ($bform)? $bform['title']: '',
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Thank you Message'),
      '#required' => TRUE,
      '#default_value' => ($bform)? $bform['message']: '',
    ];

    $form['fields'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#prefix' => '<div id="fields-container" class="pt-3">',
      '#suffix' => '</div>'
    ];

    $form['fields']['counter'] = [
      '#type' => 'hidden',
      '#value' => $fields
    ];

    for($i = 0; $i < $fields; $i++) {

      $icon = 'fa-angle-down';
      if ($bform && isset($bform['fields'][$i]) && (!$new_field || $i < ($fields-1))) {
        $default_values = $bform['fields'][$i];
        if (!empty($default_values['label'])) {
          $defaul_class = 'closed';
          $icon = 'fa-angle-up';
        }
      } else {
        $defaul_class = '';
        $default_values = [
          'type' => 'text',
          'key' => '',
          'label' => '',
          'placeholder' => '',
          'options' => '',
        ];
      }

      $form['fields']['items'][$i] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'bform-field-container ' . $defaul_class
          ]
        ]
      ];

      $title = $this->t('Question @number', ['@number' => ($i+1)]);
      $tlabel = ($default_values['label'])? ' - '.$default_values['label']: '';

      $form['fields']['items'][$i]['title'] = [
        '#markup' => Markup::create('<h2>'.$title.'<small>'.$tlabel.'</small><i class="fa '.$icon.'"></i></h2>'),
      ];

      $form['fields']['items'][$i]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Type'),
        '#default_value' => $default_values['type'],
        '#options' => [
          'text' => 'Text Field',
          'textarea' => 'Text Area',
          'select' => 'Select',
          'date' => 'Date',
          'radio' => 'Radios',
          'checkbox' => 'Checkboxes',
        ]
      ];

      $form['fields']['items'][$i]['key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Key'),
        '#size' => 20,
        '#default_value' => $default_values['key'],
      ];

      $form['fields']['items'][$i]['placeholder'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Placeholder'),
        '#size' => 20,
        '#default_value' => $default_values['placeholder'],
      ];

      $form['fields']['items'][$i]['remove'] = [
        '#type' => 'button',
        '#value' => $this->t('Delete Field'),
        '#id' => 'remove-'.$i,
        '#name' => 'remove_'.$i,
        '#attributes' => [
          'class' => ['btn-danger']
        ],
        '#ajax' => [
          'callback' => [$this, 'removeField'],
          'wrapper' => 'fields-container'
        ]
      ];

      $form['fields']['items'][$i]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $default_values['label'],
        '#prefix' => '<div class="full-width">',
        '#suffix' => '</div>',
      ];

      $form['fields']['items'][$i]['options'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Options'),
        '#default_value' => $default_values['options'],
        '#description' => 'Add option on each line',
        '#states' => [
          'invisible' => [
            [':input[name="fields[items]['.$i.'][type]"]' => ['value' => 'text']],
            'or',
            [':input[name="fields[items]['.$i.'][type]"]' => ['value' => 'textarea']],
            'or',
            [':input[name="fields[items]['.$i.'][type]"]' => ['value' => 'date']],
          ],
        ],
      ];
    }

    $form['fields']['add'] = [
      '#type' => 'button',
      '#value' => 'Add Field',
      '#attributes' => [
        'class' => ['btn-outline']
      ],
      '#ajax' => [
        'callback' => [$this, 'addField'],
        'wrapper' => 'fields-container'
      ]
    ];

    $form['actions'] = ['#type' => 'actions'];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save')
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addField(array &$form, FormStateInterface $form_state) {
    return $form['fields'];
  }

  public function removeField(array &$form, FormStateInterface $form_state) {

    $trigger = $form_state->getTriggeringElement();

    if (strpos($trigger['#id'], 'remove-') === 0) {
      $field_id = str_replace('remove_', '', $trigger['#name']);
      unset($form['fields']['items'][$field_id]);
      $form['fields']['counter']['#value']--;

      $index = 0;
      $items = [];
      foreach ($form['fields']['items'] as $i =>  $item) {
        if (is_numeric($i)) {

          $items[$index] = $form['fields']['items'][$i];
          unset($form['fields']['items'][$i]);

          $title = $this->t('Question @number', ['@number' => ($index+1)]);
          $tlabel = ($items[$index]['label']['#value'])? ' - '.$items[$index]['label']['#value']: '';
          $icon = ($tlabel)? 'fa-angle-up' : 'fa-angle-down';
          $items[$index]['title'] = [
            '#markup' => Markup::create('<h2>'.$title.'<small>'.$tlabel.'</small><i class="fa '.$icon.'"></i></h2>'),
          ];

          $items[$index]['remove']['#id'] = 'remove-'.$index;
          $items[$index]['remove']['#name'] = 'remove_'.$index;
          $index++;
        }
      }

      $form['fields']['items'] = array_merge($form['fields']['items'], $items);

    }

    return $form['fields'];
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $data = [];
    $data['assigned'] = $values['assigned'];
    $data['title'] = $values['title'];
    $data['message'] = $values['message'];

    if (isset($values['fields']['items'])) {
      $total = count($values['fields']['items']);
      foreach ($values['fields']['items'] as &$item) {
        unset($item['remove']);
      }

      if (empty($values['fields']['items'][$total-1]['label'])) {
        unset($values['fields']['items'][$total-1]);
      }

      $data['data'] = $values['fields']['items'];
    } else {
      $data['data'] = [];
    }

    $data['data'] = JSON::encode($data['data']);

    if ($values['id']) {
      $bform = $this->formStorage->load($values['id']);
      $bform->set('data', $data['data']);
      $bform->set('message', $data['message']);
      $bform->set('title', $data['title']);
      $bform->set('assigned', $data['assigned']);
    } else {
      $data['uid'] = $this->currentUser->id();
      $bform = $this->formStorage->create($data);
    }

    $bform->save();

    return $form_state->setRedirect('blingby_forms.index');
  }
}






