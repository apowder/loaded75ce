<?php

namespace common\api\models\AR;

use common\api\models\AR\Products\AssignedCategories;
use common\api\models\AR\Products\Attributes;
use common\api\models\AR\Products\Description;
use common\api\models\AR\Products\Documents;
use common\api\models\AR\Products\Images;
use common\api\models\AR\Products\Inventory;
use common\api\models\AR\Products\Prices;
use common\api\models\AR\Products\Properties;
use common\api\models\AR\Products\Xsell;
use yii\db\Expression;

class Products extends EPMap
{

    protected $hideFields = [
        'products_image',
        'products_image_med',
        'products_image_lrg',
        'products_image_sm_1',
        'products_image_xl_1',
        'products_image_sm_2',
        'products_image_xl_2',
        'products_image_sm_3',
        'products_image_xl_3',
        'products_image_sm_4',
        'products_image_xl_4',
        'products_image_sm_5',
        'products_image_xl_5',
        'products_image_sm_6',
        'products_image_xl_6',
        'products_image_sm_7',
        'products_image_xl_7',
        'products_image_alt_1',
        'products_image_alt_2',
        'products_image_alt_3',
        'products_image_alt_4',
        'products_image_alt_5',
        'products_image_alt_6',
        //'products_date_added',
        //'products_last_modified',
        'products_seo_page_name',
        'last_xml_import',
        'last_xml_export',
        'previous_status',
        'vendor_id',
    ];

    protected $childCollections = [
        'descriptions' => [],
        'prices' => [],
        'assigned_categories' => false,
        'attributes' => false,
        'inventory' => false,
        'images' => false,
        'properties' => false,
        'xsell' => false,
        'documents' => false,
    ];

    protected $indexedCollections = [
        'assigned_categories' => 'common\api\models\AR\Products\AssignedCategories',
        'attributes' => 'common\api\models\AR\Products\Attributes',
        'inventory' => 'common\api\models\AR\Products\Inventory',
        'images' => 'common\api\models\AR\Products\Images',
        'properties' => 'common\api\models\AR\Products\Properties',
        'xsell' => 'common\api\models\AR\Products\Xsell',
        'documents' => 'common\api\models\AR\Products\Documents',
    ];

    public static function tableName()
    {
        return TABLE_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id'];
    }

    public function initCollectionByLookupKey_Descriptions($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['descriptions'][$keyCode] = null;
            if ( is_null($this->products_id) ) {
                $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['descriptions'][$keyCode])) {
                    $lookupPK['products_id'] = $this->products_id;
                    $this->childCollections['descriptions'][$keyCode] = Description::findOne($lookupPK);
                    if (!is_object($this->childCollections['descriptions'][$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['descriptions'];
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['prices'][$keyCode] = null;
            if ( is_null($this->products_id) ) {
                $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['prices'][$keyCode])) {
                    $lookupPK['products_id'] = $this->products_id;
                    $this->childCollections['prices'][$keyCode] = Prices::findOne($lookupPK);
                    if (!is_object($this->childCollections['prices'][$keyCode])) {
                        $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }

    public function initCollectionByLookupKey_AssignedCategories($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_categories']) ) {
            $this->childCollections['assigned_categories'] = [];
            if ($this->products_id) {
                $this->childCollections['assigned_categories'] =
                    AssignedCategories::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC, 'categories_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_categories'];
    }

    public function initCollectionByLookupKey_Attributes($lookupKeys)
    {
        if ( !is_array($this->childCollections['attributes']) ) {
            $this->childCollections['attributes'] = [];
            if ($this->products_id) {
                $this->childCollections['attributes'] =
                    Attributes::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['options_id' => SORT_ASC, 'options_values_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['attributes'];
    }

    public function getAssignedAttributeIds()
    {
        if ( !is_array($this->childCollections['attributes']) ) {
            $this->initCollectionByLookupKey_Attributes([]);
        }
        $ids = [];
        foreach ( $this->childCollections['attributes'] as $attrAR ){
            if ( $attrAR->pendingRemoval ) continue;
            if ( !is_array($ids[$attrAR->options_id]) ) $ids[$attrAR->options_id] = [];
            $ids[$attrAR->options_id][] = $attrAR->options_values_id;
        }
        return $ids;
    }

    public function initCollectionByLookupKey_Inventory($lookupKeys)
    {
        if ( !is_array($this->childCollections['inventory']) ) {
            $this->childCollections['inventory'] = [];
            if ($this->products_id) {
                $this->childCollections['inventory'] =
                    Inventory::find()
                        ->where(['prid' => $this->products_id])
                        ->orderBy(['products_id' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['inventory'];
    }

    public function initCollectionByLookupKey_Images($lookupKeys)
    {
        if ( !is_array($this->childCollections['images']) ) {
            $this->childCollections['images'] = [];
            if ($this->products_id) {
                $this->childCollections['images'] =
                    Images::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['images'];
    }

    public function initCollectionByLookupKey_Properties($lookupKeys)
    {
        if ( !is_array($this->childCollections['properties']) ) {
            $this->childCollections['properties'] = [];
            if ($this->products_id) {
                $this->childCollections['properties'] =
                    Properties::find()
                        ->where(['products_id' => $this->products_id])
                        //->orderBy(['sort_order' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['properties'];
    }

    public function initCollectionByLookupKey_Xsell($lookupKeys)
    {
        if ( !is_array($this->childCollections['xsell']) ) {
            $this->childCollections['xsell'] = [];
            if ($this->products_id) {
                $this->childCollections['xsell'] =
                    Xsell::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['xsell'];
    }

    public function initCollectionByLookupKey_Documents($lookupKeys)
    {
        if ( !is_array($this->childCollections['documents']) ) {
            $this->childCollections['documents'] = [];
            if ($this->products_id) {
                $this->childCollections['documents'] =
                    Documents::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['documents'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(Description::className(), ['products_id'=>'products_id']);
    }

    /*public function extraFields()
    {
        return ['descriptions'=>'descriptions'];
    }*/

    public function exportArray(array $fields = [])
    {
        $tools = new \backend\models\EP\Tools();
        $export = parent::exportArray($fields);
        if ( array_key_exists('stock_delivery_terms_id', $export) || in_array('stock_delivery_terms_text',$fields) ){
            $export['stock_delivery_terms_text'] = $tools->getStockDeliveryTerms($this->stock_delivery_terms_id);
        }
        if ( array_key_exists('stock_indication_id', $export) || in_array('stock_indication_text',$fields) ){
            $export['stock_indication_text'] = $tools->getStockIndication($this->stock_indication_id);
        }
        if ( array_key_exists('manufacturers_id', $export) || in_array('manufacturers_name',$fields) ) {
            $export['manufacturers_name'] = \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $this->manufacturers_id);
        }
        return $export;
    }

    public function importArray($data)
    {
        $tools = new \backend\models\EP\Tools();
        if ( array_key_exists('stock_delivery_terms_text', $data) ){
            $data['stock_delivery_terms_id'] = $tools->lookupStockDeliveryTermId($data['stock_delivery_terms_text']);
        }
        if ( array_key_exists('stock_indication_text', $data) ){
            $data['stock_indication_id'] = $tools->lookupStockIndicationId($data['stock_indication_text']);
        }
        if ( array_key_exists('manufacturers_name', $data) ) {
            $data['manufacturers_id'] = $tools->get_brand_by_name($data['manufacturers_name']);
            if ( $data['manufacturers_id']==='null' ) $data['manufacturers_id'] = null;
        }

        $importResult = parent::importArray($data);

        if ( array_key_exists('attributes', $data) ) {
            $this->checkInventory();
        }

        return $importResult;
    }


    public function checkInventory()
    {
        $attr = $this->getAssignedAttributeIds();
        $options = $attr;
        ksort($options);
        reset($options);
        $i = 0;
        $idx = 0;
        foreach ($options as $key => $value) {
            if ($i == 0) {
                $idx = $key;
                $i = 1;
            }
            asort($options[$key]);
        }
        $inventory_options = \common\helpers\Inventory::get_inventory_uprid($options, $idx);
//echo '<pre>'; var_dump($inventory_options); echo '</pre>';
//echo '<pre>'; var_dump(count($this->childCollections['inventory'])); echo '</pre>';
        if ( !is_array($this->childCollections['inventory']) ) {
            $this->initCollectionByLookupKey_Inventory([]);
        }

        foreach ( $this->childCollections['inventory'] as $idx=>$inventoryObj ) {
            $partialUprid = preg_replace('/^\d+/','',$inventoryObj->products_id);
//            echo '<pre>'; var_dump($partialUprid); echo '</pre>';

            $haveValidIdx = array_search($partialUprid,$inventory_options);
//            echo '<pre>$haveValidIdx '; var_dump($haveValidIdx); echo '</pre>';
            if ( $haveValidIdx!==false ) {
                // valid inventory uprid
                unset($inventory_options[$haveValidIdx]);
                $inventoryObj->pendingRemoval = false;
            }else{
                $inventoryObj->pendingRemoval = true;
            }
//            echo '<pre>'; var_dump($inventoryObj->pendingRemoval); echo '</pre>';
        }
        // not checked need add
//echo '<pre>'; var_dump(count($this->childCollections['inventory'])); echo '</pre>';
//echo '<pre>'; var_dump($inventory_options); echo '</pre>'; die;
        foreach ($inventory_options as $partialUprid) {

            $newInventory = new Inventory();
            $newInventory->products_id = strval($this->products_id).$partialUprid;
            $newInventory->fillOptionValueList();
            $newInventory->parentEPMap($this);

            $this->childCollections['inventory'][] = $newInventory;
        }
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( empty($this->products_date_added) ) {
                $this->products_date_added = new Expression("NOW()");
            }
        }else{
            //$this->products_last_modified = new Expression("NOW()");
        }
        return parent::beforeSave($insert);
    }


}