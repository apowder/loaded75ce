<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace app\components;

use Yii;
use \common\models\SessionFlow;
use \frontend\design\Info;

class InitFactory {
  
  public static function init(){
    global $session_started, $request_type, $ssl_session_id, $http_user_agent, $mysql_error_dump, $device, $SID, $user_agent;
    global $customer_id, $customer_groups_id;
    global $currencies, $lng, $language, $languages_id, $locale, $breadcrumb, $navigation, $messageStack, $messageToStack;
    global $currency_id, $currency, $cPath, $cPath_array, $platform_code;
    global $payment, $shipping, $sendto, $billto, $comments, $credit_covers, $cartID;

    /*init session from appication_top*/

    if ( ($session_started == true) && (PHP_VERSION >= 4.3) && function_exists('ini_get') && (ini_get('register_globals') == false || ini_get('register_globals') == "Off") )
    {
     if (is_array($_SESSION)){
        $skip = ['language', 'shipping', 'payment'];
        extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
        foreach($_SESSION as $_key => $_item){
          global $$_key;
          $GLOBALS[$_key] = &$_SESSION[$_key];
        }
      }
     
    }

     if (!tep_session_is_registered('platform_code')) {
         tep_session_register('platform_code'); 
         $platform_code = '';
     }
     if (isset($_GET['code']) && $_GET['code'] == 'WBUKGOOGLE') {//test on google
         $platform_code = $_GET['code'];
     }

    
    // set SID once, even if empty
    $SID = (defined('SID') ? SID : '');

  // verify the ssl_session_id if the feature is enabled
    if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true) ) {
      $ssl_session_id = getenv('SSL_SESSION_ID');
      if (!tep_session_is_registered('SSL_SESSION_ID')) {
        $SESSION_SSL_ID = $ssl_session_id;
        tep_session_register('SESSION_SSL_ID');
      }

      if ($SESSION_SSL_ID != $ssl_session_id) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
      }
    }
    
    if (tep_session_is_registered('customer_id') && $customer_id > 0) {
      if (!\common\helpers\Customer::is_customer_exist($customer_id)) {
        tep_session_unregister('customer_id');
        tep_session_unregister('customer_default_address_id');
        tep_session_unregister('customer_first_name');
        tep_session_unregister('customer_country_id');
        tep_session_unregister('customer_zone_id');
        tep_session_unregister('comments');
        tep_session_unregister('customer_groups_id');
        tep_session_unregister('cart_address_id');
        tep_session_unregister('gv_id');
        tep_session_unregister('cc_id');
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      }
    }

  // verify the browser user agent if the feature is enabled
    if (SESSION_CHECK_USER_AGENT == 'True') {
      $http_user_agent = getenv('HTTP_USER_AGENT');
      if (!tep_session_is_registered('SESSION_USER_AGENT')) {
        $SESSION_USER_AGENT = $http_user_agent;
        tep_session_register('SESSION_USER_AGENT');
      }

      if ($SESSION_USER_AGENT != $http_user_agent) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      }
    }

    if (!tep_session_is_registered('customer_groups_id')) {
      $customer_groups_id = $_SESSION['customer_groups_id'] = DEFAULT_USER_GROUP;
    }

  // verify the IP address if the feature is enabled
    if (SESSION_CHECK_IP_ADDRESS == 'True') {
      $ip_address = \common\helpers\System::get_ip_address();
      if (!tep_session_is_registered('SESSION_IP_ADDRESS')) {
        $SESSION_IP_ADDRESS = $ip_address;
        tep_session_register('SESSION_IP_ADDRESS');
      }

      if ($SESSION_IP_ADDRESS != $ip_address) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      }
    }

    if (SEARCH_ENGINE_STATS == 'True') {
      \common\helpers\System::referer_stat();
    }   

  // mysql error
    if(!tep_session_is_registered('mysql_error_dump')) {
      $mysql_error_dump = array();
      tep_session_register('mysql_error_dump');
      if(count($mysql_errors) > 0) {
        $mysql_error_dump = $mysql_errors;
      }
    }
    else {
      if( count($mysql_errors) > 0) {
        if(count($mysql_error_dump == 0)) {
          $mysql_error_dump = $mysql_errors;
        }
        else {
          $mysql_error_dump = array_merge($mysql_error_dump, $mysql_errors);
        }
      }
    }

    $currencies = new \common\classes\currencies();
    
    $lng = new \common\classes\language();

    if (!tep_session_is_registered('languages_id') || (isset($_GET['language']) ) ) {
      if (!tep_session_is_registered('languages_id')) {
        tep_session_register('languages_id');
        tep_session_register('language');
      }
      if (!tep_session_is_registered('locale')){
        tep_session_register('locale');
      }
      
      if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
        $lng->set_language($_GET['language']);
      } else {
        $lng->get_browser_language();
      }

      $language = $_SESSION['language'] = $lng->language['directory'];
      $languages_id = $_SESSION['languages_id'] = $lng->language['id'];
      $locale = $_SESSION['locale'] = $lng->language['locale'];

    }else{
      $language = $_SESSION['language'];
      $languages_id = $_SESSION['languages_id'];
      $locale = $_SESSION['locale'];
    }

    $lng->set_locale();
    $lng->load_vars();  

    $breadcrumb = new \common\classes\breadcrumb;

  // currency
    if (tep_session_is_registered('currency') && in_array($_SESSION['currency'], $currencies->platform_currencies))
    {
      $currency = $_SESSION['currency'];
    } else {
      $currency = null;
      $currency_id = 0;
    }
    if (tep_session_is_registered('currency'))
    {
      $currency_id = $_SESSION['currency_id'];
    }  

    if (is_null($currency) || !tep_session_is_registered('currency') || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $currency) ) ) {
      if (!tep_session_is_registered('currency')) tep_session_register('currency');

      if (isset($_GET['currency'])){
        $_maybe_currency = $_GET['currency'];
      } elseif (USE_DEFAULT_LANGUAGE_CURRENCY == 'true'){
        $_maybe_currency = LANGUAGE_CURRENCY;
      }
      if (!in_array($_maybe_currency, $currencies->platform_currencies) || is_null($_maybe_currency)){
        $currency = $currencies->dp_currency;
      } else {
        $currency = $_maybe_currency;
      }
      $currency_id = 0;
    }
  //currency_id
    if (!$currency_id || !tep_session_is_registered('currency_id') || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $currency) )) {
      if (!tep_session_is_registered('currency_id')) tep_session_register('currency_id');
      $currency_id = $currencies->currencies[$currency]['id'];
    }
    $_SESSION['currency_id'] = $currency_id;
    $_SESSION['currency'] = $currency;

    // navigation history
    if (!tep_session_is_registered('navigation'))
    {
      tep_session_register('navigation');    
      $navigation = new \common\classes\navigation();
    }
    if (is_object($navigation) && method_exists($navigation, 'add_current_page')){
      $navigation->add_current_page();
    }   

    // initialize the message stack for output messages

    $messageStack = new \common\classes\message_stack;

  // BOF: Down for Maintenance except for admin ip
  if (EXCLUDE_ADMIN_IP_FOR_MAINTENANCE != getenv('REMOTE_ADDR')) {
    if (DOWN_FOR_MAINTENANCE == 'true' and !strstr($PHP_SELF, DOWN_FOR_MAINTENANCE_FILENAME)) { 
      tep_redirect(tep_href_link(DOWN_FOR_MAINTENANCE_FILENAME)); 
    }
  }
  // do not let people get to down for maintenance page if not turned on
  if (DOWN_FOR_MAINTENANCE=='false' and strstr($PHP_SELF,DOWN_FOR_MAINTENANCE_FILENAME)) {
      tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }
  // EOF: WebMakers.com Added: Down for Maintenance

  require(DIR_WS_INCLUDES . 'add_ccgvdc_application_top.php');

  /***
  * actions moved to \frontend\models\Cartfactory
  ***/

  self::tep_update_whos_online();
  self::tep_expire_specials();
  self::tep_check_selemaker();
  self::tep_expire_featured();

  /// 
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (stripos($user_agent, 'iphone') !== false ||
        stripos($user_agent, 'ipod') !== false ||
        stripos($user_agent, 'blackberry') !== false ||
        (stripos($user_agent, 'BB10') !== false && stripos($user_agent, 'Mobile') !== false) ||
        stripos($user_agent, 'NOKIA') !== false ||
        (stripos($user_agent, 'android') !== false && stripos($user_agent, 'mobile') !== false)) {
      if ((isset($_GET['r']) && $_GET['r'] == 'fullsite' ) || ($_SESSION['device'] == 'pc')) {
        $device = 'pc';
      } else {
        $device = 'mobile';
      }
    } elseif (stripos($user_agent, 'ipad') !== false ||
              stripos($user_agent, 'android') !== false) {
      $device = 'tablet';
    } else {
      $device = 'pc';
    }
    if (!tep_session_is_registered('device')) tep_session_register('device');

    if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkCustomerGroups')) {
        $ext::checkCustomerGroups();
    }
    define('GROUPS_IS_SHOW_PRICE', true);
    define('GROUPS_DISABLE_CHECKOUT', false);

    global $Blog;
    $Blog = new \common\classes\Blog;
    
    if ($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_track_reference')){
        $ext::rf_track_reference();
    }
    
    return;
  }
  
  public static function tep_update_whos_online() {
    global $customer_id;

    if (tep_session_is_registered('customer_id')) {
      $wo_customer_id = $customer_id;

      $customer_query = tep_db_query("select customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
      $customer = tep_db_fetch_array($customer_query);

      $wo_full_name = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
    } else {
      $wo_customer_id = '';
      $wo_full_name = 'Guest';
    }

    $wo_session_id = tep_session_id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = getenv('REQUEST_URI');

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

    // remove entries that have expired
    tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_customer_query = tep_db_query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where session_id = '" . tep_db_input($wo_session_id) . "'");
    $stored_customer = tep_db_fetch_array($stored_customer_query);

    if ($stored_customer['count'] > 0) {
      tep_db_query("update " . TABLE_WHOS_ONLINE . " set customer_id = '" . (int)$wo_customer_id . "', full_name = '" . tep_db_input($wo_full_name) . "', ip_address = '" . tep_db_input($wo_ip_address) . "', time_last_click = '" . tep_db_input($current_time) . "', last_page_url = '" . tep_db_input($wo_last_page_url) . "', platform_id = '" . (int)PLATFORM_ID . "' where session_id = '" . tep_db_input($wo_session_id) . "'");
    } else {
      tep_db_query("insert into " . TABLE_WHOS_ONLINE . " (customer_id, full_name, session_id, ip_address, time_entry, time_last_click, last_page_url, platform_id) values ('" . (int)$wo_customer_id . "', '" . tep_db_input($wo_full_name) . "', '" . tep_db_input($wo_session_id) . "', '" . tep_db_input($wo_ip_address) . "', '" . tep_db_input($current_time) . "', '" . tep_db_input($current_time) . "', '" . tep_db_input($wo_last_page_url) . "', '" . (int)PLATFORM_ID . "')");
    }
  }
  
  public static function tep_expire_specials() {
    tep_db_query("update " . TABLE_SPECIALS . " set status = 0, date_status_change = now() where status = '1' and now() >= expires_date and expires_date > 0");
  }

  public static function tep_check_selemaker(){
    Global $customer_groups_id, $salemaker_array;
    $salemaker_array = array();
    $salemaker_query = tep_db_query("select sale_specials_condition, sale_deduction_value, sale_deduction_type, sale_categories_all, sale_pricerange_from, sale_pricerange_to from " . TABLE_SALEMAKER_SALES . " where sale_status = '1' and groups_id = '" . (int)$customer_groups_id . "' and (sale_date_start <= now() or sale_date_start = '0000-00-00') and (sale_deduction_value > 0) and (sale_date_end >= now() or sale_date_end = '0000-00-00') ");
    while ($salemaker_data = tep_db_fetch_array($salemaker_query)){
      $salemaker_array[] = array('sale_specials_condition' => $salemaker_data['sale_specials_condition'],
      'sale_deduction_value' => $salemaker_data['sale_deduction_value'],
      'sale_deduction_type' => $salemaker_data['sale_deduction_type'],
      'sale_pricerange_from' => $salemaker_data['sale_pricerange_from'],
      'sale_pricerange_to' => $salemaker_data['sale_pricerange_to'],
      'sale_categories_all' => (tep_not_null($salemaker_data['sale_categories_all'])?explode(",", trim($salemaker_data['sale_categories_all'], " ,")):""));
    }
  }
  
  public static  function tep_expire_featured() {
    tep_db_query("update " . TABLE_FEATURED . " set status = 0, date_status_change = now() where status = '1' and now() >= expires_date and expires_date > 0");
  }
  
}