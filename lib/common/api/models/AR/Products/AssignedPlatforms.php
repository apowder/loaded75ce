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

class AssignedPlatforms extends EPMap
{

    protected $hideFields = [
        'products_id',
    ];

    public static function tableName()
    {
        return TABLE_PLATFORMS_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id', 'platform_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->platform_id) && !is_null($this->platform_id) && $importedObject->platform_id==$this->platform_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $tools = new Tools();
        $data = parent::exportArray($fields);
        $data['platform_name'] = $tools->getPlatformName($this->platform_id);
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['platform_name'])) {
            $tools = new Tools();
            $data['platform_id'] = $tools->getPlatformId($data['platform_name']);
        }
        return parent::importArray($data);
    }

}