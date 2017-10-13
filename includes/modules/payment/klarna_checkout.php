<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
//ini_set('display_errors', 1);
class klarna_checkout extends ModulePayment{
    var $code, $title, $description, $enabled, $test_mode;

    // class constructor
    function __construct() {
        global $order;

        $this->code = 'klarna_checkout';

        $this->title = MODULE_PAYMENT_KLARNA_CHECKOUT_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_KLARNA_CHECKOUT_TEXT_DESCRIPTION;
        $this->enabled = ((MODULE_PAYMENT_KLARNA_CHECKOUT_STATUS == 'True') ? true : false);
        $this->update_status();
    }

    // class methods
    function update_status() {
        global $currency;
        
        $dependance = ['SEK' => ['SE'], 'EUR' => ['FI', 'DE', 'AT'], 'NOK' => ['NO'],];
        $country = \common\helpers\Country::get_country_info_by_id(STORE_COUNTRY);
        
        if (!array_key_exists($currency, $dependance)){
            $this->enabled = false;
        } else {
            if (!in_array($country['countries_iso_code_2'], $dependance[$currency])){
                $this->enabled = false;
            }
        }
        
    }

    function javascript_validation() {
        return false;
    }

    function selection() {
        
    }

    function pre_confirmation_check() {
        
    }

    function confirmation() {
        return array('title' => MODULE_PAYMENT_KLARNA_CHECKOUT_TEXT_CONFIRM_DESCRIPTION);
    }

    function process_button() {
        
    }

    function before_process() {
        
    }

    function after_process() {
        return false;
    }


    function get_error() {

        if (isset($_GET['message']) && strlen($_GET['message']) > 0) {
            $error = stripslashes(urldecode($_GET['message']));
        } else {
            $error = $_GET['error'];
        }
        return array('title' => html_entity_decode(KLARNA_LANG_SE_ERRORINVOICE),
                     'error' => $error);
    }
    
    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_PAYMENT_KLARNA_CHECKOUT_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_PAYMENT_KLARNA_CHECKOUT_SORT_ORDER');
    }
    
    function isOnline() {
        return false;
    }
    
    public function configure_keys() {

        tep_db_query("CREATE TABLE IF NOT EXISTS `klarna_order_reference` (`klarna_id` varchar(255) NOT NULL, `order_id` int(11) NOT NULL, PRIMARY KEY (`klarna_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $params = array('MODULE_PAYMENT_KLARNA_CHECKOUT_STATUS' => array('title' => 'Enable Klarna Module',
                                                                          'description' => 'Do you want to accept Klarna payments?',
                                                                          'value' => 'True',
                                                                          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_KLARNA_CHECKOUT_EID' => array('title' => 'Merchant ID',
                                                                               'description' => 'Merchant ID (estore id) to use for the Klarna service (provided by Klarna)'),
                      'MODULE_PAYMENT_KLARNA_CHECKOUT_SECRET' => array('title' => 'Shared secret',
                                                                                     'description' => 'Shared secret to use with the Klarna service (provided by Klarna).'),
                      'MODULE_PAYMENT_KLARNA_CHECKOUT_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                   'description' => 'Set the status of orders made with this payment module to this value',
                                                                                   'value' => '0',
                                                                                   'set_function' => 'tep_cfg_pull_down_order_statuses(',
                                                                                   'use_function' => '\\common\helpers\\Order::get_order_status_name'),
                      'MODULE_PAYMENT_KLARNA_CHECKOUT_SHIPPING_METHOD' => array('title' => 'Shipping Method',
                                                                               'description' => 'Set the default shipping method to be used',
                                                                               'value' => '0',
                                                                               'sort_order' => '0',
                                                                               'set_function' => 'tep_cfg_pull_down_shipping_modules(',
                                                                               'use_function' => 'tep_get_shipping_module_name',
                                                                               ),  
                      'MODULE_PAYMENT_KLARNA_CHECKOUT_TEST_MODE' => array('title' => 'Test Mode',
                                                                            'description' => 'Do you want to activate the Testmode?',
                                                                            'value' => 'False',
                                                                            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                        'MODULE_PAYMENT_KLARNA_CHECKOUT_CONDITIONS' => array('title' => 'Conditions',
                                                                            'description' => 'URL with condition details',),
                                                                            );

      return $params;        
    }

}

function tep_cfg_pull_down_shipping_modules($shipping_module_code, $key = '') {

    $shipping_modules = new \common\classes\shipping;
    $include_modules = $shipping_modules->getIncludedModules();
    $modules_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
 		  if (\frontend\design\Info::isTotallyAdmin()){
			include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
		  } else {
			include_once(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
		  }
          $class = new $include_modules[$i]['class'];
          if (is_object($class)) {
                $module = new $class;
                $modules_array[] = array(
                    'id' => $module->code,
                    'text' => $module->title
                );
          }
    }
    
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    return tep_draw_pull_down_menu($name, $modules_array, $shipping_module_code);
}

  function tep_get_shipping_module_name($shipping_module_code, $language_id = '') {
    global $cfgModules, $language;
    
    if (is_numeric($shipping_module_code)) return TEXT_DEFAULT;

    $shipping_modules = new \common\classes\shipping;
    
    if (class_exists($shipping_module_code)){
        $module = new $shipping_module_code;
        return $module->title;
    }
    return '';

  }

?>