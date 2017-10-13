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

class Name extends Widget
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
    $params = Yii::$app->request->get();

    if ($params['products_id']) {

      $product = tep_db_fetch_array(tep_db_query("
        select products_name
        from " . TABLE_PRODUCTS_DESCRIPTION . "
        where products_id = '" . (int)$params['products_id'] . "' and language_id = '" . (int)$languages_id . "'
        "));


      return IncludeTpl::widget(['file' => 'boxes/product/name.tpl', 'params' => ['name' => $product['products_name'], 'params'=> $this->params]]);
    } else {
      return '';
    }
  }
}