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

if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN']) && file_exists(DIR_WS_BOXES. 'gv_admin.php')) {
    $subsubmenu = $submenu;
    $extState = (false === \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'allowed'));
    require(DIR_WS_BOXES. 'gv_admin.php' );
    $subsubmenu[] = array('gv_admin', 'gv_admin.php', BOX_HEADING_GV_ADMIN, $submenu, $extState);
    $submenu = $subsubmenu;
}
if (RECOVER_CART_SALES_DISPLAY == 'True' && \common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_TOOLS_RECOVER_CART'])) {
    $extState = (false === \common\helpers\Acl::checkExtension('RecoverShoppingCart', 'allowed'));
    $submenu[] = array(FILENAME_RECOVER_CART_SALES, '', BOX_TOOLS_RECOVER_CART, false, $extState);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_TOOLS_BANNER_MANAGER'])) {
    $submenu[] = array(FILENAME_BANNER_MANAGER, '', BOX_TOOLS_BANNER_MANAGER);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_SPECIALS'])) {
    $submenu[] = array(FILENAME_SPECIALS, '', BOX_CATALOG_SPECIALS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_GIVE_AWAY'])) {
    $submenu[] = array('giveaway', '', BOX_CATALOG_GIVE_AWAY);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_FEATURED'])) {
    $submenu[] = array(FILENAME_FEATURED, '', BOX_CATALOG_FEATURED);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_MARKETING_TOOLS', 'BOX_MARKETING_TRUSTPILOT'])) {
    $submenu[] = array(FILENAME_TRUSTPILOT, '', BOX_MARKETING_TRUSTPILOT);
}