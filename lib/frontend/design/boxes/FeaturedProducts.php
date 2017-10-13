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

class FeaturedProducts extends Widget
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
    global $languages_id, $currency_id, $customer_groups_id, $HTTP_SESSION_VARS, $affiliate_ref, $current_category_id;

    if ($this->settings[0]['params']) {
      $max = $this->settings[0]['params'];
    } else {
      $max = MAX_DISPLAY_FEATURED_PRODUCTS;
    }


    $featured_products_category_id = $current_category_id;
    $info_box_contents = array();
    $add_from = '';
    if (tep_session_is_registered('affiliate_ref') && $HTTP_SESSION_VARS['affiliate_ref'] != ''){
      $add_sql = " and (f.affiliate_id = '" . $affiliate_ref . "' or f.affiliate_id = 0) ";
    }else{
      $add_sql = " and f.affiliate_id = 0 ";
    }

    $products_join = '';
    $products2_join = '';
    $categories2_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $products2_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $categories2_join .=
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $common_columns = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, ';

    if ( (!isset($featured_products_category_id)) || ($featured_products_category_id == '0') ) {
      $info_box_contents[] = array('align' => 'left', 'text' => TABLE_HEADING_FEATURED_PRODUCTS);
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, s.status as specstat, s.specials_new_products_price, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id left join " . TABLE_FEATURED . " f on p.products_id = f.products_id left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' " . $add_from . "  where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and f.status = '1' " . $add_sql . " and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by rand() DESC limit " . $max);
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, s.status as specstat, s.specials_new_products_price, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id left join " . TABLE_FEATURED . " f on p.products_id = f.products_id " . $add_from . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and f.status = 1 " . $add_sql . " and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by rand() DESC limit " . $max);
      }
    } else {
      $cat_name = \common\helpers\Categories::get_categories_name($featured_products_category_id);
      $info_box_contents[] = array('align' => 'left', 'text' => sprintf(TABLE_HEADING_FEATURED_PRODUCTS_CATEGORY, $cat_name));
      if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, s.status as specstat, s.specials_new_products_price, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories2_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id left join " . TABLE_FEATURED . " f on p.products_id = f.products_id left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' " . $add_from . " where p.products_id = p2c.products_id and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$featured_products_category_id . "' and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and f.status = 1 and c.categories_status = 1 " . $add_sql . " and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by rand() DESC limit " . $max);
      }else{
        $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_image, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, s.status as specstat, s.specials_new_products_price, p.products_price, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories2_join}, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id left join " . TABLE_FEATURED . " f on p.products_id = f.products_id " . $add_from . "  where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$featured_products_category_id . "' and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and f.status = 1 and c.categories_status = 1 " . $add_sql . " and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0  order by rand() DESC limit " . $max);
      }
    }


    if (tep_db_num_rows($products_query) > 0){

      return IncludeTpl::widget([
        'file' => 'boxes/featured-products.tpl',
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