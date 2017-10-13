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

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_GEO_ZONES'])) {
    $submenu[] = array('tax-zones', '', BOX_TAXES_GEO_ZONES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_TAX_CLASSES'])) {
    $submenu[] = array('tax_classes', '', BOX_TAXES_TAX_CLASSES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_TAX_RATES'])) {
    $submenu[] = array('tax_rates', '', BOX_TAXES_TAX_RATES);
}

