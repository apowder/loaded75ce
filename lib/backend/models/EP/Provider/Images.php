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

class Images extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    protected $EPtools;
    protected $_processed_pids = array();
    protected $import_folder;

    protected $entry_counter;
    protected $export_query;

    function __construct()
    {
        parent::__construct();
        $this->initFields();
        $this->EPtools = new EP\Tools();

    }
    
    public function setImagesDirectory($imagesFolder)
    {
        $this->import_folder = $imagesFolder;
    }

    protected function initFields(){
        $this->fields = array();
        $this->fields[] = array( 'name' => 'products_model', 'calculated'=>true, 'value' => 'Products Model', 'is_key' => true,);
        $this->fields[] = array( 'name' => 'products_ean', 'calculated'=>true, 'value' => 'EAN', 'is_key' => true);
        $this->fields[] = array( 'name' => 'products_asin', 'calculated'=>true, 'value' => 'ASIN', 'is_key' => true);
        $this->fields[] = array( 'name' => 'products_isbn', 'calculated'=>true, 'value' => 'ISBN', 'is_key' => true);
        $this->fields[] = array( 'name' => 'products_name', 'calculated'=>true, 'value' => 'Products Name',);

        $this->fields[] = array( 'name' => 'default_image', 'value' => 'Default Image',);
        $this->fields[] = array( 'name' => 'image_status', 'value' => 'Image Status',);
        $this->fields[] = array( 'name' => 'sort_order', 'value' => 'Sort Order', 'column_db' => 'sort_order', 'prefix' => 'pi');

        $data_descriptor = '%|' . TABLE_PRODUCTS_IMAGES_DESCRIPTION . '|0';
        $this->fields[] = array(
            'data_descriptor' => $data_descriptor,
            'column_db' => 'image_title',
            'name' => 'image_title_main',
            'value' => 'Image Title Main'
        );

        $this->fields[] = array(
            'data_descriptor' => $data_descriptor,
            'column_db' => 'image_alt',
            'name' => 'image_alt_main',
            'value' => 'Image Alt Main',
        );
        $this->fields[] = array(
            'data_descriptor' => $data_descriptor,
            'column_db' => 'orig_file_name',
            'name' => 'orig_file_name_main',
            'value' => 'Original filename Main',
        );
        /*$this->fields[] = array(
          'data_descriptor' => $data_descriptor,
          'column_db' => 'file_name',
          'name' => 'file_name_main',
          'value' => 'Filename Main',
        );*/
        $this->fields[] = array(
            'data_descriptor' => $data_descriptor,
            'column_db' => 'alt_file_name',
            'name' => 'alt_file_name_main',
            'value' => 'Alt Filename Main',
        );

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_PRODUCTS_IMAGES_DESCRIPTION . '|' . $_lang['id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'image_title',
                'name' => 'image_title_' . $_lang['code'],
                'value' => 'Image Title ' . $_lang['code'],
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'image_alt',
                'name' => 'image_alt_' . $_lang['code'],
                'value' => 'Image Alt ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'orig_file_name',
                'name' => 'orig_file_name_' . $_lang['code'],
                'value' => 'Original filename ' . $_lang['code'],
            );
            /*$this->fields[] = array(
              'data_descriptor' => $data_descriptor,
              'column_db' => 'file_name',
              'name' => 'file_name_' . $_lang['code'],
              'value' => 'Filename ' . $_lang['code'],
            );*/
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'alt_file_name',
                'name' => 'alt_file_name_' . $_lang['code'],
                'value' => 'Alt Filename ' . $_lang['code'],
            );
        }

    }

    function get_key_field( $field_data, $categories_id ){
        //$this->data['key_field'] = $this->EPtools->tep_get_categories_full_path((int)$this->data['categories_id']);
    }

    protected function buildSources($useColumns){
        if (parent::buildSources($useColumns)){
            $this->file_primary_column = [];
            foreach ($this->fields as $_field) {
                if (isset($_field['is_key']) && $_field['is_key'] === true ) {
                  $this->file_primary_column[] = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
                }
            }
            return true;
        }
        return false;
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);
        $main_source = $this->main_source;
        
        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND p.products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }

        $main_sql =
            "SELECT {$main_source['select']} pi.products_images_id, p.products_id, p.products_model, p.products_ean, p.products_isbn, p.products_asin, pd.products_name " .
            "FROM ".TABLE_PRODUCTS_DESCRIPTION." pd, " . TABLE_PRODUCTS . " p ".
            " LEFT JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pi.products_id=p.products_id " .
            "WHERE p.products_id=pd.products_id AND pd.language_id='".intval($this->languages_id)."' AND pd.affiliate_id=0 ".
            " {$filter_sql} ".
            "ORDER BY p.products_id, IFNULL(pi.sort_order,0) ";

        $this->export_query = tep_db_query($main_sql);
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
                if ( $source_data['table']==TABLE_PRODUCTS_IMAGES_DESCRIPTION ) {
                    $data_sql .= "AND products_images_id='{$this->data['products_images_id']}' AND language_id='{$source_data['params'][0]}' ";
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
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_images_id']));
            }
        }

        return $this->data;
    }

    private function _checkEmptyKeys(array $primary_value){
        foreach($primary_value as $field => $value){
            $get_main_data_r = tep_db_query(
                "SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE {$field} = '" . tep_db_input($value) . "'"
            );
            if (tep_db_num_rows($get_main_data_r)){
                $found_rows = tep_db_num_rows($get_main_data_r);
                $ex = explode("_", $field);
                return [
                    'primary_value' => $value,
                    'found_rows' => $found_rows,
                    'key' => $ex[1],
                    'data' => $found_rows>0?tep_db_fetch_array($get_main_data_r):false,
                ];
            }
        }
        return false;
    }


    public function importRow($data, Messages $message)
    {
        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;


        $this->data = $data;

        if (!( count(array_intersect ($file_primary_column, array_keys ($data))) >0 ) ){
            throw new EP\Exception('Primary key(s) not found in file');
        }
        /*
        if (!array_key_exists($file_primary_column, $data)) {
        }*/
        $file_primary_value = [];

        foreach($file_primary_column as $key => $file_primary_column_key){
            if (!empty($data[$file_primary_column_key])){
                $file_primary_value[$file_primary_column_key] = $data[$file_primary_column_key];
            } else {
                // unset($file_primary_column[$key]);
            }
        }

        if ( empty($file_primary_value) || count($file_primary_value) == 0) {
            //$message->info('Empty "'.$export_columns[$file_primary_column]['value'].'" column. Row skipped');
            $message->info('Key fields are empty. Row skipped');
            return false;
        }

        $_result = $this->_checkEmptyKeys($file_primary_value);

        if ( is_array($_result) && isset($_result['primary_value'])){
            $file_primary_value = $_result['primary_value'];
        }else {
            $message->info('Lost primary value. Row skipped');
            return false;
        }

        if (is_array($_result) && !isset($_products_lookup[$_result['primary_value']]) ) {
            $found_rows = $_result['found_rows'];
            $_products_lookup[$file_primary_value] = array(
                'found_rows' => (int)$_result['found_rows'],
                'data' => $_result['data'],
            );

            if ($found_rows > 1) {
                $message->info('Product '. $_result['key']. ' "'.$file_primary_value.'"  not unique - found '.$found_rows.' rows. Skipped');
            }elseif ($found_rows == 0) {
                $message->info('Product '. $_result['key']. ' "'.$file_primary_value.'" not found. Skipped');
            }else{
                if (isset($_products_lookup[$file_primary_value]['data']['products_id']) ){
                    $_products_lookup[$file_primary_value]['data']['images'] = [];
                    $_tmp = tep_db_query("select pi.products_images_id, pd.orig_file_name, pd.hash_file_name, pd.file_name, pd.language_id from " . TABLE_PRODUCTS_IMAGES . " pi left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pd on pd.products_images_id = pi.products_images_id where pi.products_id = {$_products_lookup[$file_primary_value][data]['products_id']}");
                    if (tep_db_num_rows($_tmp)){
                        while($_products_lookup[$file_primary_value]['data']['images'][] = tep_db_fetch_array($_tmp));
                    }
                    unset($_tmp);
                }

                tep_db_query(
                    "DELETE image_desc FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." image_desc ".
                    "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." image_main ON image_main.products_images_id=image_desc.products_images_id ".
                    "WHERE image_main.products_id='{$_products_lookup[$file_primary_value]['data']['products_id']}'"
                );
                tep_db_query(
                    "DELETE FROM ".TABLE_PRODUCTS_IMAGES." ".
                    "WHERE products_id='{$_products_lookup[$file_primary_value]['data']['products_id']}'"
                );
            }
            //$entry_counter++;
        }

        $found_rows = (isset($_products_lookup[$file_primary_value]['found_rows']) ? $_products_lookup[$file_primary_value]['found_rows'] : 0);

        if ($found_rows > 1) {
            // error data not unique
            //$message->info('Product "'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        }elseif ($found_rows == 0) {
            // dummy
            //$message->info('Product "'.$file_primary_value.'" not found. Skipped');
            return false;
        }else{
            $db_main_data = $_products_lookup[$file_primary_value]['data'];
            $products_id = $db_main_data['products_id'];
        }
        $this->data['products_id'] = $products_id;

        $insert_data_array = array();
        foreach ($main_source['columns'] as $file_column => $db_column) {
            if (!array_key_exists($file_column, $data)) continue;
            if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id']));
            }
            $insert_data_array[$db_column] = $this->data[$file_column];
        }
        $insert_data_array['products_id'] = $this->data['products_id'];

        if (!isset($insert_data_array['default_image'])){
            $insert_data_array['default_image'] = 1;
        }

        if (!isset($insert_data_array['image_status'])){
            $insert_data_array['image_status'] = 1;
        }

        tep_db_perform(TABLE_PRODUCTS_IMAGES, $insert_data_array);
        $products_images_id = tep_db_insert_id();
        $this->data['products_images_id'] = $products_images_id;

        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $data)) continue;
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_images_id']));
                    }
                    $new_data[$db_column] = $this->data[$file_column];
                }
                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_PRODUCTS_IMAGES_DESCRIPTION) {
                    $update_pk = "products_images_id='{$products_images_id}' AND language_id='{$source_data['params'][0]}' ";
                    $insert_pk = array('products_images_id' => $products_images_id, 'language_id' => $source_data['params'][0], );
                    $data_sql .= " AND {$update_pk} ";
                } else {
                    continue;
                }


                $exist = false;$change_filename = false;
                if (isset($_products_lookup[$file_primary_value]['data']['images']) && sizeof($_products_lookup[$file_primary_value]['data']['images']) > 0){
                    foreach($_products_lookup[$file_primary_value]['data']['images'] as $image_data){
                        if (isset($new_data['orig_file_name']) && !empty($new_data['orig_file_name']) && $image_data['orig_file_name'] == $new_data['orig_file_name'] && $image_data['language_id'] == $source_data['params'][0]){
                            $image_location = \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . $this->data['products_id'] . DIRECTORY_SEPARATOR;
                            if (is_dir($image_location.$image_data['products_images_id'])) {
                                $exist = true;
                                $this->data['current_image_data'] = $image_data;
                                if (isset($new_data['alt_file_name']) && !empty($new_data['alt_file_name'])) {
                                    $new_data['file_name'] = $new_data['alt_file_name'];
                                    $change_filename = true;
                                }
                                break;
                            }
                        }
                    }
                }
                //var_dump($new_data);die;
                if (!$exist){
                    $result = $this->imageGenerator(is_array($insert_pk)?array_merge($new_data, $insert_pk):$new_data);
                    if (is_array($result)){
                        unset($new_data['file_name']);
                        $new_data = array_merge($result, $new_data);

                    } else {
                        unset($new_data['orig_file_name']);
                    }
                } else {
                    $new_data['orig_file_name'] = $this->data['current_image_data']['orig_file_name'];
                    $new_data['hash_file_name'] = $this->data['current_image_data']['hash_file_name'];
                    $this->imageRenameFolder();

                    if ($change_filename){
                        $this->renameImagesNames($new_data);
                    } else {
                        $new_data['file_name'] = $this->data['current_image_data']['file_name'];
                    }

                }

                if (( !isset($new_data['orig_file_name']) || !isset($new_data['hash_file_name'])) && !isset($_products_lookup[$file_primary_value]['data']['image_error']) && $source_data['params'][0] == 0){
                    $message->info('Main Image for product '. $_result['key']. ' "'.$file_primary_value.'" not found.');
                    $_products_lookup[$file_primary_value]['data']['image_error'] = true;
                    //continue;
                }

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
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_images_id']));
                    }
                }
            }
        }
        $this->_processed_pids[intval($this->data['products_id'])] = intval($this->data['products_id']);
    }

    public function postProcess(Messages $message)
    {
        $defaults = tep_db_query("select products_id, sum(default_image) as sd from " . TABLE_PRODUCTS_IMAGES . " group by products_id, default_image having sd > 1");
        if (tep_db_num_rows($defaults)){
            while($row = tep_db_fetch_array($defaults)){
                tep_db_query("UPDATE " . TABLE_PRODUCTS_IMAGES . " set default_image = 0 WHERE `products_id` = '" . (int)$row['products_id']. "'");
                $pid = tep_db_fetch_array(tep_db_query("select pi2.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi2 where pi2.products_id = '" . (int)$row['products_id']. "' and pi2.sort_order = (select min(pi3.sort_order) from " . TABLE_PRODUCTS_IMAGES . " pi3 where pi3.products_id='" . (int)$row['products_id']. "') group by pi2.sort_order"));
                tep_db_query("UPDATE " . TABLE_PRODUCTS_IMAGES . " pi set pi.default_image = 1 WHERE pi.products_images_id = '" . (int)$pid['products_images_id']. "' and pi.products_id = '" . (int)$row['products_id']. "'");
            }

        }

        $empties = tep_db_query("SELECT products_images_id, length(orig_file_name) as l FROM " . TABLE_PRODUCTS_IMAGES_DESCRIPTION. " WHERE 1 group by products_images_id having l = 0");
        if (tep_db_num_rows($empties)){
            while($row = tep_db_fetch_array($empties)){
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " WHERE `products_images_id` = '" . (int)$row['products_images_id']. "'");
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " WHERE `products_images_id` = '" . (int)$row['products_images_id']. "'");
            }

        }

        $message->info('Processed '.$this->entry_counter.' products');

        $message->info('Done');

        $this->EPtools->done('images_import');
    }


    public function renameImagesNames($new_data){
        $Images = new \common\classes\Images();
        $check_product_query = tep_db_query("SELECT products_id, products_seo_page_name FROM " . TABLE_PRODUCTS . " WHERE  products_id = {$this->data['products_id']}");
        if (tep_db_num_rows($check_product_query) > 0) {
            $product = tep_db_fetch_array($check_product_query);
            $image_location = \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR . $this->data['products_images_id'] . DIRECTORY_SEPARATOR;

            $lang = '';
            $Images->createImages($product['products_id'], $this->data['products_images_id'], $new_data['hash_file_name'], $new_data['alt_file_name'], $lang);//$orig_file
        }
    }

    public function imageRenameFolder(){

        $check_product_query = tep_db_query("SELECT products_id, products_seo_page_name FROM " . TABLE_PRODUCTS . " WHERE  products_id = {$this->data['products_id']}");
        if (tep_db_num_rows($check_product_query) > 0) {
            $product = tep_db_fetch_array($check_product_query);
            $image_location = \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
            if (is_dir($image_location . $this->data['current_image_data']['products_images_id'])){
                @rename ($image_location . $this->data['current_image_data']['products_images_id'], $image_location . $this->data['products_images_id']);
            }
        }
    }

    public function imageGenerator($new_data){
        $Images = new \common\classes\Images();

        $path = \common\classes\Images::getFSCatalogImagesPath();
        $path_import = \common\classes\Images::getFSCatalogImagesPath(). 'import/';
        if ( $this->import_folder && is_dir($this->import_folder) ) {
            $path_import = $this->import_folder;
        }
        //TRUNCATE TABLE `products_images`
        //TRUNCATE TABLE `products_images_description`
        $imageId = $this->data['products_images_id'];
        $for_language_id = isset($new_data['language_id'])?(int)$new_data['language_id']:0;

        $sql_data_array = [];

        $check_product_query = tep_db_query(
            "SELECT p.products_id, IF(LENGTH(pdi.products_seo_page_name)>0, pdi.products_seo_page_name, pd.products_seo_page_name) AS products_seo_page_name, ".
            " pid.alt_file_name ".
            "FROM " . TABLE_PRODUCTS . " p ".
            " LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id=p.products_id AND pd.language_id='".$this->languages_id."' AND pd.affiliate_id=0 ".
            " LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pdi ON pdi.products_id=p.products_id AND pdi.language_id='".(empty($for_language_id)?$this->languages_id:$for_language_id)."' AND pdi.affiliate_id=0 ".
            " LEFT JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pi.products_id=p.products_id AND pi.products_images_id='".(empty($imageId)?'-1':(int)$imageId)."' ".
            " LEFT JOIN ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pid ON pi.products_images_id=pid.products_images_id AND pid.language_id='".$for_language_id."' ".
            "WHERE p.products_id = {$this->data['products_id']} ".
            "LIMIT 1"
        );
        if (tep_db_num_rows($check_product_query) > 0) {
            $product = tep_db_fetch_array($check_product_query);

            $orig_file = $new_data['orig_file_name'];
            $tmp_name = $path_import . $orig_file;

            $do = false;

            if (!empty($orig_file) && file_exists($tmp_name)) {
                $do = true;
            }
            if (!$do){
                $tmp_name = $path . $orig_file;

                if (!empty($orig_file) && file_exists($tmp_name)) {
                    $do = true;
                }
            }

            if($do){
                $image_location = \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                if (!file_exists($image_location)) {
                    mkdir($image_location, 0777, true);
                    @chmod($image_location,0777);
                }

                $image_location .=  $imageId . DIRECTORY_SEPARATOR;
                if (!file_exists($image_location)) {
                    mkdir($image_location, 0777, true);
                    @chmod($image_location,0777);
                }

                if ( !array_key_exists('alt_file_name', $new_data) ) {
                    $new_data['alt_file_name'] = (string)$product['alt_file_name'];
                }
                if (isset($new_data['alt_file_name']) && tep_not_null($new_data['alt_file_name'])){
                    $file_name = $new_data['alt_file_name'];
                } elseif ( !empty($product['products_seo_page_name']) ) {
                    $file_name = $product['products_seo_page_name'];
                }else{
                    $file_name = pathinfo($orig_file, PATHINFO_FILENAME);
                }

                $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                $file_name .= '.' . $uploadExtension;
                $sql_data_array['file_name'] = $file_name;

                $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                $new_name = $image_location . $hashName;

                copy( $tmp_name, $new_name );
                $sql_data_array['hash_file_name'] = $hashName;

                $sql_data_array['orig_file_name'] = basename($orig_file);

                //$product_name = \common\helpers\Product::get_products_name($product['products_id']);

                $lang = '';
                $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang);//$orig_file
            } else {
                return false;
            }
        }
        return $sql_data_array;
    }

}