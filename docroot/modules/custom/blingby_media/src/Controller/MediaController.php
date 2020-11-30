<?php
namespace Drupal\blingby_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\blingby_media\Form\TileForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Asset\AttachedAssets;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\file\Entity\File;

class MediaController extends ControllerBase {

  public function temporal() {


    $path = drupal_get_path('module', 'blingby_media');
    $path .=  '/assets/img/logo.png';

    $build = [
      '#theme' => 'blingby_media_temporal',
      '#image' => $path
    ];

    return $build;
  }

  public function index($node) {
    $build = [
      '#theme' => 'blingby_media_editor',
      '#attached' => [
        'library' => ['blingby_media/editor']
      ],
      '#cache' => [
        'max-age' => 0
      ]
    ];

    $plugin_manager = \Drupal::service('plugin.manager.blingby_video_provider');

  	$nodeStorage = $this->entityManager()->getStorage('node');
    $tileStorage = $this->entityManager()->getStorage('tile');
  	$currentUser = $this->currentUser();
  	$currentRoles = $currentUser->getRoles();
  	$video = $nodeStorage->load($node);

  	if (!$video || $video->getType() != 'video') {
  		throw new NotFoundHttpException();
  	}

  	if ($video->getOwnerId() != $currentUser->id() && !in_array('administrator', $currentRoles)) {
  		throw new NotFoundHttpException();	
  	}

    $build['#title'] = $video->getTitle();
    $build['#nid'] = $video->id();
    $build['#status'] = $video->isPublished();
    $build['#form'] = $this->formBuilder()->getForm(TileForm::class, $video->id());
    $build['#path'] = drupal_get_path('module', 'blingby_dashboard');
    

  	$provider = $video->get('field_video')->getValue();
    $provider_id = false;

  	if (!empty($provider)) {
      $video_plugin = $plugin_manager->createInstance($provider[0]['provider']);
  		$provider_id = $video_plugin->validate($provider[0]['provider_id']);
      if ($library = $video_plugin->getLibrary()) {
        $build['#attached']['library'][] = $library;
      }
  	}

    $tilesEntities = $tileStorage->loadByProperties(['vid' => $video->id()]);

    $tiles = [];
    foreach ($tilesEntities as $tile) {
      $tiles[] = $tile->toArray();
    }

    $build['#attached']['drupalSettings']['blingby_video'] = [
      'tiles' => $tiles,
      'provider_id' => $provider_id
    ];

    return $build;
  }

  public function scrapper() {
    $post_data = \Drupal::request()->request->all();
    $image = $post_data['image'];
    $output = ['image' => false];

    if ($image) {
      $fs = \Drupal::service('file_system');

      $dir = 'public://scrapper';
      $fs->prepareDirectory($dir, FILE_CREATE_DIRECTORY);

      
      $image = strtok($image, '?');
      $image_content = file_get_contents($image);
      $image_name = basename($image);
      $destination = $dir . '/' . $image_name;

      $uri = $fs->saveData($image_content, $destination, 0);
      $file = File::create([
        'uri' => $uri,
        'uid' => \Drupal::currentUser()->id(),
        'status' => FILE_STATUS_PERMANENT,
      ]);

      if (is_object($file)) {
        $output['image'] = $file->url();
      }
    }

    return new JsonResponse($output);
  }

  public function iframe($node, Request $request) {
    $nodeStorage = $this->entityManager()->getStorage('node');
    $video = $nodeStorage->load($node);
    $currentUser = $this->currentUser();
    $is_preview = $request->get('preview');

    if (!$video || $video->getType() != 'video') {
      throw new NotFoundHttpException();
    }

    $is_token = ($video->get('field_preview_token')->getString() == $is_preview);
    $is_published = !$video->isPublished();

    if ($is_published && (!$is_preview ||  !$currentUser->id()) && !$is_token) {
      throw new NotFoundHttpException(); 
    }

    $build = [
      '#theme' => 'blingby_media_iframe',
      '#preview' => (int)$is_preview,
      '#nid' => $node,
      '#cache' => ['max-age' => 0],
    ];

    $output = \Drupal::service('renderer')->renderRoot($build);
    $response = new Response();
    $response->setContent($output);


    $others = $video->get('field_access')->getString();

    if (!empty($others)) {
      $others = explode("\n", $others);
      $others = implode(" ", $others);
    }

    $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' *.airforce.com  $others");
    return $response;
  }


  public function api($node, Request $request) {


    $libraries = [];
    $plugin_manager = \Drupal::service('plugin.manager.blingby_video_provider');
    $tiles_manager = \Drupal::service('plugin.manager.blingby_tiles');
    $style_manager = \Drupal::service('plugin.manager.blingby_video_style');
    $assetResolver = \Drupal::service('asset.resolver');
    $moduleHandler = \Drupal::service('module_handler');

    $nodeStorage = $this->entityManager()->getStorage('node');
    $tileStorage = $this->entityManager()->getStorage('tile');
    $formStorage = $this->entityManager()->getStorage('bForm');

    $video = $nodeStorage->load($node);

    if (!$video || $video->getType() != 'video') {
      throw new NotFoundHttpException();
    }


    $provider = $video->get('field_video')->getValue();

    // UniqueCode
    $is_preview = $request->get('preview');
    $unique_code = $request->get('uc');

    if (!$is_preview && $moduleHandler->moduleExists('blingby_analytics')) {
      $libraries[] = 'blingby_analytics/main';
      list($unique_code, $data) = \Drupal::service('blingby.analytics')->generateUniqueCode($unique_code, $node, $request);
    } else {
      $data = [];
      $unique_code = 0;
    }

    // Video Provider
    $video_plugin = $plugin_manager->createInstance($provider[0]['provider']);
    $provider_id = $video_plugin->validate($provider[0]['provider_id']);

    if($library = $video_plugin->getLibrary()) {
      $libraries[] = $library;
    }

    // Video Form
    $form_id = $video->get('field_form')->getValue();

    if (!empty($form_id)) {
      $formEntity = $formStorage->load($form_id[0]['value']);

      if ($formEntity) {
        $form = $formEntity->toArray(true);
      }
    }


    // Video Style
    $style = $video->get('field_style')->getValue();
    $style_id = !empty($style)? $style[0]['value'] : 'airforce';

    $style_plugin = $style_manager->createInstance($style_id);

    $templates = [
      'video' => $style_plugin->getVideoTemplate(),
      'tile' => $style_plugin->getTileTemplate(),
    ];

    if($library = $style_plugin->getLibrary()) {
      $libraries[] = $library;
    }

    $libraries[] = 'blingby_media/iframe';

    $tilesEntities = $tileStorage->loadByProperties(['vid' => $video->id()]);

    $tiles = [];
    foreach ($tilesEntities as $tile) {
      $tile = $tile->toArray();
      $tile['description'] = nl2br($tile['description']);

      if(filter_var($tile['url'], FILTER_VALIDATE_EMAIL)) {
        $tile['url'] = 'mailto:'.$tile['url'];
      } else if (preg_match('/^\+?[0-9\-]+$/m', $tile['url'])) {
        $tile['url'] = 'tel:'.$tile['url'];
      }

      if ($tile_plugin = $tiles_manager->createInstance($tile['plugin'])) {
        $tile = $tile_plugin->preprocess($tile);
      }
      $tiles[] = $tile;
    }

    usort($tiles, function($a, $b){
      return $a['time'] > $b['time']? -1 : 1;
    });

    $image = FALSE;
    if (!$video->get('field_image')->isEmpty()) {
      $image = file_create_url($video->field_image->entity->getFileUri());
    }

    $body = $video->get('body')->getValue();

    if (!empty($body)) {
      $body = nl2br(strip_tags($body[0]['value']));
    } else {
      $body = '';
    }

    $output = [
      'uc' => $unique_code,
      'data' => $data,
      'style' => $templates,
      'form' => $form,
      'video' => [
        'id' => $video->id(),
        'title' => $video->label(),
        'decription' => $body,
        'image' => $image,
        'provider_id' => $provider_id,
        'provider' => $provider[0]['provider'],
      ],
      'tiles' => $tiles,
      'history' => [],
      'css' => [],
      'js' => [],
    ];

    $assets = new AttachedAssets();
    $assets->setLibraries($libraries);
    $css_assets = $assetResolver->getCssAssets($assets, 1);
    $js_assets = $assetResolver->getJsAssets($assets, 1);

    foreach($css_assets as $css) {
      $output['css'][] = file_create_url($css['data']);
    }

    foreach($js_assets[1] as $js) {
      $output['js'][] = file_create_url($js['data']);
    }

    return new JsonResponse($output);
  }
}









