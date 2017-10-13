<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;

class Reviews extends Widget
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
    $params = Yii::$app->request->get();

    if ($params['products_id']) {


      $reviews_query_raw = "select r.reviews_id, rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where status and r.products_id = '" . (int)$params['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' order by r.reviews_id desc";

      $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
      $products_query = tep_db_query($reviews_split->sql_query);

      if (tep_db_num_rows($products_query) > 0) {

        $reviews = array();

        $reviews_query = tep_db_query($reviews_split->sql_query);
        while ($review = tep_db_fetch_array($reviews_query)) {
          $review['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $params['products_id'] . '&reviews_id=' . $review['reviews_id']);
          $review['date'] = \common\helpers\Date::date_long($review['date_added']);
          $review['date_schema'] = \common\helpers\Date::date_long($review['date_added'], '%Y-%m-%d');
          $reviews[] = $review;
        }

        $links = $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y', 'ajax', 't', 'filter', 'split')), 'catalog/product');

      }

      $review_write_now = 0;
      $reviews_link = tep_href_link('reviews', 'products_id=' . $params['products_id']);
      if ( Yii::$app->request->getPathInfo()=='reviews/write' ) {
        $reviews_link = tep_href_link('reviews/write', 'products_id=' . $params['products_id']);;
        $review_write_now = 1;
      }

      return IncludeTpl::widget(['file' => 'boxes/product/reviews.tpl', 'params' => [
        'reviews' => $reviews,
        'reviews_link' => $reviews_link,
        'review_write_now' => $review_write_now,
        'number_of_rows' => $reviews_split->number_of_rows,
        'links' => $links,
        'counts' => $reviews_split->display_count(Yii::t('app', 'Items %s to %s of %s total'))
      ]]);
    } else {
      return '';
    }
  }
}