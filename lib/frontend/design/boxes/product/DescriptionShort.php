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

class DescriptionShort extends Widget
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

    if ($params['products_id']) {

      $product = tep_db_fetch_array(tep_db_query("
        select products_description_short, products_description
        from " . TABLE_PRODUCTS_DESCRIPTION . "
        where products_id = '" . (int)$params['products_id'] . "' and language_id = '" . (int)$languages_id . "'
        "));

      if (!$product['products_description_short'] && $this->settings[0]['cat_description']){
        $strip = strip_tags($product['products_description']);
        if ($this->settings[0]['length_description']){
          $length = $this->settings[0]['length_description'];
        } else {
          $length  = 200;
        }
        if (strlen($strip) > $length) {
          $description = substr($strip, 0, $length) . '...';
        } else {
          $description = $strip;
        }
        if ($this->settings[0]['link_description']){
          $description = '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, \common\helpers\Output::get_all_get_params()) . '#description">' . $description . '</a>';
        }
      } else {
        $description = $product['products_description_short'];
      }


      return IncludeTpl::widget(['file' => 'boxes/product/description-short.tpl', 'params' => [
        'description' => $description
      ]]);
    } else {
      return '';
    }
  }
}
