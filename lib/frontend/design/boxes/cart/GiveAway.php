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
use common\classes\Images;

class GiveAway extends Widget
{

  public $type;
  public $settings;
  public $params;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    global $cart, $languages_id, $currencies;

	  $products = \common\helpers\Gifts::getGiveAways();     

      if ( count($products)==0 ) return '';

      return IncludeTpl::widget([
      'file' => 'boxes/cart/give-away.tpl',
      'params' => [
        'products' => $products,
      ]]);
  }
}