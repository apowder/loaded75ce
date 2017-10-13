<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Video extends Widget
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
    global $languages_id;
    $get = Yii::$app->request->get();

    $videos_query = tep_db_query("select * from " . TABLE_PRODUCTS_VIDEOS . " where products_id = '" . (int)$get['products_id'] . "'");

    $video = array();
    while ($item = tep_db_fetch_array($videos_query)){
      if (!$this->settings[0]['by_language'] || $item['language_id'] == $languages_id ) {
        $item['code'] = '';
        if (strrpos($item['video'], 'youtu.be')) {
          preg_match_all("/\/([^\/^?]+)/", $item['video'], $arr);
          $item['code'] = $arr[1][1];
        } elseif (strrpos($item['video'], 'youtube.com')) {
          preg_match_all("/\/([^\/^?^\"]+)[\"\?]/", $item['video'], $arr);
          $item['code'] = $arr[1][0];
        }
        if ($item['code']) {
          $video[] = $item;
        }
      }
    }

    return IncludeTpl::widget(['file' => 'boxes/product/video.tpl', 'params' => [
      'video' => $video,
      'settings' => $this->settings
    ]]);
  }
}