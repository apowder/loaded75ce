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
use yii\helpers\Html;

class Languages extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $lng, $request_type, $languages_id;

    $languages = array();
    reset($lng->catalog_languages);
        
    while (list($key, $value) = each($lng->catalog_languages)) {
        if (!in_array($value['code'], $lng->paltform_languages)) continue;

        if (Yii::$app->controller->id . '/' . Yii::$app->controller->action->id == 'index/index') {
          $link = tep_href_link('/', \common\helpers\Output::get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type);
        } else {
          $link = tep_href_link(Yii::$app->controller->id . (Yii::$app->controller->action->id != 'index' ? '/' . Yii::$app->controller->action->id : ''), \common\helpers\Output::get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type);
        }

      $languages[] = array(
        'image' => Html::img(DIR_WS_ICONS . $value['image'],['width' => 24, 'height' => 16, 'class' => 'language-icon' , 'alt' => $value['name'], 'title' => $value['name']]),
        'name' => $value['name'],
        'link' => $link,
        'id' => $value['id'],
        'key' => $key
      );

    }
//echo '<pre>';print_r($lng);var_dump($languages);die;
    return IncludeTpl::widget(['file' => 'boxes/languages.tpl', 'params' => ['languages' => $languages, 'languages_id' => $languages_id]]);
  }
}