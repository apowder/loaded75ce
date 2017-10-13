<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Cart extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    if (GROUPS_DISABLE_CHECKOUT) return '';

    global $cart, $currencies;

    $products = $cart->get_products();

    foreach ($products as $key => $item ){
      $products[$key]['price'] = $currencies->display_price($item['final_price'] * (int)$item['quantity'], \common\helpers\Tax::get_tax_rate($item['tax_class_id']));
      $products[$key]['image'] = Images::getImageUrl($item['id'], 'Small');
      $products[$key]['link'] = tep_href_link('catalog/product', 'products_id='. $item['id']);
    }


    return IncludeTpl::widget(['file' => 'boxes/cart.tpl', 'params' => [
      'total' => $currencies->format($cart->show_total()),
      'count_contents' => $cart->count_contents(),
      'settings' => $this->settings,
      'products' => $products
    ]]);
  }
}