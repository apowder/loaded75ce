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

  class sage_pay_server extends ModulePayment {
    var $code, $title, $description, $enabled;

// class constructor
    function __construct() {
      global $order;

      $this->signature = 'sage_pay|sage_pay_server|2.0|2.3';
      $this->api_version = '3.00';

      $this->code = 'sage_pay_server';
      $this->title = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS == 'True') ? true : false);
      $this->online = true;

// {{
//      if (IS_TRADE_SITE == 'True') $this->enabled = false;
// }}

      if ((int)MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $sage_pay_server_securitykey, $sage_pay_server_nexturl, $customer_id, $order, $currency, $order_totals, $cartID;

      //@file_put_contents(DIR_FS_CATALOG.'/temp/sage_log.txt', var_export($HTTP_POST_VARS,true)."\n\n",FILE_APPEND);

      $error = null;

      if (isset($HTTP_GET_VARS['check']) && ($HTTP_GET_VARS['check'] == 'SERVER')) {
        $sig_string = '';
        foreach(array(
          'VPSTxId',
          'VendorTxCode',
          'Status',
          'TxAuthNo',
          'VendorName',
          'AVSCV2',
          'SecurityKey',
          'AddressResult',
          'PostCodeResult',
          'CV2Result',
          'GiftAid',
          '3DSecureStatus',
          'CAVV',
          'AddressStatus',
          'PayerStatus',
          'CardType',
          'Last4Digits',
          'DeclineCode',
          'ExpiryDate',
          'FraudResponse',
          'BankAuthCode',
        ) as $sig_post_key) {
          if ( $sig_post_key == 'VendorName' ) {
            $sig_string .= strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));
          }elseif ( $sig_post_key == 'SecurityKey' ) {
            $sig_string .= $sage_pay_server_securitykey;
          }else
          if ( isset($HTTP_POST_VARS[$sig_post_key]) ) {
            $sig_string .= $HTTP_POST_VARS[$sig_post_key];
          }
        }

        if (isset($HTTP_POST_VARS['VPSSignature']) && ($HTTP_POST_VARS['VPSSignature'] == strtoupper(md5($sig_string)))) {
          if ( ($HTTP_POST_VARS['Status'] != 'OK') && ($HTTP_POST_VARS['Status'] != 'AUTHENTICATED') && ($HTTP_POST_VARS['Status'] != 'REGISTERED') ) {
            tep_session_unregister('sage_pay_server_securitykey');
            tep_session_unregister('sage_pay_server_nexturl');

            $error = $this->getErrorMessageNumber($HTTP_POST_VARS['StatusDetail']);

            if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
              $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
            } else {
              $error_url = tep_href_link('callback/redirect-sage-pay', 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
            }

            $result = 'Status=OK' . chr(13) . chr(10) .
                      'RedirectURL=' . $error_url;
          } else {

            global $sage_pay_server_additional_info;
            $sage_pay_server_additional_info = array(
            'VPSTxId' => $HTTP_POST_VARS['VPSTxId'],
            'VendorTxCode' => $HTTP_POST_VARS['VendorTxCode'],
            'Status' => $HTTP_POST_VARS['Status'],
            'SecurityKey' => $sage_pay_server_securitykey,
            'TxAuthNo'=> $HTTP_POST_VARS['TxAuthNo'],
            'AVSCV2' => $HTTP_POST_VARS['AVSCV2'],
            'AddressResult' => $HTTP_POST_VARS['AddressResult'],
            'PostCodeResult' => $HTTP_POST_VARS['PostCodeResult'],
            'CV2Result' => $HTTP_POST_VARS['CV2Result'],
            '3DSecureStatus' => $HTTP_POST_VARS['3DSecureStatus'],
            'CAVV' => $HTTP_POST_VARS['CAVV']
            );

            tep_session_register('sage_pay_server_additional_info');


            $result = 'Status=OK' . chr(13) . chr(10) .
                      'RedirectURL=' . tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=PROCESS&key=' . md5($sage_pay_server_securitykey) . '&VPSTxId=' . $HTTP_POST_VARS['VPSTxId'] . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
          }
        } else {
          tep_session_unregister('sage_pay_server_securitykey');
          tep_session_unregister('sage_pay_server_nexturl');

          $error = $this->getErrorMessageNumber($HTTP_POST_VARS['StatusDetail']);

          if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
            $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
          } else {
            $error_url = tep_href_link('callback/redirect-sage-pay', 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
          }

          $result = 'Status=INVALID' . chr(13) . chr(10) .
                    'RedirectURL=' . $error_url;
        }

        echo $result;
        exit;
      } elseif (isset($HTTP_GET_VARS['check']) && ($HTTP_GET_VARS['check'] == 'PROCESS')) {
        if ($HTTP_GET_VARS['key'] == md5($sage_pay_server_securitykey)) {
          tep_session_unregister('sage_pay_server_securitykey');
          tep_session_unregister('sage_pay_server_nexturl');

          if ( isset($HTTP_GET_VARS['VPSTxId']) ) {
            $order->info['comments'] = 'Sage Pay Reference ID: ' . $HTTP_GET_VARS['VPSTxId'] . (tep_not_null($order->info['comments']) ? "\n\n" . $order->info['comments'] : '');
          }

          return true;
        }
      } else {
        $params = array('VPSProtocol' => $this->api_version,
                        'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
                        'VendorTxCode' => substr(date('YmdHis') . '-' . $customer_id . '-' . $cartID, 0, 40),
                        'Amount' => $this->format_raw($order->info['total']),
                        'Currency' => $currency,
                        'Description' => substr(STORE_NAME, 0, 100),
                        'NotificationURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=SERVER&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false),
                        'BillingSurname' => substr($order->billing['lastname'], 0, 20),
                        'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
                        'BillingAddress1' => substr($order->billing['street_address'], 0, 100),
                        'BillingCity' => substr($order->billing['city'], 0, 40),
                        'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
                        'BillingCountry' => $order->billing['country']['iso_code_2'],
                        'BillingPhone' => substr($order->customer['telephone'], 0, 20),
                        'DeliverySurname' => substr($order->delivery['lastname'], 0, 20),
                        'DeliveryFirstnames' => substr($order->delivery['firstname'], 0, 20),
                        'DeliveryAddress1' => substr($order->delivery['street_address'], 0, 100),
                        'DeliveryCity' => substr($order->delivery['city'], 0, 40),
                        'DeliveryPostCode' => substr($order->delivery['postcode'], 0, 10),
                        'DeliveryCountry' => $order->delivery['country']['iso_code_2'],
                        'DeliveryPhone' => substr($order->customer['telephone'], 0, 20),
                        'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                        //'ApplyAVSCV2' => '2',
                        'Apply3DSecure' => '0');

        $ip_address = \common\helpers\System::get_ip_address();

        if ( (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
          $params['ClientIPAddress']= $ip_address;
        }

        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Payment' ) {
          $params['TxType'] = 'PAYMENT';
        } elseif ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Deferred' ) {
          $params['TxType'] = 'DEFERRED';
        } else {
          $params['TxType'] = 'AUTHENTICATE';
        }

        if ($params['BillingCountry'] == 'US') {
          $params['BillingState'] = \common\helpers\Zones::get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
        }

        if ($params['DeliveryCountry'] == 'US') {
          $params['DeliveryState'] = \common\helpers\Zones::get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
        }

        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE != 'Normal' ) {
          $params['Profile'] = 'LOW';
        }

        $contents = array();

        foreach ($order->products as $product) {
          $product_name = $product['name'];

          if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $att) {
              $product_name .= '; ' . $att['option'] . '=' . $att['value'];
            }
          }

          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', $product_name) . ':' . $product['qty'] . ':' . $this->format_raw($product['final_price']) . ':' . $this->format_raw(($product['tax'] / 100) * $product['final_price']) . ':' . $this->format_raw((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) . ':' . $this->format_raw(((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) * $product['qty']);
        }

        foreach ($order_totals as $ot) {
          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($ot['title'])) . ':---:---:---:---:' . $this->format_raw($ot['value']);
        }

        $params['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        switch (MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER) {
          case 'Live':
            $gateway_url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
            break;

          case 'Test':
            $gateway_url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
            break;

          default:
            $gateway_url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx';
            break;
        }

        $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);

        $string_array = explode(chr(10), $transaction_response);
        $return = array();

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }

        if ($return['Status'] == 'OK') {
          tep_session_register('sage_pay_server_securitykey');
          $sage_pay_server_securitykey = $return['SecurityKey'];

          tep_session_register('sage_pay_server_nexturl');
          $sage_pay_server_nexturl = $return['NextURL'];

          if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
            tep_redirect($return['NextURL']);
          } else {
            tep_redirect(tep_href_link('checkout_sage_pay.php', '', 'SSL'));
          }
        } else {
          $error = $this->getErrorMessageNumber($return['StatusDetail']);
        }
      }

      tep_session_unregister('sage_pay_server_securitykey');
      tep_session_unregister('sage_pay_server_nexturl');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL'));
    }

    function after_process() {
      global $cart;

      tep_session_unregister('sage_pay_server_additional_info');
      $cart->reset(true);

// unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      tep_redirect(tep_href_link('callback/redirect-sage-pay', '', 'SSL'));
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $message = MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_GENERAL;

      if ( isset($HTTP_GET_VARS['error']) && is_numeric($HTTP_GET_VARS['error']) && $this->errorMessageNumberExists($HTTP_GET_VARS['error']) ) {
        $message = $this->getErrorMessage($HTTP_GET_VARS['error']) . ' ' . MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_GENERAL;
      }

      $error = array('title' => MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_TITLE,
                     'error' => $message);

      return $error;
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER');
    }

    public function configure_keys()
    {
      return array(
        'MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS' => array(
          'title' => 'Enable Sage Pay Server Module',
          'value' => 'False',
          'description' => 'Do you want to accept Sage Pay Server payments?',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME' => array(
          'title' => 'Vendor Login Name',
          'value' => '',
          'description' => 'The vendor login name to connect to the gateway with.',
          'sort_order' => '0',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE' => array(
          'title' => 'Profile Payment Page',
          'value' => 'Normal',
          'description' => 'Profile page to use for the payment page.',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'Normal\', \'Low\'), ',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD' => array(
          'title' => 'Transaction Method',
          'value' => 'Authenticate',
          'description' => 'The processing method to use for each transaction.',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), ',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER' => array(
          'title' => 'Transaction Server',
          'value' => 'Simulator',
          'description' => 'Perform transactions on the production server or on the testing server.',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'Live\', \'Test\', \'Simulator\'), ',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE' => array(
          'title' => 'Payment Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this payment method for that zone.',
          'sort_order' => '2',
          'use_function' => '\\\\common\\\\helpers\\\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID' => array(
          'title' => 'Set Order Status',
          'value' => '0',
          'description' => 'Set the status of orders made with this payment module to this value',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_pull_down_order_statuses(',
          'use_function' => '\\\\common\\\\helpers\\\\Order::get_order_status_name',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_CURL' => array(
          'title' => 'cURL Program Location',
          'value' => '/usr/bin/curl',
          'description' => 'The location to the cURL program application.',
          'sort_order' => '0',
        ),
        'MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER' => array(
          'title' => 'Sort order of display.',
          'value' => '0',
          'description' => 'Sort order of display. Lowest is displayed first.',
          'sort_order' => '0',
        ),
      );
    }

    function sendTransactionToGateway($url, $parameters) {
      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
        $server['path'] = '/';
      }

      if (isset($server['user']) && isset($server['pass'])) {
        $header[] = 'Authorization: Basic ' . base64_encode($server['user'] . ':' . $server['pass']);
      }

      if (function_exists('curl_init')) {
        $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
        curl_setopt($curl, CURLOPT_PORT, $server['port']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_SAGE_PAY_SERVER_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k', $result);
        $result = implode("\n", $result);
      }

      return $result;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function loadErrorMessages() {
      global $language;
      $sagepag_all_errors_list_arr = array();

      if (file_exists(dirname(__FILE__) . '/../../languages/modules/payment/sage_pay_errors.php')) {
        include(dirname(__FILE__) . '/../../languages/modules/payment/sage_pay_errors.php');
      }

      $this->_error_messages = $sagepag_all_errors_list_arr;
    }

    function getErrorMessageNumber($string) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      $error = explode(' ', $string, 2);

      if (is_numeric($error[0]) && $this->errorMessageNumberExists($error[0])) {
        return $error[0];
      }

      return false;
    }

    function getErrorMessage($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      if (is_numeric($number) && $this->errorMessageNumberExists($number)) {
        return $this->_error_messages[$number];
      }

      return false;
    }

    function errorMessageNumberExists($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      return (is_numeric($number) && isset($this->_error_messages[$number]));
    }
    
    function isOnline() {
        return true;
    }
  }

