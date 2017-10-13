<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class InBundles extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $params = Yii::$app->request->get();

    if ( !$params['products_id'] ) return '';

    $bundle_products = array();

    if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'inBundles')) {
        $bundle_products = $ext::inBundles($params);
    }
    
    if ( count($bundle_products)>0 ) {
      return IncludeTpl::widget([
        'file' => 'boxes/product/in-bundles.tpl',
        'params' => ['products' => $bundle_products]
      ]);
    }

    return '';
  }
}