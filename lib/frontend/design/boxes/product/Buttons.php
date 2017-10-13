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
use frontend\design\Info;

class Buttons extends Widget
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
    global $languages_id, $customer_groups_id;
    $params = Yii::$app->request->get();

    if ($params['products_id'] && !GROUPS_DISABLE_CHECKOUT) {

      $compare_link = '';
      $wishlist_link = '';

      $product_qty = \common\helpers\Product::get_products_stock($params['products_id']);
      $stock_info = \common\classes\StockIndication::product_info(array(
        'products_id' => $params['products_id'],
        'products_quantity' => $product_qty,
      ));
      $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true);

      return IncludeTpl::widget(['file' => 'boxes/product/buttons.tpl', 'params' => [
        'compare_link' => $compare_link,
        'wishlist_link' => $wishlist_link,
        'product_qty' => \common\helpers\Product::get_products_stock($params['products_id']),
        'product_has_attributes' => \common\helpers\Attributes::has_product_attributes($params['products_id']),
        'stock_info' => $stock_info,
        'product_in_cart' => Info::checkProductInCart($params['products_id']),
        'customer_is_logged' => tep_session_is_registered('customer_id'),
      ]]);
    } else {
      return '';
    }
  }
}