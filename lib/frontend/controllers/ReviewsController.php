<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;

/**
 * Site controller
 */
class ReviewsController extends Sceleton
{

    public function actionIndex()
    {

        global $languages_id, $customer_id, $messageStack, $breadcrumb;
        global $currency_id, $customer_groups_id;

        $params = Yii::$app->request->get();


        $rating_query = tep_db_query("select count(*) as count, AVG(reviews_rating) as average from " . TABLE_REVIEWS . " where products_id = '" . (int)$params['products_id'] . "' and status");
        $rating = tep_db_fetch_array($rating_query);

        $reviews = '';
        if ($params['products_id']) {
            $product_name = \common\helpers\Product::get_products_name((int)$params['products_id']);
            if ( $product_name && \common\helpers\Product::check_product((int)$params['products_id']) ) {
                $breadcrumb->add($product_name, tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . (int)$params['products_id']));
            }
            $breadcrumb->add(NAVBAR_TITLE, tep_href_link('reviews',''));

            $reviews_query_raw = "select r.reviews_id, rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where status and r.products_id = '" . (int)$params['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' order by r.reviews_id desc";

            $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
            $products_query = tep_db_query($reviews_split->sql_query);

            if (tep_db_num_rows($products_query) > 0) {

                $reviews = array();

                $reviews_query = tep_db_query($reviews_split->sql_query);
                while ($review = tep_db_fetch_array($reviews_query)) {
                    $review['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $params['products_id'] . '&reviews_id=' . $review['reviews_id']);
                    $review['date'] = \common\helpers\Date::date_long($review['date_added']);
                    $reviews[] = $review;
                }

                $links = $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y', 'ajax', 't', 'filter', 'split')), 'reviews');

            }


            return $this->render('index.tpl', [
                'reviews' => $reviews,
                'link_write' => tep_href_link('reviews/write', 'products_id='. $params['products_id']),
                'rating' => round($rating['average']),
                'count' => $rating['count'],
                'number_of_rows' => $reviews_split->number_of_rows,
                'links' => $links,
                'counts' => $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS),
                'message_review' => $messageStack->size('review')>0?$messageStack->output('review'):'',
                'logged' => ($customer_id ? 1 : '')
            ]);
        } else {
            $breadcrumb->add(NAVBAR_TITLE, tep_href_link('reviews/info',\common\helpers\Output::get_all_get_params()));

            $products_join = '';
            if ( \common\classes\platform::activeId() ) {
              $products_join .=
                " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
                " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
                " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
            }

            if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
                $reviews_query_raw = "select distinct r.reviews_id, ".
                  "rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, ".
                  "p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ".
                  "p.products_image, p.products_price, p.products_tax_class_id, ".
                  "r.customers_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'  " . " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' where status and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and p.products_id = r.products_id and r.reviews_id = rd.reviews_id  " . "  and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and rd.languages_id = '" . (int)$languages_id . "' and pd.affiliate_id = 0 order by r.reviews_id DESC";
            }else{
                $reviews_query_raw = "select distinct r.reviews_id, ".
                  "rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, ".
                  "p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ".
                  "p.products_image, p.products_price, p.products_tax_class_id, ".
                  "r.customers_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."' and pd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'  " . " where status and p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = r.products_id and r.reviews_id = rd.reviews_id and p.products_id = pd.products_id  " . " and pd.language_id = '" . (int)$languages_id . "' and rd.languages_id = '" . (int)$languages_id . "' and pd.affiliate_id = 0 order by r.reviews_id DESC";
            }

            $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
            $products_query = tep_db_query($reviews_split->sql_query);

            $links = '';
            if (tep_db_num_rows($products_query) > 0) {

                $reviews = \frontend\design\Info::getProducts($products_query);
                foreach( $reviews as $_idx=>$review ) {
                    $reviews[$_idx]['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $review['products_id'] . '&reviews_id=' . $review['reviews_id']);
                    $reviews[$_idx]['date'] = \common\helpers\Date::date_long($review['date_added']);
                }

                $links = $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y', 'ajax', 't', 'filter', 'split')), 'reviews');

            }

            return $this->render('index.tpl', [
              'reviews' => $reviews,
              'count' => $rating['count'],
              'number_of_rows' => $reviews_split->number_of_rows,
              'links' => $links,
              'counts' => $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS),

              'page_review' => 1,
              'logged' => ($customer_id ? 1 : '')
            ]);
        }
    }


    public function actionWrite()
    {
        global $navigation;
        $params = Yii::$app->request->get();

        if (!tep_session_is_registered('customer_id')) {
            if (\Yii::$app->request->isAjax){
                $navigation->set_snapshot();
                echo '<script>window.location.href = "' . tep_href_link(FILENAME_LOGIN,'','SSL') . '";</script>';
                die;
            }else {
                $navigation->set_snapshot();
                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
            }
        }

        if ( empty($params['products_id']) ) die;

        global $messageStack, $customer_id;

        $error = false;
        $rating = '';
        $review = '';

        if (isset($params['action']) && ($params['action'] == 'process') && tep_session_is_registered('customer_id')) {
            $customer_query = tep_db_query(
              "select customers_firstname, customers_lastname ".
              "from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'"
            );
            $customer = tep_db_fetch_array($customer_query);

            $rating = tep_db_prepare_input($_POST['rating']);
            $review = tep_db_prepare_input(Yii::$app->request->post('review'));


            if (($rating < 1) || ($rating > 5)) {
                $error = true;

                $messageStack->add('review', TEXT_REVIEW_RATING_ERROR);
            }

            if (strlen($review) < REVIEW_TEXT_MIN_LENGTH) {
                $error = true;

                $messageStack->add('review', TEXT_REVIEW_TEXT_ERROR);
            }

            if ($error == false)
            {
                tep_db_perform(TABLE_REVIEWS, array(
                  'products_id' => $params['products_id'],
                  'customers_id' => $customer_id,
                  'customers_name' => $customer['customers_firstname'] . ' ' . $customer['customers_lastname'],
                  'reviews_rating' => $rating,
                  'date_added' => 'now()',
                ));
                $insert_id = tep_db_insert_id();

                tep_db_perform(TABLE_REVIEWS_DESCRIPTION, array(
                  'reviews_id' => $insert_id,
                  'languages_id' => (int)$_SESSION['languages_id'],
                  'reviews_text' => $review,
                ));

                $messageStack->add_session('review',REVIEW_ADDED,'success');
                tep_redirect(tep_href_link('reviews/index', 'products_id=' . $params['products_id']));
            }
        }

        if (\Yii::$app->request->isAjax) {
            $message_review = '';
            if ( $messageStack->size('review')>0 ) {
                $message_review = $messageStack->output('review');
            }
            return $this->render('write.tpl', [
              'message_review' => $message_review,
              'review_rate' => $rating,
              'review_text' => $review,
              'link' => tep_href_link('reviews'),
              'link_cancel' => tep_href_link('reviews/index', 'products_id=' . $params['products_id']),
              'link_write' => tep_href_link('reviews/write', 'products_id=' . $params['products_id'].'&action=process'),
              'products_id' => $params['products_id']
            ]);
            
        }else{
            return Yii::$app->runAction('catalog/product',['products_id'=>$params['products_id']]);
        }
    }


    public function actionInfo()
    {
        global $languages_id, $customer_id, $breadcrumb;
        $review = false;
        $reviews_id = intval(\Yii::$app->request->get('reviews_id',0));

        $get_review_r = tep_db_query(
          "SELECT rd.*, r.* ".
          "FROM ".TABLE_REVIEWS." r, ".TABLE_REVIEWS_DESCRIPTION." rd, ".TABLE_PRODUCTS." p ".
          "WHERE r.reviews_id = '".(int)$reviews_id."' ".
          " AND rd.reviews_id = r.reviews_id ".
          ( tep_session_is_registered('customer_id') && $customer_id>0?" AND r.customers_id='".(int)$customer_id."'":" AND r.status=1").
          " AND p.products_id = r.products_id ".
          "ORDER BY IF(rd.languages_id='".(int)$languages_id."',0,1) ".
          "LIMIT 1"
        );
        if ( tep_db_num_rows($get_review_r) ) {
            $review = tep_db_fetch_array($get_review_r);

            $review['products_link'] = '';
            $review['products_active'] = false;
            if ( \common\helpers\Product::check_product($review['products_id']) ) {
                $review['products_active'] = true;
                $review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$review['products_id'],'');
            }
            $review['products_name'] = \common\helpers\Product::get_products_name($review['products_id']);
            $review['products_image'] = \common\classes\Images::getImage($review['products_id'], 'Small');
            $review['reviews_rating'];
            $review['date_added_str'] = \common\helpers\Date::date_short($review['date_added']);
            $review['date_added_formatted'] = sprintf(TEXT_REVIEW_DATE_ADDED,$review['date_added_str']);
            $review['reviewed_by_formatted'] = sprintf(TEXT_REVIEW_BY,$review['customers_name']);
            if ($review['status']){
                $review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
            }else{
                $review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
            }
            $review['review_owner_view'] = false;
            if ( tep_session_is_registered('customer_id') && $customer_id>0 && (int)$customer_id == (int)$review['customers_id'] ) {
                $review['review_owner_view'] = true;
            }

            $breadcrumb->add($review['products_name'], tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$review['products_id']));
            $breadcrumb->add(NAVBAR_TITLE, tep_href_link('reviews/info',\common\helpers\Output::get_all_get_params()));
        }

        $back_params = '';
        if ( is_array($review) && $review['products_active'] ) {
            $back_params = 'products_id='.$review['products_id'];
        }

        $back_link_href = tep_href_link('reviews/',$back_params,'NONSSL');
        $back_ctrl = tep_db_prepare_input(\Yii::$app->request->get('back',''));
        if ( $back_ctrl=='account' ) {
            $back_link_href = tep_href_link(FILENAME_ACCOUNT,'','SSL');
        }elseif ( strpos($back_ctrl, 'account-products-reviews')===0 ) {
            $back_params = '';
            if ( preg_match('/^account-products-reviews-(\d+)/', $back_ctrl, $m) ) {
                $back_params = 'page='.max(1,(int)$m[1]);
            }
            $back_link_href = tep_href_link('account/products-reviews',$back_params,'SSL');
        }

        return $this->render('info.tpl', [
          'HEADING_TITLE' => is_array($review)?sprintf(HEADING_TITLE_S, $review['products_name']):HEADING_TITLE,
          'review' => $review,
          'back_link_href' => $back_link_href,
        ]);
    }

}
