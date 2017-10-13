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

class DesignAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/basic/js';
    public $css = [
    ];
    public $js = [
      'jquery-ui.min.js',
      'jquery.edit-blocks.js',
      'jquery.edit-theme.js',
    ];
}
