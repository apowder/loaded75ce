<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_gv extends ModuleTotal {

    var $title, $output;
    protected $config;

    function __construct() {
        $this->code = 'ot_gv';
        $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
        $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
        $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
        $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
        $this->enabled = (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true');
        $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
        $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
        $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
        $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
        $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
        $this->tax_class = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
        $this->show_redeem_box = MODULE_ORDER_TOTAL_GV_REDEEM_BOX;
        $this->credit_class = true;
        $this->checkbox = '<input type="checkbox" onClick="submitFunction()" name="' . 'c' . $this->code . '">';
        $this->output = array();
        $this->config = array(
            'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
            'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
        );
    }

    function getIncVATTitle() {
        return '';
    }

    function getIncVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function getExcVATTitle() {
        return '';
    }

    function getExcVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function config($data) {
        if (is_array($data)) {
            $this->config = array_merge($this->config, $data);
        }
    }

    function process($replacing_value = -1, $visible = false) {
        global $order, $currencies, $cot_gv, $cart;
//      if ($_SESSION['cot_gv']) {  // old code Strider

        if ((tep_session_is_registered('cot_gv') && $cot_gv) || $replacing_value != -1 || $visible) {
            $tod_amount = 0;
            $order_total = $this->get_order_total();
            $od_amount = $this->calculate_credit($order_total);
            if ($this->calculate_tax != "None") {
                $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
                $od_amount = $this->calculate_credit($order_total);
            }
            if (is_numeric($cot_gv)) {
                $od_amount = min($od_amount, $cot_gv);
            }
            $this->deduction = $od_amount;

            if ($replacing_value != -1) {
                if (is_array($replacing_value)) {
                    $od_amount = $replacing_value['ex'];
                    $tod_amount = $replacing_value['in'] - $replacing_value['ex'];
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }

            $order->info['total'] = $order->info['total'] - $od_amount - $tod_amount;
            $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - $od_amount - $tod_amount;
            $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $od_amount;

            if ($order->info['total'] < 0) {
                $order->info['total'] = 0;
            }
            if ($order->info['total_inc_tax'] < 0) {
                $order->info['total_inc_tax'] = 0;
            }
            if ($order->info['total_exc_tax'] < 0) {
                $order->info['total_exc_tax'] = 0;
            }

            if (abs($od_amount) > 0 || $visible) {

                parent::$adjusting -= $currencies->format_clear($od_amount, true, $order->info['currency'], $order->info['currency_value']);

                $this->output[] = array('title' => $this->title . ':',
                    'text' => '-' . $currencies->format($od_amount),
                    'value' => $od_amount,
                    'text_exc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount)),
                    'text_inc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount + $tod_amount)),
// {{
                    'tax_class_id' => $this->tax_class,
                    'value_exc_vat' => $od_amount,
                    'value_inc_tax' => $od_amount + $tod_amount,
// }}
                );
            }
        }
    }

    function selection_test() {
        global $customer_id;
        if ($this->user_has_gv_account($customer_id)) {
            return true;
        } else {
            return false;
        }
    }

    function pre_confirmation_check($order_total) {
        global $cot_gv, $order;
//    if ($_SESSION['cot_gv']) {  // old code Strider
        $od_amount = 0; // set the default amount we will send back
        if (tep_session_is_registered('cot_gv')) {
// pre confirmation check doesn't do a true order process. It just attempts to see if
// there is enough to handle the order. But depending on settings it will not be shown
// all of the order so this is why we do this runaround jane. What do we know so far.
// nothing. Since we need to know if we process the full amount we need to call get order total
// if there has been something before us then

            if ($this->include_tax == 'false') {
                $order_total = $order_total - $order->info['tax'];
            }
            if ($this->include_shipping == 'false') {
                $order_total = $order_total - $order->info['shipping_cost'];
            }
            $od_amount = $this->calculate_credit($order_total);


            if ($this->calculate_tax != "None") {
                $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
                $od_amount = $this->calculate_credit($order_total) + $tod_amount;
            }
        }
        return $od_amount;
    }

    // original code
    /* function pre_confirmation_check($order_total) {
      if ($SESSION['cot_gv']) {
      $gv_payment_amount = $this->calculate_credit($order_total);
      }
      return $gv_payment_amount;
      } */

    function use_credit_amount() {
        global $cot_gv;
//      $_SESSION['cot_gv'] = false;     // old code - Strider
        $cot_gv = false;
        if ($this->selection_test()) {
            $output_string .= '    <td width="50%" class="main" nowrap>';
            $output_string .= '<b>' . $this->user_prompt . '</b>' . '</td>' . "\n";
            $output_string .= '    <td width="50%" align="right" class="main">';
            $output_string .= '<b>' . $this->checkbox . '</b>' . '</td>' . "\n";
        }
        return $output_string;
    }

    function update_credit_account($i) {
        /* handled in apply credit */
        return;

        global $order, $customer_id, $insert_id, $REMOTE_ADDR, $currencies, $currency;
        if (preg_match('/^GIFT/', addslashes($order->products[$i]['model']))) {
            $gv_order_amount = ($order->products[$i]['final_price'] * $order->products[$i]['qty']);
            if ($this->credit_tax == 'true')
                $gv_order_amount = $gv_order_amount * (100 + $order->products[$i]['tax']) / 100;
//        $gv_order_amount += 0.001;
            $gv_order_amount = $gv_order_amount * 100 / 100;

            $cv_currency = DEFAULT_CURRENCY;
            $key_values = explode(", ", MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID);
            if (in_array($order->info['order_status'], $key_values)) {
                if (MODULE_ORDER_TOTAL_GV_QUEUE == 'false') {
                    // GV_QUEUE is true so release amount to account immediately
                    $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $_SESSION['customer_id'] . "'");
                    $total_gv_amount = 0;
                    if ($gv_result = tep_db_fetch_array($gv_query)) {
                        $total_gv_amount = $gv_result['amount'];
                        $cv_currency = DEFAULT_CURRENCY;
                    }
                    $total_gv_amount = $total_gv_amount + $gv_order_amount /** $currencies->get_market_price_rate($currency, $cv_currency) */;
                    tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . tep_db_input($total_gv_amount) . "' where customers_id = '" . (int) $customer_id . "'");
                    tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
                        'customers_id' => $customer_id,
                        'credit_prefix' => '+',
                        'credit_amount' => $gv_order_amount,
                        'currency' => DEFAULT_CURRENCY,
                        'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
                        'customer_notified' => 0,
                        'comments' => 'Order ' . $order->products[$i]['model'] . ' order #' . $insert_id,
                        'date_added' => 'now()',
                        'admin_id' => 0,
                    ));
                } else {
                    // GV_QUEUE is true - so queue the gv for release by store owner
                    $gv_insert = tep_db_query("insert into " . TABLE_COUPON_GV_QUEUE . " (customer_id, order_id, amount, currency, date_created, ipaddr) values ('" . (int) $customer_id . "', '" . (int) $insert_id . "', '" . tep_db_input($gv_order_amount) . "', '" . tep_db_input($cv_currency) . "', NOW(), '" . tep_db_input($REMOTE_ADDR) . "')");
                }
                tep_db_query("update " . TABLE_ORDERS . " set gv = '1' where orders_id = '" . (int) $insert_id . "'");
            }
        }
    }

    function credit_selection() {
        global $customer_id, $currencies, $language;
        $selection_string = '';
        $gv_query = tep_db_query("select coupon_id from " . TABLE_COUPONS . " where coupon_type = 'G' and coupon_active='Y'");
        if (tep_db_num_rows($gv_query)) {
            $selection_string .= '<tr>' . "\n";
            $selection_string .= '  <td>' . "\n";
//        $image_submit = '<input type="image" name="submit_redeem" onClick="submitFunction()" src="' . DIR_WS_LANGUAGES . $language . '/images/buttons/button_redeem.gif" border="0" alt="' . IMAGE_REDEEM_VOUCHER . '" title = "' . IMAGE_REDEEM_VOUCHER . '">';
            $image_submit = tep_template_image_submit('button_redeem.' . BUTTON_IMAGE_TYPE, IMAGE_REDEEM_VOUCHER, ' onClick="submitFunction()" class="transpng"  name="submit_redeem" ');

            $selection_string .= TEXT_ENTER_GV_CODE . tep_draw_input_field('gv_redeem_code') . '</td>';
            $selection_string .= ' <td align="right">' . $image_submit . '</td>';
            $selection_string .= '</tr>' . "\n";
        }
        return $selection_string;
    }

    function apply_credit() {
        global $order, $insert_id, $customer_id, $coupon_no, $cot_gv, $currencies, $currency;
        if (tep_session_is_registered('cot_gv')) {
            $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
            $gv_result = tep_db_fetch_array($gv_query);
            $gv_payment_amount = $this->deduction;
            $gv_amount = $gv_result['amount'] - $gv_payment_amount/*             * $currencies->get_market_price_rate($currency, $gv_result['currency']) */;
            tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . tep_db_input($gv_amount) . "' where customers_id = '" . (int) $customer_id . "'");
            tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
                'customers_id' => $customer_id,
                'credit_prefix' => '-',
                'credit_amount' => $gv_payment_amount,
                'currency' => DEFAULT_CURRENCY,
                'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
                'customer_notified' => 0,
                'comments' => 'Order #' . $insert_id,
                'date_added' => 'now()',
                'admin_id' => 0,
            ));
        }
        if (function_exists('tl_credit_order_check_state')) {
            tl_credit_order_check_state($insert_id);
        }

        return $gv_payment_amount;
    }

    function collect_posts() {
        global $messageStack, $error, $opc_coupon_pool;
        $result = $this->_collect_posts();
        if (is_array($result)) {
            if ($this->config['ONE_PAGE_CHECKOUT'] == 'True') {
                if ($this->config['ONE_PAGE_SHOW_TOTALS'] == 'true') {
                    if (!strstr($opc_coupon_pool['message'], ERROR_REDEEMED_AMOUNT))
                        $opc_coupon_pool = $result;
                } else {
                    $messageStack->add('one_page_checkout', $result['message'], ($result['error'] ? 'error' : 'success'));
                    $error = true;
                }
                if ($result['error']) {
                    $error = true;
                }
                return;
            } else {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, \common\helpers\Output::get_all_get_params(array('error_message')) . 'error_message=' . urlencode($result['message']), 'SSL'));
            }
        }
    }

    function _collect_posts() {
        global $currencies, $_POST, $customer_id, $coupon_no, $REMOTE_ADDR, $currency;
        if ($_POST['gv_redeem_code']) {
            $gv_query = tep_db_query("select coupon_id, coupon_code, coupon_type, coupon_amount, coupon_currency from " . TABLE_COUPONS . " where coupon_code = '" . tep_db_input($_POST['gv_redeem_code']) . "'");
            $gv_result = tep_db_fetch_array($gv_query);
            if (tep_db_num_rows($gv_query) != 0) {
                $redeem_query = tep_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . (int) $gv_result['coupon_id'] . "'");
                if ((tep_db_num_rows($redeem_query) != 0) && ($gv_result['coupon_type'] == 'G')) {
                    return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
                }
            } else {
                if (isset($this->config['GV_SOLO_APPLY']) && $this->config['GV_SOLO_APPLY'] == 'true') {
                    return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
                }
            }
            if ($gv_result['coupon_type'] == 'G') {
                if (/* $this->config['ONE_PAGE_SHOW_TOTALS']=='true' && */!tep_session_is_registered('customer_id')) {
                    global $gv_id;
                    $gv_id = $gv_result['coupon_id'];
                    tep_session_register('gv_id');
                    return array('error' => false, 'message' => ERROR_NO_CUSTOMER);
                }
                $gv_amount = $gv_result['coupon_amount'];
                $gv_currency = $gv_result['coupon_currency'];
                // Things to set
                // ip address of claimant
                // customer id of claimant
                // date
                // redemption flag
                // now update customer account with gv_amount
                $gv_amount_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
                $customer_gv = false;
                $total_gv_amount = $gv_amount;
                $_gv_amount = $gv_amount;
                $gv_amount = ($gv_amount * $currencies->get_market_price_rate($gv_currency, DEFAULT_CURRENCY));
                if ($gv_amount_result = tep_db_fetch_array($gv_amount_query)) {
                    $total_gv_amount = $gv_amount_result['amount'] + $gv_amount /*                     * $currencies->get_market_price_rate($gv_currency, $gv_amount_result['currency']) */;
                    $customer_gv = true;
                }
                $gv_update = tep_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . (int) $gv_result['coupon_id'] . "'");
                $gv_redeem = tep_db_query("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . (int) $gv_result['coupon_id'] . "', '" . (int) $customer_id . "', now(),'" . tep_db_input($REMOTE_ADDR) . "')");
                tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . tep_db_input($total_gv_amount) . "' where customers_id = '" . (int) $customer_id . "'");
                tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
                    'customers_id' => $customer_id,
                    'credit_prefix' => '+',
                    'credit_amount' => $gv_amount,
                    'currency' => $gv_currency,
                    'currency_value' => $currencies->currencies[$gv_currency]['value'],
                    'customer_notified' => 0,
                    'comments' => 'Redeem ' . $gv_result['coupon_code'],
                    'date_added' => 'now()',
                    'admin_id' => 0,
                ));
                unset($_POST['gv_redeem_code']);
                unset($_POST['gv_redeem_code']);
                return array('error' => false, 'message' => ERROR_REDEEMED_AMOUNT . $currencies->format($_gv_amount, false, $gv_currency));
            }
        }
        if ($_POST['submit_redeem_x'] && $gv_result['coupon_type'] == 'G') {
            return array('error' => true, 'message' => ERROR_NO_REDEEM_CODE);
        }
        if (isset($_SESSION['cot_gv']) && isset($_POST['cot_gv_amount']) && is_numeric($_POST['cot_gv_amount'])) {
            if (isset($_SESSION['customer_id']) && $this->user_has_gv_account($_SESSION['customer_id'])) {
                $new_gv_amount = number_format(($_POST['cot_gv_amount'] * $currencies->get_market_price_rate($currency, DEFAULT_CURRENCY)), 2, '.', '');
                if ($new_gv_amount > 0) {
                    $_SESSION['cot_gv'] = $new_gv_amount;
                }
            }
        }
    }

    function calculate_credit($amount) {
        global $customer_id, $order, $currencies, $currency;
        $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
        $gv_result = tep_db_fetch_array($gv_query);
        $gv_payment_amount = $gv_result['amount']/*         * $currencies->get_market_price_rate($gv_result['currency'], $currency) */;
//      $gv_amount = $gv_payment_amount;
        $save_total_cost = $amount;
        $full_cost = $save_total_cost - $gv_payment_amount;
        if ($full_cost <= 0) {
            $full_cost = 0;
            $gv_payment_amount = $save_total_cost;
        }
        return round($gv_payment_amount, 2);
    }

    function calculate_tax_deduction($amount, $od_amount, $method) {
        global $order;
        switch ($method) {
            case 'Standard':
                $ratio1 = round($od_amount / $amount, 2);
                $tod_amount = 0;
                reset($order->info['tax_groups']);
                while (list($key, $value) = each($order->info['tax_groups'])) {
                    $tax_rate = \common\helpers\Tax::get_tax_rate_from_desc($key);
                    $total_net += $tax_rate * $order->info['tax_groups'][$key];
                }
                if ($od_amount > $total_net)
                    $od_amount = $total_net;
                reset($order->info['tax_groups']);
                while (list($key, $value) = each($order->info['tax_groups'])) {
                    $tax_rate = \common\helpers\Tax::get_tax_rate_from_desc($key);
                    $net = $tax_rate * $order->info['tax_groups'][$key];
                    if ($net > 0) {
                        $god_amount = $order->info['tax_groups'][$key] * $ratio1;
                        $tod_amount += $god_amount;
                        $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
                    }
                }
                $order->info['tax'] -= $tod_amount;
                $order->info['total'] -= $tod_amount;
                break;
            case 'Credit Note':
                //$tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$tax_desc = \common\helpers\Tax::get_tax_description($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                $taxation = $this->getTaxValues($this->tax_class, $order);
                $tax_rate = $taxation['tax'];
                $tax_desc = $taxation['tax_description'];
                $tod_amount = $this->deduction / (100 + $tax_rate) * $tax_rate;
                $order->info['tax_groups'][$tax_desc] -= $tod_amount;
//          $order->info['total'] -= $tod_amount;   //// ????? Strider
                break;
            default:
        }
        return $tod_amount;
    }

    function user_has_gv_account($c_id) {
        $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $c_id . "'");
        if ($gv_result = tep_db_fetch_array($gv_query)) {
            if ($gv_result['amount'] > 0) {
                return true;
            }
        }
        return false;
    }

    function get_order_total() {
        global $order;
        $order_total = $order->info['total_inc_tax'];
        if ($this->include_tax == 'false')
            $order_total = $order->info['total_exc_tax']; //$order_total - $order->info['tax'];
        if ($this->include_shipping == 'false') {
            if ($this->include_tax == 'false') {
                $order_total = $order_total - $order->info['shipping_cost_exc_tax'];
            } else {
                $order_total = $order_total - $order->info['shipping_cost_inc_tax'];
            }
        }


        return $order_total;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_GV_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_GV_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_GV_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the Gift Voucher value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '740',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_GV_QUEUE' =>
            array(
                'title' => 'Queue Purchases',
                'value' => 'true',
                'description' => 'Do you want to queue purchases of the Gift Voucher?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_INC_SHIPPING' =>
            array(
                'title' => 'Include Shipping',
                'value' => 'true',
                'description' => 'Include Shipping in calculation',
                'sort_order' => '5',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_INC_TAX' =>
            array(
                'title' => 'Include Tax',
                'value' => 'true',
                'description' => 'Include Tax in calculation.',
                'sort_order' => '6',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_CALC_TAX' =>
            array(
                'title' => 'Re-calculate Tax',
                'value' => 'None',
                'description' => 'Re-Calculate Tax',
                'sort_order' => '7',
                'set_function' => 'tep_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class when treating Gift Voucher as Credit Note.',
                'sort_order' => '0',
                'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
            'MODULE_ORDER_TOTAL_GV_CREDIT_TAX' =>
            array(
                'title' => 'Credit including Tax',
                'value' => 'false',
                'description' => 'Add tax to purchased Gift Voucher when crediting to Account',
                'sort_order' => '8',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID' =>
            array(
                'title' => 'Allowed Order Statuses',
                'value' => '2, 3, 4',
                'description' => 'Select the status of orders to be processed.',
                'sort_order' => '13',
                'set_function' => 'tep_cfg_select_multioption_order_statuses(',
            ),
            'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID_COVERS' => array(
                'title' => 'Set status for covered order',
                'value' => 0,
                'description' => 'Covered order',
                'sort_order' => '0',
                'set_function' => 'tep_cfg_pull_down_order_statuses(',
                'use_function' => '\\\\common\\\\helpers\\\\Order::get_order_status_name',
            ),
        );
    }

}
