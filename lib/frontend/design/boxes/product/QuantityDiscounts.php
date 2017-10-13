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

class QuantityDiscounts extends Widget
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
    global $currencies, $customer_groups_id;
    $get = Yii::$app->request->get();

    if ($get['products_id']) {

      $product_info = tep_db_fetch_array(tep_db_query("
        select products_price, products_tax_class_id, products_id
        from " . TABLE_PRODUCTS . "
        where products_id = '" . (int)$get['products_id'] . "'
        "));

      $discounts = array();
      $dt = \common\helpers\Product::get_products_discount_table($get['products_id']);
      for ($i=0, $n=sizeof($dt); $i<$n; $i=$i+2) {
        if ($dt[$i] > 0) {
          $discounts[] = array(
            'count' => $dt[$i],
            'price' => $currencies->display_price($dt[$i+1], \common\helpers\Tax::get_tax_rate($product_info['products_tax_class_id']))
          );
        }
      }

      return IncludeTpl::widget(['file' => 'boxes/product/quantity-discounts.tpl', 'params' => [
        'discounts' => $discounts,
      ]]);
    } else {
      return '';
    }
  }
}