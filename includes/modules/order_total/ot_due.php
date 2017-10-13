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

class ot_due extends ModuleTotal {

    var $title, $output;

    function __construct() {
        $this->code = 'ot_due';
        $this->title = MODULE_ORDER_TOTAL_DUE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_DUE_DESCRIPTION;
        $this->enabled = ((MODULE_ORDER_TOTAL_DUE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_DUE_SORT_ORDER;

        $this->output = array();
    }

    function process() {
        global $order, $currencies;
        $this->output = [];
        $amount = $order->info['total_exc_tax'] - $order->info['total_paid_exc_tax'];
        $amount_tax = $order->info['total_inc_tax'] - $order->info['total_paid_inc_tax'];

        $this->output[] = array('title' => $this->title . ':',
            'text' => $currencies->format($amount_tax, true, $order->info['currency'], $order->info['currency_value']),
            'value' => $amount_tax,
            'text_exc_tax' => $currencies->format($amount, true, $order->info['currency'], $order->info['currency_value']),
            'text_inc_tax' => $currencies->format($amount_tax, true, $order->info['currency'], $order->info['currency_value']),
            'sort_order' => $this->sort_order,
            'code' => $this->code,
// {{
            'tax_class_id' => 0,
            'value_exc_vat' => $amount,
            'value_inc_tax' => $amount_tax,
// }}
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_DUE_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_DUE_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_DUE_STATUS' =>
            array(
                'title' => 'Display Admount Due',
                'value' => 'true',
                'description' => 'Do you want to display the Admount Due?',
                'sort_order' => '150',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_DUE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'description' => 'Sort order of display.',
                'sort_order' => '150',
            ),
        );
    }
    
    public function getVisibility($platform_id) {
            if ( (int)$platform_id==0 ) return '';
            
            $response = '<br><br><table width="50%"><thead><tr><th>' . TEXT_VISIBILITY_ON_PAGES . '</th><th style="text-align: center">' . $this->getIncVATTitle() . '</th><th style="text-align: center">' . SHOW_TOP_LINE . '</th></tr></thead><tbody>';
            $visibility_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY . " where 1");
            while ($visibility = tep_db_fetch_array($visibility_query)) {
                $visibility_area_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY_AREA . " where visibility_id='" . $visibility['visibility_id'] . "' AND visibility_code='" . $this->code . "' AND platform_id = '" . (int)$platform_id . "'");
                $checked = 0;
                if (tep_db_num_rows($visibility_area_query) > 0) {
                    $checked = 1;
                }
                $visibility_area = tep_db_fetch_array($visibility_area_query);
                $response .= '<tr><td>';
                $response .= tep_draw_checkbox_field('visibility[' . $visibility['visibility_id'] . ']', 1, $checked);
                $response .= '&nbsp;' . constant($visibility['visibility_constant']) . '<br>';
                $response .= '</td><td style="text-align: center">';
                $response .= $this->getIncVAT($visibility['visibility_id'], true);
                $response .= '</td><td style="text-align: center">';
                $response .= tep_draw_checkbox_field('show_line[' . $visibility['visibility_id'] . ']', '', ($visibility_area['show_line'] == 1));
                $response .= '</td></tr>';
            }
            $response .= '</tbody></table>';
            return $response;
        }
}
