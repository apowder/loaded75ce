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
use common\classes\payment;

class CheckoutBtn extends Widget
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
    global $cart;

    if (tep_session_is_registered('customer_id')){
      $checkout_link = tep_href_link('checkout', '', 'SSL');
    } else {
      $checkout_link = tep_href_link('checkout/login', '', 'SSL');
    }
    
    $paypal_link = '';
    if ($GLOBALS['paypal_express']->enabled){
      $paypal_link = $GLOBALS['paypal_express']->checkout_initialization_method();
    }

    if ($cart->count_contents() > 0) {
      return IncludeTpl::widget(['file' => 'boxes/cart/checkout-btn.tpl', 'params' => ['link' => $checkout_link, 'paypal_link' => $paypal_link]]);
    } else {
      return '';
    }
  }
}