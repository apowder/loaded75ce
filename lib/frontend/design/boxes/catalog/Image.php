<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\catalog;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Image extends Widget
{

  public $file;
  public $params;
  public $content;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $HTTP_SESSION_VARS, $languages_id, $current_category_id;

    if ($current_category_id > 0) {

      // Get the category name and description
      $category_query = tep_db_query("select if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name, if(length(cd1.categories_heading_title), cd1.categories_heading_title, cd.categories_heading_title) as categories_heading_title, if(length(c.categories_image_2), c.categories_image_2, c.categories_image) as categories_image from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id = '" . (int)$languages_id . "' and cd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);

    } elseif ($_GET['manufacturers_id'] > 0) {

      // Get the manufacturer name and image
      $manufacturer_query = tep_db_query("select m.manufacturers_name as categories_name,  m.manufacturers_image as categories_image from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
      $category = tep_db_fetch_array($manufacturer_query);

    }
    
    $category['img'] = Yii::$app->request->baseUrl . '/images/' . $category['categories_image'];
    if (!is_file(Yii::getAlias('@webroot') . '/images/' . $category['categories_image'])){
      $category['img'] = 'no';
    }
    
    return IncludeTpl::widget(['file' => 'boxes/catalog/image.tpl', 'params' => ['category' => $category]]);
  }
}