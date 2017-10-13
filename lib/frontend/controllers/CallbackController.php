<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use frontend\design\Info;
use Yii;
use common\classes\order;
use common\classes\currencies;
use common\classes\payment;
use common\classes\shipping;
use common\classes\order_total;
use common\helpers\Translation;
use common\models\KlarnaCheckoutModel;
use frontend\design\boxes\Klarna;

class CallbackController extends Sceleton {

    public function actionPaypalNotify() { // for paypalipn
        global $currencies, $order_totals, $order;

        $payment = 'paypalipn';

        $payment_modules = new payment($payment);

        $item_number = $_POST['item_number'];
        if (strpos($_POST['item_number'], 'recurr_') !== false) {
            $is_subscription = true;
            $item_number = str_replace('recurr_', '', $_POST['item_number']);
        }
        $status_check_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id='" . trim($item_number) . "'");
        if (tep_db_num_rows($status_check_query) == 0) {
            die();
        }
        $order = new order($item_number);
        $order_total_modules = new \common\classes\order_total();
        $order_totals = $order->totals;
        $paid_key = -1;
        if (is_array($order->totals)) {
            foreach ($order->totals as $key => $total) {
                $order_totals[$key]['sort_order'] = $GLOBALS[$total['class']]->sort_order;
                if ($total['class'] == 'ot_paid') {
                    $paid_key = $key;
                }
                if ($total['class'] == 'ot_due') {
                    $order->info['total'] = $total['value_inc_tax'];

                    if ($paid_key != -1) {
                        $order->info['total_inc_tax'] = $order_totals[$paid_key]['value_inc_tax'] + $total['value_inc_tax'];
                        $order->info['total_exc_tax'] = $order_totals[$paid_key]['value_exc_vat'] + $total['value_exc_vat'];
                    }
                    break;
                }
            }
        }

        $req = 'cmd=_notify-validate';

        foreach ($_POST as $key => $value) {
            $req .= '&' . $key . '=' . urlencode(stripslashes($value));
            $$key = $value;
        }

        $response_verified = '';
        $paypal_response = '';

        if (MODULE_PAYMENT_PAYPALIPN_TEST_MODE == 'True') {

            if ($item_number) {
                $paypal_response = $_POST[ipnstatus];
                echo 'TEST IPN Processed for order #' . $item_number;
            } else {
                echo 'You need to specify an order #';
            };
        } elseif (MODULE_PAYMENT_PAYPALIPN_CURL == 'True') { // IF CURL IS ON, SEND DATA USING CURL (SECURE MODE, TO https://)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paypal.com/cgi-bin/webscr");
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);

            $paypal_response = curl_exec($ch);
            curl_close($ch);
        } else { // ELSE, SEND IT WITH HEADERS (STANDARD MODE, TO http://)
            $header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: " . strlen($req) . "\r\n";
            $header .= "Host: www.paypal.com\r\n";
            $header .= "Connection: close\r\n\r\n";
            $fp = fsockopen("www.paypal.com", 80, $errno, $errstr, 30);

            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $paypal_response .= fgets($fp, 1024);
            };

            fclose($fp);
        };

        if (preg_match('/VERIFIED/', $paypal_response)) {
            $response_verified = 1;
            $ipn_result = 'VERIFIED';
        } else if (preg_match('/INVALID/', $paypal_response)) {
            $response_invalid = 1;
            $ipn_result = 'INVALID';
        } else {
            echo 'Error: no valid $paypal_response received.';
        };

        if ($txn_id && ($response_verified == 1 || $response_invalid == 1)) {

            $txn_check = tep_db_query("select txn_id from " . TABLE_PAYPALIPN_TXN . " where txn_id='$txn_id'");
            if (tep_db_num_rows($txn_check) == 0) { // If txn no previously registered, we should register it
                $sql_data_array = array('txn_id' => $txn_id,
                    'ipn_result' => $ipn_result,
                    'receiver_email' => $receiver_email,
                    'business' => $business,
                    'item_name' => $item_name,
                    'item_number' => $item_number,
                    'quantity' => $quantity,
                    'invoice' => $invoice,
                    'custom' => $custom,
                    'option_name1' => $option_name1,
                    'option_selection1' => $option_selection1,
                    'option_name2' => $option_name2,
                    'option_selection2' => $option_selection2,
                    'num_cart_items' => $num_cart_items,
                    'payment_status' => $payment_status,
                    'pending_reason' => $pending_reason,
                    'payment_date' => $payment_date,
                    'settle_amount' => $settle_amount,
                    'settle_currency' => $settle_currency,
                    'exchange_rate' => $exchange_rate,
                    'payment_gross' => $payment_gross,
                    'payment_fee' => $payment_fee,
                    'mc_gross' => $mc_gross,
                    'mc_fee' => $mc_fee,
                    'mc_currency' => $mc_currency,
                    'tax' => $tax,
                    'txn_type' => $txn_type,
                    'memo' => $memo,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'address_street' => $address_street,
                    'address_city' => $address_city,
                    'address_state' => $address_state,
                    'address_zip' => $address_zip,
                    'address_country' => $address_country,
                    'address_status' => $address_status,
                    'payer_email' => $payer_email,
                    'payer_id' => $payer_id,
                    'payer_status' => $payer_status,
                    'payment_type' => $payment_type,
                    'notify_version' => $notify_version,
                    'verify_sign' => $verify_sign);

                tep_db_perform(TABLE_PAYPALIPN_TXN, $sql_data_array);
            } else { // else we update it to the new status
                $sql_data_array = array('payment_status' => $payment_status,
                    'pending_reason' => $pending_reason,
                    'ipn_result' => $ipn_result,
                    'payer_email' => $payer_email,
                    'payer_id' => $payer_id,
                    'payer_status' => $payer_status,
                    'payment_type' => $payment_type);

                tep_db_perform(TABLE_PAYPALIPN_TXN, $sql_data_array, 'update', 'txn_id=\'' . $txn_id . '\'');
            };
        };

        if ($response_verified == 1) {
            if (strtolower($receiver_email) == strtolower(MODULE_PAYMENT_PAYPALIPN_ID) || strtolower($business) == strtolower(MODULE_PAYMENT_PAYPALIPN_ID)) {
                if ($payment_status == 'Completed') {
                    $stock_updated = false;
                    if (MODULE_PAYMENT_PAYPALIPN_UPDATE_STOCK_BEFORE_PAYMENT == 'False' && !\common\helpers\Order::is_stock_updated((int) $item_number)) {
                        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                            // Stock Update - Joao Correia
                            if (STOCK_LIMITED == 'true') {
                                \common\helpers\Product::log_stock_history_before_update($order->products[$i]['id'], $order->products[$i]['qty'], '-', ['comments' => TEXT_ORDER_STOCK_UPDATE, 'orders_id' => $item_number]);
                                \common\helpers\Product::update_stock($order->products[$i]['id'], 0, $order->products[$i]['qty']);
                                $stock_updated = true;
                            }
                        }
                    }

                    if ($_POST['txn_type'] == 'subscr_payment') { //subscription payment
                    }

                    if (is_numeric(MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID) && (MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID > 0)) {
                        $order_status = MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID;
                    } else {
                        $order_status = DEFAULT_ORDERS_STATUS_ID;
                    };
                    $order->info['order_status'] = $order_status;

                    $sql_data_array = array('orders_status' => $order_status);
                    if ($stock_updated === true) {
                        $sql_data_array['stock_updated'] = 1;
                    }

                    tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', 'orders_id=' . $item_number);

                    $pp_result = 'Transaction ID: ' . \common\helpers\Output::output_string_protected($txn_id) . "\n" .
                            'Payer Status: ' . \common\helpers\Output::output_string_protected($payer_status) . "\n" .
                            'Address Status: ' . \common\helpers\Output::output_string_protected($address_status) . "\n" .
                            'Payment Status: ' . \common\helpers\Output::output_string_protected($payment_status) . "\n" .
                            'Payment Type: ' . \common\helpers\Output::output_string_protected($payment_type);

                    $order->info['comments'] = $pp_result;

                    $order->update_piad_information(true);

                    $order->save_details();

                    $order->totals = $order_totals;
                    /*
                      $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
                      $sql_data_array = array('orders_id' => $item_number,
                      'orders_status_id' => $order_status,
                      'date_added' => 'now()',
                      'customer_notified' => $customer_notification,
                      'comments' => $pp_result);
                      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array); */

                    // lets start with the email confirmation
                    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                        if (sizeof($order->products[$i]['attributes']) > 0) {
                            $attributes_exist = '1';
                            $products_ordered_attributes = "\n";
                            for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                                $products_ordered_attributes .= '  ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
                                if ($order->products[$i]['attributes'][$j]['price'] != '0')
                                    $products_ordered_attributes .= ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')' . "\n";
                            }
                        }

                        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
                        $products_ordered_attributes = '';
                    }



                    // build the message content
                    $email_params = array();
                    $email_params['STORE_NAME'] = STORE_NAME;
                    $email_params['ORDER_NUMBER'] = $item_number;
                    $email_params['ORDER_DATE_SHORT'] = strftime(DATE_FORMAT_SHORT);
                    $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $item_number, 'SSL', false));
                    $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
                    if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'mailInfo')){
                        $email_params['ORDER_DATE_LONG'] .= $ext::mailInfo($order->info['delivery_date']);
                    }
                    $email_params['PRODUCTS_ORDERED'] = substr($products_ordered, 0, -1);

                    $email_params['ORDER_TOTALS'] = '';
                    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
                        $email_params['ORDER_TOTALS'] .= strip_tags($order->totals[$i]['title']) . ' ' . strip_tags($order->totals[$i]['text']) . "\n";
                    }
                    $email_params['ORDER_TOTALS'] = substr($email_params['ORDER_TOTALS'], 0, -1);
                    $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, false, '', "\n");
                    $email_params['DELIVERY_ADDRESS'] = ($order->content_type != 'virtual' ? \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, false, '', "\n") : '');

                    $payment_method = '';
                    if (is_object($$payment)) {
                        $payment_class = $$payment;
                        $payment_method .= $payment_class->title;
                        if ($payment_class->email_footer) {
                            $payment_method .= "\n\n" . $payment_class->email_footer;
                        }
                    }
                    $email_params['PAYMENT_METHOD'] = $payment_method;

                    $email_params['ORDER_COMMENTS'] = tep_db_output($order->info['comments']);

                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Order Confirmation', $email_params);

                    \common\helpers\Mail::send($order->customer['name'], $order->customer['email_address'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');

                    if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                        \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                    } else { // send emails to other people
                        //            \common\helpers\Mail::send('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                        \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO);
                    }

                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id=" . (int) $order->customer['id']);
                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id=" . (int) $order->customer['id']);
                    
                    if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_after_order_placed')){
                        $ext::rf_after_order_placed($item_number);
                    }
                    
                    if($ext = \common\helpers\Acl::checkExtension('CustomerLoyalty', 'afterOrderCreate')){
                        $ext::afterOrderCreate($item_number);
                    }
                    
                } elseif ($_POST['subscr_id'] != '') {
                    if ($_POST['txn_type'] == 'subscr_cancel') {
                        tep_db_query("update " . TABLE_ORDERS . " set orders_status='100003' where orders_id='" . (int) $item_number . "'");
                        $sql_data_array = array('orders_id' => (int) $item_number,
                            'orders_status_id' => 100003,
                            'date_added' => 'now()',
                            'comments' => "Subscription Cancelled",
                            'customer_notified' => 0);
                        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    } elseif ($_POST['txn_type'] == 'subscr_failed') {
                        tep_db_query("update " . TABLE_ORDERS . " set orders_status='100003' where orders_id='" . (int) $item_number . "'");
                        $sql_data_array = array('orders_id' => (int) $item_number,
                            'orders_status_id' => 100003,
                            'date_added' => 'now()',
                            'comments' => "Subscription Failed",
                            'customer_notified' => 0);
                        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    }
                }
            };
        };
        exit();
    }

    public function actionPaypalExpress() { //for paypal express
        global $customer_id, $customer_default_address_id, $order, $sendto, $billto, $cart, $messageStack, $total_count, $total_weight, $navigation, $currency, $languages_id;
        global $shipping, $customer_country_id, $customer_zone_id, $customer_first_name, $currencies;
        global $ppe_token, $ppe_secret, $payment, $ppe_payerid, $ppe_order_total_check, $comments, $response_array, $ppe_payerstatus, $ppe_addressstatus;

        Translation::init('payment');
        $payment = 'paypal_express';

// initialize variables if the customer is not logged in
        if (!tep_session_is_registered('customer_id')) {
            $customer_id = 0;
            $customer_default_address_id = 0;
        }

        require_once(DIR_WS_MODULES . 'payment/paypal_express.php');
        $paypal_express = new \paypal_express();
        $order = new order();


        if (!$paypal_express->check(PLATFORM_ID) || !$paypal_express->enabled) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        }

        if (!tep_session_is_registered('sendto')) {
            if (tep_session_is_registered('customer_id')) {
                $sendto = $customer_default_address_id;
            } else {
                $country = \common\helpers\Country::get_countries(STORE_COUNTRY, true);

                $sendto = array('firstname' => '',
                    'lastname' => '',
                    'company' => '',
                    'street_address' => '',
                    'suburb' => '',
                    'postcode' => '',
                    'city' => '',
                    'zone_id' => STORE_ZONE,
                    'zone_name' => \common\helpers\Zones::get_zone_name(STORE_COUNTRY, STORE_ZONE, ''),
                    'country_id' => STORE_COUNTRY,
                    'country_name' => $country['countries_name'],
                    'country_iso_code_2' => $country['countries_iso_code_2'],
                    'country_iso_code_3' => $country['countries_iso_code_3'],
                    'address_format_id' => \common\helpers\Address::get_address_format_id(STORE_COUNTRY));
            }
        }

        if (!tep_session_is_registered('billto')) {
            $billto = $sendto;
        }

        // register a random ID in the session to check throughout the checkout procedure
        // against alterations in the shopping cart contents
        if (!tep_session_is_registered('cartID'))
            tep_session_register('cartID');
        $cartID = $cart->cartID;

        switch ($_GET['osC_Action']) {
            case 'cancel':
                tep_session_unregister('ppe_token');
                tep_session_unregister('ppe_secret');

                if (empty($sendto['firstname']) && empty($sendto['lastname']) && empty($sendto['street_address'])) {
                    tep_session_unregister('sendto');
                }

                if (empty($billto['firstname']) && empty($billto['lastname']) && empty($billto['street_address'])) {
                    tep_session_unregister('billto');
                }

                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

                break;
            case 'callbackSet':
                if (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') {
                    $counter = 0;

                    if (isset($_POST['CURRENCYCODE']) && $currencies->is_set($_POST['CURRENCYCODE']) && ($currency != $_POST['CURRENCYCODE'])) {
                        $currency = $_POST['CURRENCYCODE'];
                    }

                    while (true) {
                        if (isset($_POST['L_NUMBER' . $counter])) {
                            $cart->add_cart($_POST['L_NUMBER' . $counter], $_POST['L_QTY' . $counter]);
                        } else {
                            break;
                        }

                        $counter++;
                    }

                    // exit if there is nothing in the shopping cart
                    if ($cart->count_contents() < 1) {
                        exit;
                    }

                    $sendto = array('firstname' => '',
                        'lastname' => '',
                        'company' => '',
                        'street_address' => $_POST['SHIPTOSTREET'],
                        'suburb' => $_POST['SHIPTOSTREET2'],
                        'postcode' => $_POST['SHIPTOZIP'],
                        'city' => $_POST['SHIPTOCITY'],
                        'zone_id' => '',
                        'zone_name' => $_POST['SHIPTOSTATE'],
                        'country_id' => '',
                        'country_name' => $_POST['SHIPTOCOUNTRY'],
                        'country_iso_code_2' => '',
                        'country_iso_code_3' => '',
                        'address_format_id' => '');

                    $country_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($sendto['country_name']) . "' limit 1");
                    if (tep_db_num_rows($country_query)) {
                        $country = tep_db_fetch_array($country_query);

                        $sendto['country_id'] = $country['countries_id'];
                        $sendto['country_name'] = $country['countries_name'];
                        $sendto['country_iso_code_2'] = $country['countries_iso_code_2'];
                        $sendto['country_iso_code_3'] = $country['countries_iso_code_3'];
                        $sendto['address_format_id'] = $country['address_format_id'];
                    }

                    if ($sendto['country_id'] > 0) {
                        $zone_query = tep_db_query("select * from " . TABLE_ZONES . " where zone_country_id = '" . (int) $sendto['country_id'] . "' and (zone_name = '" . tep_db_input($sendto['zone_name']) . "' or zone_code = '" . tep_db_input($sendto['zone_name']) . "') limit 1");
                        if (tep_db_num_rows($zone_query)) {
                            $zone = tep_db_fetch_array($zone_query);

                            $sendto['zone_id'] = $zone['zone_id'];
                            $sendto['zone_name'] = $zone['zone_name'];
                        }
                    }

                    $billto = $sendto;

                    $quotes_array = array();

                    $order = new order;

                    if ($cart->get_content_type() != 'virtual') {
                        $total_weight = $cart->show_weight();
                        $total_count = $cart->count_contents();

                        // load all enabled shipping modules

                        $shipping_modules = new shipping;

                        $free_shipping = false;

                        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                            $pass = false;

                            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                                case 'national':
                                    if ($order->delivery['country_id'] == STORE_COUNTRY) {
                                        $pass = true;
                                    }
                                    break;

                                case 'international':
                                    if ($order->delivery['country_id'] != STORE_COUNTRY) {
                                        $pass = true;
                                    }
                                    break;

                                case 'both':
                                    $pass = true;
                                    break;
                            }

                            if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                                $free_shipping = true;

                                if (file_exists(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php'))
                                    include(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php');
                            }
                        }

                        if ((\common\helpers\Modules::count_shipping_modules() > 0) || ($free_shipping == true)) {
                            if ($free_shipping == true) {
                                $quotes_array[] = array('id' => 'free_free',
                                    'name' => FREE_SHIPPING_TITLE,
                                    'label' => '',
                                    'cost' => '0',
                                    'tax' => '0');
                            } else {
                                // get all available shipping quotes
                                $quotes = $shipping_modules->quote();

                                foreach ($quotes as $quote) {
                                    if (!isset($quote['error'])) {
                                        foreach ($quote['methods'] as $rate) {
                                            $quotes_array[] = array('id' => $quote['id'] . '_' . $rate['id'],
                                                'name' => $quote['module'],
                                                'label' => $rate['title'],
                                                'cost' => $rate['cost'],
                                                'tax' => isset($quote['tax']) ? $quote['tax'] : '0');
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $quotes_array[] = array('id' => 'null',
                            'name' => 'No Shipping',
                            'label' => '',
                            'cost' => '0',
                            'tax' => '0');
                    }


                    $order_total_modules = new order_total;
                    $order_totals = $order_total_modules->process();

                    $params = array('METHOD' => 'CallbackResponse',
                        'CALLBACKVERSION' => $paypal_express->api_version);

                    if (!empty($quotes_array)) {
                        $params['CURRENCYCODE'] = $currency;
                        $params['OFFERINSURANCEOPTION'] = 'false';

                        $counter = 0;
                        $cheapest_rate = null;
                        $cheapest_counter = $counter;

                        foreach ($quotes_array as $quote) {
                            $shipping_rate = $paypal_express->format_raw($quote['cost'] + \common\helpers\Tax::calculate_tax($quote['cost'], $quote['tax']));

                            $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'];
                            $params['L_SHIPPINGOPTIONLABEL' . $counter] = $quote['label'];
                            $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
                            $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

                            if (DISPLAY_PRICE_WITH_TAX == 'false') {
                                $params['L_TAXAMT' . $counter] = $paypal_express->format_raw($order->info['tax']);
                            }

                            if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
                                $cheapest_rate = $shipping_rate;
                                $cheapest_counter = $counter;
                            }

                            $counter++;
                        }

                        $params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
                    } else {
                        $params['NO_SHIPPING_OPTION_DETAILS'] = '1';
                    }

                    $post_string = '';

                    foreach ($params as $key => $value) {
                        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
                    }

                    $post_string = substr($post_string, 0, -1);

                    echo $post_string;
                }

                tep_session_destroy();

                exit;

                break;
            case 'retrieve':
                // if there is nothing in the customers cart, redirect them to the shopping cart page
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                $response_array = $paypal_express->getExpressCheckoutDetails($_GET['token']);

                if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
                    if (!tep_session_is_registered('ppe_secret') || ($response_array['PAYMENTREQUEST_0_CUSTOM'] != $ppe_secret)) {
                        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                    }

                    if (!tep_session_is_registered('payment'))
                        tep_session_register('payment');
                    $payment = $paypal_express->code;

                    if (!tep_session_is_registered('ppe_token'))
                        tep_session_register('ppe_token');
                    $ppe_token = $response_array['TOKEN'];

                    if (!tep_session_is_registered('ppe_payerid'))
                        tep_session_register('ppe_payerid');
                    $ppe_payerid = $response_array['PAYERID'];

                    if (!tep_session_is_registered('ppe_payerstatus'))
                        tep_session_register('ppe_payerstatus');
                    $ppe_payerstatus = $response_array['PAYERSTATUS'];

                    if (!tep_session_is_registered('ppe_addressstatus'))
                        tep_session_register('ppe_addressstatus');
                    $ppe_addressstatus = $response_array['ADDRESSSTATUS'];

                    $force_login = false;

                    // check if e-mail address exists in database and login or create customer account
                    if (!tep_session_is_registered('customer_id')) {
                        $force_login = true;

                        $email_address = tep_db_prepare_input($response_array['EMAIL']);

                        $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
                        if (tep_db_num_rows($check_query)) {
                            $check = tep_db_fetch_array($check_query);

                            // Force the customer to log into their local account if payerstatus is unverified and a local password is set
                            if (($response_array['PAYERSTATUS'] == 'unverified') && !empty($check['customers_password'])) {
                                $messageStack->add_session('login', MODULE_PAYMENT_PAYPAL_EXPRESS_WARNING_LOCAL_LOGIN_REQUIRED, 'warning');

                                $navigation->set_snapshot();

                                $login_url = tep_href_link(FILENAME_LOGIN, '', 'SSL');
                                $login_email_address = \common\helpers\Output::output_string($response_array['EMAIL']);

                                $output = <<<EOD
<form name="pe" action="{$login_url}" method="post" target="_top">
 <input type="hidden" name="email_address" value="{$login_email_address}" />
</form>
<script type="text/javascript">
document.pe.submit();
</script>
EOD;

                                echo $output;
                                exit;
                            } else {
                                $customer_id = $check['customers_id'];
                                $customers_firstname = $check['customers_firstname'];
                                $customer_default_address_id = $check['customers_default_address_id'];
                            }
                        } else {
                            $customers_firstname = tep_db_prepare_input($response_array['FIRSTNAME']);
                            $customers_lastname = tep_db_prepare_input($response_array['LASTNAME']);

                            $sql_data_array = array('customers_firstname' => $customers_firstname,
                                'customers_lastname' => $customers_lastname,
                                'customers_email_address' => $email_address,
                                'customers_telephone' => '',
                                'customers_fax' => '',
                                'customers_newsletter' => '0',
                                'customers_password' => '');

                            if (isset($response_array['PHONENUM']) && tep_not_null($response_array['PHONENUM'])) {
                                $customers_telephone = tep_db_prepare_input($response_array['PHONENUM']);

                                $sql_data_array['customers_telephone'] = $customers_telephone;
                            }

                            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

                            $customer_id = tep_db_insert_id();

                            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $customer_id . "', '0', now())");
                            
                            if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_track_after_customer_create')){
                                $ext::rf_track_after_customer_create($customer_id);
                            }

                            // Only generate a password and send an email if the Set Password Content Module is not enabled
                            if (!defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS') || (MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS != 'True')) {
                                $customer_password = \common\helpers\Password::create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

                                tep_db_perform(TABLE_CUSTOMERS, array('customers_password' => \common\helpers\Password::encrypt_password($customer_password)), 'update', 'customers_id = "' . (int) $customer_id . '"');

                                // build the message content
                                if (!tep_session_is_registered('guest_email_address')) {
                                    $name = $customers_firstname . ' ' . $customers_lastname;
                                    //$email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . sprintf(MODULE_PAYMENT_PAYPAL_EXPRESS_EMAIL_PASSWORD, $email_address, $customer_password) . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
                                    // {{
                                    $email_params = array();
                                    $email_params['STORE_NAME'] = STORE_NAME;
                                    $email_params['USER_GREETING'] = trim(sprintf(EMAIL_GREET_NONE, $name));
                                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
                                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Confirmation', $email_params);
                                    // }}
                                    \common\helpers\Mail::send($name, $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                                }
                            }
                        }

                        if (SESSION_RECREATE == 'True') {
                            tep_session_recreate();
                        }

                        $customer_first_name = $customers_firstname;
                        tep_session_register('customer_id');
                        tep_session_register('customer_first_name');

                        // reset session token
                        $sessiontoken = md5(\common\helpers\Password::rand() . \common\helpers\Password::rand() . \common\helpers\Password::rand() . \common\helpers\Password::rand());
                    }

                    // check if paypal shipping address exists in the address book
                    $ship_firstname = tep_db_prepare_input(substr($response_array['PAYMENTREQUEST_0_SHIPTONAME'], 0, strpos($response_array['PAYMENTREQUEST_0_SHIPTONAME'], ' ')));
                    $ship_lastname = tep_db_prepare_input(substr($response_array['PAYMENTREQUEST_0_SHIPTONAME'], strpos($response_array['PAYMENTREQUEST_0_SHIPTONAME'], ' ') + 1));
                    $ship_address = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTREET']);
                    $ship_address2 = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTREET2']);
                    $ship_city = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOCITY']);
                    $ship_zone = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTATE']);
                    $ship_zone_id = 0;
                    $ship_postcode = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOZIP']);
                    $ship_country = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']);
                    $ship_country_id = 0;
                    $ship_address_format_id = 1;

                    $country_query = tep_db_query("select countries_id, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($ship_country) . "' limit 1");
                    if (tep_db_num_rows($country_query)) {
                        $country = tep_db_fetch_array($country_query);

                        $ship_country_id = $country['countries_id'];
                        $ship_address_format_id = $country['address_format_id'];
                    }

                    if ($ship_country_id > 0) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $ship_country_id . "' and (zone_name = '" . tep_db_input($ship_zone) . "' or zone_code = '" . tep_db_input($ship_zone) . "') limit 1");
                        if (tep_db_num_rows($zone_query)) {
                            $zone = tep_db_fetch_array($zone_query);

                            $ship_zone_id = $zone['zone_id'];
                        }
                    }

                    $check_query = tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer_id . "' and entry_firstname = '" . tep_db_input($ship_firstname) . "' and entry_lastname = '" . tep_db_input($ship_lastname) . "' and entry_street_address = '" . tep_db_input($ship_address) . "' and entry_postcode = '" . tep_db_input($ship_postcode) . "' and entry_city = '" . tep_db_input($ship_city) . "' and (entry_state = '" . tep_db_input($ship_zone) . "' or entry_zone_id = '" . (int) $ship_zone_id . "') and entry_country_id = '" . (int) $ship_country_id . "' limit 1");
                    if (tep_db_num_rows($check_query)) {
                        $check = tep_db_fetch_array($check_query);

                        $sendto = $check['address_book_id'];
                    } else {
                        $sql_data_array = array('customers_id' => $customer_id,
                            'entry_firstname' => $ship_firstname,
                            'entry_lastname' => $ship_lastname,
                            'entry_street_address' => $ship_address,
                            'entry_suburb' => $ship_address2,
                            'entry_postcode' => $ship_postcode,
                            'entry_city' => $ship_city,
                            'entry_country_id' => $ship_country_id);

                        if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
                            if ($ship_zone_id > 0) {
                                $sql_data_array['entry_zone_id'] = $ship_zone_id;
                                $sql_data_array['entry_state'] = '';
                            } else {
                                $sql_data_array['entry_zone_id'] = '0';
                                $sql_data_array['entry_state'] = $ship_zone;
                            }
                        }

                        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

                        $address_id = tep_db_insert_id();

                        $sendto = $address_id;

                        if ($customer_default_address_id < 1) {
                            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");
                            $customer_default_address_id = $address_id;
                        }
                    }

                    $billto = $sendto;

                    if (!tep_session_is_registered('sendto')) {
                        tep_session_register('sendto');
                    }

                    if (!tep_session_is_registered('billto')) {
                        tep_session_register('billto');
                    }

                    if ($force_login == true) {
                        $customer_country_id = $ship_country_id;
                        $customer_zone_id = $ship_zone_id;
                        tep_session_register('customer_default_address_id');
                        tep_session_register('customer_country_id');
                        tep_session_register('customer_zone_id');
                    }

                    $order = new order;

                    if ($cart->get_content_type() != 'virtual') {
                        $total_weight = $cart->show_weight();
                        $total_count = $cart->count_contents();

                        // load all enabled shipping modules

                        $shipping_modules = new shipping;

                        $free_shipping = false;

                        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                            $pass = false;

                            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                                case 'national':
                                    if ($order->delivery['country_id'] == STORE_COUNTRY) {
                                        $pass = true;
                                    }
                                    break;

                                case 'international':
                                    if ($order->delivery['country_id'] != STORE_COUNTRY) {
                                        $pass = true;
                                    }
                                    break;

                                case 'both':
                                    $pass = true;
                                    break;
                            }

                            if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                                $free_shipping = true;

                                if (file_exists(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php'))
                                    include(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php');
                            }
                        }

                        if (!tep_session_is_registered('shipping'))
                            tep_session_register('shipping');
                        $shipping = false;

                        if ((\common\helpers\Modules::count_shipping_modules() > 0) || ($free_shipping == true)) {
                            if ($free_shipping == true) {
                                $shipping = 'free_free';
                            } else {
                                // get all available shipping quotes
                                $quotes = $shipping_modules->quote();

                                $shipping_set = false;

                                // if available, set the selected shipping rate from PayPals order review page
                                if (isset($response_array['SHIPPINGOPTIONNAME']) && isset($response_array['SHIPPINGOPTIONAMOUNT'])) {
                                    foreach ($quotes as $quote) {
                                        if (!isset($quote['error'])) {
                                            foreach ($quote['methods'] as $rate) {
                                                if ($response_array['SHIPPINGOPTIONNAME'] == trim($quote['module'] . ' ' . $rate['title'])) {
                                                    $shipping_rate = $paypal_express->format_raw($rate['cost'] + \common\helpers\Tax::calculate_tax($rate['cost'], $quote['tax']));

                                                    if ($response_array['SHIPPINGOPTIONAMOUNT'] == $shipping_rate) {
                                                        $shipping = $quote['id'] . '_' . $rate['id'];
                                                        $shipping_set = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if ($shipping_set == false) {
                                    // select cheapest shipping method
                                    $shipping = $shipping_modules->cheapest();
                                    $shipping = $shipping['id'];
                                }
                            }
                        } else {
                            if (defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False')) {
                                tep_session_unregister('shipping');

                                $messageStack->add_session('checkout_address', MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS, 'error');

                                tep_session_register('ppec_right_turn');
                                $ppec_right_turn = true;

                                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
                            }
                        }

                        if (strpos($shipping, '_')) {
                            list($module, $method) = explode('_', $shipping);

                            if (is_object($GLOBALS[$module]) || ($shipping == 'free_free')) {
                                if ($shipping == 'free_free') {
                                    $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                                    $quote[0]['methods'][0]['cost'] = '0';
                                } else {
                                    $quote = $shipping_modules->quote($method, $module);
                                }

                                if (isset($quote['error'])) {
                                    tep_session_unregister('shipping');

                                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                                } else {
                                    if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                                        $shipping = array('id' => $shipping,
                                            'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' ' . $quote[0]['methods'][0]['title']),
                                            'cost' => $quote[0]['methods'][0]['cost']);
                                        $_SESSION['shipping'] = $shipping;
                                    }
                                }
                            }
                        }
                    } else {
                        if (!tep_session_is_registered('shipping'))
                            tep_session_register('shipping');
                        $shipping = false;
                        $_SESSION['shipping'] = $shipping;
                        $sendto = false;
                    }

                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
                } else {
                    $messageStack->add_session('header', stripslashes($response_array['L_LONGMESSAGE0']), 'error');

                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                break;

            default:
                // if there is nothing in the customers cart, redirect them to the shopping cart page
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
                    $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&';
                } else {
                    $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&';
                }

                $order = new order;

                $params = array('PAYMENTREQUEST_0_CURRENCYCODE' => $order->info['currency'],
                    'ALLOWNOTE' => 0);

                // A billing address is required for digital orders so we use the shipping address PayPal provides
                //      if ($order->content_type == 'virtual') {
                //        $params['NOSHIPPING'] = '1';
                //      }

                $item_params = array();

                $line_item_no = 0;

                foreach ($order->products as $product) {
                    // {{
                    if ($product['wristband_count'] == 1) {
                        $product['final_price'] *= $product['qty'];
                        $product['name'] = $product['qty'] . ' ' . $product['name'];
                        $product['qty'] = 1;
                    }
                    // }}
                    if (DISPLAY_PRICE_WITH_TAX == 'true') {
                        $product_price = $paypal_express->format_raw($product['final_price'] + \common\helpers\Tax::calculate_tax($product['final_price'], $product['tax']));
                    } else {
                        $product_price = $paypal_express->format_raw($product['final_price']);
                    }

                    $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $product['name'];
                    $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $product_price;
                    $item_params['L_PAYMENTREQUEST_0_NUMBER' . $line_item_no] = $product['id'];
                    $item_params['L_PAYMENTREQUEST_0_QTY' . $line_item_no] = $product['qty'];
                    $item_params['L_PAYMENTREQUEST_0_ITEMURL' . $line_item_no] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product['id'], 'NONSSL', false);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($product['attributes'])) {
                        $item_params['L_PAYMENTREQUEST_n_ITEMCATEGORY' . $line_item_no] = $paypal_express->getProductType($product['id'], $product['attributes']);
                    } else {
                        $item_params['L_PAYMENTREQUEST_n_ITEMCATEGORY' . $line_item_no] = 'Physical';
                    }

                    $line_item_no++;
                }

                if (tep_not_null($order->delivery['street_address'])) {
                    $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $order->delivery['suburb'];
                    $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = \common\helpers\Zones::get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
                    $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
                    $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
                }

                $quotes_array = array();

                if ($cart->get_content_type() != 'virtual') {
                    $total_weight = $cart->show_weight();
                    $total_count = $cart->count_contents();

                    // load all enabled shipping modules

                    $shipping_modules = new shipping;

                    $free_shipping = false;

                    if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                        $pass = false;

                        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                            case 'national':
                                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                                    $pass = true;
                                }
                                break;

                            case 'international':
                                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                                    $pass = true;
                                }
                                break;

                            case 'both':
                                $pass = true;
                                break;
                        }

                        if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                            $free_shipping = true;

                            if (file_exists(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php'))
                                include(DIR_WS_LANGUAGES . '/modules/order_total/ot_shipping.php');
                        }
                    }

                    if ((\common\helpers\Modules::count_shipping_modules() > 0) || ($free_shipping == true)) {
                        if ($free_shipping == true) {
                            $quotes_array[] = array('id' => 'free_free',
                                'name' => FREE_SHIPPING_TITLE,
                                'label' => '',
                                'cost' => '0.00',
                                'tax' => '0');
                        } else {
                            // get all available shipping quotes
                            $quotes = $shipping_modules->quote();

                            foreach ($quotes as $quote) {
                                if (!isset($quote['error'])) {
                                    foreach ($quote['methods'] as $rate) {
                                        $quotes_array[] = array('id' => $quote['id'] . '_' . $rate['id'],
                                            'name' => $quote['module'],
                                            'label' => $rate['title'],
                                            'cost' => $rate['cost'],
                                            'tax' => $quote['tax']);
                                    }
                                }
                            }
                        }
                    } else {
                        if (defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False')) {
                            tep_session_unregister('shipping');

                            $messageStack->add_session('checkout_address', MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS);

                            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
                        }
                    }
                }

                $counter = 0;
                $cheapest_rate = null;
                $expensive_rate = 0;
                $cheapest_counter = $counter;
                $default_shipping = null;
                global $select_shipping;
                foreach ($quotes_array as $quote) {
                    $shipping_rate = $paypal_express->format_raw($quote['cost'] + \common\helpers\Tax::calculate_tax($quote['cost'], $quote['tax']));

                    $item_params['L_SHIPPINGOPTIONNAME' . $counter] = trim($quote['name'] . ' ' . $quote['label']);
                    $item_params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
                    $item_params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

                    if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
                        $cheapest_rate = $shipping_rate;
                        $cheapest_counter = $counter;
                    }

                    if ($shipping_rate > $expensive_rate) {
                        $expensive_rate = $shipping_rate;
                    }

                    if ((tep_session_is_registered('shipping') && ($shipping['id'] == $quote['id'])) || (!is_null($select_shipping) && $select_shipping == $quote['id'])) {
                        $default_shipping = $counter;
                    }

                    $counter++;
                }

                if (!is_null($default_shipping)) {
                    $cheapest_rate = $item_params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping];
                    $cheapest_counter = $default_shipping;
                } else {
                    if (!empty($quotes_array)) {
                        $shipping = array('id' => $quotes_array[$cheapest_counter]['id'],
                            'title' => $item_params['L_SHIPPINGOPTIONNAME' . $cheapest_counter],
                            'cost' => $paypal_express->format_raw($quotes_array[$cheapest_counter]['cost']));

                        $default_shipping = $cheapest_counter;
                    } else {
                        $shipping = false;
                    }

                    if (!tep_session_is_registered('shipping')) {
                        tep_session_register('shipping');
                    }
                }

                // set shipping for order total calculations; shipping in $item_params includes taxes
                if (!is_null($default_shipping)) {
                    $order->info['shipping_method'] = $item_params['L_SHIPPINGOPTIONNAME' . $default_shipping];
                    $order->info['shipping_cost'] = $item_params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping]; // TODO check for non default currency:  * $currencies->get_market_price_rate($order->info['currency'], DEFAULT_CURRENCY);

                    $order->info['total'] = $order->info['subtotal'] + $order->info['shipping_cost'];

                    if (DISPLAY_PRICE_WITH_TAX == 'false') {
                        $order->info['total'] += $order->info['tax'];
                    }
                }

                if (!is_null($cheapest_rate)) {
                    $item_params['PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED'] = 'false';
                    $item_params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
                }

                if (!empty($quotes_array) && (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') && ((MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER != 'Live') || ((MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') && (ENABLE_SSL == true)))) { // Live server requires SSL to be enabled
                    $item_params['CALLBACK'] = tep_href_link('callback/paypal-express', 'osC_Action=callbackSet', 'SSL', false, false);
                    $item_params['CALLBACKTIMEOUT'] = '6';
                    $item_params['CALLBACKVERSION'] = $paypal_express->api_version;
                }


                $order_total_modules = new order_total;
                $order_totals = $order_total_modules->process();

                // Remove shipping tax from total that was added again in ot_shipping
                if (DISPLAY_PRICE_WITH_TAX == 'true')
                    $order->info['shipping_cost'] = $order->info['shipping_cost'] / (1.0 + ($quotes_array[$default_shipping]['tax'] / 100));
                $module = substr($shipping['id'], 0, strpos($shipping['id'], '_'));
                $order->info['tax'] -= \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
                $tax_desc = \common\helpers\Tax::get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                $order->info['tax_groups'][$tax_desc] -= \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
                $order->info['total'] -= \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);

                $items_total = $paypal_express->format_raw($order->info['subtotal']);

                foreach ($order_totals as $ot) {
                    if (!in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total', 'ot_subtax'))) {
                        $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $ot['title'];
                        $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $paypal_express->format_raw($ot['value']);

                        $items_total += $paypal_express->format_raw($ot['value']);

                        $line_item_no++;
                    }
                }

                $params['PAYMENTREQUEST_0_AMT'] = $paypal_express->format_raw($order->info['total']);

                $item_params['MAXAMT'] = $paypal_express->format_raw($params['PAYMENTREQUEST_0_AMT'] + $expensive_rate + 100, '', 1); // safely pad higher for dynamic shipping rates (eg, USPS express)
                $item_params['PAYMENTREQUEST_0_ITEMAMT'] = $items_total;
                $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'] = $paypal_express->format_raw($order->info['shipping_cost']);

                $paypal_item_total = $item_params['PAYMENTREQUEST_0_ITEMAMT'] + $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'];

                if (DISPLAY_PRICE_WITH_TAX == 'false') {
                    $item_params['PAYMENTREQUEST_0_TAXAMT'] = $paypal_express->format_raw($order->info['tax']);

                    $paypal_item_total += $item_params['PAYMENTREQUEST_0_TAXAMT'];
                }

                if ($paypal_express->format_raw($paypal_item_total) == $params['PAYMENTREQUEST_0_AMT']) {
                    $params = array_merge($params, $item_params);
                }

                if (tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE)) {
                    $params['PAGESTYLE'] = MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE;
                }

                $ppe_secret = \common\helpers\Password::create_random_value(16, 'digits');

                if (!tep_session_is_registered('ppe_secret')) {
                    tep_session_register('ppe_secret');
                }

                $params['PAYMENTREQUEST_0_CUSTOM'] = $ppe_secret;

                // Log In with PayPal token for seamless checkout
                if (tep_session_is_registered('paypal_login_access_token')) {
                    $params['IDENTITYACCESSTOKEN'] = $paypal_login_access_token;
                }


                $response_array = $paypal_express->setExpressCheckout($params);
                //      print_r($response_array); exit;

                if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
                    tep_redirect($paypal_url . 'token=' . $response_array['TOKEN'] . '&useraction=commit');
                } else {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
                }

                break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
    }

    public function actionRedirectSagePay() {
        global $navigation, $request_type, $payment;
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        if (isset($_GET['payment_error']) && tep_not_null($_GET['payment_error'])) {
            $redirect_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $_GET['payment_error'] . (isset($_GET['error']) && tep_not_null($_GET['error']) ? '&error=' . $_GET['error'] : ''), 'SSL');
        } else {
            $hidden_params = '';

            if ($payment == 'sage_pay_direct') {
                $redirect_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=3D', 'SSL');
                $hidden_params = tep_draw_hidden_field('MD', $_POST['MD']) . tep_draw_hidden_field('PaRes', $_POST['PaRes']);
            } else {
                $redirect_url = tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
            }
        }

        $this->layout = false;

        Translation::init('checkout/confirmation');
        ?>
        <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
        <html <?php echo HTML_PARAMS; ?>>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
                <title><?php echo TITLE; ?></title>
                <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
                <link rel="stylesheet" type="text/css" href="stylesheet.css">
            </head>
            <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
                <form name="redirect" action="<?php echo $redirect_url; ?>" method="post" target="_top"><?php echo $hidden_params; ?>
                    <noscript>
                        <p align="center" class="main">The transaction is being finalized. Please click continue to finalize your order.</p>
                        <p align="center" class="main"><input type="submit" value="Continue" /></p>
                    </noscript>
                </form>
                <script language="javascript">
                    document.redirect.submit();
                </script>
            </body>
        </html>
        <?php
        exit();
    }

    public function actionWebhooks($set, $module) {
        if (file_exists(DIR_WS_MODULES . $set . '/' . $module . '.php')) {
            require_once(DIR_WS_MODULES . $set . '/' . $module . '.php');
            $payment = new $module();
            $payment->call_webhooks();
        }
        exit();
    }

    public function actionKlarna() {
        $action = Yii::$app->request->get('action');
        
        $klarnaCheckout = new KlarnaCheckoutModel();
        
        switch($action){
            case 'push':
                $checkoutId = null;
                if (isset($_GET['klarna_order']))
                {
                    $checkoutId = $_GET['klarna_order'];
                }
                $sessionId = null;
                if (isset($_GET[tep_session_name()]))
                {
                    $sessionId = $_GET[tep_session_name()];
                }
                
                $platform = \common\classes\platform::defaultId();
                if (isset($_GET['platform']))
                {
                    $platform = $_GET['platform'];
                }   
                $platform_config = new \common\classes\platform_config($platform);
                $platform_config->constant_up();

                $klarnaCheckout->callback($checkoutId, $sessionId);
                exit();
                break;
            case 'confirm':
            default:
                $checkoutId = null;
                if (isset($_GET['klarna_order']))
                {
                    $checkoutId = $_GET['klarna_order'];
                }

                $klarnaOrder = $klarnaCheckout->confirm($checkoutId);
                $klarnaWidget = Klarna::widget(['klarnaOrder' => $klarnaOrder]);
                return $this->render('index', ['content' => $klarnaWidget]);
                
                break;
        }
    }

}
