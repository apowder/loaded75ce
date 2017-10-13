<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Klarna extends Widget
{

  public $klarnaCheckout;
  public $klarnaOrder;  

  public function init()
  {
    parent::init();
  }

  public function run()
  { global $currencies;
    return IncludeTpl::widget([
      'file' => 'boxes/klarna.tpl',
      'params' => [
        'klarnaCheckout' => $this->klarnaCheckout, 'klarnaOrder' => $this->klarnaOrder, 'currencies' => $currencies        
      ]
    ]);
  }
}