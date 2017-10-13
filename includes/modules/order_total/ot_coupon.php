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

class ot_coupon extends ModuleTotal {

    var $title, $output;
    protected $config;
    protected $products_in_order;
    protected $valid_products;

    function __construct() {

        $this->code = 'ot_coupon';
        $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
        $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
        $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
        $this->user_prompt = '';
        $this->enabled = (defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true');
        $this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;
        $this->include_shipping = 0;
        $this->include_tax = 0;
        $this->calculate_tax = false;
        $this->tax_class = 0;
        $this->credit_class = true;
        $this->output = array();
        $this->config = array(
            'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
            'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
        );
        $this->products_in_order = [];
        $this->valid_products = [];
    }

    function config($data) {
        if (is_array($data)) {
            $this->config = array_merge($this->config, $data);
        }
    }

    function process($replacing_value = -1, $visible = false) {
        global $PHP_SELF, $order, $currencies, $cart;

        $this->tax_before_calculation = $order->info['tax'];
        $order_total = $this->get_order_total();
        $result = $this->calculate_credit($order_total);
        $od_amount = $result['deduct'];
        $method = $result['method'];

        $tod_amount = 0.0; //Fred

        $this->deduction = $od_amount;

        $tod_amount = $this->calculate_tax_deduction($order_total, $this->deduction);


        if ($replacing_value != -1) {
            if (is_array($replacing_value)) {
                $od_amount = $replacing_value['ex'];
                $tod_amount = $replacing_value['in'] - $replacing_value['ex'];
            }
            $cart->setTotalKey($this->code, $replacing_value);
            if (($_title = $cart->getTotalTitle($this->code)) !== false && empty($this->coupon_code)) {
                preg_match("/\((.*)\)$/", $_title, $ex);
                if (is_array($ex) && isset($ex[1]))
                    $this->coupon_code = $ex[1];
                $order_total = $this->get_order_total();
                $result = $this->calculate_credit($order_total);
                $this->deduction = $od_amount;
                $tod_amount = $this->calculate_tax_deduction($order_total, $this->deduction);
                if (!$this->tax_before_calculation) {
                    $tod_amount = 0;
                }
                if (is_array($replacing_value)) {
                    $replacing_value['ex'] = $od_amount;
                    $replacing_value['in'] = $tod_amount + $od_amount;
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }
        }

        if ($od_amount > 0 || $visible) {

            if (DISPLAY_PRICE_WITH_TAX == 'true') {
                $order->info['total'] = $order->info['total'] - ($od_amount + $tod_amount);
            } else {
                $order->info['total'] = $order->info['total'] - $od_amount;
            }
            $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - ($od_amount + $tod_amount);
            $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $od_amount;
            if ($order->info['total'] < 0) {
                //$order->info['total']=0;
            }
            if ($order->info['total_inc_tax'] < 0) {
                //$order->info['total_inc_tax']=0;
            }
            if ($order->info['total_exc_tax'] < 0) {
                //$order->info['total_exc_tax']=0;
            }

            if (DISPLAY_PRICE_WITH_TAX == 'true') {
                $_od_amount = $od_amount + $tod_amount;
            } else {
                $_od_amount = $od_amount;
            }

            parent::$adjusting -= $currencies->format_clear($od_amount, true, $order->info['currency'], $order->info['currency_value']);
            $this->output[] = array(
                'title' => $this->title . (tep_not_null($this->coupon_code) ? '&nbsp;(' . $this->coupon_code . ')' : '') . ($method == 'free_shipping' ? " " . TEXT_FREE_SHIPPING : ''),
                'text' => '-' . $currencies->format($_od_amount),
                'value' => $_od_amount,
                'text_exc_tax' => '-' . $currencies->format($od_amount),
                'text_inc_tax' => '-' . $currencies->format($od_amount + $tod_amount),
                // {{
                'tax_class_id' => $this->tax_class,
                'value_exc_vat' => $od_amount,
                'value_inc_tax' => $od_amount + $tod_amount,
                    // }}
            ); //Fred added hyphen
        }
    }

    function selection_test() {
        return false;
    }

    function pre_confirmation_check($order_total) {
        global $customer_id;
        $result = $this->calculate_credit($order_total);
        return $result['deduct'];
    }

    function use_credit_amount() {
        return $output_string;
    }

    function credit_selection() {
        global $customer_id, $currencies, $language, $cc_id;
        $selection_string = '';
        $selection_string .= '<tr class="coupon_tr">' . "\n";
        $selection_string .= ' <td class="coupon_title">' . "\n";
        //  $image_submit = '<input type="image" name="submit_redeem" onClick="submitFunction()" src="' . DIR_WS_LANGUAGES . $language . '/images/buttons/button_redeem.gif" border="0" alt="' . IMAGE_REDEEM_VOUCHER . '" title = "' . IMAGE_REDEEM_VOUCHER . '">';
        if ($this->config['ONE_PAGE_CHECKOUT'] != 'True') {
            $image_submit = tep_template_image_submit('button_redeem.' . BUTTON_IMAGE_TYPE, IMAGE_REDEEM_VOUCHER, ' onClick="submitFunction()"  name="submit_redeem" class="transpng"');
        } else {
            if ($this->config['ONE_PAGE_SHOW_TOTALS'] == 'true') {
                $image_submit = tep_template_image_button('button_redeem.' . BUTTON_IMAGE_TYPE, IMAGE_REDEEM_VOUCHER, ' onClick="btnRedeemClick()" name="submit_redeem" class="transpng"');
            } else {
                $image_submit = '&nbsp;';
            }
        }
        $selection_string .= TEXT_ENTER_COUPON_CODE . '</td>';
        $selection_string .= ' <td align="right" class="coupon_value">' . tep_draw_input_field('gv_redeem_code', (($cc_id ? \common\helpers\Coupon::get_coupon_name($cc_id) : ''))) . '</td>';
        $selection_string .= ' <td class="main hiddenTd"' . (($this->config['ONE_PAGE_SHOW_TOTALS'] == 'true') ? ' id="label_coupon"' : '') . '>' . "\n";
        $selection_string .= '&nbsp;</td>';
        $selection_string .= ' <td align="right" class="hiddenTd">' . $image_submit . '</td>';
        $selection_string .= '</tr>' . "\n";

        return $selection_string;
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
                tep_redirect(tep_href_link(basename($_SERVER['PHP_SELF']), 'error_message=' . urlencode($result['message']), 'SSL'));
            }
        }
    }

    function _collect_posts() {
        global $_POST, $customer_id, $currencies, $cc_id, $cart;
        if ($_POST['gv_redeem_code']) {

            // get some info from the coupon table
            $coupon_query = tep_db_query("select coupon_id, coupon_amount, coupon_type, coupon_minimum_order,uses_per_coupon, uses_per_user, restrict_to_products,restrict_to_categories from " . TABLE_COUPONS . " where coupon_code='" . tep_db_input($_POST['gv_redeem_code']) . "' and coupon_active='Y'");
            $coupon_result = tep_db_fetch_array($coupon_query);

            if ($coupon_result['coupon_type'] != 'G') {

                if (tep_db_num_rows($coupon_query) == 0) {
                    return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_COUPON);
                }

                $date_query = tep_db_query("select coupon_start_date from " . TABLE_COUPONS . " where coupon_start_date <= now() and coupon_code='" . tep_db_input($_POST['gv_redeem_code']) . "'");

                if (tep_db_num_rows($date_query) == 0) {
                    return array('error' => true, 'message' => ERROR_INVALID_STARTDATE_COUPON);
                }

                $date_query = tep_db_query("select coupon_expire_date from " . TABLE_COUPONS . " where to_days(coupon_expire_date) >= to_days(now()) and coupon_code='" . tep_db_input($_POST['gv_redeem_code']) . "'");

                if (tep_db_num_rows($date_query) == 0) {
                    return array('error' => true, 'message' => ERROR_INVALID_FINISDATE_COUPON);
                }

                $coupon_count = tep_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . (int) $coupon_result['coupon_id'] . "'");
                $coupon_count_customer = tep_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . (int) $coupon_result['coupon_id'] . "' and customer_id = '" . (int) $customer_id . "'");

                if (tep_db_num_rows($coupon_count) >= $coupon_result['uses_per_coupon'] && $coupon_result['uses_per_coupon'] > 0) {
                    return array('error' => true, 'message' => ERROR_INVALID_USES_COUPON . $coupon_result['uses_per_coupon'] . TIMES);
                }

                if (tep_db_num_rows($coupon_count_customer) >= $coupon_result['uses_per_user'] && $coupon_result['uses_per_user'] > 0) {
                    return array('error' => true, 'message' => ERROR_INVALID_USES_USER_COUPON . $coupon_result['uses_per_user'] . TIMES);
                }

                if ($coupon_result['coupon_type'] == 'S') {
                    $coupon_amount = /* $order->info['shipping_cost'] */ 'Free shipping ';
                } else {
                    //$coupon_amount = $currencies->format($coupon_result['coupon_amount']) . ' ';
                    $coupon_amount = 'Code accepted';
                }
                /* if ($coupon_result['coupon_type']=='P') $coupon_amount = (int)$coupon_result['coupon_amount'] . '% ';
                  if ($coupon_result['coupon_minimum_order']>0) $coupon_amount .= 'on orders greater than ' . $coupon_result['coupon_minimum_order']; */
                if (!tep_session_is_registered('cc_id'))
                    tep_session_register('cc_id'); //Fred - this was commented out before
                $cc_id = $coupon_result['coupon_id']; //Fred ADDED, set the global and session variable
                // $_SESSION['cc_id'] = $coupon_result['coupon_id']; //Fred commented out, do not use $_SESSION[] due to backward comp. Reference the global var instead.
                if (isset($this->config['COUPON_SUCCESS_APPLY']) && $this->config['COUPON_SUCCESS_APPLY'] == 'true') {
                    return array('error' => false, 'message' => $coupon_amount);
                }
                if (defined('ONE_PAGE_CALCULATE') && $this->config['ONE_PAGE_SHOW_TOTALS'] == 'true') {
                    // only if call from opc_ajax.php and SHOW TOTALS ON - check coupon and return status
                    tep_session_unregister('cc_id');
                    return array('error' => false, 'message' => $coupon_amount);
                }
            }
            if ($_POST['submit_redeem_coupon_x'] && !$_POST['gv_redeem_code']) {
                return array('error' => true, 'message' => ERROR_NO_REDEEM_CODE);
            }
        }
    }

    function calculate_credit($order_total) {
        global $customer_id, $order, $cc_id, $currencies, $currency;
        //$cc_id = $_SESSION['cc_id']; //Fred commented out, do not use $_SESSION[] due to backward comp. Reference the global var instead.
        $od_amount = 0;
        $result = [];
        if (isset($cc_id) || tep_not_null($this->coupon_code)) {
            $coupon_query = tep_db_query("select coupon_code, coupon_amount, coupon_currency, coupon_minimum_order, restrict_to_products, restrict_to_categories, coupon_type, flag_with_tax, tax_class_id from " . TABLE_COUPONS . " where coupon_id = '" . (int) $cc_id . "' " . (tep_not_null($this->coupon_code) ? " or coupon_code = '" . tep_db_input($this->coupon_code) . "'" : '' ));
            if (tep_db_num_rows($coupon_query) != 0) {
                $get_result = tep_db_fetch_array($coupon_query);
                $this->coupon_code = $get_result['coupon_code'];
                $this->tax_class = $get_result['tax_class_id'];
                $c_deduct = $get_result['coupon_amount'] * $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);
                $result['method'] = 'standard';
                $get_result['coupon_minimum_order'] *= $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);
                if ($get_result['coupon_type'] == 'S') {
                    $od_amount = $order->info['shipping_cost'];
                    $result['method'] = 'free_shipping';
                } else {
                    if ($get_result['coupon_minimum_order'] <= $order_total) {//$this->get_order_total()
                        if ($get_result['flag_with_tax']) {
                            $tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $c_deduct = \common\helpers\Tax::get_untaxed_value($c_deduct, $tax_rate);
                        }
                        if ($get_result['coupon_type'] != 'P') {
                            $od_amount = $c_deduct;
                        } else {
                            $od_amount = $order_total * $get_result['coupon_amount'] / 100;
                        }
                    }
                }
            }
            if ($od_amount > $order_total && $result['method'] != 'free_shipping')
                $od_amount = $order_total;
        }
        $result['deduct'] = $od_amount;
        return $result;
    }

    function calculate_tax_deduction($amount, $od_amount) {
        global $customer_id, $order, $cc_id, $cart, $shipping;
        //if (!$this->tax_before_calculation) return 0;
        //$cc_id = $_SESSION['cc_id']; //Fred commented out, do not use $_SESSION[] due to backward comp. Reference the global var instead.
        $coupon_query = tep_db_query("select coupon_code, coupon_amount, coupon_minimum_order, restrict_to_products, restrict_to_categories, coupon_type, uses_per_shipping, tax_class_id, flag_with_tax from " . TABLE_COUPONS . " where coupon_id = '" . (int) $cc_id . "' " . (tep_not_null($this->coupon_code) ? " or coupon_code = '" . tep_db_input($this->coupon_code) . "'" : '' ));
        if (tep_db_num_rows($coupon_query) != 0) {
            $get_result = tep_db_fetch_array($coupon_query);
            $this->tax_class = $get_result['tax_class_id'];
            $this->include_shipping = $get_result['uses_per_shipping'];
            global $select_shipping;
            $shipping_tax_class_id = 0;
            if ($this->include_shipping || $get_result['coupon_type'] == 'S') {
                if (is_array($shipping)) {
                    $ex = explode("_", $shipping['id']);
                    if (isset($GLOBALS[$ex[0]])) {
                        $shipping_tax_class_id = $GLOBALS[$ex[0]]->tax_class;
                    }
                } else if ($select_shipping) {
                    $ex = explode("_", $select_shipping);
                    $_sp = new \common\classes\shipping(['id' => $select_shipping]);
                    if (isset($GLOBALS[$ex[0]])) {
                        $shipping_tax_class_id = $GLOBALS[$ex[0]]->tax_class;
                    }
                }
                $taxation = $this->getTaxValues($shipping_tax_class_id, $order);
                $shipping_tax = $taxation['tax'];
                $shipping_tax_desc = $taxation['tax_description'];
            }
            $tod_amount = 0;
            if ($this->tax_class) {
                if (tep_not_null($get_result['restrict_to_categories']) || tep_not_null($get_result['restrict_to_products'])) {
                    $products_in_order = $this->products_in_order;

                    if (is_array($products_in_order) && count($products_in_order)) {
                        $products_in_order = \yii\helpers\ArrayHelper::map($this->products_in_order, 'quantity', 'final_price', 'id');
                        $products_in_order_taxes = \yii\helpers\ArrayHelper::map($this->products_in_order, 'id', 'tax_class_id');
                    } else {
                        $products_in_order = [];
                        $products_in_order_taxes = [];
                    }
                    if (is_array($products_in_order) && count($products_in_order) > 0) {
                        foreach ($products_in_order as $pid => $details) {
                            if (array_key_exists($pid, $this->valid_products)) {
                                list($quantity, $final_price) = each($details);
                                $t = \common\helpers\Tax::get_tax_rate($products_in_order_taxes[$pid], $order->delivery['country']['id'], $order->delivery['zone_id']);
                                $ptod_amount += \common\helpers\Tax::calculate_tax(($quantity * $final_price), $t);
                            }
                        }
                    }

                    if ($get_result['coupon_type'] == 'P') {
                        if ($this->tax_class) {
                            //$tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $taxation = $this->getTaxValues($this->tax_class, $order);
                            $tax_rate = $taxation['tax'];
                            if ($tax_rate) {
                                if ($ptod_amount > 0) {
                                    $tod_amount = $ptod_amount / 100 * $get_result['coupon_amount'];
                                }
                                reset($order->info['tax_groups']);

                                while (list($key, $value) = each($order->info['tax_groups'])) {
                                    $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $tod_amount;
                                }
                                if ($this->include_shipping) {
                                    //$shipping_tax = \common\helpers\Tax::get_tax_rate($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                    //$shipping_tax_desc = \common\helpers\Tax::get_tax_description($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                    $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost_exc_tax'], $shipping_tax);
                                    $god_amount = $shipping_tax_calculated / 100 * $get_result['coupon_amount'];
                                    if ($shipping_tax_calculated) {
                                        if (isset($order->info['tax_groups'][$shipping_tax_desc]))
                                            $order->info['tax_groups'][$shipping_tax_desc] = $order->info['tax_groups'][$shipping_tax_desc] - $god_amount;
                                    }
                                }
                                $order->info['tax'] -= $tod_amount;
                            }
                        }
                    } else {
                        $taxation = $this->getTaxValues($this->tax_class, $order);
                        $tax_rate = $taxation['tax'];
                        $tax_desc = $taxation['tax_description'];

                        //$tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        //$tax_desc = \common\helpers\Tax::get_tax_description($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        if ($get_result['coupon_type'] != 'S') {
                            $tod_amount = $od_amount / 100 * (100 + $tax_rate) - $od_amount; //$od_amount / (100 + $tax_rate) * $tax_rate;
                        }

                        if ($ptod_amount > 0) {
                            $tod_amount = min($tod_amount, $ptod_amount);
                            if ($this->include_shipping || $get_result['coupon_type'] == 'S') {

                                //$shipping_tax = \common\helpers\Tax::get_tax_rate($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                //$shipping_tax_desc = \common\helpers\Tax::get_tax_description($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax);
                                if ($get_result['coupon_type'] == 'S') {
                                    $tod_amount = $shipping_tax_calculated;
                                }
                                $order->info['total_inc_tax'] -= $shipping_tax_calculated;

                                $tod_amount = min($tod_amount, ($order->info['subtotal_inc_tax'] - $order->info['subtotal_exc_tax'] + $shipping_tax_calculated));
                            }
                            if (isset($order->info['tax_groups'][$tax_desc])) {
                                $order->info['tax'] -= $tod_amount;
                                $order->info['tax_groups'][$tax_desc] -= $tod_amount;
                            } else {
                                $tod_amount = 0;
                            }
                        }
                    }
                } else {
                    if ($get_result['coupon_type'] == 'P') {
                        if ($this->tax_class) {
                            //$tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $taxation = $this->getTaxValues($this->tax_class, $order);
                            $tax_rate = $taxation['tax'];
                            if ($tax_rate) {

                                reset($order->info['tax_groups']);

                                while (list($key, $value) = each($order->info['tax_groups'])) {
                                    $god_amount = $value / 100 * $get_result['coupon_amount'];
                                    $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
                                    $tod_amount += $god_amount;
                                }
                                if ($this->include_shipping) {
                                    //$shipping_tax = \common\helpers\Tax::get_tax_rate($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                    //$shipping_tax_desc = \common\helpers\Tax::get_tax_description($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                    $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost_exc_tax'], $shipping_tax);
                                    $god_amount = $shipping_tax_calculated / 100 * $get_result['coupon_amount'];

                                    if ($shipping_tax_calculated) {
                                        if (isset($order->info['tax_groups'][$shipping_tax_desc]))
                                            $order->info['tax_groups'][$shipping_tax_desc] = $order->info['tax_groups'][$shipping_tax_desc] - $god_amount;
                                    }
                                    $tod_amount += $god_amount;
                                }
                                $order->info['tax'] -= $tod_amount;
                            }
                        }
                    } else { //F or S
                        //$tax_rate = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        //$tax_desc = \common\helpers\Tax::get_tax_description($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        $taxation = $this->getTaxValues($this->tax_class, $order);
                        $tax_rate = $taxation['tax'];
                        $tax_desc = $taxation['tax_description'];
                        $tod_amount = \common\helpers\Tax::calculate_tax($od_amount, $tax_rate);

                        if ($get_result['coupon_type'] != 'S') {
                            //$tod_amount = $od_amount / 100  * (100 + $tax_rate) - $od_amount;//$od_amount / (100 + $tax_rate) * $tax_rate;
                        }
                        $shipping_tax_calculated = 0;
                        if ($this->include_shipping || $get_result['coupon_type'] == 'S') {
                            //$shipping_tax = \common\helpers\Tax::get_tax_rate($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            //$shipping_tax_desc = \common\helpers\Tax::get_tax_description($shipping_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax);

                            if ($get_result['coupon_type'] == 'S') {
                                $tod_amount = $shipping_tax_calculated;
                            } else {
                                $tod_amount = min($tod_amount, (float) $order->info['tax_groups'][$tax_desc] + $shipping_tax_calculated);
                            }
                            //$tod_amount = min($tod_amount, ($order->info['subtotal_inc_tax'] - $order->info['subtotal_exc_tax'] + $shipping_tax_calculated));
                        } else {
                            $tod_amount = min($tod_amount, (float) $order->info['tax_groups'][$tax_desc]);
                        }

                        //if (isset($order->info['tax_groups'][$tax_desc])){
                        $order->info['tax'] -= $tod_amount;
                        $order->info['tax_groups'][$tax_desc] -= $tod_amount;
                        //} 
                    }
                }
            }
        }
        return $tod_amount;
    }

    function update_credit_account($i) {
        return false;
    }

    function apply_credit() {
        global $insert_id, $customer_id, $cc_id;
        //$cc_id = $_SESSION['cc_id']; //Fred commented out, do not use $_SESSION[] due to backward comp. Reference the global var instead.
        if ($this->deduction != 0 && $cc_id) {
            tep_db_query("insert into " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, redeem_date, redeem_ip, customer_id, order_id) values ('" . (int) $cc_id . "', now(), '" . tep_db_input(\common\helpers\System::get_ip_address()) . "', '" . (int) $customer_id . "', '" . (int) $insert_id . "')");
        }
        tep_session_unregister('cc_id');
    }

    function get_order_total() {
        global $order, $cart, $customer_id, $cc_id;
        //$cc_id = $_SESSION['cc_id']; //Fred commented out, do not use $_SESSION[] due to backward comp. Reference the global var instead.
        $order_total = 0;

        // OK thats fine for global coupons but what about restricted coupons
        // where you can only redeem against certain products/categories.
        // and I though this was going to be easy !!!
        $coupon_query = tep_db_query("select coupon_code, uses_per_shipping,restrict_to_products,restrict_to_categories from " . TABLE_COUPONS . " where coupon_id='" . (int) $cc_id . "' " . (tep_not_null($this->coupon_code) ? " or coupon_code = '" . tep_db_input($this->coupon_code) . "'" : '' ));
        if (tep_db_num_rows($coupon_query) != 0) {
            $get_result = tep_db_fetch_array($coupon_query);

            $order_total = $order->info['subtotal_exc_tax'];
            if ($get_result['uses_per_shipping'])
                $order_total += $order->info['shipping_cost'];

            $products_in_order = $cart->get_products_calculated();
            $this->products_in_order = $products_in_order;
            if (is_array($products_in_order) && count($products_in_order)) {
                $products_in_order = \yii\helpers\ArrayHelper::map($products_in_order, 'quantity', 'final_price', 'id');
            } else {
                $products_in_order = [];
            }
            if (count($products_in_order)) {
                if (tep_not_null($get_result['restrict_to_categories']) || tep_not_null($get_result['restrict_to_products'])) {
                    $total = 0;
                    if ($get_result['restrict_to_categories']) {
                        $cat_ids = explode(",", $get_result['restrict_to_categories']);
                        for ($i = 0; $i < count($cat_ids); $i++) {
                            reset($products_in_order);
                            while (list($products_id, $details) = each($products_in_order)) {
                                $cat_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $cat_ids[$i] . "'");
                                if (tep_db_num_rows($cat_query) != 0) {
                                    list($quantity, $final_price) = each($details);
                                    $total += ($final_price * $quantity);
                                    $this->valid_products[$products_id] = $products_in_order[$products_id];
                                }
                            }
                        }
                    }
                    if ($get_result['restrict_to_products']) {
                        $pr_ids = explode(",", $get_result['restrict_to_products']);

                        foreach ($products_in_order as $pid => $details) {
                            list($quantity, $final_price) = each($details);
                            if (in_array(\common\helpers\Inventory::get_prid($pid), $pr_ids)) {
                                $total += ($final_price * $quantity);
                                $this->valid_products[$pid] = $products_in_order[$pid];
                            }
                        }
                    }
                    $order_total = $total;
                }
            }
        }
        return $order_total;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_COUPON_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the Discount Coupon value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '9',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}
