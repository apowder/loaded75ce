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

if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CONFIGURATOR', 'BOX_CATALOG_CATEGORIES_PC_TEMPLATES'])) {
    $submenu[] = array(FILENAME_PCTEMPLATES, '', BOX_CATALOG_CATEGORIES_PC_TEMPLATES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CONFIGURATOR', 'BOX_CATALOG_CATEGORIES_CLASSES'])) {
    $submenu[] = array(FILENAME_CLASSES, '', BOX_CATALOG_CATEGORIES_CLASSES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CONFIGURATOR', 'BOX_CATALOG_CATEGORIES_ELEMENTS'])) {
    $submenu[] = array(FILENAME_ELEMENTS, '', BOX_CATALOG_CATEGORIES_ELEMENTS);
}

