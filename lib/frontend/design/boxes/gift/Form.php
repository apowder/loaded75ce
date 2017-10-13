<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\gift;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Form extends Widget
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
        global $languages_id, $currency_id, $currencies;

        $giftAmount = [];
        $check_product = tep_db_fetch_array(tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_model = 'VIRTUAL_GIFT_CARD'"));
        $products_id = $check_product['products_id'];
        if ($products_id > 0) {
            if (USE_MARKET_PRICES == 'True') {
                $gift_card_price_query = tep_db_query("select products_price from " . TABLE_VIRTUAL_GIFT_CARD_PRICES . " where products_id = '" . (int)$products_id . "' and currencies_id = '" . (int)$currency_id . "' order by products_price");
                while ($gift_card_price = tep_db_fetch_array($gift_card_price_query)) {
                    $giftAmount[$gift_card_price['products_price']] = $currencies->format($gift_card_price['products_price'], false);
                }
            } else {
                $gift_card_price_query = tep_db_query("select products_price from " . TABLE_VIRTUAL_GIFT_CARD_PRICES . " where products_id = '" . (int)$products_id . "' and currencies_id = '" . (int)$currencies->currencies[DEFAULT_CURRENCY]['id'] . "' order by products_price");
                while ($gift_card_price = tep_db_fetch_array($gift_card_price_query)) {
                    $giftAmount[$gift_card_price['products_price']] = $currencies->format($gift_card_price['products_price']);
                }
            }
        }
     
        return IncludeTpl::widget(['file' => 'boxes/gift/form.tpl', 'params' => [
            'params' => $this->params,
            'giftAmount' => $giftAmount,
        ]]);
  }
}