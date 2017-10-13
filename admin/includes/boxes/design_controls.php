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

if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_INFORMATION_MANAGER'])) {
    $submenu[] = array('information_manager', '', BOX_INFORMATION_MANAGER);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'FILENAME_CMS_MENUS'])) {
    $submenu[] = array('menus', '', FILENAME_CMS_MENUS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES'])) {
    $submenu[] = array('design/themes', '', BOX_HEADING_THEMES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_TEXTS'])) {
    $submenu[] = array('texts', '', BOX_TRANSLATION_TEXTS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_EMAIL_TEMPLATES'])) {
    $submenu[] = array('email/templates', '', BOX_TRANSLATION_EMAIL_TEMPLATES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BLOG'])) {
    $submenu[] = array('blog/index', '', WP_BLOG);
}