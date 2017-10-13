<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models\google;

use common\models\Google;
use common\models\google\GoogleInterface;
use common\classes\platform;
use common\classes\opc_order;
use common\classes\order;
use common\models\google\analytics;

final class ecommerce extends Google implements GoogleInterface {
	
	public $config;
	public $code = 'ecommerce';

	public function getParams(){
		
		$this->config = [
			$this->code => [
				'name' => 'ECommerce Tracking',
				'fields' => [
				],
				'pages' => [
					'checkout',
				],
				'priority' => 2,
        'example' => true,
			],
		];
		return $this->config;
	
	}
		
	public function renderWidget(){
	global $order;

      if (class_exists('\common\widgets\GoogleCommerce') && class_exists('\common\classes\opc_order') && ($order instanceof opc_order || $order instanceof order)){
          return \common\widgets\GoogleCommerce::widget(['order'=> $order]);
      }
	}
  
	public function renderExample(){
    $platform = tep_db_fetch_array(tep_db_query("select platform_id from " . TABLE_GOOGLE_SETTINGS . " where google_settings_id = '" . (int)$_GET['id']. "'"));
    if ($platform){
      $_a = new analytics;
      $analytics = tep_db_fetch_array(tep_db_query("select google_settings_id as id from " . TABLE_GOOGLE_SETTINGS . " where module = '" .  tep_db_input($_a->code). "' and platform_id = '" . $platform['platform_id'] . "'"));
      $installed = Google::getInstalledModule($analytics['id'], true);
      if ($installed instanceof analytics){
        if ($installed->config[$_a->code]['type']['selected'] == 'ga'){
        return <<<EOD
          ga('require', 'ecommerce');<br>
<br>
          ga('ecommerce:addTransaction', {"id":"228517","affiliation":"Trueloaded","revenue":"7.05","shipping":"2.50","tax":"0.76"});<br>
ga('ecommerce:addItem', {"id":"228517","name":"Test product","sku":"402","category":"Category name","price":"3.79","quantity":"1"});<br>
ga('ecommerce:send', []);<br>
        
EOD;
          
        } else {
          return <<<EOD
          _gaq.push(['_addTrans', "228517", "Trueloaded", "7.05", "0.76", "2.50", "Swindon", "Wilshire", "United Kingdom"]);<br>
_gaq.push(['_addItem', "228517", "402", "Test product", "Category name", "3.79", "1"]);<br>
_gaq.push(['_trackTrans']);<br>
          
EOD;
        }
      } else {
        return 'Google Analytics ' . TEXT_FIELD_REQUIRED;
      }
    }
    return;
	}
}