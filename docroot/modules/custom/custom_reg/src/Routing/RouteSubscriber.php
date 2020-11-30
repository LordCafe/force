<?php

namespace Drupal\custom_reg\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change the route associated with the user profile page (/user, /user/{uid}).
    if ($route = $collection->get('node.add')) {
      $route->setDefault('_title_callback', '\Drupal\custom_reg\Controller\OpenPopUp::alterTitle');
    }
  }

}