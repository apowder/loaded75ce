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

class Points {

    public static function get_bonus_points_price($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select bonus_points_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select bonus_points_price from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . ( int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);
        if ($product['bonus_points_price'] == '' && $default != '') {
            $product['bonus_points_price'] = $default;
        }
        return $product['bonus_points_price'];
    }

    public static function get_bonus_points_cost($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select bonus_points_cost from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select bonus_points_cost from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);
        if ($product['bonus_points_cost'] == '' && $default != '') {
            $product['bonus_points_cost'] = $default;
        }
        return $product['bonus_points_cost'];
    }

}
