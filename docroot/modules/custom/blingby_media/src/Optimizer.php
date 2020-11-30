<?php 

namespace Drupal\blingby_media;

class Optimizer {


  public function compress($image){
    if (!empty(exec('which pngquant'))) {
      $filename = md5(mt_rand());
      $tmp_path = file_directory_temp() .'/'. $filename .'.png';
      $img = imagecreatefromstring($image);
      imagealphablending($img, false);
      imagesavealpha($img, true);
      $file = imagepng($img, $tmp_path);

      if ($file) {
        $min_quality = 60;
        $max_quality = 85;
        $content = shell_exec("pngquant --quality={$min_quality}-$max_quality - < ".escapeshellarg($file));

        if ($content) {
          $image = $content;
        }
      }
    }
    return $image;
  }
}