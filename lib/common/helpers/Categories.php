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

class Categories {

    public static function get_categories_name($who_am_i) {
        global $languages_id, $HTTP_SESSION_VARS;

        $the_categories_name = tep_db_fetch_array(tep_db_query("select if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd.categories_id = cd1.categories_id and cd1.affiliate_id = '" . (int) $HTTP_SESSION_VARS['affiliate_ref'] . "' and cd1.language_id = '" . (int) $languages_id . "' and cd1.categories_id = '" . (int) $who_am_i . "' where cd.categories_id = '" . (int) $who_am_i . "' and cd.language_id = '" . (int) $languages_id . "' and cd.affiliate_id = '0'"));
        return $the_categories_name['categories_name'];
    }
    
    public static function get_categories($categories_array = '', $parent_id = '0', $indent = '') {
        global $languages_id, $HTTP_SESSION_VARS;

        if (!is_array($categories_array))
            $categories_array = array();
        $categories_query = tep_db_query("select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on c.categories_id = cd1.categories_id and cd1.affiliate_id = '" . (tep_session_is_registered('affiliate_ref') ? (int) $HTTP_SESSION_VARS['affiliate_ref'] : '0') . "' and cd1.language_id = '" . (int) $languages_id . "' where c.parent_id = '" . (int) $parent_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "' AND c.categories_status = 1 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $categories_array[] = array('id' => $categories['categories_id'],
                'text' => $indent . $categories['categories_name']);

            if ($categories['categories_id'] != $parent_id) {
                $categories_array = self::get_categories($categories_array, $categories['categories_id'], $indent . '&nbsp;&nbsp;');
            }
        }
        return $categories_array;
    }

    public static function get_path($current_category_id = '') {
        global $cPath_array;

        if (tep_not_null($current_category_id)) {
            $cp_size = sizeof($cPath_array);
            if ($cp_size == 0) {
                $cPath_new = $current_category_id;
            } else {
                $cPath_new = '';
                $last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $cPath_array[($cp_size - 1)] . "'");
                $last_category = tep_db_fetch_array($last_category_query);

                $current_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $current_category_id . "'");
                $current_category = tep_db_fetch_array($current_category_query);

                if ($last_category['parent_id'] == $current_category['parent_id']) {
                    for ($i = 0; $i < ($cp_size - 1); $i++) {
                        $cPath_new .= '_' . $cPath_array[$i];
                    }
                } else {
                    for ($i = 0; $i < $cp_size; $i++) {
                        $cPath_new .= '_' . $cPath_array[$i];
                    }
                }
                $cPath_new .= '_' . $current_category_id;

                if (substr($cPath_new, 0, 1) == '_') {
                    $cPath_new = substr($cPath_new, 1);
                }
            }
        } else {
            $cPath_new = implode('_', $cPath_array);
        }

        return 'cPath=' . $cPath_new;
    }

    public static function count_products_in_category($category_id, $include_inactive = false) {
        Global $customer_groups_id, $HTTP_SESSION_VARS, $currency_id;
        $products_count = 0;

        if (!$include_inactive) {
            $add_sql = " and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ";
        }

        $categories_join = '';
        $products_join = '';
        if (\common\classes\platform::activeId()) {
            $categories_join .=
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
            $products_join .=
                    " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        if ($customer_groups_id == 0) {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} " . " where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $category_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql));
        } else {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "' and pgp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' " . " where p.products_id = p2c.products_id and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $category_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql));
        }

        return $products['total'];
    }
    
    public static function has_category_subcategories($category_id) {

        $categories_join = '';
        if (\common\classes\platform::activeId()) {
            $categories_join .=
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $child_category = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CATEGORIES . " c {$categories_join} where c.parent_id = '" . (int) $category_id . "' and c.categories_status = 1"));

        if ($child_category['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function get_subcategories(&$subcategories_array, $parent_id = 0, $include_deactivated = true) {
        $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $parent_id . "'" . (!$include_deactivated ? " and categories_status = 1" : ''));
        while ($subcategories = tep_db_fetch_array($subcategories_query)) {
            $subcategories_array[$subcategories['categories_id']] = $subcategories['categories_id'];
            if ($subcategories['categories_id'] != $parent_id) {
                self::get_subcategories($subcategories_array, $subcategories['categories_id'], $include_deactivated);
            }
        }
    }
    
    public static function get_parent_categories(&$categories, $categories_id) {
        $parent_categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $categories_id . "' and categories_status = 1 ");
        while ($parent_categories = tep_db_fetch_array($parent_categories_query)) {
            if ($parent_categories['parent_id'] == 0)
                return true;
            $categories[sizeof($categories)] = $parent_categories['parent_id'];
            if ($parent_categories['parent_id'] != $categories_id) {
                self::get_parent_categories($categories, $parent_categories['parent_id']);
            }
        }
    }
    
    public static function parse_category_path($cPath) {
        $string_to_int = function($string) {
            return (int)$string;
        };
        $cPath_array = array_map($string_to_int, explode('_', $cPath));
        $tmp_array = array();
        $n = sizeof($cPath_array);
        for ($i = 0; $i < $n; $i++) {
            if (!in_array($cPath_array[$i], $tmp_array)) {
                $tmp_array[] = $cPath_array[$i];
            }
        }
        return $tmp_array;
    }

    public static function get_category_filters($categories_id) {
        $filters_array = array();
        if ($categories_id > 0) {
            $filters_query = tep_db_query("select c.parent_id, f.filters_type, f.options_id, f.properties_id, f.status from " . TABLE_CATEGORIES . " c left join " . TABLE_FILTERS . " f on c.categories_id = f.categories_id where c.categories_id = '" . (int) $categories_id . "' order by f.sort_order");
            while ($filters = tep_db_fetch_array($filters_query)) {
                $parent_id = $filters['parent_id'];
                if ($filters['status']) {
                    $filters_array[] = $filters;
                }
            }
            if (count($filters_array) == 0 && $parent_id > 0) {
                return self::get_category_filters($parent_id);
            }
        } else {
            $filters_query = tep_db_query("select f.filters_type, f.options_id, f.properties_id, min(f.sort_order) from " . TABLE_CATEGORIES . " c left join " . TABLE_FILTERS . " f on c.categories_id = f.categories_id where c.parent_id = '0' and f.status = '1' group by f.filters_type, f.options_id, f.properties_id order by min(f.sort_order)");
            while ($filters = tep_db_fetch_array($filters_query)) {
                $filters_array[] = $filters;
            }
        }
        return $filters_array;
    }

    public static function remove_category($category_id) {
        $category_image_query = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        $category_image = tep_db_fetch_array($category_image_query);

        self::remove_category_image($category_image['categories_image']);

        tep_db_query("delete from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_PLATFORMS_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public static function remove_category_image($filename) {
        $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where categories_image = '" . tep_db_input($filename) . "'");
        $duplicate_image = tep_db_fetch_array($duplicate_image_query);

        if ($duplicate_image['total'] < 2) {
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename);
            }
        }
    }

    public static function set_categories_status($category_id, $status) {
        $chk_status = tep_db_fetch_array(tep_db_query("select categories_status from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'"));
        if (!isset($chk_status['categories_status']) || (int) $chk_status['categories_status'] == $status)
            return;

        if ($status == '1') {
            tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = NULL, categories_status = '1', last_modified = now() where categories_id = '" . $category_id . "'");
            $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $category_id);
            while ($data = tep_db_fetch_array($query)) {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']);
            }
            $tree = self::get_category_tree($category_id);
            for ($i = 1; $i < sizeof($tree); $i++) {
                tep_db_query("update " . TABLE_CATEGORIES . " set  categories_status = IFNULL(previous_status, '1'), previous_status = NULL, last_modified = now() where categories_id = '" . $tree[$i]['id'] . "'");
                $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $tree[$i]['id']);
                while ($data = tep_db_fetch_array($query)) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set  products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']);
                }
            }
        } elseif ($status == '0') {
            tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = NULL, categories_status = '0', last_modified = now() where categories_id = '" . $category_id . "'");
            $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $category_id);
            while ($data = tep_db_fetch_array($query)) {
                tep_db_query("update " . TABLE_PRODUCTS . " set previous_status = products_status, products_status = '0' where products_id = " . $data['products_id']);
            }
            $tree = self::get_category_tree($category_id);
            for ($i = 1; $i < sizeof($tree); $i++) {
                tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = categories_status, categories_status = '0', last_modified = now() where categories_id = '" . $tree[$i]['id'] . "'");
                $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $tree[$i]['id']);
                while ($data = tep_db_fetch_array($query)) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set previous_status = products_status, products_status = '0' where products_id = " . $data['products_id']);
                }
            }
        }
    }

    public static function get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $with_full_path = false, $platform_id = 0, $active = false, $add_products = false) {
        global $languages_id;
        
        if (!is_array($category_tree_array))
            $category_tree_array = array();
        if ((sizeof($category_tree_array) < 1) && ($exclude != '0'))
            $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP, 'desc'=>'cat');

        if ($include_itself) {
            $category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd where cd.language_id = '" . (int) $languages_id . "' and cd.categories_id = '" . (int) $parent_id . "' and affiliate_id = 0");
            $category = tep_db_fetch_array($category_query);
            $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name'], 'desc'=>'cat', 'parent_id'=>$parent_id);
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . 
                                         TABLE_CATEGORIES_DESCRIPTION . " cd " .
                                         ($platform_id? " left join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") . 
                                         " where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "'"  .       
                                         ($active? " and c.categories_status = 1 " : "") .
                                         " and c.parent_id = '" . (int) $parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");
                                         
        while ($categories = tep_db_fetch_array($categories_query)) {
            if ($exclude != $categories['categories_id']) {
                $products = [];
                if ($add_products){
                    $products = self::products_in_category($categories['categories_id'], false, $platform_id, $children_spacing);
                }
                $category_tree_array[] = array('id' => $categories['categories_id'], 'text' => $spacing . $categories['categories_name'], 'desc'=>'cat', 'parent_id'=>$parent_id, 'products' => $products);                
            }
            $children_spacing = $spacing . '&nbsp;&nbsp;&nbsp;';
            if ($with_full_path) {
                $children_spacing = $spacing . $categories['categories_name'] . '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
            }
            $category_tree_array = self::get_category_tree($categories['categories_id'], $children_spacing, $exclude, $category_tree_array, false, $with_full_path, $platform_id, $active, $add_products);
        }

        return $category_tree_array;
    }
        
    public static function products_in_category($categories_id, $include_deactivated = false, $platform_id = 0, $spacing = '') {
        global $languages_id;
        $products_array = [];

        $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p " . 
                                       ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                       " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id ."' and pd.affiliate_id = 0 " .
                                       ", " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id " . 
                                       (!$include_deactivated? "and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "":"" ) ." and p2c.categories_id = '" . (int) $categories_id . "' order by p.sort_order, pd.products_name");
        
        
        if (tep_db_num_rows($products_query)){
            while($products = tep_db_fetch_array($products_query)){
               $products_array[] =  array('id' => $products['products_id'], 'text' => $spacing . $products['products_name'], 'desc'=>'prod', 'parent_id'=>$categories_id);
            }            
        }
        return $products_array;
    } 

    public static function get_full_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $platform_id = 0, $active = false, $level = 0) {
      global $languages_id;
      
      if (!is_array($category_tree_array)) $category_tree_array = array();

      if ($include_itself && $parent_id != 0) {
        $category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_CATEGORIES . " c on c.categories_id = cd.categories_id ".
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") .
                                      " where cd.language_id = '" . (int)$languages_id . "' and cd.affiliate_id = 0 and cd.categories_id = '" . (int)$parent_id . "'" .
                                      ($active? " and c.categories_status = 1" : ""));
        $category = tep_db_fetch_array($category_query);
        $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name'], 'category' => '1', 'level' => $level);
      }

      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd " .
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") .
                                       " where c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' " . 
                                       ($active? " and c.categories_status = 1" : "") .
                                       " order by c.sort_order, cd.categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        if ($exclude != $categories['categories_id']) $category_tree_array[] = array('id' => $categories['categories_id'], 'text' => $spacing . $categories['categories_name'], 'category' => '1', 'level' => $level);
        $category_tree_array = self::get_full_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $platform_id, $active, $level+1);

        $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p " .
                                        ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                        ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" .(int)$categories['categories_id'] . "' and pd.affiliate_id = 0 and pd.language_id = '" . (int)$languages_id . "' " .
                                        ($active? " and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : "") .
                                        " order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)){
          $category_tree_array[] = array('id' => $products['products_id'], 'text' => $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $products['products_name'], 'parent_id' => $categories['categories_id'], 'category' => '0');
        }

      }

      if ($parent_id == 0){
        $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p "  .
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                       ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" .(int)$parent_id . "' and pd.affiliate_id = 0 and pd.language_id = '" . (int)$languages_id . "' ".
                                       ($active? " and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : "") .
                                       " order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)){
          $category_tree_array[] = array('id' => $products['products_id'], 'text' => $spacing . '&nbsp;&nbsp;&nbsp;' . $products['products_name'], 'parent_id' => $parent_id, 'category' => '0');
        }
      }

      return $category_tree_array;
    }    

    public static function products_in_category_count($categories_id, $include_deactivated = false) {
        $products_count = 0;

        if ($include_deactivated) {
            $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . (int) $categories_id . "'");
        } else {
            $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p2c.categories_id = '" . (int) $categories_id . "'");
        }

        $products = tep_db_fetch_array($products_query);

        $products_count += $products['total'];

        $childs_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $categories_id . "'");
        if (tep_db_num_rows($childs_query)) {
            while ($childs = tep_db_fetch_array($childs_query)) {
                $products_count += self::products_in_category_count($childs['categories_id'], $include_deactivated);
            }
        }

        return $products_count;
    }

    public static function childs_in_category_count($categories_id) {
        $categories_count = 0;

        $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $categories_id . "'");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $categories_count++;
            $categories_count += self::childs_in_category_count($categories['categories_id']);
        }

        return $categories_count;
    }

    public static function output_generated_category_path($id, $from = 'category', $format = '%2$s', $line_separator = '<br>') {
        $calculated_category_path_string = '';
        $calculated_category_path = self::generate_category_path($id, $from);
        for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i++) {
            for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j++) {
                $variant = $calculated_category_path[$i][$j];
                if ($from == 'category' && $variant['id'] == 0 && count($calculated_category_path[$i]) == 1) {
                    $variant['text'] = TEXT_TOP;
                }
                $calculated_category_path_string .= (empty($format) ? $variant['text'] : sprintf($format, $variant['id'], $variant['text'])) . '&nbsp;&gt;&nbsp;';
            }
            $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . $line_separator;
        }
        $calculated_category_path_string = substr($calculated_category_path_string, 0, -(strlen($line_separator)));

        if (strlen($calculated_category_path_string) < 1)
            $calculated_category_path_string = (empty($format) ? TEXT_TOP : sprintf($format, '0', TEXT_TOP));

        return $calculated_category_path_string;
    }

    public static function get_category_path($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false) {
        global $languages_id;

        if (!is_array($category_tree_array))
            $category_tree_array = array();
        if ((sizeof($category_tree_array) < 1) && ($exclude != '0'))
            $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

        if ($include_itself) {
            $category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd where cd.language_id = '" . (int) $languages_id . "' and cd.categories_id = '" . (int) $parent_id . "' and affiliate_id = 0");
            $category = tep_db_fetch_array($category_query);
            $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name']);
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, cd.categories_seo_page_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c.parent_id = '" . (int) $parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            if ($exclude != $categories['categories_id'])
                $category_tree_array[] = array('id' => $categories['categories_seo_page_name'], 'text' => $spacing . $categories['categories_name']);
            $category_tree_array = self::get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
        }

        return $category_tree_array;
    }

    public static function categories_tree($parent_id = 0) {
        global $counter, $level/*, $languages_id*/;
        $languages_id = \common\classes\language::defaultId();
        $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id='" . (int) $parent_id . "' and c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "' and cd.affiliate_id='0' order by sort_order, categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $counter++;
            // update level and left part for node
            tep_db_query("update " . TABLE_CATEGORIES . " set categories_level='" . $level . "', categories_left='" . $counter . "' where categories_id='" . $categories['categories_id'] . "'");
            // check for siblings
            $sibling_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id='" . (int) $categories['categories_id'] . "' and c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "' and cd.affiliate_id='0' order by sort_order, categories_name");
            if (tep_db_num_rows($sibling_query) > 0) { // has siblings
                $level++;
                self::categories_tree($categories['categories_id']);
                $level--;
            }
            $counter++;
            // update right part of node
            tep_db_query("update " . TABLE_CATEGORIES . " set categories_right='" . $counter . "' where categories_id='" . $categories['categories_id'] . "'");
        }
    }

    public static function update_categories() {
        global $counter, $level;
        $counter = 1;
        $level = 1;
        self::categories_tree();
    }

    public static function generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
        global $languages_id;

        if (!is_array($categories_array))
            $categories_array = array();

        if ($from == 'product') {
            $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $id . "'");
            while ($categories = tep_db_fetch_array($categories_query)) {
                if (!is_array($categories_array[$index]))
                    $categories_array[$index] = array();
                if ($categories['categories_id'] == '0') {
                    array_unshift($categories_array[$index], array('id' => '0', 'text' => TEXT_TOP));
                } else {
                    $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories['categories_id'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "'");
                    $category = tep_db_fetch_array($category_query);
                    array_unshift($categories_array[$index], array('id' => $categories['categories_id'], 'text' => $category['categories_name']));
                    if ((tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0'))
                        $categories_array = self::generate_category_path($category['parent_id'], 'category', $categories_array, $index);
                }
                $index++;
            }
        } elseif ($from == 'category') {
            if (!is_array($categories_array[$index]))
                $categories_array[$index] = array();
            $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "'");
            $category = tep_db_fetch_array($category_query);
            array_unshift($categories_array[$index], array('id' => $id, 'text' => $category['categories_name']));
            if ((tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0'))
                $categories_array = self::generate_category_path($category['parent_id'], 'category', $categories_array, $index);
        }

        return $categories_array;
    }
    
    public static function get_assigned_catalog($platform_id,$validate=false,$active = false) {
        $assigned = array();
        if ( $validate ) {
          $get_assigned_r = tep_db_query(
            "SELECT pp.products_id AS id, p2c.categories_id as cid " .
            "FROM " . TABLE_PLATFORMS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd " .
            "WHERE pp.platform_id = '" . intval($platform_id) . "' and pp.products_id=p2c.products_id ".
            " AND p.products_id=pp.products_id ".
            " AND c.categories_id=p2c.categories_id ".
            ($active? " AND p.products_status=1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
            " AND cd.categories_id=c.categories_id AND cd.language_id='".$_SESSION['languages_id']."' AND cd.affiliate_id=0 ".
            " AND pd.products_id=p.products_id AND pd.language_id='".$_SESSION['languages_id']."' AND pd.affiliate_id=0 "
          );
        }else {
          $get_assigned_r = tep_db_query(
            "SELECT pp.products_id AS id, p2c.categories_id as cid " .
            "FROM " . TABLE_PLATFORMS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
            "WHERE pp.platform_id = '" . intval($platform_id) . "' and pp.products_id=p2c.products_id "
          );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
          while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
            $_key = 'p'.(int)$_assigned['id']."_".$_assigned['cid'];
            $assigned[$_key] = $_key;
          }
        }
        if ( $validate ) {
          $get_assigned_r = tep_db_query(
            "SELECT DISTINCT pc.categories_id AS id " .
            "FROM " . TABLE_PLATFORMS_CATEGORIES . " pc, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd " .
            "WHERE pc.platform_id = '" . intval($platform_id) . "' ".
            " AND c.categories_id=pc.categories_id ".
            " AND cd.categories_id=c.categories_id AND cd.language_id='".$_SESSION['languages_id']."' AND cd.affiliate_id=0 "
          );
        }else {
          $get_assigned_r = tep_db_query(
            "SELECT categories_id AS id " .
            "FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
            "WHERE platform_id = '" . intval($platform_id) . "' "
          );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
          while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
            $assigned['c'.(int)$_assigned['id']] = 'c'.(int)$_assigned['id'];
          }
        }
        return $assigned;
    }
    
    public static function load_tree_slice($platform_id, $category_id, $active = false, $search = '', $inner = false){
          $tree_init_data = array();

          $category_selected_state = true;
          if ( $category_id>0 ) {
            $_check = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE platform_id='" . $platform_id . "' AND categories_id='" . (int)$category_id . "' "
            ));
            $category_selected_state = $_check['c']>0;
          }

          $get_categories_r = tep_db_query(
            "SELECT CONCAT('c',c.categories_id) as `key`, cd.categories_name as title, ".
            " IF(pc.categories_id IS NULL, 0, 1) AS selected ".
            "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
            " left join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='".$platform_id."' ".
            "WHERE cd.categories_id=c.categories_id and cd.language_id='" . $_SESSION['languages_id'] . "' AND cd.affiliate_id=0 and c.parent_id='" . (int)$category_id . "' ".
            "order by c.sort_order, cd.categories_name"
          );
          while ($_categories = tep_db_fetch_array($get_categories_r)) {
              //$_categories['parent'] = (int)$category_id;
              $_categories['folder'] = true;
              $_categories['lazy'] = true;
              $_categories['selected'] = $category_selected_state && !!$_categories['selected'];
              $tree_init_data[] = $_categories;
          }
          $get_products_r = tep_db_query(
            "SELECT concat('p',p.products_id,'_',p2c.categories_id) AS `key`, pd.products_name as title, ".
            " IF(pp.products_id IS NULL, 0, 1) AS selected ".
            "from ".TABLE_PRODUCTS_DESCRIPTION." pd, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_PRODUCTS." p ".
            ($inner ? "inner " : "left ") . " join ".TABLE_PLATFORMS_PRODUCTS." pp on pp.products_id=p.products_id and pp.platform_id='".$platform_id."' ".
            "WHERE pd.products_id=p.products_id and pd.language_id='".$_SESSION['languages_id']."' and pd.affiliate_id=0 and p2c.products_id=p.products_id and p2c.categories_id='".(int)$category_id."' ".
            ($active? " AND p.products_status=1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
            (tep_not_null($search)?" and pd.products_name like '%{$search}%' " :"").
            "order by p.sort_order, pd.products_name"
          );
          if ( tep_db_num_rows($get_products_r)>0 ) {
              while ($_product = tep_db_fetch_array($get_products_r)) {
                //$_product['parent'] = (int)$category_id;
                $_product['selected'] = $category_selected_state && !!$_product['selected'];
                $tree_init_data[] = $_product;
              }
          }

          return $tree_init_data;
      }    

}
