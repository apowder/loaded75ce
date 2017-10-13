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


use common\api\models\AR\Categories\AssignedPlatforms;
use common\api\models\AR\Categories\Description;
use yii\db\Expression;

class Categories extends EPMap
{

    protected $hideFields = [
        'previous_status',
        'last_xml_import',
        'last_xml_export',
        //'categories_level',
        'categories_left',
        'categories_right',
    ];

    protected $childCollections = [
        'descriptions' => [],
        'assigned_platforms' => false,
    ];

    protected $indexedCollections = [
        'assigned_platforms' => 'common\api\models\AR\Categories\AssignedPlatforms',
    ];

    private $changedName = false;

    public static function tableName()
    {
        return TABLE_CATEGORIES;
    }

    public static function primaryKey()
    {
        return ['categories_id'];
    }

    public function initCollectionByLookupKey_Descriptions($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['descriptions'][$keyCode] = null;
            if ( is_null($this->categories_id) ) {
                $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['descriptions'][$keyCode])) {
                    $lookupPK['categories_id'] = $this->categories_id;
                    $this->childCollections['descriptions'][$keyCode] = Description::findOne($lookupPK);
                    if (!is_object($this->childCollections['descriptions'][$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['descriptions'];
    }

    public function initCollectionByLookupKey_AssignedPlatforms($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_platforms']) ) {
            $this->childCollections['assigned_platforms'] = [];
            if ($this->categories_id) {
                $this->childCollections['assigned_platforms'] =
                    AssignedPlatforms::find()
                        ->where(['categories_id' => $this->categories_id])
                        ->orderBy(['platform_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_platforms'];
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }else{
            $this->last_modified = new Expression("NOW()");
        }

        $this->changedName = false;
        $defaultKey = \common\classes\language::get_code(\common\classes\language::defaultId()).'_0';
        if( is_array($this->childCollections['descriptions']) && isset($this->childCollections['descriptions'][$defaultKey]) && is_object($this->childCollections['descriptions'][$defaultKey]) ){
            $defaultDescription = $this->childCollections['descriptions'][$defaultKey];
            /**
             * @var EPMap $defaultDescription
             */
            if (  $defaultDescription->isAttributeChanged('categories_name',false) ) {
                $this->changedName = true;
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( array_key_exists('sort_order', $changedAttributes) || $this->changedName ) {
            \common\helpers\Categories::update_categories();
        }
    }


}