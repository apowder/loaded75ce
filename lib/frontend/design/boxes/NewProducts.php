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

class NewProducts extends Widget
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
    global $languages_id, $currency_id, $customer_groups_id, $HTTP_SESSION_VARS;

    if ($this->settings[0]['params']) {
      $max = $this->settings[0]['params'];
    } else {
      $max = MAX_DISPLAY_NEW_PRODUCTS;
    }

    $categories_join = '';
    $products_join = '';
    $products2c_join = '';
    if ( \common\classes\platform::activeId() ) {
      $categories_join .=
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $products_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $products2c_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $common_columns = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, ';
    if ( (!isset($current_category_id)) || ($current_category_id == '0') ) {
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_tax_class_id, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 )  " . "  and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by p.products_date_added desc limit " . $max);
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_tax_class_id, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "  " . "  and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by p.products_date_added desc limit " . $max);
      }
    } else {
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_tax_class_id, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'  " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_id = p2c.products_id and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and p2c.categories_id = c.categories_id and c.parent_id = '" . $current_category_id . "' and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and c.categories_status = 1  " . "  and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by p.products_date_added desc limit " . $max);
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_tax_class_id, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS . " p {$products_join} " . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$current_category_id . "' and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and c.categories_status = 1  " . "  and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0 order by p.products_date_added desc limit " . $max);
      }
    }

    if (tep_db_num_rows($products_query) > 0){
      return IncludeTpl::widget([
        'file' => 'boxes/new-products.tpl',
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