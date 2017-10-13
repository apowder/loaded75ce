<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design;

use Yii;
use yii\base\Widget;

class IncludeTpl extends Widget
{

  public $file;
  public $params;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    for ($i = 0; $i < count(Yii::$app->view->theme->pathMap['@app/views']); $i++) {
      if (file_exists(Yii::getAlias(Yii::$app->view->theme->pathMap['@app/views'][$i]) . '/' . $this->file)) {
        return $this->render(Yii::$app->view->theme->pathMap['@app/views'][$i] . '/' . $this->file, $this->params);
      }
    }
    return '';
  }
}