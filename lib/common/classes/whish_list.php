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

class whish_list
{
  protected $contents = array();

  function __construct()
  {
    $this->reset();
  }

  function reset($reset_database = false)
  {
    global $customer_id;

    $this->contents = array();

    if (tep_session_is_registered('customer_id') && ($reset_database == true)) {
      tep_db_query("delete from " . TABLE_WISHLIST . " where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_WISHLIST_ATTRIBUTES. " where customers_id = '" . (int)$customer_id . "'");
    }
  }

  function cleanup()
  {

  }

  function restore_contents()
  {
    global $customer_id;

    if (!tep_session_is_registered('customer_id')) return false;

    if ( is_array($this->contents) ) {
      foreach($this->contents as $products_id=>$products_info ) {
        $qty = isset($products_info['qty'])?intval($products_info['qty']):1;
        $product_query = tep_db_query(
          "select products_id from " . TABLE_WISHLIST. " ".
          "where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'"
        );
        if (!tep_db_num_rows($product_query)) {
          tep_db_perform(TABLE_WISHLIST, array(
            'products_id' => $products_id,
            'customers_id' => $customer_id,
            'final_price' => '',
            'products_quantity' => max(1,$qty),
            'date_added' => 'now()',
          ));
          if (isset($products_info['attributes']) && is_array($products_info) ) {
            foreach( $products_info['attributes'] as $option=>$value ) {
              tep_db_perform(TABLE_WISHLIST_ATTRIBUTES, array(
                'customers_id' => $customer_id,
                'products_id' => $products_id,
                'products_options_id' => $option,
                'products_options_value_id'=> $value,
              ));
            }
          }
        }
      }
    }

    // reset per-session cart contents, but not the database contents
    $this->reset(false);


    $products_query = tep_db_query(
      "select products_id, products_quantity ".
      "from " . TABLE_WISHLIST. " ".
      "where customers_id = '" . (int)$customer_id . "'"
    ); // select only ordinary products
    while ($products = tep_db_fetch_array($products_query)) {
      $this->contents[$products['products_id']] = array(
        'qty' => $products['products_quantity'],
      );
      // attributes
      $attributes_query = tep_db_query(
        "select products_options_id, products_options_value_id ".
        "from " . TABLE_WISHLIST_ATTRIBUTES. " ".
        "where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products['products_id']) . "'"
      );
      while ($attributes = tep_db_fetch_array($attributes_query)) {
        $this->contents[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
      }
    }

    $this->cleanup();

  }

  function in_wish_list($products_id)
  {
    $products_id = \common\helpers\Inventory::normalize_id($products_id);
    return isset($this->contents[$products_id]);
  }

  protected function _persist_product($products_id)
  {
    global $customer_id;
    if (!tep_session_is_registered('customer_id')) return;

    if ($this->in_wish_list($products_id)) {
      $qty = 1;
      $product_query = tep_db_query(
        "select products_id from " . TABLE_WISHLIST. " ".
        "where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'"
      );
      if (!tep_db_num_rows($product_query)) {
        tep_db_perform(TABLE_WISHLIST, array(
          'products_id' => $products_id,
          'customers_id' => $customer_id,
          'final_price' => '',
          'products_quantity' => max(1,$qty),
          'date_added' => 'now()',
        ));
        if (isset($products_info['attributes']) && is_array($products_info) ) {
          foreach( $products_info['attributes'] as $option=>$value ) {
            tep_db_perform(TABLE_WISHLIST_ATTRIBUTES, array(
              'customers_id' => $customer_id,
              'products_id' => $products_id,
              'products_options_id' => $option,
              'products_options_value_id'=> $value,
            ));
          }
        }
      }
    }else{
      tep_db_query("delete from " . TABLE_WISHLIST . " where customers_id = '" . (int)$customer_id . "' and products_id='".tep_db_input($products_id)."'");
      tep_db_query("delete from " . TABLE_WISHLIST_ATTRIBUTES. " where customers_id = '" . (int)$customer_id . "' and products_id='".tep_db_input($products_id)."'");
    }
  }

  function add_product($products_id, $attributes)
  {
    $products_id = \common\helpers\Inventory::get_uprid($products_id, $attributes);
    if ( is_array($attributes) && count($attributes)>0 ) {
      $products_id = \common\helpers\Inventory::normalize_id($products_id);
    }

    if (!$this->in_wish_list($products_id)) {

      $this->contents[$products_id] = array(
        'qty' => 1,
      );
      if (is_array($attributes)) {
        reset($attributes);
        while (list($option, $value) = each($attributes)) {
          $this->contents[$products_id]['attributes'][$option] = $value;
        }
      }
      $this->_persist_product($products_id);
    }

  }
  
  function remove_product($products_id)
  {
    $products_id = \common\helpers\Inventory::normalize_id($products_id);
    if ($this->in_wish_list($products_id)) {
      unset($this->contents[$products_id]);
      $this->_persist_product($products_id);
    }
  }
  
  function remove_any_product_id($products_id)
  {
    $products_id = \common\helpers\Inventory::get_prid($products_id);
    foreach( array_keys($this->contents) as $list_uprid ) {
      if ( $products_id == \common\helpers\Inventory::get_prid($list_uprid) ) {
        unset($this->contents[$list_uprid]);
        $this->_persist_product($list_uprid);
      }
    }
  }
  
  function get_product_info($products_id)
  {
    $products_id = \common\helpers\Inventory::normalize_id($products_id);
    if ( !isset($this->contents[$products_id]) ) {
      return false;
    }else{
      return $this->contents[$products_id];  
    } 
    
  }

  function get_products()
  {

    global $languages_id, $HTTP_SESSION_VARS;
    $products_array = array();
    
    foreach($this->contents as $products_id=>$products_info ) {
      $products_query = tep_db_query(
        "select p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, ".
        "p.stock_indication_id, ".
        "p.products_price, p.products_weight, p.products_tax_class_id, p.products_file ".
        "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p ".
        " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' " . 
        "where p.products_id = '" . (int)$products_id . "' ".
        " and pd.affiliate_id = 0 and pd.products_id = p.products_id " . " and pd.language_id = '" . (int)$languages_id . "' "
      );

      if ( tep_db_num_rows($products_query)==0 ) continue;
      $products = tep_db_fetch_array($products_query);

      $prid = $products['products_id'];
      $products_price = \common\helpers\Product::get_products_price($products['products_id'], $this->contents[$products_id]['qty'], $products['products_price']);

      $special_price = \common\helpers\Product::get_products_special_price($prid, $this->contents[$products_id]['qty']);
      if ($special_price !== false) {
        $products_price = $special_price;
      }
      // inventory
      if (PRODUCTS_INVENTORY == 'True') {
        $r = tep_db_query("select products_model, stock_indication_id from " . TABLE_INVENTORY . " where products_id='" . \common\helpers\Inventory::normalize_id($products_id) . "'");
        if ($inventory = tep_db_fetch_array($r)) {
          if ($inventory['products_model']){
            $products['products_model'] = $inventory['products_model'];
          }
          if ($inventory['stock_indication_id']){
            $products['stock_indication_id'] = $inventory['stock_indication_id'];
          }
        }
      }
      // inventory eof

      $stock_info = \common\classes\StockIndication::product_info(array(
        'products_id' => \common\helpers\Inventory::normalize_id($products_id),
        'stock_indication_id' => $products['stock_indication_id'],
        'products_quantity' => (\common\helpers\Product::get_products_stock(\common\helpers\Inventory::normalize_id($products_id))-$this->contents[$products_id]['qty']),
      ));

      $product = array(
        'id' => $products_id,
        'name' => $products['products_name'],
        'model' => $products['products_model'],
        'image' => $products['products_image'],
        'price' => $products_price,
        'status' => !!\common\helpers\Product::check_product((int)$products_id),
        'quantity' => $this->contents[$products_id]['qty'],
        'stock_info' => $stock_info,
        'final_price' => ($products_price + $this->attributes_price($products_id, $this->contents[$products_id]['qty'])),
        'tax_class_id' => $products['products_tax_class_id'],
        'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : '')
      );


// {{ Products Bundle Sets
        if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'inProducts')) {
            list($bundles, $bundles_info) = $ext::inProducts($product);
            if (count($bundles) > 0) {
                $product['bundles'] = $bundles;
            }
            if (count($bundles_info) > 0) {
                $product['bundles_info'] = $bundles_info;
            }
        }
// }}
      if (isset($product['attributes']) && is_array($product['attributes'])){
        while (list($option, $value) = each($product['attributes'])) {

          $option_arr = explode('-', $option);
          $attributes = tep_db_query("select pa.products_id, pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                    from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                    where pa.products_id = '" . (int)($option_arr[1] > 0 ? $option_arr[1] : $product['id']) . "'
                                     and pa.options_id = '" . (int)$option_arr[0] . "'
                                     and pa.options_id = popt.products_options_id
                                     and pa.options_values_id = '" . (int)$value . "'
                                     and pa.options_values_id = poval.products_options_values_id
                                     and popt.language_id = '" . $languages_id . "'
                                     and poval.language_id = '" . $languages_id . "'");
          $attributes_values = tep_db_fetch_array($attributes);
          if ( !is_array($attributes_values) ) continue;

// {{ Products Bundle Sets
          $product['attr'][$option]['products_id'] = $attributes_values['products_id'];
// }}
          $product['attr'][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $product['attr'][$option]['options_values_id'] = $value;
          $product['attr'][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $product['attr'][$option]['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes_values['products_attributes_id']);
          $product['attr'][$option]['price_prefix'] = $attributes_values['price_prefix'];
        }
      }

// {{ Products Bundle Sets
      $product['is_bundle'] = false;
      if ( isset($product['bundles_info']) && is_array($product['bundles_info'])) {

        foreach ($product['bundles_info'] as $bpid => $bundle_info) {
          $product['bundles_info'][$bpid]['attr'] = array();

          if (isset($product['attr']) && is_array($product['attr']) && count($product['attr']) > 0) {
            foreach ($product['attr'] as $__option_id => $__option_value_data) {
              if (strpos($__option_id . '-', '-' . $bpid . '-') === false) continue;
              $product['bundles_info'][$bpid]['attr'][$__option_id] = $__option_value_data;
              unset($product['attr'][$__option_id]);
            }
          }
          $product['bundles_info'][$bpid]['with_attr'] = count($product['bundles_info'][$bpid]['attr']) > 0;
        }
        $product['is_bundle'] = true;
      }
// }}
      $products_array[] = $product;
    }
    
    return $products_array;
  }

  protected function attributes_price($products_id, $qty = 1)
  {
    $attributes_price = 0;

    if (isset($this->contents[$products_id]['attributes'])) {
      reset($this->contents[$products_id]['attributes']);
      while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
// {{ Products Bundle Sets
        $option_arr = explode('-', $option);
// }}
        $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)($option_arr[1] > 0 ? $option_arr[1] : $products_id) . "' and options_id = '" . (int)$option_arr[0] . "' and options_values_id = '" . (int)$value . "'");
        $attribute_price = tep_db_fetch_array($attribute_price_query);
        $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'], $qty);
        if ($attribute_price['price_prefix'] == '+' || $attribute_price['price_prefix'] == '') {
          $attributes_price += $attribute_price['options_values_price'];
        } else {
          $attributes_price -= $attribute_price['options_values_price'];
        }
      }
    }
    return $attributes_price;
  }

}