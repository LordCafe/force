<?php

namespace Drupal\custom_reg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\user\Entity\User;

/**
 * Class OpenPopUp.
 */
class OpenPopUp extends ControllerBase {

  /**
   *
   * @var Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('renderer')
    );
  }

  /**
   * Get User Register form.
   *
   */
  public function openSquadronList() {
    $options = [
      'width' => '500px',
      'height' => '500px',
      'dialogClass' => 'popup-class',
      'title' => 'Select Your Squadron'
    ];
    $form = \Drupal::formBuilder()->getForm('\Drupal\custom_reg\Form\SquadronAutoComplete');
    $html = \Drupal::getContainer()->get('renderer')->render($form);
    $ajax = new AjaxResponse();
    $ajax->addCommand(new OpenModalDialogCommand('', '<div class="custom-registration-form-wrapper clearfix">' . $html . '</div>', $options));
    return $ajax;
  }

  /**
   * Get User Register form.
   *
   */
  public function addressPopup($squadronNumber) {
    $options = [
      'width' => '500px',
      'height' => '500px',
      'dialogClass' => 'address-popup-field',
      'title' => 'Select Your Address'
    ];
    $form = \Drupal::formBuilder()->getForm('\Drupal\custom_reg\Form\AddressAutoCompleteForm', $squadronNumber);

    $html = \Drupal::getContainer()->get('renderer')->render($form);
    $ajax = new AjaxResponse();
     $ajax->addCommand(new OpenModalDialogCommand('', '<div class="custom-address-field-wrapper clearfix">' . $html . '</div>', $options));
    return $ajax;
  }

  public function viewAddressPopup($uid) {
    $options = [
      'width' => '500px',
      'height' => '500px',
      'dialogClass' => 'adress-popup',
      'title' => 'Address'
    ];
    $user = User::load($uid);
    $address = $user->get('field_recruiter_address')->getString();
    $html = $address;
    $ajax = new AjaxResponse();
     $ajax->addCommand(new OpenModalDialogCommand('', '<div class="address-link-wrapper clearfix">' . $html . '</div>', $options));
    return $ajax;
  }
  public function squadronAccess($uid) {
  $options = [
    'width' => '500px',
    'height' => '500px',
    'dialogClass' => 'squadron-access'
  ];
  $user = User::load($uid);
  $squadron = $user->get('field_registry_number')->getString();
  $html = '<p>Thank you for registering! We have received your request for Administrative Access for <span style="display:block"><strong>' . '[' . $squadron . ']' . '</strong>.</span> We will review your request and get in touch with you shortly.</p>';
  $ajax = new AjaxResponse();
   $ajax->addCommand(new OpenModalDialogCommand('', '<div class="squadron-access-popup clearfix">'. $html . '</div>', $options));
    return $ajax;
  }


  public function alterTitle() {
    return "Add/Edit Video";
  }

  public function migrateUserZipcodes() {
    $db = \Drupal::database();
    $userStorage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery();
    $uids = $query
      ->condition('status', '1')
      ->condition('roles', 'recruiter')
      ->execute();
    $users = $userStorage->loadMultiple($uids);
    foreach ($users as $key => $user) {
      $zipcodes = $user->get('field_zip_codes')->getString();
      $userId = $user->id();
      $zipArray = explode(',', $zipcodes);
      foreach ($zipArray as $key => $value) {
        if(is_numeric($value)) {
          $db->insert('user_zipcodes')->fields([
            'user_id' => $userId,
            'zipcode' => $value
            ])->execute();
        }
      }
    }
    die("Migrated");
  }
}
