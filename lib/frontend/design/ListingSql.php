<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design;

class ListingSql
{

  public static function query($settings = array())
  {
    global $HTTP_SESSION_VARS, $currency_id, $currencies, $customer_groups_id, $languages_id, $current_category_id;

    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
      'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
      'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
      'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
      'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
      'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
      'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
      'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW,
      'PRODUCT_LIST_SHORT_DESRIPTION' => PRODUCT_LIST_SHORT_DESRIPTION);

    asort($define_list);

    $column_list = array();
    reset($define_list);
    while (list($key, $value) = each($define_list)) {
      if ($value > 0) $column_list[] = $key;
    }

    $select_column_list = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, ';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_SHORT_DESRIPTION':
          $select_column_list .= 'if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
      }
    }

    if (!isset($settings['no_filters'])) {
      $filters_sql_array = ListingSql::get_filters_sql_array();
    }

    $listing_sql_array = ListingSql::get_listing_sql_array($settings['filename']);


    $p2c_listing_join = '';
    if ( \common\classes\platform::activeId() ) {
      $p2c_listing_join = " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }
    $listing_sql = "
      select
        " . $select_column_list . "
        p.products_id,
        p.is_virtual,
        p.manufacturers_id,
        p.products_price,
        p.products_tax_class_id,
        IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price,
        IF(s.status, s.specials_new_products_price, p.products_price) as final_price
      from
        " . $listing_sql_array['from'] . "
        " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$p2c_listing_join},
        " . TABLE_PRODUCTS_DESCRIPTION . " pd,
        " . TABLE_PRODUCTS . " p
          left join
            " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id
              and pd1.language_id = '" . (int)$languages_id . "'
              and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'
          left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
          left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id
          " . $listing_sql_array['left_join'] . $filters_sql_array['left_join'] . "
      where
        p2c.products_id = p.products_id
        and pd.products_id = p.products_id
        and pd.affiliate_id = '0'
        and pd.language_id = '" . (int)$languages_id . "'
        " . $listing_sql_array['where'] . $filters_sql_array['where'] ."
      group by p.products_id";

    if ($_GET['sort']) {
      $sr = $_GET['sort'];
    } elseif ($settings['sort']) {
      $sr = $settings['sort'];
    } else {
      $sr = Info::sortingId();
    }
    
    if ($sr) {
      $sort_col = substr($sr, 0 , 1);
      $sort_order = substr($sr, 1);
      $listing_sql .= ' order by ';
      switch ($sort_col) {
        case 'm':
          $listing_sql .= " p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'n':
          $listing_sql .= " products_name " . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'b':
          $listing_sql .= " m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'q':
          $listing_sql .= " p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'i':
          $listing_sql .= " products_name";
          break;
        case 'w':
          $listing_sql .= " p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'p':
          $listing_sql .= " final_price " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'd':
          $listing_sql .= " p.products_date_added " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
      }
    } else {
      if (tep_not_null($filters_sql_array['relevance_order'])) {
        $listing_sql .= ' order by (' . $filters_sql_array['relevance_order'] . ') desc, products_name';
      } else {
        $listing_sql .= " order by " . (!empty($listing_sql_array['order'])? $listing_sql_array['order'] .", " : "") . " p.sort_order, products_name";
      }
    }

    //echo $listing_sql;
    return $listing_sql;
  }

  public static function get_listing_sql_array($filename = '') {
    global $HTTP_SESSION_VARS, $currency_id, $currencies, $customer_groups_id, $languages_id, $current_category_id;

    $listing_from = '';
    $listing_where = '';
    $listing_left_join = '';
    $order = '';

    if ( \common\classes\platform::activeId() ) {
      $listing_left_join .= " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
      //$listing_where .= " and plp.platform_id is not null ";
      //$listing_left_join .= " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
      //$listing_where .= " and plc.platform_id is not null ";
    }

    if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
      $listing_left_join .= " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' ";
      $listing_where .= " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) ";
    }

    // show the active products
    $listing_where .= " and p.products_status = '1' " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ";

    switch ($filename) {
    case FILENAME_SPECIALS: // show specials
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
        $listing_left_join .= " left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int)$customer_groups_id . "' and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?(int)$currency_id:'0'). "' ";
        $listing_where .= " and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.status = 1 ";
      } else {
        $listing_where .= " and s.status = 1 ";
      }
      break;
    case FILENAME_FEATURED_PRODUCTS: // show featured products
      $listing_from .= " " . TABLE_FEATURED . " f, ";
      $listing_where .= " and p.products_id = f.products_id and f.status = '1' ";
      if (tep_session_is_registered('affiliate_ref') && $HTTP_SESSION_VARS['affiliate_ref'] != '') {
        $listing_where .= " and (f.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' or f.affiliate_id = 0) ";
      } else {
        $listing_where .= " and f.affiliate_id = 0 ";
      }
      break;
    case FILENAME_PRODUCTS_NEW:// show all products sorted by date
      break;
    case FILENAME_ALL_PRODUCTS:
      $order = "p2c.sort_order";
      break;
    case FILENAME_ADVANCED_SEARCH: // show all products filtered
      break;
    default: case 'catalog': case 'catalog/index':
      if ($current_category_id > 0) {
        if (\frontend\design\Info::themeSetting('show_products_from_subcategories')) {
          $categories_array = array($current_category_id);
          \common\helpers\Categories::get_subcategories($categories_array, $current_category_id);
          if (count($categories_array) == 1 && $categories_array[0] == $current_category_id) {
            $order = "p2c.sort_order";
          }
        } else {
          $categories_array = array($current_category_id);
        }
      }
      if (isset($_GET['manufacturers_id'])) {
        // show the products of a specified manufacturer(s)
        $listing_where .= " and m.manufacturers_id in ('" . implode("','", array_map('intval', explode('_', $_GET['manufacturers_id']))) . "') " . (count($categories_array) > 0 ? " and p2c.categories_id in ('" . implode("','", array_map('intval', $categories_array)) . "') " : '');
      } else {
        // show the products of a specified category(ies)
        $listing_where .= (count($categories_array) > 0 ? " and p2c.categories_id in ('" . implode("','", array_map('intval', $categories_array)) . "') " : '');
      }
      break;
    }

    return array('from' => $listing_from, 'left_join' => $listing_left_join, 'where' => $listing_where, 'order' => $order);
  }

  public static function get_filters_sql_array($exclude = '') {
    global $currency_id, $currencies, $customer_groups_id, $languages_id;

    $filters_left_join = '';
    $filters_where = '';
    $relevance_order = '';

    // Search keywords
    if (isset($_GET['keywords'])) {
      $keywords = $_GET['keywords'];
      if (tep_not_null($keywords)) {
        if (MSEARCH_ENABLE == 'true') {
          if (!\common\helpers\Output::parse_search_string($keywords, $search_keywords, false) || !\common\helpers\Output::parse_search_string($keywords, $msearch_keywords, MSEARCH_ENABLE)) {
//            $error = true;
          }
        } else {
          if (!\common\helpers\Output::parse_search_string($keywords, $search_keywords)) {
//            $error = true;
          }
        }
      }

      $relevance_keywords = array();
      if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
        $filters_where .= " and (";

        for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
          switch ($search_keywords[$i]) {
            case '(':
            case ')':
            case 'and':
            case 'or':
              $filters_where .= " " . $search_keywords[$i] . " ";
              break;
            default:
              $keyword = tep_db_prepare_input($search_keywords[$i]);
              $relevance_keywords[] = tep_db_input($keyword);
              $filters_where .= "(if(length(pd1.products_name), pd1.products_name, pd.products_name) like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'";
              if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) $filters_where .= " or if(length(pd1.products_description), pd1.products_description, pd.products_description) like '%" . tep_db_input($keyword) . "%'";
              $filters_where .= ')';
              break;
          }
        }

        $relevance_order .= " (match(pd.products_name) against ('" . implode(' ', $relevance_keywords) . "') * 1.2) + (match(p.products_model) against ('" . implode(' ', $relevance_keywords) . "') * 1.0) ";
        if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) {
          $relevance_order .= " + (match(pd.products_description) against ('" . implode(' ', $relevance_keywords) . "') * 0.8) ";
        }

        if (MSEARCH_ENABLE == 'true') {
          if (isset($msearch_keywords) && (sizeof($msearch_keywords) > 0)) {
            $filters_where .= " or (";
            for ($i=0, $n=sizeof($msearch_keywords); $i<$n; $i++ ) {
              switch ($msearch_keywords[$i]) {
                case '(':
                case ')':
                case 'and':
                case 'or':
                  $filters_where .= " " . $msearch_keywords[$i] . " ";
                  break;
                default:
                  $keyword = tep_db_prepare_input($msearch_keywords[$i]);
                  if (tep_not_null($keyword)) {
                    $filters_where .= "(if(length(pd1.products_name_soundex), pd1.products_name_soundex, pd.products_name_soundex) like '%" . tep_db_input($keyword) . "%' ";
                    if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) $filters_where .= "or if(length(pd1.products_description_soundex), pd1.products_description_soundex, pd.products_description_soundex) like '%" . tep_db_input($keyword) . "%'";
                    $filters_where .= ')';
                  } else {
                    $filters_where .= 'false';
                  }
                  break;
              }
            }
            $filters_where .= " )";
          }
        }

        if (PRODUCTS_PROPERTIES == 'True' && true) {
          $filters_where .= " or ";
          $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " pr2pk on pr2pk.products_id = p.products_id left join " . TABLE_PROPERTIES . " prk on prk.properties_id = pr2pk.properties_id and prk.display_search = '1' left join " . TABLE_PROPERTIES_VALUES . " pvk on prk.properties_id = pvk.properties_id and pr2pk.values_id = pvk.values_id";
            for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
            switch ($search_keywords[$i]) {
              case '(':
              case ')':
              case 'and':
              case 'or':
                $filters_where .= " " . $search_keywords[$i] . " ";
                break;
              default:
                $filters_where .= " (pvk.language_id = '" . (int)$languages_id . "' and pvk.values_text like '%" . tep_db_input($search_keywords[$i]) . "%') ";
              break;
            }
          }
        }

        $filters_where .= " )";
      }
    }

    // Price interval
    if ($exclude !== 'p') {
      $pfrom = (float)preg_replace("/[^\d\.]/", '', $_GET['pfrom']);
      $pto = (float)preg_replace("/[^\d\.]/", '', $_GET['pto']);
      if ($pfrom > 0 || $pto > 0) {
        $ids = array();
        $ids[] = 0;

        $products_join = '';
        if ( \common\classes\platform::activeId() ) {
          $products_join .=
            " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
            " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
            " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
          $query = tep_db_query("select p.products_id, p.products_tax_class_id, p.products_price from " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) group by p.products_id");
        } else {
          $query = tep_db_query("select p.products_id, p.products_tax_class_id, p.products_price from " . TABLE_PRODUCTS . " p {$products_join} where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " group by p.products_id");
        }
        while ($data = tep_db_fetch_array($query)) {
          $special_price = \common\helpers\Product::get_products_special_price($data['products_id']);
          $price = \common\helpers\Product::get_products_price($data['products_id'], 1, $data['products_price']);
          if ($special_price) {
            $price = $special_price;
          }
          $price = $currencies->display_price_clear($price, \common\helpers\Tax::get_tax_rate($data['products_tax_class_id']));
          if ($pfrom > 0 && $pto > 0) {
            if ($price >= $pfrom && $price <= $pto) {
              $ids[] = $data['products_id'];
            }
          } elseif ($pfrom > 0) {
            if ($price >= $pfrom) {
              $ids[] = $data['products_id'];
            }
          } elseif ($pto > 0) {
            if ($price <= $pto) {
              $ids[] = $data['products_id'];
            }
          }
        }
        $filters_where .= " and p.products_id in ('" . implode("','", array_map('intval', $ids)) . "') ";
      }
    }

    // Brands selected
    if ($exclude !== 'brand') {
      if (is_array($_GET['brand'])) {
        $filters_where .= " and p.manufacturers_id in ('" . implode("','", array_map('intval', $_GET['brand'])) . "') ";
      } elseif ($_GET['brand'] > 0) {
        $filters_where .= " and p.manufacturers_id = '" . (int)$_GET['brand'] . "' ";
      }
    }

    if (is_array($_GET))
    foreach ($_GET as $key => $values) {
      // Properties interval
      if (preg_match("/^pr(\d+)from$/", $key, $arr)) {
        $prop_id = (int)$arr[1];
        if ($prop_id > 0 && $exclude !== 'pr' . $prop_id && isset($_GET['pr' . $prop_id . 'to'])) {
          $from = (float)$values;
          $to = (float)$_GET['pr' . $prop_id . 'to'];
          $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " p2pi" . $prop_id . " on p.products_id = p2pi" . $prop_id . ".products_id and p2pi" . $prop_id . ".properties_id = '" . (int)$prop_id . "' left join " . TABLE_PROPERTIES_VALUES . " pvi" . $prop_id . " on p2pi" . $prop_id . ".properties_id = pvi" . $prop_id . ".properties_id and p2pi" . $prop_id . ".values_id = pvi" . $prop_id . ".values_id and pvi" . $prop_id . ".language_id = '" . (int)$languages_id . "' ";
          if ($from > 0 && $to > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number >= " . (float)$from . " and pvi" . $prop_id . ".values_number <= " . (float)$to . " ";
          } elseif ($from > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number >= " . (float)$from . " ";
          } elseif ($to > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number <= " . (float)$to . " ";
          }
        }
      }

      // Properties selected
      if (preg_match("/^pr(\d+)$/", $key, $arr)) {
        $prop_id = (int)$arr[1];
        if ($prop_id > 0 && $exclude !== 'pr' . $prop_id) {
          $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p" . $prop_id . " on p.products_id = p2p" . $prop_id . ".products_id ";
          $filters_where .= " and p2p" . $prop_id . ".properties_id = '" . (int)$prop_id . "' ";
          if (is_array($values)) {
            if ($values[0] > 0) {
              $filters_where .= " and p2p" . $prop_id . ".values_id in ('" . implode("','", array_map('intval', $values)) . "') ";
            } elseif ($values[0] == 'Y' || $values[0] == 'N') { // properties_type == flag
              $filters_where .= " and p2p" . $prop_id . ".values_id = '0' and p2p" . $prop_id . ".values_flag in ('" . implode("','", array_map(function($v) {return (int)($v == 'Y');}, $values)) . "') ";
            }
          } else {
            if ($values > 0) {
              $filters_where .= " and p2p" . $prop_id . ".values_id = '" . (int)$values . "' ";
            } elseif ($values == 'Y' || $values == 'N') { // properties_type == flag
              $filters_where .= " and p2p" . $prop_id . ".values_id = '0' and p2p" . $prop_id . ".values_flag = '" . (int)($values == 'Y') . "' ";
            }
          }
        }
      }

      // Attributes selected
      if (preg_match("/^at(\d+)$/", $key, $arr)) {
        $attr_id = (int)$arr[1];
        if ($attr_id > 0 && $exclude !== 'at' . $attr_id) {
          $filters_left_join .= " left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa" . $attr_id . " on p.products_id = pa" . $attr_id . ".products_id ";
          $filters_where .= " and pa" . $attr_id . ".options_id = '" . (int)$attr_id . "' ";
          if (is_array($values)) {
            $filters_where .= " and pa" . $attr_id . ".options_values_id in ('" . implode("','", array_map('intval', $values)) . "') ";
          } else {
            $filters_where .= " and pa" . $attr_id . ".options_values_id = '" . (int)$values . "' ";
          }
        }
      }
    }

    return array('left_join' => $filters_left_join, 'where' => $filters_where, 'relevance_order' => $relevance_order);
  }
}