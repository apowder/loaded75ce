<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use Yii;
use yii\web\ViewAction;

/**
 * Site custom action 
 */
class CustomPageAction extends ViewAction
{

    public $page;
    public $params = [];
    
    public function run()
    {   
        $modify = $this->page;
        $modify = strtolower($modify);
        $modify = str_replace(' ', '_', $modify);
        $modify = preg_replace('/[^a-z0-9_-]/', '', $modify);
        $this->page = $modify;
        return $this->render('custom');
    }
}
