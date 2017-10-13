<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\modules\ModuleShipping;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class zonetable extends ModuleShipping {

    var $code,
            $title,
            $description,
            $icon,
            $enabled,
            $zone_id,
            $methods,
            $select_id,
            $shipping_weight,
            $products_qty,
            $total;

    function __construct() {
        global $order, $shipping_weight, $shipping_num_boxes, $total_weight, $languages_id, $cart;
        //, $admin_mode;    
        $this->code = 'zonetable';
        $this->title = MODULE_SHIPPING_ZONE_TABLE_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_ZONE_TABLE_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_SHIPPING_ZONE_TABLE_SORT_ORDER;
        //$this->icon = DIR_WS_ICONS . 'international.png';
        $this->tax_class = MODULE_SHIPPING_ZONE_TABLE_TAX_CLASS;
        $this->enabled = ((MODULE_SHIPPING_ZONE_TABLE_STATUS == 'True') ? true : false);
        /*
          if(strpos(DIR_WS_ADMIN,'/')===false) {
          $this->admin_mode = false;
          } else {
          $this->admin_mode = true;
          }
         */
        if (is_object($cart)) {
            if ($cart->show_weight() >= 0) {
                $this->shipping_weight = $cart->show_weight() + SHIPPING_BOX_WEIGHT;
                $this->total = round($cart->total * $order->info['currency_value'], 2);
                $this->products_qty = $cart->count_contents();
            }
        }
        /*
          if ( is_object($cart) && $this->admin_mode != true ) {
          if($cart->show_weight()>=0)  {
          $this->shipping_weight = $cart->show_weight() + SHIPPING_BOX_WEIGHT;
          $this->total = round($cart->total * $order->info['currency_value'],2);
          $this->products_qty = $cart->count_contents();
          }
          } elseif ( $this->admin_mode==true && is_object($order) ) {
          $this->shipping_weight = $order->show_weight() + SHIPPING_BOX_WEIGHT;
          if(is_object($cart))  {
          $this->total = $cart->total;
          $this->products_qty = $cart->count_contents();
          }
          }
         */
        if ($this->enabled == true) {
            $check_flag = false;
            $postcode = substr(str_replace(' ', '', $order->delivery['postcode']), 0, 4);
            $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES_TO_SHIP_ZONES . " gz where (gz.zone_country_id = '" . $order->delivery['country']['id'] . "' or gz.zone_country_id=0 ) and (gz.zone_id = '" . $order->delivery['zone_id'] . "' or gz.zone_id = 0 ) and if(gz.start_postcode<>'',gz.start_postcode<='" . tep_db_input($postcode) . "',1) and if(gz.stop_postcode<>'',gz.stop_postcode >= '" . tep_db_input($postcode) . "',1) order by gz.start_postcode desc");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] == 0) {
                $this->enabled = false;
            }
            // check products shipping options
            if (!is_object($order) && !sizeof($order->products)) {
                return;
            }

            // 1. create array of products id
            $products_array = array();
            if (is_object($cart)) {
                $tmp = $cart->get_products();
            } else {
                $tmp = $order->products;
            }

            if (count($tmp) > 0) {
                foreach ($tmp as $products) {
                    $products_array[] = (int) $products['id'];
                }
            }


            // 2. select all ship options
            $ship_options = array();
            $ship_options_query = tep_db_query("select ship_options_id as id, ship_options_name as name from " . TABLE_SHIP_OPTIONS . " where language_id='" . $languages_id . "' order by sort_order, ship_options_id");
            while ($d = tep_db_fetch_array($ship_options_query)) {
                $ship_options[] = $d['id'];
            }

            // 3. check product and options all product must have appropriate option enabled

            foreach ($ship_options as $ship_options_id) {
                // fill available methods array
                $this->methods[] = $ship_options_id;
            }

            if (count($this->methods) == 0) {
                $this->enabled = false;
            }
        }
    }

// class methods
    function quote($method = '') {
        // Weight per package - SHIPPING_MAX_WEIGHT
        global $order, $cart, $shipping_weight, $shipping_num_boxes, $total_weight, $languages_id, $inc_methods, $select_id, $shipping;

        $platform_id = (int)$order->info['platform_id'];
        
        $all_in_one_mode = false;

        $methods_query = tep_db_query("select ship_options_id from " . TABLE_SHIP_OPTIONS . " where platform_id='" . $platform_id . "' and language_id='" . $languages_id . "' order by sort_order");
        $methods = array();
        $select_id = 0;
        $inc_methods = 0;
        while ($methods_fetch = tep_db_fetch_array($methods_query)) {
            if (!empty($method) && $method != $methods_fetch['ship_options_id'])
                continue;
            $tmp = $this->_quote($methods_fetch['ship_options_id']);
//        if ($tmp['title'] != '' && is_numeric($tmp['cost'])){ 
            if (is_numeric($tmp['cost'])) {
                $methods[] = $tmp;
                $inc_methods++;
            }
        }

        $this->quotes = array('id' => $this->code,
            'module' => '<span class = "ship-title">' . $this->title . '</span><span class="shippingExtNote"><span>' . MODULE_SHIPPING_ZONE_TABLE_NOTE_TEXT . '</span></span>',
            'methods' => $methods,
            'tax' => \common\helpers\Tax::get_tax_rate(MODULE_SHIPPING_ZONE_TABLE_TAX_CLASS)
        );
        if (sizeof($this->quotes['methods']) == 0)
            $this->quotes['error'] = "Please check data";
        return $this->quotes;
    }

    function _quote($method_id) {
        global $order, $cart, $languages_id, $min_price, $inc_methods;
        
        $platform_id = (int)$order->info['platform_id'];
        
//      $postcode = substr(str_replace(' ','',$order->delivery['postcode']),0,4);
        $postcode = str_replace(' ', '', $order->delivery['postcode']);

        $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES_TO_SHIP_ZONES . " gz where gz.platform_id='" . $platform_id . "' and (gz.zone_country_id = '" . $order->delivery['country']['id'] . "' or gz.zone_country_id=0 ) and (gz.zone_id = '" . $order->delivery['zone_id'] . "' or gz.zone_id = 0 ) and if(gz.start_postcode<>'',gz.start_postcode<='" . tep_db_input($postcode) . "',1) and if(gz.stop_postcode<>'',gz.stop_postcode >= '" . tep_db_input($postcode) . "',1)  order by gz.start_postcode desc");
        $check = tep_db_fetch_array($check_query);

        if ($check['total'] == 0) {
            // error!!!
            $this->quotes = array('id' => $this->code,
                'error' => MODULE_SHIPPING_ZONE_TABLE_INVALID_ZONE,
                'module' => MODULE_SHIPPING_ZONE_TABLE_TEXT_TITLE);
            return $this->quotes;
        }


        $sql = "select * from " . TABLE_SHIP_OPTIONS . " so, " . TABLE_ZONE_TABLE . " zt, " . TABLE_ZONES_TO_SHIP_ZONES . " gz, " . TABLE_SHIP_ZONES . " sz 
                where so.ship_options_id = zt.ship_options_id 
                  and zt.ship_zone_id = gz.ship_zone_id 
                  and (gz.zone_id = '" . $order->delivery['zone_id'] . "' 
                      or gz.zone_id=0) 
                  and (zt.country_id = '" . $order->delivery['country_id'] . "' 
                      or zt.country_id=0)
                  and (gz.zone_country_id = '" . $order->delivery['country_id'] . "' 
                      or gz.zone_country_id=0)     
                  and if(gz.start_postcode<>'',gz.start_postcode<='" . tep_db_input($postcode) . "',1) 
                  and if(gz.stop_postcode<>'',gz.stop_postcode >= '" . tep_db_input($postcode) . "',1) 
                  and sz.ship_zone_id = zt.ship_zone_id 
                  and zt.ship_options_id='" . $method_id . "' 
                  and so.language_id = '" . $languages_id . "'
                  and zt.enabled=1
                  and so.platform_id='" . $platform_id . "' 
                  and zt.platform_id='" . $platform_id . "' 
                  and gz.platform_id='" . $platform_id . "' 
                  and sz.platform_id='" . $platform_id . "' 
                order by gz.start_postcode desc, gz.zone_country_id desc ";

        $query = tep_db_query($sql);
        $data = tep_db_fetch_array($query);

        $price = null;
        $shipping_method = $data['ship_options_name'];

        if (($data['per_kg_price'] > 0) /* && ($data['mode'] == '0') */ && !tep_not_null($data['rate'])) {
            $shipping_value = $this->shipping_weight;
            $price = ($shipping_value ) * $data['per_kg_price'];
        } else {
            $price = -1;
            switch ($data['mode']) {
                case '1':
                    $shipping_value = $this->total;
                    break;
                case '2':
                    $shipping_value = $this->products_qty;
                    break;
                default:
                case '0':
                    $shipping_value = $this->shipping_weight;
                    break;
            }

            $last_value = 0;
            $rates = explode(';', $data['rate']);
            foreach ($rates as $rate_info) {
                if (empty($rate_info)) {
                    continue;
                }
                $rate_info = explode(':', $rate_info);
                if ($shipping_value < $rate_info[0] && $shipping_value >= $last_value) {
                    $price = $rate_info[1];
                }
                $last_value = $rate_info[0];
            }
            // per kg price and special 0 price
            if ($price == 0 && ($data['per_kg_price'] > 0)) {
                $price = ($shipping_value ) * $data['per_kg_price'];
            }
        }

        if ($price >= 0) {
            if ($data['handling_price'] > 0) {
                $price += $data['handling_price'];
            }

            if ($inc_methods == 0)
                $min_price = $price;
            if ($price <= $min_price && $price != 0) {
                $this->select_id = (int) $inc_methods;
                $min_price = $price;
            }

            if ($price >= 0) {
                return array('id' => $data['ship_options_id'],
                    'title' => $shipping_method,//'<span class="ship-img">' . tep_image($this->icon, $shipping_method) . '</span>',
                    'tax' => MODULE_SHIPPING_ZONE_TABLE_TAX_CLASS,
                    'cost' => $price,
                    'selected' => 0);
            }
        }
    }

    public function configure_keys() {
        return array(
            'MODULE_SHIPPING_ZONE_TABLE_STATUS' =>
            array(
                'title' => 'Enable Table Method',
                'value' => 'True',
                'description' => 'Do you want to offer Zone Table rate shipping?',
                'sort_order' => '0',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
            'MODULE_SHIPPING_ZONE_TABLE_HANDLING' =>
            array(
                'title' => 'Handling Fee',
                'value' => '0',
                'description' => 'Handling fee for this shipping method.',
                'sort_order' => '0',
            ),
            'MODULE_SHIPPING_ZONE_TABLE_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the shipping fee.',
                'sort_order' => '0',
                'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
            'MODULE_SHIPPING_ZONE_TABLE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '0',
                'description' => 'Sort order of display.',
                'sort_order' => '0',
            ),
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_SHIPPING_ZONE_TABLE_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_SHIPPING_ZONE_TABLE_SORT_ORDER');
    }

    function extra_params() {
        global $languages_id;
        
        $platform_id = (int)\Yii::$app->request->get('platform_id');
        if ($platform_id == 0) {
            $platform_id = (int)\Yii::$app->request->post('platform_id');
        }
        
        $languages = \common\helpers\Language::get_languages();
        
        $tab = \Yii::$app->request->post('tab', '');
        $action = \Yii::$app->request->post('action', '');
        switch ($action) {
            case 'add_option':
                $next_id_query = tep_db_query("select max(ship_options_id) as ship_options_id from " . TABLE_SHIP_OPTIONS . "");
                $next_id = tep_db_fetch_array($next_id_query);
                $ship_options_id = $next_id['ship_options_id'] + 1;
                for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                    $sql_data_array = array(
                        'ship_options_id' => $ship_options_id,
                        'language_id' => $languages[$i]['id'],
                        'ship_options_name' => '',
                        'sort_order' => $ship_options_id,
                        'platform_id' => $platform_id,
                    );
                    tep_db_perform(TABLE_SHIP_OPTIONS, $sql_data_array);
                }
                break;
            case 'del_option':
                $sID = \Yii::$app->request->post('params');
                tep_db_query("delete from " . TABLE_SHIP_OPTIONS . " where ship_options_id = '" . (int)$sID . "'");
                tep_db_query("delete from " . TABLE_ZONE_TABLE . " where ship_options_id = '" . (int)$sID . "'");
                break;
            case 'add_zone';
                $sql_data_array = array(
                    'ship_zone_name' => '',
                    'ship_zone_description' => '',
                    'date_added' => 'now()',
                    'platform_id' => $platform_id,
                );
                tep_db_perform(TABLE_SHIP_ZONES, $sql_data_array);
                
                break;
            case 'del_zone':
                $zID = \Yii::$app->request->post('params');
                tep_db_query("delete from " . TABLE_SHIP_ZONES . " where ship_zone_id = '" . (int)$zID . "'");
                tep_db_query("delete from " . TABLE_ZONES_TO_SHIP_ZONES . " where ship_zone_id = '" . (int)$zID . "'");
                break;
            case 'add_ship_zone';
                $zID = \Yii::$app->request->post('params');
                $start_postcode = \Yii::$app->request->post('start_postcode');
                $stop_postcode = \Yii::$app->request->post('stop_postcode');
                $zone_country_id = \Yii::$app->request->post('zone_country_id');
                $zone_id = \Yii::$app->request->post('zone_id');
                $sql_data_array = array(
                    'zone_country_id' => (int)$zone_country_id[$zID],
                    'zone_id' => (int)$zone_id[$zID],
                    'ship_zone_id' => (int)$zID,
                    'date_added' => 'now()',
                    'start_postcode' => $start_postcode[$zID],
                    'stop_postcode' => $stop_postcode[$zID],
                    'platform_id' => $platform_id,
                );
                tep_db_perform(TABLE_ZONES_TO_SHIP_ZONES, $sql_data_array);
                
                break;
            case 'del_ship_zone';
                $sID = \Yii::$app->request->post('params');
                tep_db_query("delete from " . TABLE_ZONES_TO_SHIP_ZONES . " where association_id = '" . (int)$sID . "'");
                break;
            case 'add_table':
                $ship_zone_id = \Yii::$app->request->post('ship_zone_id');
                $next_id_query = tep_db_query("select max(zone_table_id) as zone_table_id from " . TABLE_ZONE_TABLE . "");
                $next_id = tep_db_fetch_array($next_id_query);
                $zone_table_id = $next_id['zone_table_id'] + 1;

                $ship_options_query = tep_db_query("select ship_options_id as id from " . TABLE_SHIP_OPTIONS . " where platform_id='" . $platform_id . "' and language_id='" . $languages_id . "' order by sort_order");
                while($d = tep_db_fetch_array($ship_options_query)){
                    $sql_data_array = array(
                        'zone_table_id' => $zone_table_id,
                        'ship_zone_id' => $ship_zone_id,
                        'ship_options_id' => $d['id'],
                        'rate' => '',
                        'platform_id' => $platform_id,
                    );
                    tep_db_perform(TABLE_ZONE_TABLE, $sql_data_array);
                }
  
                
                break;
            case 'del_table':
                $zID = \Yii::$app->request->post('params');
                tep_db_query("delete from " . TABLE_ZONE_TABLE . " where zone_table_id = '" . (int)$zID . "'");
                break;
            default:
                break;
        }
        
        $saveto = \Yii::$app->request->post('saveto', '');
        switch ($saveto) {
            case 'zones':
                $ship_zone_name = \Yii::$app->request->post('ship_zone_name');
                $zones_query = tep_db_query("select * from " . TABLE_SHIP_ZONES . " where platform_id='" . $platform_id . "'");
                while ($zones = tep_db_fetch_array($zones_query)) {
                    if (isset($ship_zone_name[$zones['ship_zone_id']])) {
                        tep_db_query("update " . TABLE_SHIP_ZONES . " set ship_zone_name = '" . $ship_zone_name[$zones['ship_zone_id']] . "' where ship_zone_id = '" . (int)$zones['ship_zone_id'] . "'");
                    }
                }
                break;
            case 'options':
                $ship_options_name = \Yii::$app->request->post('ship_options_name');
                $sort_order = \Yii::$app->request->post('sort_order');
                $options_query = tep_db_query("select * from " . TABLE_SHIP_OPTIONS . " where platform_id='" . $platform_id . "'");
                while ($options = tep_db_fetch_array($options_query)) {
                    if (isset($ship_options_name[$options['ship_options_id']][$options['language_id']])) {
                        tep_db_query("update " . TABLE_SHIP_OPTIONS . " set ship_options_name = '" . $ship_options_name[$options['ship_options_id']][$options['language_id']] . "' where ship_options_id = '" . (int)$options['ship_options_id'] . "' and language_id='" . (int)$options['language_id'] . "' and platform_id='" . $platform_id . "'");
                    }
                    if (isset($sort_order[$options['ship_options_id']])) {
                        tep_db_query("update " . TABLE_SHIP_OPTIONS . " set sort_order = '" . (int)$sort_order[$options['ship_options_id']] . "' where ship_options_id = '" . (int)$options['ship_options_id'] . "' and platform_id='" . $platform_id . "'");
                    }
                }
                break;
            case 'table':
                $enabled = \Yii::$app->request->post('enabled');
                $mode = \Yii::$app->request->post('mode');
                $handling_price = \Yii::$app->request->post('handling_price');
                $per_kg_price = \Yii::$app->request->post('per_kg_price');
                $rate = \Yii::$app->request->post('rate');
                $new_rate = \Yii::$app->request->post('new_rate');
                
                $table_query = tep_db_query("select zone_table_id, ship_zone_id from " . TABLE_ZONE_TABLE . " where platform_id='" . $platform_id . "' group by zone_table_id");
                while($table = tep_db_fetch_array($table_query)){
                    $zone_table_id = $table['zone_table_id'];
                    $ship_zone_id = $table['ship_zone_id'];
                    //$zones_query = tep_db_query("select ship_zone_id from " . TABLE_SHIP_ZONES . " where 1");
                    //while($zones = tep_db_fetch_array($zones_query)){
                        //$ship_zone_id = $zones['ship_zone_id'];
                        $options_query = tep_db_query("select ship_options_id from " . TABLE_SHIP_OPTIONS . " where language_id = '" . (int)$languages_id . "' and platform_id='" . $platform_id . "'");
                        while ($options = tep_db_fetch_array($options_query)) {
                            $ship_options_id = $options['ship_options_id'];
                            
                            //rate   
                            $sql_data_array = [];
                            
                            $sql_data_array['enabled'] = (isset($enabled[$zone_table_id][$ship_options_id]) ? $enabled[$zone_table_id][$ship_options_id] : 0);
                            $sql_data_array['mode'] = (isset($mode[$zone_table_id][$ship_options_id]) ? $mode[$zone_table_id][$ship_options_id] : 0);
                            $sql_data_array['handling_price'] = (isset($handling_price[$zone_table_id][$ship_options_id]) ? $handling_price[$zone_table_id][$ship_options_id] : 0);
                            $sql_data_array['per_kg_price'] = (isset($per_kg_price[$zone_table_id][$ship_options_id]) ? $per_kg_price[$zone_table_id][$ship_options_id] : 0);
                            
                            
                            $value_true = \common\helpers\Zones::stick_shipping_rates($rate[$zone_table_id][$ship_options_id]);
                            $value_new_add = \common\helpers\Zones::stick_shipping_rates($new_rate[$zone_table_id][$ship_options_id]);
                            if(strlen($value_new_add)) $value_true .= $value_new_add;
                            $sql_data_array['rate'] = $value_true;
                            
                            $tst = tep_db_fetch_array(tep_db_query("select count(*) as c from ".TABLE_ZONE_TABLE." where zone_table_id = '" . $zone_table_id . "' and ship_zone_id = '" . $ship_zone_id . "' and ship_options_id='" . $ship_options_id . "' and platform_id='" . $platform_id . "'"));
                            if ( $tst['c']==0 ) {
                                $sql_data_array['country_id'] = 0;
                                $sql_data_array['zone_table_id'] = $zone_table_id;
                                $sql_data_array['ship_zone_id'] = $ship_zone_id;
                                $sql_data_array['ship_options_id'] = $ship_options_id;
                                $sql_data_array['platform_id'] = $platform_id;
                                tep_db_perform(TABLE_ZONE_TABLE, $sql_data_array);
                            }else{
                                tep_db_perform(TABLE_ZONE_TABLE, $sql_data_array, 'update', "zone_table_id = '" . $zone_table_id . "' and ship_zone_id = '" . $ship_zone_id . "' and ship_options_id='" . $ship_options_id . "' and platform_id='" . $platform_id . "'");
                            }
                        }
                    //}
                }
                break;
            default:
                break;
        }
        
        if (empty($tab)) {
            $tab = 'table';
        }

        $html = '';
        if (!Yii::$app->request->isAjax) {
            $html .= '<div id="modules_extra_params">';
        }
        
        $html .= '<input type="hidden" name="action" value="">';
        $html .= '<input type="hidden" name="params" value="">';
        $html .= tep_draw_hidden_field('tab', $tab);
        $html .= tep_draw_hidden_field('saveto', $tab);

        $html .= '<div style="margin-bottom: 20px">';
        $html .= '<a href="javascript:void(0)" onclick="return changeTab(\'table\');" class="btn'.($tab == 'table' ?' btn-primary' : '').'">' . TEXT_SHIPPING_TABLE . '</a>';
        $html .= '&nbsp;<a href="javascript:void(0)" onclick="return changeTab(\'zones\');" class="btn'.($tab == 'zones' ?' btn-primary' : '').'">' . TEXT_SHIPPING_ZONES . '</a>';
        $html .= '&nbsp;<a href="javascript:void(0)" onclick="return changeTab(\'options\');" class="btn'.($tab == 'options' ?' btn-primary' : '').'">' . TEXT_SHIPPING_OPTIONS . '</a>';
        $html .= '</div>';

        switch ($tab) {
            case 'zones':
                $html .= '<table width="100%" class="selected-methods">';
                $html .= '<tr><th width="10%">'.TABLE_HEADING_ACTION.'</th><th width="20%">'.TABLE_HEADING_TITLE.'</th><th width="70%">'.IMAGE_DETAILS.'</th></tr>';
                $zones_query = tep_db_query("select ship_zone_id, ship_zone_name, ship_zone_description, last_modified, date_added from " . TABLE_SHIP_ZONES . " where platform_id='" . $platform_id . "' order by ship_zone_name");
                while ($zones = tep_db_fetch_array($zones_query)) {
                    $html .= '<tr><td><span class="delMethod" onclick="delZoneMethod(\'' . $zones['ship_zone_id'] . '\')"></span></td><td>';
                    $html .= '<input type="text" name="ship_zone_name[' . $zones['ship_zone_id'] . ']" value="' . $zones['ship_zone_name'] . '">';
                    $html .= '</td><td>';
                    $ship_zones_query = tep_db_query("select a.association_id, a.zone_country_id, c.countries_name, a.start_postcode, a.stop_postcode, a.zone_id, a.ship_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_SHIP_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id='" . $languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.ship_zone_id = " . $zones['ship_zone_id'] . " and a.platform_id='" . $platform_id . "' order by c.countries_name, association_id");
                    $html .= '<table width="100%">';
                    $html .= '<tr><th width="20%">'.TABLE_HEADING_COUNTRY_NAME.'</th><th width="20%">'.TABLE_HEADING_COUNTRY_ZONE.'</th><th width="20%">'.TABLE_HEADING_START_POSTCODE.'</th><th width="20%">'.TABLE_HEADING_STOP_POSTCODE.'</th><th width="20%">'.TABLE_HEADING_ACTION.'</th></tr>';
                    while ($ship_zones = tep_db_fetch_array($ship_zones_query)) {
                        $html .= '<tr>';
                        $html .= '<td>' . (($ship_zones['countries_name']) ? $ship_zones['countries_name'] : TEXT_ALL_COUNTRIES) . '</td>';
                        $html .= '<td>' . (($ship_zones['zone_id']) ? $ship_zones['zone_name'] : TEXT_ALL_ZONES) . '</td>';
                        $html .= '<td>' . (($ship_zones['start_postcode']) ? $ship_zones['start_postcode'] : '-') . '</td>';
                        $html .= '<td>' . (($ship_zones['stop_postcode']) ? $ship_zones['stop_postcode'] : '-') . '</td>';
                        $html .= '<td><span class="delMethod" onclick="delShipZoneMethod(\'' . $ship_zones['association_id'] . '\')"></span></td>';
                        $html .= '</tr>';
                    }
                    $html .= '<tr>';
                    $html .= '<td>' . tep_draw_pull_down_menu('zone_country_id['.$zones['ship_zone_id'].']', \common\helpers\Country::get_countries('', false, TEXT_ALL_COUNTRIES), '', 'onChange="update_zone(this.form, '.$zones['ship_zone_id'].');"') . '</td>';
                    $html .= '<td>' . tep_draw_pull_down_menu('zone_id['.$zones['ship_zone_id'].']', \common\helpers\Zones::prepare_country_zones_pull_down()) . '</td>';
                    $html .= '<td>' . tep_draw_input_field('start_postcode['.$zones['ship_zone_id'].']', '', 'size="10"') . '</td>';
                    $html .= '<td>' . tep_draw_input_field('stop_postcode['.$zones['ship_zone_id'].']', '', 'size="10"') . '</td>';
                    $html .= '<td><span class="addMethod" onclick="addShipZoneMethod(\'' . $zones['ship_zone_id'] . '\')"></span></td>';
                    $html .= '</tr>';

                    $html .= '</table>';
                    $html .= '</td></tr>';
                }
                $html .= '<tr><td><span class="addMethod" onclick="return addZoneMethod();"></span></td><td>&nbsp;</td><td></td></tr>';
                $html .= '</table><br><br>';
                break;
            case 'options':
                $html .= '<table width="100%" class="selected-methods">';
                $html .= '<tr><th width="10%">'.TABLE_HEADING_ACTION.'</th><th width="80%">'.TABLE_HEADING_TITLE.'</th><th width="10%">' . TEXT_SORT_ORDER . '</th></tr>';
                $options_query = tep_db_query("select * from " . TABLE_SHIP_OPTIONS . " where language_id = '" . (int)$languages_id . "' and platform_id='" . $platform_id . "' order by sort_order,ship_options_id");
                while ($options = tep_db_fetch_array($options_query)) {
                    $html .= '<tr><td><span class="delMethod" onclick="delOptionMethod(\'' . $options['ship_options_id'] . '\')"></span></td><td>';
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $html .= $languages[$i]['image'] . '&nbsp;<input type="text" name="ship_options_name[' . $options['ship_options_id'] . '][' . $languages[$i]['id'] . ']" value="' . \common\helpers\Zones::get_ship_options_name($options['ship_options_id'], $languages[$i]['id']) . '">' . '<br>';
                    }
                    $html .= '</td><td><input type="text" name="sort_order[' . $options['ship_options_id'] . ']" value="' . $options['sort_order'] . '">';
                    $html .= '</td></tr>';
                }
                $html .= '<tr><td><span class="addMethod" onclick="return addOptionMethod();"></span></td><td>&nbsp;</td></tr>';
                $html .= '</table><br><br>';
                break;
            case 'table':
            default:
                $table_query = tep_db_query("select distinct z.zone_table_id, c1.ship_zone_name, z.ship_zone_id from " . TABLE_ZONE_TABLE . " z, " . TABLE_SHIP_ZONES . " c1 where z.ship_zone_id = c1.ship_zone_id and z.platform_id='" . $platform_id . "' and c1.platform_id='" . $platform_id . "' ORDER BY ship_zone_name");
                while ($table = tep_db_fetch_array($table_query)) {


                    $html .= '
<div class="zone-table-box" id="zone-table-box-' . $table['zone_table_id'] . '">
  <div class="zone-table-box-header">
    <span class="delMethod" onclick="delTableMethod(\'' . $table['zone_table_id'] . '\')"></span><div class="zone-table-box-close"></div>
    ' . $table['ship_zone_name'] . '
  </div>
  <div class="zone-table-box-content">';
                    $options_query = tep_db_query("select ship_options_id as id, rate, mode, handling_price, per_kg_price, enabled from " . TABLE_ZONE_TABLE . " where zone_table_id ='" . $table['zone_table_id'] . "' and platform_id='" . $platform_id . "'");
                    $rate_array = array();
                    $mode_array = array();
                    $enabled_array = array();
                    $handling_price_array = array();
                    $per_kg_price_array = array();
                    while($d = tep_db_fetch_array($options_query)){
                        $rate_array[$d['id']] = $d['rate'];
                        $mode_array[$d['id']] = $d['mode'];
                        $handling_price_array[$d['id']] = $d['handling_price'];
                        $per_kg_price_array[$d['id']] = $d['per_kg_price'];
                        $enabled_array[$d['id']] = $d['enabled'];
                    }
                    $cInfo = new objectInfo($zones);
                    $cInfo->rate = $rate_array;
                    $cInfo->mode = $mode_array;
                    $cInfo->enabled = $enabled_array;
                    $cInfo->handling_price = $handling_price_array;
                    $cInfo->per_kg_price = $per_kg_price_array;


                    $ship_options_query = tep_db_query("select ship_options_id as id, ship_options_name as name from " . TABLE_SHIP_OPTIONS . " where platform_id='" . $platform_id . "' and language_id='" . $languages_id . "' order by sort_order");
                    while($d = tep_db_fetch_array($ship_options_query)){
                        $id = $d['id'];
                        //$ship_options[$d['id']] = $d['name'];
                        $html .= '<div class="fieldset"><div class="legend">' . tep_draw_checkbox_field('enabled[' . $table['zone_table_id'] . '][' . $id . ']', '1', ($cInfo->enabled[$id] == 1)) . ' ' . $d['name'] . '</div><div class="fieldset-content"' . ($cInfo->enabled[$id] != 1 ? ' style="display:none"' : '') . '>';


                        $html .= '<div class="ztb-col-1"><strong>' . TEXT_INFO_MODE . '</strong><br>' .
                          tep_draw_radio_field('mode[' . $table['zone_table_id'] . '][' . $id . ']', '0', ($cInfo->mode[$id]==0)) . ' ' . TEXT_INFO_WEIGHT . '<br>' .
                          tep_draw_radio_field('mode[' . $table['zone_table_id'] . '][' . $id . ']', '1', ($cInfo->mode[$id]==1)) . ' ' . TEXT_INFO_PRICE . '<br>' .
                          tep_draw_radio_field('mode[' . $table['zone_table_id'] . '][' . $id . ']', '2', ($cInfo->mode[$id]==2)) . ' ' . TEXT_INFO_QUANTITY . '</div>' .
                          '<div class="ztb-col-2">
                              <div><strong>' . TEXT_PRODUCTS_PRICE_INFO . '</strong></div>
                              <div style="float:left; width:150px">' . TEXT_HANDLING_PRICE . '</div>
                              <div>' . TEXT_PER_KG_PRICE . '</div>
                              <div class="setting-row" style="clear:both">
                                <div style="float:left; width:150px">' . tep_draw_input_field('handling_price[' . $table['zone_table_id'] . '][' . $id . ']', $cInfo->handling_price[$id], 'size="5"') . '</div>
                                
                                ' . tep_draw_input_field('per_kg_price[' . $table['zone_table_id'] . '][' . $id . ']', $cInfo->per_kg_price[$id], 'size="5"') . '
                              </div>
                          </div>' .
                          '<div class="ztb-col-3"><strong>' . TEXT_INFO_RATE .
                          '</strong><br>'. self::tep_draw_shipping_table_cost($cInfo->rate[$id], $id, $table['zone_table_id']) . '</div>';

                        $html .= '</div></div>';
                    }
                    $html .='
  </div>
</div>
<script type="text/javascript">
(function(){$(function(){
  var ztb_close = $.cookie("ztb_close");
  var ztb_close_i = -1;
  if (ztb_close){
    ztb_close_i = ztb_close.split("a").indexOf("' . $table['zone_table_id'] . '");
  } else {
    ztb_close = "";
  }
  var box = $("#zone-table-box-' . $table['zone_table_id'] . '");
  $(".zone-table-box-close", box).on("click", function(){
    $(this).toggleClass("ztb-opened");
    $(".zone-table-box-content", box).slideToggle();
    
      ztb_close = $.cookie("ztb_close");
      ztb_close_i = -1;
      if (ztb_close){
        ztb_close_i = ztb_close.split("a").indexOf("' . $table['zone_table_id'] . '");
      } else {
        ztb_close = "";
      }
      if (ztb_close_i == -1){
        $.cookie("ztb_close", ztb_close + "' . $table['zone_table_id'] . '" + "a")
      } else {
        $.cookie("ztb_close", ztb_close.replace("' . $table['zone_table_id'] . 'a", ""))
      }
  });
  if (ztb_close_i != -1){
    $(".zone-table-box-close", box).toggleClass("ztb-opened");
    $(".zone-table-box-content", box).slideToggle(0)
  }
  $(".legend input", box).on("change", function(){
    if ($(this).prop("checked")){
        $(this).parents(".fieldset").find(".fieldset-content").show()
    } else {    
        $(this).parents(".fieldset").find(".fieldset-content").hide()
    }
  })
})})(jQuery)
</script>
                ';

                }
                $zones_query = tep_db_query("select ship_zone_id, ship_zone_name from " . TABLE_SHIP_ZONES . " where platform_id='" . $platform_id . "' order by ship_zone_name");
                if ( tep_db_num_rows($zones_query) > 0 ) {
                    $html .= '<div>'.\common\helpers\Zones::ship_zones_pull_down('name="ship_zone_id"', '', $platform_id).' <span class="btn" onclick="return addTableMethod();">' . TEXT_ADD_SHIPPING_TABLE . '</span></div>';
                }

                break;
        }
        if (!Yii::$app->request->isAjax) {
            $html .= '</div>';
            
            $html .= '<script type="text/javascript">
function submitForm() {
    $.post("' . tep_href_link('modules/extra-params') . '", $(\'form[name=modules]\').serialize(), function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function changeTab(tab) {
    $("input[name=\'tab\']").val(tab);
    submitForm();
    return false;
}

function addOptionMethod() {
    $("input[name=\'action\']").val("add_option");
    submitForm();
    return false;
}

function delOptionMethod(id) {
    $("input[name=\'action\']").val("del_option");
    $("input[name=\'params\']").val(id);
    submitForm();
    return false;
}

function addZoneMethod() {
    $("input[name=\'action\']").val("add_zone");
    submitForm();
    return false;
}

function delZoneMethod(id) {
    $("input[name=\'action\']").val("del_zone");
    $("input[name=\'params\']").val(id);
    submitForm();
    return false;
}

function addShipZoneMethod(id) {
    $("input[name=\'action\']").val("add_ship_zone");
    $("input[name=\'params\']").val(id);
    submitForm();
    return false;
}

function delShipZoneMethod(id) {
    $("input[name=\'action\']").val("del_ship_zone");
    $("input[name=\'params\']").val(id);
    submitForm();
    return false;
}

function update_zone(theForm, id) {
  var NumState = theForm.elements[\'zone_id[\'+id+\']\'].options.length;
  var SelectedCountry = "";

  while(NumState > 0) {
    NumState--;
    theForm.elements[\'zone_id[\'+id+\']\'].options[NumState] = null;
  }         

  SelectedCountry = theForm.elements[\'zone_country_id[\'+id+\']\'].options[theForm.elements[\'zone_country_id[\'+id+\']\'].selectedIndex].value;

' .  tep_js_zone_list('SelectedCountry', 'theForm', 'elements[\'zone_id[\'+id+\']\']') . '

}

function addTableMethod() {
    $("input[name=\'action\']").val("add_table");
    submitForm();
    return false;
}

function delTableMethod(id) {
    $("input[name=\'action\']").val("del_table");
    $("input[name=\'params\']").val(id);
    submitForm();
    return false;
}

function add_row_cost(obj_id,new_obj_html){
	var div = document.createElement(\'div\');
	div.innerHTML = new_obj_html;
	document.getElementById(obj_id).appendChild(div);
}

function delete_row_cost($obj){
	$obj.parentNode.parentNode.removeChild($obj.parentNode);
}

function delete_tr_cost($obj){
	$obj.parentNode.parentNode.parentNode.removeChild($obj.parentNode.parentNode);
}

</script>';
        }
        
        return $html;
    }

    public static function tep_draw_shipping_table_cost($shipping_cost_string='',$id, $zone_table_id){
	
	if(substr($shipping_cost_string,-1)==";") $shipping_cost_string = substr($shipping_cost_string,0,-1);
	
	$shipping_cost = preg_split('/[;:]/',$shipping_cost_string);
	for($i=0;$i<sizeof($shipping_cost);$i+=2){
		$output .= '<tr>
						<td class="shipping_cost">
						' . tep_draw_input_field('rate[' . $zone_table_id . '][' . $id . ']['.$i.']', $shipping_cost[$i],'size="10" class="shipping_cost"') . '
						' . tep_draw_input_field('rate[' . $zone_table_id . '][' . $id . ']['.($i+1).']', $shipping_cost[$i+1],'size="10" class="shipping_cost"') . '
						
						<span onClick="delete_tr_cost(this)"  class="remove-rate"></span>
						</td>
					</tr>';
	}
	
	$output = '<div id="id_nodesContent">
				<table border="0" cellspacing="0" cellpadding="0" class="shipping_cost">' . 
				'<tr class="shipping_cost"><td class="shipping_cost_heading" style="width:105px">'.TEXT_VALUE.'</td><td class="shipping_cost_heading">'.TEXT_COST.'</td></tr>'.
				'</table>'.
				'<div class="'.((strlen($shipping_cost_string) && ($i/2)>10)?'shipping_cost':'shipping_cost_small').'">'.
				'<table border="0" cellspacing="0" cellpadding="0" class="shipping_cost">' . 
				$output .  
			   '</table>
			   </div>'.
			   '<div id="rate_cost_' . $zone_table_id . '_'.$id.'"></div>'.
			   '<div class="shipping_cost_width">'.
			   '<input type="button" value="' . TEXT_ADD_MORE . '" onClick="add_row_cost(\'rate_cost_' . $zone_table_id . '_'.$id.'\',\'' .
			   
			   htmlspecialchars('<div class="shipping_cost2">'.
			   tep_draw_input_field('new_rate[' . $zone_table_id . '][' . $id . '][]', '','size="10" class="new_shipping_cost"') . ' ' . 
			   tep_draw_input_field('new_rate[' . $zone_table_id . '][' . $id . '][]', '','size="10" class="new_shipping_cost"') .
			   ' <span onClick="delete_row_cost(this)"  class="remove-rate"></span>'.
			   '</div>') 
			   
			   .'\')" class="btn">'.
			   '</div>
			   <div id="virtual"></div>
			   </div>'
			   ;
	
	return $output;
    }
}
