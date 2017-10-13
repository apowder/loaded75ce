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

class Product {

    public static function check_product($products_id, $check_status = 1) {
        global $customer_groups_id, $HTTP_SESSION_VARS;

        $products_join = '';
        if (\common\classes\platform::activeId() && $check_status) {
            $products_join .= " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        if ($customer_groups_id == 0) {
            $products_check_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p {$products_join} " . "  where p.products_id = '" . (int) $products_id . "' " . "  " . ($check_status ? " and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : ""));
        } else {
            $products_check_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p {$products_join} " . " left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "'  where if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) and p.products_id = '" . (int) $products_id . "'  " . " " . ($check_status ? " and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : ""));
        }
        return tep_db_num_rows($products_check_query);
    }

    public static function get_product_order_quantity($product_id, $data = null) {
        static $fetched = array();
        if (!isset($fetched[(int) $product_id]) && is_array($data) && array_key_exists('order_quantity_minimal', $data) && array_key_exists('order_quantity_step', $data)) {
            $fetched[(int) $product_id] = array(
                'order_quantity_minimal' => $data['order_quantity_minimal'],
                'order_quantity_step' => $data['order_quantity_step'],
            );
        }
        if (!isset($fetched[(int) $product_id])) {
            $get_data_r = tep_db_query("SELECT order_quantity_minimal, order_quantity_step, pack_unit, packaging FROM " . TABLE_PRODUCTS . " WHERE products_id='" . (int) $product_id . "'");
            if (tep_db_num_rows($get_data_r) > 0) {
                $fetched[(int)$product_id] = tep_db_fetch_array($get_data_r);
//                if ( $fetched[(int)$product_id]['pack_unit']>0 || $fetched[(int)$product_id]['packaging']>0 ) {
//                   $fetched[(int)$product_id]['order_quantity_step'] = 1;
//                }
            } else {
                $fetched[(int)$product_id] = array('order_quantity_minimal' => 1, 'order_quantity_step' => 1,);
            }
        }
        $fetched[(int) $product_id]['order_quantity_minimal'] = max(1, $fetched[(int) $product_id]['order_quantity_minimal']);
        $fetched[(int) $product_id]['order_quantity_step'] = max(1, $fetched[(int) $product_id]['order_quantity_step']);
        //$fetched[(int) $product_id]['order_quantity_minimal'] = max($fetched[(int) $product_id]['order_quantity_minimal'], $fetched[(int) $product_id]['order_quantity_step']);
        return $fetched[(int) $product_id];
    }

    public static function filter_product_order_quantity($product_id, $quantity, $quantity_is_top_bound = false) {
        $order_qty_data = self::get_product_order_quantity($product_id);
        if ( $order_qty_data['order_quantity_minimal']>$order_qty_data['order_quantity_step'] ) {
          $result_quantity = max($order_qty_data['order_quantity_minimal'], $quantity,1);
          $base_qty = $order_qty_data['order_quantity_minimal'];
        }else{
          $result_quantity = max($order_qty_data['order_quantity_minimal'],$quantity, 1);
          $base_qty = 0;
        }
        if ( $result_quantity>$order_qty_data['order_quantity_minimal'] && (($result_quantity-$base_qty)%$order_qty_data['order_quantity_step'])!=0 ) {
          $result_quantity = $base_qty+((intval(($result_quantity-$base_qty) / $order_qty_data['order_quantity_step'])+1)*$order_qty_data['order_quantity_step']);
        }
        if ( $quantity_is_top_bound && $result_quantity>$quantity ) {
          $result_quantity = max($order_qty_data['order_quantity_minimal'],$result_quantity-$order_qty_data['order_quantity_step']);
        }
        return $result_quantity;
    }

    public static function get_product_path($products_id) {
        $cPath = '';

        if (!self::check_product($products_id)) {
            return '';
        }

        $categories_join = '';
        if (\common\classes\platform::activeId()) {
            $categories_join .=
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $category_query = tep_db_query("select p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c where p.products_id = '" . (int) $products_id . "' and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = p2c.products_id and c.categories_id=p2c.categories_id and c.categories_status=1 limit 1");
        if (tep_db_num_rows($category_query)) {
            $category = tep_db_fetch_array($category_query);

            $categories = array();
            \common\helpers\Categories::get_parent_categories($categories, $category['categories_id']);

            $categories = array_reverse($categories);

            $cPath = implode('_', $categories);

            if (tep_not_null($cPath))
                $cPath .= '_';
            $cPath .= $category['categories_id'];
        }

        return $cPath;
    }

    public static function get_products_weight($products_id) {
        $product = tep_db_fetch_array(tep_db_query("select products_weight from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
        return $product['products_weight'];
    }
	
    public static function get_products_info($products_id, $field) {
		$product = tep_db_fetch_array(tep_db_query("select {$field} from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
        return $product[$field];
    }	

    public static function get_products_name($product_id, $language = '', $search_terms = array()) {
        global $languages_id;
        if (empty($language))
            $language = $languages_id;
        $product_query = tep_db_query("select if(length(pd1.products_name) > 0, pd1.products_name, pd.products_name) as products_name from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.affiliate_id = '0' and pd1.language_id = '" . (int) $language . "' where pd.products_id = '" . (int) $product_id . "' and pd.language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and pd.affiliate_id = '0'");
        $product = tep_db_fetch_array($product_query);
        if (sizeof($search_terms) == 0) {
            return $product['products_name'];
        } else {
            if (MSEARCH_ENABLE == 'true' && MSEARCH_HIGHLIGHT_ENABLE == 'true') {
                return \common\helpers\Output::highlight_text($product['products_name'], $search_terms);
            } else {
                return $product['products_name'];
            }
        }
    }

    public static function get_products_stock($products_id) {
        $products_id = \common\helpers\Inventory::normalize_id($products_id);
        if (PRODUCTS_INVENTORY == 'True') {
            $stock_query = tep_db_query("select products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($products_id) . "'");
            if (tep_db_num_rows($stock_query)) {
                $stock_values = tep_db_fetch_array($stock_query);
            } else {
                $products_id = \common\helpers\Inventory::get_prid($products_id);
                $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $stock_values = tep_db_fetch_array($stock_query);
            }
        } else {
            $products_id = \common\helpers\Inventory::get_prid($products_id);
            $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
            $stock_values = tep_db_fetch_array($stock_query);
        }

        return $stock_values['products_quantity'];
    }

    public static function check_stock($products_id, $products_quantity) {
        $stock_left = self::get_products_stock($products_id) - $products_quantity;
        $out_of_stock = '';

        if ($stock_left < 0) {
            $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
        }

        return $out_of_stock;
    }

    public static function get_allocated_stock_quantity($products_id) {
        $orders_status_array = array(); // not Completed and not Cancelled orders
        $orders_status_query = tep_db_query("select distinct orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id not in (4,5)");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
          $orders_status_array[] = $orders_status['orders_status_id'];
        }
        if (strpos($products_id, '{') !== false) {
            $check_stock = tep_db_fetch_array(tep_db_query("select sum(op.products_quantity) as allocated_stock_quantity from " . TABLE_INVENTORY . " i left join " . TABLE_ORDERS_PRODUCTS . " op on op.uprid = i.products_id and op.products_id = i.prid left join " . TABLE_ORDERS . " o on o.orders_id = op.orders_id where i.products_id = '" . tep_db_input($products_id) . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by i.products_id"));
        } else {
            $check_stock = tep_db_fetch_array(tep_db_query("select sum(op.products_quantity) as allocated_stock_quantity from " . TABLE_PRODUCTS . " p left join " . TABLE_ORDERS_PRODUCTS . " op on op.products_id = p.products_id left join " . TABLE_ORDERS . " o on o.orders_id = op.orders_id where p.products_id = '" . (int)$products_id . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by p.products_id"));
        }
        return (int)$check_stock['allocated_stock_quantity'];
    }

    public static function log_stock_history_before_update($uprid, $qty, $qty_prefix, $params = []) {
        if (strpos($uprid, '{') !== false) {
            $check = tep_db_fetch_array(tep_db_query("select products_id, prid, products_model, products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));
        } else {
            $check = tep_db_fetch_array(tep_db_query("select products_id, products_id as prid, products_model, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$uprid . "'"));
        }
        $sql_data_array = [
            'products_id' => $check['products_id'],
            'prid' => $check['prid'],
            'products_model' => $check['products_model'],
            'products_quantity_before' => $check['products_quantity'],
            'products_quantity_update_prefix' => $qty_prefix,
            'products_quantity_update' => $qty,
            'comments' => $params['comments'],
            'orders_id' => $params['orders_id'],
            'admin_id' => $params['admin_id'],
            'date_added' => 'now()',
        ];
        tep_db_perform(TABLE_STOCK_HISTORY, $sql_data_array);
    }

    public static function update_stock($uprid, $qty, $old_qty = 0) {
        //return true;
        $prid = \common\helpers\Inventory::get_prid($uprid);
        if (!tep_not_null($prid))
            return false;

        if (STOCK_LIMITED == 'true') {
            if ($qty > $old_qty) {
                $q = "+" . (int) ($qty - $old_qty) . "";
            } else {
                $q = "-" . (int) ($old_qty - $qty) . "";
            }
            if (DOWNLOAD_ENABLED == 'true') {
                preg_match_all("/\{\d+\}/", $uprid, $arr);
                $options_id = $arr[0][1];
                preg_match_all("/\}[^\{]+/", $uprid, $arr);
                $values_id = $arr[0][1];

                if (is_array($options_id)) {
                    $stock_query_raw = "SELECT count(*) as total FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad WHERE pa.products_attributes_id=pad.products_attributes_id and pa.products_id = '" . (int) $prid . "' and pad.products_attributes_filename<>'' ";
                    $stock_query_raw .= " and ( 0 ";
                    for ($k = 0; $k < count($options_id); $k++) {
                        $stock_query_raw .= " OR (pa.options_id = '" . (int) $options_id[$k] . "' AND pa.options_values_id = '" . (int) $values_id[$k] . "')  ";
                    }
                    $stock_query_raw .= ") ";
                    $d = tep_db_fetch_array(tep_db_query($stock_query_raw));
                    if ($d['total'] > 0) {
                        return true;
                    }
                }
                $stock_query_raw = "SELECT count(*) as total FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . (int) $prid . "' and products_file <> '' ";
                $d = tep_db_fetch_array(tep_db_query($stock_query_raw));
                if ($d['total'] > 0) {
                    return true;
                }
            }

            if (\common\helpers\Acl::checkExtension('ProductBundles', 'allowed') && PRODUCTS_BUNDLE_SETS == 'True') {
                $vids = array();
                $attributes_query = tep_db_query("select options_id, options_values_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $prid . "'");
                while ($attributes = tep_db_fetch_array($attributes_query)) {
                    if (preg_match('/\{' . $attributes['options_id'] . '\}' . $attributes['options_values_id'] . '(\{|$)/', $uprid)) {
                        $vids[$attributes['options_id']] = $attributes['options_values_id'];
                    }
                }
                ksort($vids);
                $uprid = \common\helpers\Inventory::get_uprid($prid, $vids);
            }
            if ( ($ext = \common\helpers\Acl::checkExtension('Inventory', 'updateStock')) && PRODUCTS_INVENTORY == 'True') {
                $ext::updateStock($prid, $uprid, $q);
            } else {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity  " . $q . " where products_id = '" . (int)$prid . "'");
                $data_q = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where  products_id = '" . (int)$prid . "'");
                $data = tep_db_fetch_array($data_q);
                if ($data['products_quantity'] < 1 && (STOCK_ALLOW_CHECKOUT == 'false')) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_status = 0 where products_id = '" . (int)$prid . "'");
                }
            }
        }
    }

    public static function remove_product_image($filename) {
        $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image = '" . tep_db_input($filename) . "' or products_image_med = '" . tep_db_input($filename) . "' or products_image_lrg = '" . tep_db_input($filename) . "' or products_image_xl_1 = '" . tep_db_input($filename) . "' or products_image_sm_1 = '" . tep_db_input($filename) . "' or products_image_xl_2 = '" . tep_db_input($filename) . "' or products_image_sm_2 = '" . tep_db_input($filename) . "' or products_image_xl_3 = '" . tep_db_input($filename) . "' or products_image_sm_3 = '" . tep_db_input($filename) . "' or products_image_xl_4 = '" . tep_db_input($filename) . "' or products_image_sm_4 = '" . tep_db_input($filename) . "' or products_image_xl_5 = '" . tep_db_input($filename) . "' or products_image_sm_5 = '" . tep_db_input($filename) . "' or products_image_xl_6 = '" . tep_db_input($filename) . "' or products_image_sm_6 = '" . tep_db_input($filename) . "'");
        $duplicate_image = tep_db_fetch_array($duplicate_image_query);
        if ($duplicate_image['total'] < 2) {
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename);
            }
        }
    }

    public static function remove_product($product_id) {
        $product_image_query = tep_db_query("select products_image, products_image_med, products_image_lrg, products_image_xl_1, products_image_sm_1, products_image_xl_2, products_image_sm_2, products_image_xl_3, products_image_sm_3, products_image_xl_4, products_image_sm_4, products_image_xl_5, products_image_sm_5, products_image_xl_6, products_image_sm_6 from " . TABLE_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        $product_image = tep_db_fetch_array($product_image_query);

        self::remove_product_image($product_image['products_image']);
        self::remove_product_image($product_image['products_image_med']);
        self::remove_product_image($product_image['products_image_lrg']);
        for ($i = 1; $i < 7; $i++) {
            self::remove_product_image($product_image['products_image_sm_' . $i]);
            self::remove_product_image($product_image['products_image_xl_' . $i]);
        }

        //if (USE_MARKET_PRICES == 'True') {
        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PLATFORMS_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        $query = tep_db_query("select specials_id from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "'");
        while ($data = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where specials_id = " . $data['specials_id']);
        }
        $query = tep_db_query("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        while ($data = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
        }
        //}
        if (PRODUCTS_PROPERTIES == 'True') {
            tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        }
        if (SUPPLEMENT_STATUS == 'True') {
            tep_db_query("delete from " . TABLE_PRODUCTS_UPSELL . " where products_id = '" . (int) $product_id . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_UPSELL . " where upsell_id = '" . (int) $product_id . "'");
            tep_db_query("delete from " . TABLE_CATS_PRODUCTS_XSELL . " where xsell_products_id = '" . (int) $product_id . "'");
            tep_db_query("delete from " . TABLE_CATS_PRODUCTS_UPSELL . " where upsell_products_id = '" . (int) $product_id . "'");
        }
        tep_db_query("delete from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $product_id . "'");

        if (PRODUCTS_BUNDLE_SETS == 'True') {
            tep_db_query("delete from " . TABLE_SETS_PRODUCTS . " where sets_id = '" . (int) $product_id . "'");
        }

        if (PRODUCTS_INVENTORY == 'True') {
            tep_db_query("delete from " . TABLE_INVENTORY . " where prid = '" . (int) $product_id . "'");
        }

        tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $product_id . "'");

        $product_reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where products_id = '" . (int) $product_id . "'");
        while ($product_reviews = tep_db_fetch_array($product_reviews_query)) {
            tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int) $product_reviews['reviews_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_REVIEWS . " where products_id = '" . (int) $product_id . "'");

        \backend\design\ProductTemplate::productDelete($product_id);

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public static function get_products_special_price($product_id, $qty = 1) {
        global $currency_id, $customer_groups_id;

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($customer_groups_id)) {
                return false;
            }
        }

        if (function_exists('self::check_product')) {
            if (!self::check_product($product_id)) {
                return false;
            }
        }
        $product_price = self::get_products_price($product_id, $qty);
        $__product_price = $product_price;

        if (\common\helpers\Acl::checkExtension('ProductBundles', 'allowed') && PRODUCTS_BUNDLE_SETS == 'True') {
            $products2c_join = '';
            if (\common\classes\platform::activeId()) {
                $products2c_join .=
                        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' " .
                        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
            }

            $bundle_sets_query = tep_db_query("select distinct p.products_id, sp.num_product from " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "' and pgp.currencies_id = '" . (int) (USE_MARKET_PRICES == 'True' ? $currency_id : 0) . "', " . TABLE_SETS_PRODUCTS . " sp where sp.product_id = p.products_id and sp.sets_id = '" . (int) $product_id . "' and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) order by sp.sort_order");
            if (tep_db_num_rows($bundle_sets_query) > 0) {
                $sets_discount = tep_db_fetch_array(tep_db_query("select products_sets_discount from " . TABLE_PRODUCTS . " where products_id = '" . (int) $product_id . "'"));
                if ($sets_discount['products_sets_discount'] > 0) {
                    return ($product_price * (100 - $sets_discount['products_sets_discount']) / 100);
                }
            }
        }

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            if (USE_MARKET_PRICES != 'True' && $customer_groups_id == 0) {
                $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "' and status");
            } else {
                $specials_query = tep_db_query("select s.specials_id, if(sp.specials_new_products_price is NULL, -2, sp.specials_new_products_price) as specials_new_products_price from " . TABLE_SPECIALS . " s left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int) $customer_groups_id . "'  and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' where s.products_id = '" . (int) $product_id . "'  and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.status ");
            }
        } else {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "' and status");
        }

        if (tep_db_num_rows($specials_query)) {
            $special = tep_db_fetch_array($specials_query);
            $special_price = $special['specials_new_products_price'];
            if ($special_price == -2) {
                if ($customer_groups_id != 0) {
                    if (USE_MARKET_PRICES == 'True') {
                        $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . (int) $special['specials_id'] . "' and currencies_id = '" . (int) $currency_id . "' and groups_id = 0");
                    } else {
                        $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "' and status");
                    }
                    if (tep_db_num_rows($specials_query)) {
                        $special = tep_db_fetch_array($specials_query);
                        if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'apply_groups_discount_to_specials')) {
                            $discount = \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_discount');
                            $special_price = $special['specials_new_products_price'] * (1 - ($discount / 100));
                        } else {
                            $special_price = $special['specials_new_products_price'];
                        }
                    } else {
                        $special_price = false;
                    }
                } else {
                    $special_price = false;
                }
            }
        } else {
            $special_price = false;
        }

        $product = tep_db_fetch_array(tep_db_query('select products_model from ' . TABLE_PRODUCTS . " where products_id='" . (int) $product_id . "'"));
        if (substr($product['products_model'], 0, 4) == 'GIFT') {    //Never apply a salededuction to Ian Wilson's Giftvouchers
            return $special_price >= $__product_price ? false : $special_price;
        }

        global $salemaker_array;
        if (sizeof($salemaker_array)) {

            for ($i = 0, $n = sizeof($salemaker_array); $i < $n; $i++) {
                if (!is_array($salemaker_array[$i]['sale_categories_all'])) {
                    continue;
                } else {
                    $query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "' and categories_id in ('" . implode("', '", $salemaker_array[$i]['sale_categories_all']) . "')");
                    $data = tep_db_fetch_array($query);
                    if (!$data['total']) {
                        continue;
                    } else {
                        $product_price = $__product_price;
                        if (($salemaker_array[$i]['sale_pricerange_from'] > 0 && $product_price < $salemaker_array[$i]['sale_pricerange_from']) || ($salemaker_array[$i]['sale_pricerange_to'] > 0 && $product_price > $salemaker_array[$i]['sale_pricerange_to'])) {
                            continue;
                        } else {
                            if (!$special_price) {
                                $tmp_special_price = $product_price;
                            } else {
                                $tmp_special_price = $special_price;
                            }
                            switch ($salemaker_array[$i]['sale_deduction_type']) {
                                case 0:
                                    $sale_product_price = $product_price - $salemaker_array[$i]['sale_deduction_value'];
                                    $sale_special_price = $tmp_special_price - $salemaker_array[$i]['sale_deduction_value'];
                                    break;
                                case 1:
                                    $sale_product_price = $product_price - (($product_price * $salemaker_array[$i]['sale_deduction_value']) / 100);
                                    $sale_special_price = $tmp_special_price - (($tmp_special_price * $salemaker_array[$i]['sale_deduction_value']) / 100);
                                    break;
                                case 2:
                                    $sale_product_price = $salemaker_array[$i]['sale_deduction_value'];
                                    $sale_special_price = $salemaker_array[$i]['sale_deduction_value'];
                                    break;
                                default:
                                    return $special_price >= $__product_price ? false : $special_price;
                            }
                            if ($sale_product_price < 0) {
                                $sale_product_price = 0;
                            }

                            if ($sale_special_price < 0) {
                                $sale_special_price = 0;
                            }
                            if (!$special_price) {
                                return $sale_product_price >= $__product_price ? false : number_format($sale_product_price, 4, '.', '');
                            } else {
                                switch ($salemaker_array[$i]['sale_specials_condition']) {
                                    case 0:
                                        return $sale_product_price >= $__product_price ? false : number_format($sale_product_price, 4, '.', '');
                                        break;
                                    case 1:
                                        return $special_price >= $__product_price ? false : number_format($special_price, 4, '.', '');
                                        break;
                                    case 2:
                                        return $sale_special_price >= $__product_price ? false : number_format($sale_special_price, 4, '.', '');
                                        break;
                                    default:
                                        return $special_price >= $__product_price ? false : number_format($special_price, 4, '.', '');
                                }
                            }
                        }
                    }
                }
            }
            return $special_price >= $__product_price ? false : $special_price;
        } else {
            return $special_price >= $__product_price ? false : $special_price;
        }
    }

    public static function get_products_special_price_edit_order($product_id, $currency_id = 0, $customer_groups_id = 0) {

        $product_price = \common\helpers\Product::get_products_price_edit_order($product_id, $currency_id, $customer_groups_id, 1, true);
        $__product_price = $product_price;

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            if (USE_MARKET_PRICES != 'True' && $customer_groups_id == 0) {
                $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status");
            } else {
                $specials_query = tep_db_query("select s.specials_id, if(sp.specials_new_products_price is NULL, -2, sp.specials_new_products_price) as specials_new_products_price from " . TABLE_SPECIALS . " s left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int)$customer_groups_id . "'  and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' where s.products_id = '" . (int)$product_id . "'  and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.status ");
            }
        } else {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status");
        }

        if (tep_db_num_rows($specials_query)) {
            $special = tep_db_fetch_array($specials_query);
            $special_price = $special['specials_new_products_price'];
            if ($special_price == -2 && $customer_groups_id != 0) {
                if (USE_MARKET_PRICES == 'True') {
                    $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . (int)$special['specials_id'] . "' and currencies_id = '" . (int)$currency_id . "' and groups_id = 0");
                } else {
                    $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status");
                }
                if (tep_db_num_rows($specials_query)) {
                    $special = tep_db_fetch_array($specials_query);
                    if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'apply_groups_discount_to_specials')) {
                        $discount = \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_discount');
                        $special_price = $special['specials_new_products_price'] * (1 - ($discount / 100));
                    } else {
                        $special_price = $special['specials_new_products_price'];
                    }
                } else {
                    $special_price = false;
                }
            }
        } else {
            $special_price = false;
        }

        if (substr($product['products_model'], 0, 4) == 'GIFT') {    //Never apply a salededuction to Ian Wilson's Giftvouchers
            return $special_price >= $__product_price ? false : $special_price;
        }

        $product_to_categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
        $i_count = 0;
        while ($product_to_categories = tep_db_fetch_array($product_to_categories_query)) {
            if ($i_count++ != 0)
                $while_arr .= 'or';
            $while_arr .= "(sale_categories_all like '%," . $product_to_categories['categories_id'] . ",%')";
        }

        $sale_query = tep_db_query("select sale_specials_condition, sale_deduction_value, sale_deduction_type from " . TABLE_SALEMAKER_SALES . " where (" . $while_arr . ") and sale_status = '1' and groups_id = '" . $customer_groups_id . "' and (sale_date_start <= now() or sale_date_start = '0000-00-00') and (sale_deduction_value > 0) and (sale_date_end >= now() or sale_date_end = '0000-00-00') and (sale_pricerange_from <= '" . $product_price . "' or sale_pricerange_from = '0') and (sale_pricerange_to >= '" . $product_price . "' or sale_pricerange_to = '0')");
        if (tep_db_num_rows($sale_query)) {
            $sale = tep_db_fetch_array($sale_query);
        } else {
            return $special_price >= $__product_price ? false : $special_price;
        }

        if (!$special_price) {
            $tmp_special_price = $product_price;
        } else {
            $tmp_special_price = $special_price;
        }

        switch ($sale['sale_deduction_type']) {
            case 0:
                $sale_product_price = $product_price - $sale['sale_deduction_value'];
                $sale_special_price = $tmp_special_price - $sale['sale_deduction_value'];
                break;
            case 1:
                $sale_product_price = $product_price - (($product_price * $sale['sale_deduction_value']) / 100);
                $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale['sale_deduction_value']) / 100);
                break;
            case 2:
                $sale_product_price = $sale['sale_deduction_value'];
                $sale_special_price = $sale['sale_deduction_value'];
                break;
            default:
                return $special_price >= $__product_price ? false : $special_price;
        }

        if ($sale_product_price < 0) {
            $sale_product_price = 0;
        }

        if ($sale_special_price < 0) {
            $sale_special_price = 0;
        }

        if (!$special_price) {
            return $sale_product_price >= $__product_price ? false : number_format($sale_product_price, 4, '.', '');
        } else {
            switch ($sale['sale_specials_condition']) {
                case 0:
                    return $sale_product_price >= $__product_price ? false : number_format($sale_product_price, 4, '.', '');
                    break;
                case 1:
                    return $special_price >= $__product_price ? false : number_format($special_price, 4, '.', '');
                    break;
                case 2:
                    return $sale_special_price >= $__product_price ? false : number_format($sale_special_price, 4, '.', '');
                    break;
                default:
                    return $special_price >= $__product_price ? false : number_format($special_price, 4, '.', '');
            }
        }
    }

    public static function get_products_price_edit_order($products_id, $currency_id = 0, $group_id = 0, $qty = 1, $recalculate_value = false) {
        $price = \common\helpers\Product::get_products_price($products_id, 1, false, $currency_id, $group_id);
        if (CUSTOMERS_GROUPS_ENABLE == 'True' && $group_id != 0 && ($price === false || $price == -2) && $recalculate_value) {
            $discount = tep_db_fetch_array(tep_db_query('select groups_discount from ' . TABLE_GROUPS . " where groups_id = '" . (int)$group_id . "'"));
            $price = \common\helpers\Product::get_products_price($products_id, 1, 0, $currency_id, 0);
            $price = $price * (100 - $discount['groups_discount']) / 100;
        }
        if ($qty > 1) {
            $discount_price = self::get_products_discount_price($products_id, 1, false, $currency_id, $group_id);
            if (CUSTOMERS_GROUPS_ENABLE == 'True' && $group_id != 0 && $discount_price === false && $recalculate_value) {
                $discount_price = self::get_products_discount_price($products_id, 1, false, $currency_id, 0);
                $apply_discount = true;
            }
            if ($discount_price !== false && $discount_price != -1) {
                $ar = preg_split("/[:;]/", $discount_price);
                for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                    if ($qty < $ar[$i]) {
                        if ($i > 0) {
                            $price = $ar[$i - 1];
                        }
                        break;
                    }
                }
                if (sizeof($ar) > 2 && $qty >= $ar[sizeof($ar) - 2]) {
                    $discount_price = $ar[sizeof($ar) - 1];
                }
                if ($apply_discount) {
                    $discount = tep_db_fetch_array(tep_db_query('select groups_discount from ' . TABLE_GROUPS . " where groups_id = '" . (int)$group_id . "'"));
                    $discount_price = $discount_price * (100 - $discount['groups_discount']) / 100;
                }
            }
            return $discount_price;
        } else {
            return $price;
        }
    }

    public static function get_products_price_for_edit($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select products_group_price as products_price from " . TABLE_PRODUCTS_PRICES . " where  products_id = '" . (int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);

        if ($product['products_price'] == '' && $default != '') {
            $product['products_price'] = $default;
        }
        return $product['products_price'];
    }

    /*function get_products_price($product_id, $currency_id = 0, $group_id = 0, $default = '')*/
    public static function get_products_price($products_id, $qty = 1, $price = 0, $curr_id = 0, $group_id = 0) {
        $type = 'unit';
        if (is_array($qty)) {
            if (count($qty) > 1) {
                die('stop');
                $fullPrice = 0;
                foreach ($qty as $key => $value) {
                    $fullPrice += self::get_products_price($products_id, [$key => $value], $price, $curr_id, $group_id);
                }
                return $fullPrice;
            }
            foreach ($qty as $key => $value) {
                $type = $key;
                $qty = $value;
                break;
            }
        }
        
        global $currency_id, $customer_groups_id;
        
        if ($curr_id > 0) {
            $_currency_id = $curr_id;
        } else {
            $_currency_id = $currency_id;
        }
        if ($group_id > 0) {
            $_customer_groups_id = $group_id;
        } else {
            $_customer_groups_id = $customer_groups_id;
        }
        if (PRODUCTS_BUNDLE_SETS != 'True' && USE_MARKET_PRICES != 'True' && CUSTOMERS_GROUPS_ENABLE != 'True' && $price > 0 && $qty == 1) {
            return $price;
        }
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }        

        if (\common\helpers\Acl::checkExtension('ProductBundles', 'allowed') && PRODUCTS_BUNDLE_SETS == 'True') {

            $products2c_join = '';
            if (\common\classes\platform::activeId()) {
                $products2c_join .=
                        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' " .
                        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
            }

            $bundle_sets_query = tep_db_query("select distinct p.products_id, sp.num_product from " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $_customer_groups_id . "' and pgp.currencies_id = '" . (int) (USE_MARKET_PRICES == 'True' ? $_currency_id : 0) . "', " . TABLE_SETS_PRODUCTS . " sp where sp.product_id = p.products_id and sp.sets_id = '" . (int) $products_id . "' and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) order by sp.sort_order");
            if (tep_db_num_rows($bundle_sets_query) > 0) {
                $bundle_sets_price = 0;
                while ($bundle_sets = tep_db_fetch_array($bundle_sets_query)) {
                    if (($new_price = self::get_products_special_price($bundle_sets['products_id'], $qty * $bundle_sets['num_product']))) {
                        $bundle_sets_price += $bundle_sets['num_product'] * $new_price;
                    } else {
                        $bundle_sets_price += $bundle_sets['num_product'] * self::get_products_price($bundle_sets['products_id'], $qty * $bundle_sets['num_product']);
                    }
                }
                return $bundle_sets_price;
            }
        }

        $discount = 0;
        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select products_group_price as products_price, products_group_price_pack_unit as products_price_pack_unit, products_group_price_packaging as products_price_packaging from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $_currency_id : '0') . "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['products_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select products_group_price as products_price, products_group_price_pack_unit as products_price_pack_unit, products_group_price_packaging as products_price_packaging from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and groups_id = '0' and currencies_id = '" . (int) $_currency_id . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_price, products_price_pack_unit, products_price_packaging from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
                }
                $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                $data['products_price'] = $data['products_price'] * (1 - ($discount / 100));
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select products_price, pack_unit, products_price_pack_unit, packaging, products_price_packaging from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
        }
        
        $pack_info = tep_db_fetch_array(tep_db_query("select pack_unit, packaging from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
        switch ($type) {
            case 'packaging':
                if ($data['products_price_packaging'] > 0) {
                    $data['products_price'] = $data['products_price_packaging'];
                    $data['products_price'] = $data['products_price'] * (1 - ($discount / 100));
                } elseif ($pack_info['pack_unit'] > 0 && $pack_info['packaging'] > 0) {
                    $data['products_price'] = $data['products_price'] * $pack_info['pack_unit'] * $pack_info['packaging'];
                    $data['products_price'] = $data['products_price'] * (1 - ($discount / 100));
                }
                break;
            case 'pack_unit':
                if ($data['products_price_pack_unit'] > 0) {
                    $data['products_price'] = $data['products_price_pack_unit'];
                    $data['products_price'] = $data['products_price'] * (1 - ($discount / 100));
                } elseif ($pack_info['pack_unit'] > 0) {
                    $data['products_price'] = $data['products_price'] * $pack_info['pack_unit'];
                    $data['products_price'] = $data['products_price'] * (1 - ($discount / 100));
                }
                break;
            case 'unit':
            default :
                break;
        }        
        
        if ($qty > 1) {
            return self::get_products_discount_price($products_id, [$type => $qty], $data['products_price']);
        } else {
            return $data['products_price'];
        }
    }

    /* function tep_get_products_discount_price($product_id, $currency_id = 0, $group_id = 0, $default = ''){ */
    public static function get_products_discount_price($products_id, $qty, $products_price, $curr_id = 0, $group_id = 0) {
        $type = 'unit';
        if (is_array($qty)) {
            foreach ($qty as $key => $value) {
                $type = $key;
                $qty = $value;
                break;
            }
        }
        
        global $currency_id, $customer_groups_id;

        if ($curr_id > 0) {
            $_currency_id = $curr_id;
        } else {
            $_currency_id = $currency_id;
        }
        if ($group_id > 0) {
            $_customer_groups_id = $group_id;
        } else {
            $_customer_groups_id = $customer_groups_id;
        }

        $apply_discount = false;
        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select pp.products_group_discount_price as products_price_discount, pp.products_group_price"
                    . ", pp.products_group_discount_price_pack_unit as products_price_discount_pack_unit"
                    . ", pp.products_group_price_pack_unit"
                    . ", pp.products_group_discount_price_packaging as products_price_discount_packaging"
                    . ", pp.products_group_price_packaging"
                    . " from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int) $products_id . "' and pp.groups_id = '" . (int) $_customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $_currency_id : '0') . "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['products_price_discount'] == '' && $data['products_group_price'] == -2) || $data['products_price_discount'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select pp.products_group_discount_price as products_price_discount"
                            . ", products_group_discount_price_pack_unit as products_price_discount_pack_unit"
                            . ", products_group_discount_price_packaging as products_price_discount_packaging"
                            . " from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int) $products_id . "' and pp.groups_id = '0' and pp.currencies_id = '" . (int) $_currency_id . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_price_discount, products_price_discount_pack_unit, products_price_discount_packaging from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
                }
                $apply_discount = true;
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select products_price_discount, products_price_discount_pack_unit, products_price_discount_packaging from " . TABLE_PRODUCTS . " where products_id=" . (int) $products_id));
        }
        
        switch ($type) {
            case 'packaging':
                $data['products_price_discount'] = $data['products_price_discount_packaging'];
                break;
            case 'pack_unit':
                $data['products_price_discount'] = $data['products_price_discount_pack_unit'];
                break;
            case 'unit':
            default :
                break;
        }
        
        
        if ($data['products_price_discount'] == '' || $data['products_price_discount'] == -1) {
            return $products_price;
        }
        $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['products_price_discount'])); // remove final separator
        for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
            if ($qty < $ar[$i]) {
                if ($i == 0) {
                    return $products_price;
                }
                $price = $ar[$i - 1];
                break;
            }
        }
        if ($qty >= $ar[$i - 2]) {
            $price = $ar[$i - 1];
        }
        if ($apply_discount) {
            $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
            $price = $price * (1 - ($discount / 100));
        }
        return $price;
    }

    public static function get_products_discount_table($products_id, $curr_id = 0, $group_id = 0) {
        global $currency, $currency_id, $customer_groups_id;

        if ($curr_id > 0) {
            $_currency_id = $curr_id;
        } else {
            $_currency_id = $currency_id;
        }
        if ($group_id > 0) {
            $_customer_groups_id = $group_id;
        } else {
            $_customer_groups_id = $customer_groups_id;
        }
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }

        $apply_discount = false;
        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select pp.products_group_discount_price as products_price_discount, pp.products_group_price from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int)$products_id . "' and pp.groups_id = '" . (int)$_customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'? $_currency_id :'0'). "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['products_price_discount'] == '' && $data['products_group_price'] == -2) || $data['products_price_discount'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select pp.products_group_discount_price as products_price_discount from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int)$products_id . "' and pp.groups_id = '0' and pp.currencies_id = '" . (int)$_currency_id . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_price_discount from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
                }
                $apply_discount = true;
            }
        } else {
            $data  = tep_db_fetch_array(tep_db_query("select products_price_discount from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
        }
        if ($data['products_price_discount'] == '' || $data['products_price_discount'] == -1) {
            return false;
        }
        $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['products_price_discount'])); // remove final separator
        if ($apply_discount) {
            $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
            for ($i=0, $n=sizeof($ar); $i<$n; $i=$i+2) {
                $ar[$i+1] = $ar[$i+1] * (1 - ($discount/100));
            }
        }
        return $ar;
    }

    public static function is_giveaway($products_id) {
        $query = tep_db_query("select * from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        if (tep_db_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    public static function draw_products_pull_down($name, $parameters = '', $exclude = '') {
        global $currencies, $languages_id, $HTTP_POST_VARS;

        $_params = \Yii::$app->request->getBodyParams();

        if (!isset($_params->currencies)) {
            $currencies = new \common\classes\currencies();
        } else {
            $currencies = $_params->currencies;
        }

        if ($exclude == '') {
            $exclude = array();
        }

        $select_string = '<select name="' . $name . '"';

        if ($parameters) {
            $select_string .= ' ' . $parameters;
        }

        $select_string .= '>';

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and affiliate_id = 0 and pd.language_id = '" . (int) $languages_id . "' order by products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            if (!in_array($products['products_id'], $exclude)) {
                $select_string .= '<option ' . (($HTTP_POST_VARS[$name] == $products['products_id']) ? ' selected ' : '') . ' value="' . $products['products_id'] . '">' . $products['products_name'] . ' (' . $currencies->format(\common\helpers\Product::get_products_price($products['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id']), true, DEFAULT_CURRENCY) . ')</option>';
            }
        }

        $select_string .= '</select>';

        return $select_string;
    }

    public static function get_specials_price($specials_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");
        } else {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS_PRICES . " where  specials_id = '" . (int)$specials_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $specials_data = tep_db_fetch_array($specials_query);
        if ($specials_data['specials_new_products_price'] == '' && $default != '') {
            $specials_data['specials_new_products_price'] = $default;
        }
        return $specials_data['specials_new_products_price'];
    }
    public static function get_sql_product_restrictions($table_prefixes = array('p', 'pd', 's', 'sp', 'pp')) {
      // " . \common\helpers\Product::get_sql_product_restrictions(array('p'=>'')) . "
      $def = array('p', 'pd', 's', 'sp', 'pp');
      if (!is_array($table_prefixes)) {
        $table_prefixes['p'] = (trim($table_prefixes)!=''?rtrim($table_prefixes, '.') . '.':'');
      } else {
        foreach($table_prefixes as $k => $v) {
          if (is_integer($k)) {
            $k = $def[$k];
          }
          $table_prefixes[$k] = (trim($v) != '' ? rtrim($v, '.') . '.':'');
        }
      }
      foreach($def as $k) {
        if (!isset($table_prefixes[$k])) {
          $table_prefixes[$k] = $k . '.';
        }
      }
    
      $where_str = '';
      $r = tep_db_query("select stock_indication_id from " . TABLE_PRODUCTS_STOCK_INDICATION. " where is_hidden=1");
      $tmp = array();
      while ($d = tep_db_fetch_array($r)) {
        $tmp[] = $d['stock_indication_id'];
      }
      if (count($tmp)>0) {
        $where_str .= " and " .$table_prefixes['p'] . "stock_indication_id not in ('" . implode("','", $tmp) . "')";
      }
      return $where_str;
    }
}
