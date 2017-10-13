<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\AR\Products;

use backend\models\EP\Tools;
use common\api\models\AR\EPMap;
use common\api\models\AR\Products\Properties\Name;

class Properties extends EPMap
{

    protected $hideFields = [
        'products_id',
        'properties_id',
        'values_id',
    ];

    public static function tableName()
    {
        return TABLE_PROPERTIES_TO_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id', 'properties_id', 'values_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;

        parent::parentEPMap($parentObject);
    }

    public function importArray($data)
    {
        //$data['names'];
        if ( isset($data['name_path']) ) {
            $lookupId = 0;
            foreach ( $data['name_path'] as $langCode=>$prop_name_path ) {
                $prop_names = explode(';', $prop_name_path);
                $langId = $langCode=='*'?0:\common\classes\language::get_id($langCode);
                $countNames = count($prop_names);
                foreach( $prop_names as $__idx=>$prop_name ) {
                    $parentId = $lookupId;
                    $lookupId = $this->lookupPropertiesByName($prop_name, $parentId, $langId);
                    if ( $lookupId==0 ) {
                        $lookupId = $this->createProperties(($__idx+1==$countNames)?'text':'category', $prop_name, $parentId, $langId);
                    }
                }
                break;
            }
            $data['properties_id'] = $lookupId;
        }

        if ( isset($data['values']) && !empty($data['properties_id']) ) {
            $lookupId = 0;
            foreach ( $data['values'] as $langCode=>$prop_value ) {
                $langId = $langCode=='*'?0:\common\classes\language::get_id($langCode);

                $get_value_id_r = tep_db_query(
                    "SELECT values_id ".
                    "FROM ".TABLE_PROPERTIES_VALUES." ".
                    "WHERE properties_id='".$data['properties_id']."' AND values_text='".tep_db_input($prop_value)."' ".
                    "LIMIT 1 "
                );
                if (tep_db_num_rows($get_value_id_r)>0){
                    $_value_id = tep_db_fetch_array($get_value_id_r);
                    $data['values_id'] = $_value_id['values_id'];
                }else{
                    $max_value = tep_db_fetch_array(tep_db_query("SELECT MAX(values_id) AS current_max_id FROM " . TABLE_PROPERTIES_VALUES));
                    $values_id = intval($max_value['current_max_id'])+1;

                    tep_db_query(
                        "INSERT INTO ".TABLE_PROPERTIES_VALUES." (values_id, properties_id, language_id, values_text ) ".
                        "SELECT '{$values_id}', '".$data['properties_id']."', languages_id, '".tep_db_input($prop_value)."' FROM ".TABLE_LANGUAGES." "
                    );
                    $data['values_id'] = $values_id;
                }
                break;
            }
        }
/*        values_id
        properties_id
        language_id
        values_text*/

        if ( empty($data['properties_id']) || empty($data['values_id']) ) {
            return false;
        }

        return parent::importArray($data);
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if ( count($fields)==0 || in_array('name',$fields) ) {
            $propNames = $this->getPropertiesNameArr($this->properties_id);
            $data['names'] = $propNames['names'];
            $data['name_path'] = $propNames['names'];
            $parent_id = $propNames['parent_id'];
            while( $parent_id>0 ) {
                $propNames = $this->getPropertiesNameArr($parent_id);
                foreach ( $data['name_path'] as $langCode=>$savedPath ) {
                    $data['name_path'][$langCode] = (isset($propNames['names'][$langCode])?$propNames['names'][$langCode]:'').';'.$savedPath;
                }
                $parent_id = $propNames['parent_id'];
            }
        }
        if ( count($fields)==0 || in_array('values',$fields) ) {
            $data['values'] = [];
            $get_data_r = tep_db_query(
                "SELECT pv.language_id, pv.values_text ".
                "FROM ".TABLE_PROPERTIES_VALUES." pv ".
                "WHERE pv.values_id='".$this->values_id."' ".
                " AND pv.properties_id='".$this->properties_id."' "
            );
            if ( tep_db_num_rows($get_data_r)>0 ) {
                while( $_data = tep_db_fetch_array($get_data_r) ) {
                    $data['values'][ \common\classes\language::get_code($_data['language_id']) ] = $_data['values_text'];
                }
            }
        }

        return $data;
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( intval($importedObject->properties_id)==intval($this->properties_id) && intval($importedObject->values_id)==intval($this->values_id) ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;

    }


    protected function getPropertiesNameArr($id)
    {
        $result = [
            'parent_id' => 0,
            'names' => [],
        ];

        $get_data_r = tep_db_query(
            "SELECT p.parent_id, pd.language_id, pd.properties_name ".
            "FROM ".TABLE_PROPERTIES." p, ".TABLE_PROPERTIES_DESCRIPTION ." pd ".
            "WHERE p.properties_id=pd.properties_id ".
            " AND p.properties_id='".$id."' "
        );
        if ( tep_db_num_rows($get_data_r)>0 ) {
            while( $_data = tep_db_fetch_array($get_data_r) ) {
                $result['parent_id'] = $_data['parent_id'];
                $result['names'][ \common\classes\language::get_code($_data['language_id']) ] = $_data['properties_name'];
            }
        }

        return $result;
    }

    protected function lookupPropertiesByName($prop_name, $parentId, $langId)
    {
        static $lookups = [];
        $key = (int)$parentId.'^'.(int)$langId.'^'.$prop_name;
        if ( isset($lookups[$key]) ) return $lookups[$key];

        $propId = 0;
        $get_data_r = tep_db_query(
            "SELECT p.properties_id ".
            "FROM ".TABLE_PROPERTIES." p, ".TABLE_PROPERTIES_DESCRIPTION ." pd ".
            "WHERE p.properties_id=pd.properties_id ".
            " AND pd.properties_name = '".tep_db_input($prop_name)."' ".
            " AND p.parent_id='".$parentId."' ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($get_data_r)>0 ) {
            $get_data = tep_db_fetch_array($get_data_r);
            $propId = $get_data['properties_id'];
            $lookups[$key] = $propId;
        }
        return $propId;
    }

    protected function createProperties($type, $prop_name, $parentId, $langId)
    {
        tep_db_perform(TABLE_PROPERTIES, [
            'parent_id' => $parentId,
            'properties_type' => $type,
            'date_added' => 'now()',
        ]);
        $propId = tep_db_insert_id();

        tep_db_query(
            "INSERT INTO ".TABLE_PROPERTIES_DESCRIPTION." (properties_id, language_id, properties_name) ".
            "SELECT '".(int)$propId."', languages_id, '".tep_db_input($prop_name)."' FROM ".TABLE_LANGUAGES." "
        );

        return $propId;
    }

}