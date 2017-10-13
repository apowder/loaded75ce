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

if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MEMBERS'])) {
    $submenu[] = array('adminmembers', '', BOX_ADMINISTRATOR_MEMBERS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_BOXES'])) {
    $submenu[] = array('adminfiles', '', BOX_ADMINISTRATOR_BOXES);
}
