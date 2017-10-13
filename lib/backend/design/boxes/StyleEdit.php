<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class StyleEdit extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $block_type;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    return $this->render('style-edit.tpl', [
      'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings, 'block_type' => $this->block_type,
      'visibility' => $this->visibility,
    ]);
  }
}