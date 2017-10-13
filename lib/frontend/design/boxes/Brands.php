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

class Brands extends Widget
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


    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name, manufacturers_image from " . TABLE_MANUFACTURERS ." order by manufacturers_name asc");
    if ($number_of_rows = tep_db_num_rows($manufacturers_query)) {
      $manufacturers_arr = array();
      while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
        $manufacturers['link'] = tep_href_link('catalog/manufacturers', 'manufacturers_id=' . $manufacturers['manufacturers_id']);
        $manufacturers['img'] = Yii::$app->request->baseUrl . '/images/' . $manufacturers['manufacturers_image'];
        if (!is_file(Yii::getAlias('@webroot') . '/images/' . $manufacturers['manufacturers_image'])){
          $manufacturers['img'] = 'no';
        }
        $manufacturers_arr[] = $manufacturers;
      }

      return IncludeTpl::widget([
        'file' => 'boxes/brands.tpl',
        'params' => ['brands' => $manufacturers_arr]
      ]);

    }

    return '';
  }
}