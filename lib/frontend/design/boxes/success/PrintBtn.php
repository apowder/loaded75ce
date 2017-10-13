<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\success;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PrintBtn extends Widget
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
    if ($this->params['print_order_href']) {
      return IncludeTpl::widget(['file' => 'boxes/success/print-btn.tpl', 'params' => ['link' => $this->params['print_order_href']]]);
    } else {
      return '';
    }
  }
}