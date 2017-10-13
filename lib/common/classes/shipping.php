<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

  class shipping {
    var $modules;
    private $include_modules = [];

// class constructor
    function __construct($module = '') {
      global $language, $PHP_SELF, $cart;

      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        if ( (tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
          reset($this->modules);
// Show either normal shipping modules or free shipping module when Free Shipping Module is On
          // Free Shipping Only
          if ( false && defined('MODULE_SHIPPING_FREESHIPPER_STATUS') && (MODULE_SHIPPING_FREESHIPPER_STATUS=='1' || MODULE_SHIPPING_FREESHIPPER_STATUS=='True') and $cart->show_weight()==0 ) {
            $include_modules[] = array('class'=> 'freeshipper', 'file' => 'freeshipper.php');
          } else {
          // All Other Shipping Modules
            while (list(, $value) = each($this->modules)) {
              $class = substr($value, 0, strrpos($value, '.'));
              // Don't show Free Shipping Module
              if (true || $class !='freeshipper') {
                $include_modules[] = array('class' => $class, 'file' => $value);
              }
            }
          }
        }
        
        \common\helpers\Translation::init('shipping');
        $this->include_modules = $include_modules;
        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {

 		  if (\frontend\design\Info::isTotallyAdmin()){
			include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
		  } else {
			include_once(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
		  }
          

          $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }
      }
    }
    
    function getIncludedModules(){
        return $this->include_modules;
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes, $order;

      $quotes_array = array();

      if (is_array($this->modules)) {
          
        $surcharge = 0;
        if (is_array($order->products)) {
            foreach ($order->products as $product) {
              $p_query = tep_db_query("select shipping_surcharge_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product['id'] . "'");
              $p_result = tep_db_fetch_array($p_query);
              if (isset($p_result['shipping_surcharge_price'])) {
                  $surcharge += $p_result['shipping_surcharge_price'] * $product['qty'];
              }
            }
        }
        
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (tep_not_null($module)) {
            if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
            $include_quotes[] = $class;
          }
        }

        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) {
              if ($surcharge > 0 && $quotes['id'] != 'freeshipper' && is_array($quotes['methods'])) {
                  foreach ($quotes['methods'] as $key => $value) {
                      $quotes['methods'][$key]['cost'] = $value['cost'] + $surcharge;
                  }
              }
              $quotes_array[] = $quotes;
          }
        }
      }

      return $quotes_array;
    }

    function cheapest() {
		global $select_shipping;
      if (is_array($this->modules)) {
        $rates = array();
		$exist_selected = false;
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (is_object($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
              if (isset($quotes['methods'][$i]['cost']) && is_numeric($quotes['methods'][$i]['cost'])) {
				if ($quotes['id'] . '_' . $quotes['methods'][$i]['id'] == $select_shipping) $exist_selected = true;
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
              }
            }
          }
        }
		
		if (!$exist_selected) $select_shipping = null;

        $cheapest = false;
        for ($i=0, $n=sizeof($rates); $i<$n; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }

        return $cheapest;
      }
    }
  }
?>
