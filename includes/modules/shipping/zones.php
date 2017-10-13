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

  class zones extends ModuleShipping{
    var $code, $title, $description, $enabled, $num_zones;

// class constructor
    function __construct() {
      $this->code = 'zones';
      $this->title = MODULE_SHIPPING_ZONES_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_ZONES_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_ZONES_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_ZONES_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_ZONES_STATUS == 'True') ? true : false);

      // CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
      $this->num_zones = 1;
    }

// class methods
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes;
      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_zones; $i++) {
        $countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $i);
        $country_zones = explode(",", $countries_table);
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }


      if ($dest_zone == 0) {
        $error = true;
      } else {
        $shipping = -1;
        $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $dest_zone);

        $zones_table = preg_split("/[:,]/" , $zones_cost);
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shipping_weight <= $zones_table[$i]) {
            $shipping = $zones_table[$i+1];
            $shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $dest_country . ' : ' . $shipping_weight . ' ' . MODULE_SHIPPING_ZONES_TEXT_UNITS;
            break;
          }
        }

        if ($shipping == -1) {
          $shipping_cost = 0;
          $shipping_method = MODULE_SHIPPING_ZONES_UNDEFINED_RATE;
        } else {
          $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
        }
      }

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_ZONES_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $shipping_cost)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_ZONES_INVALID_ZONE;

      return $this->quotes;
    }

    protected function get_install_keys($platform_id)
    {
      $keys = $this->configure_keys();

      $prefill_values = array(
        'MODULE_SHIPPING_ZONES_COUNTRIES_1' => 'US,CA',
        'MODULE_SHIPPING_ZONES_COST_1' => '3:8.50,7:10.50,99:20.00',
      );

      foreach( $prefill_values as $_key=>$_value ) {
        if ( isset($keys[$_key]) ) {
          $keys[$_key]['value'] = $_value;
        }
      }

      return $keys;
    }

    public function configure_keys()
    {
      $config = array (
        'MODULE_SHIPPING_ZONES_STATUS' =>
          array (
            'title' => 'Zones Enable Zones Method',
            'value' => 'True',
            'description' => 'Do you want to offer zone rate shipping?',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
          ),
        'MODULE_SHIPPING_ZONES_TAX_CLASS' =>
          array (
            'title' => 'Zones Tax Class',
            'value' => '0',
            'description' => 'Use the following tax class on the shipping fee.',
            'sort_order' => '0',
            'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
            'set_function' => 'tep_cfg_pull_down_tax_classes(',
          ),
        'MODULE_SHIPPING_ZONES_SORT_ORDER' =>
          array (
            'title' => 'Zones Sort Order',
            'value' => '0',
            'description' => 'Sort order of display.',
            'sort_order' => '0',
          ),
      );
      for ($i = 1; $i <= $this->num_zones; $i ++) {
        $config['MODULE_SHIPPING_ZONES_COUNTRIES_'.$i] = array(
          'title' => 'Zone ' . $i .' Countries',
          'value' => '',
          'description' => 'Comma separated list of two character ISO country codes that are part of Zone '.$i.'.',
          'sort_order' => '0',
        );
        $config['MODULE_SHIPPING_ZONES_COST_'.$i] = array(
          'title' => 'Zone ' . $i. ' Shipping Table',
          'value' => '',
          'description' => 'Shipping rates to Zone ' . $i . ' destinations based on a group of maximum order weights. Example: 3:8.50,7:10.50,... Weights less than or equal to 3 would cost 8.50 for Zone ' . $i . ' destinations.',
          'sort_order' => '0',
        );
        $config['MODULE_SHIPPING_ZONES_HANDLING_'.$i] = array(
          'title' => 'Zone ' . $i .' Handling Fee',
          'value' => '0',
          'description' => 'Handling Fee for this shipping zone',
          'sort_order' => '0',
        );
      }
      return $config;
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_ZONES_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_ZONES_SORT_ORDER');
    }

  }
