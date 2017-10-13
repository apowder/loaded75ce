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

class ot_total extends ModuleTotal {

    var $title, $output;

    function __construct() {
        $this->code = 'ot_total';
        $this->title = MODULE_ORDER_TOTAL_TOTAL_TITLE;
        $this->description = MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION;
        $this->enabled = ((MODULE_ORDER_TOTAL_TOTAL_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER;

        $this->output = array();
    }

    function process() {
        global $order, $currencies;

        $this->output[] = array('title' => $this->title . ':',
            'text' => '<b>' . $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '</b>',
            'value' => $order->info['total'],
            'text_exc_tax' => '<b>' . $currencies->format($order->info['total_exc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b>',
            'text_inc_tax' => '<b>' . $currencies->format($order->info['total_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b>',
// {{
            'tax_class_id' => 0,
            'value_exc_vat' => $order->info['total_exc_tax'],
            'value_inc_tax' => $order->info['total_inc_tax'],
// }}
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_TOTAL_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the total order value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}
