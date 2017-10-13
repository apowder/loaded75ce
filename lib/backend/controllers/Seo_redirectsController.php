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
use common\extensions\SeoRedirects\SeoRedirects;
use common\classes\platform;

class Seo_redirectsController extends Sceleton {

    public $acl = ['BOX_HEADING_SEO_CMS', 'BOX_HEADING_REDIRECTS'];
    
    public function __construct($id, $module = null) {
        if (false === \common\helpers\Acl::checkExtension('SeoRedirects', 'allowed')) {
            $this->redirect(array('/'));
        }
        \common\helpers\Translation::init('admin/seo_redirects');
        
        parent::__construct($id, $module);
    }
    

    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('seo_cms', 'seo_redirects');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('seo_redirects/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="javascript:void(0)" onClick="edit(0);" class="create_item"><i class="icon-file-text"></i>' . TEXT_NEW . '</a>';
        $this->view->headingTitle = HEADING_TITLE;

        $platforms = platform::getList(false);
            
        $this->view->RedirectsTable = array(
            array(
                'title' => TABLE_HEADING_OLD_URL,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_NEW_URL,
                'not_important' => 0,
            ),
        );
        return $this->render('index', [
				'platforms' => $platforms,
				'first_platform_id' => platform::firstId(),
                                'default_platform_id' => platform::defaultId(),
				'isMultiPlatforms' => platform::isMulti(),
		  ]);
    }

    public function actionList() {
        global $languages_id, $language;       

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_page_number = ($start / $length) + 1;
        $platform_id = Yii::$app->request->get('platform_id');

        $responseList = SeoRedirects::getAllItems($platform_id);
                
        $response = array(
            'draw' => $draw,
            'recordsTotal' => count($responseList),
            'recordsFiltered' => count($responseList),
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionItempreedit() {
        global $languages_id, $language;

        $item_id = (int) Yii::$app->request->post('item_id', 0);
        
        $cInfo = SeoRedirects::getItem($item_id);

        return $this->renderAjax('view', ['cInfo' => $cInfo]);
    }

    public function actionEdit() {
        $item_id = (int) Yii::$app->request->get('item_id', 0);
        $platform_id = (int) Yii::$app->request->get('platform_id', 0);
        
        $cInfo = SeoRedirects::getItem($item_id, $platform_id);
        
        return SeoRedirects::renderForm($cInfo);
    }
    
    public function actionSubmit(){
        $response = [];
        $item_id = Yii::$app->request->post('item_id', 0);
        if (SeoRedirects::saveItem($_POST)){
            $response['message'] = ($item_id?TEXT_MESSEAGE_SUCCESS:TEXT_MESSEAGE_SUCCESS_ADDED);
            $response['messageType'] = 'alert-success';
        } else {
            $response['message'] = TEXT_MESSAGE_ERROR;
            $response['messageType'] = 'alert-error';
        }
        echo json_encode($response);
        exit();
    }
    
    public function actionDelete(){
        $this->layout = false;
        
        $item_id = Yii::$app->request->post('item_id', 0);
        if ($item_id){
            SeoRedirects::deleteItem($item_id);
        }
        echo '1';
        exit();
    }

}
