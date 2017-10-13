<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\assets;

use yii\web\AssetBundle;

class BDPAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '@web/../plugins/bootstrap-datepicker/bootstrap-datepicker.min.css',
    ];
    public $js = [
//        '@web/../plugins/bootstrap-datepicker/nonconflict.js',
        '@web/../plugins/bootstrap-datepicker/bootstrap-datepicker.js',
        
    ];
}
