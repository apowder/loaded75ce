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

final class certifiedshop extends Google implements GoogleInterface {
	
	public $config;
	public $code = 'certifiedshop';

	public function getParams(){
		
		$this->config = [
			$this->code => [
				'name' => ' Google Certified Shops',
				'fields' => [
					[
					'name' => 'store_id',
					'value' => 'STORE_ID',
					'type' => 'text'
					],
					[
					'name' => 'badge_position',
					'value' => 'BOTTOM_RIGHT',
					'type' => 'text',
          'comment' => '<div class="ord-total"><div class="ord-total-info">BOTTOM_RIGHT, BOTTOM_LEFT, or USER_DEFINED</div></div>'
					],
					[
					'name' => 'badge_container',
					'value' => 'GTS_CONTAINER',
					'type' => 'text',
          'comment' => '<div class="ord-total"><div class="ord-total-info">An HTML element ID which you would like the Trusted Stores Bage to be injected into (if badge_position is USER_DEFINED)</div></div>',
					],
					[
					'name' => 'google_base_offer_id',
					'value' => '0',
					'type' => 'checkbox',
          'comment' => '<div class="ord-total"><div class="ord-total-info">Provide this field only if you submit feeds for Google Shopping (optional)</div></div>',
					],
					[
					'name' => 'google_base_subaccount_id',
					'value' => 'ITEM_GOOGLE_SHOPPING_ACCOUNT_ID',
					'type' => 'text',
          'comment' => '<div class="ord-total"><div class="ord-total-info">Provide this field only if you submit feeds for Google Shopping (optional)</div></div>',
					],
				],
				'pages' => [
					'all',
				],
				'priority' => 4,
        'example' => true,
			],
		];
		return $this->config;
	
	}
		
	public function renderWidget(){
    global $order, $request_type;
      $response = '';
      if ($request_type == 'SSL' && class_exists('\common\widgets\GoogleCMC') && class_exists('\common\classes\opc_order') && ($order instanceof opc_order || $order instanceof order)){
        if (strtolower(str_replace("-", "", \Yii::$app->controller->id)) == 'checkout' && \Yii::$app->controller->action->id == 'success'){
          $response .= \common\widgets\GoogleCMC::widget(['order'=> $order]);
        }          
      }   
    $response .= $this->getCode();
    return $response;
	}
  
	public function renderExample(){
    return preg_replace("/<script.*>/", "", preg_replace("/<\/script>/", "", nl2br($this->getCode())));
  }
  
	public function getCode(){
    global $request_type, $lng;
    
		$elements = $this->config[$this->code];
    $store_id = $elements['fields'][0]['value'];
    $badge_position = strtoupper($elements['fields'][1]['value']);
    if(is_object($lng)){
      $locale = $lng->language['locale'];
    } else {
      $locale = 'en_EN';
    }    
    $badge_container = 'gts.push(["badge_position", "' . $elements['fields'][2]['value'] . '"]);'."\n";
    $badge_container_div = '<div id="' . $elements['fields'][2]['value'] . '"></div>';
    if ($badge_position != 'USER_DEFINED'){
      $badge_container = '';
      $badge_container_div = '';
    }
    /*$google_base_offer_id = $elements['fields'][3]['value'];
    if (tep_not_null($google_base_offer_id)){
      $google_base_offer_id = 'gts.push(["google_base_offer_id", "' . $google_base_offer_id . '"]);'."\n";
    }*/
    $google_base_subaccount_id = $elements['fields'][4]['value'];
    if (tep_not_null($google_base_subaccount_id)){
      $google_base_subaccount_id = 'gts.push(["google_base_subaccount_id", "'. $google_base_subaccount_id. '"]);'."\n";
    }    
return <<<EOD
    <!-- BEGIN: Google Certified Shops -->
<script type="text/javascript">

  var gts = gts || [];

  gts.push(["id", "$store_id"]);
  gts.push(["badge_position", "$badge_position"]);
  $badge_container
  gts.push(["locale", "$locale"]);
  $google_base_subaccount_id

  (function() {
    var gts = document.createElement("script");
    gts.type = "text/javascript";
    gts.async = true;
    gts.src = "https://www.googlecommerce.com/trustedstores/api/js";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(gts, s);
  })();
</script>
<!-- END: Google Certified Shops -->
$badge_container_div
EOD;
   
	}
}