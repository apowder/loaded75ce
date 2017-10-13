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

class Inventory {

    public static function normalize_id($uprid, &$vids = []) {
        if (preg_match("/^\d+$/", $uprid)) {
            return $uprid;
        } else {
            $product_id = self::get_prid($uprid);
            preg_match_all('/\{([\d\-]+)/', $uprid, $arr);
            $oids = $arr[1];
            preg_match_all('/\}(\d+)/', $uprid, $arr);
            $vids = array();
            for ($i = 0; $i < count($arr[1]); $i++) {
                $vids[$oids[$i]] = $arr[1][$i];
            }
            ksort($vids);
            return self::get_uprid($product_id, $vids);
        }
    }

    public static function get_prid($uprid) {
        $pieces = explode('{', $uprid);
        if (is_numeric($pieces[0])) {
            return $pieces[0];
        } else {
            return false;
        }
    }

    public static function get_uprid($prid, $params) {
        $uprid = $prid;
        if ((is_array($params)) && (!strstr($prid, '{'))) {
            while (list($option, $value) = each($params)) {
                $uprid = $uprid . '{' . $option . '}' . $value;
            }
        }
        return $uprid;
    }

    public static function get_inventory_id_by_uprid($uprid) {
        $data = tep_db_fetch_array(tep_db_query("select inventory_id from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));
        return $data['inventory_id'];
    }
    
    public static function product_has_inventory($prid) {
        $data = tep_db_fetch_array(tep_db_query("select count(inventory_id) as total from " . TABLE_INVENTORY . " where prid = '" . (int)$prid . "'"));
        return $data['total'];
    }
    
    public static function get_first_invetory($prid) {
        $data = tep_db_fetch_array(tep_db_query("select products_id from " . TABLE_INVENTORY . " where prid = '" . (int)$prid . "' limit 1"));
        return $data['products_id'];
    }    

    public static function get_inventory_price_prefix_by_uprid($uprid) {
        $data = tep_db_fetch_array(tep_db_query("select price_prefix from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));
        return $data['price_prefix'];
    }

    public static function get_inventory_price_by_uprid($uprid, $qty = 1, $inventory_price = 0, $curr_id = 0, $group_id = 0) {
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
        if (PRODUCTS_BUNDLE_SETS != 'True' && USE_MARKET_PRICES != 'True' && CUSTOMERS_GROUPS_ENABLE != 'True' && $inventory_price > 0 && $qty == 1) {
            return $inventory_price;
        }
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($uprid) . "' and groups_id = '" . (int)$_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "' order by inventory_price asc limit 1");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['inventory_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "' order by inventory_price asc limit 1"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_price from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($uprid) . "' order by inventory_price asc limit 1"));
                }
                $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                $data['inventory_price'] = $data['inventory_price'] * (1 - ($discount / 100));
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select inventory_price from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($uprid) . "' order by inventory_price asc limit 1"));
        }
        if ($qty > 1 && $data['inventory_price'] > 0) {
            return self::get_inventory_discount_price_by_uprid($uprid, $qty, $data['inventory_price'], $curr_id, $group_id);
        } else {
            return $data['inventory_price'];
        }
    }

    public static function get_inventory_discount_price_by_uprid($uprid, $qty, $inventory_price, $curr_id = 0, $group_id = 0) {
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
            $query = tep_db_query("select inventory_group_discount_price as inventory_discount_price, inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($uprid) . "' and groups_id = '" . (int)$_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['inventory_discount_price'] == '' && $data['inventory_price'] == -2) || $data['inventory_discount_price'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_group_discount_price as inventory_discount_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_discount_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));
                }
                $apply_discount = true;
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select inventory_discount_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));
        }

        if ($data['inventory_discount_price'] == '') {
            return $inventory_price;
        }
        $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['inventory_discount_price'])); // remove final separator
        for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
            if ($qty < $ar[$i]) {
                if ($i == 0) {
                    return $inventory_price;
                } else {
                    $price = $ar[$i - 1];
                    break;
                }
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

    public static function get_inventory_full_price_by_uprid($uprid, $qty = 1, $inventory_full_price = 0, $curr_id = 0, $group_id = 0) {
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
        if (PRODUCTS_BUNDLE_SETS != 'True' && USE_MARKET_PRICES != 'True' && CUSTOMERS_GROUPS_ENABLE != 'True' && $inventory_full_price > 0 && $qty == 1) {
            return $inventory_full_price;
        }
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select inventory_full_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($uprid) . "' and groups_id = '" . (int)$_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "' order by inventory_full_price asc limit 1");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['inventory_full_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_full_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$_currency_id : 0) . "' order by inventory_full_price asc limit 1"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_full_price from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($uprid) . "' order by inventory_full_price asc limit 1"));
                }
                $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                $data['inventory_full_price'] = $data['inventory_full_price'] * (1 - ($discount / 100));
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select inventory_full_price from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($uprid) . "' order by inventory_full_price asc limit 1"));
        }
        if ($qty > 1 && $data['inventory_full_price'] > 0) {
            return self::get_inventory_full_discount_price_by_uprid($uprid, $qty, $data['inventory_full_price'], $curr_id, $group_id);
        } else {
            return $data['inventory_full_price'];
        }
    }

    public static function get_inventory_full_discount_price_by_uprid($uprid, $qty, $inventory_full_price, $curr_id = 0, $group_id = 0) {
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

        if ($ext = \common\helpers\Acl::checkExtension('AttributesQuantity', 'getFullInventoryDiscount')) {
            return $ext::getFullInventoryDiscount($uprid, $qty, $inventory_full_price, $_currency_id, $_customer_groups_id);
        }
        return $inventory_full_price;
    }

    public static function get_inventory_weight_by_uprid($uprid) {
        $data = tep_db_fetch_array(tep_db_query("select inventory_weight from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($uprid) . "' order by inventory_weight asc limit 1"));
        return $data['inventory_weight'];
    }

    public static function get_inventory_price($inventory_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select inventory_price from " . TABLE_INVENTORY . " where  inventory_id  = '" . $inventory_id . "'");
        } else {
            $data_query = tep_db_query("select inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where inventory_id = '" . $inventory_id . "' and currencies_id = '" . $currency_id . "' and groups_id = '" . $group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['inventory_price'] == '' && $default != '') {
            $data['inventory_price'] = $default;
        }
        return $data['inventory_price'];
    }

    public static function get_inventory_full_price($inventory_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select inventory_full_price from " . TABLE_INVENTORY . " where  inventory_id  = '" . $inventory_id . "'");
        } else {
            $data_query = tep_db_query("select inventory_full_price from " . TABLE_INVENTORY_PRICES . " where inventory_id = '" . $inventory_id . "' and currencies_id = '" . $currency_id . "' and groups_id = '" . $group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['inventory_full_price'] == '' && $default != '') {
            $data['inventory_full_price'] = $default;
        }
        return $data['inventory_full_price'];
    }

    public static function get_inventory_discount_price($inventory_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select inventory_discount_price from " . TABLE_INVENTORY . " where  inventory_id  = '" . $inventory_id . "'");
        } else {
            $data_query = tep_db_query("select inventory_group_discount_price as inventory_discount_price from " . TABLE_INVENTORY_PRICES . " where inventory_id = '" . $inventory_id . "' and currencies_id = '" . $currency_id . "' and groups_id = '" . $group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['inventory_discount_price'] == '' && $default != '') {
            $data['inventory_discount_price'] = $default;
        }
        return $data['inventory_discount_price'];
    }

    public static function get_inventory_discount_full_price($inventory_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($ext = \common\helpers\Acl::checkExtension('AttributesQuantity', 'getInventoryDiscount')) {
            return $ext::getInventoryDiscount($inventory_id, $currency_id, $group_id, $default);
        }
        return $default;
    }

    public static function get_inventory_uprid($ar, $idx) {
        reset($ar);
        $next = false;
        foreach ($ar as $key => $value) {
            if ($next) {
                $next = $key;
                break;
            }
            if ($key == $idx) {
                $next = true;
            }
        }
        if ($next !== false && $next !== true) {
            $sub = self::get_inventory_uprid($ar, $next);
        }
        //}
        $ret = array();
        for ($i = 0, $n = sizeof($ar[$idx]); $i < $n; $i++) {
            if (is_array($sub)) {
                for ($j = 0, $m = sizeof($sub); $j < $m; $j++) {
                    $ret[] = '{' . $idx . '}' . $ar[$idx][$i] . $sub[$j];
                }
            } else {
                $ret[] = '{' . $idx . '}' . $ar[$idx][$i];
            }
        }
        return $ret;
    }

    public static function getDetails($products_id, $current_uprid, $params = array()) {
        global $languages_id, $language, $currencies, $cart;

        if ( !($params['qty'] > 0) ) $params['qty'] = 1;
        if (strpos($current_uprid, '{') === false) $current_uprid = $products_id;

        $products_id = \common\helpers\Inventory::get_prid($products_id);

        $inventory_array = array();
        $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_quantity, products_price_full from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
        if ($product = tep_db_fetch_array($product_query)) {
            $product_price = \common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']);
            $special_price = \common\helpers\Product::get_products_special_price($product['products_id'], 1);
            $product_price_old = $product_price;

            $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id = '" . (int)$product['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
            $products_attributes = tep_db_fetch_array($products_attributes_query);
            if ($products_attributes['total'] > 0) {
                $inventory_query = tep_db_query("select * from " . TABLE_INVENTORY . " i where non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip')) . " and prid = '" . (int)$product['products_id'] . "' order by " . ($product['products_price_full'] ? " inventory_full_price" : " inventory_price"));
                while ($inventory = tep_db_fetch_array($inventory_query)) {
                    $inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($inventory['products_id'], $params['qty'], $inventory['inventory_price']);
                    $inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($inventory['products_id'], $params['qty'], $inventory['inventory_full_price']);

                    if ($product['products_price_full'] && $inventory['inventory_full_price'] == -1) {
                        continue; // Disabled for specific group
                    } elseif ($inventory['inventory_price'] == -1) {
                        continue; // Disabled for specific group
                    }

                    if ($product['products_price_full']) {
                        $inventory['actual_price'] = $inventory['inventory_full_price'];
                        if ($special_price !== false) {
                            // if special - subtract difference
                            $inventory['actual_price'] -= $product_price_old - $special_price;
                        }
                    } else {
                        if (\common\helpers\Inventory::get_inventory_price_prefix_by_uprid($products_id) == '-') {
                            $inventory['actual_price'] = $product_price - $inventory['inventory_price'];
                            if ($special_price !== false) {
                                $inventory['actual_price'] = $special_price - $inventory['inventory_price'];
                            }
                        } else {
                            $inventory['actual_price'] = $product_price + $inventory['inventory_price'];
                            if ($special_price !== false) {
                                $inventory['actual_price'] = $special_price + $inventory['inventory_price'];
                            }
                        }
                    }
                    $inventory['actual_price'] = $currencies->display_price($inventory['actual_price'], \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));

                    $inventory['attributes_names'] = $inventory['attributes_names_short'] = '';
                    if (strpos($inventory['products_id'], '{') !== false) {
                        $ar = preg_split('/[\{\}]/', $inventory['products_id']);
                        for ($i=1; $i<sizeof($ar); $i=$i+2) {
                            $option = tep_db_fetch_array(tep_db_query("select products_options_name as name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$ar[$i] . "' and language_id  = '" . (int)$languages_id . "'"));
                            $options_values = tep_db_fetch_array(tep_db_query("select pov.products_options_values_name as name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where products_options_values_id  = '" . (int)$ar[$i+1] . "' and language_id  = '" . (int)$languages_id . "'"));
                            if ($inventory['attributes_names'] == '') {
                                $inventory['attributes_names'] .= $option['name'] . ': ' . $options_values['name'];
                            } else {
                                $inventory['attributes_names'] .= '; ' . $option['name'] . ': ' . $options_values['name'];
                            }
                            if ($inventory['attributes_names_short'] == '') {
                                $inventory['attributes_names_short'] .= $options_values['name'];
                            } else {
                                $inventory['attributes_names_short'] .= '; ' . $options_values['name'];
                            }
                        }
                    }

                    $inventory['stock_indicator'] = \common\classes\StockIndication::product_info(array(
                      'products_id' => $inventory['products_id'],
                      'products_quantity' => $inventory['products_quantity'],
                      'stock_indication_id' => (isset($inventory['stock_indication_id'])?$inventory['stock_indication_id']:null),
                    ));
                    if ( !($inventory['products_quantity'] > 0) ) {
                      $inventory['attributes_names'] .= ' - ' . strip_tags($inventory['stock_indicator']['stock_indicator_text_short']);
                      $inventory['attributes_names_short'] .= ' - ' . strip_tags($inventory['stock_indicator']['stock_indicator_text_short']);
                    }

                    if ($inventory['products_id'] == $current_uprid) {
                      $inventory['selected'] = true;
                    } else {
                      $inventory['selected'] = false;
                    }

                    $inventory_array[$inventory['products_id']] = $inventory;
                }
            }

            $check_inventory = tep_db_fetch_array(tep_db_query("select inventory_id, min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price from " . TABLE_INVENTORY . " i where products_id like '" . tep_db_input($current_uprid) . "' and non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip'))  . " limit 1"));
            if ($check_inventory['inventory_id']) {
                $check_inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($current_uprid, $params['qty'], $check_inventory['inventory_price']);
                $check_inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($current_uprid, $params['qty'], $check_inventory['inventory_full_price']);
                if ($product['products_price_full'] && $check_inventory['inventory_full_price'] != -1) {
                    $product_price = $check_inventory['inventory_full_price'];
                    if ($special_price !== false) {
                        // if special - add difference
                        $special_price += $product_price - $product_price_old;
                    }
                } elseif ($check_inventory['inventory_price'] != -1) {
                    $product_price += $check_inventory['inventory_price'];
                    if ($special_price !== false) {
                        $special_price += $check_inventory['inventory_price'];
                    }
                }
            }
        }

        $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
        $_backup_products_quantity = 0;
        $_backup_stock_indication_id = 0;
        if ($product = tep_db_fetch_array($product_query)) {
            $_backup_products_quantity = $product['products_quantity'];
            $_backup_stock_indication_id = $product['stock_indication_id'];
        }

        $check_inventory = tep_db_fetch_array(tep_db_query(
          "select inventory_id, products_quantity, stock_indication_id ".
          "from " . TABLE_INVENTORY . " ".
          "where products_id like '" . tep_db_input($current_uprid) . "' ".
          "limit 1"
        ));
        $get_dynamic_prop_r = tep_db_query(
          "SELECT ".
          "  IF(LENGTH(i.products_model)>0,i.products_model, p.products_model) AS products_model, ".
          "  IF(LENGTH(i.products_upc)>0,i.products_upc, p.products_upc) AS products_upc, ".
          "  IF(LENGTH(i.products_ean)>0,i.products_ean, p.products_ean) AS products_ean, ".
          "  IF(LENGTH(i.products_asin)>0,i.products_asin, p.products_asin) AS products_asin, ".
          "  IF(LENGTH(i.products_isbn)>0,i.products_isbn, p.products_isbn) AS products_isbn ".
          "FROM ".TABLE_PRODUCTS." p ".
          " LEFT JOIN ".TABLE_INVENTORY." i ON i.prid=p.products_id AND i.products_id='".tep_db_input($current_uprid)."' ".
          "WHERE p.products_id='".intval($products_id)."' "
        );
        $dynamic_prop = array();
        if ( tep_db_num_rows($get_dynamic_prop_r)>0 ) {
          $dynamic_prop = tep_db_fetch_array($get_dynamic_prop_r);
        }

        $stock_indicator = \common\classes\StockIndication::product_info(array(
          'products_id' => $current_uprid,
          'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : $_backup_products_quantity),
          'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:$_backup_stock_indication_id),
        ));
        $stock_indicator_public = $stock_indicator['flags'];
        $stock_indicator_public['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($current_uprid, $stock_indicator['max_qty'], true);
        $stock_indicator_public['stock_code'] = $stock_indicator['stock_code'];
        $stock_indicator_public['stock_indicator_text'] = $stock_indicator['stock_indicator_text'];
        if ($stock_indicator_public['request_for_quote']){
          $special_price = false;
        }

        global $currency;
        $return_data = [
            'product_valid' => (strpos($current_uprid, '{') !== false ? '1' : '0'),
            'product_price' => $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']), 1, ($special_price === false ? true : '')),
            'product_unit_price' => $currencies->display_price_clear(($product_price), 0),
            'tax' => \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']),
            'special_price' => ($special_price !== false ? $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']), 1, true) : ''),
            'special_unit_price' => ($special_price !== false ? $currencies->display_price_clear(($special_price), 0) : ''),
            'product_qty' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : ($product['products_quantity']?$product['products_quantity']:'0')),
            'product_in_cart' => \frontend\design\Info::checkProductInCart($current_uprid),
            'current_uprid' => $current_uprid,
            'inventory_array' => $inventory_array,
            'stock_indicator' => $stock_indicator_public,
            'dynamic_prop' => $dynamic_prop,
            ];

        return $return_data;
    }


    public static function get_sql_inventory_restrictions($table_prefixes = array('i', 'ip')) {
      // " . \common\helpers\Product::get_sql_product_restrictions(array('p'=>'')) . "
      $def = array('i', 'ip');
      if (!is_array($table_prefixes)) {
        $table_prefixes['i'] = (trim(table_prefixes)!=''?rtrim($table_prefixes, '.') . '.':'');
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
        $where_str .= " and " .$table_prefixes['i'] . "stock_indication_id not in ('" . implode("','", $tmp) . "')";
      }
      return $where_str;
    }
}
