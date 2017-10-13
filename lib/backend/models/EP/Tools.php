<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP;

use common\helpers\Seo;

class Tools
{
    public $languages_id;
    private $categories_id_to_name = array();
    private $categories_path_to_id = array();
    private $new_category_added = false;

    function __construct() {
        $this->languages_id = \common\classes\language::defaultId();
        if ( isset($_SESSION) && !empty($_SESSION['languages_id']) ) {
            $this->languages_id = $_SESSION['languages_id'];
        }
    }
    
    function tep_get_full_listing_products_for_categories($parent_id = '0', $products_array = '')
    {
      $languages_id = $this->languages_id;
      if (!is_array($products_array)) $products_array = array();
      $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$parent_id . "' and pd.language_id = '" . (int)$languages_id . "' order by p.sort_order, p.products_id, pd.products_name");
      while ($products = tep_db_fetch_array($products_query)) {
        $products_array[$products['products_id']] = $products['products_id'];
      }

      $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        $products_array = $this->tep_get_full_listing_products_for_categories($categories['categories_id'], $products_array);
      }
      return $products_array;
    }

    function tep_get_categories_full_path( $categories_id )
    {
      $languages_id = $this->languages_id;

      if ( !isset($this->categories_id_to_name[(int)$categories_id]) ) {
        $this->categories_id_to_name[(int)$categories_id] = tep_db_fetch_array( tep_db_query(
          "SELECT c.categories_id, cd.categories_name, c.parent_id ".
          "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
          "WHERE c.categories_id = cd.categories_id and c.categories_id = '" . $categories_id . "' AND cd.affiliate_id = 0 AND cd.language_id = " . (int)$languages_id
        ) );
      }
      $sql2 = $this->categories_id_to_name[(int)$categories_id];

      if( $sql2['parent_id'] > 0 ) {
        return $this->tep_get_categories_full_path( (int) $sql2['parent_id'] ) . ';' . $sql2['categories_name'];
      } else {
        return $sql2['categories_name'];
      }
    }

    private function lookupCategory($lookup_parent_id, $category_name )
    {
      $languages_id = $this->languages_id;

      $category_ids = array();

      $lookup_names = array();
      $lookup_names[] = $category_name;
      $lookup_names[] = $this->_cleanCatName($category_name,1);
      $lookup_names[] = $this->_cleanCatName($category_name,2);
      $lookup_names[] = $this->_cleanCatName($category_name);

      $db_lookup_r = tep_db_query(
        "SELECT c.categories_id ".
        "FROM ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd ".
        "WHERE cd.categories_id = c.categories_id AND cd.language_id = '".$languages_id."' AND cd.affiliate_id=0 ".
        " AND c.parent_id='".(int)$lookup_parent_id."' ".
        " AND cd.categories_name IN ('".implode("','",array_map('tep_db_input',array_unique($lookup_names)))."') ".
        ""
      );
      if ( tep_db_num_rows($db_lookup_r)>0 ) {
        while( $_lookup = tep_db_fetch_array($db_lookup_r) ) {
          $category_ids[] = (int)$_lookup['categories_id'];
        }
      }
      return $category_ids;
    }

    function tep_get_categories_by_name( $categories_path, $messages=false )
    {
      $languages_id = $this->languages_id;
      if ( isset($this->categories_path_to_id[$categories_path]) ) {
        return $this->categories_path_to_id[$categories_path];
      }
      $categories_array = explode(';',$categories_path);

      $category_id = false;
      // skip empty
      if ( empty($categories_path) ) {
        return $category_id;
      }
      $lookup_parent_id = 0;
      $_track_path_original = '';
      $_track_path_trimmed = '';
      foreach( $categories_array as $idx=>$category_name ){
        $category_name_trimmed = $this->_cleanCatName($category_name);
        $_track_path_original .= (empty($_track_path_original)?'':';').$category_name;
        $_track_path_trimmed .= (empty($_track_path_trimmed)?'':';').$category_name_trimmed;

        $found_categories = $this->lookupCategory((int)$lookup_parent_id,$category_name);
        $count_found_categories = count($found_categories);

        if ( $count_found_categories==1 ) {
          $category_id = current($found_categories);
        }elseif($count_found_categories==0){
          $category_seo_name = Seo::makeSlug($category_name);

          // create
          tep_db_perform(TABLE_CATEGORIES,array(
            'parent_id' => $lookup_parent_id,
            'date_added' => 'now()',
            'categories_status' => 0,
            'categories_seo_page_name' => ''
          ));
          $category_id = tep_db_insert_id();

          $check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_seo_page_name='".tep_db_input($category_seo_name)."'"));
          if ( $check['c']>0 ) {
            $category_seo_name .= '-'.$category_id;
          }

          $category_name_create = $this->_cleanCatName($category_name,2);

          tep_db_query(
            "INSERT INTO ".TABLE_CATEGORIES_DESCRIPTION." ".
            "(categories_id, language_id, categories_name, categories_seo_page_name) ".
            " SELECT '{$category_id}', languages_id, '".tep_db_input($category_name_create)."', '".tep_db_input($category_seo_name)."' FROM ".TABLE_LANGUAGES." "
          );
          $this->new_category_added = true;


          if ( is_object($messages) && is_a($messages,'backend\models\EP\Messages') ) {
            /**
             * @var $messages Messages
             */
            $messages->info('New category "'.$category_name_create.'" has been added');
          }

        }else{
          // 1 child deep lookup
          if ( isset($categories_array[$idx+1]) ) {
            $current_level_possible_cat_ids = array();
            foreach ($found_categories as $alter_parent) {
              $found_next_level = $this->lookupCategory($alter_parent,$categories_array[$idx+1]);
              if ( count($found_next_level)==1 ) {
                $current_level_possible_cat_ids[] = $alter_parent;
              }
            }
            if ( count($current_level_possible_cat_ids)==1 ) {
              $category_id = current($current_level_possible_cat_ids);
            }else{
              if (is_object($messages) && is_a($messages, 'backend\models\EP\Messages')) {
                /**
                 * @var $messages Messages
                 */
                $messages->info('Found multiple "' . $_track_path_original . '"');
              }
            }
          }else {
            if (is_object($messages) && is_a($messages, 'backend\models\EP\Messages')) {
              /**
               * @var $messages Messages
               */
              $messages->info('Found multiple "' . $_track_path_original . '"');
            }
          }
        }
        $lookup_parent_id = $category_id;

        $this->categories_path_to_id[$_track_path_original] = $category_id;
        if ( $_track_path_original!=$_track_path_trimmed ) {
          $this->categories_path_to_id[$_track_path_trimmed] = $category_id;
        }
      }

      return $category_id;
    }

    private function _cleanCatName($name, $clean_level=10){
      $name = trim($name);
      if ( $clean_level>1 && strpos($name,'  ')!==false ) $name = preg_replace('/\s{2,}/',' ',$name);
      static $mb_str = null;
      if ( $mb_str===null ) $mb_str = function_exists('mb_strtolower');
      if ( $clean_level>2 ) {
        if ( $mb_str ) {
          $name = mb_strtolower($name);
        }else{
          $name = strtolower($name);
        }
      }
      return $name;
    }

    public function done($what){
      // products_import, categories_import, attributes_import, properties_import, properties_settings_import
      if ( $what=='products_import' || $what=='categories_import' || $what=='products_to_categories_import' ){
        if ( true || $this->new_category_added) {
          \common\helpers\Categories::update_categories();
        }
        echo '<script type="text/javascript">if (typeof window.parent.refreshFilterContent==\'function\') window.parent.refreshFilterContent();</script>';
      }elseif( $what=='properties_import' || $what=='properties_settings_import' ){
        echo '<script type="text/javascript">if (typeof window.parent.refreshFilterContent==\'function\') window.parent.refreshFilterContent();</script>';
      }
    }

    public function get_brand_by_name($brand_name){
      if ( empty($brand_name) ) return 'null';
      static $processed = array();
      if ( !isset($processed[$brand_name]) ) {
        $processed[$brand_name] = 'null';
        $get_brand_r = tep_db_query(
          "SELECT manufacturers_id ".
          "FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_name IN('".tep_db_input($brand_name)."', '".tep_db_input(trim($brand_name))."') ".
          "ORDER BY IF(manufacturers_name='".tep_db_input($brand_name)."',0,1) ".
          "LIMIT 1"
        );
        if ( tep_db_num_rows($get_brand_r)>0 ) {
          $get_brand = tep_db_fetch_array($get_brand_r);
          $processed[$brand_name] = $get_brand['manufacturers_id'];
        }else{
          tep_db_perform(TABLE_MANUFACTURERS, array(
            'manufacturers_name' => trim($brand_name),
            'date_added' => 'now()',
          ));
          $processed[$brand_name] = tep_db_insert_id();
          tep_db_query("INSERT INTO ".TABLE_MANUFACTURERS_INFO." (manufacturers_id, languages_id) SELECT '{$processed[$brand_name]}', languages_id FROM ".TABLE_LANGUAGES." ");
        }
      }
      return $processed[$brand_name];
    }

    public function get_option_by_name($option_names){
      if ( empty($option_names) || (is_array($option_names) && trim(implode('',$option_names))=='') ) return 0;
      if ( !is_array($option_names) ) $option_names = array( intval($this->languages_id) => $option_names);
      $option_id = false;
      foreach($option_names as $_lang=> $_lookup_name ) {
        if ( $option_id ) {
          //update
          tep_db_perform(TABLE_PRODUCTS_OPTIONS, array(
            'products_options_name' => trim($_lookup_name),
          ),'update', "products_options_id='{$option_id}' AND language_id='".(int)$_lang."'");
          continue;
        }
        $get_id_r = tep_db_query(
          "SELECT products_options_id ".
          "FROM ".TABLE_PRODUCTS_OPTIONS." ".
          "WHERE language_id='".(int)$_lang."' AND (products_options_name='".tep_db_input($_lookup_name)."' OR products_options_name='".tep_db_input(trim($_lookup_name))."' )"
        );
        if ( tep_db_num_rows($get_id_r)>0 ) {
          $get_id = tep_db_fetch_array($get_id_r);
          $option_id = $get_id['products_options_id'];
        }else{
          $_new_option_id = tep_db_fetch_array(tep_db_query(
            "SELECT MAX(products_options_id) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS." "
          ));
          $option_id = (is_array($_new_option_id)?($_new_option_id['current_max']+1):1);

          $_new_order = tep_db_fetch_array(tep_db_query(
            "SELECT MAX(products_options_sort_order) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS." "
          ));
          $sort_order = (is_array($_new_order)?($_new_order['current_max']+1):1);

          tep_db_query(
            "INSERT INTO ".TABLE_PRODUCTS_OPTIONS." (products_options_id, language_id, products_options_name, products_options_sort_order) ".
            "SELECT '{$option_id}', languages_id, '".tep_db_input(trim($_lookup_name))."', '{$sort_order}' FROM ".TABLE_LANGUAGES
          );
        }
      }
      return $option_id;
    }

    public function get_option_value_by_name($option_id, $value_names){
        if ( empty($value_names) || (is_array($value_names) && trim(implode('',$value_names))=='') ) return 0;
        if ( !is_array($value_names) ) $value_names = array( intval($this->languages_id) => $value_names);
        $option_value_id = false;

        foreach($value_names as $_lang=> $_lookup_name ) {
            if ( $option_value_id ) {
              //update
              tep_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, array(
                'products_options_values_name' => trim($_lookup_name),
              ),'update', "products_options_values_id='{$option_value_id}' AND language_id='".(int)$_lang."'");
              continue;
            }
            $get_id_r = tep_db_query(
              "SELECT pov.products_options_values_id ".
              "FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov, ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." pov2po ".
              "WHERE pov2po.products_options_id='".$option_id."' AND pov2po.products_options_values_id=pov.products_options_values_id ".
              " AND pov.language_id='".(int)$_lang."' AND (pov.products_options_values_name='".tep_db_input($_lookup_name)."' OR pov.products_options_values_name='".tep_db_input(trim($_lookup_name))."' )"
            );
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $option_value_id = $get_id['products_options_values_id'];
            }else{
                $_new_option_id = tep_db_fetch_array(tep_db_query(
                  "SELECT MAX(products_options_values_id) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." "
                ));
                $option_value_id = (is_array($_new_option_id)?($_new_option_id['current_max']+1):1);

                $_new_order = tep_db_fetch_array(tep_db_query(
                  "SELECT MAX(products_options_values_sort_order) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." "
                ));
                $sort_order = (is_array($_new_order)?($_new_order['current_max']+1):1);

                tep_db_query(
                  "INSERT INTO ".TABLE_PRODUCTS_OPTIONS_VALUES." (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order) ".
                  "SELECT '{$option_value_id}', languages_id, '".tep_db_input(trim($_lookup_name))."', '{$sort_order}' FROM ".TABLE_LANGUAGES
                );
                tep_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS,array(
                  'products_options_id' => $option_id,
                  'products_options_values_id' => $option_value_id,
                ));
            }
        }

        return $option_value_id;
    }

    public function get_option_value_name($option_value_id, $language_id)
    {
        $key = (int)$option_value_id.'^'.(int)$language_id;
        static $_cached = array();
        if ( !isset($_cached[$key]) ) {
            $get_name_r = tep_db_query(
              "SELECT products_options_values_name AS name " .
              "FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " " .
              "WHERE products_options_values_id='" . (int)$option_value_id . "' AND language_id='" . (int)$language_id . "'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $_name = tep_db_fetch_array($get_name_r);
                $_cached[$key] = $_name['name'];
            }
        }
        return $_cached[$key];
    }

    public function get_option_name($option_id, $language_id)
    {
        $key = (int)$option_id.'^'.(int)$language_id;
        static $_cached = array();
        if ( !isset($_cached[$key]) ) {
            $get_name_r = tep_db_query(
                "SELECT products_options_name AS name " .
                "FROM " . TABLE_PRODUCTS_OPTIONS . " " .
                "WHERE products_options_id='" . (int)$option_id . "' AND language_id='" . (int)$language_id . "'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $_name = tep_db_fetch_array($get_name_r);
                $_cached[$key] = $_name['name'];
            }
        }
        return $_cached[$key];
    }

    public function getStockIndication($id)
    {
        static $cached = [];
        if ( !isset($cached[(int)$id]) ) {
            $cached[(int)$id] = '';
            $get_text_r = tep_db_query(
                "SELECT sit.stock_indication_text " .
                "FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " sit ON sit.stock_indication_id=si.stock_indication_id AND sit.language_id='" . $this->languages_id . "' " .
                "WHERE si.stock_indication_id='" . (int)$id . "'"
            );
            if ( tep_db_num_rows($get_text_r)>0 ){
                $get_text = tep_db_fetch_array($get_text_r);
                $cached[(int)$id] = $get_text['stock_indication_text'];
            }
        }
        return $cached[(int)$id];
    }

    public function lookupStockIndicationId($text)
    {
        static $cached = [];
        if (!isset($cached[$text])) {
            $cached[$text] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT si.stock_indication_id " .
                "FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " sit ON sit.stock_indication_id=si.stock_indication_id " .
                "WHERE (sit.stock_indication_text='" . tep_db_input($text) . "' OR sit.stock_indication_text='" . tep_db_input(trim($text)) . "') ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$text] = $get_id['stock_indication_id'];
            }

        }
        return $cached[$text];
    }

    public function getStockDeliveryTerms($id)
    {
        static $cached = [];
        if ( !isset($cached[(int)$id]) ) {
            $cached[(int)$id] = '';
            $get_text_r = tep_db_query(
                "SELECT sit.stock_delivery_terms_text " .
                "FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " sit ON sit.stock_delivery_terms_id=si.stock_delivery_terms_id AND sit.language_id='" . $this->languages_id . "' " .
                "WHERE si.stock_delivery_terms_id='" . (int)$id . "'"
            );
            if ( tep_db_num_rows($get_text_r)>0 ){
                $get_text = tep_db_fetch_array($get_text_r);
                $cached[(int)$id] = $get_text['stock_delivery_terms_text'];
            }
        }
        return $cached[(int)$id];
    }

    public function lookupStockDeliveryTermId($text)
    {
        static $cached = [];
        if (!isset($cached[$text])) {
            $cached[$text] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT si.stock_delivery_terms_id " .
                "FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " sit ON sit.stock_delivery_terms_id=si.stock_delivery_terms_id " .
                "WHERE (sit.stock_delivery_terms_text='" . tep_db_input($text) . "' OR sit.stock_delivery_terms_text='" . tep_db_input(trim($text)) . "') ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$text] = $get_id['stock_delivery_terms_id'];
            }

        }
        return $cached[$text];
    }

    public function getPlatformName($id)
    {
        static $lookuped = [];
        if ( !isset($lookuped[(int)$id]) ) {
            $lookuped[(int)$id] = '';
            $get_name_r = tep_db_query("SELECT platform_name FROM ".TABLE_PLATFORMS." WHERE platform_id='".(int)$id."'");
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $get_name = tep_db_fetch_array($get_name_r);
                $lookuped[(int)$id] = $get_name['platform_name'];
            }
        }
        return $lookuped[(int)$id];
    }

    public function getPlatformId($name)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$name]) ) {
            $lookuped[$name] = 0;
            $get_id_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS." WHERE platform_name='".tep_db_input($name)."'");
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $lookuped[$name] = $get_id['platform_id'];
            }
        }
        return $lookuped[$name];
    }

    public function get_document_types_name($id, $language_id)
    {
        static $lookuped = [];
        if ( !isset($lookuped[(int)$id]) ) {
            $lookuped[(int)$id] = '';
            $get_name_r = tep_db_query(
                "SELECT document_types_name FROM ".TABLE_DOCUMENT_TYPES." ".
                "WHERE document_types_id='".(int)$id."' AND language_id='".$language_id."'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $get_name = tep_db_fetch_array($get_name_r);
                $lookuped[(int)$id] = $get_name['document_types_name'];
            }
        }
        return $lookuped[(int)$id];
    }

    public function get_document_types_by_name($name)
    {
        static $cached = [];
        if (!isset($cached[$name])) {
            $cached[$name] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT dt.document_types_id " .
                "FROM " . TABLE_DOCUMENT_TYPES . " dt " .
                "WHERE dt.document_types_name='" . tep_db_input($name) . "' ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$name] = $get_id['document_types_id'];
            }else{
                $get_max_value = tep_db_fetch_array(tep_db_query(
                    "SELECT MAX(document_types_id) AS max_id FROM ".TABLE_DOCUMENT_TYPES
                ));
                $doc_id = intval($get_max_value['max_id'])+1;
                tep_db_query(
                    "INSERT INTO ".TABLE_DOCUMENT_TYPES." (document_types_id, language_id, document_types_name) ".
                    "SELECT '{$doc_id}', languages_id, '".tep_db_input(trim($name))."' FROM ".TABLE_LANGUAGES
                );
                $cached[$name] = $doc_id;
            }
        }
        return $cached[$name];
    }

}