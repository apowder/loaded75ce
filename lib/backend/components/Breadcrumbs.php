<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\components;

use yii\base\Widget;

class Breadcrumbs extends Widget {

    public $navigation = array();
		public $topButtons = array();
    
    public function run() {
        if (isset(\Yii::$app->controller->navigation)) {
            $this->navigation = \Yii::$app->controller->navigation;
        }
        if (isset(\Yii::$app->controller->topButtons)) {
						$this->topButtons = \Yii::$app->controller->topButtons;
        }
        return $this->render('Breadcrumbs', [
          'context' => $this,
        ]);
    }

}

