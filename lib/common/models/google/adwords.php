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

final class adwords extends Google implements GoogleInterface {
	
	public $config;
	public $code = 'adwords';

	public function getParams(){
		
		$this->config = [
			$this->code => [
				'name' => 'Google Adwords (Remarketing)',
				'fields' => [
					[
					'name' => 'code',
					'value' => '',
					'type' => 'text'
					],
					[
					'name' => 'language',
					'value' => 'en',
					'type' => 'text'
					],        
					[
					'name' => 'color',
					'value' => 'ffffff',
					'type' => 'text'
					],          
					[
					'name' => 'label',
					'value' => '',
					'type' => 'text'
					],         
				],
				'pages' => [
					'checkout',
				],
				'priority' => 3,
        'example' => true,
			],
		];
		return $this->config;
	
	}
		
	public function renderWidget(){
		$elements = $this->config[$this->code];
		$code = $elements['fields'][0]['value'];
    $language = $elements['fields'][1]['value'];
    $color = $elements['fields'][2]['value'];
    $label = $elements['fields'][3]['value'];
    
    global $order;
    $currency = 'GBP';
    if (is_object($order)){
      $currency = $order->info['currency'];
    }    
		if (tep_not_null($code)){
$_return = '
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = ' . $code . ';
var google_custom_params = window.google_tag_params;
var google_conversion_format = "3";
var google_conversion_value = 1.00;
var google_remarketing_only = false;
';
if (!tep_not_null($language)){
  $_return .= 'var google_conversion_language = "' . $language. '";'."\n";
}
if (tep_not_null($color)){
  $_return .= 'var google_conversion_color = "' . $color . '";'."\n";
}
if (tep_not_null($label)){
  $_return .= 'var google_conversion_label = "' . $label . '";'."\n";
}
if (tep_not_null($currency)){
  $_return .= 'var google_conversion_currency = "' . $currency . '";'."\n";
}
$_return .= '
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/' .$code. '/?value=1.00&amp;currency_code=' . $currency . '&amp;label=' . $label . '&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
';
return $_return;
		}
    return;
	}

	public function renderExample(){
    $installed = Google::getInstalledModule($_GET['id'], true);
    if ($installed){
      $code = $installed->config[$this->code]['fields'][0]['value'];
  return <<<EOD
var google_conversion_id = $code;<br>
var google_custom_params = window.google_tag_params;<br>
var google_remarketing_only = false;<br>
var google_conversion_format = "3";<br>
var google_conversion_value = 1.00;<br>
var google_conversion_language = "en";<br>
var google_conversion_color = "ffffff";<br>
var google_conversion_currency = "GBP";<br>
var google_conversion_label = "myy_CJeu8QcQofSa5AM";<br>
EOD;
    }
    return;
	}	
}