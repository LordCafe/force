<?php

// @docksal Drush alias
$aliases['docksal'] = array(
  'root' => '/var/www/docroot',
  'uri' => 'drupal8.docksal',
);

$aliases['newtestv2'] = array(
  'root' => '/var/www/newtest-v2/docroot',
  'uri' => 'newtest-v2.blingby.com',
  'path-aliases' =>
    array (
      '%drush-script' => 'drush8',
    ),
);
