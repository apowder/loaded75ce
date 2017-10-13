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
use frontend\design\Info;

class Bestsellers extends Widget
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
    global $languages_id, $currency_id, $customer_groups_id, $current_category_id, $HTTP_SESSION_VARS;

    if ($this->settings[0]['params']) {
      $max = $this->settings[0]['params'];
    } else {
      $max = 4;
    }

    $products_join = '';
    $categories_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $categories_join .=
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $common_columns = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, ';

    if (isset($current_category_id) && ($current_category_id > 0)) {
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.products_price, p.products_weight, p.products_quantity, p.products_model,p.products_tax_class_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "'  and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'  " . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and '" . (int)$current_category_id . "' in (c.categories_id, c.parent_id) and c.categories_status = 1 and pd.affiliate_id =0  " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . "  order by p.products_ordered desc, products_name  limit $max");
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.products_price, p.products_weight, p.products_quantity, p.products_model,p.products_tax_class_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  " . "  where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and '" . (int)$current_category_id . "' in (c.categories_id, c.parent_id) and c.categories_status = 1 and pd.affiliate_id =0  " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . "  order by p.products_ordered desc, products_name  limit $max");
      }
    } else {
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.products_price, p.products_weight, p.products_quantity, p.products_model,p.products_tax_class_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'  " . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and  if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'  and pd.affiliate_id =0  " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . " and p.products_id = p2c.products_id order by p.products_ordered desc, products_name  limit $max");
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.products_price, p.products_weight, p.products_quantity, p.products_model,p.products_tax_class_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  " . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'  and pd.affiliate_id =0  " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . " and p.products_id = p2c.products_id order by p.products_ordered desc, products_name limit $max");
      }
    }


    if (tep_db_num_rows($products_query) > 0){

      return IncludeTpl::widget([
        'file' => 'boxes/bestsellers.tpl',
        'params' => [
          'products' => Info::getProducts($products_query),
          'settings' => $this->settings,
          'languages_id' => $languages_id
        ]
      ]);

    }

    return '';
  }
}