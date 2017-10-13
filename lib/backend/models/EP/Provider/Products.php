<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Provider;

use backend\models\EP\Formatter;
use backend\models\EP;
use backend\models\EP\Messages;
use yii\base\Exception;

class Products extends ProviderAbstract implements ImportInterface, ExportInterface {

    protected $entry_counter;
    protected $max_categories = 7;
    protected $fields = array();
    protected $additional_fields = array();
    protected $fields_languages = array();

    protected $additional_data = array();

    protected $data = array();
    protected $EPtools;
    protected $export_query;

    function __construct( )
    {
        parent::__construct();
        $this->initFields();
        $this->initAdditionalFields();

        $this->EPtools = new EP\Tools();
    }

    protected function initFields()
    {
        global $currencies;

        //$this->fields[] = array('name' => 'products_tax_class_id', 'value' => 'Products Tax Class possible values: ' . $txt);

        $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key'=>true );
        $this->fields[] = array( 'name' => 'products_quantity', 'value' => 'Products Quantity', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'sort_order', 'value' => 'Products Sort order' );
        $this->fields[] = array( 'name' => 'products_date_available', 'value' => 'Products Date Available', 'type' => 'date' );
        $this->fields[] = array( 'name' => 'weight_cm', 'value' => 'Products Weight', 'type' => 'numeric' );

        $this->fields[] = array( 'name' => 'products_ean', 'value' => 'EAN' );
        $this->fields[] = array( 'name' => 'products_asin', 'value' => 'ASIN' );
        $this->fields[] = array( 'name' => 'products_isbn', 'value' => 'ISBN' );
        $this->fields[] = array( 'name' => 'order_quantity_minimal', 'value' => 'Order quantity minimal', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'order_quantity_step', 'value' => 'Order quantity step', 'type' => 'int' );

        $this->fields[] = array( 'name' => 'products_status', 'value' => 'Products Status', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'manufacturers_id', 'value' => 'Brand', 'set'=>'set_brand', 'get'=>'get_brand' );

// {{
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        $txt = '';
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $txt .= $tax_class['tax_class_id'] . '=' . $tax_class['tax_class_title'] . ';';
        }
        $this->fields[] = array('name' => 'products_tax_class_id', 'value' => 'Products Tax Class', 'set'=>'set_tax_class', 'get'=>'get_tax_class');

        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {

                $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|'.$value['id'].'|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_price',
                    'name' => 'products_price_' . $value['id'] . '_0',
                    'value' => 'Products Price ' . $key,
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_discount_price',
                    'name' => 'products_price_discount_' . $value['id'] . '_0',
                    'value' => 'Products Discount Price ' . $key . ' ',
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table'
                );

                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'products_group_price',
                        'name' => 'products_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Products Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_price', 'set' => 'set_products_price',
                        'type' => 'numeric'
                    );

                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'products_group_discount_price',
                        'name' => 'products_price_discount_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Products Discount Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                        'type' => 'price_table'
                    );

                }

            }
        } else {
            //$data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|0';
            $this->fields[] = array(
                //'data_descriptor' => $data_descriptor,
                //'column_db' => 'products_group_price',
                'column_db' => 'products_price',
                'name' => 'products_price_0',
                'value' => 'Products Price',
                //'get' => 'get_products_price', 'set' => 'set_products_price',
                'type' => 'numeric'
            );
            $this->fields[] = array(
                //'data_descriptor' => $data_descriptor,
                //'column_db' => 'products_group_discount_price',
                'column_db' => 'products_price_discount',
                'name' => 'products_price_discount_0',
                'value' => 'Products Discount Price',
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table'
            );

            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_price',
                    'name' => 'products_price_' . $groups_data['groups_id'],
                    'value' => 'Products Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_discount_price',
                    'name' => 'products_price_discount_' . $groups_data['groups_id'],
                    'value' => 'Products Discount Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table'
                );
            }
        }

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|'.TABLE_PRODUCTS_DESCRIPTION.'|'.$_lang['id'].'|0';
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_name',
                'name' => 'products_name_'.$_lang['code'].'_0',
                'value' => 'Products Name '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_description_short',
                'name' => 'products_description_short_'.$_lang['code'].'_0',
                'value' => 'Products Short Description '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_description',
                'name' => 'products_description_'.$_lang['code'].'_0',
                'value' => 'Products Description '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_url',
                'name' => 'products_url_'.$_lang['code'].'_0',
                'value' => 'Products URL '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_head_title_tag',
                'name' => 'products_head_title_tag_'.$_lang['code'].'_0',
                'value' => 'Products Head Title Tag '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_head_desc_tag',
                'name' => 'products_head_desc_tag_'.$_lang['code'].'_0',
                'value' => 'Products Description Tag '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_head_keywords_tag',
                'name' => 'products_head_keywords_tag_'.$_lang['code'].'_0',
                'value' => 'Products Keywords Tag '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_self_service',
                'name' => 'products_self_service_'.$_lang['code'].'_0',
                'value' => 'Self-service metadata '.$_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_seo_page_name',
                'name' => 'products_seo_page_name_'.$_lang['code'].'_0',
                'value' => 'Products SEO page name '.$_lang['code'],
            );
        }

        $this->fields[] = array( 'name' => 'is_virtual', 'value' => 'Is Virtual?', 'type' => 'int' );

        $this->fields[] = array( 'name' => 'dimensions_cm', 'value' => 'Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'length_cm', 'value' => 'Length (cm)');
        $this->fields[] = array( 'name' => 'width_cm', 'value' => 'Width (cm)');
        $this->fields[] = array( 'name' => 'height_cm', 'value' => 'Height (cm)');

        $this->fields[] = array( 'name' => 'inner_carton_dimensions_cm', 'value' => 'Pack Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'inner_length_cm', 'value' => 'Pack Length (cm)');
        $this->fields[] = array( 'name' => 'inner_width_cm', 'value' => 'Pack Width (cm)');
        $this->fields[] = array( 'name' => 'inner_height_cm', 'value' => 'Pack Height (cm)');
        $this->fields[] = array( 'name' => 'inner_weight_cm', 'value' => 'Pack Weight (kg)');
        $this->fields[] = array( 'name' => 'pack_unit', 'value' => 'Pack products QTY');
        $this->fields[] = array( 'name' => 'products_price_pack_unit', 'value' => 'Pack Price');
        $this->fields[] = array( 'name' => 'products_price_discount_pack_unit', 'value' => 'Pack Quantity Discount Table');
        foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
            $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_price_pack_unit',
                'name' => 'products_group_price_pack_unit_' . $groups_data['groups_id'],
                'value' => 'Pack Price ' . $groups_data['groups_name'],
                //'get' => 'get_products_price', 'set' => 'set_products_price',
                'type' => 'numeric'
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_discount_price_pack_unit',
                'name' => 'products_group_discount_price_pack_unit_' . $groups_data['groups_id'],
                'value' => 'Pack Quantity Discount Table ' . $groups_data['groups_name'],
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table'
            );
        }



        $this->fields[] = array( 'name' => 'outer_carton_dimensions_cm', 'value' => 'Pallet Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'outer_length_cm', 'value' => 'Pallet Length (cm)');
        $this->fields[] = array( 'name' => 'outer_width_cm', 'value' => 'Pallet Width (cm)');
        $this->fields[] = array( 'name' => 'outer_height_cm', 'value' => 'Pallet Height (cm)');
        $this->fields[] = array( 'name' => 'outer_weight_cm', 'value' => 'Pallet Weight (kg)');
        $this->fields[] = array( 'name' => 'packaging', 'value' => 'Pallet Pack QTY');
        $this->fields[] = array( 'name' => 'products_price_packaging', 'value' => 'Pallet Price');
        $this->fields[] = array( 'name' => 'products_price_discount_packaging', 'value' => 'Pallet Quantity Discount Table');
        foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
            $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_price_packaging',
                'name' => 'products_group_price_packaging_' . $groups_data['groups_id'],
                'value' => 'Pallet Price ' . $groups_data['groups_name'],
                //'get' => 'get_products_price', 'set' => 'set_products_price',
                'type' => 'numeric'
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_discount_price_packaging',
                'name' => 'products_group_discount_price_packaging_' . $groups_data['groups_id'],
                'value' => 'Pallet Quantity Discount Table ' . $groups_data['groups_name'],
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table'
            );
        }

        $this->fields[] = array( 'name' => 'products_price_full', 'value' => 'Full price?');

        $this->fields[] = array( 'name' => 'stock_indication_id', 'value' => 'Stock Availability', 'get' => 'get_stock_indication', 'set' => 'set_stock_indication', );
        $this->fields[] = array( 'name' => 'stock_delivery_terms_id', 'value' => 'Stock Delivery Terms', 'get' => 'get_delivery_terms', 'set' => 'set_delivery_terms', );

        for( $i = 0; $i < $this->max_categories; $i++ ) {
            $this->fields[] = array(
                'data_descriptor' => '@|linked_categories|'.$i,
                'name' => '_categories_'.$i,
                'value' => TEXT_CATEGORIES . '_' . $i,
                'get' => 'get_category',
                'set' => 'set_category',
            );
        }

        foreach(\common\classes\platform::getList() as $platformInfo){
            $this->fields[] = array(
                'data_descriptor' => '@|assigned_platforms|0',
                'name' => 'platform_assign_'.$platformInfo['id'],
                'value' => 'Platform - ' . $platformInfo['text'],
                'get' => 'get_platform',
                'set' => 'set_platform',
            );
        }

    }

    protected function initAdditionalFields()
    {
        $additional_fields              = array();
        $additional_fields[0]           = array( 'table' => TABLE_MANUFACTURERS, 'link_field' => 'manufacturers_id', 'language_table' => TABLE_MANUFACTURERS_INFO, 'table_prefix' => 'm', 'language_table_prefix' => 'mi', 'language_field' => 'languages_id', 'default_empty' => NULL );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_name', 'value' => 'Manufacturers Name', 'language' => '0' );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_image', 'value' => 'Manufacturers Image', 'language' => '0' );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_url', 'value' => 'Manufacturers URL', 'language' => '1' );

        $this->additional_fields = $additional_fields;
    }

    public function prepareExport($useColumns, $filter){
        $this->buildSources($useColumns);

        $main_source = $this->main_source;
        
        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }
        $main_sql =
            "SELECT {$main_source['select']} products_id, ".
            " manufacturers_id as _manufacturers_id, ".
            " products_price AS products_price_def, products_price_discount AS products_price_discount_def ".
            "FROM ".TABLE_PRODUCTS." ".
            "WHERE 1 {$filter_sql} ".
            "/*LIMIT 3*/";

        $this->export_query = tep_db_query( $main_sql );
    }

    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;
        
        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_PRODUCTS_DESCRIPTION ) {
                    $data_sql .= "AND products_id='{$this->data['products_id']}' AND language_id='{$source_data['params'][0]}' AND affiliate_id='{$source_data['params'][1]}'";
                }elseif ( $source_data['table']==TABLE_PRODUCTS_PRICES  ) {
                    $data_sql .= "AND products_id='{$this->data['products_id']}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                }else{
                    $data_sql .= "AND 1=0 ";
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if ( tep_db_num_rows($data_sql_r)>0 ) {
                    $_data = tep_db_fetch_array($data_sql_r);
                    $this->data = array_merge($this->data, $_data);
                }
            }elseif($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
            }
        }
        
        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }
        return $this->data;
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;

        $this->data = $data;
        $is_updated = false;
        $need_touch_date_modify = true;
        $_is_product_added = false;

        if (!array_key_exists($file_primary_column, $data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = $data[$file_primary_column];
        $get_main_data_r = tep_db_query(
            "SELECT p.*, COUNT(pa.products_attributes_id) as _attr_counter ".
            "FROM " . TABLE_PRODUCTS . " p ".
            "  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa ON pa.products_id=p.products_id ".
            "WHERE p.{$file_primary_column}='" . tep_db_input($file_primary_value) . "' ".
            "GROUP BY p.products_id"
        );
        $found_rows = tep_db_num_rows($get_main_data_r);
        if ($found_rows > 1) {
            // error data not unique
            $message->info('"'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
            // create new entry
            $create_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;

                if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
                }

                $create_data_array[$db_column] = $this->data[$file_column];
            }
            if ( array_key_exists('weight_cm', $create_data_array) ) {
                $create_data_array['products_weight'] = $create_data_array['weight_cm'];
            }
            foreach ( array('length_', 'width_', 'height_', 'inner_length_', 'inner_width_', 'inner_height_', 'outer_length_', 'outer_width_', 'outer_height_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$create_data_array) ) {
                    $create_data_array[$metricKey.'in'] = 0.393707143*$create_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$create_data_array)){
                    $create_data_array[$metricKey.'cm'] = 2.539959*$create_data_array[$metricKey.'in'];
                }
            }
            foreach ( array('weight_', 'inner_weight_', 'outer_weight_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$create_data_array) ) {
                    $create_data_array[$metricKey.'in'] = 2.20462262*$create_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$create_data_array)){
                    $create_data_array[$metricKey.'cm'] = 0.45359237*$create_data_array[$metricKey.'in'];
                }
            }

            $create_data_array['products_date_added'] = 'now()';

            tep_db_perform(TABLE_PRODUCTS, $create_data_array);
            $products_id = tep_db_insert_id();

            $need_touch_date_modify = false;
            $_is_product_added = true;
            $message->info('Create "'.$file_primary_value.'"');
        } else {
            // update

            $db_main_data = tep_db_fetch_array($get_main_data_r);
            $products_id = $db_main_data['products_id'];
            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;

                if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
                }

                $update_data_array[$db_column] = $this->data[$file_column];
            }
            if ( isset($update_data_array['products_quantity']) ) {
                if ( $db_main_data['_attr_counter']>0 ) {
                    unset($update_data_array['products_quantity']);
                }else{
                    if ( false && $db_main_data['products_status'] && $db_main_data['products_quantity']>0 && $update_data_array['products_quantity']<=0 ) {
                        $switch_off_stock_ids = \common\classes\StockIndication::productDisableByStockIds();
                        if ( isset($update_data_array['stock_indication_id']) && isset( $switch_off_stock_ids[$update_data_array['stock_indication_id']] ) ) {
                            $update_data_array['products_status'] = 0;
                            $message->info('Product "'.$file_primary_value.'" disabled');
                        }elseif ( isset($db_main_data['stock_indication_id']) && isset( $switch_off_stock_ids[$db_main_data['stock_indication_id']] ) ) {
                            $update_data_array['products_status'] = 0;
                        }
                    }
                }
            }
            if ( array_key_exists('weight_cm', $update_data_array) ) {
                $update_data_array['products_weight'] = $update_data_array['weight_cm'];
            }
            foreach ( array('length_', 'width_', 'height_', 'inner_length_', 'inner_width_', 'inner_height_', 'outer_length_', 'outer_width_', 'outer_height_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$update_data_array) ) {
                    $update_data_array[$metricKey.'in'] = 0.393707143*$update_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$update_data_array)){
                    $update_data_array[$metricKey.'cm'] = 2.539959*$update_data_array[$metricKey.'in'];
                }
            }
            foreach ( array('weight_', 'inner_weight_', 'outer_weight_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$update_data_array) ) {
                    $update_data_array[$metricKey.'in'] = 2.20462262*$update_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$update_data_array)){
                    $update_data_array[$metricKey.'cm'] = 0.45359237*$update_data_array[$metricKey.'in'];
                }
            }

            if (count($update_data_array) > 0) {
                $update_data_array['products_last_modified'] = 'now()';
                tep_db_perform(TABLE_PRODUCTS, $update_data_array, 'update', "products_id='" . (int)$products_id . "'");
                $is_updated = true;
                $need_touch_date_modify = false;
            }
        }
        //$entry_counter++;
        $this->data['products_id'] = $products_id;

        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $data)) continue;
                    if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
                    }
                    $new_data[$db_column] = $this->data[$file_column];
                }
                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_PRODUCTS_DESCRIPTION) {
                    $update_pk = "products_id='{$products_id}' AND language_id='{$source_data['params'][0]}' AND affiliate_id='{$source_data['params'][1]}'";
                    $insert_pk = array('products_id' => $products_id, 'language_id' => $source_data['params'][0], 'affiliate_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";

                    if (defined('MSEARCH_ENABLE') && MSEARCH_ENABLE == 'true') {
                        if (isset($new_data['products_name']) && strlen($new_data['products_name']) > 0) {
                            $products_name_keywords = array_filter(preg_split('/[\s]+/', strip_tags($new_data['products_name'])), function ($__word) {
                                return strlen($__word) >= (defined('MSEARCH_WORD_LENGTH')?intval(MSEARCH_WORD_LENGTH):0);
                            });
                            if (count($products_name_keywords) > 0) {
                                $ks_hash = tep_db_fetch_array(tep_db_query(
                                    "SELECT CONCAT(" . implode(', ', array_map(function ($_word) {
                                        return 'SOUNDEX(\'' . tep_db_input($_word) . '\'),\',\'';
                                    }, $products_name_keywords)) . ") AS sx"
                                ));
                                if (is_array($ks_hash)) {
                                    $new_data['products_name_soundex'] = implode(',', array_unique(preg_split('/,/', $ks_hash['sx'], -1, PREG_SPLIT_NO_EMPTY)));
                                }
                            }
                        }

                        if (isset($new_data['products_description']) && strlen($new_data['products_description']) > 0) {
                            $products_description_keywords = array_filter(preg_split('/[\s]+/', strip_tags($new_data['products_description'])), function ($__word) {
                                return strlen($__word) >= (defined('MSEARCH_WORD_LENGTH')?intval(MSEARCH_WORD_LENGTH):0);
                            });
                            if (count($products_description_keywords) > 0) {
                                $ks_hash = tep_db_fetch_array(tep_db_query(
                                    "SELECT CONCAT(" . implode(', ', array_map(function ($_word) {
                                        return 'SOUNDEX(\'' . tep_db_input($_word) . '\'),\',\'';
                                    }, $products_description_keywords)) . ") AS sx"
                                ));
                                if (is_array($ks_hash)) {
                                    $new_data['products_description_soundex'] = implode(',', array_unique(preg_split('/,/', $ks_hash['sx'], -1, PREG_SPLIT_NO_EMPTY)));
                                }
                            }
                        }
                    }

                    if (empty($new_data['products_seo_page_name']) && !empty($new_data['products_name']) ) {
                        $new_data['products_seo_page_name'] = \common\helpers\Seo::makeSlug($new_data['products_name']);
                    }
                    if (empty($new_data['products_seo_page_name']) && !empty($new_data['products_model']) ) {
                        $new_data['products_seo_page_name'] = \common\helpers\Seo::makeSlug($new_data['products_model']);
                    }
                    if (empty($new_data['products_seo_page_name'])) {
                        $new_data['products_seo_page_name'] = $products_id;
                    }

                    /*if (empty($new_data['products_seo_page_name'])) {
                      $new_data['products_seo_page_name'] = $products_id;
                    }*/
                } elseif ($source_data['table'] == TABLE_PRODUCTS_PRICES) {
                    $update_pk = "products_id='{$products_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                    $insert_pk = array('products_id' => $products_id, 'currencies_id' => $source_data['params'][0], 'groups_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";
                } else {
                    continue;
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if (tep_db_num_rows($data_sql_r) > 0) {
                    //$_data = tep_db_fetch_array($data_sql_r);
                    tep_db_free_result($data_sql_r);
                    //echo '<pre>update rel '; var_dump($source_data['table'],$new_data,'update', $update_pk); echo '</pre>';
                    tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);
                } else {
                    //echo '<pre>insert rel '; var_dump($source_data['table'],array_merge($new_data,$insert_pk)); echo '</pre>';
                    tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                }
                $is_updated = true;
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
                    }
                }
            }
        }

        if ($is_updated && $need_touch_date_modify) {
            //-- products_seo_page_name
            tep_db_perform(TABLE_PRODUCTS, array(
                'products_last_modified' => 'now()',
            ), 'update', "products_id='" . (int)$products_id . "'");

            //products_name_soundex, products_description_soundex, products_seo_page_name
        }
        if ( $_is_product_added ) {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$products_id."' "));
            if ( $_check['c']==0 ) {
                tep_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, array(
                    'products_id' => $products_id,
                    'categories_id' => 0,
                ));
            }
        }
        return true;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' products');
        $message->info('Done.');

        $this->EPtools->done('products_import');
    }


    function linked_categories($link)
    {
        if ( !isset($this->data['assigned_categories']) ) {
            $this->data['assigned_categories'] = array();
            $this->data['assigned_categories_ids'] = array();
            $get_categories_r = tep_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$this->data['products_id']."' ORDER BY categories_id");
            while( $get_category = tep_db_fetch_array($get_categories_r) ) {
                $this->data['assigned_categories_ids'][] = (int)$get_category['categories_id'];
                $this->data['assigned_categories'][] = $this->EPtools->tep_get_categories_full_path((int)$get_category['categories_id']);
            }
            $this->data['assign_categories_ids'] = array();
        }
    }

    function get_category($field_data)
    {
        $idx = intval(str_replace('_categories_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_categories'][$idx])?$this->data['assigned_categories'][$idx]:'';
        return $this->data[$field_data['name']];
    }

    function set_category($field_data, $products_id)
    {
        $import_category_path = $this->data[$field_data['name']];
        $category_id = $this->EPtools->tep_get_categories_by_name($import_category_path);
        if ( is_numeric($category_id) ) {
            $this->data['assign_categories_ids'] = $category_id;
            tep_db_query("INSERT IGNORE INTO ".TABLE_PRODUCTS_TO_CATEGORIES." (products_id, categories_id) VALUES('".(int)$products_id."', '".(int)$category_id."')");
        }
        return ;
    }

    function assigned_platforms()
    {
        if ( !isset($this->data['assigned_platforms']) ) {
            $this->data['assigned_platforms'] = [];
            $get_assigned_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS_PRODUCTS." WHERE products_id='".$this->data['products_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_platforms'][ (int)$_assigned['platform_id'] ] = (int)$_assigned['platform_id'];
                }
            }
        }
    }

    function get_platform($field_data)
    {
        $idx = intval(str_replace('platform_assign_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_platforms'][$idx])?'1':'';
        return $this->data[$field_data['name']];
    }

    function set_platform($field_data, $products_id)
    {
        if ( empty($products_id) ) return;

        $platform_id = intval(str_replace('platform_assign_','', $field_data['name']));
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }

        if ( isset($this->data['assigned_platforms'][$platform_id]) ) {
            if (!$fileValue) {
                tep_db_query("DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." WHERE platform_id='".(int)$platform_id."' AND products_id='".(int)$products_id."'");
            }
        }else{
            if ($fileValue) {
                tep_db_query("INSERT IGNORE INTO ".TABLE_PLATFORMS_PRODUCTS." (platform_id, products_id) VALUES('".(int)$platform_id."', '".(int)$products_id."')");
            }
        }
    }

    function get_products_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = 'same'/*'-2'*/;
        }elseif( floatval($this->data[$field_data['name']])==-2 ) {
            $this->data[$field_data['name']] = 'same';
        }elseif( floatval($this->data[$field_data['name']])==-1 ) {
            $this->data[$field_data['name']] = 'disabled';
        }
        return $this->data[$field_data['name']];
    }

    function set_products_price( $field_data, $products_id )
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-2 || $this->data[$field_data['name']]=='same' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disabled' ) {
            $this->data[$field_data['name']] = '-1';
        }
        return '';
    }

    function get_products_discount_price( $field_data, $products_id )
    {
        return '';
    }

    function get_brand( $field_data, $products_id ){
        static $brands = false;
        if ( !is_array($brands) ) {
            $brands = array();
            $get_brands_r = tep_db_query("SELECT manufacturers_id, manufacturers_name FROM ".TABLE_MANUFACTURERS);
            if ( tep_db_num_rows($get_brands_r)>0 ) {
                while( $get_brand = tep_db_fetch_array($get_brands_r) ) {
                    $brands[$get_brand['manufacturers_id']] = $get_brand['manufacturers_name'];
                }
            }
        }
        $this->data['manufacturers_id'] = isset($brands[$this->data['manufacturers_id']])?$brands[$this->data['manufacturers_id']]:'';
        return $this->data['manufacturers_id'];
    }

    function set_brand( $field_data, $products_id ){
        $this->data['manufacturers_id'] = $this->EPtools->get_brand_by_name($this->data['manufacturers_id']);
    }

    function set_tax_class($field_data, $products_id, $message){
        static $fetched_map = array();
        $file_value = trim($this->data['products_tax_class_id']);
        $tax_class_id = 0;

        if ( !isset($fetched_map[$file_value]) ) {
            if ( empty($file_value) ) {
                $tax_class_id = 0;
            }else
                if ( is_numeric($file_value) ) {
                    $check_number = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS check FROM " . TABLE_TAX_CLASS . " WHERE tax_class_id='" . (int)$file_value . "' "));
                    if (is_array($check_number) && $check_number['check'] > 0) {
                        $fetched_map[$file_value] = (int)$file_value;
                        $tax_class_id = (int)$file_value;
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 0;
                    }
                }else{
                    $get_by_name_r = tep_db_query(
                        "SELECT tax_class_id FROM " . TABLE_TAX_CLASS . " WHERE tax_class_title='" . tep_db_input($file_value) . "' LIMIT 1"
                    );
                    if (tep_db_num_rows($get_by_name_r)>0) {
                        $get_by_name = tep_db_fetch_array($get_by_name_r);
                        $fetched_map[$file_value] = $get_by_name['tax_class_id'];
                        $tax_class_id = $get_by_name['tax_class_id'];
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 0;
                    }
                }
        }else{
            $tax_class_id = $fetched_map[$file_value];
        }
        $this->data['products_tax_class_id'] = $tax_class_id;
        return $tax_class_id;
    }

    function get_tax_class($field_data, $products_id){
        static $fetched = false;
        if ( !is_array($fetched) ) {
            $fetched = array();
            $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS );
            if ( tep_db_num_rows($tax_class_query)>0 ) {
                while ($tax_class = tep_db_fetch_array($tax_class_query)) {
                    $fetched[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
                }
            }
        }
        $this->data['products_tax_class_id'] = isset( $fetched[$this->data['products_tax_class_id']] )?$fetched[$this->data['products_tax_class_id']]:'';
        return $this->data['products_tax_class_id'];
    }

    function get_delivery_terms($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockDeliveryTerms($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_delivery_terms($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockDeliveryTermId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function get_stock_indication($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockIndication($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_stock_indication($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockIndicationId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

}