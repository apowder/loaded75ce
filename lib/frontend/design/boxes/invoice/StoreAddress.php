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
use frontend\design\Info;

class StoreAddress extends Widget
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
    if ($this->settings[0]['address_spacer']){
      $spacer = $this->settings[0]['address_spacer'];
    } else {
      $spacer = '<br>';
    }
    if ($this->params['platform_id']){
      $data = Info::platformData($this->params['platform_id']);
    } else {
      $data = Info::platformData();
    }
    return \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $data, 1, ' ', $spacer);
  }
}