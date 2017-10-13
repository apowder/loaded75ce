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

if (SALES_STATS_DISPLAY == 'True'  && \common\helpers\Acl::rule(['BOX_HEADING_REPORTS', 'BOX_REPORTS_SALES'])) {
    $submenu[] = array(FILENAME_SALES_STATISTICS, '', BOX_REPORTS_SALES);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_REPORTS', 'BOX_CATALOG_PRODUCTS_EXPECTED'])) {
    $submenu[] = array(FILENAME_PRODUCTS_EXPECTED, '', BOX_CATALOG_PRODUCTS_EXPECTED);
}
  