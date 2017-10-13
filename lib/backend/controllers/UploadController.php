<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
/**
 *
 */
class UploadController extends Sceleton
{
  /**
   *
   */
  public function actionIndex()
  {
    if (isset($_FILES['file'])) {
      $path = \Yii::getAlias('@webroot');
      $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
      $uploadfile = $path . basename($_FILES['file']['name']);

      if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
        $text = '';
        $response = ['status' => 'ok', 'text' => $text];
      } else {
        $response = ['status' => 'error'];
      }
    }
    echo json_encode($response);
  }

  public function actionScreenshot()
  {
    if (isset($_POST['image'])) {
      $path = \Yii::getAlias('@webroot');
      $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;

      file_put_contents($path . 'screenshot-' . $_POST['theme_name'] . '.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['image'])));
    }
    echo $_POST['image'];
  }




}
