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

abstract class ProviderAbstract {
    public $languages_id = 0;
    
    protected $fields = array();

    protected $export_columns;
    protected $main_source;
    protected $data_sources;
    protected $pre_lookup;
    protected $file_primary_column;
    protected $sourcesForKey = '';

    protected $wColumns = [];

    function __construct() {
        $this->languages_id = \common\classes\language::defaultId();
        if ( isset($_SESSION) && !empty($_SESSION['languages_id']) ) {
            $this->languages_id = $_SESSION['languages_id'];
        }
    }

    public function setColumns($columns)
    {
        $this->wColumns = $columns;
    }

    public function customConfig()
    {

    }
    
    protected function buildSources($useColumns)
    {
        $in_columns = md5(implode('|',$useColumns));
        if ( $this->sourcesForKey==$in_columns ) return false;

        // {{ prepare column data
        $export_columns = array();
        $main_source = array(
            'select' => '',
            'columns' => array(),
        );
        $data_sources = array();
        $file_primary_column = '';
        $pre_lookup = array();
        foreach ($this->fields as $_field) {
            if (isset($_field['is_key']) && $_field['is_key'] === true) {
                $file_primary_column = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
            }
            if ( !in_array($_field['name'], $useColumns) ) continue;
            // skip not configured here
//      if ( is_array($selected_fields) && !in_array($_field['name'],$selected_fields) ) {
//        continue;
//      }
            $selectPrefix = (isset($_field['prefix']) && !empty($_field['prefix']))?($_field['prefix'].'.'):'';
            if (isset($_field['data_descriptor'])) {
                if ( isset($_field['pre_lookup']) ) {
                    if ( !isset($pre_lookup[$_field['pre_lookup']]) ) $pre_lookup[$_field['pre_lookup']] = array();
                    $pre_lookup[$_field['pre_lookup']][] = $_field;
                }
                if (!isset($data_sources[$_field['data_descriptor']])) {
                    $data_descriptor = explode('|', $_field['data_descriptor']);
                    $data_sources[$_field['data_descriptor']] = array(
                        'select' => '',
                        'columns' => array(),
                        'table' => $data_descriptor[0] == '%' ? $data_descriptor[1] : false,
                        'init_function' => $data_descriptor[0] == '@' ? $data_descriptor[1] : false,
                        'params' => array_slice($data_descriptor, 2),
                    );
                }
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else {
                    $data_sources[$_field['data_descriptor']]['select'] .= (isset($_field['column_db']) ? "{$selectPrefix}{$_field['column_db']} AS {$_field['name']}" : "{$selectPrefix}{$_field['name']}") . ", ";
                    $data_sources[$_field['data_descriptor']]['columns'][$_field['name']] = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
                }
            } else {
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else{
                    $main_source['select'] .= (isset($_field['column_db']) ? "{$selectPrefix}{$_field['column_db']} AS {$_field['name']}" : "{$selectPrefix}{$_field['name']}") . ", ";
                    $main_source['columns'][$_field['name']] = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
                }
            }
            $export_columns[$_field['name']] = $_field;

        }
        // }}

        $this->export_columns = $export_columns;
        $this->main_source = $main_source;
        $this->data_sources = $data_sources;
        $this->pre_lookup = $pre_lookup;
        $this->file_primary_column = $file_primary_column;
        $this->sourcesForKey = $in_columns;

        return true;
    }

    function export(Formatter\FormatterInterface $output, $selected_fields, $filter){

    }

    function import(Formatter\FormatterInterface $input, EP\Messages $message)
    {

    }

    /**
     * @deprecated
     *
     * @param Formatter\FormatterInterface $input
     * @param array $file_header_line
     * @return float|int
     */
    function isColumnsMatch(Formatter\FormatterInterface $input, array $file_header_line)
    {
        $file_header_line = $input->getHeaders();

        return $this->getColumnMatchScore($file_header_line);
    }

    function getColumnMatchScore($inputColumns)
    {
        $columns = $this->getColumns();

        $score = 0;
        foreach( $inputColumns as $field ) {
            if ( in_array($field, $columns) ) {
                $score++;
            }
        }

        if ( count($inputColumns)==0 ) return 0;
        return $score/count($inputColumns);
    }

    function getColumns()
    {
        $columns = array();
        foreach( $this->fields as $field ) {
            $columns[$field['name']] = $field['value'];
        }
        return $columns;
    }

    function setColumnRemap($remap)
    {
        foreach( $this->fields as $idx=>$field ) {
            if ( isset($remap[$field['name']]) ) {
                $this->fields[$idx]['value'] = $remap[$field['name']];
            }
        }
    }

}