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

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_INDICATION'])) {
    $submenu[] = array(FILENAME_STOCK_INDICATION_INDICATION, '', BOX_SETTINGS_BOX_STOCK_INDICATION_INDICATION);
}

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_DELIVERY_TERMS'])) {
    $submenu[] = array(FILENAME_STOCK_INDICATION_DELIVERY_TERMS, '', BOX_SETTINGS_BOX_STOCK_INDICATION_DELIVERY_TERMS);
}

/*if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_ICONS'])) {
    $submenu[] = array(FILENAME_STOCK_INDICATION_ICONS, '', BOX_SETTINGS_BOX_STOCK_INDICATION_ICONS);
}*/
