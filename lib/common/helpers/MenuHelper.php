<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class MenuHelper {

    public static function getUrlByLinkId($link_id, $link_type) {
        switch ($link_type) {
            case 'default':
                if ($link_id == '8888886') {
                    return tep_href_link(FILENAME_DEFAULT);
                } elseif ($link_id == '8888885') {
                    return tep_href_link('contact/index');
                } elseif ($link_id == '8888887') {
                    if (isset($_SESSION['customer_id'])) {
                        return tep_href_link('account/logoff', '', 'SSL');
                    } else {
                        return tep_href_link('account/login', '', 'SSL');
                    }
                } elseif ($link_id == '8888888') {
                    if (isset($_SESSION['customer_id'])) {
                        return tep_href_link('account/index', '', 'SSL');
                    } else {
                        return tep_href_link('account/login', '', 'SSL');
                    }
                } elseif ($link_id == '8888884') {
                    return tep_href_link('checkout/index', '', 'SSL');
                } elseif ($link_id == '8888883') {
                    return tep_href_link('shopping-cart/index');
                } elseif ($link_id == '8888882') {
                    return tep_href_link('catalog/products_new');
                } elseif ($link_id == '8888881') {
                    return tep_href_link('catalog/featured_products');
                } elseif ($link_id == '8888880') {
                    return tep_href_link('catalog/specials');
                } elseif ($link_id == '8888879') {
                    return tep_href_link('catalog/gift-card');
                } elseif ($link_id == '8888878') {
                    return tep_href_link('catalog/all-products');
                } elseif ($link_id == '8888877') {
                    return tep_href_link('sitemap');
                }
                break;
            case 'custom':
                $link = tep_db_fetch_array(tep_db_query("select link from " . TABLE_MENU_ITEMS . " where platform_id = '" . \common\classes\platform::currentId() . "' and link_id = '" . (int) $link_id . "'"));
                if ($link) {
                    return tep_href_link($link['link']);
                }
                break;
        }
        return false;
    }
    
    public static function getAllCustomPages($platform_id){
        $cusom_pages_query = tep_db_query("select ts.id, ts.setting_value from " . TABLE_THEMES_SETTINGS . " ts left join " . TABLE_THEMES . " t on ts.theme_name = t.theme_name inner join " . TABLE_PLATFORMS_TO_THEMES . " pt on pt.theme_id = t.id where pt.platform_id = '" . (int)$platform_id . "' and ts.setting_group = 'added_page' and ts.setting_name='custom' order by ts.setting_value");
        $custom_pages = [];
        if (tep_db_num_rows($cusom_pages_query)){
            while($custom = tep_db_fetch_array($cusom_pages_query)){
                $custom_pages[$custom['id']] = $custom['setting_value'];
            }
        }
        return $custom_pages;
    }

}
