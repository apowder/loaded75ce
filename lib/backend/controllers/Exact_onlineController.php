<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;

class Exact_onlineController extends Sceleton {

  public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_EXACT_ONLINE'];

  public function actionIndex() {
    global $languages_id, $language, $messageStack;

    $this->selectedMenu = array('settings', 'exact_online');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('exact_online/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    $check = tep_db_fetch_array(tep_db_query("select configuration_group_id from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_title = 'Exact Online'"));
    if ( !($check['configuration_group_id'] > 0) ) {
      tep_db_query("insert into " . TABLE_CONFIGURATION_GROUP . " (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible) values (null, 'Exact Online', 'Exact Online', 5555, 0)");
      $configuration_group_id = tep_db_insert_id();
    } else {
      $configuration_group_id = $check['configuration_group_id'];
    }
    if (!defined('EXACT_BASE_URL')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, '" . tep_db_input(TEXT_EXACT_BASE_URL) . "', 'EXACT_BASE_URL', 'https://start.exactonline.nl', '', " . (int)$configuration_group_id . ", 1, null, now())");
      define('EXACT_BASE_URL', 'https://start.exactonline.nl');
    }
    if (!defined('EXACT_CLIENT_ID')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, '" . tep_db_input(TEXT_EXACT_CLIENT_ID) . "', 'EXACT_CLIENT_ID', '', '', " . (int)$configuration_group_id . ", 11, null, now())");
      define('EXACT_CLIENT_ID', '');
    }
    if (!defined('EXACT_CLIENT_SECRET')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, '" . tep_db_input(TEXT_EXACT_CLIENT_SECRET) . "', 'EXACT_CLIENT_SECRET', '', '', " . (int)$configuration_group_id . ", 22, null, now())");
      define('EXACT_CLIENT_SECRET', '');
    }
    if (!defined('EXACT_REFRESH_TOKEN')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Refresh Token', 'EXACT_REFRESH_TOKEN', '', '', " . (int)$configuration_group_id . ", 33, null, now())");
      define('EXACT_REFRESH_TOKEN', '');
    }
    if (!defined('EXACT_CURRENT_DIVISION')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Current Division', 'EXACT_CURRENT_DIVISION', '', '', " . (int)$configuration_group_id . ", 44, null, now())");
      define('EXACT_CURRENT_DIVISION', '');
    }
    if (!defined('EXACT_DIVISIONS_LIST')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Divisions List', 'EXACT_DIVISIONS_LIST', '', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_DIVISIONS_LIST', '');
    }
    if (!defined('EXACT_CONNECTOR_STATUS')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
(null, 'Exact Connector Status', 'EXACT_CONNECTOR_STATUS', 'False', 'True - enable cron scripts run, False - disable', " . (int)$configuration_group_id . ", 0, null, now(), null, 'tep_cfg_select_option(array(''True'', ''False''),')");
      define('EXACT_CONNECTOR_STATUS', 'False');
    }
    if (!defined('EXACT_NEXT_ITEMS_URL')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Next Items URL', 'EXACT_NEXT_ITEMS_URL', '', '', " . (int)$configuration_group_id . ", 55, null, now())");
      define('EXACT_NEXT_ITEMS_URL', '');
    }
    if (!defined('EXACT_ITEMS_IDS')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Items IDs', 'EXACT_ITEMS_IDS', '', '', " . (int)$configuration_group_id . ", 55, null, now())");
      define('EXACT_ITEMS_IDS', '');
    }
// {{
    if (!defined('EXACT_ORDERNUMBER_SHIFT')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Order Number Shift', 'EXACT_ORDERNUMBER_SHIFT', '100000', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_ORDERNUMBER_SHIFT', '100000');
    }
    if (!defined('EXACT_PAYMENT_MAP')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Payment Modules Map', 'EXACT_PAYMENT_MAP', '', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_PAYMENT_MAP', '');
    }
    if (!defined('EXACT_SHIPPING_MAP')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Shipping Modules Map', 'EXACT_SHIPPING_MAP', '', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_SHIPPING_MAP', '');
    }
    if (!defined('EXACT_DESCRIPTION_FIELD')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Divisions List', 'EXACT_DESCRIPTION_FIELD', 'Notes', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_DESCRIPTION_FIELD', 'Notes');
    }
    $description_fields_array = array(array('id' => 'Notes', 'text' => 'Remarks (Notes)'), array('id' => 'ExtraDescription', 'text' => 'Extra Description'));

    if (!defined('EXACT_ORDER_STATUSES_SYNCED')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values (null, 'Exact Order Statuses Synced', 'EXACT_ORDER_STATUSES_SYNCED', '3,4', 'Sync orders of the following statuses to Exact Online.', " . (int)$configuration_group_id . ", 55, null, now(), 'tep_get_status_name', 'tep_cfg_select_download_status(')");
      define('EXACT_ORDER_STATUSES_SYNCED', '3,4');
    }
    if (!defined('EXACT_0_VAT_CODE')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact 0% VAT code', 'EXACT_0_VAT_CODE', '0', '', " . (int)$configuration_group_id . ", 45, null, now())");
      define('EXACT_0_VAT_CODE', '0');
    }
    if (!defined('EXACT_AUTH_ERROR_COUNT')) {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) values (null, 'Exact Authorization Error Count', 'EXACT_AUTH_ERROR_COUNT', '0', '', " . (int)$configuration_group_id . ", 75, null, now())");
      define('EXACT_AUTH_ERROR_COUNT', '0');
    }
// }}

    if (is_array($divisions_list = @unserialize(EXACT_DIVISIONS_LIST))) {
      $divisions_array = array();
      foreach ($divisions_list as $division_id => $division_name) {
        $divisions_array[] = array('id' => $division_id, 'text' => $division_name);
      }
    }

    $exact_cron_array = array();
    $exact_cron_query = tep_db_query("select exact_crons_id, exact_crons_name, exact_crons_function, schedule_every_minutes, schedule_last_started from " . TABLE_EXACT_CRONS . " where 1");
    while ($exact_cron = tep_db_fetch_array($exact_cron_query)) {
      $exact_cron_array[] = $exact_cron;
    }

    $intervals_array = array(array('id' => '0', 'text' => TEXT_RUN_MANUAL),
                             array('id' => '10', 'text' => sprintf(TEXT_RUN_EVERY_XX_MIN, '10')),
                             array('id' => '20', 'text' => sprintf(TEXT_RUN_EVERY_XX_MIN, '20')),
                             array('id' => '30', 'text' => sprintf(TEXT_RUN_EVERY_XX_MIN, '30')),
                             array('id' => '60', 'text' => sprintf(TEXT_RUN_EVERY_XX_HOUR, '1')),
                             array('id' => '120', 'text' => sprintf(TEXT_RUN_EVERY_XX_HOUR, '2')),
                             array('id' => '180', 'text' => sprintf(TEXT_RUN_EVERY_XX_HOUR, '3')),
                             array('id' => '240', 'text' => sprintf(TEXT_RUN_EVERY_XX_HOUR, '4')));

// {{
    $file_extension = '.php';
    $platforms_list_array = array();
    foreach (\common\classes\platform::getList(false) as $platform) {
      $platforms_list_array[$platform['id']] = $platform['text'];
      Yii::$app->get('platform')->config($platform['id'])->constant_up();

      $module_type = 'payment';
      $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
      require_once(DIR_WS_CLASSES . 'payment.php');
      $payment_modules = new \payment;

      $directory_array = array();
      if ($dir = @dir($module_directory)) {
        while ($file = $dir->read()) {
          if (!is_dir($module_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
              $directory_array[] = $file;
            }
          }
        }
        sort($directory_array);
        $dir->close();
      }
      $installed_payment_modules[$platform['id']] = array();
      for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
        $file = $directory_array[$i];
        include_once($module_directory . $file);
        $class = substr($file, 0, strrpos($file, '.'));
        if (class_exists($class)) {
          $module_class = new $class;
          if (!tep_not_null($module_class->code) && !tep_not_null($module_class->title)) continue;
          if ($module_class->check($platform['id']) > 0) {
            $installed_payment_modules[$platform['id']][$module_class->code] = $module_class->title;
          }
        }
      }
      asort($installed_payment_modules[$platform['id']]);

      $module_type = 'shipping';
      $module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
      require_once(DIR_WS_CLASSES . 'shipping.php');
      $shipping_modules = new \shipping;

      $directory_array = array();
      if ($dir = @dir($module_directory)) {
        while ($file = $dir->read()) {
          if (!is_dir($module_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
              $directory_array[] = $file;
            }
          }
        }
        sort($directory_array);
        $dir->close();
      }
      $installed_shipping_modules[$platform['id']] = array();
      for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
        $file = $directory_array[$i];
        include_once($module_directory . $file);
        $class = substr($file, 0, strrpos($file, '.'));
        if (class_exists($class)) {
          $module_class = new $class;
          if (!tep_not_null($module_class->code) && !tep_not_null($module_class->title)) continue;
          if ($module_class->check($platform['id']) > 0) {
            $installed_shipping_modules[$platform['id']][$module_class->code] = $module_class->title;
          }
        }
      }
      asort($installed_shipping_modules[$platform['id']]);
    }
// }}

    if ($messageStack->size > 0) {
      $this->view->errorMessage = $messageStack->output(true);
      $this->view->errorMessageType = $messageStack->messageType;
    }

    return $this->render('index', array('divisions_array' => $divisions_array,
                                        'description_fields_array' => $description_fields_array,
                                        'platforms_list_array' => $platforms_list_array,
                                        'installed_payment_modules' => $installed_payment_modules,
                                        'installed_shipping_modules' => $installed_shipping_modules,
                                        'payment_map' => @unserialize(EXACT_PAYMENT_MAP),
                                        'shipping_map' => @unserialize(EXACT_SHIPPING_MAP),
                                        'order_statuses_array' => \common\helpers\Order::get_status(),
                                        'exact_cron_array' => $exact_cron_array,
                                        'intervals_array' => $intervals_array));
  }

  public function actionUpdate() {
    global $HTTP_POST_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    $in_data = tep_db_prepare_input($HTTP_POST_VARS);
    foreach($in_data as $inkey => $invalue) {
      if (in_array($inkey, array('EXACT_BASE_URL', 'EXACT_CLIENT_ID', 'EXACT_CLIENT_SECRET', 'EXACT_ORDERNUMBER_SHIFT', 'EXACT_CURRENT_DIVISION', 'EXACT_DESCRIPTION_FIELD', 'EXACT_0_VAT_CODE', 'EXACT_ORDER_STATUSES_SYNCED', 'EXACT_CONNECTOR_STATUS'))) {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(is_array($invalue) ? implode(',', $invalue) : $invalue) . "', last_modified = now() where configuration_key = '" . tep_db_input($inkey) . "'");
      } elseif ($inkey == 'schedule_every_minutes') {
        if (is_array($invalue)) foreach($invalue as $id => $minutes) {
          tep_db_query("update " . TABLE_EXACT_CRONS . " set schedule_every_minutes = '" . (int)$minutes . "', last_modified = now() where exact_crons_id = '" . (int)$id . "'");
        }
      } elseif ($inkey == 'payment_map') {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(serialize($invalue)) . "', last_modified = now() where configuration_key = 'EXACT_PAYMENT_MAP'");
      } elseif ($inkey == 'shipping_map') {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(serialize($invalue)) . "', last_modified = now() where configuration_key = 'EXACT_SHIPPING_MAP'");
      }
    }

    $messageStack->add_session(TEXT_SUCCESS_EXACT_DATA_UPDATED, 'success');

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionRun() {
    global $HTTP_GET_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    require(DIR_WS_FUNCTIONS . 'exact.php');

    $check = tep_db_fetch_array(tep_db_query("select exact_crons_id, exact_crons_function, if((is_locked > 0 && (schedule_last_started + interval " . (int)PRODUCTS_CRON_LOCK_TIME . " minute) > now()), 1, 0) do_not_run from " . TABLE_EXACT_CRONS . " where exact_crons_function = '" . tep_db_input($HTTP_GET_VARS['feed']) . "'"));
    if ($check['exact_crons_id'] > 0 && function_exists($check['exact_crons_function'])) {
      //check if cron is runing here
      if ($check['do_not_run'] > 0) {
        $messageStack->add_session(TEXT_ERROR_ANOTHER_THREAD_RUNING, 'error');
        tep_redirect(tep_href_link('exact_online'));
      }

      tep_db_query("update " . TABLE_EXACT_CRONS . " set schedule_last_started = now(), is_locked = 1,
        schedule_next_started = '" . tep_db_input(get_schedule_next_started($check['exact_crons_id'])) . "'
        where exact_crons_id = '" . tep_db_input($check['exact_crons_id']) . "'");

      $message = call_user_func($check['exact_crons_function']);

      tep_db_query("update " . TABLE_EXACT_CRONS . " set is_locked = 0,
        schedule_next_started = '" . tep_db_input(get_schedule_next_started($check['exact_crons_id'])) . "'
        where exact_crons_id = '" . tep_db_input($check['exact_crons_id']) . "'");
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionProducts() {
    global $HTTP_GET_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    require(DIR_WS_FUNCTIONS . 'exact.php');

    $message = exact_run_products();

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionStock() {
    global $HTTP_GET_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    require(DIR_WS_FUNCTIONS . 'exact.php');

    $message = exact_run_products_qty();

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOrders() {
    global $HTTP_GET_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    require(DIR_WS_FUNCTIONS . 'exact.php');

    $message = exact_run_orders();

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOauth() {
    global $HTTP_GET_VARS, $messageStack;
    \common\helpers\Translation::init('admin/exact_online');

    require(DIR_WS_FUNCTIONS . 'exact.php');

    $redirect_uri = str_replace('?' . SID, '', tep_href_link('exact_online/oauth'));
    if (!tep_not_null($HTTP_GET_VARS['code'])) {
//      tep_redirect(EXACT_BASE_URL . '/api/oauth2/auth?client_id=' .urlencode(EXACT_CLIENT_ID) . '&response_type=code&redirect_uri=' . urlencode($redirect_uri));
      tep_redirect(SUPERADMIN_HTTP_URL . 'rest?dID=' . DEPARTMENTS_ID . '&action=exact&http=' . str_replace('http://', '', HTTP_SERVER) . '&redirect_uri=' . urlencode($redirect_uri) . '&sID=' . tep_session_id());
    } else {
      $params = array(
        'code' => $HTTP_GET_VARS['code'],
//        'redirect_uri' => $redirect_uri,
        'redirect_uri' => $HTTP_GET_VARS['redirect_uri'],
        'grant_type' => 'authorization_code',
        'client_id' => EXACT_CLIENT_ID,
        'client_secret' => EXACT_CLIENT_SECRET,
      );
      $response = exact_call_http_url(EXACT_BASE_URL . '/api/oauth2/token', $params);
      $result = json_decode($response);

      if (is_object($result) && tep_not_null($result->refresh_token)) {
        $access_token = $result->access_token;
        tep_db_query("alter table " . TABLE_CONFIGURATION . " change configuration_value configuration_value text not null");
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->refresh_token) . "', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
        $messageStack->add_session(TEXT_SUCCESS_AUTHORIZATION_TOKENS, 'success');
      } else {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
        $messageStack->add_session(TEXT_ERROR_AUTHORIZATION_TOKENS, 'error');
        tep_redirect(tep_href_link('exact_online'));
      }

      $headers = array(
        'Accept: Application/json',
        'Content-Type: Application/json',
        'Authorization: Bearer ' . $access_token
      );
      $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/current/Me', array(), $headers);
      $result = json_decode($response);

      if (is_object($result) && is_object($result->d->results[0])) {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->d->results[0]->CurrentDivision) . "', last_modified = now() where configuration_key = 'EXACT_CURRENT_DIVISION'");
        $messageStack->add_session(TEXT_SUCCESS_CURRENT_DIVISION, 'success');
      } else {
        $messageStack->add_session(TEXT_ERROR_CURRENT_DIVISION, 'error');
      }

      $headers = array(
        'Accept: Application/json',
        'Content-Type: Application/json',
        'Authorization: Bearer ' . $access_token
      );
      $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/hrm/Divisions', array(), $headers);
      $result = json_decode($response);

      if (is_array($result->d->results)) {
        $DivisionsList = array();
        foreach ($result->d->results as $division) {
          $DivisionsList[$division->Code] = $division->HID . ' - ' . $division->Description;
        }
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(serialize($DivisionsList)) . "', last_modified = now() where configuration_key = 'EXACT_DIVISIONS_LIST'");
      }

      $check = tep_db_fetch_array(tep_db_query("select count(*) as column_exist from information_schema.columns where table_name = '" . TABLE_PRODUCTS . "' and column_name = 'exact_id'"));
      if (!$check['column_exist']) {
        tep_db_query("alter table " . TABLE_PRODUCTS . " add exact_id varchar(64) not null after products_id");
      }

      $check = tep_db_fetch_array(tep_db_query("select count(*) as column_exist from information_schema.columns where table_name = '" . TABLE_TAX_RATES . "' and column_name = 'tax_type'"));
      if (!$check['column_exist']) {
        tep_db_query("alter table " . TABLE_TAX_RATES . " add tax_type char(1) not null");
      }

      tep_redirect(tep_href_link('exact_online'));
    }
  }
}
