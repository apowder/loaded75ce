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
use common\models\Google;

/**
 * default controller to handle user requests.
 */
class Google_analyticsController extends Sceleton  {
    
    public $acl = ['BOX_HEADING_SEO_CMS', 'BOX_HEADING_GOOGLE_ANALYTICS'];
	
	public function __construct($id, $module=''){
		\common\helpers\Translation::init('admin/google_analytics');	
		parent::__construct($id, $module);
	}

    public function actionIndex() {
		global $language;
		  
		$this->selectedMenu = array('seo_cms', 'google_analytics');
		$this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('google_analytics/index'), 'title' => HEADING_TITLE);
		  
		$this->view->headingTitle = HEADING_TITLE;

		$this->view->tabListReserved = [];
		$this->view->tabList = [];
		
		$platforms = \common\classes\platform::getList(false);
		
		Google::checkReservedInstalled();

		if (is_array($platforms)){
			foreach($platforms as $_platform){
				$this->view->tabList[$_platform['id']] = [
						array(
							'title'         => 'Module Name',
							'not_important' => 0,
						),
						array(
							'title'         => TABLE_HEADING_STATUS,
							'not_important' => 3
						),
                                                array(
							'title'         => TABLE_HEADING_ACTION,
							'not_important' => 0
						),
				];		
			}
		}	
	   
		if (is_array(Yii::$app->session->getAllFlashes())){
			foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
				Yii::$app->controller->view->errorMessage = $message;
				Yii::$app->controller->view->errorMessageType = $key;
			}
		  }

		   Yii::$app->session->removeAllFlashes();
		  
		  return $this->render('index', [
				'platforms'=> $platforms,
				'first_platform_id'=>\common\classes\platform::firstId(),
				'isMultiPlatform'=>\common\classes\platform::isMulti(),
				'reserved' => Google::getAllReservedKeys(),
		  ]);      
	}
  
	public function actionList(){
		global $languages_id, $language, $PHP_SELF, $login_id;
		$draw = Yii::$app->request->get('draw', 1);
		$search = Yii::$app->request->get('search', '');
		$start = Yii::$app->request->get('start', 0);
		$length = Yii::$app->request->get('length', 15);
		$platform_id = Yii::$app->request->get('platform_id', 0);
		
		$modules = [];
		$_visible = [];
		
		if ($platform_id){
			$installed = Google::getInstalledModules($platform_id);
			
			if (is_array($installed) && count($installed)){
				//echo '<pre>';print_r($installed);
				foreach($installed as $_key => $module){
					$priority[$_key] = $installed[$_key]['info'][$_key]['priority'];
				}
				asort($priority);
				$_tmp = [];
				foreach($priority as $_key => $s_order){
					$_tmp[$_key] = $installed[$_key];
				}
				$installed = $_tmp;
//echo '<pre>';print_r($installed);
				foreach($installed as $module){
					$_visible[] = $module['module'];
					$modules[] = array(
						'<div class="simple_row click_double"><div class="module_title' . ($module['status'] ? '' :' dis_module') . '">' . $module['module_name'] . tep_draw_hidden_field('module', $module['module'], 'class="cell_identify" data-installed="true"') . '</div></div>',
						'<input name="enabled" type="checkbox" data-module="' . $module['module'] . '" data-platform_id="' .$platform_id. '" class="check_on_off" ' . ($module['status'] ? 'checked' :'') . '><script>BootstrapIt(\'' . $module['module'] . '\', ' .$platform_id. ')</script>',
						'<a href="' . \yii\helpers\Url::to(['google_analytics/settings', 'id' => $module['google_settings_id']]) . '" class="btn btn-primary btn-small" title="' . IMAGE_EDIT . '">' . IMAGE_EDIT . '</a>&nbsp;<button class="btn btn-small" onClick="changeModule(\'' . $module['module'] . '\', ' . $platform_id . ', \'remove\')" title="' . TEXT_REMOVE . '">' . TEXT_REMOVE . '</button>'
					); 
				}			
			}
			$uninstalled = Google::getUninstalledModules();
			if (is_array($uninstalled) && count($uninstalled)) {
				foreach($uninstalled as $code => $module){
					if (array_key_exists ($code, $installed)) unset($uninstalled[$code]);
				}
			}
			if (is_array($uninstalled) && count($uninstalled)) {
				if (count($modules)){
					$modules[] = array( '<span class="modules_divider"></span>', '<span class="modules_divider"></span>', '<span class="modules_divider"></span>');
				}
				foreach($uninstalled as $code => $module){
					$_visible[] = $code;
					$modules[] = array(
						'<div class="simple_row click_double"><div class="module_title dis_module">' . $module['name'] . tep_draw_hidden_field('module', $code, 'class="cell_identify" data-installed="true"') . '</div></div>',
                                                '',
						'<button class="btn btn-default btn-small" onClick="changeModule(\'' . $code . '\', ' . $platform_id . ', \'install\')">' . IMAGE_INSTALL . '</button>'
					); 				
				}
			}		
		}
		
		$response = array(
			'draw' => $draw,
			'recordsTotal' => count($_visible),
			'recordsFiltered' => count($_visible),
			'data' => $modules,
			'head' => new \stdClass(),
			);
		 echo json_encode($response);
	}
	  
	public function actionChange(){
		$action = Yii::$app->request->post('action');
		$module = Yii::$app->request->post('module');
		$platform_id = Yii::$app->request->post('platform_id', 0);
		$status = Yii::$app->request->post('status', 'false') == 'true' ? 1 : 0;
		if ($platform_id){
			Google::perform($module, $action, $platform_id, $status);
		}
		echo 'ok';
	}
	  
	public function actionSettings(){
		global $language;

		$id = Yii::$app->request->get('id', 0);
		  
		$this->selectedMenu = array('seo_cms', 'google_analytics');
		$this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('google_analytics/index'), 'title' => HEADING_TITLE);
		  
		$this->view->headingTitle = HEADING_TITLE;
		
		$context = '';
		$module = Google::getInstalledModule($id);
		
		if ($module){
			$context = $module->render();
		}
		
		return $this->render('edit.tpl', ['context' => $context, 'id'=>$id]);
	}
	   	
	public function actionSave(){
    
		if (Yii::$app->request->isPost){
		
			$id = Yii::$app->request->post('id', 0);
			
			$module = Google::getInstalledModule($id, false);

			if ($module && $module->loaded()){
				$module->save($id);
			}

			Yii::$app->session->setFlash('success', ICON_SUCCESS);
		}
		
		return $this->redirect('index');
	}
	
	public function actionSubmit(){
		if (Yii::$app->request->isPost){
			$settings = tep_db_prepare_input(Yii::$app->request->post('settings'));
			if (is_array($settings)){
				foreach($settings as $id => $value){
					Google::saveRow(['google_settings_id' => $id, 'code' => $value]);
				}
				Yii::$app->session->setFlash('success', ICON_SUCCESS);
			}
			
		}
		return $this->redirect('index');
	}

}
