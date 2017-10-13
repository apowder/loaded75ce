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
use common\api\models\AR\Products\Images\Description;

class Images extends EPMap
{
    protected $hideFields = [
        'products_images_id',
        'products_id',
    ];

    protected $childCollections = [
        'image_description' => [],
    ];

    /**
     * @var EPMap
     */
    protected $parentObject;

    public static function tableName()
    {
        return TABLE_PRODUCTS_IMAGES;
    }

    public static function primaryKey()
    {
        return ['products_images_id'];
    }

    public function initCollectionByLookupKey_ImageDescription($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
            if ( is_object($this->childCollections['image_description'][$keyCode]) ) continue;
            $this->childCollections['image_description'][$keyCode] = null;
            if ( is_null($this->products_images_id) ) {
                $this->childCollections['image_description'][$keyCode] = new Description($lookupPK);
                $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!is_object($this->childCollections['image_description'][$keyCode])) {
                    $lookupPK['products_images_id'] = $this->products_images_id;
                    $this->childCollections['image_description'][$keyCode] = Description::findOne($lookupPK);
                    if (!is_object($this->childCollections['image_description'][$keyCode])) {
                        $this->childCollections['image_description'][$keyCode] = new Description($lookupPK);
                    }
                    $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
                }
            }
        }
        return $this->childCollections['image_description'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->parentObject = $parentObject;
    }

    public function getImageHashes()
    {
        if ( count($this->childCollections['image_description'])==0 ) {
            $this->initCollectionByLookupKey_ImageDescription(['*']);
        }
        $hashes = [];
        foreach($this->childCollections['image_description'] as $key=>$imageDesc){
            $hashes[$key] = $imageDesc->hash_file_name;
        }
        return $hashes;
    }
    public function getImageCompareKeys()
    {
        if ( count($this->childCollections['image_description'])==0 ) {
            $this->initCollectionByLookupKey_ImageDescription(['*']);
        }
        $hashes = [];
        foreach($this->childCollections['image_description'] as $key=>$imageDesc){
            $hashes[$key] = [
                'hash' => $imageDesc->hash_file_name,
                'orig_name' => $imageDesc->orig_file_name,
            ];
        }
        return $hashes;
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $k1_match = 0;
        $k2_match = 0;
        $this_keys = $this->getImageCompareKeys();
        $imported_keys = $importedObject->getImageCompareKeys();
        foreach ( $this_keys as $key=>$compareValues ) {
            if ( !empty($compareValues['hash']) && isset($imported_keys[$key]['hash']) && $compareValues['hash']==$imported_keys[$key]['hash'] ) {
                $k1_match++;
            }
            if ( !empty($compareValues['orig_name']) && isset($imported_keys[$key]['orig_name']) && $compareValues['orig_name']==$imported_keys[$key]['orig_name'] ) {
                $k2_match++;
            }
        }
        if ( $k1_match>0 || $k2_match>0 ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;

        /*
        $match_images = 0;
        $this_hashes = $this->getImageHashes();
        $imported_hashes = $importedObject->getImageHashes();
        foreach ( $this_hashes as $key=>$hash ) {
            if ( !empty($hash) && isset($imported_hashes[$key]) && $hash==$imported_hashes[$key] ) {
                $match_images++;
            }
        }
        if ( $match_images>0 ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;
        */
    }

    public function importArray($data)
    {
        //if ( count($this->childCollections['image_description'])==0 ) {
        //    $this->initCollectionByLookupKey_ImageDescription([]);
        //}
//echo '<pre>'; var_dump($data); echo '</pre>';
        $result = parent::importArray($data);

        return $result;
    }


}