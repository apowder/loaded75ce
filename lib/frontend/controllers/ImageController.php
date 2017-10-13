<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\classes\Images;

/**
 * Image controller
 */
class ImageController extends Sceleton {

    public function actionIndex() {
        
    }
    
    public function actionPath() {
        $src = tep_db_prepare_input(Yii::$app->request->get('src'));
        \common\classes\Images::waterMark($src);
    }

    public function actionCached()
    {
      $this->layout = false;
      $requested_image = tep_db_prepare_input(Yii::$app->getRequest()->get('image',''));

      if ( !preg_match('@([^/]+)/(.*)/([^/]+)$@',$requested_image, $image_params) ) {
        // layout issue - need no response
        // throw new NotFoundHttpException();
        header("HTTP/1.0 404 Not Found");
        die;
      }
      $cache_key = $image_params[1];
      $partial_path = str_replace('.','',$image_params[2]);
      $image_filename = $image_params[3];

      $source_file = rtrim($partial_path,'/').'/'.$image_filename;

      if ( is_file(Images::getFSCatalogImagesPath().$source_file) ) {
        $output_file_mtime = filemtime(Images::getFSCatalogImagesPath().$source_file);
        $get_cache_data_r = tep_db_query("SELECT * FROM ".TABLE_IMAGE_CACHE_KEYS." WHERE external_key='".tep_db_input($cache_key)."' AND is_valid=1");
        if ( tep_db_num_rows($get_cache_data_r)>0 ) {
          $cache_data = tep_db_fetch_array($get_cache_data_r);
          if ( !empty($cache_data['extra_params']) ) {
            $cache_data['extra_params'] = unserialize(base64_decode($cache_data['extra_params']));
          }
          //$watermark_filename = Images::getFSCatalogImagesPath(). 'stamp' . DIRECTORY_SEPARATOR . $cache_data['watermark_image'];
          //if ( !is_file($watermark_filename) ) $watermark_filename = '';

          $output_file = Images::getFSCatalogImagesPath().'cached/'.$cache_key.'/'.$source_file;
          if ( !is_dir(dirname($output_file)) ) {
            try {
              \yii\helpers\BaseFileHelper::createDirectory(dirname($output_file), 0777);
            }catch (\Exception $ex){
              // make dir fail
            }
          }

          if (!empty($cache_data['watermark_image'])) {
              $watermark_filename = unserialize($cache_data['watermark_image']);
          }
          $image_content = Images::applyWatermark(Images::getFSCatalogImagesPath().$source_file, $watermark_filename, 'string');
          $tmp_file = tempnam(dirname($output_file), basename($output_file));
          if ($tmp_file_h = fopen($tmp_file,'wb')) {
            fwrite($tmp_file_h, $image_content);
            fclose($tmp_file_h);

            @chmod($tmp_file,0666);
            if ( $cache_data['watermark_mtime']>$output_file_mtime ) $output_file_mtime = $cache_data['watermark_mtime'];
            touch($tmp_file, $output_file_mtime);
            rename($tmp_file, $output_file);

            $size = @GetImageSize($output_file);
            header('Content-type: ' . $size['mime']);//image/png
            readfile($output_file);
            die;
          }else{
            $_image_info = array();
            $size = getimagesizefromstring($image_content, $_image_info);
            header('Content-type: ' . $size['mime']);
            echo $image_content;
            die;
          }
        }
      }
      header("HTTP/1.0 404 Not Found");
      die;
    }
    
}
