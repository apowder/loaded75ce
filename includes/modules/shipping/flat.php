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

  class flat extends ModuleShipping{
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function __construct() {
      global $order;

      $this->code = 'flat';
      $this->title = MODULE_SHIPPING_FLAT_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FLAT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_FLAT_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_FLAT_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FLAT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

// class methods
    function quote($method = '') {
      global $order;

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_FLAT_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => MODULE_SHIPPING_FLAT_TEXT_WAY,
                                                     'cost' => MODULE_SHIPPING_FLAT_COST)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      return $this->quotes;
    }

    public function configure_keys() {
      return array(
        'MODULE_SHIPPING_FLAT_STATUS' => array(
          'title' => 'Enable Flat Shipping',
          'value' => 'True',
          'description' => 'Do you want to offer flat rate shipping?',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_SHIPPING_FLAT_COST' => array(
          'title' => 'Flat Shipping Cost',
          'value' => '5.00',
          'description' => 'The shipping cost for all orders using this shipping method.',
          'sort_order' => '0',
        ),
        'MODULE_SHIPPING_FLAT_TAX_CLASS' => array(
          'title' => 'Flat Tax Class',
          'value' => '0',
          'description' => 'Use the following tax class on the shipping fee.',
          'sort_order' => '0',
          'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
          'set_function' => 'tep_cfg_pull_down_tax_classes(',
        ),
        'MODULE_SHIPPING_FLAT_ZONE' => array(
          'title' => 'Flat Shipping Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this shipping method for that zone.',
          'sort_order' => '0',
          'use_function' => '\\\\common\\\\helpers\\\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_SHIPPING_FLAT_SORT_ORDER' => array(
          'title' => 'Flat Sort Order',
          'value' => '0',
          'description' => 'Sort order of display.',
          'sort_order' => '0',
        ),
      );
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_FLAT_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_FLAT_SORT_ORDER');
    }

}
