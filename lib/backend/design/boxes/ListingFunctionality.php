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

class ListingFunctionality extends Widget
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

    for($i=0; $i<15; $i++){
      $_orders['sort_pos_' . $i] = $this->settings[0]['sort_pos_' . $i] ? $this->settings[0]['sort_pos_' . $i] : 0;
    }
    asort($_orders, SORT_NUMERIC);

    $orders = array();
    $counter = 1;
    foreach ($_orders as $key => $item){
      $orders[$key] = $counter;
      $counter++;
    }
    for ($i = 0; $i < 15; $i++){
      if (!$orders['sort_pos_' . $i]) $orders['sort_pos_' . $i] = 100 + $i;
    }

    $sorting[$orders['sort_pos_0']] = ['title' => TEXT_NO_SORTING,
      'hide' => ($this->settings[0]['sort_hide_0']? '0' : '1'), 'name' => '0'];
    $sorting[$orders['sort_pos_1']] = ['title' => TEXT_BY_MODEL . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_1']? '0' : '1'), 'name' => '1'];
    $sorting[$orders['sort_pos_2']] = ['title' => TEXT_BY_MODEL . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_2']? '0' : '1'), 'name' => '2'];
    $sorting[$orders['sort_pos_3']] = ['title' => TEXT_BY_NAME . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_3']? '0' : '1'), 'name' => '3'];
    $sorting[$orders['sort_pos_4']] = ['title' => TEXT_BY_NAME . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_4']? '0' : '1'), 'name' => '4'];
    $sorting[$orders['sort_pos_5']] = ['title' => TEXT_BY_MANUFACTURER . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_5']? '0' : '1'), 'name' => '5'];
    $sorting[$orders['sort_pos_6']] = ['title' => TEXT_BY_MANUFACTURER . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_6']? '0' : '1'), 'name' => '6'];
    $sorting[$orders['sort_pos_7']] = ['title' => TEXT_BY_PRICE . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_7']? '0' : '1'), 'name' => '7'];
    $sorting[$orders['sort_pos_8']] = ['title' => TEXT_BY_PRICE . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_8']? '0' : '1'), 'name' => '8'];
    $sorting[$orders['sort_pos_9']] = ['title' => TEXT_BY_QUANTITY . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_9']? '0' : '1'), 'name' => '9'];
    $sorting[$orders['sort_pos_10']] = ['title' => TEXT_BY_QUANTITY . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_10']? '0' : '1'), 'name' => '10'];
    $sorting[$orders['sort_pos_11']] = ['title' => TEXT_BY_WEIGHT . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_11']? '0' : '1'), 'name' => '11'];
    $sorting[$orders['sort_pos_12']] = ['title' => TEXT_BY_WEIGHT . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_12']? '0' : '1'), 'name' => '12'];
    
    $sorting[$orders['sort_pos_13']] = ['title' => TEXT_BY_DATE . ' <span class="ico">&#xe996;</span>',
      'hide' => ($this->settings[0]['sort_hide_13']? '0' : '1'), 'name' => '13'];
    
    $sorting[$orders['sort_pos_14']] = ['title' => TEXT_BY_DATE . ' <span class="ico">&#xe995;</span>',
      'hide' => ($this->settings[0]['sort_hide_14']? '0' : '1'), 'name' => '14'];
    ksort($sorting);

    return $this->render('listing-functionality.tpl', [
      'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings,
      'visibility' => $this->visibility,
      'sorting' => $sorting,
    ]);
  }
}