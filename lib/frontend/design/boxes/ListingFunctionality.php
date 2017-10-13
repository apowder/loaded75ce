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
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class ListingFunctionality extends Widget
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
    if ( !isset($this->params['listing_split']) || !is_object($this->params['listing_split']) || !is_a($this->params['listing_split'], 'frontend\design\splitPageResults' ) ) {
      return '';
    }
    $listing_split = $this->params['listing_split'];
    /**
     * @var $listing_split SplitPageResults
     */
    if ($listing_split->number_of_rows > 0){

      $sorting_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('sort')));

      if ( isset($this->params['sorting_options']) && is_array($this->params['sorting_options']) ) {
        $sorting = $this->params['sorting_options'];
      }else{
        $sorting = array();
      }





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
/*echo '<pre>';
var_dump($this->settings);
echo '</pre>';die;*/
      $sorting = array();
      if (!$this->settings[0]['sort_hide_0']) {
        $sorting[$orders['sort_pos_0']] = ['title' => TEXT_NO_SORTING, 'id' => '0'];
      }
      if (!$this->settings[0]['sort_hide_1']) {
        $sorting[$orders['sort_pos_1']] = ['title' => '&#xe996; ' . TEXT_BY_MODEL, 'id' => 'ma'];
      }
      if (!$this->settings[0]['sort_hide_2']) {
        $sorting[$orders['sort_pos_2']] = ['title' => '&#xe995; ' . TEXT_BY_MODEL, 'id' => 'md'];
      }
      if (!$this->settings[0]['sort_hide_3']) {
        $sorting[$orders['sort_pos_3']] = ['title' => '&#xe996; ' . TEXT_BY_NAME, 'id' => 'na'];
      }
      if (!$this->settings[0]['sort_hide_4']) {
        $sorting[$orders['sort_pos_4']] = ['title' => '&#xe995; ' . TEXT_BY_NAME, 'id' => 'nd'];
      }
      if (!$this->settings[0]['sort_hide_5']) {
        $sorting[$orders['sort_pos_5']] = ['title' => '&#xe996; ' . TEXT_BY_MANUFACTURER, 'id' => 'ba'];
      }
      if (!$this->settings[0]['sort_hide_6']) {
        $sorting[$orders['sort_pos_6']] = ['title' => '&#xe995; ' . TEXT_BY_MANUFACTURER, 'id' => 'bd'];
      }
      if (!$this->settings[0]['sort_hide_7']) {
        $sorting[$orders['sort_pos_7']] = ['title' => '&#xe996; ' . TEXT_BY_PRICE, 'id' => 'pa'];
      }
      if (!$this->settings[0]['sort_hide_8']) {
        $sorting[$orders['sort_pos_8']] = ['title' => '&#xe995; ' . TEXT_BY_PRICE, 'id' => 'pd'];
      }
      if (!$this->settings[0]['sort_hide_9']) {
        $sorting[$orders['sort_pos_9']] = ['title' => '&#xe996; ' . TEXT_BY_QUANTITY, 'id' => 'qa'];
      }
      if (!$this->settings[0]['sort_hide_10']) {
        $sorting[$orders['sort_pos_10']] = ['title' => '&#xe995; ' . TEXT_BY_QUANTITY, 'id' => 'qd'];
      }
      if (!$this->settings[0]['sort_hide_11']) {
        $sorting[$orders['sort_pos_11']] = ['title' => '&#xe996; ' . TEXT_BY_WEIGHT, 'id' => 'wa'];
      }
      if (!$this->settings[0]['sort_hide_12']) {
        $sorting[$orders['sort_pos_12']] = ['title' => '&#xe995; ' . TEXT_BY_WEIGHT, 'id' => 'wd'];
      }
      if (!$this->settings[0]['sort_hide_13']) {
        $sorting[$orders['sort_pos_13']] = ['title' => '&#xe996; ' . TEXT_BY_DATE, 'id' => 'da'];
      }
      if (!$this->settings[0]['sort_hide_14']) {
        $sorting[$orders['sort_pos_14']] = ['title' => '&#xe995; ' . TEXT_BY_DATE, 'id' => 'dd'];
      }
      ksort($sorting);




      /*$sorting = array();
      $sorting[] = array('id' => '0', 'title' => TEXT_NO_SORTING);
      if (PRODUCT_LIST_MODEL) {
        $sorting[] = array('id' => 'ma', 'title' => TEXT_BY_MODEL . ' &darr;');
        $sorting[] = array('id' => 'md', 'title' => TEXT_BY_MODEL . ' &uarr;');
      }
      if (PRODUCT_LIST_NAME) {
        $sorting[] = array('id' => 'na', 'title' => TEXT_BY_NAME . ' &darr;');
        $sorting[] = array('id' => 'nd', 'title' => TEXT_BY_NAME . ' &uarr;');
      }
      if (PRODUCT_LIST_MANUFACTURER) {
        $sorting[] = array('id' => 'ba', 'title' => TEXT_BY_MANUFACTURER . ' &darr;');
        $sorting[] = array('id' => 'bd', 'title' => TEXT_BY_MANUFACTURER . ' &uarr;');
      }
      if (PRODUCT_LIST_PRICE) {
        $sorting[] = array('id' => 'pa', 'title' => TEXT_BY_PRICE . ' &darr;');
        $sorting[] = array('id' => 'pd', 'title' => TEXT_BY_PRICE . ' &uarr;');
      }
      if (PRODUCT_LIST_QUANTITY) {
        $sorting[] = array('id' => 'qa', 'title' => TEXT_BY_QUANTITY . ' &darr;');
        $sorting[] = array('id' => 'qd', 'title' => TEXT_BY_QUANTITY . ' &uarr;');
      }
      if (PRODUCT_LIST_WEIGHT) {
        $sorting[] = array('id' => 'wa', 'title' => TEXT_BY_WEIGHT . ' &darr;');
        $sorting[] = array('id' => 'wd', 'title' => TEXT_BY_WEIGHT . ' &uarr;');
      }*/


      $searchResults = Info::widgetSettings('Listing', 'items_on_page', 'products');
      if (!$searchResults) $searchResults = SEARCH_RESULTS_1;

      $view = array();
      $view[] = $searchResults * 1;
      $view[] = $searchResults * 2;
      $view[] = $searchResults * 4;
      $view[] = $searchResults * 8;

      if (Info::widgetSettings('Listing', 'listing_type') != 'no') $grid_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . '&gl=grid');
      if (Info::widgetSettings('Listing', 'listing_type_rows') != 'no') $list_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . '&gl=list');
      if (Info::widgetSettings('Listing', 'listing_type_b2b')) $b2b_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . '&gl=b2b');
      Info::sortingId();
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/listing-functionality.tpl',
        'params' => [
          'view' => $view,
          'view_id' => $_SESSION['max_items'],
          'sorting_link' => $sorting_link,
          'sorting' => $sorting,
          'sorting_id' => $this->params['sorting_id'],
          'hidden_fields' => \common\helpers\Output::get_all_get_params(array('sort','max_items'), true),
          'grid_link' => $grid_link,
          'list_link' => $list_link,
          'b2b_link' => $b2b_link,
          'gl' => $_SESSION['gl'],
          'fbl' => Info::widgetSettings('Listing', 'fbl'),
          'compare_button' => $this->settings[0]['compare_button']
        ]
      ]);
    }



  }
}