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

  class freeshipper extends ModuleShipping{
    var $code, $title, $description, $icon, $enabled;

// BOF: WebMakers.com Added: Free Payments and Shipping
// class constructor
    function __construct() {
      global $order, $cart;
      $this->code = 'freeshipper';
      $this->title = MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FREESHIPPER_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_FREESHIPPER_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_free_shipper.jpg';
      $this->tax_class = MODULE_SHIPPING_FREESHIPPER_TAX_CLASS;
      $this->enabled = (defined('MODULE_SHIPPING_FREESHIPPER_STATUS') && (MODULE_SHIPPING_FREESHIPPER_STATUS == 'True'));

// Only show if weight is 0
//      if ( (!strstr($PHP_SELF,'modules.php')) || $cart->show_weight()==0) {

        if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FREESHIPPER_ZONE > 0) ) {
          $check_flag = false;
          $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FREESHIPPER_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
//      }
// EOF: WebMakers.com Added: Free Payments and Shipping
    }

// class methods
    function quote($method = '') {
      global $order;

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => '<FONT COLOR=FF0000><B>' . MODULE_SHIPPING_FREESHIPPER_TEXT_WAY . '</B></FONT>',
                                                     'cost' => SHIPPING_HANDLING + MODULE_SHIPPING_FREESHIPPER_COST)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }
      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      return $this->quotes;
    }

    public function configure_keys()
    {
      return array(
        'MODULE_SHIPPING_FREESHIPPER_STATUS' => array(
          'title' => 'Enable Free Shipping',
          'value' => 'True',
          'description' => 'Do you want to offer Free shipping?',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_SHIPPING_FREESHIPPER_COST' => array(
          'title' => 'Free Shipping Cost',
          'value' => '0.00',
          'description' => 'What is the Shipping cost? The Handling fee will also be added.',
          'sort_order' => '6',
        ),
        'MODULE_SHIPPING_FREESHIPPER_TAX_CLASS' => array(
          'title' => 'FREESHIPPER Tax Class',
          'value' => '0',
          'description' => 'Use the following tax class on the shipping fee.',
          'sort_order' => '0',
          'use_function' => '\\\\common\\\\helpers\\\\Tax::get_tax_class_title',
          'set_function' => 'tep_cfg_pull_down_tax_classes(',
        ),
        'MODULE_SHIPPING_FREESHIPPER_ZONE' => array(
          'title' => 'FREESHIPPER Shipping Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this shipping method for that zone.',
          'sort_order' => '0',
          'use_function' => '\\\\common\\\\helpers\\\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER' => array(
          'title' => 'FREESHIPPER Sort Order',
          'value' => '0',
          'description' => 'Sort order of display.',
          'sort_order' => '0',
        ),
      );
    }
    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_FREESHIPPER_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_FREESHIPPER_SORT_ORDER');
    }

  }
