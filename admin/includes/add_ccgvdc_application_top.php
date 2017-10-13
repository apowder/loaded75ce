<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

DEFINE('FILENAME_GV_QUEUE', 'gv_queue');
DEFINE('FILENAME_GV_MAIL', 'gv_mail');
DEFINE('FILENAME_GV_SENT', 'gv_sent');
define('FILENAME_COUPON_ADMIN', 'coupon_admin');

define('TABLE_COUPON_GV_QUEUE', 'coupon_gv_queue');
define('TABLE_COUPON_GV_CUSTOMER', 'coupon_gv_customer');
define('TABLE_COUPON_EMAIL_TRACK', 'coupon_email_track');
define('TABLE_COUPON_REDEEM_TRACK', 'coupon_redeem_track');
define('TABLE_COUPONS', 'coupons');
define('TABLE_COUPONS_DESCRIPTION', 'coupons_description');

// Below are some defines which affect the way the discount coupon/gift voucher system work
// Be careful when editing them.
//
// Set the length of the redeem code, the longer the more secure

define('SECURITY_CODE_LENGTH', '6');

////
// Create a Coupon Code. length may be between 1 and 16 Characters
// $salt needs some thought.

  function create_coupon_code($salt="secret", $length=SECURITY_CODE_LENGTH) {
    $ccid = md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    srand((double)microtime()*1000000); // seed the random number generator
    $random_start = @rand(0, (128-$length));
    $good_result = 0;
    $id1 = '';
    while ($good_result == 0) {
      $id1 = substr($ccid, $random_start, $length);
      /*if ( strlen($id1)!=$length ) {
        $id1 = create_coupon_code($salt,$length);
      }*/
      $check = tep_db_fetch_array(tep_db_query("select COUNT(*) AS exist_count from " . TABLE_COUPONS . " where coupon_code = '" . $id1 . "'"));
      if ($check['exist_count'] == 0) $good_result = 1;
    }
    return $id1;
  }
////
// Update the Customers GV account
  function tep_gv_account_update($customer_id, $gv_id) {
    $customer_gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
    $coupon_gv_query = tep_db_query("select coupon_amount from " . TABLE_COUPONS . " where coupon_id = '" . $gv_id . "'");
    $coupon_gv = tep_db_fetch_array($coupon_gv_query);
    if (tep_db_num_rows($customer_gv_query) > 0) {
      $customer_gv = tep_db_fetch_array($customer_gv_query);
      $new_gv_amount = $customer_gv['amount'] + $coupon_gv['coupon_amount'];
      tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . $new_gv_amount . "' where customers_id = '" . $customer_id . "'");
    }
  }
////
// Output a day/month/year dropdown selector
  function tep_draw_date_selector($prefix, $date='') {
    $month_array = array();
    $month_array[1] =_JANUARY;
    $month_array[2] =_FEBRUARY;
    $month_array[3] =_MARCH;
    $month_array[4] =_APRIL;
    $month_array[5] =_MAY;
    $month_array[6] =_JUNE;
    $month_array[7] =_JULY;
    $month_array[8] =_AUGUST;
    $month_array[9] =_SEPTEMBER;
    $month_array[10] =_OCTOBER;
    $month_array[11] =_NOVEMBER;
    $month_array[12] =_DECEMBER;
    $usedate = getdate($date);
    $day = $usedate['mday'];
    $month = $usedate['mon'];
    $year = $usedate['year'];		
    $date_selector = '<select name="'. $prefix .'_day">';
    for ($i=1;$i<32;$i++){
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$day) $date_selector .= 'selected';
      $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $prefix .'_month">';
    for ($i=1;$i<13;$i++){
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$month) $date_selector .= 'selected';      
      $date_selector .= '>' . $month_array[$i] . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $prefix .'_year">';
    for ($i=2001;$i<2019;$i++){
      $date_selector .= '<option value="' . $i . '"';
      if ($i==$year) $date_selector .= 'selected';
      $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    return $date_selector;
  }
  
  function tl_credit_order_check_state($order_id) {
    $release_statuses = array_map('intval',explode(',',MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID));
    $ordered_gv_r = tep_db_query(
      "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
      "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
      "WHERE o.orders_id='".(int)$order_id."' AND op.orders_id=o.orders_id AND op.gv_state='pending' "
    );
    if ( tep_db_num_rows($ordered_gv_r)>0 ) {
      while( $ordered_gv = tep_db_fetch_array($ordered_gv_r) ) {
        if ($ordered_gv['gv_state']=='pending' && in_array((int)$ordered_gv['orders_status'], $release_statuses) ) {
          tl_credit_order_product_release($ordered_gv);
        }
      }
    }
  }

  function tl_credit_order_manual_update_state($orders_products_id, $new_state) {
    $ordered_gv_r = tep_db_query(
      "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
      "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
      "WHERE op.orders_id=o.orders_id AND op.gv_state!='none' ".
      " AND op.orders_products_id='".(int)$orders_products_id."' "
    );
    if ( tep_db_num_rows($ordered_gv_r)>0 ) {
      while( $ordered_gv = tep_db_fetch_array($ordered_gv_r) ) {
        if ( $ordered_gv['gv_state']=='released' ) continue;
        if ($new_state=='released' && ($ordered_gv['gv_state']=='pending' || $ordered_gv['gv_state']=='canceled') ) {
          tl_credit_order_product_release($ordered_gv);
        }elseif ($new_state=='pending' && $ordered_gv['gv_state']=='canceled') {
          tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='{$new_state}' WHERE orders_products_id='".$ordered_gv['orders_products_id']."' ");
        }elseif ($new_state=='canceled' && $ordered_gv['gv_state']=='pending') {
          tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='{$new_state}' WHERE orders_products_id='".$ordered_gv['orders_products_id']."' ");
        }
      }
    }
  }

  function tl_credit_order_product_release($order_products) {
    $release_info = false;
    if ( is_array($order_products) && array_key_exists('customers_id',$order_products) && array_key_exists('orders_products_id',$order_products) ) {
      $release_info = $order_products;
    }
    if ( is_numeric($order_products) ) {
      $ordered_gv_r = tep_db_query(
        "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
        "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
        "WHERE op.orders_products_id='".(int)$order_products."' AND op.orders_id=o.orders_id "
      );
      if ( tep_db_num_rows($ordered_gv_r)>0 ) {
        $release_info = tep_db_fetch_array($ordered_gv_r);
      }
    }
    if ( is_array($release_info) && !empty($release_info['gv_state']) && $release_info['gv_state']!='released' && $release_info['gv_state']!='none' ){
      $gv_order_amount = ($release_info['final_price'] * $release_info['products_quantity']);
      if (MODULE_ORDER_TOTAL_GV_CREDIT_TAX=='true') $gv_order_amount = $gv_order_amount * (100 + $release_info['products_tax']) / 100;
      $gv_order_amount = $gv_order_amount * 100 / 100;

      tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = credit_amount + '" . tep_db_input($gv_order_amount) . "' where customers_id = '" . (int)$release_info['customers_id'] . "'");
      tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
        'customers_id' => $release_info['customers_id'],
        'credit_prefix' => '+',
        'credit_amount' => $gv_order_amount,
        'currency' => DEFAULT_CURRENCY,
        'currency_value' => 1,
        'customer_notified' => 0,
        'comments' => 'Order '. $release_info['products_model'].' order #'.$release_info['orders_id'],
        'date_added' => 'now()',
        'admin_id' => (int)$_SESSION['login_id'],
      ));
      tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='released' WHERE orders_products_id='".$release_info['orders_products_id']."' ");
    }
  }  
  
  function generate_customer_gvcc($coupon_id, $mail, $amount, $currency, $customer_id = 0, $basket_id = 0){
	  require_once(DIR_WS_CLASSES . 'currencies.php');
	  $currencies = new currencies();
    if (!$coupon_id){
        $id1 = create_coupon_code($mail);
        // Now create the coupon main and email entry
        
        $insert_query = tep_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, coupon_currency, date_created) values ('" . $id1 . "', 'G', '" . $amount . "', '" . tep_db_input($currency) . "', now())");
        $insert_id = tep_db_insert_id();      
        $amount = $currencies->format($amount, false, $currency);
    } else {
        $coupon = tep_db_fetch_array(tep_db_query("select coupon_code, coupon_amount, coupon_currency from " . TABLE_COUPONS . " where coupon_id = '" . (int)$coupon_id . "'"));
        $id1 = $coupon['coupon_code'];
        $insert_id = $coupon_id;
        $amount = $currencies->format($coupon['coupon_amount'], false, $coupon['coupon_currency']);
    }
    $admin_fname = 'Admin';
    $admin_lname = '';
    if (class_exists('\backend\models\Admin')){
      $admin = new \backend\models\Admin();
      $admin_fname = $admin->getInfo('admin_firstname');
      $admin_lname = $admin->getInfo('admin_lastname');
    }
      
	  $insert_query = tep_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, basket_id, sent_firstname, sent_lastname, emailed_to, date_sent) values ('" . $insert_id ."', '" . (int)$customer_id . "', '" . (int)$basket_id . "','" . tep_db_input($admin_fname) . "', '" . tep_db_input($admin_lname) . "', '" . $mail . "', now() )"); 
    return ['id1' => $id1, 'amount' => $amount];
  }


