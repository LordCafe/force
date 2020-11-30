<?php
namespace Drupal\blingby_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class RecruiterController extends ControllerBase {

  public function get($zipcode = FALSE) {
    $recruiter_id = \Drupal::service('custom_reg.recruiter_name')->getRecruiterName($zipcode);
    $userStorage = $this->entityManager()->getStorage('user');

    $data['recruiter'] = FALSE;

    if ($recruiter_id) {
      if ($recruiter = $userStorage->load($recruiter_id)) {

        $data['recruiter'] = [
          'title' => $recruiter->get('field_title')->getString(),
          'phone' => $recruiter->get('field_work_phone_number')->getString(),
          'fullname' => $recruiter->get('field_full_name')->getString(),
          'email' => $recruiter->getEmail(),
        ];
      }
    }

    return new JsonResponse($data);
  }
}









