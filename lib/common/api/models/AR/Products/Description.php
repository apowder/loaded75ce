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
use common\helpers\Seo;

class Description extends EPMap
{

    protected $hideFields = [
        'products_id',
        'language_id',
        'affiliate_id',
        'products_name_soundex',
        'products_description_soundex',
    ];

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'].'_0';
            $keyCodes[$keyCode] = [
                'products_id' => null,
                'language_id' => $lang['id'],
                'affiliate_id' => 0,
            ];
        }
        return $keyCodes;
    }

    public static function tableName()
    {
        return TABLE_PRODUCTS_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['products_id', 'language_id', 'affiliate_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
    }

    public function beforeSave($insert)
    {
        if ( empty($this->products_seo_page_name) ) {
            $this->products_seo_page_name = Seo::makeSlug($this->products_name);
        }
        return parent::beforeSave($insert);
    }

}