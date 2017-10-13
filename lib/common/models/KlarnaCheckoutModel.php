<?php

namespace common\models;

use Yii;
use common\helpers\Tax;
use common\classes\shipping;
use common\classes\order;
use common\classes\order_total;

require_once('klarna/Checkout.php');

class KlarnaCheckoutModel {

    const ORDER_CONTENT_TYPE = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
    const ORDER_URL_TEST = 'https://checkout.testdrive.klarna.com/checkout/orders';
    const ORDER_URL_LIVE = 'https://checkout.klarna.com/checkout/orders';

    function register() {
        global $cart, $currency, $language, $lng;

        $this->_setOrderUrl();
        $this->_setOrderContentType();

        $connector = Klarna_Checkout_Connector::create(MODULE_PAYMENT_KLARNA_CHECKOUT_SECRET);

        $checkoutId = null;
        if (isset($_SESSION['klarna_order'])) {
            $checkoutId = $_SESSION['klarna_order'];
        }

        if (isset($_SESSION['klarna_error'])) {
            unset($_SESSION['klarna_error']);
        }

        if ($checkoutId && $_SESSION['selected_currency'] == $currency) {
            $klarnaOrder = new Klarna_Checkout_Order($connector, $checkoutId);
            try {
                $klarnaOrder->fetch();
            } catch (Klarna_Checkout_Exception $e) {
                $_SESSION['klarna_error'] = $e->getMessage();
                return false;
            }

            $update = array();
            $update['cart'] = $this->_formatCartItems();

            try {
                $klarnaOrder->update($update);
            } catch (Exception $e) {
                $_SESSION['klarna_error'] = $e->getMessage();
                return false;
            }
        } else {
            $klarnaOrder = new Klarna_Checkout_Order($connector);
            $country = $this->getCountryById(STORE_COUNTRY);
            
            $locales = ['SE' => 'sv-se', 'FI' => 'fi-fi', 'NO' => 'nb-no', 'DE' => 'de-de', 'AT' => 'de-at'];

            $create = array();
            $create['purchase_country'] = $country['countries_iso_code_2'];//'SE';
            $create['purchase_currency'] = $currency;//'SEK'; // replace currency
            $create['locale'] = $locales[$country['countries_iso_code_2']];//'sv-se';
            $create['merchant']['id'] = MODULE_PAYMENT_KLARNA_CHECKOUT_EID;
            $create['merchant']['terms_uri'] = MODULE_PAYMENT_KLARNA_CHECKOUT_CONDITIONS;
            $create['merchant']['checkout_uri'] = tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL');
            $create['merchant']['confirmation_uri'] = Yii::$app->urlManager->createAbsoluteUrl(['callback/klarna', 'action' => 'confirm'], 'https') . "&klarna_order={checkout.order.uri}"; //HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'klarna_checkout.php?action=confirm&klarna_order={checkout.order.uri}';
            $create['merchant']['push_uri'] = Yii::$app->urlManager->createAbsoluteUrl(['callback/klarna', 'action' => 'push', tep_session_name() => tep_session_id(), 'platform' => PLATFORM_ID], 'https') . "&klarna_order={checkout.order.uri}"; //HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'klarna_checkout.php?action=push&klarna_order={checkout.order.uri}' . '&session_id=' . session_id();

            $create['cart'] = $this->_formatCartItems();
            $_SESSION['selected_currency'] = $currency;
            try {
                $klarnaOrder->create($create);
            } catch (Exception $e) {
                $_SESSION['klarna_error'] = $e->getMessage();
                return false;
            }
        }

        try {
            $klarnaOrder->fetch();
        } catch (Exception $e) {
            $_SESSION['klarna_error'] = $e->getMessage();
            return false;
        }

        $_SESSION['klarna_order'] = $klarnaOrder->getLocation();

        return $klarnaOrder;
    }

    function _formatCartItems() {
        global $cart, $currencies;

        $cartItems = array();

        foreach ($cart->get_products() as $product) {
            $discountPercent = 0;
            $price = $currencies->format_clear($product['price']);
            $taxPercent = Tax::get_tax_rate($product['tax_class_id']);
            $taxAmount = Tax::calculate_tax($price, $taxPercent);
            $priceInclTax = $price + $taxAmount;

            $name = utf8_encode($product['name']);

            $cartItems['items'][] = array(
                'reference' => "{$product['model']}",
                'name' => $name,
                'quantity' => round($product['quantity'], 0),
                'unit_price' => round($priceInclTax, 2) * 100,
                'discount_rate' => round($discountPercent, 2) * 100,
                'tax_rate' => round($taxPercent, 2) * 100
            );
        }

        global $select_shipping;

        $shipping_modules = new shipping();
        $quotes = $shipping_modules->quote();
        $cheapest_shipping_array = $shipping_modules->cheapest();
        if (is_array($cheapest_shipping_array)) {
            $cheapest_shipping = $cheapest_shipping_array['id'];
        }
        if (empty($select_shipping)) {
            $select_shipping = $cheapest_shipping;
        }
        /*
          $shipping_method[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
          $shipping_method[0]['methods'][0]['cost'] = '0';
          $free_shipping = true;
          $shipping = 'free_free'; */
        $ex = explode("_", $select_shipping);
        $_tax = 0;
        foreach ($quotes as $quote) {
            if ($quote['id'] == MODULE_PAYMENT_KLARNA_CHECKOUT_SHIPPING_METHOD) {
                $shipping_method[0] = $quote;
                $method = $quote['methods'][0];
                $free_shipping = false;
                $shipping = $quote['id'] . '_' . $method['id'];

                $taxPercent = 0;
                $_tax = (float) $quote['tax'];

                $priceInclTax = $currencies->format_clear(\common\helpers\Tax::add_tax($method['cost'], $_tax));
                $title = $quote['module'] . ' - ' . $method['title'];
                $cartItems['items'][] = array(
                    'type' => 'shipping_fee',
                    'reference' => $quote['id'],
                    'name' => mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title)),
                    'quantity' => 1,
                    'unit_price' => round($priceInclTax, 2) * 100,
                    'discount_rate' => 0,
                    'tax_rate' => round($taxPercent, 2) * 100
                );
                break;
            } else if ($quote['id'] == $ex[0] && !MODULE_PAYMENT_KLARNA_CHECKOUT_SHIPPING_METHOD) {

                $shipping_method[0] = $quote;
                $_tax = (float) $quote['tax'];
                foreach ($quote['methods'] as $method) {
                    if ($method['id'] == $ex[1]) {
                        $free_shipping = false;
                        $shipping = $quote['id'] . '_' . $method['id'];

                        $taxPercent = 0;

                        $priceInclTax = $currencies->format_clear(\common\helpers\Tax::add_tax($method['cost'], $_tax));
                        $title = $quote['module'] . ' - ' . $method['title'];
                        $cartItems['items'][] = array(
                            'type' => 'shipping_fee',
                            'reference' => $quote['id'],
                            'name' => mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title)),
                            'quantity' => 1,
                            'unit_price' => round($priceInclTax, 2) * 100,
                            'discount_rate' => 0,
                            'tax_rate' => round($taxPercent, 2) * 100
                        );
                        break;
                    }
                }
//                $method = $quote['methods'][0];
            }
        }

        $_SESSION['shipping'] = array(
            'id' => $shipping,
            'title' => ($free_shipping ? $shipping_method[0]['methods'][0]['title'] : $shipping_method[0]['module'] . ' (' . $shipping_method[0]['methods'][0]['title'] . ')'),
            'cost' => \common\helpers\Tax::add_tax($method['cost'], $_tax)//$shipping_method[0]['methods'][0]['cost']            
        );

        return $cartItems;
    }

    function getShippingMethod() {
        if (isset($_SESSION['shipping'])) {
            return $_SESSION['shipping'];
        }
        return false;
    }

    function confirm($checkoutId) {
        global $order, $cart, $currencies;

        $this->_setOrderContentType();

        $connector = Klarna_Checkout_Connector::create(MODULE_PAYMENT_KLARNA_CHECKOUT_SECRET);

        $klarnaOrder = new Klarna_Checkout_Order($connector, $checkoutId);
        $klarnaOrder->fetch();

        if (!$cart->count_contents() || $klarnaOrder['status'] == 'checkout_incomplete') {
            return false;
        }

        return $klarnaOrder;
    }

    protected function generatePassword() {
        $characters = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
        $min_length = 8;
        $max_length = 20;
        $length = mt_rand($min_length, $max_length);
        $password = '';
        for ($i = 0; $i < $length && $i < count($characters); $i++) {
            $position = mt_rand(0, count($characters) - 1);
            $password .= $characters[$position];
        }
        return $password;
    }

    function getCountryFromISO2($iso2) {
        global $languages_id, $language;
        $countries_query_raw = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 like '" . tep_db_input($iso2) . "' and language_id = '" . tep_db_input($languages_id) . "'";
        $countries_query = tep_db_query($countries_query_raw);
        return tep_db_fetch_array($countries_query);
    }

    function getCountryById($id) {
        $countries_query_raw = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " WHERE countries_id = '" . intval($id) . "'";
        $countries_query = tep_db_query($countries_query_raw);
        return tep_db_fetch_array($countries_query);
    }

    function createAccountFromKlarnaOrder($klarnaOrder) {
        global $customer_id, $address_id, $language, $sendto, $billto;
        $firstname = $klarnaOrder['billing_address']['given_name'];
        $lastname = $klarnaOrder['billing_address']['family_name'];
        $email_address = $klarnaOrder['billing_address']['email'];
        $telephone = $klarnaOrder['billing_address']['phone'];
        $fax = '';
        $newsletter = 1;
        $password = $this->generatePassword();

        $street_address = $klarnaOrder['billing_address']['street_address'];
        $postcode = $klarnaOrder['billing_address']['postal_code'];
        $city = $klarnaOrder['billing_address']['city'];

        $country = $this->getCountryFromISO2($klarnaOrder['billing_address']['country']);
        $country_id = $country['countries_id'];


        $sql_data_array = array('customers_firstname' => $firstname,
            'customers_lastname' => $lastname,
            'customers_email_address' => $email_address,
            'customers_telephone' => $telephone,
            'customers_fax' => $fax,
            'customers_newsletter' => $newsletter,
            'platform_id' => (int) PLATFORM_ID,
            'customers_password' => \common\helpers\Password::encrypt_password($password));

        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

        $customer_id = tep_db_insert_id();

        $billto = $address_id = $this->createAddress($klarnaOrder['billing_address'], $country_id);

        tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");

        $country = $this->getCountryFromISO2($klarnaOrder['shipping_address']['country']);
        $country_id = $country['countries_id'];

        $sendto = $this->createAddress($klarnaOrder['shipping_address'], $country_id);

        tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $customer_id . "', '0', now())");

        $_SESSION['customer_first_name'] = $firstname;
        $_SESSION['customer_id'] = $customer_id;
        
        if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_track_after_customer_create')){
            $ext::rf_track_after_customer_create($customer_id);
        }
    }

    private function createAddress($address, $country_id) {
        global $customer_id;
        $sql_data_array = array('customers_id' => $customer_id,
            'entry_firstname' => tep_db_prepare_input($address['given_name']),
            'entry_lastname' => tep_db_prepare_input($address['family_name']),
            'entry_street_address' => tep_db_prepare_input($address['street_address']),
            'entry_postcode' => tep_db_prepare_input($address['postal_code']),
            'entry_city' => tep_db_prepare_input($address['city']),
            'entry_country_id' => $country_id);

        $sql_data_array['entry_zone_id'] = '0';
        $sql_data_array['entry_state'] = '';
        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
        $id = tep_db_insert_id();
        return $id;
    }

    private function detectAddess($address, $country_id) {
        global $customer_id;
        $address_new = tep_db_fetch_array(tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer_id . "' and entry_street_address = '" . tep_db_input($address['street_address']) . "' and entry_postcode='" . tep_db_input($address['postal_code']) . "' and entry_city='" . tep_db_input($address['city']) . "' and entry_country_id = '" . (int) $country_id . "'"));
        if ($address_new) {
            return $address_new['address_book_id'];
        } else {
            $address_book_id = $this->createAddress($address, $country_id);
            return $address_book_id;
        }
    }

    function callback($checkoutId, $sessionId) {
        global $customer_id, $sendto, $billto, $order_totals, $cart, $order, $order_total_modules, $languages_id, $currencies;
        $this->_setOrderContentType();

        $connector = Klarna_Checkout_Connector::create(MODULE_PAYMENT_KLARNA_CHECKOUT_SECRET);

        $klarnaOrder = new Klarna_Checkout_Order($connector, $checkoutId);
        $klarnaOrder->fetch();
        if ($klarnaOrder['status'] == 'checkout_complete') {
            unset($_SESSION['klarna_order']);

            $shipping_modules = new shipping($_SESSION['shipping']);

            if (!$customer_id) {
                // check if customer with this email exists
                $check_email_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($klarnaOrder['billing_address']['email']) . "'");
                $check_email = tep_db_fetch_array($check_email_query);
                if (@$check_email['customers_id']) {
                    $session = new \yii\web\Session;
                    $customer_id = $check_email['customers_id'];
                    $session['customer_id'] = $_SESSION['customer_id'] = $customer_id;
                    $_SESSION['customer_first_name'] = $check_email['customers_firstname'];
                } else {
                    $this->createAccountFromKlarnaOrder($klarnaOrder);
                }
                $cart->restore_contents();
            }

            global $payment;
            $payment = 'klarna_checkout';

            $ship_country = $this->getCountryFromISO2($klarnaOrder['shipping_address']['country']);
            $bill_country = $this->getCountryFromISO2($klarnaOrder['billing_address']['country']);

            if (!$sendto) {
                $sendto = $this->detectAddess($klarnaOrder['shipping_address'], $ship_country['countries_id']);
            }
            if (!$billto) {
                $billto = $this->detectAddess($klarnaOrder['billing_address'], $bill_country['countries_id']);
            }

            $order = new order;

            $order->customer['firstname'] = $klarnaOrder['billing_address']['given_name'];
            $order->customer['lastname'] = $klarnaOrder['billing_address']['family_name'];
            $order->customer['street_address'] = $klarnaOrder['billing_address']['street_address'];
            $order->customer['city'] = $klarnaOrder['billing_address']['city'];
            $order->customer['postcode'] = $klarnaOrder['billing_address']['postal_code'];
            $order->customer['country'] = array('id' => $bill_country['countries_id'], 'title' => $bill_country['countries_name'], 'iso_code_2' => $bill_country['countries_iso_code_2'], 'iso_code_3' => $bill_country['countries_iso_code_3']);
            $order->customer['telephone'] = $klarnaOrder['billing_address']['phone'];
            $order->customer['email_address'] = $klarnaOrder['billing_address']['email'];

            $order->delivery['firstname'] = $klarnaOrder['shipping_address']['given_name'];
            $order->delivery['lastname'] = $klarnaOrder['shipping_address']['family_name'];
            $order->delivery['street_address'] = $klarnaOrder['shipping_address']['street_address'];
            $order->delivery['city'] = $klarnaOrder['shipping_address']['city'];
            $order->delivery['postcode'] = $klarnaOrder['shipping_address']['postal_code'];
            $order->delivery['country'] = array('id' => $ship_country['countries_id'], 'title' => $ship_country['countries_name'], 'iso_code_2' => $ship_country['countries_iso_code_2'], 'iso_code_3' => $ship_country['countries_iso_code_3']);
            $order->delivery['telephone'] = $klarnaOrder['shipping_address']['phone'];
            $order->delivery['email_address'] = $klarnaOrder['shipping_address']['email'];

            $order->billing['firstname'] = $klarnaOrder['billing_address']['given_name'];
            $order->billing['lastname'] = $klarnaOrder['billing_address']['family_name'];
            $order->billing['street_address'] = $klarnaOrder['billing_address']['street_address'];
            $order->billing['city'] = $klarnaOrder['billing_address']['city'];
            $order->billing['postcode'] = $klarnaOrder['billing_address']['postal_code'];
            $order->billing['country'] = array('id' => $bill_country['countries_id'], 'title' => $bill_country['countries_name'], 'iso_code_2' => $bill_country['countries_iso_code_2'], 'iso_code_3' => $bill_country['countries_iso_code_3']);
            $order->billing['telephone'] = $klarnaOrder['billing_address']['phone'];
            $order->billing['email_address'] = $klarnaOrder['billing_address']['email'];

            $order->info['order_status'] = MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID ? MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID : $order->info['order_status'];
            $order->info['payment_method'] = 'Klarna Checkout';
            $insert_id = $order->save_order();
            /*
              $sql_data_array = array(
              'customers_id' => ($customer_id ? $customer_id : null),
              'customers_name' => $klarnaOrder['billing_address']['given_name'] . ' ' . $klarnaOrder['billing_address']['family_name'],
              'customers_company' => '',
              'customers_street_address' => $klarnaOrder['billing_address']['street_address'],
              'customers_suburb' => '',
              'customers_city' => $klarnaOrder['billing_address']['city'],
              'customers_postcode' => $klarnaOrder['billing_address']['postal_code'],
              'customers_state' => '',
              'customers_country' => $klarnaOrder['billing_address']['country'],
              'customers_telephone' => $klarnaOrder['billing_address']['phone'],
              'customers_email_address' => $klarnaOrder['billing_address']['email'],
              'customers_address_format_id' => 1,

              'delivery_name' => $klarnaOrder['shipping_address']['given_name'] . ' ' . $klarnaOrder['shipping_address']['family_name'],
              'delivery_company' => '',
              'delivery_street_address' => $klarnaOrder['shipping_address']['street_address'],
              'delivery_suburb' => '',
              'delivery_city' => $klarnaOrder['shipping_address']['city'],
              'delivery_postcode' => $klarnaOrder['shipping_address']['postal_code'],
              'delivery_state' => '',
              'delivery_country' => $klarnaOrder['shipping_address']['country'],
              'delivery_address_format_id' => $ship_country['address_format_id'],
              'delivery_address_book_id' => (int)$sendto,

              'billing_name' => $klarnaOrder['billing_address']['given_name'] . ' ' . $klarnaOrder['billing_address']['family_name'],
              'billing_company' => '',
              'billing_street_address' => $klarnaOrder['billing_address']['street_address'],
              'billing_suburb' => '',
              'billing_city' => $klarnaOrder['billing_address']['city'],
              'billing_postcode' => $klarnaOrder['billing_address']['postal_code'],
              'billing_state' => '',
              'billing_country' => $klarnaOrder['billing_address']['country'],
              'billing_address_format_id' => $bill_country['address_format_id'],
              'billing_address_book_id' => (int)$billto,

              'payment_method' => 'Klarna Checkout',
              'payment_class' => $order->info['payment_class'],
              'shipping_class' => $order->info['shipping_class'],
              'cc_type' => '',
              'cc_owner' => '',
              'cc_number' => '',
              'cc_expires' => '',
              'date_purchased' => 'now()',
              'orders_status' => MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID ? MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID : $order->info['order_status'],
              'currency' => $order->info['currency'],
              'currency_value' => $order->info['currency_value'],
              );
              tep_db_perform(TABLE_ORDERS, $sql_data_array);
              $insert_id = tep_db_insert_id();
             */
            global $order_total_modules;
            $order_total_modules = new order_total;
            $order_totals = $order_total_modules->process();

            $order->save_details();
            /* for ($i=0, $n=sizeof($order_totals); $i<$n; $i++)
              {
              $sql_data_array = array(
              'orders_id' => $insert_id,
              'title' => $order_totals[$i]['title'],
              'text' => $order_totals[$i]['text'],
              'value' => $order_totals[$i]['value'],
              'class' => $order_totals[$i]['code'],
              'sort_order' => $order_totals[$i]['sort_order'],
              'text_exc_tax' => $order_totals[$i]['text_exc_tax'],
              'text_inc_tax' => $order_totals[$i]['text_inc_tax'],
              'tax_class_id' => $order_totals[$i]['tax_class_id'],
              'value_exc_vat' => $order_totals[$i]['value_exc_vat'],
              'value_inc_tax' => $order_totals[$i]['value_inc_tax'],
              'is_removed' => 0,
              'currency' => $order->info['currency'],
              'currency_value' => $order->info['currency_value'],
              );
              tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
              } */

            $order->save_products();
            /*
              $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
              $sql_data_array = array(
              'orders_id' => $insert_id,
              'orders_status_id' => MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID,
              'date_added' => 'now()',
              'customer_notified' => $customer_notification,
              'comments' => ''
              );
              tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

              $products_ordered = '';

              for ($i=0, $n = sizeof($order->products); $i < $n; $i++)
              {
              if (STOCK_LIMITED == 'true')
              {
              if (DOWNLOAD_ENABLED == 'true')
              {
              $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
              FROM " . TABLE_PRODUCTS . " p
              LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              ON p.products_id=pa.products_id
              LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
              ON pa.products_attributes_id=pad.products_attributes_id
              WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";

              $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
              if (is_array($products_attributes))
              {
              $stock_query_raw .= " AND pa.options_id = '" . (int)$products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int)$products_attributes[0]['value_id'] . "'";
              }
              $stock_query = tep_db_query($stock_query_raw);
              }
              else
              {
              $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
              }

              if (tep_db_num_rows($stock_query) > 0)
              {
              $stock_values = tep_db_fetch_array($stock_query);

              if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename']))
              {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
              }
              else
              {
              $stock_left = $stock_values['products_quantity'];
              }
              tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
              if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false'))
              {
              tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
              }
              }
              }

              // Update products_ordered (for bestsellers list)
              tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

              $sql_data_array = array(
              'orders_id' => $insert_id,
              'products_id' => tep_get_prid($order->products[$i]['id']),
              'products_model' => $order->products[$i]['model'],
              'products_name' => $order->products[$i]['name'],
              'products_price' => $order->products[$i]['price'],
              'final_price' => $order->products[$i]['final_price'],
              'products_tax' => $order->products[$i]['tax'],
              'products_quantity' => $order->products[$i]['qty']
              );
              tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
              $order_products_id = tep_db_insert_id();

              //------insert customer choosen option to order--------
              $attributes_exist = '0';
              $products_ordered_attributes = '';
              if (isset($order->products[$i]['attributes']))
              {
              $attributes_exist = '1';
              for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++)
              {
              if (DOWNLOAD_ENABLED == 'true')
              {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
              from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
              on pa.products_attributes_id=pad.products_attributes_id
              where pa.products_id = '" . (int)$order->products[$i]['id'] . "'
              and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "'
              and pa.options_id = popt.products_options_id
              and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "'
              and pa.options_values_id = poval.products_options_values_id
              and popt.language_id = '" . (int)$languages_id . "'
              and poval.language_id = '" . (int)$languages_id . "'";
              $attributes = tep_db_query($attributes_query);
              }
              else
              {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$languages_id . "' and poval.language_id = '" . (int)$languages_id . "'");
              }
              $attributes_values = tep_db_fetch_array($attributes);

              $sql_data_array = array(
              'orders_id' => $insert_id,
              'orders_products_id' => $klarnaOrder_products_id,
              'products_options' => $attributes_values['products_options_name'],
              'products_options_values' => $attributes_values['products_options_values_name'],
              'options_values_price' => $attributes_values['options_values_price'],
              'price_prefix' => $attributes_values['price_prefix']
              );
              tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

              if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename']))
              {
              $sql_data_array = array(
              'orders_id' => $insert_id,
              'orders_products_id' => $klarnaOrder_products_id,
              'orders_products_filename' => $attributes_values['products_attributes_filename'],
              'download_maxdays' => $attributes_values['products_attributes_maxdays'],
              'download_count' => $attributes_values['products_attributes_maxcount']
              );
              tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
              }
              $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
              }
              }
              //------insert customer choosen option eof ----
              $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
              }

              // lets start with the email confirmation
              $email_order = STORE_NAME . "\n" .
              EMAIL_SEPARATOR . "\n" .
              EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
              EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
              EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";

              $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
              EMAIL_SEPARATOR . "\n" .
              $products_ordered .
              EMAIL_SEPARATOR . "\n";

              for ($i=0, $n=sizeof($order_totals); $i<$n; $i++)
              {
              $email_order .= strip_tags($klarnaOrder_totals[$i]['title']) . ' ' . strip_tags($klarnaOrder_totals[$i]['text']) . "\n";
              }

              if ($order->content_type != 'virtual')
              {
              $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
              EMAIL_SEPARATOR . "\n" .
              tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
              }

              $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
              EMAIL_SEPARATOR . "\n" .
              tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";

              if (is_object($$payment))
              {
              $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
              EMAIL_SEPARATOR . "\n";
              $payment_class = $$payment;
              $email_order .= $klarnaOrder->info['payment_method'] . "\n\n";
              if (isset($payment_class->email_footer))
              {
              $email_order .= $payment_class->email_footer . "\n\n";
              }
              }

              tep_mail($klarnaOrder['billing_address']['given_name'] . ' ' . $klarnaOrder['billing_address']['family_name'], $klarnaOrder['billing_address']['email'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

              // send emails to other people
              if (SEND_EXTRA_ORDER_EMAILS_TO != '')
              {
              tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
              }
             */

            $_SESSION['last_order_id'] = $insert_id;

            $this->setOrderIdByKlarnaId($checkoutId, $_SESSION['last_order_id']);

            $cart->reset(true);

            // unregister session variables used during checkout
            tep_session_unregister('sendto');
            tep_session_unregister('billto');
            tep_session_unregister('shipping');
            tep_session_unregister('payment');
            tep_session_unregister('comments');
            
            if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_after_order_placed')){
                        $ext::rf_after_order_placed($insert_id);
            }
            
            if($ext = \common\helpers\Acl::checkExtension('CustomerLoyalty', 'afterOrderCreate')){
                $ext::afterOrderCreate($insert_id);
            }

            $update = array(
                'status' => 'created',
                'merchant_reference' => array(
                    'orderid1' => "{$_SESSION['last_order_id']}"
                )
            );
            $klarnaOrder->update($update);
        }
    }

    public function getOrderIdByKlarnaId($klarnaId) {
        $results = tep_db_query("SELECT * FROM klarna_order_reference WHERE klarna_id = '" . tep_db_input($klarnaId) . "'");
        while ($result = tep_db_fetch_array($results)) {
            return $result['order_id'];
        }
        return false;
    }

    public function setOrderIdByKlarnaId($klarnaId, $orderId) {
        if ($this->getOrderIdByKlarnaId($klarnaId)) {
            tep_db_query("UPDATE klarna_order_reference SET order_id = {$orderId} WHERE klarna_id = '" . tep_db_input($klarnaId) . "'");
        } else {
            tep_db_query("INSERT INTO klarna_order_reference SET klarna_id = '" . tep_db_input($klarnaId) . "', order_id = {$orderId}");
        }
    }

    function _setOrderUrl() {
        if (MODULE_PAYMENT_KLARNA_CHECKOUT_TEST_MODE == 'True') {
            Klarna_Checkout_Order::$baseUri = self::ORDER_URL_TEST;
        } else {
            Klarna_Checkout_Order::$baseUri = self::ORDER_URL_LIVE;
        }
    }

    function _setOrderContentType() {
        Klarna_Checkout_Order::$contentType = self::ORDER_CONTENT_TYPE;
    }

}

?>