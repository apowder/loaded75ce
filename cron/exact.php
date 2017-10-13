<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

set_time_limit (0);
chdir('../');
require('includes/application_top.php');

if (EXACT_CONNECTOR_STATUS != 'True') {
  echo 'Cron Disabled'; exit;
}

if (!defined('DIR_FS_CATALOG_IMAGES')) {
  define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
}

//require('lib/vendor/autoload.php');
//require('lib/vendor/yiisoft/yii2/Yii.php');
//require('lib/common/config/bootstrap.php');

include(DIR_WS_HTTP_ADMIN_CATALOG . DIR_WS_FUNCTIONS . 'exact.php');

$exact_crons_query = tep_db_query("select exact_crons_id, exact_crons_function, if((is_locked > 0 && (schedule_last_started + interval " . (int)PRODUCTS_CRON_LOCK_TIME . " minute) > now()), 1, 0) do_not_run from " . TABLE_EXACT_CRONS . " where schedule_every_minutes > 0 and schedule_next_started <= now()");
while ($exact_crons = tep_db_fetch_array($exact_crons_query)) {
  if (function_exists($exact_crons['exact_crons_function'])) {
    //check if cron is runing here
    if ($exact_crons['do_not_run'] > 0) {
      echo 'Error: Another thread is runing';
      exit;
    }

    tep_db_query("update " . TABLE_EXACT_CRONS . " set schedule_last_started = now(), is_locked = 1,
      schedule_next_started = '" . tep_db_input(get_schedule_next_started($exact_crons['exact_crons_id'])) . "'
      where exact_crons_id = '" . tep_db_input($exact_crons['exact_crons_id']) . "'");

    $result = call_user_func($exact_crons['exact_crons_function']);
    if (is_array($result)) foreach ($result as $message) {
      echo $message . "<br>\n";
    } else {
      echo $result . "<br>\n";
    }

    tep_db_query("update " . TABLE_EXACT_CRONS . " set is_locked = 0,
      schedule_next_started = '" . tep_db_input(get_schedule_next_started($exact_crons['exact_crons_id'])) . "'
      where exact_crons_id = '" . tep_db_input($exact_crons['exact_crons_id']) . "'");
  }
}

