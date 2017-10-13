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

class Description extends Widget
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
      $category_query = tep_db_query("select if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) as categories_description from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id = '" . (int)$languages_id . "' and cd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);

    }
    
    return IncludeTpl::widget(['file' => 'boxes/catalog/description.tpl', 'params' => ['description' => $category['categories_description']]]);
  }
}