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

class Reviews extends Widget
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
      $max = MAX_RANDOM_SELECT_REVIEWS;
    }

    $products2c_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products2c_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
      $random_select = "select distinct r.reviews_id, r.reviews_rating, r.customers_name, p.products_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, substring(rd.reviews_text, 1, 200) as reviews_text from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'  " . " where status and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = r.products_id and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' " . " and pd.affiliate_id = 0 and p.products_id = pd.products_id and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and pd.language_id = '" . (int)$languages_id . "'";
    }else{
      $random_select = "select distinct r.reviews_id, r.reviews_rating, r.customers_name, p.products_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, substring(rd.reviews_text, 1, 200) as reviews_text from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products2c_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  " . " where status and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = r.products_id and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' " . " and p.products_id = pd.products_id and pd.affiliate_id = 0 and pd.language_id = '" . (int)$languages_id . "'";
    }
    if (isset($HTTP_GET_VARS['products_id'])) {
      $random_select .= " and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'";
    }
    $random_select .= " order by r.reviews_id desc limit " . $max;
    $random_product = tep_db_query($random_select);


    $items = array();
    if (tep_db_num_rows($random_product) > 0) {

      while($res_rev = tep_db_fetch_array($random_product)){

        $res_rev['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $res_rev['products_id'] . '&amp;reviews_id=' . $res_rev['reviews_id']);

        $res_rev['img'] = Yii::$app->request->baseUrl . '/images/' . $res_rev['products_image'];
        if (!is_file(Yii::getAlias('@webroot') . '/images/' . $res_rev['products_image'])){
          $res_rev['img'] = 'no';
        }

        $items[] = $res_rev;

      }


      return IncludeTpl::widget([
        'file' => 'boxes/reviews.tpl',
        'params' => ['items' => $items]
      ]);
    }



    return '';
  }
}