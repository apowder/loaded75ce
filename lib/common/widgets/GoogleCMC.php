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
use common\models\google\certifiedshop;

class GoogleCMC extends \yii\bootstrap\Widget
{
    public $order;
    
    public function init()
    {
        //echo '<pre>';print_r($this->order);
        parent::init();

    }
	
	public function run(){
    global $languages_id, $currencies;
    
    $_tax = $_total = $_shipping = $_coupon = 0;
    foreach($this->order->totals as $totals){
      if ($totals['class'] == 'ot_total')
      {
        $_total = number_format($currencies->format_clear($totals['value'], true, $this->order->info['currency'], $this->order->info['currency_value']), 2, ".", "");
      }
      else if ($totals['class'] == 'ot_tax') 
      {
        $_tax = number_format($currencies->format_clear($totals['value'], true, $this->order->info['currency'], $this->order->info['currency_value']), 2, ".", "");
      }
      else if ($totals['class'] == 'ot_shipping') 
      {
        $_shipping = number_format($currencies->format_clear($totals['value'], true, $this->order->info['currency'], $this->order->info['currency_value']), 2, ".", "");
      } 
      else if ($totals['class'] == 'ot_coupon') 
      {
        $_coupon = number_format($currencies->format_clear($totals['value'], true, $this->order->info['currency'], $this->order->info['currency_value']), 2, ".", "");
      }
    }
    
    $response = '
<!-- START Google Certified Shops Order -->
<div id="gts-order" style="display:none;" translate="no">

  <!-- start order and merchant information -->
  <span id="gts-o-id">' . $this->order->info['order_id'] . '</span>
  <span id="gts-o-domain">' . $_SERVER["HTTP_HOST"] . '</span>
  <span id="gts-o-email">' . $this->order->customer['email_address'] . '</span>
  <span id="gts-o-country">' . $this->order->customer['country']['iso_code_2'] . '</span>
  <span id="gts-o-currency">' . $this->order->info['currency'] . '</span>
  <span id="gts-o-total">' . $_total . '</span>
  <span id="gts-o-discounts">' . $_coupon . '</span>
  <span id="gts-o-shipping-total">' . $_shipping . '</span>
  <span id="gts-o-tax-total">' . $_tax . '</span>
  <span id="gts-o-est-ship-date">' . date("Y-m-d", strtotime("+3 day")) . '</span>
  <span id="gts-o-est-delivery-date">' . date("Y-m-d", strtotime("+3 day")) . '</span>
  <span id="gts-o-has-preorder">N</span>
  <span id="gts-o-has-digital">' . ($this->order->content_type == 'virtual' || $this->order->content_type == 'mixed' ? 'Y': 'N' ) . '</span>
  <!-- end order and merchant information -->

  <!-- start repeated item specific information -->
  <!-- item example: this area repeated for each item in the order -->
  ';
  $certifiedshop = new certifiedshop();
  $platform_id = PLATFORM_ID;
  $depending = tep_db_fetch_array(tep_db_query("select google_settings_id as id from " . TABLE_GOOGLE_SETTINGS . " where module = '" .  tep_db_input($certifiedshop->code). "' and platform_id = '" . $platform_id . "'"));
  $installed = Google::getInstalledModule($depending['id'], true);
  $params = $installed->getParams();
  $use_shopping_account = $params->config[$certifiedshop->code]['fields'][3]['value'];
  $shopping_accout_id =  $params->config[$certifiedshop->code]['fields'][4]['value'];
  for($i=0;$i<count($this->order->products);$i++){
     $response .= 
       '<span class="gts-item">
          <span class="gts-i-name">' . $this->order->products[$i]['name'] . '</span>
          <span class="gts-i-price">' . number_format($currencies->format_clear($this->order->products[$i]['final_price'], true, $this->order->info['currency'], $this->order->info['currency_value']), 2, ".", "") . '</span>
          <span class="gts-i-quantity">' . $this->order->products[$i]['qty'] . '</span>';
     if ($use_shopping_account){
       $response .= '<span class="gts-i-prodsearch-id">' . $this->order->products[$i]['id'] . '</span>';
     }
     if (tep_not_null($shopping_accout_id)){
       $response .= '
            <span class="gts-i-prodsearch-store-id">ITEM_GOOGLE_SHOPPING_ACCOUNT_ID</span>';       
     }          
     $response .= '</span>';
  }

  $response .= '<!-- end item 1 example -->
  <!-- end repeated item specific information -->

</div>
<!-- END Google Certified Shops Order -->    
';

  return $response;
	}
  

}
