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

if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS'])) {
    $submenu[] = array('orders', '', BOX_CUSTOMERS_ORDERS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_CUSTOMERS'])) {
    $submenu[] = array('customers', '', BOX_CUSTOMERS_CUSTOMERS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_GROUPS'])) {
    $extState = (false === \common\helpers\Acl::checkExtension('UserGroups', 'allowed') || CUSTOMERS_GROUPS_ENABLE != 'True');
    $submenu[] = array('groups', '', BOX_CUSTOMERS_GROUPS, false, $extState);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_SUBSCRIPTION'])) {
    $submenu[] = array('subscription', '', BOX_CUSTOMERS_SUBSCRIPTION);
}

if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_LOYALTY'])) {
    $extState = (false === \common\helpers\Acl::checkExtension('CustomerLoyalty', 'allowed'));
    if (!$extState){
        \common\extensions\CustomerLoyalty\CustomerLoyalty::checkLoyaltyConfiguration();
    }
    $submenu[] = array('customers_loyalty', '', BOX_CUSTOMERS_LOYALTY, false, $extState);
}
  