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

class Quantity extends Widget
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
    global $languages_id, $customer_groups_id, $currencies;
    $params = Yii::$app->request->get();
    $post = Yii::$app->request->post();
		
	  if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'quantityBoxFrontend')) {
			$par = $ext::quantityBoxFrontend($post, $params);
			return IncludeTpl::widget(['file' => 'boxes/product/quantity.tpl', 'params' => $par]);
		}else{
			if ($params['products_id'] && !GROUPS_DISABLE_CHECKOUT) {
			
//				$stock = false;
//				if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT != 'true'){
//					$stock = \common\helpers\Product::get_products_stock($params['products_id']);
//				}
        $show_quantity_input = true;
        $product_qty = \common\helpers\Product::get_products_stock($params['products_id']);
        $stock_info = \common\classes\StockIndication::product_info(array(
          'products_id' => $params['products_id'],
          'products_quantity' => $product_qty,
        ));
        $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true);
        if ($stock_info['flags']['request_for_quote']) {
          $show_quantity_input = false;
        }

        return IncludeTpl::widget(['file' => 'boxes/product/quantity.tpl', 'params' => [
					'qty' => \common\helpers\Product::filter_product_order_quantity($params['products_id'],$post['qty']),
					'stock' => $product_qty,
					'quantity_max' => $stock_info['quantity_max'],
					'show_quantity_input' => $show_quantity_input,
					'order_quantity_data' => \common\helpers\Product::get_product_order_quantity($params['products_id']),
					'product_in_cart' => Info::checkProductInCart($params['products_id']),
				]]);
			} else {
				return '';
			}
		}
  }
}