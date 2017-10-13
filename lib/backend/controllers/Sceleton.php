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
use yii\web\Controller;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Sceleton extends Controller {

    public $enableCsrfValidation = false;
    
    /**
     * @var array the breadcrumbs of the current page.
     */
    public $navigation = array();

    /**
     * @var array 
     */
    public $topButtons = array();

    /**
     * @var stdClass the variables for smarty.
     */
    public $view = null;
    
    /**
     * Access Control List
     * @var array current access level
     */
    public $acl = null;

    /**
     * Selected items in menu
     * @var array 
     */
    public $selectedMenu = array();
    
    function __construct($id,$module=null) {
        if (!is_null($this->acl)) {
            \common\helpers\Acl::checkAccess($this->acl);
        }
        $this->layout = 'main.tpl';
        \Yii::$app->view->title = \Yii::$app->name;
        $this->view = new \stdClass();
        parent::__construct($id,$module);
    }

    public function bindActionParams($action, $params)
    {
        if ($action->id == 'index') {
            \common\helpers\Translation::init('admin/' . $action->controller->id);
        } else {
            \common\helpers\Translation::init('admin/' . $action->controller->id . '/' . $action->id);
        }
        \common\helpers\Translation::init('admin/main');
        return parent::bindActionParams($action, $params);
    }
}