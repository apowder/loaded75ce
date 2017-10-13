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

class Model extends Widget
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


      $data = tep_db_fetch_array(tep_db_query(
        "select products_model as model, products_ean as ean, products_isbn as isbn, products_asin as asin, products_upc as upc from " . TABLE_PRODUCTS . " where products_id='" . (int)$params['products_id'] . "'"));

      return IncludeTpl::widget(['file' => 'boxes/product/model.tpl', 'params' => ['data' => $data, 'settings' => $this->settings[0]]]);
    } else {
      return '';
    }
  }
}