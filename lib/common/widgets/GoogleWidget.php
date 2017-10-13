<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\widgets;

use common\models\Google;
use frontend\design\Info;

class GoogleWidget extends \yii\bootstrap\Widget
{
    public function init()
    {
        parent::init();
    }
	
	public function run(){
    
		if (Info::isAdmin()) return;

		$modules = Google::getInstalledModules(\common\classes\platform::currentId());
		$to_work = [];$priority = [];
		if (is_array($modules)){
			foreach($modules as $_module){
				$module = Google::getInstalledModule($_module['google_settings_id']);
				if ($module){
					$_pages = $module->getAvailablePages();
					if (in_array('checkout', $_pages) && strtolower(str_replace("-", "", \Yii::$app->controller->id)) == 'checkout'){
						if (\Yii::$app->controller->action->id == 'success'){
							$priority[$_module['module']] = $module->getPriority();
							$to_work[$_module['module']] = $module;						
						}					
					} else if (in_array(strtolower(str_replace("-", "", \Yii::$app->controller->id)), $_pages) || in_array('all', $_pages)){
						$priority[$_module['module']] = $module->getPriority();
						$to_work[$_module['module']] = $module;					
					}
				}
				//
			}
		}
		if (is_array($priority)){
			asort($priority);
			foreach($priority as $_module => $value){
				echo $to_work[$_module]->renderWidget();
			}
		}
   
	}
}
