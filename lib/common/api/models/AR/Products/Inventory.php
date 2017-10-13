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
use common\api\models\AR\Products;
use common\api\models\AR\Products\Inventory\Prices;

class Inventory extends EPMap
{

    protected $hideFields = [
        'inventory_id',
        //'products_id',
        //'prid',
    ];

    protected $childCollections = [
        'prices' => [],
    ];

    protected $optionValuesList = [

    ];

    /**
     * @var Products
     */
    protected $parentObject;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return TABLE_INVENTORY;
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['inventory_id'];
    }

    public function fillOptionValueList()
    {
        $this->optionValuesList = [];
        if ( preg_match_all('/{(\d+)}(\d+)/', $this->products_id, $optValMatch) ){
            foreach( $optValMatch[1] as $_idx=>$optId ) {
                $valId = $optValMatch[2][$_idx];
                $int_key = $optId.'-'.$valId;
                $this->optionValuesList[$int_key] = [
                    'options_id' => $optId,
                    'options_values_id' => $valId,
                ];
            }
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->fillOptionValueList();
    }


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
        $export['attribute_map'] = array_values($this->optionValuesList);
        foreach ($export['attribute_map'] as $idx=>$optionValue) {
            $export['attribute_map'][$idx]['options_name'] = $tools->get_option_name($optionValue['options_id'], \common\classes\language::defaultId() );
            $export['attribute_map'][$idx]['options_values_name'] = $tools->get_option_value_name($optionValue['options_values_id'], \common\classes\language::defaultId() );
        }

        return $export;
    }

    public function importArray($data)
    {
        $validAttributes = false;
        if ( is_object($this->parentObject) ) {
            $validAttributes = $this->parentObject->getAssignedAttributeIds();
        }

        $tools = new \backend\models\EP\Tools();
        if ( array_key_exists('stock_delivery_terms_text', $data) ){
            $data['stock_delivery_terms_id'] = $tools->lookupStockDeliveryTermId($data['stock_delivery_terms_text']);
        }
        if ( array_key_exists('stock_indication_text', $data) ){
            $data['stock_indication_id'] = $tools->lookupStockIndicationId($data['stock_indication_text']);
        }
        if (isset($data['attribute_map']) && is_array($data['attribute_map']) ){
            foreach( $data['attribute_map'] as $idx=>$attrInfo ) {
                $data['attribute_map'][$idx]['options_id'] = $tools->get_option_by_name($attrInfo['options_name']);
                $data['attribute_map'][$idx]['options_values_id'] = $tools->get_option_value_by_name($data['attribute_map'][$idx]['options_id'], $attrInfo['options_values_name']);
            }
            $this->optionValuesList = [];
            foreach( $data['attribute_map'] as $idx=>$attrInfo ) {
                if ( is_array($validAttributes) && !isset($validAttributes[$attrInfo['options_id']]) ) return false;
                if ( is_array($validAttributes) && !in_array($attrInfo['options_values_id'],$validAttributes[$attrInfo['options_id']]) ) return false;

                $int_key = $attrInfo['options_id'].'-'.$attrInfo['options_values_id'];
                $this->optionValuesList[$int_key] = $attrInfo;
            }

            $this->regenerateFields(true);
        }

        if ( strpos((string)$this->products_id,'{')===false ) return false;

        $result = parent::importArray($data);

        $this->regenerateFields();

        return $result;
    }

    protected function regenerateFields($onlyUprid=false)
    {
        $attr = [];
        foreach($this->optionValuesList as $optValInfo){
            $attr[ $optValInfo['options_id'] ] = $optValInfo['options_values_id'];
        }
        ksort($attr);

        $this->products_id = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($this->parentObject->products_id, $attr));
        if ( !$onlyUprid ) {
            $tools = new Tools();
            $this->products_name = \common\helpers\Product::get_products_name($this->parentObject->products_id, \common\classes\language::defaultId());
            foreach ( $attr as $value_id ) {
                $this->products_name .= ' '.$tools->get_option_value_name($value_id, \common\classes\language::defaultId());
            }
        }
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->prid = $parentObject->products_id;

        $this->parentObject = $parentObject;
        $this->regenerateFields();
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {

        $matchedAttrKeys = array_intersect(array_keys($this->optionValuesList),array_keys($importedObject->optionValuesList));
        $objectMatch = count($matchedAttrKeys)==count($this->optionValuesList);

        if ( $objectMatch ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['prices'][$keyCode] = null;
            if ( is_null($this->inventory_id) ) {
                $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['prices'][$keyCode])) {
                    $lookupPK['inventory_id'] = $this->inventory_id;
                    $this->childCollections['prices'][$keyCode] = Prices::findOne($lookupPK);
                    if (!is_object($this->childCollections['prices'][$keyCode])) {
                        $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }

}