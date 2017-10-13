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

class ot_gift_wrap extends ModuleTotal {

    var $title, $output;

    function __construct() {
        $this->code = 'ot_gift_wrap';
        $this->title = MODULE_ORDER_TOTAL_GIFT_WRAP_TITLE;
        $this->description = MODULE_ORDER_TOTAL_GIFT_WRAP_DESCRIPTION;
        $this->enabled = ((MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER;

        $this->output = array();
    }

    function process($replacing_value = -1, $visible = false) {
        global $order, $currencies, $payment, $cart;

        if (!is_object($order) || !is_array($order->products))
            return;

        $have_gift_wrapped_products = false;
        $gift_wrap_amount = 0;
        $_tax_class_rates = [];

        foreach ($order->products as $ordered_product) {
            if (isset($ordered_product['gift_wrapped']) && $ordered_product['gift_wrapped']) {
                $have_gift_wrapped_products = true;
                $gift_wrap_amount += $ordered_product['gift_wrap_price'];
                $_tax_class_rates[] = ['value' => \common\helpers\Tax::calculate_tax($ordered_product['gift_wrap_price'], $ordered_product['tax']),
                    'class' => $ordered_product['tax_class_id'],
                    'taxed' => \common\helpers\Tax::add_tax($ordered_product['gift_wrap_price'], $ordered_product['tax'])
                ];
            }
        }



        if ($have_gift_wrapped_products == true || $replacing_value != -1 || $visible) {

            $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS, $order);

            $tax_class_id = $taxation['tax_class_id'];
            $tax = $taxation['tax'];
            $tax_description = $taxation['tax_description'];
            //$tax_class_id = MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS;
            //$tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
            //$tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
            //$gift_wrap_tax_amount = \common\helpers\Tax::calculate_tax($gift_wrap_amount, $tax);
            $gift_wrap_tax_amount = array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'value'));
            $gift_wrap_amount_inc = array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'taxed')); //$gift_wrap_amount + $gift_wrap_tax_amount;

            if ($replacing_value != -1) {
                if (is_array($replacing_value)) {
                    $gift_wrap_amount = (float) $replacing_value['ex'];
                    $gift_wrap_amount_inc = (float) $replacing_value['in'];
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }
            $order->info['tax'] += $gift_wrap_tax_amount;
            $order->info['tax_groups']["$tax_description"] += $gift_wrap_tax_amount;
            $order->info['total'] += $gift_wrap_amount_inc;
            $order->info['total_inc_tax'] += $gift_wrap_amount_inc;
            $order->info['total_exc_tax'] += $gift_wrap_amount;

            parent::$adjusting += $currencies->format_clear($gift_wrap_amount, true, $order->info['currency'], $order->info['currency_value']);

            $this->output[] = array(
                'title' => $this->title . ':',
                'text' => $currencies->format(array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'taxed')), true, $order->info['currency'], $order->info['currency_value']), //$currencies->format(\common\helpers\Tax::add_tax($gift_wrap_amount, $tax), true, $order->info['currency'], $order->info['currency_value']),
                'value' => array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'taxed')), //\common\helpers\Tax::add_tax($gift_wrap_amount, $tax),
                'text_exc_tax' => $currencies->format($gift_wrap_amount, true, $order->info['currency'], $order->info['currency_value']),
                'text_inc_tax' => $currencies->format($gift_wrap_amount_inc, true, $order->info['currency'], $order->info['currency_value']),
// {{
                'tax_class_id' => $tax_class_id,
                'value_exc_vat' => $gift_wrap_amount,
                'value_inc_tax' => $gift_wrap_amount_inc,
// }}
                'prefix' => '+',
            );
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS' =>
            array(
                'title' => 'Display Gift Wrap Order Fee',
                'value' => 'true',
                'description' => 'Do you want to display the Gift Wrap Order Fee?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the Gift Wrap Order Fee.',
                'sort_order' => '7',
                'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
        );
    }

}
