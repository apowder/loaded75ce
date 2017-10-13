<?php
namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Stock extends Widget
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
    $post = Yii::$app->request->post();

    if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowStock')) {
        if ($ext::checkShowStock($customer_groups_id)) {
            return '';
        }
    }
    if ($params['products_id']) {

      $products_quantity = \common\helpers\Product::get_products_stock($params['products_id']);

      return IncludeTpl::widget(['file' => 'boxes/product/stock.tpl', 'params' => [
        'stock_indicator' => \common\classes\StockIndication::product_info(array(
          'products_id' => $params['products_id'],
          'products_quantity' => $products_quantity,
          //'stock_indication_id' => (isset($products_arr['stock_indication_id'])?$products_arr['stock_indication_id']:null),
        )),
      ]]);
    } else {
      return '';
    }
  }
}