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
use common\classes\ReCaptcha;

class ReCaptchaWidget extends Widget
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
    global $lng;
    
    $captcha = new ReCaptcha();
    if (!$captcha->isEnabled()) return;
    
    return IncludeTpl::widget(['file' => 'boxes/captcha.tpl', 'params' => [
      'code' => $lng->get_code(),
      'public_key' => $captcha->getPublicKey(),
    ]]);
  }
}