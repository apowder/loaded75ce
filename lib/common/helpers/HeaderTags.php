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

class HeaderTags {

    public static function get_header_tag_products_title($product_id) {
        global $languages_id, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

        $products2c_join = '';
        if (\common\classes\platform::activeId()) {
            $products2c_join .=
                    " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' " .
                    " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $product_header_tags_values = tep_db_fetch_array(tep_db_query("select if(length(pd1.products_head_title_tag), pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = pd.products_id  " . " and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = '" . (int) $product_id . "' limit 1"));

        return $product_header_tags_values['products_head_title_tag'];
    }

    public static function get_header_tag_products_keywords($product_id) {
        global $languages_id, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

        $products2c_join = '';
        if (\common\classes\platform::activeId()) {
            $products2c_join .=
                    " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' " .
                    " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $product_header_tags_values = tep_db_fetch_array(tep_db_query("select if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) as products_head_keywords_tag from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = pd.products_id  " . "  and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = '" . (int) $product_id . "' limit 1"));

        return $product_header_tags_values['products_head_keywords_tag'];
    }

    public static function get_header_tag_products_desc($product_id) {
        global $languages_id, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

        $products2c_join = '';
        if (\common\classes\platform::activeId()) {
            $products2c_join .=
                    " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' " .
                    " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $product_header_tags_values = tep_db_fetch_array(tep_db_query("select if(length(pd1.products_head_desc_tag), pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = pd.products_id  " . "  and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = '" . (int) $product_id . "' limit 1"));

        return $product_header_tags_values['products_head_desc_tag'];
    }

    public static function seo_correct($invalue, $fcheck = array(), $is_prod = true) {
        $invalue = preg_replace("/" . HEAD_SHABLON_NAME . "/", $fcheck['products_name'], $invalue);
        $invalue = preg_replace("/" . HEAD_SHABLON_MANUFACTURER . "/", $fcheck['manufacturers_name'], $invalue);
        $invalue = preg_replace("/" . HEAD_SHABLON_BREADCRUMBMONE . "/", $fcheck['breadcrumbmone'], $invalue);
        //clear
        $invalue = preg_replace("/" . HEAD_SHABLON_NAME . "/", '', $invalue);
        $invalue = preg_replace("/" . HEAD_SHABLON_MANUFACTURER . "/", '', $invalue);
        $invalue = preg_replace("/" . HEAD_SHABLON_BREADCRUMBMONE . "/", '', $invalue);

// short clear
        if (strlen($invalue)) {
            $ex = explode(" ", $invalue);
            $new = '';
            foreach ($ex as $i => $value) {
                if (tep_not_null($value) && (strpos(HEAD_SHABLON_NAME, $value) !== false || strpos(HEAD_SHABLON_MANUFACTURER, $value) !== false || strpos(HEAD_SHABLON_BREADCRUMBMONE, $value) !== false)) {
                    unset($ex[$i]);
                }
            }
            $invalue = implode(" ", $ex);
        }
        return $invalue;
    }

}
