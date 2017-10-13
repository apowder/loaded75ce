<?php
namespace common\classes;

if ( !defined('MAX_CART_QTY') ) define('MAX_CART_QTY',99999);

class StockIndication
{

  static public function get_variants($with_empty=true)
  {
    $lang_id = (int)$_SESSION['languages_id'];
    $key = $lang_id.'^'.($with_empty?'1':'0');
    static $fetched = array();
    if ( !isset($fetched[$key]) ) {
      $fetched[$key] = array();
      $get_variants_r = tep_db_query(
        "SELECT s.stock_indication_id AS id, st.stock_indication_text AS text, ".
        " IF(LENGTH(st.stock_indication_short_text)>0,st.stock_indication_short_text,st.stock_indication_text) AS text_short, ".
        " s.* ".
        "FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " s " .
        " LEFT JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " st ON st.stock_indication_id=s.stock_indication_id AND st.language_id='{$lang_id}' " .
        "ORDER BY s.sort_order"
      );
      if (tep_db_num_rows($get_variants_r) > 0) {
        if ($with_empty) {
          $fetched[$key][] = array(
            'id' => '',
            'text' => STOCK_INDICATION_BY_QTY_IN_STOCK,
          );
        };
        $bool_list = array('is_default', 'allow_out_of_stock_checkout', 'allow_out_of_stock_add_to_cart', 'allow_in_stock_notify', 'disable_product_on_oos', 'request_for_quote');
        while ($_variant = tep_db_fetch_array($get_variants_r)) {
          foreach($bool_list as $bool_field) {
            $_variant[$bool_field] = !!$_variant[$bool_field];
          }
          $fetched[$key][] = $_variant;
        }
      }

    }
    return $fetched[$key];
  }

  public static function sortStockIndicators(array $ids)
  {
      $_def_stock_id = 0;
      $stock_ids = array();
      foreach(self::get_variants(false) as $_stock_variant) {
          $stock_ids[] = (int)$_stock_variant['id'];
          if ( $_stock_variant['is_default'] ) $_def_stock_id = (int)$_stock_variant['id'];
      }
      usort($ids,function($a, $b) use($stock_ids, $_def_stock_id){
          if ( empty($a) ) $a = $_def_stock_id;
          if ( empty($b) ) $b = $_def_stock_id;
          $index_a = array_search((int)$a, $stock_ids);
          $index_b = array_search((int)$b, $stock_ids);
          if ( $index_a===false || $index_b===false ) {
              if ( $index_a===$index_b ) {
                  return 0;
              }elseif ( $index_a===false ) {
                  return -1;
              }
              return 1;
          }else{
              return $index_a-$index_b;
          }
      });
      return $ids;
  }

  public static function product_info($data_array)
  {
    $cart_qty = isset($data_array['cart_qty'])?(int)$data_array['cart_qty']:0;
    $on_cart_page = (isset($data_array['cart_class']) && $data_array['cart_class']);
    $stock_indication_id = isset($data_array['stock_indication_id'])?intval($data_array['stock_indication_id']):0;
    $stock_delivery_terms_id = isset($data_array['stock_delivery_terms_id'])?intval($data_array['stock_delivery_terms_id']):0;
    $is_virtual = isset($data_array['is_virtual'])?intval($data_array['is_virtual']):null;
    if ( empty($stock_indication_id) || empty($stock_delivery_terms_id) ) {
      if ( strpos($data_array['products_id'],'{')!==false ) {
        $uprid = \common\helpers\Inventory::normalize_id($data_array['products_id']);
        $get_from_inventory_r = tep_db_query("SELECT stock_indication_id, stock_delivery_terms_id FROM ".TABLE_INVENTORY." WHERE prid='".(int)$uprid."' AND products_id='".tep_db_input($uprid)."'");
        if ( tep_db_num_rows($get_from_inventory_r)>0 ) {
          $_from_inventory = tep_db_fetch_array($get_from_inventory_r);
          $stock_indication_id = $_from_inventory['stock_indication_id'];
          $stock_delivery_terms_id = $_from_inventory['stock_delivery_terms_id'];
        }
      }
    }
    if ( empty($stock_indication_id) || empty($stock_delivery_terms_id) || is_null($is_virtual) ) {
      $get_from_product_r = tep_db_query("SELECT stock_indication_id, stock_delivery_terms_id, is_virtual FROM ".TABLE_PRODUCTS." WHERE products_id='".(int)$data_array['products_id']."'");
      if ( tep_db_num_rows($get_from_product_r)>0 ) {
        $_from_product = tep_db_fetch_array($get_from_product_r);
        if ( empty($stock_indication_id) ) {
          $stock_indication_id = $_from_product['stock_indication_id'];
        }
        if ( empty($stock_delivery_terms_id) ) {
          $stock_delivery_terms_id = $_from_product['stock_delivery_terms_id'];
        }
        $is_virtual = $_from_product['is_virtual'];
      }
    }
    $data_array['stock_indication_id'] = $stock_indication_id;
    $data_array['stock_delivery_terms_id'] = $stock_delivery_terms_id;
    if ( $is_virtual ) {
      return array(
        'stock_code' => 'in-stock',
        'max_qty' => MAX_CART_QTY,
        'products_quantity' => MAX_CART_QTY,
        'stock_indicator_text' => TEXT_IN_STOCK,
        'stock_indicator_text_short' => TEXT_IN_STOCK,
        'allow_out_of_stock_checkout' => true,
        'allow_out_of_stock_add_to_cart' => true,
        'order_instock_bound' => false,
        'flags' => array(
          'notify_instock' => false,
          'add_to_cart' => true,
          'can_add_to_cart' => true,
          'request_for_quote' => false,
        ),
      );
    }

    $stock_info = array(
      'stock_code' => 'out-stock',
      'text_stock_code' => 'out-stock',
      'max_qty' => MAX_CART_QTY,
      'stock_indicator_text' => TEXT_OUT_STOCK,
      'stock_indicator_text_short' => TEXT_OUT_STOCK,
      'allow_out_of_stock_add_to_cart' => true,
      'allow_out_of_stock_checkout' => (defined('STOCK_CHECK') && STOCK_CHECK != 'true') || (defined('STOCK_ALLOW_CHECKOUT') && STOCK_ALLOW_CHECKOUT == 'true'),
      'order_instock_bound' => false,
      'products_quantity' => $data_array['products_quantity'],
      'flags' => array(
          'notify_instock' => false,
          'add_to_cart' => true,
          'can_add_to_cart' => true,
          'request_for_quote' => false,
      ),
    );

    $stock_info_pre_lookup = false;
    foreach( self::get_variants(false) as $variant) {
      if ( $variant['is_default'] ) {
        $stock_info_pre_lookup = $variant;
      }
      if ($data_array['stock_indication_id']==$variant['id']){
        $stock_info_pre_lookup = $variant;
        break;
      }
    }
    $delivery_terms_pre_lookup = false;
    foreach( self::get_delivery_terms(false) as $variant) {
      if ( $variant['is_default'] ) {
        $delivery_terms_pre_lookup = $variant;
        $delivery_terms_pre_lookup['stock_code'] = $variant['stock_code']?$variant['stock_code']:'out-stock';
        $delivery_terms_pre_lookup['text_stock_code'] = $variant['text_stock_code']?$variant['text_stock_code']:'out-stock';
      }
      if ($data_array['stock_delivery_terms_id']==$variant['id']){
        $delivery_terms_pre_lookup = $variant;
        $delivery_terms_pre_lookup['stock_code'] = $variant['stock_code']?$variant['stock_code']:'out-stock';
        $delivery_terms_pre_lookup['text_stock_code'] = $variant['text_stock_code']?$variant['text_stock_code']:'out-stock';
        break;
      }
    }

    $instock_condition = $cart_qty>0?(($data_array['products_quantity']-$cart_qty)>=0):($data_array['products_quantity']>0);
    //if ( ($instock_condition || ($on_cart_page && $data_array['products_quantity']>0)) && !$stock_info_pre_lookup['request_for_quote'] ) {
    if ( $stock_indication_id==0 && ($instock_condition || ($on_cart_page && $data_array['products_quantity']>0)) ) {
      //$stock_info['stock_code'] = 'in-stock';
      $stock_info['max_qty'] = (int)$data_array['products_quantity'];
      //$stock_info['stock_indicator_text'] = TEXT_IN_STOCK;
      //$stock_info['stock_indicator_text_short'] = TEXT_IN_STOCK;
      $stock_info['allow_out_of_stock_checkout'] = true;
      if ( !$instock_condition ) {
        $stock_info['order_instock_bound'] = true;
      }
    }else{
      $stock_info = $stock_info_pre_lookup;
      //$stock_info['stock_indicator_text'] = $stock_info['text'];
      //$stock_info['stock_indicator_text_short'] = $stock_info['text_short'];
      unset($stock_info['text']);
      unset($stock_info['text_short']);

      $stock_info['flags'] = array(
        'notify_instock' => $stock_info['allow_in_stock_notify'],
        'add_to_cart' => $stock_info['allow_out_of_stock_add_to_cart'],
        'can_add_to_cart' => $stock_info['allow_out_of_stock_add_to_cart'],
        'request_for_quote' => $stock_info['request_for_quote'],
      );
      if ( !$stock_info['allow_out_of_stock_add_to_cart'] ) {
        $stock_info['max_qty'] = 0;
      }else{
        $stock_info['max_qty'] = MAX_CART_QTY;
      }
    }
    if ( $cart_qty>0 && !$stock_info['flags'] ) {
      $stock_info['max_qty'] = (int)$data_array['products_quantity'];
    }
    //if ( ($instock_condition || ($on_cart_page && $data_array['products_quantity']>0)) && !$stock_info_pre_lookup['request_for_quote'] ) {
    if ( $stock_delivery_terms_id==0 && ($instock_condition || ($on_cart_page && $data_array['products_quantity']>0)) ) {
      $stock_info['stock_code'] = 'in-stock';
      $stock_info['text_stock_code'] = 'in-stock';
      $stock_info['stock_indicator_text'] = TEXT_IN_STOCK;
      $stock_info['stock_indicator_text_short'] = TEXT_IN_STOCK;
    } else {
      $stock_info['stock_indicator_text'] = $delivery_terms_pre_lookup['text'];
      $stock_info['stock_indicator_text_short'] = $delivery_terms_pre_lookup['text_short'];
      $stock_info['stock_code'] =  $delivery_terms_pre_lookup['stock_code'];
      $stock_info['text_stock_code'] =  $delivery_terms_pre_lookup['text_stock_code'];
    }
//echo '<BR><PRE>';print_r($delivery_terms_pre_lookup);
    return $stock_info;
  }

  public static function productDisableByStockIds()
  {
    $ids = array();
    foreach( self::get_variants(false) as $variant) {
      if ( !$variant['disable_product_on_oos'] ) continue;
      $ids[(int)$variant['id']] = (int)$variant['id'];
    }
    return $ids;
  }
  
  public static function get_delivery_terms($with_empty=true)
  {
    $lang_id = (int)$_SESSION['languages_id'];
    $key = $lang_id.'^'.($with_empty?'1':'0');
    static $fetched = array();
    if ( !isset($fetched[$key]) ) {
      $fetched[$key] = array();
      $get_variants_r = tep_db_query(
        "SELECT dt.stock_delivery_terms_id AS id, dtt.stock_delivery_terms_text AS text, ".
        " IF(LENGTH(dtt.stock_delivery_terms_short_text)>0,dtt.stock_delivery_terms_short_text,dtt.stock_delivery_terms_text) AS text_short, ".
        " dt.* ".
        "FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " dt " .
        " LEFT JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " dtt ON dtt.stock_delivery_terms_id=dt.stock_delivery_terms_id AND dtt.language_id='{$lang_id}' " .
        "ORDER BY dt.sort_order"
      );
      if (tep_db_num_rows($get_variants_r) > 0) {
        if ($with_empty) {
          $fetched[$key][] = array(
            'id' => '',
            'text' => TEXT_DEFAULT_VALUE,
          );
        };
        $bool_list = array('is_default');
        while ($_variant = tep_db_fetch_array($get_variants_r)) {
          foreach($bool_list as $bool_field) {
            $_variant[$bool_field] = !!$_variant[$bool_field];
          }
          $fetched[$key][] = $_variant;
        }
      }

    }
    return $fetched[$key];
  }
}