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

class Xsell extends EPMap
{

    protected $hideFields = [
        'ID',
        'products_id',
    ];

    public static function tableName()
    {
        return TABLE_PRODUCTS_XSELL;
    }

    public static function primaryKey()
    {
        return ['ID'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->xsell_id) && !is_null($this->xsell_id) && $importedObject->xsell_id==$this->xsell_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

}