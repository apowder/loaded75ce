<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  ini_set('session.use_only_cookies', '0');
// set the level of error reporting
  if(defined('E_DEPRECATED'))
  {
    //error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    error_reporting(E_ALL & ~E_NOTICE);
  }
  else
  {
    error_reporting(E_ALL & ~E_NOTICE);
  }

// MySQL error
  $mysql_errors = array(); 

/*
// check if register_globals is enabled.
// since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
  if (function_exists('ini_get')) {
    ini_get('register_globals') or exit('FATAL ERROR: register_globals is disabled in php.ini, please enable it!');
  }
*/

// some code to solve compatibility issues
  require('includes/functions/compatibility.php');

// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// include server parameters
  require('includes/configure.php');

  if ( !class_exists('\common\classes\platform') ) {
    include_once('lib/common/classes/platform.php');
  }

// define the project version
  define('PROJECT_VERSION', 'osc 2.2 ms2 Holbi Trueloaded');

  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

if (!file_exists('lib/common/extensions/ProductBundles.php')) {
    define('PRODUCTS_BUNDLE_SETS', 'False');
}
// include the database functions -- moved to configure.php (Andrew)
//  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now -- moved to configure.php (Andrew)
//  tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
if (!file_exists('lib/common/extensions/VatOnOrder.php')) {
    define('ACCOUNT_COMPANY', 'disabled');
    define('ACCOUNT_COMPANY_VAT_ID', 'disabled');
}

  if (PLATFORM_ID > 0) {
        $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_PLATFORMS_CONFIGURATION . ' where platform_id = ' . PLATFORM_ID);
        while ($configuration = tep_db_fetch_array($configuration_query)) {
            if (!defined($configuration['cfgKey'])) {
                define($configuration['cfgKey'], $configuration['cfgValue']);
            }
        }
  }
  
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
      if (!defined($configuration['cfgKey'])) {
        define($configuration['cfgKey'], $configuration['cfgValue']);
      }
    /*
    if ($configuration['cfgKey'] == 'STORE_NAME') {
      $store_name = $configuration['cfgValue'];
    }else if ($configuration['cfgKey'] == 'STORE_OWNER') {
      $store_owner = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'STORE_OWNER_EMAIL_ADDRESS') {
      $store_owner_email_address = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'EMAIL_FROM') {
      $email_from = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'STORE_OWNER_EMAIL_ADDRESS') {
      $store_owner_email_address = $configuration['cfgValue'];
    } else {
      define($configuration['cfgKey'], $configuration['cfgValue']);
    }
    */
  }
  if (!defined("DEFAULT_USER_GROUP")) {
    define("DEFAULT_USER_GROUP", 0);
  }

if (defined(PURCHASE_OFF_STOCK)) {
    if (PURCHASE_OFF_STOCK == 'true'){
        define(STOCK_CHECK, 'false');
        define(STOCK_ALLOW_CHECKOUT, 'true');
    } else {
        define(STOCK_CHECK, 'true');
        define(STOCK_ALLOW_CHECKOUT, 'false');
    }
}


  $tax_rates_array = array();

// define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');


// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

// include cache functions if enabled
  //if (USE_CACHE == 'true') include(DIR_WS_FUNCTIONS . 'cache.php');

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  tep_session_name('tlSID');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
  if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $cookie_path, $cookie_domain);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $cookie_path);
    ini_set('session.cookie_domain', $cookie_domain);
  }

  /*common\models\sessionFlow*/

// set which precautions should be checked
  define('WARN_INSTALL_EXISTENCE', 'true');
  define('WARN_CONFIG_WRITEABLE', 'true');
  define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
  define('WARN_SESSION_AUTO_START', 'true');
  define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

  


