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

class Bundles {

    public static function getDetails($params) {
        global $languages_id, $customer_groups_id, $HTTP_SESSION_VARS, $currency_id, $cart, $currencies;

        if ( !$params['products_id'] ) return '';

        $bundle_products = array();
        $bundle_products_attributes = array();

        $attributes = $params['id'];
        if (is_array($attributes)) {
          foreach ($attributes as $key => $value) {
            if (strpos($key, '-') !== false) {
              list($bundle_item_attr_id, $bundle_item_id) = explode('-', $key);
              $bundle_products_attributes[$bundle_item_id][$bundle_item_attr_id] = $value;
            }
          }
        }

        $products_join = '';
        if ( \common\classes\platform::activeId() ) {
          $products_join .=
            " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
            " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
            " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $bundle_sets_query = tep_db_query(
          "select p.products_id, p.products_model, p.products_image, p.products_tax_class_id, sp.num_product, ".
          "parent_p.products_tax_class_id AS parent_products_tax_class_id, ".
          "if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name ".
          "from " . TABLE_PRODUCTS . " p {$products_join} " .
          "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' ".
          " left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int)$customer_groups_id . "' and pgp.currencies_id = '" . (int)(USE_MARKET_PRICES == 'True' ? $currency_id : 0) . "', "  . TABLE_PRODUCTS_DESCRIPTION . " pd, ".
          "" . TABLE_SETS_PRODUCTS . " sp ".
          " inner join ".TABLE_PRODUCTS." parent_p on parent_p.products_id=sp.sets_id ".
          "where sp.product_id = p.products_id and sp.product_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' ".
          " and sp.sets_id = '" . (int)$params['products_id'] . "' and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . " and pd.affiliate_id = 0 and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) ".
          "group by p.products_id ".
          "order by sp.sort_order"
        );
        if (tep_db_num_rows($bundle_sets_query) > 0)
        {
          $all_filled_array = array();
          $product_qty_array = array();
          $quantity_max_array = array();
          $stock_indicators_ids = array();
          $stock_indicators_array = array();
          while ($bundle_sets = tep_db_fetch_array($bundle_sets_query))
          {
            $products_id = $bundle_sets['products_id'];
            if (is_array($bundle_products_attributes[$products_id])) {
              $attributes = $bundle_products_attributes[$products_id];
            } else {
              $attributes = array();
            }

            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_quantity, products_price_full from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            if ($product = tep_db_fetch_array($product_query)) {
                $product_price = \common\helpers\Product::get_products_price($product['products_id'], $params['qty'] * $bundle_sets['num_product'], $product['products_price']);
                $special_price = \common\helpers\Product::get_products_special_price($product['products_id'], $params['qty'] * $bundle_sets['num_product']);
                $product_price_old = $product_price;

                $comb_arr = $attributes;
                foreach ($comb_arr as $opt_id => $val_id) {
                    if ( !($val_id > 0) ) {
                        $comb_arr[$opt_id] = '0000000';
                    }
                }
                reset($comb_arr);
                $mask = str_replace('0000000', '%', \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $comb_arr)));
                $check_inventory = tep_db_fetch_array(tep_db_query("select inventory_id, min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price from " . TABLE_INVENTORY . " i where products_id like '" . tep_db_input($mask) . "' and non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip'))  . " limit 1"));
                if ($check_inventory['inventory_id']) {
                    $check_inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_price']);
                    $check_inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_full_price']);
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
            $actual_product_price = $product_price;

            $products_options_name_query = tep_db_query("select distinct p.products_id, p.products_tax_class_id, popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where p.products_id = '" . (int)$products_id . "' and patrib.products_id = p.products_id and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_sort_order, popt.products_options_name");
            while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
                if (!isset($attributes[$products_options_name['products_options_id']])) {
                    $check = tep_db_fetch_array(tep_db_query("select max(pov.products_options_values_id) as values_id, count(pov.products_options_values_id) as values_count from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$products_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'"));
                    if ($check['values_count'] == 1) { // if only one option value - it should be selected
                        $attributes[$products_options_name['products_options_id']] = $check['values_id'];
                    } else {
                        $attributes[$products_options_name['products_options_id']] = 0;
                    }
                }
            }

            $all_filled = true;
            if (isset($attributes) && is_array($attributes))
            foreach($attributes as $value) {
               $all_filled = $all_filled && (bool)$value;
            }

            $attributes_array = array();
            tep_db_data_seek($products_options_name_query, 0);
            while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
                $products_options_array = array();
                $products_options_query = tep_db_query(
                  "select pa.products_attributes_id, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix ".
                  "from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ".
                  "where pa.products_id = '" . (int)$products_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "' ".
                  "order by pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name");
                while ($products_options = tep_db_fetch_array($products_options_query)) {
                    $comb_arr = $attributes;
                    $comb_arr[$products_options_name['products_options_id']] = $products_options['products_options_values_id'];
                    foreach ($comb_arr as $opt_id => $val_id) {
                        if ( !($val_id > 0) ) {
                            $comb_arr[$opt_id] = '0000000';
                        }
                    }
                    reset($comb_arr);
                    $mask = str_replace('0000000', '%', \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $comb_arr)));
                    $check_inventory = tep_db_fetch_array(tep_db_query(
                      "select inventory_id, max(products_quantity) as products_quantity, stock_indication_id, stock_delivery_terms_id, ".
                      " min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price ".
                      "from " . TABLE_INVENTORY . " i ".
                      "where products_id like '" . tep_db_input($mask) . "' and non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip'))  . " ".
                      "order by products_quantity desc ".
                      "limit 1"
                    ));
                    if (!$check_inventory['inventory_id']) continue;
                    $check_inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_price']);
                    $check_inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_full_price']);
                    if ($product['products_price_full'] && $check_inventory['inventory_full_price'] == -1) {
                        continue; // Disabled for specific group
                    } elseif ($check_inventory['inventory_price'] == -1) {
                        continue; // Disabled for specific group
                    }

                    $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name'], 'price_diff' => 0);
                    if ($product['products_price_full']) {
                        $price_diff = $check_inventory['inventory_full_price'] - $actual_product_price;
                    } else {
                        $price_diff = $product_price_old + $check_inventory['inventory_price'] - $actual_product_price;
                    }
                    if ($price_diff != '0') {
                        $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . ($price_diff < 0 ? '-' : '+') . $currencies->display_price(abs($price_diff), \common\helpers\Tax::get_tax_rate($products_options_name['products_tax_class_id'])) .') ';
                    }
                    $products_options_array[sizeof($products_options_array)-1]['price_diff'] = $price_diff;

                    $stock_indicator = \common\classes\StockIndication::product_info(array(
                      'products_id' => $check_inventory['products_id'],
                      'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : '0'),
                      'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:null),
                      'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id'])?$check_inventory['stock_delivery_terms_id']:null),
                    ));

                    if ( !($check_inventory['products_quantity'] > 0) ) {
                      $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="outstock" data-max-qty="' . (int)$stock_indicator['max_qty'] . '"';
                      $products_options_array[sizeof($products_options_array)-1]['text'] .= ' - ' . strip_tags($stock_indicator['stock_indicator_text_short']);
                    } else {
                      $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="outstock" data-max-qty="'.(int)$stock_indicator['max_qty'].'"';
                    }
                }

                if ($attributes[$products_options_name['products_options_id']] > 0) {
                    $selected_attribute = $attributes[$products_options_name['products_options_id']];
                } elseif (isset($cart->contents[$params['products_id']]['attributes'][$products_options_name['products_options_id'] . '-' . $products_id])) {
                    $selected_attribute = $cart->contents[$params['products_id']]['attributes'][$products_options_name['products_options_id'] . '-' . $products_id];
                } else {
                    $selected_attribute = false;
                }

                $attributes_array[] = array(
                    'title' => htmlspecialchars($products_options_name['products_options_name']),
                    'name' => 'id[' . $products_options_name['products_options_id'] . '-' . $products_id . ']',
                    'options' => $products_options_array,
                    'selected' => $selected_attribute,
                );

            }

            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, stock_delivery_terms_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $_backup_products_quantity = 0;
            $_backup_stock_indication_id = $_backup_stock_delivery_terms_id = 0;
            if ($product = tep_db_fetch_array($product_query)) {
                $_backup_products_quantity = $product['products_quantity'];
                $_backup_stock_indication_id = $product['stock_indication_id'];
                $_backup_stock_delivery_terms_id = $product['stock_delivery_terms_id'];
            }
            $current_uprid = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $attributes));
            $bundle_sets['current_uprid'] = $current_uprid;

            $check_inventory = tep_db_fetch_array(tep_db_query(
              "select inventory_id, products_quantity, stock_indication_id, stock_delivery_terms_id ".
              "from " . TABLE_INVENTORY . " ".
              "where products_id like '" . tep_db_input($current_uprid) . "' ".
              "limit 1"
            ));

            $stock_indicator = \common\classes\StockIndication::product_info(array(
              'products_id' => $current_uprid,
              'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : $_backup_products_quantity),
              'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:$_backup_stock_indication_id),
              'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id'])?$check_inventory['stock_delivery_terms_id']:$_backup_stock_delivery_terms_id),
            ));
            $stock_indicator_public = $stock_indicator['flags'];
            $stock_indicator_public['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($current_uprid, $stock_indicator['max_qty'], true);
            $stock_indicator_public['stock_code'] = $stock_indicator['stock_code'];
            $stock_indicator_public['text_stock_code'] = $stock_indicator['text_stock_code'];
            $stock_indicator_public['stock_indicator_text'] = $stock_indicator['stock_indicator_text'];
            if ($stock_indicator_public['request_for_quote']) {
              $special_price = false;
            }
            $bundle_sets['stock_indicator'] = $stock_indicator_public;

            if ($stock_indicator['id'] > 0) {
              $stock_indicators_ids[] = $stock_indicator['id'];
              $stock_indicators_array[$stock_indicator['id']] = $stock_indicator_public;
            }

            $bundle_sets['all_filled'] = $all_filled;
            $bundle_sets['product_qty'] = ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : ($product['products_quantity']?$product['products_quantity']:'0'));

            $all_filled_array[] = $bundle_sets['all_filled'];
            $product_qty_array[] = floor($bundle_sets['num_product'] > 0 ? $bundle_sets['product_qty'] / $bundle_sets['num_product'] : $bundle_sets['product_qty']);
            $quantity_max_array[] = floor($bundle_sets['num_product'] > 0 ? $stock_indicator_public['quantity_max'] / $bundle_sets['num_product'] : $stock_indicator_public['quantity_max']);

            if ($special_price !== false) {
              $bundle_sets['price_old'] = $currencies->display_price($bundle_sets['num_product'] * $product_price, \common\helpers\Tax::get_tax_rate($bundle_sets['products_tax_class_id']));
              $bundle_sets['price_special'] = $currencies->display_price($bundle_sets['num_product'] * $special_price, \common\helpers\Tax::get_tax_rate($bundle_sets['products_tax_class_id']));
            } else {
              $bundle_sets['price'] = $currencies->display_price($bundle_sets['num_product'] * $product_price, \common\helpers\Tax::get_tax_rate($bundle_sets['products_tax_class_id'])); 
            }

            $bundle_sets['attributes_array'] = $attributes_array;

            $bundle_sets['image'] = \common\classes\Images::getImageUrl($bundle_sets['products_id'], 'Small');
            $bundle_sets['product_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $bundle_sets['products_id']);

            $bundle_products[] = $bundle_sets;
          }

          if (count($stock_indicators_ids) > 0) {
            $stock_indicators_sorted = \common\classes\StockIndication::sortStockIndicators($stock_indicators_ids);
            $bundle_stock_indicator = $stock_indicators_array[$stock_indicators_sorted[count($stock_indicators_sorted)-1]];
          } else {
            $bundle_stock_indicator = $stock_indicator_public;
          }
          $bundle_stock_indicator['quantity_max'] = min($quantity_max_array);

          $return_data = [
              'product_valid' => (min($all_filled_array) ? '1' : '0'),
              'product_qty' => min($product_qty_array),
              'bundle_products' => $bundle_products,
              'stock_indicator' => $bundle_stock_indicator,
          ];
          return $return_data;
        }
    }

}
