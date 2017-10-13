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

class GoogleCommerce extends \yii\bootstrap\Widget
{
    public $order;
    public $_gaq = ['_addTrans' => [], '_addItem' => [], '_trackTrans' => []];
    public $ga = ['ecommerce:addTransaction' => [], 'ecommerce:addItem' => [], 'ecommerce:send' => []];

    
    public function init()
    {
        //echo '<pre>';print_r($this->order);
        parent::init();

    }
	
	public function run(){
    global $languages_id;
    
    $_tax = $_total = $_shipping = 0;
    foreach($this->order->totals as $totals){
      if ($totals['class'] == 'ot_total')
      {
        $_total = number_format($totals['value'], 2, ".", "");
      }
      else if ($totals['class'] == 'ot_tax') 
      {
        $_tax = number_format($totals['value'], 2, ".", "");
      }
      else if ($totals['class'] == 'ot_shipping') 
      {
        $_shipping = number_format($totals['value'], 2, ".", "");
      }
    }
    
    $this->_gaq['_addTrans'] = [
                            $this->order->info['order_id'],
                            \common\classes\platform::name($this->order->info['platform_id']),
                            $_total,
                            $_tax,
                            $_shipping,
                            $this->order->customer['city'],
                            $this->order->customer['state'],
                            $this->order->customer['country']['iso_code_3'],
                          ];
                          
    $this->ga['ecommerce:addTransaction'] = [
                            'id' => $this->order->info['order_id'],
                            'affiliation' => \common\classes\platform::name($this->order->info['platform_id']),
                            'revenue' => $_total,
                            'shipping'  => $_shipping,
                            'tax' => $_tax
                                      ];
                                      
    if (is_array($this->order->products)  && sizeof($this->order->products)){
      foreach($this->order->products as $item){
        
        $product_to_categories = tep_db_fetch_array(tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$item['id'] . "' limit 1"));
        $category_name = str_replace('"', '\"', \common\helpers\Categories::get_categories_name($product_to_categories['categories_id']));
        
         $this->_gaq['_addItem'][] = [
                            $this->order->info['order_id'],
                            $item['id'],
                            str_replace('"', '\"', $item['name']),
                            $category_name,
                            number_format($item['final_price'], 2, ".", ""),
                            $item['qty']
                               ];
        $this->ga['ecommerce:addItem'][] = [
                            'id' => $this->order->info['order_id'],
                            'name' => str_replace('"', '\"', $item['name']),
                            'sku' => $item['id'],
                            'category' => $category_name,
                            'price' => number_format($item['final_price'], 2, ".", ""),
                            'quantity' => $item['qty']
                               ];
        
      }
	  
    }
   
    return $this->renderJs();
	}
  
  public function renderJs(){
    ob_start();
    //
    ?>
    <script>
    tl(function(){
      var type = '';
    
      window.onload = function(){
        if (typeof ga != 'undefined' && typeof ga.P == 'object'){
          //check id
          var _tracker = ga.getByName('t0');
          var _account = _tracker.b.get('trackingId');
          if ( _tracker.b.data.values.hasOwnProperty(':trackingId') && _account.length > 0 && _account.indexOf('UA') > -1){
            type = 'ga';
          } 
          
        } else if (typeof _gaq != 'undefined'){
          var _tracker = _gaq._getAsyncTracker();
          var _account = _tracker._getAccount();
          if ( _account.length > 0 && _account.indexOf('UA') > -1){
            type = '_gaq';
          }
        } else { //notifie admin to set up analytics
          $.post('checkout/notify-admin', {
            'type': 'need_analytics',
          }, function(data, status){
            
          });
        }

        if (type == 'ga'){
          ga('require', 'ecommerce');
          
          <?php 
            foreach($this->ga as $key => $item){
              if (!count(array_filter($item, 'is_array'))){
                echo 'ga(\'' . $key . '\', ' . json_encode($item) . ');'."\r\n";
              } else {
                foreach($item as $item1){
                  echo 'ga(\'' . $key . '\', ' . json_encode($item1) . ');'."\r\n";
                }
              }
            }
          ?>
          localStorage.removeItem('ga_cookie');
        } else if (type == '_gaq'){
          <?php 
            foreach($this->_gaq as $key => $item){
              if (!count(array_filter($item, 'is_array'))){
                if (count($item) > 0){
                  echo '_gaq.push([\'' . $key . '\', "' . implode('", "', $item) . '"]);'."\r\n";
                } else {
                  echo '_gaq.push([\'' . $key . '\']);'."\r\n";
                }                
              } else {
                foreach($item as $item1){
                   echo '_gaq.push([\'' . $key . '\', "' . implode('", "', $item1) . '"]);'."\r\n";
                }
              }
              
            }
          ?>          
        }
      }
    });
    </script>
    <?php
    $buf = ob_get_contents();
    ob_clean();
    return $buf;
  }

}
