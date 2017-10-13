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

use common\classes\opc_order;
use common\classes\order;
use frontend\design\boxes\cart\OrderTotal;
use frontend\design\boxes\cart\ShippingEstimator;
use frontend\design\Info;
use Yii;

/**
 * Site controller
 */
class ShoppingCartController extends Sceleton
{

    public function actionIndex()
    {
        global $currencies, $cart, $order, $cc_id, $messageStack, $customer_id, $breadcrumb, $shipping;

        if (GROUPS_DISABLE_CHECKOUT){
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }
        
        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));

        if (tep_session_is_registered('customer_id')){
            $checkout_link = tep_href_link('checkout', '', 'SSL');
        } else {
            $checkout_link = tep_href_link('checkout/login', '', 'SSL');
        }
        
        global $select_shipping;
        if (!tep_session_is_registered('select_shipping')){
            $select_shipping = '';
            tep_session_register('select_shipping');
        }
        //$select_shipping = 'zonesperitem_standard';
        
        $payment_modules = new \common\classes\payment();
        
        if (Yii::$app->request->isPost && isset($_POST['ajax_estimate'])){
            return $this->actionEstimate();
        }

        $message_discount_coupon = '';
        if ( $messageStack->size('cart_discount_coupon')>0 ) {
            $message_discount_coupon = $messageStack->output('cart_discount_coupon');
        }
        $message_discount_gv = '';
        if ( $messageStack->size('cart_discount_gv')>0 ) {
            $message_discount_gv = $messageStack->output('cart_discount_gv');
        }
        $ot_gv_data = array(
          'can_apply_gv_credit' => false,
          'message_discount_gv' => $message_discount_gv,
          'credit_amount' => '',
          'credit_gv_in_use' => tep_session_is_registered('cot_gv'),
        );
        if ( defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS=='true' && tep_session_is_registered('customer_id') ) {
            $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            if ( tep_db_num_rows($gv_query)>0 ) {
                $gv_result = tep_db_fetch_array($gv_query);
                if ($gv_result['amount']>0){
                    $ot_gv_data['can_apply_gv_credit'] = true;
                    $ot_gv_data['credit_amount'] = $currencies->format($gv_result['amount']);
                }
            }
        }
        
        

        $render_data = array(
          'action' => tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product'),
          'cart_link' => tep_href_link(FILENAME_SHOPPING_CART),
          'cart_count' => $cart->count_contents(),
          'home_page_link' => tep_href_link('index'),
          'checkout_link' => $checkout_link,
          'gv_redeem_code' => \common\helpers\Coupon::get_coupon_name($cc_id),
          'message_discount_coupon' => $message_discount_coupon,
          'message_shopping_cart' => ($messageStack->size('shopping_cart')>0?$messageStack->output('shopping_cart'):''),
        );
        $render_data = array_merge($render_data, $this->prepareEstimateData());
        $render_data = array_merge($render_data, $ot_gv_data);

        if (Yii::$app->request->isAjax && $_GET['popup'] && Info::themeSetting('after_add') == 'popup'){
            return $this->render('popup.tpl', $render_data);
        } else {
            return $this->render('index.tpl', $render_data);
        }
    }

    static function prepareEstimateData()
    {
        global $currencies, $cart, $order, $cc_id;
        global $opc_sendto, $customer_id, $customer_default_address_id;
        global $cart_address_id, $shipping;
        global $request_type;

        $addresses_array = array();
        if ( tep_session_is_registered('customer_id') ) {
            $addresses_query = tep_db_query("select address_book_id, entry_gender, entry_company, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street, entry_suburb as suburb, entry_postcode as postcode, entry_city as city, entry_postcode as postcode, if(length(zone_name),zone_name,entry_state) as state, entry_zone_id as zone_id, entry_country_id as country_id, zone_name from " . TABLE_ADDRESS_BOOK . " left JOIN " . TABLE_ZONES . " z on z.zone_id = entry_zone_id and z.zone_country_id = entry_country_id where customers_id = '" . (int)$customer_id . "'");
            while ($addresses = tep_db_fetch_array($addresses_query)) {
                $addresses_array[(int)$addresses['address_book_id']] = array(
                  'id' => $addresses['address_book_id'],
                  'text' => \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($addresses['country_id']), $addresses, 0, ' ', ' '),
                  'country_id' => $addresses['country_id'],
                  'postcode' => $addresses['postcode'],
                );
            }
        }
        if ( tep_session_is_registered('cart_address_id') ) {
            if ( !isset($addresses_array[(int)$cart_address_id]) ) {
                tep_session_unregister('cart_address_id');
                unset($cart_address_id);
            }
        }

        $estimate_sendto = 0;
        if ( Yii::$app->request->isPost) {
            $ship_country = (int)STORE_COUNTRY;
            $postcode = '';
            if ( isset($_POST['estimate']) && is_array($_POST['estimate']) ) {
                if ( isset($_POST['estimate']['country_id']) && (int)$_POST['estimate']['country_id']>0 ) {
                    $ship_country = intval($_POST['estimate']['country_id']);
                }
                if ( isset($_POST['estimate']['post_code']) && !empty($_POST['estimate']['post_code']) ) {
                    $postcode = tep_db_prepare_input($_POST['estimate']['post_code']);
                }
                if ( tep_session_is_registered('customer_id') &&  isset($_POST['estimate']['sendto']) && (int)$_POST['estimate']['sendto']>0 ) {
                    if ( isset( $addresses_array[intval($_POST['estimate']['sendto'])] ) ) {
                        $cart_address_id = intval($_POST['estimate']['sendto']);
                        if (!tep_session_is_registered('cart_address_id')) tep_session_register('cart_address_id');
                        $estimate_sendto = (int)$cart_address_id;
                        $ship_country = $addresses_array[$cart_address_id]['country_id'];
                        $postcode = $addresses_array[$cart_address_id]['postcode'];
                    }
                }
            }
        } else{
            $ship_country = (int)STORE_COUNTRY;
            $postcode = '';
            if ( tep_session_is_registered('customer_id') ) {
                $estimate_sendto = $customer_default_address_id;
                if (tep_session_is_registered('cart_address_id')) {
                    $estimate_sendto = (int)$cart_address_id;
                }
                if ( isset($addresses_array[$estimate_sendto]) ) {
                    $ship_country = $addresses_array[$estimate_sendto]['country_id'];
                    $postcode = $addresses_array[$estimate_sendto]['postcode'];
                }
            }
        }
        $_country_info = \common\helpers\Country::get_countries($ship_country, true);
        $opc_sendto = array(
          'postcode' => $postcode,
          'country' => array(
            'id' => $ship_country,
            'title' => $_country_info['countries_name'],
            'iso_code_2' => $_country_info['countries_iso_code_2'],
            'iso_code_3' => $_country_info['countries_iso_code_3'],
          ),
          'country_id' => $ship_country,
        );
		
		if (!\frontend\design\Info::isTotallyAdmin())
		$order = new opc_order();
        

        /**
         * @var $cart \shoppingCart
         */
        // weight and count needed for shipping !
        global $total_weight, $total_count;
        $total_weight = $cart->show_weight();
        $total_count = $cart->count_contents();

        //$order_total_modules = new \common\classes\order_total();

        $free_shipping = false;
        $quotes = array();
        //$select_shipping = (isset($_POST['estimate']) && isset($_POST['estimate']['shipping']) && $_POST['estimate']['shipping'])?$_POST['estimate']['shipping']:'';
        $cheapest_shipping = '';
        
        global $select_shipping;
        if (!tep_session_is_registered('select_shipping')){
            tep_session_register('select_shipping');
        }
        if ( isset($_POST['estimate']) && isset($_POST['estimate']['shipping']) && $_POST['estimate']['shipping'] ) {
            $select_shipping = $_POST['estimate']['shipping'];
            tep_session_register('select_shipping');
        }
        
        //$select_shipping = 'zonesperitem_standard';
        
        if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight') ) {

            $shipping_modules = new \common\classes\shipping();

            if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
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

                $free_shipping = false;
                if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
                    $free_shipping = true;
                }
            } else {
                $free_shipping = false;
            }
            // get all available shipping quotes
            $quotes = $shipping_modules->quote();
			

            $cheapest_shipping_array = $shipping_modules->cheapest();
            if ( is_array($cheapest_shipping_array) ) {
                $cheapest_shipping = $cheapest_shipping_array['id'];
            }
        }
        if ( empty($select_shipping) ) {
            $select_shipping = $cheapest_shipping;
        }

        $quotes_radio_buttons = 0;
        if ( $free_shipping ) {
            $quotes = array(
              array(
                'id' => 'free',
                'module' => FREE_SHIPPING_TITLE,
                'methods' => array(
                  array(
                    'id' => 'free',
                    'selected' => true,
                    'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)),
                    'code' => 'free_free',
                    'cost_f' => '&nbsp;',
                    'cost' => 0,
                  ),
                ),
              ),
            );
        }else {
            for ($i = 0, $n = sizeof($quotes); $i < $n; $i++) {
                if (!isset($quotes[$i]['error'])) {
                    for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j++) {
                        $quotes[$i]['methods'][$j]['cost_f'] = $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
                        $quotes[$i]['methods'][$j]['code'] = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
                        $quotes[$i]['methods'][$j]['selected'] = ($select_shipping==$quotes[$i]['methods'][$j]['code']);
                        $quotes_radio_buttons++;
                    }
                }else{
                    unset($quotes[$i]);
                }
            }
            $quotes = array_values($quotes);
        }
        $keep_shipping = $shipping;
        foreach ($quotes as $quote_info) {
            if ( !is_array($quote_info['methods']) ) continue;
            foreach($quote_info['methods'] as $quote_method ) {
                if ( $quote_method['selected'] ) {
                    $shipping = array(
                      'id' => $quote_method['code'],
                      'title' => $quote_info['module'].(empty($quote_method['title'])?'':' ('.$quote_method['title'].')'),
                      'cost' => $quote_method['cost'],
					  'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote_method['cost'], (isset($quote_info['tax']) ? $quote_info['tax'] : 0))
                    );
                    $order->change_shipping($shipping);
                    $select_shipping = $quote_method['code'];
                    tep_session_register('select_shipping');
                }
            }
        }

        $order_total_modules = new \common\classes\order_total();

        //$order_total_modules->collect_posts();

        //$order_total_modules->pre_confirmation_check();

        //$order_total_output = $order_total_modules->process();

		//if (!\frontend\design\Info::isTotallyAdmin()) $shipping = $keep_shipping;

        return array(
          'is_logged_customer' => tep_session_is_registered('customer_id'),
          'estimate_country' => $order->delivery['country_id'],
          'estimate_postcode' => $order->delivery['postcode'],
          'countries' => \common\helpers\Country::get_countries(),
          'addresses_array' => array_values($addresses_array),
          'addresses_selected_value' => $estimate_sendto,
          'cart_weight' => rtrim(rtrim(number_format($cart->weight,2,'.',''),'0'),'.'),
          //'order_total_output' => $order_total_output,
          'quotes' => $quotes,
          'quotes_radio_buttons' => $quotes_radio_buttons,
          'estimate_ajax_server_url' => tep_href_link(FILENAME_SHOPPING_CART,'',$request_type)
        );
    }
    
    public function actionEstimate(){
        $this->layout = false;
        //return $this->render('estimate.tpl', $this->prepareEstimateData());
        return json_encode(array('estimate' => ShippingEstimator::widget(), 'total' => OrderTotal::widget()));
    }


}
