<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\contact;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class StreetView extends Widget
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
    $key = tep_db_fetch_array(tep_db_query("select info as setting_code from " . TABLE_GOOGLE_SETTINGS . " where module='mapskey'"));

    $data = Info::platformData();

    return IncludeTpl::widget(['file' => 'boxes/contact/street-view.tpl', 'params' => [
      'address' =>
        $data['street_address'] . ', ' .
        $data['suburb'] .($data['suburb'] ? ', ' : '') .
        $data['city'] . ', ' .
        $data['state'] . ', ' .
        $data['postcode'] . ', ' .
        $data['country'],
      'key' => $key['setting_code']
    ]]);

  }
}