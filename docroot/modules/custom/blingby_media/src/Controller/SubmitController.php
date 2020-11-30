<?php
namespace Drupal\blingby_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\blingby_media\Form\SubmitForm;
use Drupal\blingby_media\Form\PublishForm;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;

class SubmitController extends ControllerBase {


  protected $formBuilder;

  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }


  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  public function action($node) {
    $nodeStorage = $this->entityManager()->getStorage('node');
    $video = $nodeStorage->load($node);
    $response = new AjaxResponse();
    $form = $this->formBuilder->getForm(SubmitForm::class, $video);
    $response->addCommand(new OpenModalDialogCommand('', $form, ['width' => '300']));
    $response->addCommand(new InvokeCommand(NULL, 'closeDropdowns', []));
    return $response;
  }


  public function publish($node) {
    $nodeStorage = $this->entityManager()->getStorage('node');
    $video = $nodeStorage->load($node);
    $response = new AjaxResponse();
    $form = $this->formBuilder->getForm(PublishForm::class, $video);
    $response->addCommand(new OpenModalDialogCommand('', $form, ['width' => '300']));
    $response->addCommand(new InvokeCommand(NULL, 'closeDropdowns', []));
    return $response;
  }

  public function code($node) {
    $nodeStorage = $this->entityManager()->getStorage('node');
    $video = $nodeStorage->load($node);
    $currentUser = $this->currentUser();
    $currentRoles = $currentUser->getRoles();

    if ($video->getOwnerId() != $currentUser->id() && !in_array('administrator', $currentRoles)) {
      throw new NotFoundHttpException();  
    }

    $path = Url::fromRoute('blingby_media.iframe', ['node' => $node]);
    $path->setAbsolute(true);
    $path = $path->toString();

    $content = '<iframe src="'.$path.'" width="100%" height="800px" frameBorder="0"></iframe>';
    $filename = "Confidential_Blingby_Proprietary_" . str_replace(' ','_',$video->label())."_".$video->id()."_I-Frame_Code.txt";

    $response = new Response();
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Content-type', 'text/plain');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Cache-control', 'private');
    $response->headers->set('Content-length', strlen($content));

    $response->setContent($content);
    return $response;
  }


}









