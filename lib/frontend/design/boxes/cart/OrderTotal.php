<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\models\KlarnaCheckoutModel;
use frontend\design\boxes\Klarna;

class OrderTotal extends Widget
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
    $order_total_modules = new \common\classes\order_total();

    $order_total_output = $order_total_modules->process();
    
    $result = [];
    foreach ($order_total_output as $total) {
        if (class_exists($total['code'])) {
            if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_SHOPPING_CART') ) {
                    if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                        $result[]  = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_SHOPPING_CART', $total);
                    } else {
                        $result[] = $total;
                    }
                }
            }
        }
    }
    
    $klarnaOrder = $klarnaCheckout = null;
    $klarnaWidget = '';
    if ($GLOBALS['klarna_checkout']->enabled){
        $klarnaCheckout = new KlarnaCheckoutModel();
        $klarnaOrder = $klarnaCheckout->register();
        $klarnaWidget = Klarna::widget(['klarnaCheckout' => $klarnaCheckout, 'klarnaOrder' => $klarnaOrder]);
    }
    
    return IncludeTpl::widget(['file' => 'boxes/cart/order-total.tpl', 'params' => ['order_total_output' => $result, 'klarna' => $klarnaWidget]]);
  }
}