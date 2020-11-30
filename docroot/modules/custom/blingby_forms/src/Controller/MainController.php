<?php
namespace Drupal\blingby_forms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\blingby_forms\Form\BForm;
use Drupal\blingby_forms\Form\DeleteForm;

class MainController extends ControllerBase {

  public function index() {
    $request = \Drupal::request();
    $currentUser = $this->currentUser();
    $formStorage = $this->entityManager()->getStorage('bForm');

    $query = $formStorage->getQuery();
    $query->condition('uid', $currentUser->id());
    $query->pager(10);
    $fids = $query->execute();

    $forms = [];
    if (!empty($fids)) {
      $formsEntities = $formStorage->loadMultiple($fids);
      foreach ($formsEntities as $f) {
        $forms[] = $f->toArray();
      }
    }

    $build = [
      '#theme' => 'blingby_forms',
      '#cache' => [
        'max-age' => 0
      ],
      '#forms' => $forms,
    ];

    return $build;
  }

  public function add() {

    $build = [
      '#theme' => 'blingby_forms_form',
      '#title' => $this->t('Add Form'),
      '#attached' => [
        'library' => ['blingby_forms/form']
      ],
      '#cache' => [
        'max-age' => 0
      ]
    ];


    $build['#form'] = $this->formBuilder()->getForm(BForm::class);

    return $build;
  }

  public function single($fid) {

    $formStorage = $this->entityManager()->getStorage('bForm');
    $currentUser = $this->currentUser();
    $currentRoles = $currentUser->getRoles();

    $bformEntity = $formStorage->load($fid);

    if (!$bformEntity) {
      throw new NotFoundHttpException();
    }

    if ($bformEntity->get('uid')->getString() != $currentUser->id() && !in_array('administrator', $currentRoles)) {
      throw new NotFoundHttpException();  
    }

    $bform = $bformEntity->toArray();


    $build = [
      '#theme' => 'blingby_forms_form',
      '#title' => $this->t('Edit Form'),
      '#attached' => [
        'library' => ['blingby_forms/form']
      ],
      '#cache' => [
        'max-age' => 0
      ]
    ];

    $build['#form'] = $this->formBuilder()->getForm(BForm::class, $bform);

    return $build;
  }

  public function remove($fid) {
    $formStorage = $this->entityManager()->getStorage('bForm');
    $currentUser = $this->currentUser();
    $currentRoles = $currentUser->getRoles();

    $bformEntity = $formStorage->load($fid);

    if (!$bformEntity) {
      throw new NotFoundHttpException();
    }

    if ($bformEntity->get('uid')->getString() != $currentUser->id() && !in_array('administrator', $currentRoles)) {
      throw new NotFoundHttpException();  
    }

    $bform = $bformEntity->toArray();


    $build = $this->formBuilder()->getForm(DeleteForm::class, $bform);

    return $build;

  }

  public function results() {

    $build = [
      '#theme' => 'blingby_forms_results',
      '#cache' => [
        'max-age' => 0
      ]
    ];

    return $build;
  }

}
