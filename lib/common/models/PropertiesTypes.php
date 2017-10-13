<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use Yii;

class PropertiesTypes {

    public static function getTypes($mode = 'all') {

        \common\helpers\Translation::init('admin/properties');

        if ($mode == 'search') {
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
            ];
        } else if ($mode == 'filter') {
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
                'file' => TEXT_PR_FILE
            ];
        } else {//all
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
                'file' => TEXT_PR_FILE
            ];
        }
    }

}
