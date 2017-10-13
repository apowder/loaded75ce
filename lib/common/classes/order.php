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

use yii\web\Session;
use common\classes\platform;

class order {

    var $info, $totals, $products, $customer, $delivery, $content_type, $subscription;
    private $data;
    public $status;

    function __construct($order_id = '') {
        $this->info = array();
        $this->totals = array();
        $this->products = array();
        $this->customer = array();
        $this->delivery = array();
        $this->billing = array();
        $this->tax_address = array();
        $this->order_id = (int) $order_id;

        if (tep_not_null($order_id)) {
            $this->query($order_id);
        } else {
            $this->cart();
        }
    }

    function prepareDetails($order_id) {
        $order_id = tep_db_prepare_input($order_id);
        $order_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . (int) $order_id . "'");
        $this->data = tep_db_fetch_array($order_query);
        return $this;
    }

    function getDetails() {
        return $this->data;
    }

    public function getReferenceId(){
        if (isset($this->data['reference_id'])){
            return $this->data['reference_id'];
        } else {
            return false;
        }
    }

    /**
     * returns orders_id now Should be used on printable documents etc to replace DB ID with custom number quickly
     */
    public function getOrderId() {
      return $this->info['orders_id'];
    }

    function overloadAddressDetails($type = 'delivery') {

        $country = \common\helpers\Country::get_country_info_by_name($this->data[$type . '_country'], $this->data['language_id']);
        $this->$type = array('name' => $this->data[$type . '_name'],
            'gender' => $this->data[$type . '_gender'],
            'firstname' => $this->data[$type . '_firstname'],
            'lastname' => $this->data[$type . '_lastname'],
            'company' => $this->data[$type . '_company'],
            'street_address' => $this->data[$type . '_street_address'],
            'suburb' => $this->data[$type . '_suburb'],
            'city' => $this->data[$type . '_city'],
            'postcode' => $this->data[$type . '_postcode'],
            'state' => $this->data[$type . '_state'],
            'country' => $country,
            'address_book_id' => $this->data[$type . '_address_book_id'],
            'format_id' => $this->data[$type . '_address_format_id'],
            'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $this->data[$type . '_state']),
            'country_id' => $country['id'],
        );
    }

    function query($order_id) {
        global $languages_id, $cart;

        $order = $this->prepareDetails($order_id)->getDetails();


        $totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "' order by sort_order");
        while ($totals = tep_db_fetch_array($totals_query)) {
            $this->totals[] = array('title' => $totals['title'],
                'value' => $totals['value'],
                'class' => $totals['class'],
                'code' => $totals['class'],
                'text' => $totals['text'],
                'text_exc_tax' => $totals['text_exc_tax'],
                'text_inc_tax' => $totals['text_inc_tax'],
// {{
                'tax_class_id' => $totals['tax_class_id'],
                'value_exc_vat' => $totals['value_exc_vat'],
                'value_inc_tax' => $totals['value_inc_tax'],
// }}
            );
            /*if ($totals['class'] == 'ot_subtotal') {
                $this->info['subtotal_inc_tax'] = $totals['value_inc_tax'];
                $this->info['subtotal_exc_tax'] = $totals['value_exc_vat'];
            } else if ($totals['class'] == 'ot_shipping') {
                $this->info['shipping_cost_inc_tax'] = $totals['value_inc_tax'];
                $this->info['shipping_cost_exc_tax'] = $totals['value_exc_vat'];
            }*/
            if ($totals['class'] == 'ot_total') {
                $total_inc_tax = $totals['value_inc_tax'];
                $total_exc_tax = $totals['value_exc_vat'];
            }
            if($totals['class'] == 'ot_paid'){
                $total_paid_inc_tax = $totals['value_inc_tax'];
                $total_paid_exc_tax = $totals['value_exc_vat'];                
            }
        }

        $order_total_query = tep_db_query("select value_inc_tax from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "' and class = 'ot_total'");
        $order_total = tep_db_fetch_array($order_total_query);

        $shipping_method_query = tep_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "' and class = 'ot_shipping'");
        $shipping_method = tep_db_fetch_array($shipping_method_query);

        $order_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . $order['orders_status'] . "' and language_id = '" . (int) $languages_id . "'");
        $order_status = tep_db_fetch_array($order_status_query);

        $this->info = array('currency' => $order['currency'],
            'currency_value' => $order['currency_value'],
            'platform_id' => $order['platform_id'],
            'language_id' => $order['language_id'],
            'admin_id' => $order['admin_id'],
            'orders_id' => $order['orders_id'],
            'payment_method' => $order['payment_method'],
            'cc_type' => $order['cc_type'],
            'cc_owner' => $order['cc_owner'],
            'cc_number' => $order['cc_number'],
            'cc_expires' => $order['cc_expires'],
            'date_purchased' => $order['date_purchased'],
            'tracking_number' => $order['tracking_number'],
            'orders_status_name' => $order_status['orders_status_name'],
            'order_status' => $order['orders_status'],
            'last_modified' => $order['last_modified'],
            'total' => $order_total['value_inc_tax'],
            'payment_class' => $order['payment_class'],
            'shipping_class' => $order['shipping_class'],
            'shipping_method' => ((substr($shipping_method['title'], -1) == ':') ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title'])),
            'shipping_cost' => 0, //new added
            'subtotal' => 0, //new added
            'subtotal_inc_tax' => 0, //new added
            'subtotal_exc_tax' => 0, //new added
            'tax' => 0, //new added
            'tax_groups' => array(), //new added
            'comments' => (isset($_POST['comments']) ? $_POST['comments'] : $_SESSION['comments']), //new added
            'basket_id' => (int) $order['basket_id'], //new added
            'shipping_weight' => $order['shipping_weight'],
            'total_paid_inc_tax' => $total_paid_inc_tax,
            'total_paid_exc_tax' => $total_paid_exc_tax,
            'delivery_date' => $order['delivery_date'],
        );

        $this->subscription = [
            'subtotal' => 0,
            'subtotal_inc_tax' => 0,
            'subtotal_exc_tax' => 0,
            'shipping_cost' => 0,
            'shipping_cost_inc_tax' => 0,
            'shipping_cost_exc_tax' => 0,
            'tax' => 0,
            'tax_groups' => array(),
            'total' => 0,
            'total_inc_tax' => 0,
            'total_exc_tax' => 0,
        ];
                
        if (!tep_not_null($this->info['comments'])) {
            $check = tep_db_fetch_array(tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $order_id . "' order by orders_status_history_id asc limit 1"));
            $this->info['comments'] = $check['comments'];
        }

        $country = \common\helpers\Country::get_country_info_by_name($order['customers_country'], $order['language_id']);
        $this->customer = array('id' => $order['customers_id'],
            'customer_id' => $order['customers_id'],
            'name' => $order['customers_name'],
            'firstname' => $order['customers_firstname'],
            'lastname' => $order['customers_lastname'],
            'company' => $order['customers_company'],
            'company_vat' => $order['customers_company_vat'],
            'company_vat_status' => $order['customers_company_vat_status'],
            'street_address' => $order['customers_street_address'],
            'suburb' => $order['customers_suburb'],
            'city' => $order['customers_city'],
            'postcode' => $order['customers_postcode'],
            'state' => $order['customers_state'],
            'country' => $country,
            'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $order['customers_state']),
            'country_id' => $country['id'],
            'format_id' => $order['customers_address_format_id'],
            'telephone' => $order['customers_telephone'],
            'landline' => $order['customers_landline'],
            'email_address' => $order['customers_email_address']);

        $this->overloadAddressDetails('delivery');

        if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
            $this->delivery = false;
        }

        $this->overloadAddressDetails('billing');

        $index = 0;
        $tax_groups = [];
        $orders_products_query = tep_db_query("select orders_products_id, " .
                "is_virtual, " .
                "gv_state, " .
                "gift_wrap_price, gift_wrapped, " .
                "if(length(uprid),uprid, products_id) as products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price, is_giveaway from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $order_id . "'");
        while ($orders_products = tep_db_fetch_array($orders_products_query)) {
            $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                'id' => \common\helpers\Inventory::normalize_id($orders_products['products_id']),
                'name' => $orders_products['products_name'],
                'model' => $orders_products['products_model'],
                'tax' => $orders_products['products_tax'],
                'ga' => $orders_products['is_giveaway'],
                'is_virtual' => (int) $orders_products['is_virtual'],
                'gv_state' => $orders_products['gv_state'],
                'gift_wrap_price' => $orders_products['gift_wrap_price'],
                'gift_wrapped' => !!$orders_products['gift_wrapped'],
                'price' => $orders_products['products_price'],
                'final_price' => $orders_products['final_price'],
                'orders_products_id' => $orders_products['orders_products_id']
            );
            if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'queryOrderFrontend')) {
                $this->products[$index] = array_merge($ext::queryOrderFrontend($order_id, $index), $this->products[$index]);
            }

            if (is_object($cart) && $cart->existOwerwritten($this->products[$index]['id'])) {
                $this->overWrite($this->products[$index]['id'], $this->products[$index]);
            }
            $subtotal += $orders_products['final_price'] * $orders_products['products_quantity'];
            $tax += $orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100;
            $selected_tax = "";
            $query_tax_class_id = "select tax_class_id, sum(tax_rate) as rate from " . TABLE_TAX_RATES . " group by tax_class_id, tax_zone_id order by tax_priority";
            $result_tax_class_id = tep_db_query($query_tax_class_id);
            if (tep_db_num_rows($result_tax_class_id) > 0) {
                while ($array_tax_class_id = tep_db_fetch_array($result_tax_class_id)) {
                    if ($array_tax_class_id['rate'] == $this->products[$index]['tax']) {
                        $tax_class_id = $array_tax_class_id['tax_class_id'];
                        $selected_tax = \common\helpers\Tax::get_tax_description($tax_class_id, $this->delivery['country_id'], $this->delivery['zone_id']);
                        break;
                    }
                }
            }
            if (!isset($tax_groups[$selected_tax])) {
                $tax_groups[$selected_tax] = $this->products[$index]['final_price'] * $this->products[$index]['qty'] * $this->products[$index]['tax'] / 100;
            } else {
                $tax_groups[$selected_tax] += $this->products[$index]['final_price'] * $this->products[$index]['qty'] * $this->products[$index]['tax'] / 100;
            }

            $subindex = 0;
            $attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix, products_options_id, products_options_values_id from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $order_id . "' and orders_products_id = '" . (int) $orders_products['orders_products_id'] . "'");
            if (tep_db_num_rows($attributes_query)) {
                while ($attributes = tep_db_fetch_array($attributes_query)) {
                    $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options'],
                        'value' => $attributes['products_options_values'],
                        'prefix' => $attributes['price_prefix'],
                        'price' => $attributes['options_values_price'],
                        'option_id' => $attributes['products_options_id'],
                        'value_id' => $attributes['products_options_values_id']);

                    $subindex++;
                }
            }

            $index++;
        }
        // {{ content type
        $this->content_type = 'physical';
        $count_virtual = 0;
        $count_physical = 0;
        foreach ($this->products as $__product) {
            if ($__product['is_virtual'] != 0) {
                $count_virtual++;
            } else {
                $count_physical++;
            }
        }
        if ($count_physical > 0 && $count_virtual == 0) {
            $this->content_type = 'physical';
        } elseif ($count_physical > 0 && $count_virtual > 0) {
            $this->content_type = 'mixed';
        } elseif ($count_physical == 0 && $count_virtual > 0) {
            $this->content_type = 'virtual';
        } else {
            $this->content_type = 'physical';
        }
        // }} content type
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            $this->info['subtotal'] = round($subtotal + $tax, 2);
        } else {
            $this->info['subtotal'] = round($subtotal, 2);
        }
        $this->info['subtotal_inc_tax'] = round($subtotal + $tax, 2);
        $this->info['subtotal_exc_tax'] = round($subtotal, 2);

        $this->info['total'] = round($subtotal + $tax, 2);
        $this->info['total_inc_tax'] = round($total_inc_tax, 2);
        $this->info['total_exc_tax'] = round($total_exc_tax, 2);
        $this->info['tax'] = round($tax, 2);
        $this->info['tax_groups'] = $tax_groups;
    }

// recalc stubs
    function _billing_address() {
        return false;
    }

    function _shipping_address() {
        return false;
    }

    function change_shipping($new_shipping) {
        if (get_class($this) == 'common\classes\order' && \frontend\design\Info::isTotallyAdmin()) {
            if (!is_array($new_shipping)) {
                $this->info['shipping_class'] = '';
                $this->info['shipping_method'] = '';
                $this->info['shipping_cost'] = '0';
                $this->info['shipping_cost_inc_tax'] = '0';
                $this->info['shipping_cost_exc_tax'] = '0';
            } else {
                $this->info['shipping_class'] = $new_shipping['id'];
                $this->info['shipping_method'] = $new_shipping['title'];
                $this->info['shipping_cost'] = $new_shipping['cost'];
                $this->info['shipping_cost_inc_tax'] = $new_shipping['cost_inc_tax'];
                $this->info['shipping_cost_exc_tax'] = $new_shipping['cost'];
                $this->info['total'] = $this->info['subtotal'] + $new_shipping['cost'];
                $this->info['total_inc_tax'] = $this->info['subtotal_inc_tax'] + $new_shipping['cost'];
                $this->info['total_exc_tax'] = $this->info['subtotal_exc_tax'] + $new_shipping['cost'];
            }
        }
        return false;
    }

//\ recalc stubs    
    function cart() {
        global $customer_id, $sendto, $billto, $cart, $languages_id, $currency, $currencies, $shipping, $payment;
        global $_POST;

        $session = new Session;

        $sendto = $GLOBALS['sendto'];
        $billto = $GLOBALS['billto'];

        $this->content_type = $cart->get_content_type();

        $customer_address_query = tep_db_query("select c.customers_id, ab.address_book_id AS address_book_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_telephone, c.customers_landline, c.customers_email_address, c.customers_company, c.customers_company_vat, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " ab on ab.customers_id = '" . (int) $session['customer_id'] . "' and c.customers_default_address_id = ab.address_book_id left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " co on (ab.entry_country_id = co.countries_id and co.language_id = '" . (int) $languages_id . "') where c.customers_id = '" . (int) $session['customer_id'] . "'");
        $customer_address = tep_db_fetch_array($customer_address_query);
        if (is_array($sendto) && !empty($sendto)) {
            $shipping_address = array('entry_firstname' => $sendto['firstname'],
                'entry_lastname' => $sendto['lastname'],
                'entry_street_address' => $sendto['street_address'],
                'entry_suburb' => $sendto['suburb'],
                'entry_postcode' => $sendto['postcode'],
                'entry_city' => $sendto['city'],
                'entry_zone_id' => $sendto['zone_id'],
                'zone_name' => $sendto['zone_name'],
                'entry_country_id' => $sendto['country_id'],
                'countries_id' => $sendto['country_id'],
                'countries_name' => $sendto['country_name'],
                'countries_iso_code_2' => $sendto['country_iso_code_2'],
                'countries_iso_code_3' => $sendto['country_iso_code_3'],
                'address_format_id' => $sendto['address_format_id'],
                'entry_state' => $sendto['zone_name']);
        } else {
            $shipping_address_query = tep_db_query("select ab.address_book_id AS address_book_id, ab.entry_gender, ab.entry_firstname, ab.entry_lastname, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . (int) $session['customer_id'] . "' and c.language_id = '" . (int) $languages_id . "' and ab.address_book_id = '" . (int) $sendto . "'");
            $shipping_address = tep_db_fetch_array($shipping_address_query);
        }

        if (is_array($billto) && !empty($billto)) {
            $billing_address = array('entry_firstname' => $billto['firstname'],
                'entry_lastname' => $billto['lastname'],
                'entry_street_address' => $billto['street_address'],
                'entry_suburb' => $billto['suburb'],
                'entry_postcode' => $billto['postcode'],
                'entry_city' => $billto['city'],
                'entry_zone_id' => $billto['zone_id'],
                'zone_name' => $billto['zone_name'],
                'entry_country_id' => $billto['country_id'],
                'countries_id' => $billto['country_id'],
                'countries_name' => $billto['country_name'],
                'countries_iso_code_2' => $billto['country_iso_code_2'],
                'countries_iso_code_3' => $billto['country_iso_code_3'],
                'address_format_id' => $billto['address_format_id'],
                'entry_state' => $billto['zone_name']);
        } else {
            $billing_address_query = tep_db_query("select ab.address_book_id AS address_book_id, ab.entry_gender, ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_company_vat, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . (int) $session['customer_id'] . "' and c.language_id = '" . (int) $languages_id . "' and ab.address_book_id = '" . (int) $billto . "'");
            $billing_address = tep_db_fetch_array($billing_address_query);
        }

        $tax_address_query = tep_db_query("select ab.entry_country_id, ab.entry_zone_id from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) where ab.customers_id = '" . (int) $session['customer_id'] . "' and ab.address_book_id = '" . (int) (isset($sendto) && $sendto != '' ? $sendto : $billto) . "'");
        $this->tax_address = tep_db_fetch_array($tax_address_query);

        $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
            'platform_id' => $cart->platform_id,
            'currency' => $cart->currency,
            'currency_value' => $currencies->currencies[$cart->currency]['value'],
            'language_id' => $cart->language_id,
            'admin_id' => @$cart->admin_id,
            'payment_method' => $payment,
            'payment_class' => $payment,
            'shipping_class' => @$shipping['id'],
            'shipping_weight' => $cart->show_weight(),
            'cc_type' => (isset($_POST['cc_type']) ? $_POST['cc_type'] : ''),
            'cc_owner' => (isset($_POST['cc_owner']) ? $_POST['cc_owner'] : ''),
            'cc_number' => (isset($_POST['cc_number']) ? $_POST['cc_number'] : ''),
            'cc_expires' => (isset($_POST['cc_expires']) ? $_POST['cc_expires'] : ''),
            'shipping_method' => @$shipping['title'],
            'shipping_cost' => @$shipping['cost'],
            'shipping_cost_inc_tax' => @$shipping['cost'],
            'shipping_cost_exc_tax' => @$shipping['cost'],
            'subtotal' => 0,
            'subtotal_inc_tax' => 0,
            'subtotal_exc_tax' => 0,
            'total_paid_exc_tax' => 0,
            'total_paid_inc_tax' => 0,
            'tax' => 0,
            'tax_groups' => array(),
            'comments' => (isset($_POST['comments']) ? $_POST['comments'] : $_SESSION['comments']),
            'basket_id' => (int) $cart->basketID);

        $this->subscription = [
            'subtotal' => 0,
            'subtotal_inc_tax' => 0,
            'subtotal_exc_tax' => 0,
            'shipping_cost' => 0,
            'shipping_cost_inc_tax' => 0,
            'shipping_cost_exc_tax' => 0,
            'tax' => 0,
            'tax_groups' => array(),
            'total' => 0,
            'total_inc_tax' => 0,
            'total_exc_tax' => 0,
        ];
        
        if (isset($GLOBALS[$payment]) && is_object($GLOBALS[$payment])) {
            $this->info['payment_method'] = $GLOBALS[$payment]->title;
            $this->info['payment_class'] = $GLOBALS[$payment]->code;

            if (isset($GLOBALS[$payment]->order_status) && is_numeric($GLOBALS[$payment]->order_status) && ($GLOBALS[$payment]->order_status > 0)) {
                $this->info['order_status'] = $GLOBALS[$payment]->order_status;
            }
        } elseif (isset($GLOBALS[substr($payment, 0, strpos($payment, '_'))]) && is_object($GLOBALS[substr($payment, 0, strpos($payment, '_'))])) {
            $this->info['payment_method'] = $GLOBALS[substr($payment, 0, strpos($payment, '_'))]->getTitle($payment);
            //$this->info['payment_class'] = $GLOBALS[substr($payment, 0, strpos($payment, '_'))]->code;
            if (isset($GLOBALS[substr($payment, 0, strpos($payment, '_'))]->order_status) && is_numeric($GLOBALS[substr($payment, 0, strpos($payment, '_'))]->order_status) && ($GLOBALS[substr($payment, 0, strpos($payment, '_'))]->order_status > 0)) {
                $this->info['order_status'] = $GLOBALS[substr($payment, 0, strpos($payment, '_'))]->order_status;
            }
        }

        if(($new_status = $cart->getStatusAfterPaid()) !== false){
            $this->info['order_status'] = $new_status;
        }

        $company_vat_status = 0;
        if ($ext = \common\helpers\Acl::checkExtension('VatOnOrder', 'check_vat_status')) {
            $company_vat_status = $ext::check_vat_status($customer_address);
        }

        $this->customer = array('id' => $customer_address['customers_id'],
            'customer_id' => $customer_address['customers_id'],
            'address_book_id' => $customer_address['address_book_id'],
            'gender' => $customer_address['customers_gender'],
            'firstname' => $customer_address['customers_firstname'],
            'lastname' => $customer_address['customers_lastname'],
            'company' => $customer_address['customers_company'],
            'company_vat' => $customer_address['customers_company_vat'],
            'company_vat_status' => $company_vat_status,
            'street_address' => $customer_address['entry_street_address'],
            'suburb' => $customer_address['entry_suburb'],
            'city' => $customer_address['entry_city'],
            'postcode' => $customer_address['entry_postcode'],
            'state' => ((tep_not_null($customer_address['entry_state'])) ? $customer_address['entry_state'] : $customer_address['zone_name']),
            'zone_id' => $customer_address['entry_zone_id'],
            'country' => array('id' => $customer_address['countries_id'], 'title' => $customer_address['countries_name'], 'iso_code_2' => $customer_address['countries_iso_code_2'], 'iso_code_3' => $customer_address['countries_iso_code_3']),
            'format_id' => $customer_address['address_format_id'],
            'telephone' => $customer_address['customers_telephone'],
            'landline' => $customer_address['customers_landline'],
            'email_address' => $customer_address['customers_email_address']);

        $this->delivery = array(
            'address_book_id' => $shipping_address['address_book_id'],
            'gender' => $shipping_address['entry_gender'],
            'firstname' => $shipping_address['entry_firstname'],
            'lastname' => $shipping_address['entry_lastname'],
            'street_address' => $shipping_address['entry_street_address'],
            'suburb' => $shipping_address['entry_suburb'],
            'city' => $shipping_address['entry_city'],
            'postcode' => $shipping_address['entry_postcode'],
            'state' => ((tep_not_null($shipping_address['entry_state'])) ? $shipping_address['entry_state'] : $shipping_address['zone_name']),
            'zone_id' => $shipping_address['entry_zone_id'],
            'country' => array('id' => $shipping_address['countries_id'], 'title' => $shipping_address['countries_name'], 'iso_code_2' => $shipping_address['countries_iso_code_2'], 'iso_code_3' => $shipping_address['countries_iso_code_3']),
            'country_id' => $shipping_address['entry_country_id'],
            'format_id' => $shipping_address['address_format_id']);

        $this->billing = array(
            'address_book_id' => $billing_address['address_book_id'],
            'gender' => $billing_address['entry_gender'],
            'firstname' => $billing_address['entry_firstname'],
            'lastname' => $billing_address['entry_lastname'],
            'street_address' => $billing_address['entry_street_address'],
            'suburb' => $billing_address['entry_suburb'],
            'city' => $billing_address['entry_city'],
            'postcode' => $billing_address['entry_postcode'],
            'state' => ((tep_not_null($billing_address['entry_state'])) ? $billing_address['entry_state'] : $billing_address['zone_name']),
            'zone_id' => $billing_address['entry_zone_id'],
            'country' => array('id' => $billing_address['countries_id'], 'title' => $billing_address['countries_name'], 'iso_code_2' => $billing_address['countries_iso_code_2'], 'iso_code_3' => $billing_address['countries_iso_code_3']),
            'country_id' => $billing_address['entry_country_id'],
            'format_id' => $billing_address['address_format_id']);

        $this->_billing_address();
        $this->_shipping_address();

        //set default country id for not logged in ( UK=222 )
        if (!$this->tax_address['entry_country_id']) {
            $this->tax_address['entry_country_id'] = STORE_COUNTRY;
        }

        $index = 0;
        $products = $cart->get_products();
        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
            $this->products[$index] = array('qty' => $products[$i]['quantity'],
                'reserved_qty' => $products[$i]['reserved_qty'],
                'name' => $products[$i]['name'],
                'model' => $products[$i]['model'],
                'stock_info' => $products[$i]['stock_info'],
                'products_file' => $products[$i]['products_file'],
                'is_virtual' => isset($products[$i]['is_virtual']) ? intval($products[$i]['is_virtual']) : 0,
                'gv_state' => (preg_match('/^GIFT/', $products[$i]['model']) ? 'pending' : 'none'),
                'tax' => \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id'], $this->tax_address['entry_country_id'], $this->tax_address['entry_zone_id']),
                'tax_class_id' => $products[$i]['tax_class_id'],
                'tax_description' => \common\helpers\Tax::get_tax_description($products[$i]['tax_class_id'], $this->tax_address['entry_country_id'], $this->tax_address['entry_zone_id']),
                'ga' => $products[$i]['ga'],
                'price' => $products[$i]['price'],
                'final_price' => $products[$i]['final_price'], //$products[$i]['price'] + $cart->attributes_price($products[$i]['id'], $products[$i]['quantity']),
                'weight' => $products[$i]['weight'],
                'gift_wrap_price' => $products[$i]['gift_wrap_price'],
                'gift_wrapped' => $products[$i]['gift_wrapped'],
                'gift_wrap_allowed' => $products[$i]['gift_wrap_allowed'],
                'virtual_gift_card' => $products[$i]['virtual_gift_card'],
                'id' => \common\helpers\Inventory::normalize_id($products[$i]['id']),
                'subscription' => $products[$i]['subscription'],
                'subscription_code' => $products[$i]['subscription_code'],
                'overwritten' => $products[$i]['overwritten']);
            if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'cartOrderFrontend')) {
                $this->products[$index] = array_merge($ext::cartOrderFrontend($index), $this->products[$index]);
            }
            if (!$products[$i]['ga'] && $cart->existOwerwritten($this->products[$index]['id'])) {
                $this->overWrite($this->products[$index]['id'], $this->products[$index]);
            }
            $subindex = 0;
// {{ Products Bundle Sets
            $bundle_prods_options = array();
            $bundle_prods_options_array = array();
            if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'cartOrder')) {
                list($bundle_prods_options_array, $bundle_prods_options) = $ext::cartOrder($products[$i], $session['customer_groups_id']);
            }
// }}
            if ($products[$i]['attributes']) {
                reset($products[$i]['attributes']);
// {{ Virtual Gift Card
                if ($products[$i]['virtual_gift_card'] && $products[$i]['attributes'][0] > 0) {
                    global $languages_id;
                    $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and vgcb.virtual_gift_card_basket_id = '" . (int) $products[$i]['attributes'][0] . "' and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and " . ($session['customer_id'] > 0 ? " vgcb.customers_id = '" . (int) $session['customer_id'] . "'" : " vgcb.session_id = '" . tep_session_id() . "'")));
                    $products_options_values_name = "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_name']))
                        $products_options_values_name .= TEXT_GIFT_CARD_RECIPIENTS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_recipients_name'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_email']))
                        $products_options_values_name .= TEXT_GIFT_CARD_RECIPIENTS_EMAIL . ' ' . $virtual_gift_card['virtual_gift_card_recipients_email'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_message']))
                        $products_options_values_name .= TEXT_GIFT_CARD_MESSAGE . ' ' . $virtual_gift_card['virtual_gift_card_message'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_senders_name']))
                        $products_options_values_name .= TEXT_GIFT_CARD_SENDERS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_senders_name'] . "\n";
                    $this->products[$index]['attributes'][$subindex] = array('option' => TEXT_GIFT_CARD_DETAILS,
                        'value' => $products_options_values_name,
                        'option_id' => 0,
                        'value_id' => $products[$i]['attributes'][0]);
                } else
// }}
                    while (list($option, $value) = each($products[$i]['attributes'])) {
// {{ Products Bundle Sets
                        if (in_array((string) $option, $bundle_prods_options))
                            continue;
// }}
                        $attributes_query = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $products[$i]['id'] . "' and pa.options_id = '" . (int) $option . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int) $value . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int) $languages_id . "' and poval.language_id = '" . (int) $languages_id . "'");
                        $attributes = tep_db_fetch_array($attributes_query);
                        $attributes['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes['products_attributes_id'], $products[$i]['quantity']);

                        $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options_name'],
                            'value' => $attributes['products_options_values_name'],
                            'option_id' => $option,
                            'value_id' => $value,
                            'prefix' => $attributes['price_prefix'],
                            'price' => $attributes['options_values_price']);

                        $subindex++;
                    }
            }
// {{ Products Bundle Sets
            foreach ($bundle_prods_options_array as $bundle_prods_option) {
                $this->products[$index]['attributes'][$subindex] = $bundle_prods_option;
                $subindex++;
            }
// }}
            if ($products[$i]['gift_wrapped']) {
                if (!is_array($this->products[$index]))
                    $this->products[$index] = array();
                $this->products[$index]['attributes'][] = array(
                    'option' => GIFT_WRAP_OPTION,
                    'value' => GIFT_WRAP_VALUE_YES,
                    'option_id' => -2,
                    'value_id' => -2);
            }

            $shown_price = \common\helpers\Tax::add_tax($this->products[$index]['final_price'] * $this->products[$index]['qty'], $this->products[$index]['tax']);
            $this->info['subtotal'] += $shown_price;

            $this->info['subtotal_exc_tax'] += $this->products[$index]['final_price'] * $this->products[$index]['qty'];
            $this->info['subtotal_inc_tax'] += \common\helpers\Tax::add_tax_always($this->products[$index]['final_price'] * $this->products[$index]['qty'], $this->products[$index]['tax']);

            $products_tax = $this->products[$index]['tax'];
            $products_tax_description = $this->products[$index]['tax_description'];
            if (DISPLAY_PRICE_WITH_TAX == 'true') {
                $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                if (isset($this->info['tax_groups']["$products_tax_description"])) {
                    $this->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                } else {
                    $this->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                }
            } else {
                $this->info['tax'] += ($products_tax / 100) * $shown_price;
                if (isset($this->info['tax_groups']["$products_tax_description"])) {
                    $this->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
                } else {
                    $this->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
                }
            }

            $index++;
        }

        $this->info['total_inc_tax'] = $this->info['subtotal_inc_tax'] + $this->info['shipping_cost_exc_tax'];
        $this->info['total_exc_tax'] = $this->info['subtotal_exc_tax'] + $this->info['shipping_cost_exc_tax'];
        
        if (($values = $cart->getTotalKey('ot_paid')) !== false){
            if(is_array($values)){
                $this->info['total_paid_exc_tax'] = $values['ex'];
                $this->info['total_paid_inc_tax'] = $values['in'];
            }
        }

        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
            //$this->info['total_inc_tax'] = $this->info['subtotal'] + $this->info['shipping_cost'];
            //$this->info['total_exc_tax'] = $this->info['subtotal'] + $this->info['shipping_cost'] - $this->info['tax'];
        } else {
            $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
            //$this->info['total_inc_tax'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
            //$this->info['total_exc_tax'] = $this->info['subtotal'] + $this->info['shipping_cost'];
        }
    }

    public function overWrite($uprid, &$product) {
        global $cart;
        $details = $cart->getOwerwritten($uprid);
        if (is_array($details) && count($details)) {
            foreach ($details as $key => $value) {
                $product[$key] = $value;
            }
        }
    }

    public function save_subscription($subscription_id = 0, $order_id = 0, $i = 0, $uuid = '', $info = '') {
        global $languages_id, $currencies;

        $current_status = 100002;

        $sql_data_array = [
            'platform_id' => $this->info['platform_id'],
            'orders_id' => $order_id,
            'customers_id' => $this->customer['customer_id'],
            'customers_name' => $this->customer['firstname'] . ' ' . $this->customer['lastname'],
            'customers_firstname' => $this->customer['firstname'],
            'customers_lastname' => $this->customer['lastname'],
            'customers_company' => $this->customer['company'],
            'customers_company_vat' => $this->customer['company_vat'],
            'customers_company_vat_status' => $this->customer['company_vat_status'],
            'customers_street_address' => $this->customer['street_address'],
            'customers_suburb' => $this->customer['suburb'],
            'customers_city' => $this->customer['city'],
            'customers_postcode' => $this->customer['postcode'],
            'customers_state' => $this->customer['state'],
            'customers_country' => $this->customer['country']['title'],
            'customers_telephone' => $this->customer['telephone'],
            'customers_landline' => $this->customer['landline'],
            'customers_email_address' => $this->customer['email_address'],
            'customers_address_format_id' => $this->customer['format_id'],
            'delivery_gender' => $this->delivery['gender'],
            'delivery_name' => $this->delivery['firstname'] . ' ' . $this->delivery['lastname'],
            'delivery_firstname' => $this->delivery['firstname'],
            'delivery_lastname' => $this->delivery['lastname'],
            'delivery_street_address' => $this->delivery['street_address'],
            'delivery_suburb' => $this->delivery['suburb'],
            'delivery_city' => $this->delivery['city'],
            'delivery_postcode' => $this->delivery['postcode'],
            'delivery_state' => $this->delivery['state'],
            'delivery_country' => $this->delivery['country']['title'],
            'delivery_address_format_id' => $this->delivery['format_id'],
            'delivery_address_book_id' => isset($this->delivery['address_book_id']) ? $this->delivery['address_book_id'] : 0,
            'billing_gender' => $this->billing['gender'],
            'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
            'billing_firstname' => $this->billing['firstname'],
            'billing_lastname' => $this->billing['lastname'],
            'billing_street_address' => $this->billing['street_address'],
            'billing_suburb' => $this->billing['suburb'],
            'billing_city' => $this->billing['city'],
            'billing_postcode' => $this->billing['postcode'],
            'billing_state' => $this->billing['state'],
            'billing_country' => $this->billing['country']['title'],
            'billing_address_format_id' => $this->billing['format_id'],
            'billing_address_book_id' => isset($this->billing['address_book_id']) ? $this->billing['address_book_id'] : 0,
            'payment_class' => $this->info['payment_class'],
            'payment_method' => $this->info['payment_method'],
            'shipping_class' => $this->info['shipping_class'],
            'shipping_method' => strip_tags($this->info['shipping_method']),
            'currency' => $this->info['currency'],
            'currency_value' => $this->info['currency_value'],
            'last_modified' => 'now()',
            'date_purchased' => 'now()',
            'subscription_status' => $current_status,
            'transaction_id' => $uuid,
            'language_id' => $this->info['language_id'],
            'info' => $info,
        ];

        if (USE_MARKET_PRICES == 'True') {
            $sql_data_array['currency_value_default'] = $currencies->currencies[DEFAULT_CURRENCY]['value'];
        }
        if ($subscription_id > 0) {
            tep_db_perform(TABLE_SUBSCRIPTION, $sql_data_array, 'update', 'subscription_id=' . (int) $subscription_id);
        } else {
            tep_db_perform(TABLE_SUBSCRIPTION, $sql_data_array);
            $subscription_id = tep_db_insert_id();
        }

        $sql_data_array = [
            'subscription_id' => (int) $subscription_id,
            'subscription_status_id' => $current_status,
            'date_added' => 'now()',
            'customer_notified' => 0,
            'comments' => '',
            'admin_id' => 0,
        ];
        tep_db_perform(TABLE_SUBSCRIPTION_STATUS_HISTORY, $sql_data_array);

        $sql_data_array = [
            'subscription_id' => $subscription_id,
            'products_id' => \common\helpers\Inventory::get_prid($this->products[$i]['id']),
            'products_model' => $this->products[$i]['model'],
            'products_name' => $this->products[$i]['name'],
            'products_price' => $this->products[$i]['price'],
            'final_price' => $this->products[$i]['final_price'],
            'products_tax' => $this->products[$i]['tax'],
            'products_quantity' => $this->products[$i]['qty'],
            'is_giveaway' => $this->products[$i]['ga'],
            'is_virtual' => $this->products[$i]['is_virtual'],
            'gift_wrap_price' => $this->products[$i]['gift_wrap_price'],
            'gift_wrapped' => $this->products[$i]['gift_wrapped'] ? 1 : 0,
            'gv_state' => $this->products[$i]['gv_state'],
            'uprid' => \common\helpers\Inventory::normalize_id($this->products[$i]['id']),
        ];
        tep_db_perform(TABLE_SUBSCRIPTION_PRODUCTS, $sql_data_array);

        //tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        
        return $subscription_id;
    }

    public function save_order($order_id = 0) {
        global $languages_id, $currencies, $shipping_weight, $cart;
        
        if(($new_status = $cart->getStatusAfterPaid()) !== false){
            $this->info['order_status'] = $new_status;
        }        

// BOF: WebMakers.com Added: Downloads Controller
        $sql_data_array = array(
            'customers_id' => $this->customer['customer_id'],
            'basket_id' => $this->info['basket_id'],
            'customers_name' => $this->customer['firstname'] . ' ' . $this->customer['lastname'],
            //{{ BEGIN FISTNAME
            'customers_firstname' => $this->customer['firstname'],
            'customers_lastname' => $this->customer['lastname'],
            //}} END FIRSTNAME
            'customers_company' => $this->customer['company'],
            'customers_company_vat' => $this->customer['company_vat'],
            'customers_company_vat_status' => $this->customer['company_vat_status'],
            'customers_street_address' => $this->customer['street_address'],
            'customers_suburb' => $this->customer['suburb'],
            'customers_city' => $this->customer['city'],
            'customers_postcode' => $this->customer['postcode'],
            'customers_state' => $this->customer['state'],
            'customers_country' => $this->customer['country']['title'],
            'customers_telephone' => $this->customer['telephone'],
            'customers_landline' => $this->customer['landline'],
            'customers_email_address' => $this->customer['email_address'],
            'customers_address_format_id' => $this->customer['format_id'],
            'delivery_address_book_id' => isset($this->delivery['address_book_id']) ? $this->delivery['address_book_id'] : 0,
            'delivery_gender' => $this->delivery['gender'],
            'delivery_name' => $this->delivery['firstname'] . ' ' . $this->delivery['lastname'],
            //{{ BEGIN FISTNAME
            'delivery_firstname' => $this->delivery['firstname'],
            'delivery_lastname' => $this->delivery['lastname'],
            //}} END FIRSTNAME
            'delivery_street_address' => $this->delivery['street_address'],
            'delivery_suburb' => $this->delivery['suburb'],
            'delivery_city' => $this->delivery['city'],
            'delivery_postcode' => $this->delivery['postcode'],
            'delivery_state' => $this->delivery['state'],
            'delivery_country' => $this->delivery['country']['title'],
            'delivery_address_format_id' => $this->delivery['format_id'],
            'billing_address_book_id' => isset($this->billing['address_book_id']) ? $this->billing['address_book_id'] : 0,
            'billing_gender' => $this->billing['gender'],
            'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
            //{{ BEGIN FISTNAME
            'billing_firstname' => $this->billing['firstname'],
            'billing_lastname' => $this->billing['lastname'],
            //}} END FIRSTNAME
            'billing_street_address' => $this->billing['street_address'],
            'billing_suburb' => $this->billing['suburb'],
            'billing_city' => $this->billing['city'],
            'billing_postcode' => $this->billing['postcode'],
            'billing_state' => $this->billing['state'],
            'billing_country' => $this->billing['country']['title'],
            'billing_address_format_id' => $this->billing['format_id'],
            'platform_id' => $this->info['platform_id'],
            'payment_method' => $this->info['payment_method'],
// BOF: Lango Added for print order mod
            'payment_info' => $GLOBALS['payment_info'],
// EOF: Lango Added for print order mod
            'cc_type' => $this->info['cc_type'],
            'cc_owner' => $this->info['cc_owner'],
            'cc_number' => $this->info['cc_number'],
            'cc_expires' => $this->info['cc_expires'],
            'language_id' => $this->info['language_id'], //(int)$languages_id,
            'payment_class' => $this->info['payment_class'],
            'shipping_class' => $this->info['shipping_class'],
            'shipping_method' => strip_tags($this->info['shipping_method']),
            'date_purchased' => 'now()',
            'last_modified' => 'now()',
            /* start search engines statistics */
            'search_engines_id' => isset($_SESSION['search_engines_id']) ? (int) $_SESSION['search_engines_id'] : 0,
            'search_words_id' => isset($_SESSION['search_words_id']) ? (int) $_SESSION['search_words_id'] : 0,
            /* end search engines statistics */
            'orders_status' => $this->info['order_status'],
            'currency' => $this->info['currency'],
            'currency_value' => $this->info['currency_value'],
            'shipping_weight' => $this->info['shipping_weight'],
            'adjusted' => 0,
            'reference_id' => $cart->getReference(),
        );
        
        if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'toOrder')){
            $ext::toOrder($sql_data_array);
        }

        if (tep_session_is_registered('platform_code')) {
            global $platform_code;
            if (!empty($platform_code)) {
                $sql_data_array['platform_id'] = \Yii::$app->get('platform')->config()->getGoogleShopPlatformId($platform_code);
            }
        }

// EOF: WebMakers.com Added: Downloads Controller
        if (USE_MARKET_PRICES == 'True') {
            $sql_data_array['currency_value_default'] = $currencies->currencies[DEFAULT_CURRENCY]['value'];
        }
//  tep_session_unregister('shipping');
//  tep_session_unregister('payment');

        if ($order_id) {
            $this->status = 'update';
            tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', 'orders_id=' . (int) $order_id);
        } else {
            $this->status = 'new';
            tep_db_perform(TABLE_ORDERS, $sql_data_array);
            $order_id = tep_db_insert_id();
        }

        $this->order_id = (int) $order_id;

        return $order_id;
    }

    public function get_private_info() {
        global $cart;
        $info = '';
        if (!tep_not_null($this->status))
            return $info;
        $skip = false;
        switch ($this->status) {
            case 'new':
                $info = 'Created';
                break;
            case 'update':
                if ($cart->admin_id) {
                    $skip = true;

                    $info = 'Edited by ';
                    try {
                        $admin = new \backend\models\Admin();
                        if (is_object($admin)) {
                            $info .= $admin->getInfo('admin_firstname') . ' ' . $admin->getInfo('admin_lastname');
                        }
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                    }
                } else {
                    $info = 'Updated';
                }
                break;
            default:
                break;
        }
        if ($cart->admin_id) {
            if (!$skip)
                $info .= ' from backend for ';
        } else {
            $info .= ' from store ';
        }
        if (!$skip)
            $info .= platform::name($cart->platform_id);
        return $info;
    }

    public function save_details() {
        global $order_totals, $cart;
        if (!$this->order_id)
            return false;
        //if(!\frontend\design\Info::isTotallyAdmin()) tep_db_query("delete from " . TABLE_ORDERS_HISTORY . " where orders_id = '" . (int)$this->order_id . "'");


        $sql_data_array = array(
            'orders_id' => $this->order_id,
            'comments' => $this->get_private_info(),
            'admin_id' => (int) $cart->admin_id,
            'date_added' => 'now()'
        );
        tep_db_perform(TABLE_ORDERS_HISTORY, $sql_data_array);

        tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "'");
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $sql_data_array = array(
                'orders_id' => $this->order_id,
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
                'currency' => $this->info['currency'],
                'currency_value' => $this->info['currency_value'],
            );
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
            if ($order_totals[$i]['adjusted']){
                tep_db_query("update " . TABLE_ORDERS . " set adjusted ='1' where orders_id = '" . (int) $this->order_id . "'");
            }
        }
        $removed = $cart->getHiddenModules();
        if (count($removed)) {
            for ($i = 0, $n = sizeof($removed); $i < $n; $i++) {
                $sql_data_array = array(
                    'orders_id' => $this->order_id,
                    'title' => '',
                    'text' => '',
                    'value' => 0,
                    'class' => $removed[$i],
                    'sort_order' => 0,
                    'text_exc_tax' => '',
                    'text_inc_tax' => '',
                    'tax_class_id' => 0,
                    'value_exc_vat' => 0,
                    'value_inc_tax' => 0,
                    'is_removed' => 1,
                    'currency' => $this->info['currency'],
                    'currency_value' => $this->info['currency_value'],
                );
                tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
            }
        }

        //if(!\frontend\design\Info::isTotallyAdmin()) tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$this->order_id . "'");
        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $this->order_id,
            'orders_status_id' => $this->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $this->info['comments'],
            'admin_id' => (int) $cart->admin_id,
        );
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        
        $ref_id = $cart->getReference();
        if (!is_null($ref_id)){
            $sql_data_array = array(
                'orders_id' => $this->order_id,
                'comments' => TEXT_REORDER_FROM . $ref_id,
                'admin_id' => (int) $cart->admin_id,
                'date_added' => 'now()'
                );
            tep_db_perform(TABLE_ORDERS_HISTORY, $sql_data_array);
            
            foreach($this->collectAdminComments($ref_id) as $_comment){
               $sql_data_array = array(
                        'orders_id' => $this->order_id,
                        'orders_status_id' => $this->info['order_status'],
                        'comments' => TEXT_COMMENT_FROM_REORDERED . "\n" . $_comment['comments'],
                        'customer_notified' => 0,
                        'admin_id' => (int) $_comment['admin_id'],
                        'date_added' => 'now()'
                    );
                tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array); 
            }
        }
        
        if (($paid_info = $cart->getPaidInfo()) !== false){
            if (is_array($paid_info['info'])){
                foreach($paid_info['info'] as $pi){
                    $sql_data_array = array(
                        'orders_id' => $this->order_id,
                        'orders_status_id' => ($paid_info['status']?$paid_info['status']:$this->info['order_status']),
                        'comments' => $pi['comment'],
                        'customer_notified' => $customer_notification,
                        'admin_id' => (int) $cart->admin_id,
                        'date_added' => 'now()'
                    );
                    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);                    
                }
            }
        }
        
        return;
    }
    
    public function collectAdminComments($ref_id){
        global $cart;
        $comments = [];
        $query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = {$ref_id} and admin_id > 0");
        if (tep_db_num_rows($query)){
            $tracking = \common\helpers\Translation::getTranslationValue('TEXT_TRACKING_NUMBER', 'admin/orders', $cart->language_id);
            while($row = tep_db_fetch_array($query)){
                if (empty($row['comments'])) continue;
                if ($tracking && strpos($row['comments'], $tracking)) continue;
                $comments[] = $row;
            }
        }
        return $comments;
    }

    public function clear_products() {
        \common\helpers\Order::restock($this->order_id);
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $this->order_id . "'");
    }

    public function save_products($notify = true) {
        global $payment, $order_total_modules, $languages_id, $currencies, $cart;

// initialized for the email confirmation
        $products_ordered = '';
        $subtotal = 0;
        $total_tax = 0;
        $stock_update_flag = false;

        $this->clear_products();
        for ($i = 0, $n = sizeof($this->products); $i < $n; $i++) {
            if ((STOCK_LIMITED == 'true' && !$GLOBALS[$payment]->dont_update_stock) || \common\helpers\Order::is_stock_updated(intval($this->order_id))) {
                global $login_id;
                \common\helpers\Product::log_stock_history_before_update($this->products[$i]['id'], $this->products[$i]['qty'], '-',
                                                                         ['comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $this->order_id]);
                \common\helpers\Product::update_stock($this->products[$i]['id'], 0, $this->products[$i]['qty']);
                $stock_update_flag = true;
            }

// Update products_ordered (for bestsellers list)
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $this->products[$i]['qty']) . " where products_id = '" . \common\helpers\Inventory::get_prid($this->products[$i]['id']) . "'");
            $sql_data_array = array('orders_id' => $this->order_id,
                'products_id' => \common\helpers\Inventory::get_prid($this->products[$i]['id']),
                'products_model' => $this->products[$i]['model'],
                'products_name' => $this->products[$i]['name'],
                'products_price' => $this->products[$i]['price'],
                'final_price' => $this->products[$i]['final_price'],
                'products_tax' => $this->products[$i]['tax'],
                'products_quantity' => $this->products[$i]['qty'],
                'is_giveaway' => $this->products[$i]['ga'],
                'is_virtual' => $this->products[$i]['is_virtual'],
                'gift_wrap_price' => $this->products[$i]['gift_wrap_price'],
                'gift_wrapped' => $this->products[$i]['gift_wrapped'] ? 1 : 0,
                'gv_state' => $this->products[$i]['gv_state'],
                'uprid' => \common\helpers\Inventory::normalize_id($this->products[$i]['id']),
                'overwritten' => serialize($this->products[$i]['overwritten']));
            
            if (is_object($cart)){ // for edit order, after saving to know how match was saved
                $cart->contents[$this->products[$i]['id']]['reserved_qty'] = $this->products[$i]['qty'];
                $this->products[$i]['reserved_qty'] = $this->products[$i]['qty'];
            }

            if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'saveProductsOrderFrontend')) {
                $sql_data_array = array_merge($ext::saveProductsOrderFrontend($this->products, $i), $sql_data_array);
            }

            tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

            $order_products_id = tep_db_insert_id();

            $order_total_modules->update_credit_account($i); //ICW ADDED FOR CREDIT CLASS SYSTEM
            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';

            if ((DOWNLOAD_ENABLED == 'true') && tep_not_null($this->products[$i]['products_file'])) {
                $sql_data_array = array('orders_id' => $this->order_id,
                    'orders_products_id' => $order_products_id,
                    'orders_products_name' => $this->products[$i]['name'],
                    'orders_products_filename' => $this->products[$i]['products_file'],
                    'download_maxdays' => DOWNLOAD_MAX_DAYS,
                    'download_count' => DOWNLOAD_MAX_COUNT);
                tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
            }

            // {{ Products Bundle Sets
            $sets_array = array();
            if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'cartOrderProducts')) {
                list($sets_array, $sets_products_ordered_attributes) = $ext::cartOrderProducts($this->products[$i], $this->order_id);
                $products_ordered_attributes .= $sets_products_ordered_attributes;
            }
            // }}
            // {{ Virtual Gift Card
            if ($this->products[$i]['virtual_gift_card'] && $this->products[$i]['attributes'][0]['value_id'] > 0) {
                $virtual_gift_card_code = \common\helpers\Gifts::virtual_gift_card_process($this->products[$i]['attributes'][0]['value_id'], $this->customer['email_address']);
                if (tep_not_null($virtual_gift_card_code)) {
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_products_id' => $order_products_id,
                        'products_options_id' => 0,
                        'products_options_values_id' => $this->products[$i]['attributes'][0]['value_id'],
                        'products_options' => TEXT_GIFT_CARD_CODE,
                        'products_options_values' => $virtual_gift_card_code);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
                }
            } else
            // }}
            if (isset($this->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($this->products[$i]['attributes']); $j < $n2; $j++) {
                    // {{ Products Bundle Sets
                    if ($this->products[$i]['attributes'][$j]['option_id'] == 0 && $this->products[$i]['attributes'][$j]['value_id'] == 0)
                        continue;
                    // }}
                    if ($this->products[$i]['attributes'][$j]['option_id'] == -2) {
                        $attributes_values = array(
                            'products_options_name' => $this->products[$i]['attributes'][$j]['option'],
                            'products_options_values_name' => $this->products[$i]['attributes'][$j]['value'],
                        );
                    } else
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_maxdays, pa.products_attributes_maxcount , pa.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   where pa.products_id = '" . $this->products[$i]['id'] . "'
                                    and pa.options_id = '" . $this->products[$i]['attributes'][$j]['option_id'] . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . $this->products[$i]['attributes'][$j]['value_id'] . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . $languages_id . "'
                                    and poval.language_id = '" . $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                        $attributes_values = tep_db_fetch_array($attributes);
                    } else {
                        $attributes = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $this->products[$i]['id'] . "' and pa.options_id = '" . $this->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $this->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                        $attributes_values = tep_db_fetch_array($attributes);
                    }

                    $attributes_values['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes_values['products_attributes_id']);
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values['products_options_name'],
                        'products_options_values' => $attributes_values['products_options_values_name'],
                        'options_values_price' => $attributes_values['options_values_price'],
                        'price_prefix' => $attributes_values['price_prefix'],
                        'products_options_id' => $this->products[$i]['attributes'][$j]['option_id'],
                        'products_options_values_id' => $this->products[$i]['attributes'][$j]['value_id']);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                        $sql_data_array = array('orders_id' => $this->order_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_name' => $this->products[$i]['name'],
                            'orders_products_filename' => $attributes_values['products_attributes_filename'],
                            'download_maxdays' => ($attributes_values['products_attributes_maxdays'] ? $attributes_values['products_attributes_maxdays'] : DOWNLOAD_MAX_DAYS),
                            'download_count' => ($attributes_values['products_attributes_maxcount'] ? $attributes_values['products_attributes_maxcount'] : DOWNLOAD_MAX_COUNT));
                        tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes .= "\n\t" . htmlspecialchars($attributes_values['products_options_name']) . ': ' . htmlspecialchars($attributes_values['products_options_values_name']);
                }
            }

            // {{ Products Bundle Sets
            if (count($sets_array) > 0) {
                tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set sets_array = '" . tep_db_input(serialize($sets_array)) . "' where orders_products_id = '" . (int) $order_products_id . "'");
            }
            // }}
            //------insert customer choosen option eof ----
            $total_weight += ($this->products[$i]['qty'] * $this->products[$i]['weight']);
            $total_tax += \common\helpers\Tax::calculate_tax($total_products_price, $products_tax) * $this->products[$i]['qty'];
            $total_cost += $total_products_price;

            $products_ordered .= $this->products[$i]['qty'] . ' x ' . $this->products[$i]['name'] . (($this->products[$i]['model'] != '') ? ' (' . $this->products[$i]['model'] . ')' : '') . ' = ' . $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']) . $products_ordered_attributes . "\n";

//            $products_ordered .= $this->products[$i]['qty'] . ' x ' . $this->products[$i]['name'] . (($this->products[$i]['model'] != '') ? ' (' . $this->products[$i]['model'] . ')' : '') . ' = ' . $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }
        if ($stock_update_flag) {
            tep_db_query("UPDATE " . TABLE_ORDERS . " SET stock_updated=1 WHERE orders_id='" . intval($this->order_id) . "'");
        }
        
        $cart->emptyReference();

        if ($notify) {
            $this->notify_customer($products_ordered);
        }
    }

    public function notify_customer($products_ordered) {
        global $order_totals, $customer_id, $sendto, $billto, $payment;

        $email_params = array();
        $email_params['STORE_NAME'] = STORE_NAME;
        $email_params['ORDER_NUMBER'] = $this->order_id;
        $email_params['ORDER_DATE_SHORT'] = strftime(DATE_FORMAT_SHORT);
        if (\frontend\design\Info::isTotallyAdmin()) {
            $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false));
        } else {
            $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false));
        }
        $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
        if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'mailInfo')){
            $email_params['ORDER_DATE_LONG'] .= $ext::mailInfo($this->info['delivery_date']);
        }
        $email_params['PRODUCTS_ORDERED'] = substr($products_ordered, 0, -1);

        $email_params['ORDER_TOTALS'] = '';

        $order_total_output = [];
        foreach ($order_totals as $total) {
            if (class_exists($total['code'])) {
                if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                    if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_EMAIL')) {
                        if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                            $order_total_output[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_EMAIL', $total);
                        } else {
                            $order_total_output[] = $total;
                        }
                    }
                }
            }
        }
        for ($i = 0, $n = sizeof($order_total_output); $i < $n; $i++) {
            $email_params['ORDER_TOTALS'] .= ($order_total_output[$i]['show_line'] ? '<div style="border-top:1px solid #ccc"></div>' : '') . strip_tags($order_total_output[$i]['title']) . ' ' . strip_tags($order_total_output[$i]['text']) . "\n";
        }
        $email_params['ORDER_TOTALS'] = substr($email_params['ORDER_TOTALS'], 0, -1);
        $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_label($customer_id, $billto, 0, '', "\n");
        $email_params['DELIVERY_ADDRESS'] = ($this->content_type != 'virtual' ? \common\helpers\Address::address_label($customer_id, $sendto, 0, '', "\n") : '');
        $payment_method = '';
        if (!empty($payment) && is_object($GLOBALS[$payment])) {
            $payment_method = $GLOBALS[$payment]->title;
            if ($GLOBALS[$payment]->email_footer) {
                $payment_method .= "\n\n" . $GLOBALS[$payment]->email_footer;
            }
        }
        $email_params['PAYMENT_METHOD'] = $payment_method;

        $email_params['ORDER_COMMENTS'] = tep_db_output($this->info['comments']);

        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Order Confirmation', $email_params, -1, $this->info['platform_id']);

        // {{
        if (!$GLOBALS[$payment]->dont_send_email)
        // }}
            \common\helpers\Mail::send(
                    $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS
            );
        // {{
        if (!$GLOBALS[$payment]->dont_send_email)
        // }}
            if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            } else {
                //            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO);
            }
    }

    public function haveSubscription() {
        if (is_array($this->products)) {
            foreach ($this->products as $value) {
                if ($value['subscription'] == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    public function stockAllowCheckout() {
        $checkout_allowed = true;
        if (STOCK_CHECK == 'true') {
            foreach ($this->products as $ordered_product) {
                if (!isset($ordered_product['stock_info']))
                    continue;

                if (!$ordered_product['stock_info']['allow_out_of_stock_checkout'] || $ordered_product['stock_info']['order_instock_bound']) {
                    $checkout_allowed = false;
                    break;
                }
            }
        }
        return $checkout_allowed;
    }
    
    public function update_piad_information($without_check = false){
        global $order_totals, $payment;
        if ((is_object($GLOBALS[$payment]) && $GLOBALS[$payment]->isOnline()) || $without_check) {
            $this->info['total_paid_inc_tax'] = $this->info['total_inc_tax'];
            $this->info['total_paid_exc_tax'] = $this->info['total_exc_tax'];
            if (isset($GLOBALS['ot_paid']) || isset($GLOBALS['ot_due'])){
                if(is_array($order_totals)){
                    foreach($order_totals as $key => $ot){
                        if (in_array($ot['code'], ['ot_paid', 'ot_due']) ){
                            $GLOBALS[$ot['code']]->process();
                            list(,$v) = each($GLOBALS[$ot['code']]->output);
                            $order_totals[$key] = $v;
                        }
                    }
                }
            }
        }
        
    }

}
