<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes\modules;

abstract class ModuleTotal extends Module {
    
    protected static $adjusting;

    public function process() {
        
    }

    function visibility($platform_id = 0, $area = '') {
        if ( (int)$platform_id==0 ) return true;
        
        $visibility_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY . " where visibility_constant='" . $area . "'");
        $visibility = tep_db_fetch_array($visibility_query);
        if (is_array($visibility)) {
            $visibility_area_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY_AREA . " where visibility_id='" . $visibility['visibility_id'] . "' AND visibility_code='" . $this->code . "' AND platform_id = '" . (int)$platform_id . "'");
            if (tep_db_num_rows($visibility_area_query) > 0) {
                return true;
            }
        }
        return false;
    }
    
    function displayText($platform_id = 0, $area = '', $totals) {
        if ( (int)$platform_id==0 ) return $totals;
        
        $visibility_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY . " where visibility_constant='" . $area . "'");
        $visibility = tep_db_fetch_array($visibility_query);
        if (is_array($visibility)) {
            $visibility_area_query = tep_db_query("SELECT visibility_vat, show_line FROM " . TABLE_VISIBILITY_AREA . " where visibility_id='" . $visibility['visibility_id'] . "' AND visibility_code='" . $this->code . "' AND platform_id = '" . (int)$platform_id . "'");
            if (tep_db_num_rows($visibility_area_query) > 0) {
                $visibility_area = tep_db_fetch_array($visibility_area_query);
                $totals['show_line'] = $visibility_area['show_line'];
                if ($visibility_area['visibility_vat'] == 1) {
                    $totals['text'] = $totals['text_inc_tax'];
                    $totals['title'] = str_replace(":", "(" . TEXT_INC_VAT . "):", $totals['title']);
                } elseif ($visibility_area['visibility_vat'] == -1) {
                    $totals['text'] = $totals['text_exc_tax'];
                    $totals['title'] = str_replace(":", "(" . TEXT_EXC_VAT . "):", $totals['title']);
                }
            }
        }
       
        return $totals;
    }

    function getIncVATTitle() {
        return TEXT_INC_VAT;
    }
    
    function getIncVAT($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', 1, $checked);
    }
    
    function getExcVATTitle() {
        return TEXT_EXC_VAT;
    }
    
    function getExcVAT($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', -1, $checked);
    }
    
    function getVisibility($platform_id) {
        if ( (int)$platform_id==0 ) return '';
        
        $response = '<br><br><table width="50%"><thead><tr><th>' . TEXT_VISIBILITY_ON_PAGES . '</th><th style="text-align: center">' . $this->getIncVATTitle() . '</th><th style="text-align: center">' . $this->getExcVATTitle() . '</th><th style="text-align: center">' . TEXT_DEFAULT . '</th><th style="text-align: center">' . SHOW_TOP_LINE . '</th></tr></thead><tbody>';
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
            $response .= $this->getIncVAT($visibility['visibility_id'], ($visibility_area['visibility_vat'] == 1));
            $response .= '</td><td style="text-align: center">';
            $response .= $this->getExcVAT($visibility['visibility_id'], ($visibility_area['visibility_vat'] == -1));
            $response .= '</td><td style="text-align: center">';
            $response .= tep_draw_radio_field('visibility_vat[' . $visibility['visibility_id'] . ']', '0', ($visibility_area['visibility_vat'] == 0));
            $response .= '</td><td style="text-align: center">';
            $response .= tep_draw_checkbox_field('show_line[' . $visibility['visibility_id'] . ']', '', ($visibility_area['show_line'] == 1));
            $response .= '</td></tr>';
        }
        $response .= '</tbody></table>';
        return $response;
    }

    function setVisibility() {
        $platform_id = (int)\Yii::$app->request->post('platform_id');
        if ( (int)$platform_id==0 ) return false;
        
        tep_db_query("delete from " . TABLE_VISIBILITY_AREA . " where visibility_code = '" . $this->code . "' AND platform_id = '" . (int)$platform_id . "'");
        
        $visibility = \Yii::$app->request->post('visibility');
        $visibility_vat = \Yii::$app->request->post('visibility_vat');
        $show_line = \Yii::$app->request->post('show_line');
        if (is_array($visibility)) {
            foreach ($visibility as $visibility_id => $checked) {
                $sl = $show_line[$visibility_id] ? 1 : 0;
                tep_db_query("insert into " . TABLE_VISIBILITY_AREA . " (visibility_id, visibility_code, platform_id, visibility_vat, show_line) values ('" . $visibility_id . "', '" . $this->code . "', '" . (int)$platform_id . "', '" . (int)$visibility_vat[$visibility_id] . "', '" . $sl . "')");
            }
        }
        
        return true;
    }
    
    function getTaxValues($tax_class_id, \common\classes\Order $order){
        
        $tax = 0;
        $tax_description = '';
        
        $paltform_config = new \common\classes\platform_config($order->info['platform_id']);
        $platform_address = $paltform_config->getPlatformAddress();
        if ($platform_address){
            $check_delivery_zone = tep_db_fetch_array(tep_db_query("select geo_zone_id from " . TABLE_ZONES_TO_TAX_ZONES . " za where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $order->delivery['country']['id'] . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$order->delivery['zone_id'] . "')"));
            $check_billing_zone = tep_db_fetch_array(tep_db_query("select geo_zone_id from " . TABLE_ZONES_TO_TAX_ZONES . " za where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $order->billing['country']['id'] . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$order->billing['zone_id'] . "')"));

            $check_platform_zone = tep_db_fetch_array(tep_db_query("select geo_zone_id from " . TABLE_ZONES_TO_TAX_ZONES . " za where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $platform_address['country_id'] . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$platform_address['zone_id'] . "')"));
            if ($check_platform_zone['geo_zone_id'] == $check_delivery_zone['geo_zone_id'] || $check_platform_zone['geo_zone_id'] == $check_billing_zone['geo_zone_id']){
                $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
                $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
            }
        }
        return [
                'tax_class_id' => $tax_class_id,
                'tax' => $tax,
                'tax_description' => $tax_description
                ];
    }

}
