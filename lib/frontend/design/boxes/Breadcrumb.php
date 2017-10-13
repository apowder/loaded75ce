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

class Breadcrumb extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $breadcrumb;
    $breadcrumb_trail = $breadcrumb->trail();
    return IncludeTpl::widget(['file' => 'boxes/breadcrumb.tpl', 'params' => [
      'breadcrumb' => $breadcrumb_trail,
      'settings' => $this->settings
    ]]);
  }
}