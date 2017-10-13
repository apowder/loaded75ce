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

class Description extends Widget
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
        select products_description
        from " . TABLE_PRODUCTS_DESCRIPTION . "
        where products_id = '" . (int)$params['products_id'] . "' and language_id = '" . (int)$languages_id . "'
        "));


      return IncludeTpl::widget(['file' => 'boxes/product/description.tpl', 'params' => ['description' => $product['products_description']]]);
    } else {
      return '';
    }
  }
}