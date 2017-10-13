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

class Currencies extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $currency_id, $currencies, $request_type;
    reset($currencies->currencies);
    $currencies_array = array();
    while (list($key, $value) = each($currencies->currencies)) {
      $value['key'] = $key;
      if (!in_array($key, $currencies->platform_currencies)) continue;
      if (Yii::$app->controller->id . '/' . Yii::$app->controller->action->id == 'index/index') {
        $value['link'] = tep_href_link('/', \common\helpers\Output::get_all_get_params(array('language', 'currency')) . 'currency=' . $key, $request_type);
      } else {
        $value['link'] = tep_href_link(Yii::$app->controller->id . (Yii::$app->controller->action->id != 'index' ? '/' . Yii::$app->controller->action->id : ''), \common\helpers\Output::get_all_get_params(array('language', 'currency')) . 'currency=' . $key, $request_type);
      }
      $currencies_array[] = $value;
    }

    return IncludeTpl::widget(['file' => 'boxes/currencies.tpl', 'params' => ['currencies' => $currencies_array, 'currency_id' => $currency_id]]);
  }
}