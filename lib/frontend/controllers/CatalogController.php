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
use common\classes\Images;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use frontend\design\SplitPageResults;
use common\models\PropertiesTypes;
use frontend\design\ListingSql;
use frontend\design\boxes\Listing;
use common\classes\design;

/*use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;*/

/**
 * Site controller
 */
class CatalogController extends Sceleton
{

    public function actionIndex()
    {
        global $_SESSION, $languages_id, $current_category_id;
        global $breadcrumb;

        if ($current_category_id > 0) {

            // Get the category name and description
            $category_query = tep_db_query("select c.categories_id as id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name, if(length(cd1.categories_heading_title), cd1.categories_heading_title, cd.categories_heading_title) as categories_heading_title, if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) as categories_description, c.categories_image from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id = '" . (int)$languages_id . "' and cd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "' where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "' AND c.categories_status=1");
            if ( tep_db_num_rows($category_query)==0 ) {
                throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
            }
            $category = tep_db_fetch_array($category_query);
            if ( is_array($category) ) {
                $parent_categories = array($category['id']);
                \common\helpers\Categories::get_parent_categories($parent_categories, $parent_categories[0]);
                $bcPath = '';
                foreach(array_reverse($parent_categories) as $_cid){
                    $bcPath.=( !empty($bcPath)?'_':'').$_cid;
                    $breadcrumb->add(\common\helpers\Categories::get_categories_name($_cid), tep_href_link('catalog/index', 'cPath='.$bcPath, 'NONSSL'));
                }
            }

        } elseif ($_GET['manufacturers_id'] > 0) {

            // Get the manufacturer name and image
            $manufacturer_query = tep_db_query("select m.manufacturers_id as id, m.manufacturers_name as categories_name, '' as categories_description, m.manufacturers_image as categories_image from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
            $category = tep_db_fetch_array($manufacturer_query);

            $breadcrumb->add($category['categories_name'], tep_href_link('catalog/index', 'manufacturers_id='.$category['id'], 'NONSSL'));

        }else{
            return Yii::$app->runAction('index/index');
        }

        $category['img'] = Yii::$app->request->baseUrl . '/images/' . $category['categories_image'];
        if (!is_file(Yii::getAlias('@webroot') . '/images/' . $category['categories_image'])){
            $category['img'] = 'no';
        }
        
        

        $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and categories_status = 1");
        $category_parent = tep_db_fetch_array($category_parent_query);
        $category_p = ($_GET['manufacturers_id'] ? '0' : $category_parent['total'] );

        $sorting = array();
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
        }

        $search_results = Info::widgetSettings('Listing', 'items_on_page');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $view = array();
        $view[] = $search_results * 1;
        $view[] = $search_results * 2;
        $view[] = $search_results * 4;
        $view[] = $search_results * 8;

        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id'),
          'this_filename' => 'catalog',
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }

        if ($category_p > 0) {
            if ($_GET['page_name']) {
                $page_name = $_GET['page_name'];
            } else {
                $page_name = 'categories';
            }
        } else {

            $query = tep_db_query("
select aps.setting_value as rule, ap.setting_value as page_title
from " . TABLE_THEMES_SETTINGS . " ap left join " . TABLE_THEMES_SETTINGS . " aps on ap.setting_value = aps.setting_name
where 
    ap.theme_name = '" . tep_db_input(THEME_NAME) . "' and 
    aps.theme_name = '" . tep_db_input(THEME_NAME) . "' and 
    ap.setting_group = 'added_page' and 
    aps.setting_group = 'added_page_settings' and 
    ap.setting_name = 'products'");

            $arr = array();
            while ($page = tep_db_fetch_array($query)){
                $p_name = design::pageName($page['page_title']);
                if (!isset($arr[$p_name])) $arr[$p_name] = true;

                if ($page['rule'] == 'no_filters'){
                    $filters = \common\helpers\Categories::get_category_filters($current_category_id);
                    if (count($filters) > 0) {
                        $arr[$p_name] = false;
                    }
                }

            }
            foreach ($arr as $pn => $set){
                if ($set) $page_name = $pn;
            }

            if ($_GET['page_name']){
                $page_name = $_GET['page_name'];
            } elseif (!$page_name || Info::isAdmin()) {
                $page_name = 'products';
            }
        }
        $this->view->page_name = $page_name;
        return $this->render('index.tpl', [
          'category' => $category,
          'category_parent' => $category_p,
          'params' => $params,
          'page_name' => $page_name
        ]);
    }


    public function actionProduct()
    {
        global $messageStack, $breadcrumb, $cPath_array, $languages_id, $customer_id;
        
        
        $params = Yii::$app->request->get();

        if (isset($_SESSION['viewed_products'])){
            $viewed = explode(",", $_SESSION['viewed_products']);
            if (!in_array($params['products_id'], $viewed)) {
                $_SESSION['viewed_products'] .= ',' . $params['products_id'];
            } else {
                $key = array_search($params['products_id'], $viewed);
                unset($viewed[$key]);
                $_SESSION['viewed_products'] = implode(',', $viewed) . ',' . $params['products_id'];
            }
        } else {
            $_SESSION['viewed_products'] = $params['products_id'];
        }

		$check_status = 1;
		if (Info::isAdmin()){
			$check_status = 0;
		}
        if ( !isset($params['products_id']) || !\common\helpers\Product::check_product($params['products_id'], $check_status) ) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $message = '';
        if ($_SESSION['product_info']) {
            $message = $_SESSION['product_info'];
            unset($_SESSION['product_info']);
        }
        $review_write_now = 0;
        if ( Yii::$app->request->getPathInfo()=='reviews/write' ) {
            $review_write_now = 1;
        }else{
            if (!is_array($cPath_array)) {
                $cPath_array = explode("_", \common\helpers\Product::get_product_path($params['products_id']));
            }
            if (isset($cPath_array)) {
                for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
                    $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$languages_id . "'");
                    if (tep_db_num_rows($categories_query) > 0) {
                        $categories = tep_db_fetch_array($categories_query);
                        //$breadcrumb->add($categories['categories_name'], tep_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
						$breadcrumb->add($categories['categories_name'], tep_href_link('catalog/index', 'cPath='.implode('_', array_slice($cPath_array, 0, ($i+1)))));
                    } else {
                        break;
                    }
                }
            }
            $breadcrumb->add(\common\helpers\Product::get_products_name($params['products_id']),tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$params['products_id']));
        }

        if (Info::checkProductInCart($params['products_id'])){
            $message .= '<div>' . TEXT_ADDED_1 . ' <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . TEXT_ADDED_2 . '</a>. <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING) . '">' . TEXT_ADDED_3 . '</a>. ' . TEXT_ADDED_4 . '</div>';
        }
        
        $product_in_orders = 0;
        if (tep_session_is_registered('customer_id')){
            $query = tep_db_query("select op.products_id from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op where o.orders_id = op.orders_id and o.customers_id = " . $customer_id . "");
            while ($item = tep_db_fetch_array($query)){
                if ($item['products_id'] ==  $params['products_id']){
                    $product_in_orders++;
                    if ($product_in_orders > 1) break;
                }
            }
        }
        if ($product_in_orders == 1){
            $message .= '<div>' . TEXT_YOU_BOUGHT_THIS_ITEM . '</div>';
        }
        if ($product_in_orders == 2){
            $message .= '<div>' . TEXT_PURCHASED_MORE_THAN . '</div>';
        }

        $page_name = \frontend\design\Product::pageName($params['products_id']);
        $this->view->page_name = $page_name;
        return $this->render('product.tpl', [
          'action' => tep_href_link('catalog/product', \common\helpers\Output::get_all_get_params(array('action')) . 'action=add_product'),
          'products_id' => $params['products_id'],
          'products_prid' => \common\helpers\Inventory::get_prid($params['products_id']),
          'review_write_now' => $review_write_now,
          'message' => $message,
          'page_name' => $page_name
        ]);
    }

    public function actionProductAttributes()
    {
        global $languages_id, $language, $currencies, $cart;

        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->get());
        $products_id = tep_db_prepare_input(Yii::$app->request->get('products_id'));
        $attributes = tep_db_prepare_input(Yii::$app->request->get('id', array()));
		
		$details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);

        if (Yii::$app->request->isAjax) {
			$details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
			$details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => true]]);
            return json_encode($details);
        } else {
            if (count($details['attributes_array']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => false,]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductNotify()
    {
        global $languages_id, $language, $customer_id;

        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->get());
        // Inventory widget bof
        if (strpos($params['uprid'], '{') !== false) {
          $attrib = array();
          $ar = preg_split('/[\{\}]/', $params['uprid']);
          for ($i=1; $i<sizeof($ar); $i=$i+2) {
            if (isset($ar[$i+1])) {
              $attrib[$ar[$i]] = $ar[$i+1];
            }
          }
          $params['id'] = $attrib;
        }
        // Inventory widget eof
        $uprid = tep_db_input(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($params['products_id'], $params['id'])));
        if (empty($params['id'])) {
            $check_item = tep_db_fetch_array(tep_db_query("select products_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '{$uprid}' limit 1"));
            $out_of_stock = $check_item['products_id'] && !($check_item['products_quantity'] > 0);
            $item_found = $check_item['products_id'];
        } else {
            $check_item = tep_db_fetch_array(tep_db_query("select inventory_id, products_quantity from " . TABLE_INVENTORY . " where products_id like '{$uprid}' limit 1"));
            $out_of_stock = $check_item['inventory_id'] && !($check_item['products_quantity'] > 0);
            $item_found = $check_item['inventory_id'];
        }
        if ($out_of_stock) {
            $products_notify_name = tep_db_input(tep_db_prepare_input($params['name']));
            $products_notify_email = tep_db_input(tep_db_prepare_input($params['email']));
            $check_notify = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_products_id like '{$uprid}' and products_notify_email = '{$products_notify_email}' limit 1"));
            if (!$check_notify['products_notify_id']) {
                tep_db_query("insert into products_notify set products_notify_products_id = '{$uprid}', products_notify_email = '{$products_notify_email}', products_notify_name = '{$products_notify_name}', products_notify_customers_id = '{$customer_id}', products_notify_date = now(), products_notify_sent = null");
                return YOU_WILL_BE_NOTIFIED;
            } else {
                return YOU_ALREADY_GOT_NOTIFY;
            }
        } else {
            return ($item_found ? ITEM_IS_IN_STOCK : ITEM_NOT_FOUND);
        }
    }

    public function actionProductRequestForQuote()
    {
        global $languages_id, $messageStack, $customer_id;

        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->post());
        if ( tep_session_is_registered('customer_id') ) {
          $customer_info = tep_db_fetch_array(tep_db_query(
            "SELECT customers_firstname, customers_email_address FROM ".TABLE_CUSTOMERS." WHERE customers_id='".(int)$_SESSION['customer_id']."'"
          ));
          $customers_name = $customer_info['customers_firstname'];
          $customers_email = $customer_info['customers_email_address'];
        }else{
          $customers_name = $params['name'];
          $customers_email = $params['email'];
        }

        $check_error = false;
        if (strlen($customers_name) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $check_error = true;
          $messageStack->add('rfq_send', sprintf(NAME_IS_TOO_SHORT, ENTRY_FIRST_NAME_MIN_LENGTH) );
        }
        if ( empty($customers_email) || !\common\helpers\Validations::validate_email($customers_email) ) {
          $check_error = true;
          $messageStack->add('rfq_send', ENTER_VALID_EMAIL );
        }
        if ( empty($params['message']) ) {
          $check_error = true;
          $messageStack->add('rfq_send', REQUEST_MESSAGE_IS_TOO_SHORT );
        }
        if ( $check_error ) {
          return $messageStack->output('rfq_send');
        }else {
          $uprid = tep_db_input(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($params['products_id'], $params['id'])));
          $product_name = \common\helpers\Product::get_products_name($uprid);
          if ( strpos($uprid,'{')!==false ) {
            $check_item = tep_db_fetch_array(tep_db_query("select products_name from " . TABLE_INVENTORY . " where products_id like '{$uprid}' limit 1"));
            if( !empty($check_item['products_name']) ) {
              $product_name = $check_item['products_name'];
            }
          }


          $email_params = array();
          $email_params['STORE_NAME'] = STORE_NAME;
          $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
          $email_params['CUSTOMER_NAME'] = $customers_name;
          $email_params['CUSTOMER_EMAIL'] = $customers_email;
          $email_params['PRODUCT_NAME'] = $product_name;
          $email_params['PRODUCT_URL'] = tep_href_link('catalog/product', 'products_id=' . $uprid);
          $email_params['REQUEST_MESSAGE'] = $params['message'];

          list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Request for quote', $email_params);

          \common\helpers\Mail::send(
            STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS,
            $email_subject, $email_text,
            $customers_name, $customers_email
          );

          return REQUEST_FOR_QUOTE_MESSAGE_SENT;
        }
    }

    public function actionSearch()
    {

        return '';
    }


    public function actionSearchSuggest()
    {
        global $_SESSION, $languages_id, $customer_groups_id, $currency_id;

        $response = array();

        if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
            $keywords = tep_db_prepare_input($_GET['keywords']);
            $_SESSION['keywords'] = $keywords;
            //Add slashes to any quotes to avoid SQL problems.
            // $search = preg_replace("/\//",'',tep_db_input(tep_db_prepare_input($_GET['keywords'])));  //???
            $search = $keywords;

            $where_str_categories = "";
            $where_str_gapi ="";
            $where_str_products = "";
            $where_str_manufacturers = "";
            $where_str_information = "";
            $replace_keywords = array();

            if (\common\helpers\Output::parse_search_string($search, $search_keywords, false)) {
                $where_str_categories .= " and (";
                $where_str_gapi .= " and (";
                $where_str_products .= " and (";
                $where_str_manufacturers .= " (";
                $where_str_information .= " and (";
                for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
                    switch ($search_keywords[$i]) {
                        case '(':
                        case ')':
                        case 'and':
                        case 'or':
                            $where_str_gapi .= " " . $search_keywords[$i] . " ";
                            $where_str_categories .= " " . $search_keywords[$i] . " ";
                            $where_str_products .= " " . $search_keywords[$i] . " ";
                            $where_str_manufacturers .= " " . $search_keywords[$i] . " ";
                            $where_str_information .= " " . $search_keywords[$i] . " ";
                            break;
                        default:

                            $keyword = tep_db_prepare_input($search_keywords[$i]);
                            $replace_keywords[] = $search_keywords[$i];
                            $where_str_gapi .=" gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' or  gs.gapi_keyword like '%" .tep_db_input($keyword) . "%' ";

                            $where_str_products .= "(if(length(pd1.products_name), pd1.products_name, pd.products_name) like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%' " . (SEARCH_IN_DESCRIPTION == 'True' ? " or if(length(pd1.products_description), pd1.products_description, pd.products_description) like '%" . tep_db_input($keyword) . "%' " : '') . " or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . tep_db_input($keyword) . "%' or  gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' )";
                            $where_str_categories .= "(if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) like '%" . tep_db_input($keyword) . "%' or if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) like '%" . tep_db_input($keyword) . "%')";

                            $where_str_manufacturers .= "(manufacturers_name like '%" . tep_db_input($keyword) . "%')";

                            $where_str_information .= "(if(length(i1.info_title), i1.info_title, i.info_title) like '%" . tep_db_input($keyword) . "%' or if(length(i1.description), i1.description, i.description) like '%" . tep_db_input($keyword) . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . tep_db_input($keyword) . "%')";
                            break;
                    }
                }
                $where_str_categories .= ") ";
                $where_str_gapi .= ") ";
                $where_str_products .= ") ";
                $where_str_manufacturers .= ") ";
                $where_str_information .= ") ";

            } else {
                $replace_keywords[] = $search;
                $where_str_gapi .= "and gs.gapi_keyword like ('%" . tep_db_input($search) . "%')))";
                $where_str_products .= "and (if(length(pd1.products_name), pd1.products_name like ('%" . tep_db_input($search) . "%'), pd.products_name like ('%" . tep_db_input($search) . "%')) " . (SEARCH_IN_DESCRIPTION == 'True' ? " or if(length(pd1.products_description), pd1.products_description, pd.products_description) like '%" . tep_db_input($search) . "%' " : '') . " or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . tep_db_input($search) . "%'  or gs.gapi_keyword like ('%" . tep_db_input($search) . "%'))";
                $where_str_categories .= "and (if(length(cd1.categories_name), cd1.categories_name like ('%" . tep_db_input($search) . "%'), cd.categories_name like ('%" . tep_db_input($search) . "%')) or if(length(cd1.categories_description), cd1.categories_description like ('%" . tep_db_input($search) . "%'), cd.categories_description like ('%" . tep_db_input($search) . "%'))  )";
                $where_str_manufacturers .= " (manufacturers_name like '%" . tep_db_input($search) . "%')";
                $where_str_information .= "and (if(length(i1.info_title), i1.info_title, i.info_title) like '%" . tep_db_input($search) . "%' or if(length(i1.description), i1.description, i.description) like '%" . tep_db_input($search) . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . tep_db_input($search) . "%')";
            }

            $from_str = "select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name,  (if(length(cd1.categories_name), if(position('" . tep_db_input($search) . "' IN cd1.categories_name), position('" . tep_db_input($search) . "' IN cd1.categories_name), 100), if(position('" . tep_db_input($search) . "' IN cd.categories_name), position('" . tep_db_input($search) . "' IN cd.categories_name), 100))) as pos, 1 as is_category  from " . TABLE_CATEGORIES . " c " . ($_SESSION['affiliate_ref']>0?" LEFT join " . TABLE_CATEGORIES_TO_AFFILIATES . " c2a on c.categories_id = c2a.categories_id  and c2a.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "' ":'') . " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . $languages_id ."' and cd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "', " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = 1 " . ($_SESSION['affiliate_ref']>0?" and c2a.affiliate_id is not null ":'') . " and cd.affiliate_id = 0 and cd.categories_id = c.categories_id and cd.language_id = '" . $languages_id . "' " . $where_str_categories . " and c.quick_find = 1 order by pos limit 0, 3" ;

            $products_join = '';
            if ( \common\classes\platform::activeId() ) {
              $products_join .=
                " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
                " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
                " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
            }

            $sql_gapi = "
      select  distinct p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, m.manufacturers_name,
          (if(length(pd1.products_name),
            if(position('" . tep_db_input($search) . "' IN pd1.products_name),
              position('" . tep_db_input($search) . "' IN pd1.products_name),
              100
            ),
            if(position('" . tep_db_input($search) . "' IN pd.products_name),
              position('" . tep_db_input($search) . "' IN pd.products_name),
              100
            )
          )) as pos, 0 as is_category,
		  p.products_image
      from   " . TABLE_PRODUCTS . " p {$products_join}
          left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."'
                                              and pd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'
          left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . $customer_groups_id . "'
                                          and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'
		left join gapi_search_to_products gsp on p.products_id = gsp.products_id
		left join gapi_search gs on gsp.gapi_id = gs.gapi_id
        left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id ,
        " . TABLE_PRODUCTS_DESCRIPTION . " pd
    where   p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "
    " . ($_SESSION['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . "
      and   p.products_id = pd.products_id
      and   pd.language_id = '" . (int)$languages_id . "'
      and   if(pp.products_group_price is null, 1, pp.products_group_price != -1 )
      and   pd.affiliate_id = 0
    " . $where_str_gapi . "
    order by gsp.sort, pos
  ";

            $sql = "
      select  distinct p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, m.manufacturers_name,
          (if(length(pd1.products_name),
            if(position('" . tep_db_input($search) . "' IN pd1.products_name),
              position('" . tep_db_input($search) . "' IN pd1.products_name),
              100
            ),
            if(position('" . tep_db_input($search) . "' IN pd.products_name),
              position('" . tep_db_input($search) . "' IN pd.products_name),
              100
            )
          )) as pos, 0 as is_category,
		  p.products_image
      from   " . TABLE_PRODUCTS . " p {$products_join}
          left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."'
                                              and pd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'
          left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . $customer_groups_id . "'
                                          and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'
		LEFT JOIN ".TABLE_INVENTORY." i on p.products_id = i.prid
		left join gapi_search_to_products gsp on p.products_id = gsp.products_id
		left join gapi_search gs on gsp.gapi_id = gs.gapi_id
        left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id ,
        " . TABLE_PRODUCTS_DESCRIPTION . " pd
    where   p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "
    " . ($_SESSION['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . "
      and   p.products_id = pd.products_id
      and   pd.language_id = '" . (int)$languages_id . "'
      and   if(pp.products_group_price is null, 1, pp.products_group_price != -1 )
      and   pd.affiliate_id = 0
    " . $where_str_products . "
	group by p.products_id
    order by gapi_keyword desc, gsp.sort, products_name, pos
    limit   0, 10
  ";

            $sql_manufacturers = "select *, if(position('" . tep_db_input($search) . "' IN manufacturers_name), position('" . tep_db_input($search) . "' IN manufacturers_name), 100) as pos from " . TABLE_MANUFACTURERS . " where " . $where_str_manufacturers . " order by pos limit 0, 3";

            $sql_information = "select i.information_id, if(length(i1.info_title), i1.info_title, i.info_title) as info_title,  (if(length(i1.info_title), if(position('" . tep_db_input($search) . "' IN i1.info_title), position('" . tep_db_input($search) . "' IN i1.info_title), 100), if(position('" . tep_db_input($search) . "' IN i.info_title), position('" . tep_db_input($search) . "' IN i.info_title), 100))) as pos, 1 as is_category  from " . TABLE_INFORMATION . " i LEFT join " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id ".(\common\classes\platform::activeId()?" AND i1.platform_id='".\common\classes\platform::currentId()."' ":'')." and i1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "' and i1.languages_id='" . $languages_id ."'  where i.visible = 1 " . ($_SESSION['affiliate_ref']>0?" and i1.affiliate_id is not null ":'') . " and i.affiliate_id = 0 ".(\common\classes\platform::activeId()?" AND i.platform_id='".\common\classes\platform::currentId()."' ":'')." and i.languages_id = '" . $languages_id . "' " . $where_str_information . " order by pos limit 0, 3" ;

            /**
             * Set XML HTTP Header for ajax response
             */
            reset($replace_keywords);
            foreach ($replace_keywords as $k => $v)
            {
                $patterns[] = "/" . preg_quote($v) . "/i";
                $replace[] = str_replace('$', '/$/', '<span class="typed">' . $v . '</span>');
            }

            $re = array();
            foreach ($replace_keywords as $k => $v)
                $re[] = preg_quote($v);
            $re = "/(" . join("|", $re) . ")/i";
            $replace = '<span class="typed">\1</span>';


            $manufacturers_query = tep_db_query($sql_manufacturers);
            while ($manufacturers_array = tep_db_fetch_array($manufacturers_query)) {
                $response[] = array(
                    'type' => Yii::t('app', 'Manufacturers'),
                    'link' => tep_href_link('catalog', 'manufacturers_id=' . $manufacturers_array['manufacturers_id']),
                    'image' => DIR_WS_IMAGES . $manufacturers_array['manufacturers_image'],
                    'title' => preg_replace($re, $replace, strip_tags($manufacturers_array['manufacturers_name'])),
                );
            }

            $info_query = tep_db_query($sql_information);
            while ($info_array = tep_db_fetch_array($info_query)) {
                $response[] = array(
                  'type' => Yii::t('app', 'Information'),
                  'link' => tep_href_link('info', 'info_id=' . $info_array['information_id']),
                  'title' => preg_replace($re, $replace, strip_tags($info_array['info_title'])),
                );
            }

            $product_query = tep_db_query($sql);
            while($product_array = tep_db_fetch_array($product_query)) {
                $response[] = array(
                  'type' => Yii::t('app', 'Products'),
                  'link' => tep_href_link('catalog/product', 'products_id=' . $product_array['products_id']),
                  'image' => Images::getImageUrl($product_array['products_id'], 'Small'),
                  'title' => preg_replace($re, $replace, strip_tags($product_array['products_name'])),
                );

            }


        }

        return $this->render('search.tpl', ['list' => $response]);
    }

		public function actionSpecials(){
        global $languages_id, $currency_id, $customer_groups_id, $_SESSION, $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_SPECIALS));

        $sorting = array();
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
        }


        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;
        
        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_SPECIALS)), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id'),
          'this_filename' => FILENAME_SPECIALS,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
      return $this->render('specials.tpl', ['params' => ['params'=>$params]]);
		}

    public function actionFeatured_products(){
        global $languages_id, $currency_id, $customer_groups_id, $_SESSION, $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_FEATURED_PRODUCTS));

        $sorting = array();
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
        }

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_FEATURED_PRODUCTS)), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id'),
          'this_filename' => FILENAME_FEATURED_PRODUCTS,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('featured-products.tpl', ['params' => ['params'=>$params]]);
    }

    public function actionProducts_new(){
        global $languages_id, $currency_id, $customer_groups_id, $_SESSION, $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_PRODUCTS_NEW));

        $sorting = array();
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
        }
        $sorting[] = array('id' => 'da', 'title' => Yii::t('app', 'date &darr;'));
        $sorting[] = array('id' => 'dd', 'title' => Yii::t('app', 'date &uarr;'));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;
        
        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_PRODUCTS_NEW, 'sort' => 'dd')), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id'),
          'this_filename' => FILENAME_PRODUCTS_NEW,
          'sorting_options' => $sorting,
          'sorting_id' => 'dd',
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('products-new.tpl', ['params' => ['params'=>$params]]);
    }

    public function actionAllProducts(){
        global $languages_id, $currency_id, $customer_groups_id, $_SESSION, $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_ALL_PRODUCTS));

        $sorting = array();
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
        }
        $sorting[] = array('id' => 'da', 'title' => Yii::t('app', 'date &darr;'));
        $sorting[] = array('id' => 'dd', 'title' => Yii::t('app', 'date &uarr;'));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;
        
        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_ALL_PRODUCTS)), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id'),
          'this_filename' => FILENAME_ALL_PRODUCTS,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('all-products.tpl', ['params' => ['params'=>$params]]);
    }

    public function actionAdvancedSearchResult(){//catalog/advanced-search-result
        global $messageStack, $breadcrumb, $currency_id, $languages_id, $customer_groups_id, $_SESSION, $currencies, $currency;
        
        \common\helpers\Translation::init('catalog-advanced-search');

        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, \common\helpers\Output::get_all_get_params(), 'NONSSL', true, false));

        $sorting = array();
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
        }

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;
        
        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_ADVANCED_SEARCH)), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results), 'p.products_id'),
          'this_filename' => FILENAME_ADVANCED_SEARCH_RESULT,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if ($_GET['fbl']) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('advanced_search_result.tpl', [
            'params' => ['params'=>$params]]
        );

    }

    public function actionAdvancedSearch()
    {
        global $messageStack, $language, $breadcrumb, $languages_id;

        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ADVANCED_SEARCH));

        $messages_search = '';
        if ($messageStack->size('search') > 0) {
            $messages_search = $messageStack->output('search');
        }
        $controls = array(
          'keywords' => tep_draw_input_field('keywords', '', ''),
          'search_in_description' => tep_draw_checkbox_field('search_in_description', '1', SEARCH_IN_DESCRIPTION == 'True', 'id="search_in_description"'),
          'categories' => tep_draw_pull_down_menu('categories_id', \common\helpers\Categories::get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))),
           'inc_subcat' => tep_draw_checkbox_field('inc_subcat', '1', true, 'id="include_subcategories"'),
           'manufacturers' => '',
           'price_from' => tep_draw_input_field('pfrom'),
           'price_to' => tep_draw_input_field('pto'),
           //'date_from' => tep_draw_input_field('dfrom', '', 'placeholder="' . \common\helpers\Output::output_string(DOB_FORMAT_STRING) . '"'),
           //'date_to' => tep_draw_input_field('dto', '', 'placeholder="' . \common\helpers\Output::output_string(DOB_FORMAT_STRING) . '"'),
        );

        $site_manufacturers = \common\helpers\Manufacturers::get_manufacturers();
        if ( count($site_manufacturers)>0 ) {
           $site_manufacturers = array_merge(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)), $site_manufacturers);
           $controls['manufacturers'] = tep_draw_pull_down_menu('manufacturers_id', $site_manufacturers);
        }

        $searchable_properties = array();
        if (PRODUCTS_PROPERTIES == 'True') {
			
			$p_types = array_keys(PropertiesTypes::getTypes('search'));
			
            $properties_yes_no_array = array(array('id' => '', 'text' => OPTION_NONE), array('id' => 'true', 'text' => OPTION_TRUE), array('id' => 'false', 'text' => OPTION_FALSE));
            $properties_query = tep_db_query("select pr.properties_id, pr.properties_type, prd.properties_name, prd.properties_description, pr.multi_choice, pr.decimals from " . TABLE_PROPERTIES_DESCRIPTION . " prd, " . TABLE_PROPERTIES . " pr where pr.properties_id = prd.properties_id and prd.language_id = '" . (int)$languages_id . "' and pr.properties_type in ('".implode("', '", $p_types)."') and pr.display_search = 1 order by pr.sort_order, prd.properties_name");
            if (tep_db_num_rows($properties_query) > 0) {//need to do
                while ($properties_array = tep_db_fetch_array($properties_query)) {
                    $properties_array['control'] = '';
					
                    switch ($properties_array['properties_type']){
                        case 'text':
						case 'number':
						case 'interval':
						
							$properties_values_query = tep_db_query("select values_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$properties_array['properties_id'] . "' and language_id = '" . (int)$languages_id . "' order by " . ($properties_array['properties_type'] == 'number' || $properties_array['properties_type'] == 'interval' ? 'values_number' : 'values_text'));

							if ($properties_array['multi_choice']){
								$f = 'tep_draw_checkbox_field';
							} else {
								$f = 'tep_draw_radio_field';
							}
							
							if (tep_db_num_rows($properties_values_query)){
								while ($property_values = tep_db_fetch_array($properties_values_query)){//echo '<pre>';print_r($property_values);
									if ($properties_array['properties_type'] == 'interval'){
										$properties_array['control'] .= $f($properties_array['properties_id']) . (float)number_format($property_values['values_number'], $properties_array['decimals']) . ' - ' . (float)number_format($property_values['values_number_upto'], $properties_array['decimals']);
									} elseif($properties_array['properties_type'] == 'number'){
										$properties_array['control'] .= $f($properties_array['properties_id'] ) .(float)number_format($property_values['values_number'], $properties_array['decimals']);
									} else {
										$properties_array['control'] .= $f($properties_array['properties_id'] ) .  $property_values['values_text'];
									}
								}
							}
								
					
                        break;
                        case 'flag':
                            $properties_array['control'] .= tep_draw_pull_down_menu($properties_array['properties_id'], $properties_yes_no_array);
                            break;
                    }
                    $searchable_properties[] = $properties_array;
                }
            }
        }
        return $this->render('advanced_search.tpl', [
          'messages_search' => $messages_search,
          'controls' => $controls,
          'searchable_properties' => $searchable_properties,
          'search_result_page_link' => tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT,'','NONSSL'),

            //'params' => ['params'=>$params]
          ]);
    }

    public function actionManufacturers()
    {
        global $languages_id, $currency_id, $customer_groups_id, $_SESSION, $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE, tep_href_link('catalog/manufacturers'));

        $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name, manufacturers_image from " . TABLE_MANUFACTURERS ." order by manufacturers_name asc");
        if ($number_of_rows = tep_db_num_rows($manufacturers_query)) {
          $manufacturers_arr = array();
          while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers['link'] = tep_href_link('catalog/manufacturers', 'manufacturers_id=' . $manufacturers['manufacturers_id']);
            $manufacturers['img'] = Yii::$app->request->baseUrl . '/images/' . $manufacturers['manufacturers_image'];
            if (!is_file(Yii::getAlias('@webroot') . '/images/' . $manufacturers['manufacturers_image'])){
              $manufacturers['img'] = 'no';
            }
            $manufacturers_arr[] = $manufacturers;
          }
        }
        //echo '<pre>';print_r($manufacturers_arr);die();
        return $this->render('manufacturers.tpl', ['brands' => $manufacturers_arr]);
    }

    public function actionCompare()
    {
        global $languages_id, $currencies;
        $compare = Yii::$app->request->get('compare');

        $error_text = '';
        if (!is_array($compare) || count($compare) < 2 || count($compare) > 4) {
            $error_text = TEXT_PLEASE_SELECT_COMPARE;
        } else {
            $properties_array = array();
            $values_array = array();
            $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_compare = '1' and p2p.products_id in ('" . implode("','", array_map('intval', $compare)) . "')");
            while ($properties = tep_db_fetch_array($properties_query)) {
                if (!in_array($properties['properties_id'], $properties_array)) {
                    $properties_array[] = $properties['properties_id'];
                }
                $values_array[$properties['properties_id']][] = $properties['values_id'];
            }
            $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);

            $products_data_array = array();
            foreach ($compare as $products_id) {
                $products_arr = tep_db_fetch_array(tep_db_query("select products_id, products_model, products_price, products_tax_class_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
                $products_data_array[$products_id]['id'] = $products_id;
                $products_data_array[$products_id]['model'] = $products_arr['products_model'];
                $products_data_array[$products_id]['name'] = \common\helpers\Product::get_products_name($products_id);
                $special_price = \common\helpers\Product::get_products_special_price($products_id);
                if ($special_price){
                  $products_data_array[$products_id]['price_old'] = $currencies->display_price(\common\helpers\Product::get_products_price($products_id, 1, $products_arr['products_price']), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                  $products_data_array[$products_id]['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                } else {
                  $products_data_array[$products_id]['price'] = $currencies->display_price(\common\helpers\Product::get_products_price($products_id, 1, $products_arr['products_price']), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                }
                $products_data_array[$products_id]['link'] = tep_href_link('catalog/product', 'products_id=' . $products_id);
                $products_data_array[$products_id]['link_buy'] = tep_href_link('catalog/product', 'action=buy_now&products_id=' . $products_id);
                $products_data_array[$products_id]['action_buy'] = tep_href_link('catalog/product', 'action=add_product');
                $products_data_array[$products_id]['image'] = Images::getImageUrl($products_id, 'Small');

                $properties_array = array();
                $values_array = array();
                $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_compare = '1' and p2p.products_id = '" . (int)$products_id . "'");
                while ($properties = tep_db_fetch_array($properties_query)) {
                    if (!in_array($properties['properties_id'], $properties_array)) {
                        $properties_array[] = $properties['properties_id'];
                    }
                    $values_array[$properties['properties_id']][] = $properties['values_id'];
                }
                $products_data_array[$products_id]['properties_tree'] = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);
            }

            foreach ($properties_tree_array as $properties_id => $property) {
                $values_array = array();
                foreach ($products_data_array as $products_id => $products_data) {
                    if (is_array($products_data['properties_tree'][$properties_id]['values'])) {
                      $values_array[] = trim(implode(' ', $products_data['properties_tree'][$properties_id]['values']));
                    } else {
                      $values_array[] = '';
                    }
                }
                $unique_values_array = array_unique($values_array);
                if (count($unique_values_array) > 1 /* || trim($unique_values_array[0]) == '' */) {
                    $properties_tree_array[$properties_id]['vary'] = true;
                } else {
                    $properties_tree_array[$properties_id]['vary'] = false;
                }
            }
        }

        return $this->render('compare.tpl', [
            'error_text' => $error_text,
            'products_data_array' => $products_data_array,
            'properties_tree_array' => $properties_tree_array,
        ]);
    }

    public function actionGiftCard()
    {
        global $languages_id, $customer_id, $currencies, $currency, $messageStack;
        $product_info = tep_db_fetch_array(tep_db_query("select p.products_id, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.affiliate_id = '0', " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_model = 'VIRTUAL_GIFT_CARD' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id ."' and pd.affiliate_id = '0'"));
        
        if ( !($product_info['products_id'] > 0) ) {
          return $this->redirect(Yii::$app->urlManager->createUrl('/'));
        }
        
        if (isset($_GET['action']) && ($_GET['action'] == 'add_gift_card')) {
          $virtual_gift_card = tep_db_fetch_array(tep_db_query("select virtual_gift_card_basket_id, products_price as gift_card_price, virtual_gift_card_recipients_name, virtual_gift_card_recipients_email, virtual_gift_card_message, virtual_gift_card_senders_name from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " where length(virtual_gift_card_code) = 0 and virtual_gift_card_basket_id = '" . (int)$_GET['id'] . "' and products_id = '" . (int)$product_info['products_id'] . "' and currencies_id = '" . (int)$currencies->currencies[$currency]['id'] . "' and " . ($customer_id > 0 ? " customers_id = '" . (int)$customer_id . "'" : " session_id = '" . tep_session_id() . "'")));
          
          $gift_card_price = tep_db_prepare_input($_POST['gift_card_price']);
          $virtual_gift_card_recipients_name = tep_db_prepare_input($_POST['virtual_gift_card_recipients_name']);
          $virtual_gift_card_recipients_email = tep_db_prepare_input($_POST['virtual_gift_card_recipients_email']);
          $virtual_gift_card_confirm_email = tep_db_prepare_input($_POST['virtual_gift_card_confirm_email']);
          $virtual_gift_card_message = tep_db_prepare_input($_POST['virtual_gift_card_message']);
          $virtual_gift_card_senders_name = tep_db_prepare_input($_POST['virtual_gift_card_senders_name']);

          $error = false;

          if (strlen($virtual_gift_card_recipients_email) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $error = true;
            $messageStack->add('virtual_gift_card', ENTRY_RECIPIENTS_EMAIL_ERROR);
          }

          if (!\common\helpers\Validations::validate_email($virtual_gift_card_recipients_email)) {
            $error = true;
            $messageStack->add('virtual_gift_card', ENTRY_RECIPIENTS_EMAIL_CHECK_ERROR);
          }

          if ($virtual_gift_card_recipients_email != $virtual_gift_card_confirm_email) {
            $error = true;
            $messageStack->add('virtual_gift_card', ENTRY_CONFIRM_EMAIL_ERROR);
          }

          if ($error == false) {
            $sql_data_array = array('customers_id' => $customer_id,
                                    'session_id' => $customer_id > 0 ? '' : tep_session_id(),
                                    'currencies_id' => $currencies->currencies[$currency]['id'],
                                    'products_id' => $product_info['products_id'],
                                    'products_price' => $gift_card_price,
                                    'virtual_gift_card_recipients_name' => $virtual_gift_card_recipients_name,
                                    'virtual_gift_card_recipients_email' => $virtual_gift_card_recipients_email,
                                    'virtual_gift_card_message' => $virtual_gift_card_message,
                                    'virtual_gift_card_senders_name' => $virtual_gift_card_senders_name,
                                    'virtual_gift_card_code' => '');

            if ($virtual_gift_card['virtual_gift_card_basket_id'] > 0) {
              tep_db_perform(TABLE_VIRTUAL_GIFT_CARD_BASKET, $sql_data_array, 'update', "virtual_gift_card_basket_id = '" . (int)$virtual_gift_card['virtual_gift_card_basket_id'] . "'");
            } else {
              tep_db_perform(TABLE_VIRTUAL_GIFT_CARD_BASKET, $sql_data_array);
            }

            return $this->redirect(Yii::$app->urlManager->createUrl('shopping-cart/'));
          }
        }
        
        $params = [];
        return $this->render('gift-card.tpl', ['params' => $params]);
    }

    public function actionGetPrice() {
        $this->layout = false;
        if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'getPricePack')) {
            $ext::getPricePack();
        }
    }

    public function actionProductInventory()
    {
        global $languages_id, $language, $currencies, $cart;

        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();
        $products_id = Yii::$app->request->get('products_id');
        $inv_uprid = Yii::$app->request->get('inv_uprid');

        $details = \common\helpers\Inventory::getDetails($products_id, $inv_uprid, $params);

        if (Yii::$app->request->isAjax) {
//            $details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
            $details['product_inventory'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/inventory.tpl', 'params' => ['inventory' => $details['inventory_array'], 'isAjax' => true]]);
            return json_encode($details);
        } else {
            if (count($details['inventory_array']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/inventory.tpl', 'params' => ['inventory' => $details['inventory_array'], 'isAjax' => false,]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductBundle()
    {
        global $languages_id, $language, $currencies, $cart;

        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();

        $details = \common\helpers\Bundles::getDetails($params);

        if (Yii::$app->request->isAjax) {
            $details['product_bundle'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/bundle.tpl', 'params' => ['products' => $details['bundle_products'], 'isAjax' => true]]);
            return json_encode($details);
        } else {
            if (count($details['bundle_products']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/bundle.tpl', 'params' => ['products' => $details['bundle_products'], 'isAjax' => false,]]);
            } else {
                return '';
            }
        }
    }
    public function actionProductConfigurator()
    {
        global $languages_id, $language, $currencies, $cart;

        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();

        return IncludeTpl::widget(['file' => 'boxes/product/pc_info.tpl']);

    }
}
