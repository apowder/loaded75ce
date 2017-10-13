<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes\modules;

abstract class ModulePayment extends Module{

  function javascript_validation() {
    return false;
  }

  function selection() {
    return false;
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return false;
  }

  function process_button() {
    global $order, $currency, $languages_id, $language, $customer_id;

    if (!self::isPartlyPaid()) return false;
    
    if (isset($order->info['total_paid_inc_tax'])){
        $order->info['total'] = $order->info['total_inc_tax'] - $order->info['total_paid_inc_tax'];
        if ($order->info['total'] < 0 ) $order->info['total'] = 0;
    }
    $this->paid = 'partlypaid';
    if (!tep_session_is_registered('pay_order_id')) tep_session_register('pay_order_id');
    $_SESSION['pay_order_id'] = $order->order_id;    
  }

  function before_process() {
    global $sendto, $billto, $order;
    if (self::isPartlyPaid()){
        if (!$sendto && (int)$order->delivery['address_book_id'] > 0) $sendto = (int)$order->delivery['address_book_id'];
        if (!$billto && (int)$order->billing['address_book_id'] > 0) $billto = (int)$order->billing['address_book_id'];
        return true;
    }
    return false;
  }

  function after_process() {
    return false;
  }

  function get_error() {
    return false;
  }

  function output_error() {
    return false;
  }
  
  function before_subscription($id = 0) {
    return false;
  }
  
  function haveSubscription() {
    return false;
  }
  
  function get_subscription_info($id = '') {
    return '';
  }
  
  function get_subscription_full_info($id = '') {
    return [];
  }

  function cancel_subscription($id = '') {
    return false;
  }
  
  function terminate_subscription($id = '', $type = 'none') {
    return false;
  }
  
  function postpone_subscription($id = '', $date = '') {
    return false;
  }
  
  function reactivate_subscription($id = '') {
    return false;
  }
  
  function isOnline() {
      return false;
  }
  
  function isPartlyPaid(){
    if (strpos($_SERVER['REQUEST_URI'], 'order-confirmation') !== false || strpos($_SERVER['REQUEST_URI'], 'order-process') !== false) return true;      
    return false;
  }
  
}
