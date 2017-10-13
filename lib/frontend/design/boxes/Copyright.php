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
use frontend\design\Info;

class Copyright extends Widget
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
    $data = Info::platformData();
    return sprintf(TEXT_COPYRIGHT, date("Y"), $data['company']);//'Copyright &copy; ' . date("Y") . ' ' . $data['company'] . '. eCommerce development by <a href="http://www.loadedcommerce.com/" target="_blank">Holbi</a>';
  }
}