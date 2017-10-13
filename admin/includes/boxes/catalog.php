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

if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS'])) {
    $submenu[] = array(FILENAME_CATEGORIES, '', BOX_CATALOG_CATEGORIES_PRODUCTS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_REVIEWS'])) {
    $submenu[] = array(FILENAME_REVIEWS, '', BOX_CATALOG_REVIEWS);
}
if (PRODUCTS_TEMPLATES_CONFIGURATOR == 'True' && \common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CONFIGURATOR'])) {
    $subsubmenu = $submenu;
    require(DIR_WS_BOXES. 'configurator.php' );
    $subsubmenu[] = array('configurator', 'configurator.php', BOX_CATALOG_CONFIGURATOR, $submenu);
    $submenu = $subsubmenu;
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES'])) {
    $submenu[] = array('productsattributes', '', BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES);
}
if (PRODUCTS_PROPERTIES == 'True' && \common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_PROPERTIES'])) {
    $submenu[] = array(FILENAME_PROPERTIES, '', BOX_CATALOG_PROPERTIES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_SUPPIERS'])) {
    $submenu[] = array('suppliers', '', BOX_CATALOG_SUPPIERS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_EASYPOPULATE'])) {
    $submenu[] = array(FILENAME_EASYPOPULATE, '', BOX_CATALOG_EASYPOPULATE);
}
