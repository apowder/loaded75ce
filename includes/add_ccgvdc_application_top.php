<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  define('FILENAME_GV_FAQ', 'gv_faq.php');
  define('FILENAME_GV_REDEEM', 'gv_redeem.php');
  define('FILENAME_GV_REDEEM_PROCESS', 'gv_redeem_process.php');
  define('FILENAME_GV_SEND', 'account/gv_send');
  define('FILENAME_GV_SEND_PROCESS', 'gv_send_process.php');
  define('FILENAME_POPUP_COUPON_HELP', 'popup_coupon_help.php');

  define('TABLE_COUPON_GV_CUSTOMER', 'coupon_gv_customer');
  define('TABLE_COUPON_GV_QUEUE', 'coupon_gv_queue');
  define('TABLE_COUPON_REDEEM_TRACK', 'coupon_redeem_track');
  define('TABLE_COUPON_EMAIL_TRACK', 'coupon_email_track');
  define('TABLE_COUPONS', 'coupons');
  define('TABLE_COUPONS_DESCRIPTION', 'coupons_description');

// Below are some defines which affect the way the discount coupon/gift voucher system work
// Be careful when editing them.
//
// Set the length of the redeem code, the longer the more secure
  define('SECURITY_CODE_LENGTH', '10');
//
// The settings below determine whether a new customer receives an incentive when they first signup
//
// Set the amount of a Gift Voucher that the new signup will receive, set to 0 for none
//  define('NEW_SIGNUP_GIFT_VOUCHER_AMOUNT', '10');  // placed in the admin configuration mystore
//
// Set the coupon ID that will be sent by email to a new signup, if no id is set then no email :)
//  define('NEW_SIGNUP_DISCOUNT_COUPON', '3'); // placed in the admin configuration mystore


////
// Create a Coupon Code. length may be between 1 and 16 Characters
// $salt needs some thought.

  function create_coupon_code($salt="secret", $length = SECURITY_CODE_LENGTH) {
    $ccid = md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    srand((double)microtime()*1000000); // seed the random number generator
    $random_start = @rand(0, (128-$length));
    $good_result = 0;
    while ($good_result == 0) {
      $id1=substr($ccid, $random_start,$length);        
      $query = tep_db_query("select coupon_code from " . TABLE_COUPONS . " where coupon_code = '" . tep_db_input($id1) . "'");
      if (tep_db_num_rows($query) == 0) $good_result = 1;
    }
    return $id1;
  }
////
// Update the Customers GV account
  function tep_gv_account_update($customer_id, $gv_id) {
    $customer_gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $coupon_gv_query = tep_db_query("select coupon_code, coupon_amount, coupon_currency from " . TABLE_COUPONS . " where coupon_id = '" . (int)$gv_id . "' and coupon_active='Y'");
    $coupon_gv = tep_db_fetch_array($coupon_gv_query);
    if (tep_db_num_rows($customer_gv_query) > 0) {
      $customer_gv = tep_db_fetch_array($customer_gv_query);
      require_once(DIR_WS_CLASSES . 'currencies.php');
      $currencies = new currencies();
      $new_gv_amount = $customer_gv['amount'] + $coupon_gv['coupon_amount']/* * $currencies->get_market_price_rate($coupon_gv['coupon_currency'], $customer_gv['currency'])*/;
   // new code bugfix
      tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . tep_db_input($new_gv_amount) . "' where customers_id = '" . (int)$customer_id . "'");
	 // original code $gv_query = tep_db_query("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $new_gv_amount . "'");
      tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
        'customers_id' => $customer_id,
        'credit_prefix' => '+',
        'credit_amount' => $coupon_gv['coupon_amount'],
        'currency' => DEFAULT_CURRENCY,
        'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
        'customer_notified' => 0,
        'comments' => 'Redeem '.$customer_gv['coupon_code'],
        'date_added' => 'now()',
        'admin_id' => 0,
      ));
    }
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

  function tl_credit_order_product_release($order_products) {
    global $currencies;
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
        'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
        'customer_notified' => 0,
        'comments' => 'Order '. $release_info['products_model'].' order #'.$release_info['orders_id'],
        'date_added' => 'now()',
        'admin_id' => 0,
      ));
      tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='released' WHERE orders_products_id='".$release_info['orders_products_id']."' ");
    }
  }

