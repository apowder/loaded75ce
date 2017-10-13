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

class ot_shipping extends ModuleTotal {

    var $title, $output;

    function __construct() {
        $this->code = 'ot_shipping';
        $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
        $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;

        $this->output = array();
    }

    function process($replacing_value = -1, $visible = false) {
        global $order, $currencies, $cart;

        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($order->delivery['country_id'] == STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'international':
                    if ($order->delivery['country_id'] != STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'both':
                    $pass = true;
                    break;
                default:
                    $pass = false;
                    break;
            }

            if ((($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER))) {

                $order->info['shipping_method'] = $this->title;
                $order->info['total'] -= $order->info['shipping_cost'];
                $order->info['total_inc_tax'] -= $order->info['shipping_cost'];
                $order->info['total_exc_tax'] -= $order->info['shipping_cost'];
                $order->info['shipping_cost'] = 0;
                $order->info['shipping_cost_exc_tax'] = 0;
                $order->info['shipping_cost_inc_tax'] = 0;
            }
        }

        $module = false;
        if (defined('ONE_PAGE_SHIPPING_VAR') && defined('ONE_PAGE_CALCULATE')) {
            //$module = substr((isset($GLOBALS[ONE_PAGE_SHIPPING_VAR]['id'])?$GLOBALS[ONE_PAGE_SHIPPING_VAR]['id']:""), 0, strpos((isset($GLOBALS[ONE_PAGE_SHIPPING_VAR]['id'])?$GLOBALS[ONE_PAGE_SHIPPING_VAR]['id']:""), '_'));
            if (isset($GLOBALS[ONE_PAGE_SHIPPING_VAR]) && is_array($GLOBALS[ONE_PAGE_SHIPPING_VAR])) {
                $module = substr($GLOBALS[ONE_PAGE_SHIPPING_VAR]['id'], 0, strpos($GLOBALS[ONE_PAGE_SHIPPING_VAR]['id'], '_'));
            }
        } else {
            //$module = substr( (isset($GLOBALS['shipping']['id'])?$GLOBALS['shipping']['id']:""), 0, strpos( (isset($GLOBALS['shipping']['id'])?$GLOBALS['shipping']['id']:""), '_'));
            if (isset($GLOBALS['shipping']) && is_array($GLOBALS['shipping'])) {
                $module = substr($GLOBALS['shipping']['id'], 0, strpos($GLOBALS['shipping']['id'], '_'));
            } elseif (isset($GLOBALS['select_shipping'])) {
                $module = substr($GLOBALS['select_shipping'], 0, strpos($GLOBALS['select_shipping'], '_'));
            }
        }


        $shipping_tax_calculated = 0;
        if (tep_not_null($order->info['shipping_method'])) {
            $tax_class = 0;
            if ($module && is_object($GLOBALS[$module]) && $GLOBALS[$module]->tax_class > 0) {
                //$tax_class = $GLOBALS[$module]->tax_class;
                //$shipping_tax = \common\helpers\Tax::get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$shipping_tax_description = \common\helpers\Tax::get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax);

                $taxation = $this->getTaxValues($GLOBALS[$module]->tax_class, $order);

                $tax_class = $taxation['tax_class_id'];
                $shipping_tax = $taxation['tax'];
                $shipping_tax_description = $taxation['tax_description'];
                $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax);

                $order->info['tax'] += $shipping_tax_calculated;
                //$order->info['tax_shipping'] = $shipping_tax_calculated;
                $order->info['tax_groups']["$shipping_tax_description"] += $shipping_tax_calculated;
                //$order->info['tax_groups_shipping']["$shipping_tax_description"] += $shipping_tax_calculated;
                $order->info['total'] += $shipping_tax_calculated;
                $order->info['total_inc_tax'] += $shipping_tax_calculated;

                $order->info['shipping_cost_exc_tax'] = $order->info['shipping_cost'];
                $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost'] + $shipping_tax_calculated;

                if (DISPLAY_PRICE_WITH_TAX == 'true')
                    $order->info['shipping_cost'] += $shipping_tax_calculated;
            }
            if ($replacing_value != -1) {
                $order->info['total_inc_tax'] -= $order->info['shipping_cost_inc_tax'];
                $order->info['total_exc_tax'] -= $order->info['shipping_cost_exc_tax'];
                if (is_array($replacing_value)) {
                    $order->info['shipping_cost_exc_tax'] = $replacing_value['ex'];
                    $order->info['shipping_cost_inc_tax'] = $replacing_value['in'];
                } else {
                    $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost_exc_tax'] = $replacing_value;
                }
                $order->info['tax'] = $order->info['tax'] - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                $order->info['tax_groups']["$shipping_tax_description"] = $order->info['tax_groups']["$shipping_tax_description"] - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                $order->info['total'] = $order->info['total'] - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                $order->info['total_inc_tax'] += $order->info['shipping_cost_inc_tax'];
                $order->info['total_exc_tax'] += $order->info['shipping_cost_exc_tax'];

                $cart->setTotalKey($this->code, $replacing_value);
            }

            parent::$adjusting += $currencies->format_clear($order->info['shipping_cost_exc_tax'], true, $order->info['currency'], $order->info['currency_value']);

            $this->output[] = array('title' => $order->info['shipping_method'] . ':',
                'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['shipping_cost'],
                'text_exc_tax' => $currencies->format($order->info['shipping_cost_exc_tax'], true, $order->info['currency'], $order->info['currency_value']),
                'text_inc_tax' => $currencies->format($order->info['shipping_cost_inc_tax'], true, $order->info['currency'], $order->info['currency_value']),
// {{
                'tax_class_id' => $tax_class,
                'value_exc_vat' => $order->info['shipping_cost_exc_tax'],
                'value_inc_tax' => $order->info['shipping_cost_inc_tax'],
// }}
            );
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_SHIPPING_STATUS' =>
            array(
                'title' => 'Display Shipping',
                'value' => 'true',
                'description' => 'Do you want to display the order shipping cost?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING' =>
            array(
                'title' => 'Allow Free Shipping',
                'value' => 'false',
                'description' => 'Do you want to allow free shipping?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER' =>
            array(
                'title' => 'Free Shipping For Orders Over',
                'value' => '50',
                'description' => 'Provide free shipping for orders over the set amount.',
                'sort_order' => '4',
                'use_function' => 'currencies->format',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION' =>
            array(
                'title' => 'Provide Free Shipping For Orders Made',
                'value' => 'national',
                'description' => 'Provide free shipping for orders sent to the set destination.',
                'sort_order' => '5',
                'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
            ),
        );
    }

}
