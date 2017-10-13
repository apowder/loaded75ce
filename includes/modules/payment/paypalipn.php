<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  use common\classes\modules\ModulePayment;
  use common\classes\modules\ModuleStatus;
  use common\classes\modules\ModuleSortOrder;
  use common\classes\platform_config;

  class paypalipn extends ModulePayment{
    var $code, $title, $description, $enabled, $notify_url, $curl, $add_shipping_to_amount, $add_tax_to_amount, $update_stock_before_payment, $allowed_currencies, $default_currency, $test_mode;

// class constructor
    function __construct() {
      global $order;

      $this->code = 'paypalipn';
      $this->title = MODULE_PAYMENT_PAYPALIPN_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_PAYPALIPN_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPALIPN_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPALIPN_STATUS == 'True') ? true : false);
      $this->notify_url = MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL;
      $this->curl = ((MODULE_PAYMENT_PAYPALIPN_CURL == 'True') ? true : false);
      $this->add_shipping_to_amount = ((MODULE_PAYMENT_PAYPALIPN_ADD_SHIPPING_TO_AMOUNT == 'True') ? true : false);
      $this->add_tax_to_amount = ((MODULE_PAYMENT_PAYPALIPN_ADD_TAX_TO_AMOUNT == 'True') ? true : false);
      $this->update_stock_before_payment = ((MODULE_PAYMENT_PAYPALIPN_UPDATE_STOCK_BEFORE_PAYMENT == 'True') ? true : false);
      $this->allowed_currencies = MODULE_PAYMENT_PAYPALIPN_ALLOWED_CURRENCIES;
      $this->default_currency = MODULE_PAYMENT_PAYPALIPN_DEFAULT_CURRENCY;
      $this->test_mode = ((MODULE_PAYMENT_PAYPALIPN_TEST_MODE == 'True') ? true : false);
      $this->online = true;

      if ((int)MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->dont_update_stock = !$this->update_stock_before_payment;
      $this->dont_send_email = true;
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPALIPN_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPALIPN_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function before_process() {
      global $order;
      $order->info['order_status'] = 99999; // Paypal Processing
    }

    function after_process() {
      global $cart, $order, $currencies, $insert_id;

      $cart->reset(true);

    // unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      if (preg_match("/".preg_quote($order->info['currency'],"/")."/", MODULE_PAYMENT_PAYPALIPN_ALLOWED_CURRENCIES)) {
        $paypal_ipn_currency = $order->info['currency'];
      } else {
        $paypal_ipn_currency = MODULE_PAYMENT_PAYPALIPN_DEFAULT_CURRENCY;
      };

      $paypal_ipn_order_amount = $order->info['total'];
      $paypal_ipn_order_amount = number_format($paypal_ipn_order_amount * $currencies->get_value($paypal_ipn_currency), 2);

      if (!$this->isPartlyPaid()){
          $paypal_ipn_shipping_amount = number_format($order->info['shipping_cost'] * $currencies->get_value($paypal_ipn_currency),2 ,'.','');
          $paypal_ipn_tax_amount = number_format($order->info['tax'] * $currencies->get_value($paypal_ipn_currency),2 ,'.','');

          // is it possible to subtract:
          if (($paypal_ipn_order_amount - $paypal_ipn_shipping_amount - $paypal_ipn_tax_amount) > 0) {
              $force_add_shipping = $force_add_tax = false;
          } elseif (($paypal_ipn_order_amount - $paypal_ipn_tax_amount) > 0) {
              $force_add_shipping = true;
              $force_add_tax = false;
          } else {
              $force_add_shipping = $force_add_tax = true;
          }
          
          if (MODULE_PAYMENT_PAYPALIPN_ADD_SHIPPING_TO_AMOUNT=='True' || $force_add_shipping) {
              $paypal_ipn_shipping_amount = 0.00;
          } else {
              $paypal_ipn_order_amount -= $paypal_ipn_shipping_amount;
          }
          if (MODULE_PAYMENT_PAYPALIPN_ADD_TAX_TO_AMOUNT=='True' || $force_add_tax) {
              $paypal_ipn_tax_amount = 0.00;
          } else {
              $paypal_ipn_order_amount -= $paypal_ipn_tax_amount;
          }
      }

      $siteURL = 'https://www.paypal.com/';
      if ($this->test_mode){
          $siteURL = 'https://www.sandbox.paypal.com/';
      }
      
      $exists_subscription_data = false;
      foreach ($order->products as $i => $product) {
          if ($order->products[$i]['subscription'] == 1) {
            $exists_subscription_data = [
                'name' => $order->products[$i]['name'],
                'billingFrequency' => 12,
                'billingPeriod' => 'Month',
                'totalBillingCycles' => 12,
            ];
            break;
          }
      }
      
      if (is_array($exists_subscription_data)){
          
          tep_redirect($siteURL . "cgi-bin/webscr?cmd=_xclick-subscriptions&redirect_cmd=_xclick-subscriptions&business=".MODULE_PAYMENT_PAYPALIPN_ID.
                      "&item_name=".urlencode($exists_subscription_data['name']).(defined("TEXT_ITEM_SUBSCRIPTION")?TEXT_ITEM_SUBSCRIPTION:" - Subsribe for Products").
                      "&item_number=recurr_".$insert_id.
                      "&currency_code=".$paypal_ipn_currency.
                      "&a3=".($paypal_ipn_order_amount+$paypal_ipn_shipping_amount+$paypal_ipn_tax_amount). //regular subscription amount without trial period
                      "&p3=".$exists_subscription_data['billingFrequency']. //Subscription duration.
                      "&t3=".$exists_subscription_data['billingPeriod']. //Regular subscription units of duration. Allowable values:
                      "&src=1".//subscription payments recur
                      "&srt=".((int)$exists_subscription_data['totalBillingCycles']>52?52:$exists_subscription_data['totalBillingCycles']).//Recurring times. Number of times that subscription payments recur. Specify an integer above 1. Valid only if you specify src="1". 52 is maximum allowed
                      "&shipping=".$paypal_ipn_shipping_amount.
                      "&tax=".$paypal_ipn_tax_amount.
                      "&first_name=".urlencode($order->customer['firstname']).
                      "&last_name=".urlencode($order->customer['lastname']).
                      "&address1=".urlencode(trim($order->customer['street_address'])).
                      "&city=".urlencode($order->customer['city']).
                      "&state=".urlencode($order->customer['state']).
                      "&zip=".urlencode($order->customer['postcode']).
                      "&email=".$order->customer['email_address']."&bn=oscommerce-osmosis-0.981&return=".tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL').
                      "&cancel_return=".tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL').
                      "&notify_url=".urlencode(MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL));

          
        //tep_redirect($siteURL . "cgi-bin/webscr?cmd=_ext-enter&redirect_cmd=_xclick&business=".MODULE_PAYMENT_PAYPALIPN_ID."&item_name=".urlencode(STORE_NAME)."&item_number=".$insert_id."&currency_code=".$paypal_ipn_currency."&amount=".$paypal_ipn_order_amount."&shipping=".$paypal_ipn_shipping_amount."&tax=".$paypal_ipn_tax_amount."&first_name=".urlencode($order->customer['firstname'])."&last_name=".urlencode($order->customer['lastname'])."&address1=".urlencode($order->customer['street_address'])."&city=".urlencode($order->customer['city'])."&state=".urlencode($order->customer['state'])."&zip=".urlencode($order->customer['postcode'])."&country=".urlencode($order->customer['country']['iso_code_2'])."&email=".$order->customer['email_address']."&bn=oscommerce-osmosis-0.981&return=".tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL')."&cancel_return=".tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')."&notify_url=".MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL);
      } else {
        tep_redirect($siteURL . "cgi-bin/webscr?cmd=_ext-enter&redirect_cmd=_xclick&business=".MODULE_PAYMENT_PAYPALIPN_ID."&item_name=".urlencode(STORE_NAME)."&item_number=".$insert_id."&currency_code=".$paypal_ipn_currency."&amount=".$paypal_ipn_order_amount."&shipping=".$paypal_ipn_shipping_amount."&tax=".$paypal_ipn_tax_amount."&first_name=".urlencode($order->customer['firstname'])."&last_name=".urlencode($order->customer['lastname'])."&address1=".urlencode($order->customer['street_address'])."&city=".urlencode($order->customer['city'])."&state=".urlencode($order->customer['state'])."&zip=".urlencode($order->customer['postcode'])."&country=".urlencode($order->customer['country']['iso_code_2'])."&email=".$order->customer['email_address']."&bn=oscommerce-osmosis-0.981&return=".tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL')."&cancel_return=".tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')."&notify_url=".MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL); 
      }
       
      exit;
    }

    function output_error() {
      return false;
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_PAYMENT_PAYPALIPN_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_PAYMENT_PAYPALIPN_SORT_ORDER');
    }

    protected function get_install_keys($platform_id)
    {
      $keys = $this->configure_keys();

      $platform_config = new platform_config($platform_id);


      if (isset($keys['MODULE_PAYMENT_PAYPALIPN_CURL'])) {
        if (function_exists('curl_exec')) {
          $curl_message = '<br>cURL has been <b>DETECTED</b> in your system';
        } else {
          $curl_message = '<br>cURL has <b>NOT</b> been <b>DETECTED</b> in your system';
        };
        $keys['MODULE_PAYMENT_PAYPALIPN_CURL']['description'] = str_replace('<curl_message>', $curl_message, $keys['MODULE_PAYMENT_PAYPALIPN_CURL']['description']);
      }
      if ( isset($keys['MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL']) ) {
        $keys['MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL']['value'] = $platform_config->getCatalogBaseUrl().$keys['MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL']['value'];
      }

      $paypal_supported_currencies = "'USD','EUR','GBP','CAD','JPY'";
      $osc_allowed_currencies = implode(',',$platform_config->getAllowedCurrencies());
      if (empty($osc_allowed_currencies)) {
        $osc_allowed_currencies = 'USD';
      };

      $replace_currencies = array(
        '<paypal_supported_currencies>' => str_replace('\'','',$paypal_supported_currencies),
        '<osc_allowed_currencies>' => $osc_allowed_currencies,
        '<osc_set_allowed_currencies>' => var_export(explode(',',$osc_allowed_currencies),true),
      );

      foreach( $keys as $key=>$key_data ) {
        $keys[$key]['value'] = str_replace(array_keys($replace_currencies),array_values($replace_currencies),$keys[$key]['value']);
        $keys[$key]['description'] = str_replace(array_keys($replace_currencies),array_values($replace_currencies),$keys[$key]['description']);
        $keys[$key]['set_function'] = str_replace(array_keys($replace_currencies),array_values($replace_currencies),$keys[$key]['set_function']);
      }
      return $keys;
    }

    public function configure_keys()
    {
      return array (
        'MODULE_PAYMENT_PAYPALIPN_STATUS' => array(
          'title' => 'Allow PayPal IPN',
          'value' => 'True',
          'description' => 'Do you want to accept PayPal IPN payments and notifications?',
          'sort_order' => '1',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ID' => array(
          'title' => 'PayPal IPN ID',
          'value' => 'you@yourbusiness.com',
          'description' => 'Your business ID at PayPal.  Usually the email address you signed up with.  You can create a free PayPal account at <a href="http://www.paypal.com/" target="_blank">http://www.paypal.com</a>.',
          'sort_order' => '2',
        ),
        'MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL' => array(
          'title' => 'PayPal IPN Notify URL',
          'value' => 'callback/paypal-notify',
          'description' => 'Exact location in which your callback/paypal-notify resides.',
          'sort_order' => '3',
        ),
        'MODULE_PAYMENT_PAYPALIPN_CURL' => array (
          'title' => 'PayPal IPN Use cURL',
          'value' => 'False',
          'description' => 'Use cURL to communicate with PayPal?<curl_message>',
          'sort_order' => '4',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ADD_SHIPPING_TO_AMOUNT' => array(
          'title' => 'PayPal IPN Add Shipping to Amount',
          'value' => 'False',
          'description' => 'Add shipping amount to order amount? (will set shipping amount to $0 in PayPal)',
          'sort_order' => '5',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ADD_TAX_TO_AMOUNT' => array(
          'title' => 'PayPal IPN Add Tax to Amount',
          'value' => 'False',
          'description' => 'Add tax amount to order amount? (will set tax amount to $0 in PayPal)',
          'sort_order' => '5',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_UPDATE_STOCK_BEFORE_PAYMENT' => array (
          'title' => 'PayPal IPN Update Stock Before Payment',
          'value' => 'False',
          'description' => 'Should Products Stock be updated even when the payment is not yet COMPLETED?',
          'sort_order' => '6',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ALLOWED_CURRENCIES' => array(
          'title' => 'PayPal IPN Allowed Currencies',
          'value' => '<osc_allowed_currencies>',
          'description' => 'Allowed currencies in which customers can pay.<br>Allowed by PayPal: <paypal_supported_currencies><br>Allowed in your shop: <osc_allowed_currencies><br>To add more currencies to your shop go to Localization->Currencies.',
          'sort_order' => '9',
        ),
        'MODULE_PAYMENT_PAYPALIPN_DEFAULT_CURRENCY' => array (
          'title' => 'PayPal IPN Default Currency',
          'value' => 'USD',
          'description' => 'Default currency to use when customer try to pay in a NON allowed (because of PayPal or you) currency',
          'sort_order' => '10',
          'set_function' => 'tep_cfg_select_option(<osc_set_allowed_currencies>, ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_TEST_MODE' => array(
          'title' => 'PayPal IPN Test Mode',
          'value' => 'False',
          'description' => 'Run in TEST MODE? If so, you will be able to send TEST IPN from Admin->PayPal_IPN->Test_IPN, BUT you will not be able to receive real IPN\'s from PayPal.',
          'sort_order' => '11',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ZONE' => array(
          'title' => 'PayPal IPN Payment Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this payment method for that zone.',
          'sort_order' => '13',
          'use_function' => '\\\\common\\\\helpers\\\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID' => array(
          'title' => 'PayPal IPN Set Order Status',
          'value' => '0',
          'description' => 'Set the status of orders made with this payment module to this value',
          'sort_order' => '14',
          'set_function' => 'tep_cfg_pull_down_order_statuses(',
          'use_function' => '\\\\common\\\\helpers\\\\Order::get_order_status_name',
        ),
        'MODULE_PAYMENT_PAYPALIPN_SORT_ORDER' => array(
          'title' => 'PayPal IPN Sort order of display.',
          'value' => '0',
          'description' => 'Sort order of display. Lowest is displayed first.',
          'sort_order' => '12',
        ),
      );
    }

    function isOnline() {
        return true;
    }
    
    function haveSubscription() {
        return true;
    }

}
