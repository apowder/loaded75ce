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

class InfoPage extends Widget
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


    if($this->settings[0]['info_page']) {

      $sql = tep_db_query("select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, if(length(i1.description), i1.description, i.description) as description, i.information_id from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' " . (\common\classes\platform::activeId() ? " AND i1.platform_id='" . \common\classes\platform::currentId() . "' " : '') . " where i.information_id = '" . (int)$this->settings[0]['info_page'] . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 " . (\common\classes\platform::activeId() ? " AND i.platform_id='" . \common\classes\platform::currentId() . "' " : ''));
      $row = tep_db_fetch_array($sql);


      return IncludeTpl::widget(['file' => 'boxes/text.tpl', 'params' => ['text' => stripslashes($row['description'])]]);
    } else {
      return '';
    }
  }
}