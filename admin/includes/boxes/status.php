<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$submenu = array();

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_ORDERS_STATUS_GROUPS'])) {
    $submenu[] = array(FILENAME_ORDERS_STATUS_GROUPS, '', BOX_ORDERS_STATUS_GROUPS);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_LOCALIZATION_ORDERS_STATUS'])) {
    $submenu[] = array(FILENAME_ORDERS_STATUS, '', BOX_LOCALIZATION_ORDERS_STATUS);
}