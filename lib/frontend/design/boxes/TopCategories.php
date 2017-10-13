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

class TopCategories extends Widget
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
    global $languages_id, $HTTP_SESSION_VARS;

    $categories_join = '';
    if ( \common\classes\platform::activeId() ) {
      $categories_join .= " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }
    $categories_query = tep_db_query(
      "select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name, c.categories_image, c.parent_id ".
      "from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
      $categories_join.
      "  left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . (int)$languages_id ."' and cd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' ".
      "where c.parent_id = '0' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.categories_status = 1 and cd.affiliate_id = 0 ".
      "order by sort_order, categories_name" . ($this->settings[0]['max_items'] ? " limit " . (int)$this->settings[0]['max_items'] . "" : "")
    );




    if (tep_db_num_rows($categories_query) > 0){

      $categories_arr = array();
      while ($categories = tep_db_fetch_array($categories_query)) {
        if (\common\helpers\Categories::count_products_in_category($categories['categories_id']) > 0 || Info::themeSetting('show_empty_categories')) {
          $cPath_new = \common\helpers\Categories::get_path($categories['categories_id']);

          $categories['link'] = tep_href_link('catalog', 'cPath=' . $categories['categories_id']);
          $categories['img'] = Yii::$app->request->baseUrl . '/images/' . $categories['categories_image'];
          if (!is_file(Yii::getAlias('@webroot') . '/images/' . $categories['categories_image'])) {
            $categories['img'] = 'no';
          }
          $categories_arr[] = $categories;
        }
      }
      return IncludeTpl::widget([
        'file' => 'boxes/categories.tpl',
        'params' => ['categories' => $categories_arr, 'themeImages' => DIR_WS_THEME_IMAGES]
      ]);

    }

    return '';
  }
}