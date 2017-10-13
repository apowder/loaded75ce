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

class Price extends Widget
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
    global $languages_id, $currencies, $customer_groups_id;
    $params = Yii::$app->request->get();

    if ($params['products_id']) {
			if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'checkPackPrice')) {
				$return_price = $ext::checkPackPrice($params['products_id']);
			}else{
				$return_price = true;
			}

      $product_qty = \common\helpers\Product::get_products_stock($params['products_id']);
      $stock_info = \common\classes\StockIndication::product_info(array(
        'products_id' => $params['products_id'],
        'products_quantity' => $product_qty,
      ));
      if ($stock_info['flags']['request_for_quote']){
        $return_price = false;
      }

      if($return_price){
				$product_info = tep_db_fetch_array(tep_db_query("
					select products_price, products_tax_class_id, products_id
					from " . TABLE_PRODUCTS . "
					where products_id = '" . (int)$params['products_id'] . "'
					"));
	
	
				if ($new_price = \common\helpers\Product::get_products_special_price($product_info['products_id'])) {
					$special = $currencies->display_price($new_price, \common\helpers\Tax::get_tax_rate($product_info['products_tax_class_id']), 1, true);
					$old = $currencies->display_price(\common\helpers\Product::get_products_price($product_info['products_id'], 1, $product_info['products_price']), \common\helpers\Tax::get_tax_rate($product_info['products_tax_class_id']));
					$current = '';
				} else {
					$special = '';
					$old = '';
					$current = $currencies->display_price(\common\helpers\Product::get_products_price($product_info['products_id'], 1, $product_info['products_price']), \common\helpers\Tax::get_tax_rate($product_info['products_tax_class_id']), 1, true);
				}
			
                                if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'changeShowPrice')) {
                                    if ($ext::changeShowPrice($customer_groups_id)) {
                                        $special = $old = $current = '';
                                    }
                                }
	
				return IncludeTpl::widget(['file' => 'boxes/product/price.tpl', 'params' => [
					'special' => $special,
					'old' => $old,
					'current' => $current,
				]]);
			}else{
				return '';
			}
    } else {
      return '';
    }
  }
}