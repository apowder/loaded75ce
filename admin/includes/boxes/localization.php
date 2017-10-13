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

if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCALIZATION', 'BOX_LOCALIZATION_CURRENCIES'])) {
    $submenu[] = array(FILENAME_CURRENCIES, '', BOX_LOCALIZATION_CURRENCIES);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_LOCALIZATION', 'BOX_LOCALIZATION_LANGUAGES'])) {
    $submenu[] = array(FILENAME_LANGUAGES, '', BOX_LOCALIZATION_LANGUAGES);
}
