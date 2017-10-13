<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Block;

class Container extends Widget
{

  public $settings;
  public $params;
  public $id;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    return Block::widget(['name' => 'block-' . $this->id, 'params' => ['params' => $this->params]]);

  }
}