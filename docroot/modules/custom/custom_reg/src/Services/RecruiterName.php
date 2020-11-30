<?php

namespace Drupal\custom_reg\Services;


/**
 * Returns nearest Recruiter name by zipcode
 */

class RecruiterName {
  /**
   * Constructs a new CustomService object.
   */
  public function __construct() {

  }

  public function getRecruiterName($zipcode) {
    $db = \Drupal::database();
    $zipQuery = $db->select('zipcodes', 'z');
    $res = $zipQuery->fields('z', ['zip','lat', 'lng'])
    ->condition('zip', $zipcode)
    ->execute()
    ->fetchAssoc();
    if(empty($res)) {
      return null;
    }
    $sql = "SELECT user_zipcodes.user_id, user_zipcodes.zipcode
    from {zipcodes}  inner join {user_zipcodes}
    on zipcodes.zip = user_zipcodes.zipcode where ( 3959  * acos( cos( radians(:lat) )
    * cos( radians( lat ) )
    * cos( radians( lng )
      - radians(:lng) )
    + sin( radians(:lat) )
    * sin( radians( lat ) ) ) ) < :dist and zipcodes.zip != :queryzip limit 1";
    $nearByRes = $db->query($sql, [':queryzip' => $zipcode, ':dist' => 15, ':lat' => $res['lat'], ':lng' => $res['lng']])->fetchAssoc();
    return !empty($nearByRes) ? $nearByRes['user_id'] : null;
    }

}
