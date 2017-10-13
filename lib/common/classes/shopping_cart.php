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

class shopping_cart {

    public $contents;
    public $total;
    public $weight;
    public $cartID;
    public $content_type;
    public $giveaway; // separated storage for GA products
    public $platform_id; //current customer selected platform
    public $language_id; //current customer selected language
    public $currency; //current customer selected currency
    public $basketID; //current customer basket id, used for recovery cart
    public $admin_id = 0; //used at backend
    public $order_id;
    public $reference_id;
    public $customer_id;
    public $address = [];
    private $overwrite = []; // to overwrte by admin wish
    private $totals = []; // to overwrte by admin wish
    private $hidden = []; // to overwrte by admin wish
    public $readonly = ['ot_tax', 'ot_total', 'ot_subtotal', 'ot_subtax', 'ot_due', 'ot_paid'];
    private $adjusted = false; // order adjusted by 0.01
    private $products_array = [];
    private $paid = [];
    private $order_status;

    function __construct($id = '') {
        global $languages_id, $currency;
        $this->reset();
        $this->platform_id = \common\classes\platform::currentId();
        $this->language_id = $languages_id;
        $this->currency = $currency;
        if (tep_not_null($id)) {
            $this->order_id = $id;
            $this->restore_order();
        }
        $this->reference_id = null;
    }
    
    public function setReference($reference_id){
        $this->reference_id = (int)$reference_id;
    }
    
    public function emptyReference(){
        $this->reference_id = null;
    }
    
    public function getReference(){
        return $this->reference_id;
    }

    function restore_order() {
        $contents = array();
        $this->overwrite = array();
        $res = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");
        $this->giveaway = array();
        $this->totals = array();
        $this->hidden = array();
        while ($d = tep_db_fetch_array($res)) {
            if ($d['is_giveaway']) {
                $this->giveaway = array();
                $this->giveaway[$d['uprid']] = array('qty' => $d['products_quantity'], 'reserved_qty' => $d['products_quantity']);
            } else {
                $contents[$d['uprid']] = array('qty' => $d['products_quantity'], 'reserved_qty' => $d['products_quantity']);
                if ($d['gift_wrapped']) {
                    $contents[$d['uprid']]['gift_wrap'] = 1;
                }
                $this->setOverwrite($d['uprid'], 'name', $d['products_name']);
                $this->setOverwrite($d['uprid'], 'model', $d['products_model']);
                $this->setOverwrite($d['uprid'], 'price', $d['products_price']);
                $this->setOverwrite($d['uprid'], 'final_price', $d['final_price']);
                if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'queryOrderAdmin')) {
                    $_units = $ext::queryOrderAdmin($this->order_id, 0, $d['orders_products_id']);
                    if (is_array($_units) && count($_units)){
                        while(list($k, $v) = each($_units)){
                            $this->setOverwrite($d['uprid'], $k, $v);
                        }
                    }
                }
            }
            $ares = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . $this->order_id . "' and orders_products_id = '" . $d['orders_products_id'] . "'");
            if (tep_db_num_rows($ares)) {
                if ($d['is_giveaway']) {
                    $this->giveaway[$d['uprid']]['attributes'] = [];
                    while ($a = tep_db_fetch_array($ares)) {
                        $this->giveaway[$d['uprid']]['attributes'][$a['products_options_id']] = $a['products_options_values_id'];
                    }
                } else {
                    $contents[$d['uprid']]['attributes'] = [];
                    while ($a = tep_db_fetch_array($ares)) {
                        if ($a['products_options'] == GIFT_WRAP_OPTION)
                            continue;
                        if (!tep_not_null($a['products_options_values']) && !$a['products_options_id'] && !$a['products_options_values_id'] && strlen($a['price_prefix'] == 0)) {
                            //
                        } else {
                            $contents[$d['uprid']]['attributes'][$a['products_options_id']] = $a['products_options_values_id'];
                        }
                    }
                }
            }
            if (tep_not_null($d['sets_array'])){
                $bundle = unserialize($d['sets_array']);
                list(,$bundle) = each($bundle);
                if (is_array($bundle)){
                    if (isset($bundle['attributes']) && is_array($bundle['attributes'])){
                        foreach($bundle['attributes'] as $option => $value){
                             $contents[$d['uprid']]['attributes'][$option . '-'. $bundle['id']] = $value;
                        }
                    }
                }
            }
            if (tep_not_null($d['overwritten'])) {
                $_temp = unserialize($d['overwritten']);
                if (is_array($_temp)) {
                    foreach ($_temp as $_k => $_v) {
                        $this->setOverwrite($d['uprid'], $_k, $_v);
                    }
                }
            }
        }
        
        $res = tep_db_fetch_array(tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . $this->order_id . "'"));
        $this->setOrderStatus($res['orders_status']);
        
        $this->contents = $contents;
        $this->get_content_type();
        $this->restoreTotals();

    }
    
    public function restoreTotals(){
        if ($this->order_id > 0){
            $totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "' and is_removed = 0 order by sort_order");
            while ($totals = tep_db_fetch_array($totals_query)) {
                $this->totals[$totals['class']] = array('title' => $totals['title'],
                    'value' => [
                        'in' => $totals['value_inc_tax'],
                        'ex' => $totals['value_exc_vat'],
                    ],
                    //$totals['value'],
                    'class' => $totals['class'],
                    'text' => $totals['text'],
                    'text_exc_tax' => $totals['text_exc_tax'],
                    'text_inc_tax' => $totals['text_inc_tax'],
    // {{
                    'tax_class_id' => $totals['tax_class_id'],
                    'value_exc_vat' => $totals['value_exc_vat'],
                    'value_inc_tax' => $totals['value_inc_tax'],
    // }}
                );
                if ($totals['class'] == 'ot_tax' && $this->totals[$totals['class']]['value_exc_vat'] != $this->totals[$totals['class']]['value_inc_tax']) {
                    $this->totals[$totals['class']]['prefix'] = ($this->totals[$totals['class']]['value_inc_tax'] > $this->totals[$totals['class']]['value_exc_vat'] ? "+" : "-");
                    $this->totals[$totals['class']]['value'] = [
                        'in' => abs($this->totals[$totals['class']]['value_inc_tax'] - $this->totals[$totals['class']]['value_exc_vat']),
                        'ex' => abs($this->totals[$totals['class']]['value_inc_tax'] - $this->totals[$totals['class']]['value_exc_vat'])
                    ];
                }
            }
            $removed_query = tep_db_query("select class from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "' and is_removed = 1 order by sort_order");
            while ($removed = tep_db_fetch_array($removed_query)) {
                $this->addHiddenModule($removed['class']);
            }
            $adj = tep_db_fetch_array(tep_db_query("select adjusted from " . TABLE_ORDERS . " where orders_id = '" . (int) $this->order_id . "'"));
            $this->setAdjusted($adj['adjusted']);        
        }
    }

    public function setTotalKey($code, $value) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        //$readonly = ['ot_tax', 'ot_total', 'ot_subtotal'];
        if (!in_array($code, $this->readonly))
            $this->totals[$code]['value'] = $value;
    }

    public function setTotalTax($code, $value, $prefix) { //only for tax
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        $this->totals[$code]['value'] = $value;
        $this->totals[$code]['prefix'] = $prefix;
    }
    
    public function setTotalPaid($value, $comment) { //only for paid
        global $currencies;
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (!is_array($this->totals['ot_paid'])) $this->totals['ot_paid'] = [];
        if (!is_array($this->totals['ot_paid']['value'])) $this->totals['ot_paid']['value'] = [];
        if (is_array($this->totals['ot_paid']['value'])){
            $this->totals['ot_paid']['value']['in'] += $value;
            $this->totals['ot_paid']['value']['ex'] += $value;
        }        
        
        if (!isset($this->totals['ot_paid']['info'])) $this->totals['ot_paid']['info'] = [];
        $this->totals['ot_paid']['info'][] = [
            'comment' => $currencies->format($value) . ' ' . $comment,
            ];
    }
    
    public function getPaidInfo(){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (isset($this->totals['ot_paid']['info'])) {
            $info = ['info' => $this->totals['ot_paid']['info'], 'status' => $this->getStatusAfterPaid()];
            return $info;
        }
            
        return false;
    }
    
    public function getStatusAfterPaid(){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (!is_null($this->order_status)) return $this->order_status;
        return false;
    }
    
    public function setOrderStatus($status){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        $this->order_status = $status;
    }

    public function getTotalTaxPrefix() {
        return $this->totals['ot_tax']['prefix'];
    }

    public function getTotalKey($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (isset($this->totals[$code]))
            return $this->totals[$code]['value'];
        return false;
    }

    public function getTotalTitle($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (isset($this->totals[$code]))
            return $this->totals[$code]['title'];
        return false;
    }

    public function setTotalTitle($code, $value) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        //$readonly = ['ot_tax', 'ot_total', 'ot_subtotal'];
        if (!in_array($code, $this->readonly))
            $this->totals[$code]['title'] = $value;
    }

    public function getAllTotals() {
        if (!\frontend\design\Info::isTotallyAdmin())
            return [];
        return $this->totals;
    }

    public function clearTotalKey($code) {
        if (isset($this->totals[$code]))
            unset($this->totals[$code]);
    }

    public function clearTotals($only_values = false) {
        if (!$only_values){
            if (is_array($this->totals) && count($this->totals)){
                foreach($this->totals as $k=>$v){
                    if (!in_array($k, ['ot_coupon', 'ot_gv'])){
                        unset($this->totals[$k]);
                    }
                }
            }
            //$this->totals = array();
        } else {
            if (is_array($this->totals)){
                foreach($this->totals as $k => $v){
                    $this->totals[$k]['value'] = ['in'=>$this->totals[$k]['value_inc_tax'], 'ex'=> $this->totals[$k]['value_exc_vat']];
                }
            }
        }
        
    }

    public function addHiddenModule($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (!in_array($code, $this->readonly))
            $this->hidden[] = $code;
    }

    public function existHiddenModule($code) {
        return in_array($code, $this->hidden);
    }

    public function clearHiddenModule($code) {
        if (in_array($code, $this->hidden)) {
            $r = array_flip($this->hidden);
            unset($this->hidden[$r[$code]]);
        }
    }

    public function clearHiddenModules() {
        $this->hidden = [];
    }

    public function getHiddenModules() {
        return $this->hidden;
    }

    function restore_contents() {
        global $customer_id, $gv_id, $REMOTE_ADDR, $languages_id, $currency;

        if (!tep_session_is_registered('customer_id'))
            return false;

        $get_basket = tep_db_fetch_array(tep_db_query("select distinct basket_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "'"));

        if ($get_basket && $get_basket['basket_id']) {
            $this->basketID = $get_basket['basket_id'];
        } else {
            $this->basketID = $this->generate_cart_id();
        }
        
        if (!$this->platform_id)
            $this->platform_id = \common\classes\platform::currentId();
        if (!$this->language_id)
            $this->language_id = $languages_id;
        if (!$this->currency)
            $this->currency = $currency;

        // insert current cart contents in database
        if (is_array($this->contents)) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
                $qty = (int) $this->contents[$products_id]['qty'];
                $product_query = tep_db_query("select products_id, customers_basket_quantity from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
                if (!tep_db_num_rows($product_query)) {
                    tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "')");
                    if (isset($this->contents[$products_id]['gift_wrap'])) {
                        tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input('gift_wrap') . "', '" . intval($this->contents[$products_id]['gift_wrap']) . "')");
                    }
                    if (isset($this->contents[$products_id]['attributes'])) {
                        reset($this->contents[$products_id]['attributes']);
                        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
                            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input($option) . "', '" . (int) $value . "')");
                        }
                    }
                } else {
                    $basket_product_data = tep_db_fetch_array($product_query);
                    $new_qty = \common\helpers\Product::filter_product_order_quantity($products_id, $basket_product_data['customers_basket_quantity'] + $qty);
                    tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = " . (int) $new_qty . ", basket_id = '" . (int) $this->basketID . "', platform_id = '" . $this->platform_id . "' where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
                    if (isset($this->contents[$products_id]['gift_wrap'])) {
                        $check_existing = tep_db_fetch_array(tep_db_query(
                                        "SELECT COUNT(*) AS c " .
                                        "FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " " .
                                        "WHERE customers_id='" . (int) $customer_id . "' AND products_id='" . tep_db_input($products_id) . "' AND products_options_id='" . tep_db_input('gift_wrap') . "'"
                        ));
                        if ($check_existing['c'] == 0) {
                            //tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "'");
                            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input('gift_wrap') . "', '" . intval($this->contents[$products_id]['gift_wrap']) . "')");
                        }
                    }
                }
            }
            //ICW ADDDED FOR CREDIT CLASS GV - START
            if (tep_session_is_registered('gv_id')) {
                $gv_query = tep_db_query("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . (int) $gv_id . "', '" . (int) $customer_id . "', now(),'" . tep_db_input($REMOTE_ADDR) . "')");
                tep_gv_account_update($customer_id, $gv_id);
                $gv_update = tep_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . (int) $gv_id . "'");
                tep_session_unregister('gv_id');
            }
            //ICW ADDDED FOR CREDIT CLASS GV - END
        }

        // recreate GA products
        if (sizeof($this->giveaway) > 0) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and is_giveaway = 1"); // delete old GA
            // insert new GA into DB
            foreach ($this->giveaway as $products_id => $giveaway) {
                tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added, is_giveaway, gaw_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . $giveaway['qty'] . "', '" . date('Ymd') . "', '1', '" . $giveaway['gaw_id'] . "')");
            }
        }

        // reset per-session cart contents, but not the database contents
        $this->reset(false);

        $products_query = tep_db_query("select * from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and is_giveaway = 0"); // select only ordinary products
        while ($products = tep_db_fetch_array($products_query)) {
            if (!\common\helpers\Product::check_product($products['products_id'])) {
                $this->remove($products['products_id']);
                continue;
            }
            if ($products['is_pack'] == 1) {
                $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity'], 'unit' => $products['unit'], 'pack_unit' => $products['pack_unit'], 'packaging' => $products['packaging'], 'is_pack' => 1);
            } else {
                $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity']);
            }
            // attributes
            $attributes_query = tep_db_query("select products_options_id, products_options_value_id from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products['products_id']) . "'");
            while ($attributes = tep_db_fetch_array($attributes_query)) {
                if ($attributes['products_options_id'] == 'gift_wrap') {
                    $this->contents[$products['products_id']]['gift_wrap'] = $attributes['products_options_value_id'];
                    continue;
                }
                $this->contents[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
            }
        }

        // recreate session GA
        $ga_query = tep_db_query("select customers_basket_id, products_id, customers_basket_quantity, gaw_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and is_giveaway = 1");
        while ($d = tep_db_fetch_array($ga_query)) {
            if ($d['gaw_id']==0) { //backward compatibility
              if($gaw_id = \common\helpers\Gifts::allowedGAW($d['products_id'], $d['customers_basket_quantity'])){
                tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set gaw_id='" . $gaw_id . "' where customers_basket_id='" . $d['customers_basket_id'] . "'");
                $d['gaw_id'] = $gaw_id;
              } else {
              //can't find matching active GAW - delete
                tep_db_query("delete from  " . TABLE_CUSTOMERS_BASKET . " where customers_basket_id='" . $d['customers_basket_id'] . "'");
                continue;
              }
            }
            $this->giveaway[$d['products_id']] = array('qty' => $d['customers_basket_quantity'], 'gaw_id' => $d['gaw_id']);
            if (strpos($d['products_id'], '{') !== false && preg_match_all('/\{(\d+)\}(\d+)/', $d['products_id'], $uprid_parts)) {
                $this->giveaway[$d['products_id']]['attributes'] = array();
                foreach ($uprid_parts[1] as $idx => $opt_id) {
                    $this->giveaway[$d['products_id']]['attributes'][$opt_id] = $uprid_parts[2][$idx];
                }
            }
        }

// {{ Virtual Gift Card
        if ($customer_id > 0) {
            tep_db_query("update " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " set customers_id = '" . (int) $customer_id . "', session_id = '' where length(virtual_gift_card_code) = 0 and customers_id = '0' and session_id = '" . tep_session_id() . "'");
        }
// }}
        $this->update_basket_info();

        $this->update_customer_info(false);

        $this->cleanup();

        $this->check_giveaway(); // GAW depends on customers group
    }

    function reset($reset_database = false) {
        global $customer_id;

        $this->contents = array();
        $this->overwrite = array();
        $this->giveaway = array();
        $this->hidden = array();
        $this->total = 0;
        $this->weight = 0;
        $this->content_type = false;
        //unset($this->products_array);
        $this->products_array = [];

        if (tep_session_is_registered('customer_id') && ($reset_database == true)) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customer_id . "'");
            $this->basketID = $this->generate_cart_id();
        }

        unset($this->cartID);
        if (tep_session_is_registered('cartID'))
            tep_session_unregister('cartID');
    }

    function __sleep() {
        $object_properties = get_object_vars($this);
        unset($object_properties['products_array']);
        return array_keys($object_properties);
    }

    function add_cart($products_id, $qty = '1', $attributes = '', $notify = true, $gaw_id = 0, $gift_wrap = null) {
        global $new_products_id_in_cart, $customer_id, $messageStack;
        //unset($this->products_array);
        $this->products_array = [];
        $this->total_virtual = 0;
        $this->total = 0;
        $this->weight = 0;
        //$allow_auto_GAW = true;

// {{
        if ((int)$gaw_id > 0) { // product added as GA, check it
            $products_id = \common\helpers\Inventory::get_uprid($products_id, $attributes);
            $products_id = \common\helpers\Inventory::normalize_id($products_id);
            if (\common\helpers\Product::is_giveaway($products_id)) { // only GA can be added as GA
                //if (!isset($this->contents[$products_id . '(GA)'])) { //bug - always true (not set)
                //if (!$this->in_giveaway($products_id, $qty)) {// fix which cause logical problem - can't replace with GAW with dfferent option
                    if (sizeof($this->giveaway) > 0) { // only one GA allowed
                        $this->giveaway = array();
                    }
                    if ($gaw_id == \common\helpers\Gifts::allowedGAW($products_id, $qty)) {
                      $gaw_data = array('qty' => $qty, 'gaw_id' => $gaw_id);
                    } else {
                      $gaw_data = \common\helpers\Gifts::get_max_quantity($products_id);
                    }
                    $this->giveaway[$products_id] = array('qty' => $gaw_data['qty'], 'gaw_id' => $gaw_data['gaw_id']);
                    if (is_array($attributes) && count($attributes)) {
                        $this->giveaway[$products_id]['attributes'] = $attributes;
                    }
                    if ($customer_id > 0) { // update database
                        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id='" . (int) $customer_id . "' and is_giveaway=1");
                        tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added, is_giveaway, gaw_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "', '1', '" . $gaw_data['gaw_id'] . "')");
                        /*
                          if (isset($this->giveaway[$products_id]['attributes']) && is_array($this->giveaway[$products_id]['attributes'])) {
                          foreach($this->giveaway[$products_id]['attributes'] as $option=>$value) {
                          tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input($option) . "', '" . (int)$value . "')");
                          }
                          } */
                    }
                /*} else {
                    if (is_object($messageStack) && method_exists($messageStack, 'add_session')) {
                        $messageStack->add_session('shopping_cart', TEXT_UNABLE_GIVEAWAY, 'warning');
                    }
                }*/
            }
        } else { // ordinary product
// }}
            $packQty = $qty;
            if (is_array($qty)) {
                $qty = $packQty['qty'];
            }
            $qty = intval($qty);
            $products_id = \common\helpers\Inventory::get_uprid($products_id, $attributes);
            $products_id = \common\helpers\Inventory::normalize_id($products_id);
            if ($qty == 0) {
                return $this->remove($products_id);
            }
            if ($notify == true) {
                $new_products_id_in_cart = $products_id;
                tep_session_register('new_products_id_in_cart');
            }

            if ($this->in_cart($products_id) && ((int)$gaw_id == 0)) {
                if (is_null($gift_wrap))
                    $gift_wrap = $this->is_gift_wrapped($products_id);
                $this->update_quantity($products_id, $packQty, $attributes, $gift_wrap);
                //$allow_auto_GAW = false;
            } else {
                $qty = \common\helpers\Product::filter_product_order_quantity($products_id, $qty);

                if (is_null($gift_wrap))
                    $gift_wrap = false;
//      $this->contents[] = array($products_id);
                if (is_array($packQty)) {
                    $this->contents[$products_id] = array('qty' => $qty, 'unit' => $packQty['unit'], 'pack_unit' => $packQty['pack_unit'], 'packaging' => $packQty['packaging'], 'is_pack' => 1);
                } else {
                    $this->contents[$products_id] = array('qty' => $qty);
                }
                if ($gift_wrap && \common\helpers\Gifts::allow_gift_wrap($products_id)) {
                    $this->contents[$products_id]['gift_wrap'] = 1;
                }
                // insert into database
                if (tep_session_is_registered('customer_id')) {
                    if (is_array($packQty)) {
                        tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added, is_pack, unit, pack_unit, packaging) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "', '1', '" . $packQty['unit'] . "', '" . $packQty['pack_unit'] . "', '" . $packQty['packaging'] . "')");
                    } else {
                    tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "')");
                        if (isset($this->contents[$products_id]['gift_wrap'])) {
                            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input('gift_wrap') . "', '" . intval($this->contents[$products_id]['gift_wrap']) . "')");
                        }
                    }
                }

                if (is_array($attributes)) {
                    reset($attributes);
                    while (list($option, $value) = each($attributes)) {
                        $this->contents[$products_id]['attributes'][$option] = $value;
                        // insert into database
                        if (tep_session_is_registered('customer_id'))
                            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input($option) . "', '" . (int) $value . "')");
                    }
                }
            }
// {{
        } // end if ($gaw_id == 1) else
// }}

        $this->cleanup();

        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        $this->update_basket_info();

        if (((int)$gaw_id == 0) /*&& $allow_auto_GAW*/) {
            $this->auto_giveaway();
        }

        $this->update_customer_info();

        $this->check_giveaway();

    }

    public function cart_allow_giftwrap() {

        reset($this->contents);
        while (list($key, $product) = each($this->contents)) {
            if (\common\helpers\Gifts::allow_gift_wrap($key)) {
                return true;
                break;
            }
        }
        return false;
    }

    function update_basket_info() {
        global $customer_id, $languages_id, $currency;

        /*if (\frontend\design\Info::isTotallyAdmin())
            return;*/

        if ($this->language_id != $languages_id)
            $this->language_id = $languages_id;
        if (!$this->currency != $currency)
            $this->currency = $currency;

        if ($this->basketID && $customer_id) {
            tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set basket_id = '" . (int) $this->basketID . "', platform_id = '" . (int) $this->platform_id . "', language_id = '" . (int) $this->language_id . "', currency = '" . tep_db_input($this->currency) . "' where customers_id = '" . (int) $customer_id . "'");
        }
    }
/// Don't use directly!!!
/// use ONLY add_cart instead
/// there could be problem with auto_giveaways.
    function update_quantity($products_id, $quantity = '', $attributes = '', $gift_wrap = false) {
        global $customer_id;

        if (empty($quantity))
            return true; // nothing needs to be updated if theres no quantity, so we return true..
        $packQty = $quantity;
        if (is_array($quantity)) {
            $quantity = $packQty['qty'];
        }
        $quantity = \common\helpers\Product::filter_product_order_quantity($products_id, $quantity);
        $quantity = intval($quantity);
        $reserved_qty = (isset($this->contents[$products_id]['reserved_qty'])? $this->contents[$products_id]['reserved_qty']:0);
        if (is_array($packQty)) {
            $this->contents[$products_id] = array('qty' => $quantity, 'reserved_qty' => $reserved_qty, 'unit' => $packQty['unit'], 'pack_unit' => $packQty['pack_unit'], 'packaging' => $packQty['packaging'], 'is_pack' => 1);
        } else {
            $this->contents[$products_id] = array('qty' => $quantity, 'reserved_qty' => $reserved_qty);
        }

        if ($gift_wrap && \common\helpers\Gifts::allow_gift_wrap($products_id)) {
            $this->contents[$products_id]['gift_wrap'] = 1;
        }
        // update database
        if (tep_session_is_registered('customer_id')) {
            if (is_array($packQty)) {
                tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . $quantity . "', is_pack='1', unit='" . $packQty['unit'] . "', pack_unit='" . $packQty['pack_unit'] . "', packaging='" . $packQty['packaging'] . "' where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
            } else {
                tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . $quantity . "' where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
            }

            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id='" . (int) $customer_id . "' AND products_id='" . tep_db_input($products_id) . "' AND products_options_id='" . tep_db_input('gift_wrap') . "'");
            if (isset($this->contents[$products_id]['gift_wrap'])) {
                tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int) $customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input('gift_wrap') . "', '" . intval($this->contents[$products_id]['gift_wrap']) . "')");
            }
        }

        if (is_array($attributes)) {
            reset($attributes);
            while (list($option, $value) = each($attributes)) {
                $this->contents[$products_id]['attributes'][$option] = $value;
                // update database
                if (tep_session_is_registered('customer_id'))
                    tep_db_query("update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " set products_options_value_id = '" . (int) $value . "' where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "' and products_options_id = '" . tep_db_input($option) . "'");
            }
        }

        // will be done in add_cart, second call cause error message. $this->auto_giveaway();
        // will be done in add_cart $this->check_giveaway();
    }

    function cleanup() {
        global $customer_id;

        reset($this->contents);
        //unset($this->products_array);
        $this->products_array = [];
        $this->total_virtual = 0;
        $this->total = 0;
        $this->weight = 0;

        while (list($key, ) = each($this->contents)) {
// {{
            global $_SESSION, $customer_groups_id, $currency_id;

            $products2c_join = '';
            if ($this->platform_id/* \common\classes\platform::activeId() */) {
                $products2c_join .= " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . $this->platform_id . "' " .
                        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . $this->platform_id . "' ";
            }

            if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
                $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p {$products2c_join} " . "  left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int) $customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : 0) . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " " . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1) and p.products_id = '" . (int) $key . "'");
            } else {
                $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p {$products2c_join} " . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " " . " and p.products_id = '" . (int) $key . "'");
            }
            $product_check = tep_db_fetch_array($product_check_query);
            if ($product_check['total'] < 1) {
                $this->contents[$key]['qty'] = 0;
            }
// }}
            if ($this->contents[$key]['qty'] < 1) {
                unset($this->contents[$key]);
                // remove from database
                if (tep_session_is_registered('customer_id')) {
                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($key) . "'");
                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($key) . "'");
                }
            }
        }
    }

    function count_contents() {  // get total number of items in cart
        $total_items = 0;
        if (is_array($this->contents)) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
                $total_items += $this->get_quantity($products_id);
            }
        }

        // GA
        if (is_array($this->giveaway)) {
            foreach ($this->giveaway as $giveaway) {
                $total_items += $giveaway['qty'];
            }
        }

// {{ Virtual Gift Card
        global $customer_id, $languages_id, $currencies, $currency;
        $virtual_gift_card_query = tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and c.status = 1 and " . ($customer_id > 0 ? " vgcb.customers_id = '" . (int) $customer_id . "'" : " vgcb.session_id = '" . tep_session_id() . "'"));
        while ($virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
            $total_items += 1;
        }
// }}

        return $total_items;
    }

    function get_quantity($products_id, $ga = 0) {
        if ($ga == 1) {
            $products_id = \common\helpers\Inventory::normalize_id($products_id);
            if (isset($this->giveaway[$products_id])) {
                return $this->giveaway[$products_id]['qty'];
            } elseif (isset($this->giveaway[\common\helpers\Inventory::get_prid($products_id)])) {
                return $this->giveaway[\common\helpers\Inventory::get_prid($products_id)]['qty'];
            } else {
                foreach ($this->giveaway as $_ga_uprid => $ga_info) {
                    if (\common\helpers\Inventory::get_prid($products_id) == \common\helpers\Inventory::get_prid($_ga_uprid)) {//\common\helpers\Inventory::get_uprid
                        return $ga_info['qty'];
                    }
                }
                return 0;
            }
        } else {
            if (isset($this->contents[$products_id])) {
                return $this->contents[$products_id]['qty'];
            } else {
                return 0;
            }
        }
    }

    function get_reserved_quantity($products_id, $ga = 0) {
        if ($ga == 1) {
            $products_id = \common\helpers\Inventory::normalize_id($products_id);
            if (isset($this->giveaway[$products_id])) {
                return $this->giveaway[$products_id]['reserved_qty'];
            } elseif (isset($this->giveaway[\common\helpers\Inventory::get_prid($products_id)])) {
                return $this->giveaway[\common\helpers\Inventory::get_prid($products_id)]['reserved_qty'];
            } else {
                foreach ($this->giveaway as $_ga_uprid => $ga_info) {
                    if (\common\helpers\Inventory::get_prid($products_id) == \common\helpers\Inventory::get_prid($_ga_uprid)) {//\common\helpers\Inventory::get_uprid
                        return $ga_info['reserved_qty'];
                    }
                }
                return 0;
            }
        } else {
            if (isset($this->contents[$products_id])) {
                return $this->contents[$products_id]['reserved_qty'];
            } else {
                return 0;
            }
        }
    }

    function in_cart($products_id) {
        if (isset($this->contents[$products_id])) {
            return true;
        } else {
            return false;
        }
    }

    function in_giveaway($products_id, $qty = 0, $gaw_id = false) {
      if (!is_array($this->giveaway)) return false;
      if ($gaw_id === false) {
        // no restriction on attributes/variations, so check by prid, not uprid.
        $ga_pids = array();
        foreach($this->giveaway as $uprid => $data) {
          $ga_pids[\common\helpers\Inventory::get_prid($uprid)] = $data['qty'];
        }
        if (isset($ga_pids[\common\helpers\Inventory::get_prid($products_id)]) ) {
          if ($qty==0 || $ga_pids[\common\helpers\Inventory::get_prid($products_id)]==$qty) {
            return true;
          } else {
            return false;
          }
        } else {
            return false;
        }
      } else {
        if (is_array($this->giveaway)) {
          foreach ($this->giveaway as $gaw) {
            if ($gaw['gaw_id'] == (int)$gaw_id) {
              return true;
            }
          }
        }
        return false;
      }
    }

    function remove($products_id) {
        global $customer_id;

        unset($this->contents[$products_id]);
        if ($this->existOwerwritten($products_id))
            $this->clearOverwriten($products_id);

        // remove from database
        if (tep_session_is_registered('customer_id')) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
        }

// {{ Virtual Gift Card
        if (preg_match("/(\d+)\{0\}(\d+)/", $products_id, $arr)) {
            tep_db_query("delete from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " where length(virtual_gift_card_code) = 0 and virtual_gift_card_basket_id = '" . (int) $arr[2] . "' and products_id = '" . (int) $arr[1] . "' and " . ($customer_id > 0 ? " customers_id = '" . (int) $customer_id . "'" : " session_id = '" . tep_session_id() . "'"));
        }
// }}
        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        //$this->update_basket_info();

        $this->check_giveaway();
    }

    function remove_giveaway($products_id) {
        global $customer_id;
        $products_id = \common\helpers\Inventory::normalize_id($products_id);
        $_remove_uprid = '';
        if (isset($this->giveaway[$products_id])) {
            unset($this->giveaway[$products_id]);
            $_remove_uprid = $products_id;
        } elseif (isset($this->giveaway[\common\helpers\Inventory::get_prid($products_id)])) {
            unset($this->giveaway[\common\helpers\Inventory::get_prid($products_id)]);
            $_remove_uprid = \common\helpers\Inventory::get_prid($products_id);
        } else {
            foreach (array_keys($this->giveaway) as $_ga_uprid) {
                if (\common\helpers\Inventory::get_prid($_ga_uprid) == \common\helpers\Inventory::get_prid($products_id)) {
                    unset($this->giveaway[$_ga_uprid]);
                    $_remove_uprid = $_ga_uprid;
                    break;
                }
            }
        }

        if (!empty($_remove_uprid) && tep_session_is_registered('customer_id')) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($_remove_uprid) . "' and is_giveaway=1");
            //tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
        }
        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        //$this->update_basket_info();
    }

    function remove_all() {
        $this->reset();
    }

    function get_product_id_list() {
        $product_id_list = '';
        if (is_array($this->contents)) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
                $product_id_list .= ', ' . $products_id;
            }
        }

        return substr($product_id_list, 2);
    }

    function calculate() {
        /*
          if ($this->total != 0){
          return;
          }
         */
        $this->total_virtual = 0;
        $this->total = 0;
        $this->weight = 0;
        if (!is_array($this->contents))
            return 0;

        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
            $qty = $this->contents[$products_id]['qty'];

            // products price
            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_price_full, products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
            if ($product = tep_db_fetch_array($product_query)) {
                // ICW ORDER TOTAL CREDIT CLASS Start Amendment
                $no_count = 1;
                $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $gv_result = tep_db_fetch_array($gv_query);
                if (preg_match('/^GIFT/', $gv_result['products_model'])) {
                    $no_count = 0;
                }
                // ICW ORDER TOTAL  CREDIT CLASS End Amendment
                $prid = $product['products_id'];
                $products_tax = \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']);
                $products_price = \common\helpers\Product::get_products_price($product['products_id'], $this->getQty($products_id, false, true), $product['products_price']);

                if (($inventory_weight = \common\helpers\Inventory::get_inventory_weight_by_uprid($products_id)) > 0) {
                    $product['products_weight'] = $inventory_weight;
                }

                if ($product['products_file'] == '') {
// {{ Products Bundle Sets
                    if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getWeight')) {
                        $products_weight = $ext::getWeight($product);
                    } else {
                        $products_weight = $product['products_weight'];
                    }
// }}
                } else {
                    $products_weight = 0;
                }

                $products_price_old = $products_price;
                $special_price = \common\helpers\Product::get_products_special_price($prid, $this->getQty($products_id, false, true));
                if ($special_price && $special_price !== false) {
                    $products_price = $special_price;
                }
                $this->total_virtual += \common\helpers\Tax::add_tax($products_price, $products_tax) * $qty * $no_count; // ICW CREDIT CLASS;
                $this->weight_virtual += ($qty * $products_weight) * $no_count; // ICW CREDIT CLASS;
                //$this->total += \common\helpers\Tax::add_tax($products_price * $qty, $products_tax);
                $this->weight += ($qty * $products_weight);
            }
            
            $attributes_price = 0;
            if (($ext = \common\helpers\Acl::checkExtension('Inventory', 'getAttributesPrice')) && PRODUCTS_INVENTORY == 'True') {
                list($attributes_price, $products_price) = $ext::getAttributesPrice($products_id, $product, $products_price_old, $special_price, $products_price, $this->getQty($products_id, false, true));
            } else {
                if (isset($this->contents[$products_id]['attributes'])) {
                    reset($this->contents[$products_id]['attributes']);
                    while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
// {{ Products Bundle Sets
                        $option_arr = explode('-', $option);
// }}
                        $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $prid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                        $attribute_price = tep_db_fetch_array($attribute_price_query);
                        $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'], $qty);
                        if (tep_not_null($attribute_price['products_attributes_weight'])) {
                            if ($attribute_price['products_attributes_weight_prefix'] == '+' || $attribute_price['products_attributes_weight_prefix'] == '') {
                                $this->weight += $qty * $attribute_price['products_attributes_weight'];
                            } else {
                                $this->weight -= $qty * $attribute_price['products_attributes_weight'];
                            }
                        }
                        if ($attribute_price['price_prefix'] == '+' || $attribute_price['price_prefix'] == '') {
                            $attributes_price += $attribute_price['options_values_price'];
                        } else {
                            $attributes_price -= $attribute_price['options_values_price'];
                        }
                    }
                }
            }
            $this->total += \common\helpers\Tax::add_tax(($products_price + $attributes_price) * $qty, $products_tax);
        }

// {{ Virtual Gift Card
        global $customer_id, $languages_id, $currencies, $currency;
        $virtual_gift_card_query = tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and c.status = 1 and " . ($customer_id > 0 ? " vgcb.customers_id = '" . (int) $customer_id . "'" : " vgcb.session_id = '" . tep_session_id() . "'"));
        while ($virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
            $virtual_gift_card['products_price'] *= $currencies->get_market_price_rate($virtual_gift_card['currency_code'], $currency);
            $this->total_virtual += \common\helpers\Tax::add_tax($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']));
            $this->total += \common\helpers\Tax::add_tax($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']));
        }
// }}
    }

    function have_gift_wrap_products() {
        if (!is_array($this->contents))
            return false;
        foreach (array_unique(array_map('intval', array_keys($this->contents))) as $_check_pid) {
            if (\common\helpers\Gifts::allow_gift_wrap($_check_pid)) {
                return true;
            }
        }
        return false;
    }

    function get_gift_wrap_amount() {
        if (!is_array($this->contents))
            return 0;
        $gift_wrap_amount = 0;
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
            if (!$this->is_gift_wrapped($products_id))
                continue;
            $gift_wrap_price = \common\helpers\Gifts::get_gift_wrap_price($products_id);
            if ($gift_wrap_price !== false)
                $gift_wrap_amount += $gift_wrap_price;
        }
        return $gift_wrap_amount;
    }

    function is_gift_wrapped($products_id) {
        return (isset($this->contents[$products_id]['gift_wrap']) && $this->contents[$products_id]['gift_wrap']);
    }

    function get_products() {
        global $languages_id, $_SESSION;
        global $customer_id, $currencies, $currency;

        $virtual_gift_card_query = tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and c.status = 1 and " . ($customer_id > 0 ? " vgcb.customers_id = '" . (int) $customer_id . "'" : " vgcb.session_id = '" . tep_session_id() . "'"));

        if (!is_array($this->contents))
            return false;

        if (!is_array($this->products_array))
            $this->products_array = [];

        if (array_keys($this->products_array) == array_keys($this->contents) && tep_db_num_rows($virtual_gift_card_query) == 0 && !\frontend\design\Info::isTotallyAdmin()) {
            return array_values($this->products_array);
        }

        $products_array = array();
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
            $products_query = tep_db_query("select p.products_id, p.is_virtual, p.stock_indication_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_price, p.products_price_full, p.products_weight, p.products_tax_class_id, p.products_file, p.subscription, p.subscription_code from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' " . " where p.products_id = '" . (int) $products_id . "' and pd.affiliate_id = 0 and pd.products_id = p.products_id " . " and pd.language_id = '" . (int) $languages_id . "'");
            if ($products = tep_db_fetch_array($products_query)) {
                $prid = $products['products_id'];
                $uprid = \common\helpers\Inventory::normalize_id($products_id);
                $products_price = \common\helpers\Product::get_products_price($products['products_id'], $this->getQty($products_id, false, true) /*$this->contents[$products_id]['qty']*/, $products['products_price']);

                $products_price_old = $products_price;
                $special_price = \common\helpers\Product::get_products_special_price($prid, $this->getQty($products_id, false, true) /*$this->contents[$products_id]['qty']*/);
                if ($special_price && $special_price !== false) {
                    $products_price = $special_price;
                }

                $gift_wrap_allowed = false;
                $gift_wrap_price = \common\helpers\Gifts::get_gift_wrap_price($products_id);
                $gift_wrapped = false;
                if ($gift_wrap_price !== false) {
                    $gift_wrap_allowed = true;
                    $gift_wrapped = $this->is_gift_wrapped($products_id);
                } else {
                    $gift_wrap_price = 0;
                }

                if (($ext = \common\helpers\Acl::checkExtension('Inventory', 'getInventorySettings')) && PRODUCTS_INVENTORY == 'True') {
                    $products = array_replace($products, $ext::getInventorySettings($products_id, $uprid));
                }
                if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getAdditionalWeight')) {
                    $products['products_weight'] =  $ext::getAdditionalWeight($products, $this->platform_id);
                }
                if (($ext = \common\helpers\Acl::checkExtension('Inventory', 'getInventoryStock')) && PRODUCTS_INVENTORY == 'True') {
                    $stock_info = $ext::getInventoryStock($uprid, $products['stock_indication_id'], $this->contents[$products_id]['qty']);
                } else {
                    $stock_info = \common\classes\StockIndication::product_info(array(
                                'products_id' => $products_id,
                                'stock_indication_id' => $products['stock_indication_id'],
                                'cart_qty' => $this->contents[$products_id]['qty'],
                                'cart_class' => true,
                                'products_quantity' => \common\helpers\Product::get_products_stock($products_id),
                    ));
                    $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($products_id, $stock_info['max_qty'], true);
                }

                $attributes_price = 0;
                if (($ext = \common\helpers\Acl::checkExtension('Inventory', 'getAttributesPrice')) && PRODUCTS_INVENTORY == 'True') {
                    list($attributes_price, $products_price) = $ext::getAttributesPrice($products_id, $products, $products_price_old, $special_price, $products_price, $this->getQty($products_id, false, true));
                } else {
                    if (isset($this->contents[$products_id]['attributes'])) {
                        reset($this->contents[$products_id]['attributes']);
                        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
                            $option_arr = explode('-', $option);
                            $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $prid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                            $attribute_price = tep_db_fetch_array($attribute_price_query);
                            $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'], $qty);
                            if (tep_not_null($attribute_price['products_attributes_weight'])) {
                                if ($attribute_price['products_attributes_weight_prefix'] == '+' || $attribute_price['products_attributes_weight_prefix'] == '') {
                                    $this->weight += $qty * $attribute_price['products_attributes_weight'];
                                } else {
                                    $this->weight -= $qty * $attribute_price['products_attributes_weight'];
                                }
                            }
                            if ($attribute_price['price_prefix'] == '+' || $attribute_price['price_prefix'] == '') {
                                $attributes_price += $attribute_price['options_values_price'];
                            } else {
                                $attributes_price -= $attribute_price['options_values_price'];
                            }
                        }
                    }
                }

                $products_array[$products_id] = array(
                    'id' => $products_id,
                    'name' => $products['products_name'],
                    'model' => $products['products_model'],
                    'image' => $products['products_image'],
                    'gift_wrap_allowed' => $gift_wrap_allowed,
                    'gift_wrap_price' => $gift_wrap_price,
                    'gift_wrapped' => $gift_wrapped,
                    'price' => $products_price,
                    'products_file' => $products['products_file'],
                    'ga' => 0,
                    'is_virtual' => (int) $products['is_virtual'],
                    'quantity' => $this->contents[$products_id]['qty'],
                    'reserved_qty' => $this->contents[$products_id]['reserved_qty'],
                    'stock_info' => $stock_info,
                    'weight' => ($products['products_weight'] /* + $this->attributes_weight($products_id) */),
                    'final_price' => ($products_price + $attributes_price /* $this->attributes_price($products_id, $this->contents[$products_id]['qty']) */),
                    'tax_class_id' => $products['products_tax_class_id'],
                    'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''),
                    'overwritten' => $this->getOwerwritten($products_id),
                    'subscription' => $products['subscription'],
                    'subscription_code' => $products['subscription_code'],
                );
                if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'getProductsCartFrontend')) {
                    $products_array[$products_id] = array_replace($products_array[$products_id], $ext::getProductsCartFrontend($products_id, $this->contents));
                }

                if ($this->existOwerwritten($products_id)) {
                    $this->overWrite($products_id, $products_array[$products_id]);
                }
            }
        }

        if (sizeof($this->giveaway) > 0) { // if we also have GA add them too
            foreach ($this->giveaway as $products_id => $product) {
                $products = tep_db_fetch_array(tep_db_query("select p.products_id, p.is_virtual, p.stock_indication_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int) $products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0"));
                // {{ stock info
                /* $stock_info = \common\classes\StockIndication::product_info(array(
                  'products_id' => \common\helpers\Inventory::normalize_id($products_id),
                  'stock_indication_id' => $products['stock_indication_id'],
                  'cart_qty' => $this->contents[$products_id]['qty'],
                  'cart_class' => true,
                  'products_quantity' => \common\helpers\Product::get_products_stock(\common\helpers\Inventory::normalize_id($products_id)),
                  )); */
                // }} stock info
                $products_array[$products_id . '(GA)'] = array('id' => $products_id,
                    'name' => $products['products_name'],
                    'model' => $products['products_model'],
                    'image' => $products['products_image'],
                    //'stock_info' => $stock_info,
                    'gift_wrap_allowed' => false,
                    'gift_wrap_price' => 0,
                    'gift_wrapped' => false,
                    'price' => 0,
                    'ga' => 1,
                    'gaw_id' => $product['gaw_id'],
                    'is_virtual' => (int) $products['is_virtual'],
                    'quantity' => $product['qty'],
                    'weight' => $products['products_weight'],
                    'final_price' => 0,
                    'tax_class_id' => $products['products_tax_class_id'],
                    'attributes' => (isset($product['attributes']) ? $product['attributes'] : ''));
            }
        }

        $this->update_final_price($products_array);

// {{ Virtual Gift Card
        while ($virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
            $virtual_gift_card['source_price'] = $virtual_gift_card['products_price'];
            $virtual_gift_card['products_price'] *= $currencies->get_market_price_rate($virtual_gift_card['currency_code'], $currency);
            $products_id = $virtual_gift_card['products_id'] . '{0}' . $virtual_gift_card['virtual_gift_card_basket_id'];
            $display_gift_card_price = $currencies->display_gift_card_price($virtual_gift_card['source_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']);
            $products_array[$products_id] = array('id' => $products_id,
                'use_shipwire' => 0,
                'name' => $display_gift_card_price . ' - ' . $virtual_gift_card['products_name'],
                'model' => $virtual_gift_card['products_model'],
                'image' => $virtual_gift_card['products_image'],
                'price' => $virtual_gift_card['products_price'],
                'virtual_gift_card' => 1,
                'quantity' => 1,
                'is_virtual' => 1, //???
                'weight' => $virtual_gift_card['products_weight'],
                'final_price' => $virtual_gift_card['products_price'],
                'display_price' => $display_gift_card_price,
                'currency_code' => $virtual_gift_card['currency_code'],
                'tax_class_id' => $virtual_gift_card['products_tax_class_id'],
                'attributes' => array(0 => $virtual_gift_card['virtual_gift_card_basket_id']));
        }
// }}

        $this->products_array = $products_array;

        return array_values($products_array);
    }

    public function get_products_calculated() {
        $this->products_array = [];
        return $this->get_products();
    }

    function update_final_price($products_array) {
        global $customer_id;
        if (is_array($products_array)) {
            foreach ($products_array as $products_id => $cprod) {
                tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set final_price = '" . (float) $cprod['final_price'] . "' where customers_id = '" . (int) $customer_id . "' and basket_id = '" . $this->basketID . "' and products_id = '" . tep_db_input($products_id) . "'");
            }
        }
        return;
    }

    function show_total() {
        $this->calculate();

        return $this->total;
    }

    function show_weight() {
        $this->calculate();

        return $this->weight;
    }

    // CREDIT CLASS Start Amendment
    function show_total_virtual() {
        $this->calculate();

        return $this->total_virtual;
    }

    function show_weight_virtual() {
        $this->calculate();

        return $this->weight_virtual;
    }

    // CREDIT CLASS End Amendment

    function generate_cart_id($length = 5) {
        return \common\helpers\Password::create_random_value($length, 'digits');
    }

    function get_content_type() {
        $this->content_type = false;
        $count_virtual = 0;
        $count_physical = 0;
        foreach ($this->get_products() as $product) {
            if ($product['is_virtual'] != 0) {
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
        return $this->content_type;
    }

    function __get_content_type() {
        $this->content_type = false;

        if ((DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0)) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
                if (isset($this->contents[$products_id]['attributes'])) {

                    $virtual_check_product_query = tep_db_query("select products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                    $virtual_check_product = tep_db_fetch_array($virtual_check_product_query);

                    reset($this->contents[$products_id]['attributes']);
                    while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
// {{ Products Bundle Sets
                        $option_arr = explode('-', $option);
// }}
                        $virtual_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_ATTRIBUTES . "  where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $products_id) . "' and options_values_id = '" . (int) $value . "' and products_attributes_filename <> ''");
                        $virtual_check = tep_db_fetch_array($virtual_check_query);

                        if ($virtual_check['total'] > 0 || $virtual_check_product['products_file'] != '') {
                            switch ($this->content_type) {
                                case 'physical':
                                    $this->content_type = 'mixed';

                                    return $this->content_type;
                                    break;
                                default:
                                    $this->content_type = 'virtual';
                                    break;
                            }
                        } else {
                            switch ($this->content_type) {
                                case 'virtual':
                                    $this->content_type = 'mixed';

                                    return $this->content_type;
                                    break;
                                default:
                                    $this->content_type = 'physical';
                                    break;
                            }
                        }
                    }
                    // ICW ADDED CREDIT CLASS - Begin
                } elseif ($this->show_weight() == 0) {
                    reset($this->contents);
                    while (list($products_id, ) = each($this->contents)) {
                        $virtual_check_query = tep_db_query("select products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                        $virtual_check = tep_db_fetch_array($virtual_check_query);
                        if ($virtual_check['products_file'] != '') {
                            $virtual_check['products_weight'] = 0;
                        }
                        if ($virtual_check['products_weight'] == 0) {
                            switch ($this->content_type) {
                                case 'physical':
                                    $this->content_type = 'mixed';

                                    return $this->content_type;
                                    break;
                                default:
                                    $this->content_type = 'virtual';
                                    break;
                            }
                        } else {
                            switch ($this->content_type) {
                                case 'virtual':
                                    $this->content_type = 'mixed';

                                    return $this->content_type;
                                    break;
                                default:
                                    $this->content_type = 'physical';
                                    break;
                            }
                        }
                    }
                    // ICW ADDED CREDIT CLASS - End
                } else {
                    switch ($this->content_type) {
                        case 'virtual':
                            $this->content_type = 'mixed';
                            return $this->content_type;
                            break;
                        default:
                            $this->content_type = 'physical';
                            break;
                    }
                }
            }
        } elseif ($this->count_contents() > 0) { // GIFT addon by Senia 2008-04-21
            $this->show_weight();
            if (!is_array($this->products_array) || sizeof($this->products_array) == 0) {
                $this->get_products();
            }
            if (is_array($this->products_array) && sizeof($this->products_array) > 0) {
                $total_virtual = 0;
                foreach ($this->products_array as $pdata) {
                    if (preg_match('/^GIFT/', $pdata['model']) || $pdata['virtual_gift_card']) {
                        $total_virtual++;
                    }
                }
            }

            if ($total_virtual > 0 && $total_virtual == sizeof($this->products_array)) {
                $this->content_type = 'virtual';
            } elseif ($total_virtual > 0) {
                $this->content_type = 'mixed';
            } else {
                $this->content_type = 'physical';
            }
        } else {
            $this->content_type = 'physical';
        }

        return $this->content_type;
    }

    function unserialize($broken) {
        for (reset($broken); $kv = each($broken);) {
            $key = $kv['key'];
            if (gettype($this->$key) != "user function")
                $this->$key = $kv['value'];
        }
    }

    // ------------------------ ICW CREDIT CLASS Gift Voucher Addittion-------------------------------Start
    // amend count_contents to show nil contents for shipping
    // as we don't want to quote for 'virtual' item
    // GLOBAL CONSTANTS if NO_COUNT_ZERO_WEIGHT is true then we don't count any product with a weight
    // which is less than or equal to MINIMUM_WEIGHT
    // otherwise we just don't count gift certificates

    function count_contents_virtual() {  // get total number of items in cart disregard gift vouchers
        $total_items = 0;
        if (is_array($this->contents)) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
                $no_count = false;
                $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $gv_result = tep_db_fetch_array($gv_query);
                if (preg_match('/^GIFT/', $gv_result['products_model'])) {
                    $no_count = true;
                }
                if (NO_COUNT_ZERO_WEIGHT == 1) {
                    $gv_query = tep_db_query("select products_weight from " . TABLE_PRODUCTS . " where products_id = '" . \common\helpers\Inventory::get_prid($products_id) . "'");
                    $gv_result = tep_db_fetch_array($gv_query);
                    if ($gv_result['products_weight'] <= MINIMUM_WEIGHT) {
                        $no_count = true;
                    }
                }
                if (!$no_count)
                    $total_items += $this->get_quantity($products_id);
            }
        }
        return $total_items;
    }

    // ------------------------ ICW CREDIT CLASS Gift Voucher Addittion-------------------------------End

    function check_giveaway() {
      global $customer_id;
      if (is_array($this->giveaway)) {
        foreach ($this->giveaway as $products_id => $giveaway) {
          if(!\common\helpers\Gifts::allowedGAW($products_id, $giveaway['qty'])){
            unset($this->giveaway[$products_id]);
            if ($customer_id > 0) {
              tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
              tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
            }
          }
        }
      }
    }

    function is_valid_product_data($product_id, $attributes) {
        if (!is_array($attributes))
            $attributes = array();

        $valid_data = true;

        if (\common\helpers\Attributes::has_product_attributes($product_id)) {
            // include bundle
// {{ Products Bundle Sets
            $bundle_products = array(\common\helpers\Inventory::get_prid($product_id));
            if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getBundles')) {
                $bundle_products = $ext::getBundles($product_id, $this->platform_id);
            }
// }}
            $valid_options = array();
            $attributes_query = tep_db_query(
                    "select products_id, options_id, options_values_id " .
                    "from " . TABLE_PRODUCTS_ATTRIBUTES . " " .
                    "where 1 " .
                    "AND " . (count($bundle_products) > 1 ? " products_id in ('" . implode("','", $bundle_products) . "')" : " products_id = '" . (int) $product_id . "'") . " "
            );
            while ($attr = tep_db_fetch_array($attributes_query)) {
                $look_opt_id = $attr['options_id'];
                if ((int) $attr['products_id'] != (int) $product_id) {
                    $look_opt_id = $attr['options_id'] . '-' . $attr['products_id'];
                }
                if (!isset($valid_options[$look_opt_id]))
                    $valid_options[$look_opt_id] = false;

                if (isset($attributes[$look_opt_id]) && $attributes[$look_opt_id] == $attr['options_values_id']) {
                    $valid_options[$look_opt_id] = true;
                }
            }

            $have_other_options = $attributes;
            foreach ($valid_options as $opt_id => $_present_valid) {
                unset($have_other_options[$opt_id]);
                if (!$_present_valid) {
                    $valid_data = false;
                }
            }

            if (count($have_other_options) > 0) {
                $valid_data = false;
            }
        } elseif (is_array($attributes) && count($attributes) > 0) {
            $valid_data = false;
        }

        return $valid_data;
    }

    function show_discount() {
        global $order, $ot_coupon;
        if (!is_object($ot_coupon)) {
            if (!is_object($order)) {
                $order = new order();
            }
            require_once(DIR_WS_MODULES . 'order_total/ot_coupon.php');
            $ot_coupon = new ot_coupon();
            $ot_coupon->process();
        }
        return $ot_coupon->deduction;
    }

    function auto_giveaway() {
        if (USE_AUTO_GIVEAWAY != 'true') {
            return false;
        }


        $response = \common\helpers\Gifts::getGiveAwaysSQL(false, true, false); // all products, only active , no default sort order
        ///as only 1 gaw in cart the sort order is important.
        $giveaway_query = tep_db_query($response['giveaway_query'] . " order by price, gap.buy_qty desc, products_name ");
        $total = $response['cart_total'];
        if (tep_db_num_rows($giveaway_query) > 0) {

            while ($d = tep_db_fetch_array($giveaway_query)) {
              $best_gaw = \common\helpers\Gifts::get_max_quantity($d['products_id']);
              $this->add_cart($d['products_id'],  $best_gaw ['qty'], '', $best_gaw ['gaw_id'], 1);
              return;
            }
        }
    }

    public function update_customer_info($only_time = true) {
        global $customer_id;
        if ($customer_id) {
            $sql_array = array('time_long' => 'now()');
            if (!$only_time) {
                $sql_array['token'] = 'CT-' . strtoupper(substr(md5(microtime()), 0, 45));
            }
            tep_db_perform(TABLE_CUSTOMERS_INFO, $sql_array, 'update', "customers_info_id = '" . (int) $customer_id . "'");
        }
        if (isset($sql_array['token'])){
            return $sql_array['token'];
        }
    }

    public function setPlatform($platform_id) {
        $this->platform_id = (int) $platform_id;
        return $this;
    }

    public function setCustomer($customer_id) {
        $this->customer_id = (int) $customer_id;
        return $this;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    public function setLanguage($language_id) {
        $this->language_id = (int) $language_id;
        return $this;
    }

    public function setAdmin($admin_id) {
        $this->admin_id = (int) $admin_id;
        return $this;
    }

    public function setBasketID($basket_id) {
        if (!$basket_id) {
            $this->basketID = $this->generate_cart_id();
        } else {
            $this->basketID = (int) $basket_id;
        }
        return $this;
    }

    public function setOverwrite($uprid, $key, $value) {

        if (!\frontend\design\Info::isTotallyAdmin())
            return;

        if (!isset($this->overwrite[$uprid])) {
            $this->overwrite[$uprid] = [];
        }
        $this->overwrite[$uprid][$key] = $value;

        return $this;
    }

    public function existOwerwritten($uprid) {
        if (isset($this->overwrite[$uprid]))
            return true;
        return false;
    }

    public function getOwerwritten($uprid) {
        if (isset($this->overwrite[$uprid]))
            return $this->overwrite[$uprid];
        return false;
    }

    public function getOwerwrittenKey($uprid, $key) {
        if (isset($this->overwrite[$uprid]) && isset($this->overwrite[$uprid][$key]))
            return $this->overwrite[$uprid][$key];
        return false;
    }

    public function clearOverwritenKey($uprid, $key) {
        if (isset($this->overwrite[$uprid]) && isset($this->overwrite[$uprid][$key]))
            unset($this->overwrite[$uprid][$key]);
    }

    public function clearOverwriten($uprid) {
        unset($this->overwrite[$uprid]);
    }

    public function overWrite($uprid, &$product) {
        $details = $this->getOwerwritten($uprid);
        if (is_array($details) && count($details)) {
            foreach ($details as $key => $value) {
                $product[$key] = $value;
            }
        }
    }
    
    public function setAdjusted($value = 0){
        $this->adjusted = (bool)$value;
    }
    
    public function isAdjusted(){
        return $this->adjusted;
    }

    public function getQty($prid, $allVariations = true, $incGAW = false, $priceOnlyGAW = true) {
        if (!is_array($this->contents)) {
          return false;
        }
        $inCartQty = 0;
        if ($allVariations) {
          $prid = \common\helpers\Inventory::get_prid($prid);
        }

        $cartProducts = $this->contents;
        foreach ($cartProducts as $id => $cartProductsValues) {
          if ($allVariations) {
            if ($prid == \common\helpers\Inventory::get_prid($id)) {
              $inCartQty += $cartProductsValues['qty'];
            }
          } else {
            if ($prid == $id) {
              $inCartQty += $cartProductsValues['qty'];
            }
          }
        }

        if ($incGAW) {
          $cartProducts = $this->giveaway;
          if (is_array($cartProducts)) {
            foreach ($cartProducts as $id => $cartProductsValues) {
              if ($allVariations) {
                if ($prid == \common\helpers\Inventory::get_prid($id)) {
                  if (!$priceOnlyGAW || \common\helpers\Gifts::in_qty_discount($cartProductsValues['gaw_id']) ) {
                    $inCartQty += $cartProductsValues['qty'];
                  }
                }
              } else {
                if ($prid == $id) {
                  if (!$priceOnlyGAW || \common\helpers\Gifts::in_qty_discount($cartProductsValues['gaw_id']) ) {
                    $inCartQty += $cartProductsValues['qty'];
                  }
                }
              }
            }
          }
        }
        return $inCartQty;
    }

}
