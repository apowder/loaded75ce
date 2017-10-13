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


use common\api\models\AR\EPMap;
use common\api\models\AR\Products\Documents\Title;

class Documents extends EPMap
{

    protected $hideFields = [
        'products_documents_id',
        'products_id',
    ];

    protected $childCollections = [
        'titles' => [],
    ];


    public static function tableName()
    {
        return TABLE_PRODUCTS_DOCUMENTS;
    }

    public static function primaryKey()
    {
        return ['products_documents_id'];
    }

    public function initCollectionByLookupKey_Titles($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Title::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['titles'][$keyCode] = null;
            if ( is_null($this->products_documents_id) ) {
                $this->childCollections['titles'][$keyCode] = new Title($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['titles'][$keyCode])) {
                    $lookupPK['products_documents_id'] = $this->products_documents_id;
                    $this->childCollections['titles'][$keyCode] = Title::findOne($lookupPK);
                    if (!is_object($this->childCollections['titles'][$keyCode])) {
                        $this->childCollections['titles'][$keyCode] = new Title($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['titles'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if (
            !is_null($importedObject->document_types_id) && !is_null($this->document_types_id) && $importedObject->document_types_id==$this->document_types_id
            &&
            !is_null($importedObject->filename) && !is_null($this->filename) && $importedObject->filename==$this->filename
        ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function importArray($data)
    {

        if (isset($data['document_types_name'])) {
            $tools = new \backend\models\EP\Tools();
            $data['document_types_id'] = $tools->get_document_types_by_name($data['document_types_name']);
        }

        return parent::importArray($data);
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);

        if (count($fields)==0 || in_array('document_types_name',$fields)) {
            $tools = new \backend\models\EP\Tools();
            $data['document_types_name'] = $tools->get_document_types_name($this->document_types_id, \common\classes\language::defaultId() );
        }

        $data['document_url'] = \Yii::$app->get('platform')->config()->getCatalogBaseUrl().'documents/' . $this->filename;

        return $data;
    }

}