<?php

/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\AR\Categories;

use common\api\models\AR\EPMap;
use common\helpers\Seo;

class Description extends EPMap
{

    protected $hideFields = [
        'categories_id',
        'language_id',
        'affiliate_id',
    ];

    public static function tableName()
    {
        return TABLE_CATEGORIES_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['categories_id', 'language_id', 'affiliate_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->categories_id = $parentObject->categories_id;
        parent::parentEPMap($parentObject);
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'].'_0';
            $keyCodes[$keyCode] = [
                'categories_id' => null,
                'language_id' => $lang['id'],
                'affiliate_id' => 0,
            ];
        }
        return $keyCodes;
    }

    public function beforeSave($insert)
    {
        if ( empty($this->categories_seo_page_name) ) {
            $this->categories_seo_page_name = Seo::makeSlug($this->categories_name);
        }
        return parent::beforeSave($insert);
    }

}