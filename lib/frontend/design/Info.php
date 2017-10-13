<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design;

use Yii;
use common\classes\Images;

class Info
{

  public static function isAdmin()
  {
    $params = Yii::$app->request->get();
    
    if (strpos(Yii::$app->request->headers['referer'], '/admin/design')
     || strpos(Yii::$app->request->headers['referer'], '/admin/categories')
     || strpos(Yii::$app->request->headers['referer'], '/admin/platforms' )
	 || strpos(Yii::$app->request->headers['referer'], '/admin/google_analytics' )
	 || strpos(Yii::$app->request->headers['referer'], '/admin/orders' )
     || $params['is_admin']){
      return true;
    } else {
      return false;
    }

  }
  public static function isTotallyAdmin()
  {
    if (strpos(Yii::$app->request->getUrl(), '/admin' ) !== false){
      return true;
    } else {
      return false;
    }
  }
  
  public static function isAdminOrders()
  {
    if (strpos(Yii::$app->request->headers['referer'], '/admin/orders' )){
      return true;
    } else {
      return false;
    }
  }

  public static function dataClass($class)
  {

    if (Info::isAdmin()){
      return ' data-class="' . $class . '"';
    } else {
      return '';
    }

  }


  public static function getProducts($products_query)
  {
    global $currencies;

    $products = array();
    while ($products_arr = tep_db_fetch_array($products_query)) {

      $products_arr['id'] = $products_arr['products_id'];
      $special_price = \common\helpers\Product::get_products_special_price($products_arr['products_id']);
      if ($special_price){
        $products_arr['price_old'] = $currencies->display_price(\common\helpers\Product::get_products_price($products_arr['products_id'], 1, $products_arr['products_price']), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
        $products_arr['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
      } else {
        $products_arr['price'] = $currencies->display_price(\common\helpers\Product::get_products_price($products_arr['products_id'], 1, $products_arr['products_price']), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
      }
      $products_arr['link'] = tep_href_link('catalog/product', 'products_id=' . $products_arr['products_id']);
      $products_arr['link_buy'] = tep_href_link('catalog/product', 'action=buy_now&products_id=' . $products_arr['products_id']);
      $products_arr['action_buy'] = tep_href_link('catalog/product', 'products_id=' . $products_arr['products_id'] . '&action=add_product');
      $products_arr['image'] = Images::getImageUrl($products_arr['products_id'], 'Small');
      $products_quantity = \common\helpers\Product::get_products_stock($products_arr['products_id']);
      $products_arr['product_qty'] = $products_quantity;
      $products_arr['product_has_attributes'] = \common\helpers\Attributes::has_product_attributes($products_arr['products_id']);
      $products_arr['product_in_cart'] = Info::checkProductInCart($products_arr['products_id']);
      if ($products_arr['products_description'])$products_arr['products_description'] = strip_tags($products_arr['products_description']);
      //if ($products_arr['products_description_short'])$products_arr['products_description_short'] = strip_tags($products_arr['products_description_short']);
      $products_arr['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($products_arr['products_id'],$products_arr);
      $products_arr['stock_indicator'] = \common\classes\StockIndication::product_info(array(
        'products_id' => (int)$products_arr['products_id'],
        'is_virtual' => $products_arr['is_virtual'],
        'products_quantity' => $products_quantity,
        'stock_indication_id' => (isset($products_arr['stock_indication_id'])?$products_arr['stock_indication_id']:null),
      ));
      if ($products_arr['stock_indicator']['flags']['request_for_quote']){
        $products_arr['price'] = '';
        $products_arr['price_special'] = '';
        $products_arr['price_old'] = '';
      }
      $products_arr['stock_indicator']['quantity_max'] = \common\helpers\Product::filter_product_order_quantity((int)$products_arr['products_id'], $products_arr['stock_indicator']['max_qty'], true);

      $products_arr['properties'] = self::getProductProperties($products_arr['products_id']);

      $products[] = $products_arr;
    }
    return $products;
  }


  public static function getProductProperties($products_id){

    $properties_array = array();
    $values_array = array();
    $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_listing = '1' and p2p.products_id = '" . (int)$products_id . "'");
    while ($properties = tep_db_fetch_array($properties_query)) {
      if (!in_array($properties['properties_id'], $properties_array)) {
        $properties_array[] = $properties['properties_id'];
      }
      $values_array[$properties['properties_id']][] = $properties['values_id'];
    }
    $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);
    return $properties_tree_array;
  }


  public static function getProductsRating($products_id, $field = 'rating'){

    $rating_query = tep_db_query("select count(*) as count, AVG(reviews_rating) as average from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and status");
    $rating = tep_db_fetch_array($rating_query);
    
    if ($field == 'count') {
      return $rating['count'];
    } else {
      return round($rating['average']);
    }
  }


  public static function getStyle($theme_name, $tmp = false, $visibility = 0)
  {

    $styles_query = tep_db_query("select * from " . (Info::isAdmin() || $tmp ? TABLE_THEMES_STYLES_TMP : TABLE_THEMES_STYLES) . " where theme_name = '" . $theme_name . "' and visibility='" . $visibility . "'");

    $styles_array = array();
    $styles_groups = array();
    while ($item = tep_db_fetch_array($styles_query)){

      $styles_groups[$item['selector']][0][$item['attribute']] = $item['value'];

    }

    foreach ($styles_groups as $selector => $style){
      $text = \frontend\design\Block::styles($style);
      $styles_array[$selector] = $text;
    }

    $css = '';
    if ($visibility == 1 || $visibility == 2 || $visibility == 3 || $visibility == 4) {
      $add = '';
      switch ($visibility) {
        case 1: $add = ':hover'; break;
        case 2: $add = '.active'; break;
        case 3: $add = ':before'; break;
        case 4: $add = ':after'; break;
      }
      foreach ($styles_array as $key => $value){
        $key_arr = explode(',', $key);
        $selector_arr = array();
        foreach ($key_arr as $item){
          $selector_arr[] = trim($item) . $add;
        }
        $selector = implode(', ', $selector_arr);
        if ($value) {
          $css .= $selector . '{' . $value . '} ';
        }
      }
    } else {
      foreach ($styles_array as $key => $value){
        if ($value) {
          $css .= $key . '{' . $value . '} ';
        }
      }
    }
    
    if ($visibility == 0){
      $css .= self::getStyle($theme_name, $tmp, 1);
      $css .= self::getStyle($theme_name, $tmp, 2);
      $css .= self::getStyle($theme_name, $tmp, 3);
      $css .= self::getStyle($theme_name, $tmp, 4);

      $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . THEME_NAME . "' and setting_name = 'media_query'");
      while ($item = tep_db_fetch_array($media_query_arr)){
        $arr = explode('w', $item['setting_value']);
        $css .= '@media';
        if ($arr[0]){
          $css .= ' (min-width:' . $arr[0] . 'px)';
        }
        if ($arr[0] && $arr[1]){
          $css .= ' and ';
        }
        if ($arr[1]){
          $css .= ' (max-width:' . $arr[1] . 'px)';
        }
        $css .= '{';
        //$css .= $block_styles[$item['id']];
        $css .= self::getStyle($theme_name, $tmp, $item['id']);
        $css .= '} ';
      }
    }

    return $css;

  }


  public static function widgetsArr($name, $include_blocks = false)
  {
    $widgets = array();

    $query = tep_db_query("select id, widget_name from " . TABLE_DESIGN_BOXES . " where theme_name = '" . THEME_NAME . "' and block_name = '" . $name . "'");

    while ($item = tep_db_fetch_array($query)) {

      $settings = array();
      $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and visibility = '0'");
      while ($set = tep_db_fetch_array($settings_query)) {
        $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
      }
      
      $controller = Yii::$app->controller->id;
      $action = Yii::$app->controller->action->id;

      if (
        $controller == 'index' && $action == 'index' && $settings[0]['visibility_home'] ||
        $controller == 'catalog' && $action == 'product' && $settings[0]['visibility_product'] ||
        $controller == 'catalog' && $action == 'index' && $settings[0]['visibility_catalog'] ||
        $controller == 'info' && $action == 'index' && $settings[0]['visibility_info'] ||
        $controller == 'cart' && $action == 'index' && $settings[0]['visibility_cart'] ||
        $controller == 'checkout' && $action != 'success' && $settings[0]['visibility_checkout'] ||
        $controller == 'checkout' && $action == 'success' && $settings[0]['visibility_success'] ||
        $controller == 'account' && $action != 'login' && $settings[0]['visibility_account'] ||
        $controller == 'account' && $action == 'login' && $settings[0]['visibility_login']
      ){
      } elseif(
        !($controller == 'index' && $action == 'index' ||
          $controller == 'index' && $action == 'design' ||
          $controller == 'catalog' && $action == 'product' ||
          $controller == 'catalog' && $action == 'index' ||
          $controller == 'info' && $action == 'index' ||
          $controller == 'cart' && $action == 'index' ||
          $controller == 'checkout' && $action != 'success' ||
          $controller == 'checkout' && $action == 'success' ||
          $controller == 'account' && $action != 'login' ||
          $controller == 'account' && $action == 'login') &&
        $settings[0]['visibility_other']
      ) {
      } else {
        if ($item['widget_name'] != 'BlockBox' && $item['widget_name'] != 'Tabs' || $include_blocks) {
          $widgets['id-' . $item['id']] = $item['widget_name'];
        }
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id']));
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-1'));
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-2'));
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-3'));
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-4'));
        $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-5'));
      }
    }

    return $widgets;

  }


  public static function pageBlock() {
    global $current_category_id;

    $controller = Yii::$app->controller->id;
    $action = Yii::$app->controller->action->id;

    $block_name = '';
    if ($controller == 'index' && $action == 'index'){
      $block_name = 'main';
    } elseif ($controller == 'info' && $action == 'index'){
      $block_name = 'info';
    } elseif ($controller == 'shopping-cart' && $action == 'index'){
      $block_name = 'cart';
    } elseif ($controller == 'contact' && $action == 'index'){
      $block_name = 'contact';
    } elseif ($controller == 'checkout' && $action == 'success'){
      $block_name = 'success';
    } elseif ($controller == 'catalog' && ($action == 'product')){
      $block_name = 'product';
    } elseif ($controller == 'catalog' && $action == 'index'){
      $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and categories_status = 1");
      $category_parent = tep_db_fetch_array($category_parent_query);
      $parent = ($_GET['manufacturers_id'] ? '0' : $category_parent['total'] );
      if ($parent > 0){
        $block_name = 'categories';
      } else {
        $block_name = 'products';
      }
    } elseif ($controller == 'catalog' && (
        $action == 'advanced-search-result' ||
        $action == 'advanced-search' ||
        $action == 'featured_products' ||
        $action == 'products_new' ||
        $action == 'all-products' ||
        $action == 'specials'
      )){
        $block_name = 'products';
    } elseif ($controller == 'email-template' && $action == 'index'){
        $block_name = 'email';
    } elseif ($controller == 'email-template' && $action == 'packingslip'){
        $block_name = 'packingslip';
    } elseif ($controller == 'email-template' && $action == 'invoice'){
        $block_name = 'invoice';
    }
    
    return $block_name;
  }
  
  public static function widgets()
  {
    $widgets = array();

    $widgets = array_merge($widgets, Info::widgetsArr('header'));
    $widgets = array_merge($widgets, Info::widgetsArr('footer'));

    $block_name = Info::pageBlock();

    if ($block_name){
      $widgets = array_merge($widgets, Info::widgetsArr($block_name));
    }

    return $widgets;

  }


  public static function widgetSettings($widget_name = false, $setting_name = false, $block_name = false, $include_blocks = false) {

    $settings = array();
    
    if (!$block_name){
      $block_name = Info::pageBlock();
    }
    
    $widgets = Info::widgetsArr($block_name, $include_blocks);
    if ($widget_name){

      $query = tep_db_query("select setting_name, setting_value, language_id from " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where 	box_id = '" . str_replace('id-', '', array_search($widget_name, $widgets)) . "'" . ($setting_name ? " and setting_name = '" . $setting_name . "'" : '') . " and visibility = '0'");

      $settings = array();
      while ($item = tep_db_fetch_array($query)){
        $settings[$item['language_id']][$item['setting_name']] = $item['setting_value'];
      }
      if ($setting_name) {
        $settings = $settings[0][$setting_name];
      }
      
    } else {
      foreach ($widgets as $key => $box){

        $query = tep_db_query("select setting_name, setting_value, language_id from " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where 	box_id = '" . str_replace('id-', '', $key) . "'" . ($setting_name ? " and setting_name = '" . $setting_name . "'" : '') . " and visibility = '0'");

        while ($item = tep_db_fetch_array($query)){
          $settings[$box][] = array($item[$item['language_id']]['setting_name'] => $item['setting_value']);
        }
        if ($setting_name) {
          $settings[$box] = $settings[$setting_name];
        }
      }
    }

    
    

    return $settings;
  }


  public  static function platformData($platform_id = PLATFORM_ID){
    $query1 = tep_db_fetch_array(tep_db_query("
      select 
        platform_id as id,
        platform_owner as owner,
        platform_name as title,
        platform_url as url,
        platform_email_address as email_address,
        platform_email_from as email_from,
        platform_email_extra as email_extra,
        platform_telephone as telephone,
        platform_landline as landline
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . $platform_id . "'"));

    $query2 = tep_db_fetch_array(tep_db_query("
      select
        	entry_company as company,
        	entry_company_vat as company_vat,
        	entry_postcode as postcode,
        	entry_street_address as street_address,
        	entry_suburb as suburb,
        	entry_city as city,
        	entry_state as state,
        	entry_country_id as country_id,
        	entry_zone_id as zone_id,
          entry_company_reg_number as reg_number
      from " . TABLE_PLATFORMS_ADDRESS_BOOK . "
      where platform_id = '" . $platform_id . "' and is_default = 1"));

    if ($query1 && $query2){

      $query2['country'] = \common\helpers\Country::get_country_name($query2['country_id']);
      $query2['country_info'] = \common\helpers\Country::get_country_info_by_id($query2['country_id']);

      $times = array();
      $query_db = tep_db_query("
      select 
        open_days as days,
        open_time_from as time_from,
        open_time_to as time_to
      from " . TABLE_PLATFORMS_OPEN_HOURS . "
      where platform_id = '" . $platform_id . "'");
      while ($item = tep_db_fetch_array($query_db)){
        $day = str_replace('0,', '', $item['days']);
        $day = str_replace('1', TEXT_DAY_MO, $day);
        $day = str_replace('2', TEXT_DAY_TU, $day);
        $day = str_replace('3', TEXT_DAY_WE, $day);
        $day = str_replace('4', TEXT_DAY_TH, $day);
        $day = str_replace('5', TEXT_DAY_FR, $day);
        $day = str_replace('6', TEXT_DAY_SA, $day);
        $day = str_replace('7', TEXT_DAY_SU, $day);
        $day = str_replace(',', ', ', $day);
        $item['days_short'] = $day;
        if (str_replace('0,', '', $item['days']) == '1,2,3,4,5') $item['days_short'] = TEXT_DAY_MO . ' - ' . TEXT_DAY_FR;
        $times['open'][] = $item;
      }

      $data = array_merge($query1, $query2, $times);

      return $data;
    } else {
      return array();
    }

  }


  public  static function themeFile($file_path, $visibility = 'ws'){

    global $themeMap;

	/*if (self::isAdminOrders()){
		$url = '../themes/basic' . $file_path;
	} else {
		$url = DIR_WS_THEME . $file_path;
	}    */
	$url = DIR_WS_THEME . $file_path;
    for ($i = count($themeMap) - 1; $i >= 0; $i--){

      if (file_exists(DIR_FS_CATALOG . 'themes/' . $themeMap[$i] . $file_path)) {
        if ($visibility == 'ws') {
          $url = DIR_WS_CATALOG . 'themes/' . $themeMap[$i] . $file_path;
        } elseif ($visibility == 'fs'){
          $url = DIR_FS_CATALOG . 'themes/' . $themeMap[$i] . $file_path;
        }
      }
    }
    
    return $url;
  }


  public static function blockWidthMultiplier($id){
    $query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id='" . $id . "'"));

    if (substr($query['block_name'], 0, 5) != 'block'){
      return false;
    }

    $id_arr = explode('-', substr($query['block_name'], 6));
    if ($id_arr[1]){
      $col = $id_arr[1];
    } else {
      $col = 1;
    }
    $parent_id = $id_arr[0];
    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='block_type' and visibility = '0'"));
    $type = $query['setting_value'];

    $multiplier = 1;
    if ($type == 2 || $type == 8 && $col == 2){
      $multiplier = 0.5;
    } elseif ($type == 9 && $col == 1 || $type == 10 && $col == 2 || $type == 13 && ($col == 1 || $col == 3) || $type ==15){
      $multiplier = 0.2;
    } elseif ($type == 6 && $col == 1 || $type == 7 && $col == 2 || $type == 8 && ($col == 1 || $col == 3) || $type == 14){
      $multiplier = 0.25;
    } elseif ($type == 3 || $type == 4 && $col == 2 || $type == 5 && $col == 1){
      $multiplier = 0.3333;
    } elseif ($type == 11 && $col == 1 || $type == 12 && $col == 2){
      $multiplier = 0.4;
    } elseif ($type == 11 && $col == 2 || $type == 12 && $col == 1 || $type == 13 && $col == 2){
      $multiplier = 0.6;
    } elseif ($type == 4 && $col == 1 || $type == 5 && $col == 2){
      $multiplier = 0.6666;
    } elseif ($type == 6 && $col == 2 || $type == 7 && $col == 1){
      $multiplier = 0.75;
    } elseif ($type == 9 && $col == 2 || $type == 10 && $col == 1){
      $multiplier = 0.8;
    }

    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='padding_left' and visibility = '0'"));
    $padding = $query['setting_value'];
    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='padding_right' and visibility = '0'"));
    $padding = $padding + $query['setting_value'];

    $arr = Info::blockWidthMultiplier($parent_id);
    if (!$arr){
      $arr = array();
    }
    $arr[] = array('multiplier' => $multiplier, 'padding' => $padding);

    return $arr;
  }
  
  public static function blockWidth($id, $p_width = 680){
    $p_width_arr = (array)Info::blockWidthMultiplier($id);
    foreach ($p_width_arr as $item1) {
      if (!$item1['multiplier']) $item1['multiplier'] = 1;
      $p_width = ($p_width - $item1['padding']) * $item1['multiplier'];
    }
    return floor($p_width);
  }
  
  public static function themeSetting($setting_name, $setting_group = 'main', $theme_name = ''){
    
    if (!$theme_name && $_GET['theme_name']){
      
      $theme_name = $_GET['theme_name'];
      
    } elseif (defined("THEME_NAME")) {
      
      $theme_name = THEME_NAME;
      
    } elseif (!$theme_name) {
      
      return false;
      
    }

    $db_query = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where setting_group = '" . $setting_group . "' and setting_name = '" . $setting_name ."' and theme_name = '" . $theme_name . "'");
    if (tep_db_num_rows($db_query) > 1 || $setting_group == 'extend'){

      $arr = array();
      while ($item = tep_db_fetch_array($db_query)){
        $arr[] = $item['setting_value'];
      }
      return $arr;

    } else {
      $query = tep_db_fetch_array($db_query);
      return $query['setting_value'] ? $query['setting_value'] : false;
    }
  }

  public static function listType($settings){
    
    $list_type = 'type-1';
    
    if ($settings['list_type']){
      $list_type = $settings['list_type'];
    } else {
      $gl = $_SESSION['gl'];
      if ($gl == 'grid' && $settings['listing_type'] && $settings['listing_type'] != 'no'){
        $list_type = $settings['listing_type'];
      } elseif (($gl == 'list' || $settings['listing_type'] == 'no') && $settings['listing_type_rows'] && $settings['listing_type_rows'] != 'no') {
        $list_type = $settings['listing_type_rows'];
      } elseif ($gl == 'b2b' && $settings['listing_type_b2b']) {
        $list_type = $settings['listing_type_b2b'];
      } elseif ($settings['listing_type']) {
        $list_type = $settings['listing_type'];
      }
      
    }
    
    return $list_type;
  }


  public static function checkProductInCart($uprid){

    global $cart;
	
	if (\frontend\design\Info::isAdmin()) return ;

    $products = $cart->get_products();

    $response = false;

    $attr1 = array();
    $tmp = explode ('{', $uprid);
    $attr1[0] = $tmp[0];
    for($i = 1; $i <= count($tmp); $i++){
      $tmp2 = explode ('}', $tmp[$i]);
      $attr1[$tmp2[0]] = $tmp2[1];
    }

    foreach ($products as $item) {
      $attr2 = array();
      $tmp = explode ('{', $item['id']);
      $attr2[0] = $tmp[0];
      for($i = 1; $i <= count($tmp); $i++){
        $tmp2 = explode ('}', $tmp[$i]);
        $attr2[$tmp2[0]] = $tmp2[1];
      }

      if (count(array_diff_assoc($attr1, $attr2)) == 0) {
        $response = true;
        break;
      }
    }

    return $response;

  }
  
  public static function platformLanguages(){
    $query = tep_db_fetch_array(tep_db_query("
      select defined_languages
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . PLATFORM_ID . "'"));
    if (isset($query['defined_languages']) && tep_not_null($query['defined_languages'])){
      $check_status = tep_db_query("select code from " . TABLE_LANGUAGES . " where code in ('" . implode("','", explode(",", $query['defined_languages'])) . "') and languages_status = 1");
      if (tep_db_num_rows($check_status) == 0) return false;
      $_pl = [];
      while($row = tep_db_fetch_array($check_status)){
        $_pl[] = strtolower($row['code']);
      }
      return $_pl;
    }
    return false;
  }
  
  public static function platformDefLanguage(){
    $query = tep_db_fetch_array(tep_db_query("
      select default_language
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . PLATFORM_ID . "'"));
    if (isset($query['defined_languages']) && tep_not_null($query['defined_languages'])){
      $check_status = tep_db_query("select code from " . TABLE_LANGUAGES . " where code = '" . $query['default_language'] . "' and languages_status = 1");
      if (tep_db_num_rows($check_status) == 0) return false;      
    }    
    return $query['default_language'];
  }  
  
  public static function platformCurrencies(){
    $query = tep_db_fetch_array(tep_db_query("
      select defined_currencies
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . PLATFORM_ID . "'"));
    if (!tep_not_null($query['defined_currencies'])) return false;
    return explode(',', $query['defined_currencies']);
  }   
  
  public static function platformDefCurrency(){
    $query = tep_db_fetch_array(tep_db_query("
      select default_currency
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . PLATFORM_ID . "'"));
    if (!tep_not_null($query['default_currency'])) return false;
    return $query['default_currency'];
  }

  public static function get_gl(){
    return $_SESSION['gl'];
  }

  public static function fonts(){
    $fonts = self::themeSetting('font_added', 'extend');

    $css = '';
    if (is_array($fonts)) {
      foreach ($fonts as $font) {
        $css .= $font . "\n";
      }
    }

    return $css;
  }

  public static function sortingId(){

    $get = Yii::$app->request->get();
    $sorting_id = '';
    if ($get['sort']){
      $sorting_id = $get['sort'];
    } else {
      $settings = self::widgetSettings('ListingFunctionality');

      $arr = array();
      for ($i=0; $i < 15; $i++){
        if (!$settings[0]['sort_hide_' . $i]){
          $arr[$settings[0]['sort_pos_' . $i]] = 'sort_pos_' . $i;
        }
      }
      ksort($arr);
      $key = array_shift($arr);
      switch ($key){
        case 'sort_pos_0': $sorting_id = 0; break;
        case 'sort_pos_1': $sorting_id = 'ma'; break;
        case 'sort_pos_2': $sorting_id = 'md'; break;
        case 'sort_pos_3': $sorting_id = 'na'; break;
        case 'sort_pos_4': $sorting_id = 'nd'; break;
        case 'sort_pos_5': $sorting_id = 'ba'; break;
        case 'sort_pos_6': $sorting_id = 'bd'; break;
        case 'sort_pos_7': $sorting_id = 'pa'; break;
        case 'sort_pos_8': $sorting_id = 'pd'; break;
        case 'sort_pos_9': $sorting_id = 'qa'; break;
        case 'sort_pos_10': $sorting_id = 'qd'; break;
        case 'sort_pos_11': $sorting_id = 'wa'; break;
        case 'sort_pos_12': $sorting_id = 'wd'; break;
        case 'sort_pos_13': $sorting_id = 'da'; break;
        case 'sort_pos_14': $sorting_id = 'dd'; break;
      }
    }


    return $sorting_id;
  }

  public static function themeImage($img, $alternative_images = false, $na = true){

    if (defined('THEME_NAME')){
      $app = Yii::getAlias('@webroot') . '/';
    } else {
      $app = Yii::getAlias('@webroot') . '/../';
    }
    
    if (is_file($app . $img)) {
      return $img;
    } 
    
    if (is_file($app . 'images/' . $img)) {
      return 'images/' . $img;
    }

    if ($alternative_images && is_array($alternative_images)){
      foreach ($alternative_images as $image){
        if (is_file($app . $image)) {
          return $image;
        }
        if (is_file($app . 'images/' . $image)) {
          return 'images/' . $image;
        }
      }
    }
    
    if (defined('THEME_NAME') && $na){
      if (is_file($app . 'themes/' . THEME_NAME . '/img/na.png')) {
        return 'themes/' . THEME_NAME . '/img/na.png';
      }
    }
    if (is_file($app . 'images/na.png') && $na) {
      return 'images/na.png';
    }

    return false;
  }
}

