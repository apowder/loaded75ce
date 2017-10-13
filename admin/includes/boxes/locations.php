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

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_TAXES_COUNTRIES'])) {
    $submenu[] = array(FILENAME_COUNTRIES, '', BOX_TAXES_COUNTRIES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_TAXES_ZONES'])) {
    $submenu[] = array(FILENAME_ZONES, '', BOX_TAXES_ZONES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_GEO_ZONES'])) {
    $submenu[] = array('geo_zones', '', BOX_GEO_ZONES);
}

