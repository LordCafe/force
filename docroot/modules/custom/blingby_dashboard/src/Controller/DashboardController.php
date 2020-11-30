<?php
namespace Drupal\blingby_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;

class DashboardController extends ControllerBase {

  public function index() {
    $request = \Drupal::request();
    $searchTitle = $request->query->get('title');
    $searchRecruiter = $request->query->get('recruiter');
    $currentUser = $this->currentUser();
    $currentRoles = $currentUser->getRoles();
    $nodeStorage = $this->entityManager()->getStorage('node');
    $query = $nodeStorage->getQuery();
    $query->condition('type', 'video');

    if(!is_null($searchTitle) && $searchTitle != "") {
        $searchTitleQuery = '%' . $searchTitle . '%';
        $query->condition("title", $searchTitleQuery, 'like');
      }

    if(!empty($searchRecruiter)) {
      $query->condition('uid', $searchRecruiter);
    }else {
      if (!(in_array('administrator', $currentRoles) ||  in_array('squadron', $currentRoles))) {
        $query->condition('uid', $currentUser->id());
      } else {
          //
        $user = User::load($currentUser->id());
        $database = \Drupal::database();
        $userQuery = $database->select('user__field_registry_number', 'u');
        $userQuery->join('users_field_data', 'ufd', 'u.entity_id = ufd.uid');
        $userQuery->join('user__roles', 'ur', 'ur.entity_id = ufd.uid');
        $res = $userQuery->fields('ufd', ['uid', 'uid'])
         ->condition('field_registry_number_value', $user->get('field_registry_number')->getString())
         ->condition('status', 1)
         ->condition('roles_target_id', 'recruiter')
         ->execute()
         ->fetchAllKeyed();

        if (!empty($res)) {
          $query->condition('uid', array_keys($res), 'in');
        }
      }
    }

    $res = [];

    if(in_array('squadron', $currentRoles)) {
      $user = User::load($currentUser->id());
      $database = \Drupal::database();
      $userQuery = $database->select('user__field_registry_number', 'u');
      $userQuery->join('users_field_data', 'ufd', 'u.entity_id = ufd.uid');
      $userQuery->join('user__roles', 'ur', 'ur.entity_id = ufd.uid');
      $res = $userQuery->fields('ufd', ['uid', 'name'])
       ->condition('field_registry_number_value', $user->get('field_registry_number')->getString())
       ->condition('status', 1)
       ->condition('roles_target_id', 'recruiter')
       ->execute()
       ->fetchAllKeyed();
    }

    $query->sort('created', 'DESC');
    $query->pager(9);

    $nids = $query->execute();
    $videos = [];

    if (!empty($nids)) {
      $nodes = $nodeStorage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $videos[] = $this->renderVideo($node, $currentRoles);
      }
    }

    $squadronRole = in_array('squadron', $currentRoles);
    $build = [
      '#theme' => 'blingby_dashboard',
      '#videos' => $videos,
      '#squadronRole' => $squadronRole,
      '#roles' => $currentRoles,
      '#uid' => $currentUser->id(),
      '#path' => drupal_get_path('module', 'blingby_dashboard'),
      '#users' => $res,
      '#searchTitle' => $searchTitle,
      '#searchRecruiter' => $searchRecruiter,
      '#cache' => [
        'max-age' => 0
      ]
    ];

    return $build;
  }


  public function renderVideo($node, $currentRoles = []) {
    $video = [
      'title' => $node->getTitle(),
      'date' => $node->getCreatedTime(),
      'nid' => $node->id(),
      'published' => $node->isPublished(),
      'owner' => $node->getOwner()->get('name')->getString(),
      'status' => $node->get('field_status')->getString(),
      'image' => FALSE
    ];

    if (!$node->get('field_image')->isEmpty()) {
      $video['image'] = ImageStyle::load('video_thumbnail')->buildUrl($node->field_image->entity->getFileUri());
    }

    return $video;
  }

}
