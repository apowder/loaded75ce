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

class Search extends Widget
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

    $link = tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL');

    return IncludeTpl::widget(['file' => 'boxes/search.tpl', 'params' => [
      'link' => $link,
      'keywords' => \common\helpers\Output::output_string(isset($_GET['keywords'])?tep_db_prepare_input($_GET['keywords']):''),
      'extra_form_fields' => (ALLOW_QUICK_SEARCH_DESCRIPTION == 'true'?'<input type="hidden" name="search_in_description" value="1">':''),
    ]]);
  }
}