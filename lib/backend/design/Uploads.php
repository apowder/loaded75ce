<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design;

class Uploads
{

  public static function move($file_name, $folder = 'images', $show_path = true)
  {
    $path = \Yii::getAlias('@webroot');
    $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    $upload_file = $path . $file_name;

    if (is_file($upload_file)) {
      $folders_arr = explode('/', $folder);
      $path2 = \Yii::getAlias('@webroot');
      $path2 .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
      $path3 = '';
      foreach ($folders_arr as $item) {
        $path2 .= $item . DIRECTORY_SEPARATOR;
        $path3 .= $item . DIRECTORY_SEPARATOR;
        if (!file_exists($path2)) {
          mkdir($path2);
        }
      }

      $copy_file = $file_name;
      $i = 1;
      $dot_pos = strrpos($copy_file, '.');
      $end = substr($copy_file, $dot_pos);
      $temp_name = $copy_file;
      while (is_file($path2 . $temp_name)) {
        $temp_name = substr($copy_file, 0, $dot_pos) . '-' . $i . $end;
        $temp_name = str_replace(' ', '_', $temp_name);
        $i++;
      }

      @copy($upload_file, $path2 . $temp_name);
      @unlink($upload_file);

      return ($show_path ? $path3 : '') . $temp_name;
    } else {
      return false;
    }
  }

  public static $archiveImages = [];

  public static function addArchiveImages($name, $value){

    $image = $value;
    $image_ = $value;
    if ($name == 'background_image' || $name == 'logo' ){
      $path_arr = explode(DIRECTORY_SEPARATOR, $value);
      $image = end($path_arr);
      if (count($path_arr) == 1){
        $old = 'images' . DIRECTORY_SEPARATOR . $value;
      } else {
        $old = $value;
      }
      foreach (self::$archiveImages as $item){
        if ($old == $item['old']){
          return $item['new'];
        }
      }

      $change = false;
      $i = 1;
      $dot_pos = strrpos($image, '.');
      $end = substr($image, $dot_pos);
      $temp_name = $image;
      while (!$change){
        $has_name = false;
        foreach (self::$archiveImages as $item){
          if ($temp_name == $item['new']){
            $has_name = true;
            break;
          }
        }
        if (!$has_name){
          $change = true;
          $image = $temp_name;
          $image_ = '$$' . $temp_name;
        }
        $temp_name = substr($image, 0, $dot_pos) . '-' . $i . $end;
        $i++;
      }
      self::$archiveImages[] = ['old' => $old, 'new' => $image];
    }

    return $image_;
  }

}
