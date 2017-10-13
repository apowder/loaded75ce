<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\info;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Title extends Widget
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

    global $HTTP_SESSION_VARS, $languages_id;

    if(!$_GET['info_id']) return '';

    $info_id = (int)$_GET['info_id'];

    $sql = tep_db_query("select if(length(i1.info_title), i1.info_title, i.info_title) as info_title from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' and i1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' AND i1.platform_id='".\common\classes\platform::currentId()."' where i.information_id = '" . (int)$info_id . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 and i.affiliate_id = 0 AND i.platform_id='".\common\classes\platform::currentId()."' ");
    $row=tep_db_fetch_array($sql);

    if ($row['page_title'] == ''){
      $title = stripslashes($row['info_title']);
    }else{
      $title = stripslashes($row['page_title']);
    }
    
    return IncludeTpl::widget(['file' => 'boxes/info/title.tpl', 'params' => ['title' => $title]]);
  }
}