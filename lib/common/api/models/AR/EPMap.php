<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\AR;

use yii\db\ActiveRecord;
use yii\helpers\Inflector;

class EPMap extends ActiveRecord
{
    protected $hideFields = [];
    public $pendingRemoval = false;

    protected $childCollections = [];
    protected $indexedCollections = [];

    protected $loadedCollections = [];

    public function __construct(array $config = [])
    {
        $initArray = [];
        if ( count($config)>0 ) {
            $fields = array_flip($this->attributes());
            foreach( array_keys($config) as $field){
                if ( isset($fields[$field]) ) {
                    $initArray[$field] = $config[$field];
                }
            }
        }

        parent::__construct($initArray);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        if ( count($this->hideFields)>0 ) {
            $fields = array_merge($this->attributes(),$this->customFields());
            $fields = array_diff($fields,$this->hideFields);
        }
        return parent::toArray($fields, $expand, $recursive);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        foreach ($this->childCollections as $collectionName=>$collection) {
            if ( !is_array($collection) ) continue;
            if ( $insert ) {
                //$newPrimaryValues = $this->getPrimaryKey(true);
                foreach( $collection as $collectionItem ) {
                    /**
                     * @var EPMap $collectionItem
                     */
                    $collectionItem->parentEPMap($this);
                    /*foreach ( $newPrimaryValues as $key=>$val ) {
                        if ( $collectionItem->hasAttribute($key) ) $collectionItem->setAttribute($key, $val);
                    }*/
                    if ( $collectionItem->pendingRemoval ) continue;
                    $collectionItem->insert();
                }
            }else{
                foreach( $collection as $idx=>$collectionItem ) {
                    $collectionItem->parentEPMap($this);
                    if ( $collectionItem->pendingRemoval ) {
                        $collectionItem->delete();
                        unset($collection[$idx]);
                    }else {
                        if ( $collectionItem->isNewRecord ) {
                            $collectionItem->insert();
                        }else {
                            $collectionItem->update();
                        }
                    }
                }
            }
        }
    }

    public function exportArray(array $fields = [])
    {
        $data = $this->toArray($fields, [], false);
        if ( count($fields)==0 ) {
            foreach( array_keys($this->childCollections) as $childKey ) {
                $fields[$childKey]['*'] = [];
            }
        }

        foreach( array_keys($this->childCollections) as $collectionName ) {
            if ( !isset($fields[$collectionName]) ) continue;
            $childFields = $fields[$collectionName];

            $filterChild = isset($childFields['*'])?$childFields['*']:[];

            $methodName = 'initCollectionByLookupKey_'.Inflector::id2camel($collectionName,'_');
            if ( method_exists($this, $methodName) ) {
                call_user_func_array([$this, $methodName],[array_keys($childFields)]);
            }
            if ( is_array($this->childCollections[$collectionName]) ){
                $data[$collectionName] = [];
            }
            foreach($this->childCollections[$collectionName] as $exportKey=>$childAR){
                $filterExportChild = $filterChild;
                if ( isset($childFields[$exportKey]) ) {
                    $filterExportChild = array_merge($filterChild, $childFields[$exportKey]);
                }elseif ( !isset($childFields['*']) ) {
                    continue;
                }
                if ( in_array('*',$filterExportChild) ) $filterExportChild = [];
                $data[$collectionName][$exportKey] = $childAR->exportArray($filterExportChild);
            }
        }

        return $data;
    }

    public function importArray($data)
    {
        foreach( $data as $key=>$value ) {
            if ( $this->hasAttribute($key) ){
                $this->setAttribute($key, $value);
            }elseif ( isset($this->childCollections[$key]) ) {
                $methodName = 'initCollectionByLookupKey_'.Inflector::id2camel($key,'_');
                if ( method_exists($this, $methodName) ) {
                    call_user_func_array([$this, $methodName],[['*']]);
                }

                if ( isset($this->indexedCollections[$key]) ) {
                    foreach ($this->childCollections[$key] as $currentIdx => $childAR) {
                        $childAR->pendingRemoval = true;
                    }
                    foreach ( $value as $indexedValue ) {
                        $instance = \Yii::createObject($this->indexedCollections[$key]);
                        $instance->parentEPMap($this);
                        if ( !$instance->importArray($indexedValue) ) continue;

                        $matchCurrentAR = false;
                        $matchedIdxList = [];
                        foreach ($this->childCollections[$key] as $currentIdx => $childAR) {
                            if ( isset($matchedIdxList[$currentIdx]) ) continue;
                            /**
                             * @var EPMap $childAR
                             */
                            if ( $childAR->matchIndexedValue($instance) ) {
                                $matchedIdxList[$currentIdx] = $currentIdx;
//                                echo '<pre>'; var_dump($indexedValue); echo '</pre>';
                                // {{
                                $childAR->pendingRemoval = false;
                                $childAR->importArray($indexedValue);
                                // }}
                                $matchCurrentAR = true;
                                break;
                            }
                        }
                        if ( !$matchCurrentAR ) {
                            $this->childCollections[$key][] = $instance;
                        }
                    }
                } else {
                    $importDataArray = isset($value['*'])?$value['*']:[];
                    foreach ($this->childCollections[$key] as $importKey => $childAR) {
                        if (isset($value[$importKey]) && is_array($value[$importKey])) {
                            if ( count($importDataArray)>0 ) {
                                $childAR->importArray(array_replace_recursive($importDataArray, $value[$importKey]));
                            }else{
                                $childAR->importArray($value[$importKey]);
                            }
                        }elseif(count($importDataArray)>0){
                            $childAR->importArray($importDataArray);
                        }
                    }
                }
            }
        }
        return true;
    }

    public function customFields()
    {
        return [];
    }

    public function parentEPMap(EPMap $parentObject)
    {

    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        return false;
    }

}