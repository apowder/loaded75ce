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

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_BACKUP'])) {
    $submenu[] = array(FILENAME_BACKUP, '', BOX_TOOLS_BACKUP);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_SERVER_INFO'])) {
    $submenu[] = array(FILENAME_SERVER_INFO, '', BOX_TOOLS_SERVER_INFO);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_HEADING_WHOS_ONLINE'])) {
    $submenu[] = array('whos-online', '', BOX_HEADING_WHOS_ONLINE);
}

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_CLEANER'])) {
    $submenu[] = array(FILENAME_CLEANER, '', BOX_TOOLS_CLEANER);
}
