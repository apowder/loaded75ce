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

class Properties extends Widget
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

    if (!$params['products_id']) return '';

    $products_data_r = tep_db_query(
      "select p.products_id,
          p.products_model,
          p.products_ean,
          p.products_isbn,
          m.manufacturers_id, m.manufacturers_name
        from " . TABLE_PRODUCTS . " p
        left join ".TABLE_MANUFACTURERS." m on m.manufacturers_id=p.manufacturers_id
        where
          p.products_id='" . (int)$params['products_id'] . "'
    ");
    if ( tep_db_num_rows($products_data_r)==0 ) return '';

    $products_data = tep_db_fetch_array($products_data_r);
    $products_data['manufacturers_link'] = empty($products_data['manufacturers_id'])?'':tep_href_link(/*FILENAME_DEFAULT*/'catalog/index','manufacturers_id='.$products_data['manufacturers_id']);

    $have_product_data =
      !empty($products_data['manufacturers_name']) || !empty($products_data['products_model']) ||
      !empty($products_data['products_ean']) || !empty($products_data['products_isbn']);

    $properties_array = array();
    $values_array = array();
    $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_product = '1' and p2p.products_id = '" . (int)$products_data['products_id'] . "'");
    while ($properties = tep_db_fetch_array($properties_query)) {
        if (!in_array($properties['properties_id'], $properties_array)) {
            $properties_array[] = $properties['properties_id'];
        }
        $values_array[$properties['properties_id']][] = $properties['values_id'];
    }
    $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);

    if ( count($properties_array)>0 || $have_product_data ) {
      return IncludeTpl::widget([
        'file' => 'boxes/product/properties.tpl',
        'params' => [
          'products_data' => $products_data,
          'properties_tree_array' => $properties_tree_array,
          'settings' => $this->settings[0]
        ]
      ]);
    }
    return '';
  }

}