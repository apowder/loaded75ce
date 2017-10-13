<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class Account extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $menus = array();
    $sql = tep_db_query("select * from " . TABLE_MENUS);
    while ($row=tep_db_fetch_array($sql)){
      $menus[] = $row;
    }

    return $this->render('account.tpl', [
      'id' => $this->id, 'params' => $this->params, 'menus' => $menus, 'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}