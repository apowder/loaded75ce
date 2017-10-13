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

use yii\base\Widget;

class DatePickerJs extends Widget
{

    public $selector;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return IncludeTpl::widget([
            'file' => 'boxes/date-picker-js.tpl',
            'params' => [
                        'selector' => $this->selector,
                        'params' => $this->params
        ]]);
    }

}
