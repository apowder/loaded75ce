<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class shoppingCart {
  var $order_id, $contents, $total, $total_tax, $weight, $content_type;

  function __construct($id = '') {
    $this->order_id = $id;
    $contents = array();
    $res = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");
    while ($d = tep_db_fetch_array($res)){
      $contents[$d['products_id']] = array('qty' => $d['products_quantity']);
    }
    $this->contents = $contents;
    $this->get_content_type();
  }

  function restore_contents() {
    return false;
  }

  function reset($reset_database = FALSE) {
    return false;
  }

  function add_cart($products_id, $qty = '', $attributes = '') {
    return false;
  }

  function update_quantity($products_id, $quantity = '', $attributes = '') {
    return false;
  }

  function cleanup() {
    return false;
  }

  function count_contents() {  // get total number of items in cart
    $res = tep_db_query("select sum(products_quantity) as total from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");
    $d = tep_db_fetch_array($res);
    return $d['total'];
  }

  function get_quantity($products_id) {
    return false;
  }

  function in_cart($products_id) {
    return false;
  }

  function remove($products_id) {
    return false;
  }

  function remove_all() {
    return false;
  }

  function get_product_id_list() {
    $product_id_list = '';
    if (is_array($this->contents)) {
      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $product_id_list .= ', ' . $products_id;
      }
    }

    return substr($product_id_list, 2);
  }

  function calculate() {
    if (!tep_not_null($this->order_id)){
      $products = $this->get_products();
      $this->total = 0;
      for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
        $this->total += $products[$i]['price'] * $products[$i]['quantity'];
      }
    }else{
      $this->total = 0;
      $this->weight = 0;
      $this->total_tax = 0;
      $res = tep_db_query("select sum(op.products_quantity*op.final_price) as total_price, sum(op.products_quantity*op.final_price* op.products_tax/100) as total_tax, sum(op.products_quantity*p.products_weight) as total_weight  from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p where p.products_id = op.products_id and orders_id = '" . $this->order_id . "'");
      $d = tep_db_fetch_array($res);
      $this->total = $d['total_price'];
      $this->total_tax = $d['total_tax'];
      $this->weight = $d['total_weight'];
    }
  }

  function attributes_price($products_id) {
    return false;
  }

  function get_products() {
    global $languages_id, $currency_id, $currencies;
    
    if (!isset($currency_id)){
      $currency_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
    }

    $products_array = array();
    if (!is_array($this->contents)) return false;
    foreach($this->contents as $products_id => $value){
      if (!tep_not_null($this->order_id)){
        $res = tep_db_query("select p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from  " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
      }else{
        $res = tep_db_query("select op.*, p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and op.orders_id = '" . $this->order_id . "' and p.products_id=op.products_id");
      }
      while ($products  = tep_db_fetch_array($res)){
        $prid = $products['products_id'];
        // changed by Art
        if (!tep_not_null($this->order_id)){
          $qty = $value['qty'];
          $products_price = \common\helpers\Product::get_products_price((int)$products_id, 1, 0, $currency_id);
          
          $special_price = \common\helpers\Product::get_products_special_price($prid);
          if ($special_price) {
            $products_price = $special_price;
          }
        }else{  
          $products_price = $products['products_price'];
          $qty = $products['products_quantity'];
        }

        $products_array[] = array('id' => $products_id,
                                  'name' => $products['products_name'],
                                  'model' => $products['products_model'],
                                  'image' => $products['products_image'],
                                  'price' => $products_price + $this->get_attributes_price($products_id, $value, $currency_id),
                                  'quantity' => $qty,
                                  'weight' => $products['products_weight'],
                                  'final_price' => $products['final_price'] + $this->get_attributes_price($products_id, $value, $currency_id),
                                  'tax_class_id' => $products['products_tax_class_id'],
                                  'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''));
      }
    }

    return $products_array;
  }
  
  function get_attributes_price($products_id, $value, $currency_id){
    if (!tep_not_null($this->order_id)){
      if (sizeof($value['attributes']) > 0){
        reset($value['attributes']);
        foreach($value['attributes'] as $option => $value){
          $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "' and options_id = '" . (int)$option . "' and options_values_id = '" . (int)$value . "'");
          $attribute_price = tep_db_fetch_array($attribute_price_query);
          $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'], 1, $currency_id);
          if ($attribute_price['price_prefix'] == '+' || $attribute_price['price_prefix'] == '') {
            $attributes_price += $attribute_price['options_values_price'];
          } else {
            $attributes_price -= $attribute_price['options_values_price'];
          }          
        }
      }
      return $attributes_price;
    }else{
      return 0;
    }
  }

  function show_total() {
    $this->calculate();

    return $this->total;
  }

  function show_weight() {
    $this->calculate();

    return $this->weight;
  }

  function unserialize($broken) {
    return false;
  }
  function get_content_type() {
    $this->content_type = false;
    $res = tep_db_query("select count(*) as total from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD  . " where orders_id='" . $this->order_id . "'");
    $d = tep_db_fetch_array($res);
    if ($d['total']==0){
      $this->content_type = 'physical';
    } else {
      if ($d['total']==count($this->contents)){
        $this->content_type = 'virtual';
      } else {
        $this->content_type = 'mixed';
      }
    }
    return $this->content_type;
  }
}
