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
 * default controller to handle user requests.
 */
class LoggingController extends Sceleton  {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOGGING'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'logging');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('logging/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
      
    }
    
}
