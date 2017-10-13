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

    $poster = \frontend\design\Info::themeImage($this->settings[$languages_id]['poster'], false, false);
    if ($poster) {
      $this->settings[$languages_id]['poster'] = Yii::getAlias('@web') . '/' . $poster;
    }
    $this->settings[$languages_id]['video'] = 
        Yii::getAlias('@web') . '/' .
        \frontend\design\Info::themeImage($this->settings[$languages_id]['video'], false, false);
    
    return IncludeTpl::widget([
      'file' => 'boxes/video.tpl',
      'params' => [
        'languages_id' => $languages_id,
        'settings' => $this->settings,
      ],
    ]);
  }
}