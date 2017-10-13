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

if (\common\helpers\Acl::rule(['BOX_HEADING_MODULES', 'BOX_MODULES_PAYMENT'])) {
    $submenu[] = array(FILENAME_MODULES.'?set=payment', '', BOX_MODULES_PAYMENT);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MODULES', 'BOX_MODULES_SHIPPING'])) {
    $submenu[] = array(FILENAME_MODULES . '?set=shipping', '', BOX_MODULES_SHIPPING);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MODULES', 'BOX_MODULES_ORDER_TOTAL'])) {
    $submenu[] = array(FILENAME_MODULES . '?set=ordertotal', '', BOX_MODULES_ORDER_TOTAL);
}
