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
use \common\helpers\Translation;

class ClassesController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_CONFIGURATOR', 'BOX_CATALOG_CATEGORIES_CLASSES'];
    
    public function __construct($id, $module=null){
      Translation::init('admin/classes');
      parent::__construct($id, $module);
    }    
    
    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('catalog', 'configurator', 'classes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('classes/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        return $this->render('index');
    }

}
