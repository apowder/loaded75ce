<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

//// initial defines
  define('TABLE_PRODUCTS_TO_CLASSES', 'products_to_classes');
  define('TABLE_PRODUCTS_TO_ELEMENTS', 'products_to_elements');
  define('TABLE_PRODUCTS_TO_PCTEMPLATES_TO_ELEMENTS', 'products_to_pctemplates_to_elements');

  define('TABLE_CLASSES', 'classes');
  define('TABLE_ELEMENTS', 'elements');
  define('TABLE_PCTEMPLATES', 'pctemplates');
  define('TABLE_PCTEMPLATES_INFO', 'pctemplates_info');
//  define('TABLE_CLASSES_TO_ELEMENTS', 'classes_to_elements');
  define('TABLE_PRODUCTS_TO_PCTEMPLATES_TO_ELEMENTS', 'products_to_pctemplates_to_elements');
//  define('TABLE_ORDERS_PRODUCTS_ELEMENTS', 'orders_products_elements');

  define('FILENAME_ELEMENTS', 'elements');
  define('FILENAME_ELEMENTS_TO_PRODUCTS', 'elements_to_products');
  define('FILENAME_CLASSES', 'classes');
  define('FILENAME_PCTEMPLATES', 'pc_templates');
  define('FILENAME_PCTEMPLATES_PARTS', 'pc_templates_parts');
  define('FILENAME_PARTS_ADD', 'parts_add');
  define('FILENAME_ADD_CLASS_SUPLY', 'class_to_products');


  $eltypes = array(array('id'=>'0', 'text'=>'select'),
                   array('id'=>'2', 'text'=>'radio'),
//                   array('id'=>'3', 'text'=>'checkbox'),
                   );

  function get_type_name($t_id) {
    global $eltypes;
    foreach($eltypes as $elt) {
      if ($elt['id'] == $t_id) return  $elt['text'];
    }
    return '--none--';
  }

  function tep_get_pctemplates_description($pctemplate_id, $language_id) {
    $pctemplate_query = tep_db_query("select pctemplates_description from " . TABLE_PCTEMPLATES_INFO . " where pctemplates_id = '" . $pctemplate_id . "' and languages_id = '" . $language_id . "'");
    $pctemplate = tep_db_fetch_array($pctemplate_query);

    return $pctemplate['pctemplates_description'];
  }

  if (!function_exists('tep_array_merge')) {
    function tep_array_merge($array1, $array2, $array3 = '') {
      if ($array3 == '') $array3 = array();
      if (function_exists('array_merge')) {
        $array_merged = array_merge($array1, $array2, $array3);
      } else {
        while (list($key, $val) = each($array1)) $array_merged[$key] = $val;
        while (list($key, $val) = each($array2)) $array_merged[$key] = $val;
        if (sizeof($array3) > 0) while (list($key, $val) = each($array3)) $array_merged[$key] = $val;
      }

      return (array) $array_merged;
    }
  }

  if (!function_exists('tep_is_uploaded_file')) {
    function tep_is_uploaded_file($filename) {
      if (function_exists('is_uploaded_file')) {
        return is_uploaded_file($filename);
      } else {
        if (!$tmp_file = get_cfg_var('upload_tmp_dir')) {
          $tmp_file = dirname(tempnam('', ''));
        }
        $tmp_file .= '/' . basename($filename);
  // User might have trailing slash in php.ini
        return (ereg_replace('/+', '/', $tmp_file) == $filename);
      }
    }
  }

  function tep_get_templ_price($pctemplates_id) {
    global $languages_id;
    if (!empty($pctemplates_id) or ($pctemplates_id!='')) {
      $prod=tep_db_fetch_array(tep_db_query('select truncate(sum(if(p.products_price_configurator > 0, p.products_price_configurator, p.products_price)), 2) as pc_price from '. TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_TO_PCTEMPLATES_TO_ELEMENTS.' pc where pc.products_id = p.products_id and pc.pctemplates_id = '.$pctemplates_id.' and pc.def = 1'));
      $prod_query = tep_db_query('select p.products_id from '. TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_TO_PCTEMPLATES_TO_ELEMENTS.' pc where pc.products_id = p.products_id and pc.pctemplates_id = '.$pctemplates_id.' and pc.def = 1');
      $attr_price = 0;
      while ($prod_data = tep_db_fetch_array($prod_query)) {
        $pc_products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . $prod_data['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_sort_order");
        while ($pc_products_options_name = tep_db_fetch_array($pc_products_options_name_query)) {
          $pc_products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$prod_data['products_id'] . "' and pa.options_id = '" . (int)$pc_products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "' order by pa.products_options_sort_order limit 1");
          while ($pc_products_options_data = tep_db_fetch_array($pc_products_options_query)) {
            if ($pc_products_options_data['price_prefix'] == '-') {
              $attr_price -= $pc_products_options_data['options_values_price'];
            } else {
              $attr_price += $pc_products_options_data['options_values_price'];
            }
          }
        }
      }
      $prod['pc_price'] += number_format($attr_price, 2, '.', '');
    }
    return $prod['pc_price'];
  }

