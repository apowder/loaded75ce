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

class Brand extends Widget
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

      $manufacture = tep_db_fetch_array(tep_db_query("
        select m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url, p.manufacturers_id
        from " . TABLE_PRODUCTS . " p, " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi
        where 
          p.products_id = '" . (int)$params['products_id'] . "' and 
          p.manufacturers_id = m.manufacturers_id and
          p.manufacturers_id = mi.manufacturers_id and
          mi.languages_id = '" . (int)$languages_id . "'
        "));

      return IncludeTpl::widget(['file' => 'boxes/product/brand.tpl', 'params' => [
        'manufacture' => $manufacture,
        'params'=> $this->params,
        'link' => tep_href_link('catalog/manufacturers', 'manufacturers_id=' . $manufacture['manufacturers_id'])
      ]]);
    } else {
      return '';
    }
  }
}