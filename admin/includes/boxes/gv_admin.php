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

if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_COUPON_ADMIN'])) {
    $submenu[] = array(FILENAME_COUPON_ADMIN, '', BOX_COUPON_ADMIN, $extState);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_QUEUE'])) {
    $submenu[] = array(FILENAME_GV_QUEUE, '', BOX_GV_ADMIN_QUEUE, $extState);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_MAIL'])) {
    $submenu[] = array(FILENAME_GV_MAIL, '', BOX_GV_ADMIN_MAIL, $extState);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_SENT'])) {
    $submenu[] = array(FILENAME_GV_SENT, '', BOX_GV_ADMIN_SENT, $extState);
}

if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_VIRTUAL_GIFT_CARD'])) {
    $submenu[] = array('virtual-gift-card', '', BOX_VIRTUAL_GIFT_CARD, $extState);
}
