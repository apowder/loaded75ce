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

class SpecialsProducts extends Widget
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
      $max = 4;
    }

    $products2c_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products2c_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $common_columns = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, ';
    if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
      $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_price, p.products_weight, p.products_quantity, p.products_model, p.products_tax_class_id, p.products_image, s.specials_new_products_price, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  " . "  left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "', " . TABLE_SPECIALS . " s left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int)$customer_groups_id . "' and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.products_id = p.products_id " . " and s.status = 1 and p.products_id = pd.products_id and pd.affiliate_id = 0 and pd.language_id = '" . (int)$languages_id . "' order by s.specials_date_added DESC limit $max");
    }else{
      $products_query = tep_db_query("select distinct p.products_id, {$common_columns} p.is_virtual, p.products_price, p.products_weight, p.products_quantity, p.products_model, p.products_tax_class_id, p.products_image, s.specials_new_products_price, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_quantity, p.products_model from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  " . "  where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and s.products_id = p.products_id  " . "  and s.status = 1 " . " and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.affiliate_id = 0 order by s.specials_date_added DESC limit $max");
    }


    if (tep_db_num_rows($products_query) > 0){

      return IncludeTpl::widget([
        'file' => 'boxes/specials-products.tpl',
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