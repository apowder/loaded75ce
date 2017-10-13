<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$subsubmenu = array();

if (file_exists(DIR_WS_BOXES. 'configuration.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION'])) {
    require(DIR_WS_BOXES. 'configuration.php' );
    $subsubmenu[] = array('configuration', 'configuration.php', BOX_HEADING_CONFIGURATION, $submenu);
}
if (file_exists(DIR_WS_BOXES. 'status.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS'])) {
    require(DIR_WS_BOXES. 'status.php' );
    $subsubmenu[] = array('status', 'status.php', BOX_LOCALIZATION_ORDERS_STATUS, $submenu);
}

if (file_exists(DIR_WS_BOXES. 'product_stock_indication.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION'])) {
    require(DIR_WS_BOXES. 'product_stock_indication.php' );
    $subsubmenu[] = array('product_stock_indication', 'product_stock_indication.php', BOX_SETTINGS_BOX_STOCK_INDICATION, $submenu);
}


if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_CACHE_CONTROL'])) {
    $subsubmenu[] = array('cache_control', '', BOX_HEADING_CACHE_CONTROL);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_CRON_MANAGER'])) {
    $subsubmenu[] = array('cron_manager', '', BOX_HEADING_CRON_MANAGER);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_FILTERS'])) {
    $subsubmenu[] = array('filters', '', BOX_HEADING_FILTERS);
}
if (file_exists(DIR_WS_BOXES. 'locations.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCATION'])) {
    require(DIR_WS_BOXES. 'locations.php' );
    $subsubmenu[] = array('locations', 'locations.php', BOX_HEADING_LOCATION, $submenu);
}
if (file_exists(DIR_WS_BOXES. 'taxes.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TAXES'])) {
    require(DIR_WS_BOXES. 'taxes.php' );
    $subsubmenu[] = array('taxes', 'taxes.php', BOX_HEADING_TAXES, $submenu);
}
if (file_exists(DIR_WS_BOXES. 'localization.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCALIZATION'])) {
    require(DIR_WS_BOXES. 'localization.php' );
    $subsubmenu[] = array('localization', 'localization.php', BOX_HEADING_LOCALIZATION, $submenu);
}
if (file_exists(DIR_WS_BOXES. 'tools.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TOOLS'])) {
    require(DIR_WS_BOXES. 'tools.php' );
    $subsubmenu[] = array('tools', 'tools.php', BOX_HEADING_TOOLS, $submenu);
}

if (file_exists(DIR_WS_BOXES. 'sms.php') && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_SMS'])) {
    require(DIR_WS_BOXES. 'sms.php' );
    $subsubmenu[] = array('sms_messages', 'sms.php', BOX_HEADING_SMS, $submenu);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOGGING'])) {
    $subsubmenu[] = array('logging', '', BOX_HEADING_LOGGING);
}
if (DEPARTMENTS_ID > 0 && defined('SUPERADMIN_HTTP_URL') && tep_not_null(SUPERADMIN_HTTP_URL) && \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_FEATURES'])) {
    $subsubmenu[] = array('features', '', BOX_HEADING_FEATURES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_EXACT_ONLINE'])) {
    $subsubmenu[] = array('exact_online', '', BOX_HEADING_EXACT_ONLINE);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_SOCIALS'])) {
    $subsubmenu[] = array('socials', '', BOX_HEADING_SOCIALS);
}
$submenu = $subsubmenu;