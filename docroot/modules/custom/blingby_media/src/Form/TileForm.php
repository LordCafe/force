<?php

namespace Drupal\blingby_media\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\blingby_media\Plugin\TilesManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\Entity\File;
use GuzzleHttp\Client;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Markup;
use Drupal\blingby_media\Optimizer;

/**
 * Implements the tiles form.
 */
class TileForm extends FormBase {

  /**
   * Class constructor.
   */
  public function __construct(EntityManager $entity_manager, TilesManager $tiles_manager, ModuleHandlerInterface $module_handler, Client $http_client, Optimizer $optimizer) {
    $this->tilesStorage = $entity_manager->getStorage('tile');
    $this->nodeStorage = $entity_manager->getStorage('node');
    $this->tilesManager = $tiles_manager;
    $this->moduleHandler = $module_handler;
    $this->httpClient = $http_client;
    $this->optimizer = $optimizer;

    if ($this->moduleHandler->moduleExists('blingby_forms')) {
      $this->bformStorage = $entity_manager->getStorage('bForm');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.blingby_tiles'),
      $container->get('module_handler'),
      $container->get('http_client'),
      $container->get('blingby.optimizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tiles_form';
  }



  public function CreateFormUser(&$form, $tile){
  
      $user =$this->getInfoUser();
      if($user){
         foreach ($user as $key => $value) {
         $form['values'][$value] = [
          '#type' => 'textfield',
          '#title' => $key,
          '#default_value' => $value,
         // '#required' => TRUE,
        ];

      }
      }else{

      $keys = array('mail' =>'email', 'field_work_phone_number'=>'call','field_recruiter_address  '=>'map');
       foreach ($keys as $key => $value) {
         $form['values'][$value] = [
          '#type' => 'textfield',
          '#title' => $value,
          '#default_value' => '',
         // '#required' => TRUE,
        ];

      }


      }

        

      
     


       return $form;
  }

  public function getInfoUser(){
  // Load the current user.
  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

  //$default_user_fields  = \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user');
  $keys = array('mail' =>'email', 'field_work_phone_number'=>'call','field_recruiter_address'=>'map');
    $data_user = array();
    foreach ( $keys as $key => $value) {
      $data_user[$value] =  $user->get($key)->value;
    }

  return $data_user;



  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $video = false) {
  
    $plugins = $this->tilesManager->getDefinitions();
    $options = array_combine(array_keys($plugins), array_column($plugins, 'label'));
    $tile = FALSE;
    $default_plugin = '';

    if ($values = $form_state->getUserInput()) {
      $default_plugin = $values['plugin'];
      if ($values['id']) {

        $tile = $this->tilesStorage->load($values['id']);
      
      }
    }


    $bform = false;
    if ($this->moduleHandler->moduleExists('blingby_forms')) {
      if ($video) {
        $video_node = $this->nodeStorage->load($video);
        if ($video_node) {
          $form_field = $video_node->get('field_form')->getValue();
          if ($form_field) {
            $bform = $this->bformStorage->load($form_field[0]['value']);
            $bform = ($bform)? $bform->toArray(true) : false;
          }
        }
      }
    }


    $form['#prefix'] = '<div id="tile-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['plugin'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Select Type'),
      '#title_display' => 'invisible',
      '#default_value' => $default_plugin,
      '#options' => ['' => 'Add Tile'] + $options,
      '#ajax' => [
        'callback' => [$this, 'selected'],
        'event' => 'change',
        'wrapper' => 'tile-value'
      ]
    ];

    $form['vid'] = [
      '#type' => 'hidden',
      '#default_value' => $video
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#default_value' => ($tile)? $tile->id() : 0,
    ];

    $form['values'] = [
      '#values' => 'container',
      '#prefix' => '<div id="tile-value">',
      '#suffix' => '</div>',
    ];

    $form['values']['force'] = [
      '#type' => 'hidden',
      '#default_value' => 0,
    ];

    if ($this->moduleHandler->moduleExists('blingby_image_cropper')) {
      $form['#attached']['library'][] = 'blingby_image_cropper/tile';
    }

    if ($default_plugin) {

      $tile = $tile? $tile->toArray() : false;

      $form['values']['time'] = [
        '#type' => 'textfield',
        '#title' => 'Time',
        '#size' => '16',
        '#attributes' => [
          'placeholder' => 'HH:MM:SS:MS',
        ],
        '#title_display' => 'invisible',
        '#default_value' => $tile? $tile['timef']: '',
      ];

      $form['values']['image'] = [
        '#type' => 'managed_file',
        '#title' => 'Image',
        '#default_value' => ($tile && $tile['fid'])? ['fids' => [$tile['fid']]] : ['fids' => []],
        '#upload_location' => 'public://tiles/',
        '#upload_validators' => [
          'file_validate_extensions' => ['jpg gif png jpeg'],
        ],
      ];


  
      if ($this->moduleHandler->moduleExists('blingby_image_cropper')) {
        $form['values']['image'] = [
          '#type'=> 'file',
          '#title' => 'Image',
        ];

        if ($tile['image']) {
          $form['values']['image']['#attributes'] = ['class' => ['d-none']];
        }

        $form['values']['has_image'] = [
          '#type' => 'hidden',
          '#default_value' => $tile['fid']
        ];

        $form['values']['image_content'] = [
          '#type' => 'hidden'
        ];

        $form['values']['image_wrapper'] = [
          '#theme' => 'blingby_image_tile_cropper',
          '#image' => $tile['image'],
        ];
      }

      $form['values']['title'] = [
        '#type' => 'textfield',
        '#title' => 'Title',
       '#default_value' => $tile? $tile['title']: '',
        '#required' => TRUE,
      ];

      $form['values']['scrapper'] = [
        '#type' => 'button',
        '#attributes' => [
          'class' => ['m-0 btn-warning']
        ],
        '#ajax' => [
          'callback' => [$this, 'search'],
          'wrapper' => 'scrapper-item-box',
          'method' => 'replace',
        ],
        '#value' => 'Search',
        '#states' => [
          'disabled' => [
            ':input[name="title"]' => ['filled' => FALSE],
          ],
        ],
        '#prefix' => '<div class="js-form-item">',
        '#suffix' => '</div>',
      ];

      $form['values']['scrapper-container'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'scrapper-item-box',
        ],
      ];

      $form['values']['url'] = [
        '#type' => 'url',
        '#title' => 'URL',
        '#default_value' => $tile? $tile['url']: '',
      ];

      $form['values']['cta'] = [
        '#type' => 'textfield',
        '#title' => 'CTA',
        '#default_value' => $tile? $tile['cta']: '',
      ];

      $form['values']['description'] = [
        '#type' => 'textarea',
        '#title' => 'Description',
        '#default_value' => $tile? $tile['description']: '',
      ];

      $plugin = $this->tilesManager->createInstance($default_plugin);
      $form['values'] = $form['values'] + $plugin->getForm($tile);

      if ($plugin->hideDescription()) {
        $form['values']['description']['#access'] = FALSE;
      }

      if ($plugin->hideCTA()) {
        $form['values']['url']['#access'] = FALSE; 
        $form['values']['cta']['#access'] = FALSE; 
      }


      if ($bform && !empty($bform['fields'])) {

        $condition = false;

        if ($tile && isset($tile['params']) && $tile['params'] && isset($tile['params']['condition'])) {
          $condition = $tile['params']['condition'];
        }


        $form['values']['conditions'] = [
          '#type' => 'container',
        ];


        $fields_labels = [];
        foreach ($bform['fields'] as $field) {
          $fields_labels[$field['key']] = $field['label'];
        }

        $form['values']['condition_key'] = [
          '#type' => 'select',
          '#title' => $this->t('If Field'),
          '#default_value' => isset($condition['key'])? $condition['key']: '',
          '#empty_value' => '',
          '#empty_option' => $this->t('No Condition'),
          '#options' => $fields_labels,
          '#prefix' => '<div class="">'
        ];


        if($default_plugin === 'contact'){

            $form['values']['type_video'] = array(
'#type' => 'radios',
'#title' => t('Type of video'),
/*'#description' => t('Select a method for deleting annotations.'),*/
'#options' => array('general' => 'Air Force General video', 'private' => 'Personal video'),
'#default_value' => (isset(  $tile['video_type'] ) ) ?  $tile['video_type'] : 'general' ,
'#required' => TRUE,
);
            $this->CreateFormUser($form ,  $tile);
        }
      
        $form['values']['condition_value'] = [
          '#type' => 'textfield',
          '#size' => 40,
          '#title' => $this->t('has Value'),
          '#default_value' => isset($condition['value'])? $condition['value']: '',
          '#states' => [
            'disabled' => [
              'select[name="condition_key"]' => ['value' => ''],
            ],
          ],
          '#suffix' => '</div>'
        ];
      }
  

      $form['values']['actions'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['group-actions']
        ]
      ];

      $form['values']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => 'Save',
        '#ajax' => [
          'callback' => [$this, 'save'],
          'wrapper' => 'tile-form-wrapper'
        ]
      ];

      if ($tile) {
        $form['values']['actions']['back'] = [
          '#type' => 'button',
          '#value' => 'Cancel',
          '#attributes' => [
            'onclick' => 'return (false);',
            'class' => ['btn-cancel']
          ]
        ];

        $form['values']['actions']['delete'] = [
          '#type' => 'button',
          '#value' => 'Delete',
          '#attributes' => [
            'class' => ['btn-delete btn-danger ml-auto']
          ],
          '#ajax' => [
            'callback' => [$this, 'delete'],
            'wrapper' => 'tile-form-wrapper'
          ]
        ];
      }
    }
      
    return $form;
  }

  public function selected(array $form, FormStateInterface $form_state) {

    foreach ($form['values'] as $key => $input) {
      if ($key != 'submit') {
        if (isset($form['values'][$key]['#value']) && isset($form['values'][$key]['#default_value'])) {
          $form['values'][$key]['#value'] = $form['values'][$key]['#default_value'];
        }
      }
    }

    return $form['values'];
  }

  public function save(array $form, FormStateInterface $form_state) {
    $svalues =  $values = $form_state->getValues();

    $id = $values['id']? : 0;
    $vid = $values['vid'];
    $plugin_id = $values['plugin'];
    $image = $values['image'];

    //Clear insecure values
    $values = array_filter($values, function ($key) {
      return !in_array($key, ['submit','form_build_id','form_token','form_id','op', 'vid', 'image', 'plugin']);
    }, ARRAY_FILTER_USE_KEY);

    //Parse time
    if ($values['time']) {
      $formatted_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", substr($values['time'] , 0, 8));
      sscanf($formatted_time, "%d:%d:%d", $hours, $minutes, $seconds);
      $values['time'] = ($hours * 3600 + $minutes * 60 + $seconds);
    }

    //Pass to plugin
    $plugin = $this->tilesManager->createInstance($plugin_id);
    $values = $plugin->processValues($values);

    //Clear other values
    $values = array_filter($values, function ($key) {
      return in_array($key, ['image','title','url','cta','description', 'params', 'time']);
    }, ARRAY_FILTER_USE_KEY);

    // Put back type and vid
    $values['plugin'] = $plugin_id;
    $values['vid'] = $vid;

    if(isset($svalues['condition_key']) && $svalues['condition_key']) {
      $values['params']['condition'] = [
        'key' => $svalues['condition_key'],
        'value' => $svalues['condition_value'],
      ];
    }

    $values['params'] = serialize($values['params']?: []);

    if ($this->moduleHandler->moduleExists('blingby_image_cropper')) {

      if ($svalues['image_content']) {
        $filesystem = \Drupal::service('file_system');
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $svalues['image_content']));
        $image = $this->optimizer->compress($image);
        $image_name = time().'.png';
        $destination = "public://tiles/$image_name";
        $uri = \Drupal::service('file_system')->saveData($image, $destination, 0);
        $file = File::create([
          'uri' => $uri,
          'uid' => \Drupal::currentUser()->id(),
          'status' => FILE_STATUS_PERMANENT,
        ]);

        $file->save();
        $values['fid'] = $file->id();
      } else if ($svalues['has_image']) {
        $values['fid'] = $svalues['has_image'];
      }
    } else {
      if (!empty($image)) {
        $values['fid'] = $image[0];
      } else {
        $values['fid'] = 0;
      }
    }

    if ($id) {
      $tile = $this->tilesStorage->load($id);

      foreach ($values as $field => $value) {
        $tile->set($field, $value);
      }
    } else {
      $tile = $this->tilesStorage->create($values);
    }

    $tile->save();

    $values = $tile->toArray();

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'clearTileForm', [$values]));
    return $response;
  }

  public function delete(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $id = $values['id']? : 0;

    if ($id) {
      $this->tilesStorage->load($id)->delete();
    }

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'deleteTile', [$id]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }


  public function search(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $data = [ 
      'query' => [
        'search_term' => $values['title'],
        'images_count' => 5,
        'include_urls' => 1,
      ]
    ];

    $request = $this->httpClient->request('GET', 'https://flask.blingby.com/api/search', $data);
    $images = Json::decode($request->getBody()->getContents());

    $form['values']['scrapper-container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'scrapper-item-box',
      ],
    ];

    $items = [];

    foreach ($images['images'] as $index => $image) {
      $id = 'image-selector-' . $index;
      $item = '<label for="' . $id . '" style="background-image:url(' . $image['image'] . ')"';
      $item .= 'data-url="' . $image['url'] . '" data-image="' . $image['image'] . '">';
      $item .= '<input type="radio" value="' . $index . '" name="image" id="' . $id . '">';
      $item .= '</label>';
      $items[] = [ '#markup' => Markup::create($item) ];
    }

    $form['values']['scrapper-container']['images'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];

    return $form['values']['scrapper-container'];

  }

}



