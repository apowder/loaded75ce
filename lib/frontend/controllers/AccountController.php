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

use frontend\design\Info;
use Yii;
use yii\web\Session;
use common\classes\order;
use common\classes\opc;
use frontend\design\SplitPageResults;
use common\classes\Images;
use common\models\Customer;
use common\models\Socials;

/**
 * Site controller
 */
class AccountController extends Sceleton
{
    
    private $use_social = false;
    
    public function __construct($id, $module){
        parent::__construct($id, $module);
        
        $platform_config = new \common\classes\platform_config(\common\classes\platform::currentId());
        
        $this->use_social = $platform_config->checkNeedSocials();
        if ($this->use_social){
            \common\models\Socials::loadComponents(PLATFORM_ID);
        }
    }    
    

  public function actionIndex()
    {
			global $languages_id, $language, $customer_id, $currencies, $currency, $cc_id, $messageStack, $login_id, $customer_default_address_id;
      global $navigation, $breadcrumb;
      global $cart, $wish_list;

      \common\helpers\Translation::init('account/history');

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));

      $session = new Session;
      if (!$session->has('customer_id')){
        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
          $navigation->set_snapshot();
        }        
        tep_redirect(tep_href_link('account/login', '', 'SSL'));
      }

			if ($messageStack->size('account') > 0) {
				$account_links['message'] = '<div class="main">' . $messageStack->output('account') . '</div>';
			}
      $account_links['show_gv_block'] = false;
      $account_links['show_gv_send_block'] = false;
      $account_links['coupon'] = '';
			if (tep_session_is_registered('customer_id')) {
        $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
				$gv_result = tep_db_fetch_array($gv_query);
				if ($gv_result['amount'] > 0 ) {
          $account_links['show_gv_block'] = true;
					//$gv_result['amount'] *= $currencies->get_market_price_rate($gv_result['currency'], $currency);
          $account_links['gv_balance_formatted'] = $currencies->format($gv_result['amount']);
          $account_links['gv_send_intro'] = sprintf(GV_HAS_VOUCHER_SEND_S,tep_href_link(FILENAME_GV_SEND));
          $account_links['gv_send_link'] = tep_href_link(FILENAME_GV_SEND);
          //$account_links['show_gv_send_block'] = true;
          
					//$account_links['coupon'] .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_BALANCE . '</td><td align="right" valign="bottom"><b>' . $currencies->format($gv_result['amount']) . '</b></td></tr></table>';
					//$account_links['coupon'] .= '<div><a href="'. tep_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a></div>';
				}
			}
      
      
			if (false && /*no sense - gv_id redeemed on login*/ tep_session_is_registered('gv_id')) {
				$gv_query = tep_db_query("select coupon_amount, coupon_currency from " . TABLE_COUPONS . " where coupon_id = '" . (int)$gv_id . "'");
				$coupon = tep_db_fetch_array($gv_query);
				$coupon['coupon_amount'] *= $currencies->get_market_price_rate($coupon['coupon_currency'], $currency);
				$account_links['coupon'] .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td>' . VOUCHER_REDEEMED . '</td><td align="right" valign="bottom"><b>' . $currencies->format($coupon['coupon_amount']) . '</b></td></tr></table>';
			}
			if (false && /*WTF?*/tep_session_is_registered('cc_id') && $cc_id) {
				$account_links['coupon'] .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td>' . CART_COUPON . '</td><td align="right" valign="bottom">' . '<a href="javascript:couponpopupWindow(\'' . tep_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $cc_id) . '\')">' . CART_COUPON_INFO . '</a></td></tr></table>';
			}
			/**/
			$customers_query = tep_db_query("select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, c.customers_alt_email_address, c.groups_id, c.customers_company, c.customers_company_vat, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_landline, c.customers_alt_telephone, c.customers_cell, c.customers_status, c.customers_fax, c.customers_newsletter, c.customers_owc_member, c.customers_type_id, c.customers_bonus_points, c.customers_credit_avail, c.customers_default_address_id, c.credit_amount from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$customer_id . "' ");
        $customers = tep_db_fetch_array($customers_query);
        $topAcc = array();
        $topAcc['credit_amount'] = $currencies->format($customers['credit_amount']);
        $topAcc['count_credit_amount'] = $customers['credit_amount'];
        
        $orders_query = tep_db_query("select count(*) as total_orders, max(o.date_purchased) as last_purchased, sum(ot.value) as total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." and ot.class='ot_total' and o.customers_id = ". (int)$customer_id);
        $orders = tep_db_fetch_array($orders_query);
        $topAcc['total_orders'] = $orders['total_orders'];
        $topAcc['last_purchased'] = \common\helpers\Date::date_long($orders['last_purchased']);
        $topAcc['last_purchased_days'] = \common\helpers\Date::getDateRange(date('Y-m-d'), $orders['last_purchased']);
        $topAcc['total_sum'] = $currencies->format($orders['total_sum']);
			/**/

			$account_links['account_history_array'] = '';
if (\common\helpers\Customer::count_customer_orders() > 0) {

  $account_links['account_history_array'] .=  '<h2>' . OVERVIEW_TITLE . '&nbsp;&nbsp;<a href="' . tep_href_link('account/history', '', 'SSL') . '">' . OVERVIEW_SHOW_ALL_ORDERS . '</a></h2>';
	$account_links['account_history_array'] .= '';
    $account_orders = array();
	$orders_query = tep_db_query("select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_country, o.billing_name, o.billing_country, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by orders_id desc limit 3");
	$account_links['account_history_array'] .= '<div class="contentBoxContents"><strong class="box-title">' . OVERVIEW_PREVIOUS_ORDERS . '</strong><table class="orders-table">';      
	while ($orders = tep_db_fetch_array($orders_query)) {
			$products_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "'");
      $products = tep_db_fetch_array($products_query);
			if (tep_not_null($orders['delivery_name'])) {
				$order_name = $orders['delivery_name'];
				$order_country = $orders['delivery_country'];
			} else {
				$order_name = $orders['billing_name'];
				$order_country = $orders['billing_country'];
			}
      $orders['date'] = \common\helpers\Date::date_long($orders['date_purchased']);
      $orders['name'] = \common\helpers\Output::output_string_protected($order_name);
      $orders['country'] = $order_country;
      $orders['products'] = $products['count'];
      $orders['view'] = tep_href_link('account/history-info', 'order_id=' . $orders['orders_id'], 'SSL');

      $orders['reorder_link'] = tep_href_link('checkout/reorder', 'order_id=' . $orders['orders_id'], 'SSL');
      $orders['reorder_confirm'] = '';
      if ( $cart->count_contents()>0 ) {
        $orders['reorder_confirm'] = REORDER_CART_MERGE_WARN;
      }

        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'payLink')) {
            $pay_link = $ext::payLink($orders['orders_id']);
        }
        $orders['pay_link'] = $pay_link;
      $account_orders[] = $orders;
	}
	$account_links['account_history_array'] .= '</table></div>';
 }

      $account_reviews = array();
      $account_reviews_more_link = false;
      $get_customer_reviews_r = tep_db_query(
        "SELECT r.* ".
        "FROM " . TABLE_REVIEWS . " r ".
        " INNER JOIN ".TABLE_PRODUCTS." p ON p.products_id=r.products_id ".
        "WHERE customers_id='" . (int)$customer_id . "' ".
        "ORDER BY reviews_id DESC LIMIT 4"
      );
      while( $customer_review = tep_db_fetch_array($get_customer_reviews_r) ) {
        if ( count($account_reviews)==3 ) {
          $account_reviews_more_link = tep_href_link('account/products-reviews','','SSL');
          continue;
        }
        $customer_review['products_link'] = '';
        if ( \common\helpers\Product::check_product($customer_review['products_id']) ) {
          $customer_review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$customer_review['products_id'],'');
        }
        $customer_review['products_name'] = \common\helpers\Product::get_products_name($customer_review['products_id']);
        $customer_review['reviews_rating'];
        $customer_review['date_added_str'] = \common\helpers\Date::date_short($customer_review['date_added']);
        if ($customer_review['status']){
          $customer_review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
        }else{
          $customer_review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
        }
        $customer_review['view'] = tep_href_link('reviews/info','reviews_id='.$customer_review['reviews_id'].'&back='.FILENAME_ACCOUNT);
        $account_reviews[] = $customer_review;
      }
      /*wishlist*/
      
      $products_wishlist = $wish_list->get_products();
      for ($i=0, $n=sizeof($products_wishlist); $i<$n; $i++) {
        $products_wishlist[$i]['image'] = Images::getImageUrl($products_wishlist[$i]['id'], 'Small');
        $products_wishlist[$i]['link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_wishlist[$i]['id']);
        $products_wishlist[$i]['final_price_formatted'] = $currencies->display_price($products_wishlist[$i]['final_price'], \common\helpers\Tax::get_tax_rate($products_wishlist[$i]['tax_class_id']));
        $products_wishlist[$i]['remove_link'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products_wishlist[$i]['id'].'&action=remove_wishlist','SSL');
        $products_wishlist[$i]['move_in_cart'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products_wishlist[$i]['id'].'&action=wishlist_move_to_cart','SSL');
      }
      /*wishlist*/

      /*subscription*/
        $subscriptions = [];
        $subscription_query = tep_db_query("select o.*, s.orders_status_name from " . TABLE_SUBSCRIPTION . " o, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.subscription_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by subscription_id desc limit 3");
        while ($subscription = tep_db_fetch_array($subscription_query)) {
            $subscription['date'] = \common\helpers\Date::date_long($subscription['date_purchased']);
            $subscription['view'] = tep_href_link('account/subscription-history-info', 'subscription_id=' . $subscription['subscription_id'], 'SSL');
      
            $subscriptions[] = $subscription;
        }
      /*subscription*/
			$priamry_address = \common\helpers\Address::address_label($customer_id, $customer_default_address_id, true, ' ', '<br>');
			$account_links['acount_edit'] = tep_href_link('account/edit', '', 'SSL');
			$account_links['address_book_edit'] = tep_href_link('account/address-book-process', 'edit=' . $customer_default_address_id, 'SSL');
			$account_links['address_book'] = tep_href_link('account/address-book', '', 'SSL');
			$account_links['account_password'] = tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL');
			$account_links['wishlist'] = tep_href_link(FILENAME_WISHLIST, '','SSL');
			$account_links['account_logoff'] = tep_href_link(FILENAME_LOGOFF, '');
			$account_links['account_history'] = tep_href_link('account/history', '', 'SSL');
			$account_links['account_newsletters'] = tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL');
			$account_links['account_notifications'] = tep_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL');
                        
        
        return $this->render('index.tpl', ['description' => '', 'account_links' => $account_links, 'subscriptions'=>$subscriptions, 'account_orders'=>$account_orders, 'topAcc' => $topAcc, 'customers'=>$customers, 'priamry_address'=>$priamry_address,'account_reviews' => $account_reviews,'account_reviews_more_link'=>$account_reviews_more_link, 'products_wishlist'=>$products_wishlist]);
    }

  /* ???? */
    public function actionSuccess()
    {

        return $this->render('success.tpl', ['description' => '']);
    }


    public function actionLogin()
    {
      global $cart, $navigation, $affiliate_ref, $messageStack;
      global $wish_list;
      // customer login details
      global $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $customer_groups_id;
        
        \common\helpers\Translation::init('js');

      $order_id = (int) Yii::$app->request->get('order');
        if ($order_id > 0) {
            $customer_info_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '" . (int) $order_id . "'");
            $customer_info = tep_db_fetch_array($customer_info_query);
            if (isset($customer_info['customers_id'])) {
                $customers_id = (int) $customer_info['customers_id'];
                $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, groups_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customers_id . "' and customers_status = 1 ");
                $check_customer = tep_db_fetch_array($check_customer_query);
                if (isset($check_customer['customers_password'])) {
                    $customers_password = md5($check_customer['customers_password']);
                    $tr = tep_db_prepare_input(Yii::$app->request->get('tr'));
                    if ($customers_password == $tr) {

                        $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $check_customer['customers_id'] . "' and address_book_id = '" . (int) $check_customer['customers_default_address_id'] . "'");
                        $check_country = tep_db_fetch_array($check_country_query);

                        $customer_id = $check_customer['customers_id'];
                        $customer_default_address_id = $check_customer['customers_default_address_id'];
                        $customer_first_name = $check_customer['customers_firstname'];
                        $customer_country_id = $check_country['entry_country_id'];
                        $customer_zone_id = $check_country['entry_zone_id'];
                        if (CUSTOMERS_GROUPS_ENABLE == 'True') {
                            $customer_groups_id = $check_customer['groups_id'];
                        } else {
                            $customer_groups_id = 0;
                        }

                        tep_session_register('customer_id');
                        tep_session_register('customer_default_address_id');
                        tep_session_register('customer_first_name');
                        tep_session_register('customer_country_id');
                        tep_session_register('customer_zone_id');
                        tep_session_register('customer_groups_id');

                        tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int) $customer_id . "'");

                        // restore cart contents
                        $cart->restore_contents();
                        if (is_object($wish_list) && method_exists($wish_list, 'restore_contents')) {
                            $wish_list->restore_contents();
                        }

                        $to = Yii::$app->request->get('to');
                        if ($to == 'pay') {
                            tep_redirect(tep_href_link('account/order-pay', 'order_id=' . $order_id, 'SSL'));
                        } else {
                            tep_redirect(tep_href_link('account/history-info', 'order_id=' . $order_id, 'SSL'));
                        }
                    }
                }
            }
        }

        global $breadcrumb;

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE,tep_href_link('account/login','','SSL'));

      $account_login = Yii::$app->request->post('account_login','login');

      if ( $account_login=='create_account' ) {
        return $this->actionCreate();
      }

      $error = false;
      if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
        $email_address = tep_db_prepare_input($_POST['email_address']);
        $password = tep_db_prepare_input($_POST['password']);

        // {{
        if ($_POST['type'] == 'new_customer') {
          $_SESSION['tmp_email_address'] = $email_address;
          tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
        }
        // }}

        // Check if email exists
        $customer = new Customer(Customer::LOGIN_STANDALONE);
        if (!$customer->loginCustomer($email_address, $password)){
          $error = true;
        } else {
            if (sizeof($navigation->snapshot) > 0) {
			  if (is_array($navigation->snapshot['get'])){
				  $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
			  } else {
				  $origin_href = tep_href_link($navigation->snapshot['page'], $navigation->snapshot['get'], $navigation->snapshot['mode']);
			  }              
              $navigation->clear_snapshot();
              tep_redirect($origin_href);
            } else {
              tep_redirect(tep_href_link(FILENAME_ACCOUNT));
            }
        }
      }
      if ($error == true) {
        $messageStack->add('login', TEXT_LOGIN_ERROR);
        $messageStack->add('login', '<a href="' . tep_href_link('account/password-forgotten', '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN_S . '</a>');
      }

      $messages_login = '';
      if ($messageStack->size('login')>0){
        $messages_login = $messageStack->output('login');
      }

        if (in_array('required_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } elseif (in_array('visible_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } else {
            $showAddress = false;
        }
      return $this->render('login.tpl', [
        'action' => tep_href_link('account/login', 'action=process', 'SSL'),
        'messages_login' => $messages_login,
        'account_create_action' => tep_href_link('account/login', 'action=process', 'SSL'),
        //'regex_dob_check' => '\d{2}\.\d{2}\.(\d{2}|\d{4})',
        'customers_newsletter' => true,
        'messages_account_create' => '',
        'show_socials' => $this->use_social,
        'showAddress' => $showAddress,
      ]);
    }

    public function actionCreate() {
        global $cart, $navigation, $messageStack, $currencies;
        global $wish_list;
        global $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $customer_groups_id;

        \common\helpers\Translation::init('account/create');
        \common\helpers\Translation::init('account/login');
        \common\helpers\Translation::init('js');

        if (ENABLE_CUSTOMER_GROUP_CHOOSE == 'True') {
            $group = (int) $_POST['group'];
        } else {
            if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                if (isset($HTTP_GET_VARS['group']) && $HTTP_GET_VARS['group'] != '') {
                    $group = (int) $HTTP_GET_VARS['group'];
                } else {
                    $group = 0;
                }
            } else {
                if (isset($HTTP_GET_VARS['group']) && $HTTP_GET_VARS['group'] != '') {
                    $group = (int) $HTTP_GET_VARS['group'];
                } else {
                    $group = DEFAULT_USER_LOGIN_GROUP;
                }
            }
        }

        $error = false;
        
        if (in_array(ACCOUNT_GENDER, ['required_register', 'visible_register'])) {
            $gender = tep_db_prepare_input(Yii::$app->request->post('gender'));
            if (ACCOUNT_GENDER == 'required_register' && empty($gender)) {
                $error = true;
                $messageStack->add('create_account', ENTRY_GENDER_ERROR);
            }
        }
        
        if (in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])) {
            $firstname = tep_db_prepare_input(Yii::$app->request->post('firstname'));
            if (ACCOUNT_FIRSTNAME == 'required_register' && strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
            }
        }
        
        if (in_array(ACCOUNT_LASTNAME, ['required_register', 'visible_register'])) {
            $lastname = tep_db_prepare_input(Yii::$app->request->post('lastname'));
            if (ACCOUNT_LASTNAME == 'required_register' && strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
            }
        }

        if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])) {
            $dob = tep_db_prepare_input(Yii::$app->request->post('dob'));
            if (!empty($dob)) {
                $dob = \common\helpers\Date::date_raw($dob);
                if (!checkdate(date('m', strftime($dob)), date('d', strftime($dob)), date('Y', strftime($dob)))) {
                    if (ACCOUNT_DOB == 'required_register') {
                        $error = true;
                        $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
                    } else {
                        $dob = '0000-00-00';
                    }
                }
            } else {
                if (ACCOUNT_DOB == 'required_register') {
                    $error = true;
                    $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
                } else {
                    $dob = '0000-00-00';
                }
            }
        }
        
        if (in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])) {
            $telephone = tep_db_prepare_input(Yii::$app->request->post('telephone'));
            if (ACCOUNT_TELEPHONE == 'required_register' && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_TELEPHONE_NUMBER_ERROR, ENTRY_TELEPHONE_MIN_LENGTH));
            }
        }
        
        if (in_array(ACCOUNT_LANDLINE, ['required_register', 'visible_register'])) {
            $landline = tep_db_prepare_input(Yii::$app->request->post('landline'));
            if (ACCOUNT_LANDLINE == 'required_register' && strlen($landline) < ENTRY_LANDLINE_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_LANDLINE_NUMBER_ERROR, ENTRY_LANDLINE_MIN_LENGTH));
            }
        }
        
        if (in_array(ACCOUNT_COMPANY, ['required_register', 'visible_register'])) {
            $company = tep_db_prepare_input(Yii::$app->request->post('company'));
            if (ACCOUNT_COMPANY == 'required_register' && empty($company)) {
                $error = true;
                $messageStack->add('create_account', ENTRY_COMPANY_ERROR);
            }
        }
        
        if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required_register', 'visible_register'])) {
            $company_vat = tep_db_prepare_input(Yii::$app->request->post('company_vat'));
            if (ACCOUNT_COMPANY_VAT_ID == 'required_register' &&  (empty($company_vat) || !\common\helpers\Validations::checkVAT($company_vat))) {
                $error = true;
                $messageStack->add('create_account', ENTRY_VAT_ID_ERROR);
            }
        }
        
        if (in_array(ACCOUNT_POSTCODE, ['required_register', 'visible_register'])) {
            $postcode = tep_db_prepare_input(Yii::$app->request->post('postcode'));
            if (ACCOUNT_POSTCODE == 'required_register' && strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH));
            }
        }

        if (in_array(ACCOUNT_STREET_ADDRESS, ['required_register', 'visible_register'])) {
            $street_address = tep_db_prepare_input(Yii::$app->request->post('street_address'));
            if (ACCOUNT_STREET_ADDRESS == 'required_register' && strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
            }
        }
        
        if (in_array(ACCOUNT_SUBURB, ['required_register', 'visible_register'])) {
            $suburb = tep_db_prepare_input(Yii::$app->request->post('suburb'));
            if (ACCOUNT_SUBURB == 'required_register' &&  empty($suburb)) {
                $error = true;
                $messageStack->add('create_account', ENTRY_SUBURB_ERROR);
            }
        }
        
        if (in_array(ACCOUNT_CITY, ['required_register', 'visible_register'])) {
            $city = tep_db_prepare_input(Yii::$app->request->post('city'));
            if (ACCOUNT_CITY == 'required_register' && strlen($city) < ENTRY_CITY_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', sprintf(ENTRY_CITY_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
            }
        }
        
        if (in_array(ACCOUNT_COUNTRY, ['required_register', 'visible_register'])) {
            $country = tep_db_prepare_input(Yii::$app->request->post('country'));
            if (is_numeric($country) == false) {
                if (ACCOUNT_COUNTRY == 'required_register') {
                    $error = true;
                    $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
                } else {
                    $country = (int) STORE_COUNTRY;
                    $zone_id = (int) STORE_ZONE;
                }
            }
        } else {
            $country = (int) STORE_COUNTRY;
            $zone_id = (int) STORE_ZONE;
        }
        
        if (in_array(ACCOUNT_STATE, ['required_register', 'visible_register'])) {
            $state = tep_db_prepare_input(Yii::$app->request->post('state'));
            $zone_id = 0;
            $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
            $check = tep_db_fetch_array($check_query);
            $entry_state_has_zones = ($check['total'] > 0);
            if ($entry_state_has_zones == true) {
                $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and zone_name = '" . tep_db_input($state) . "'");
                if (tep_db_num_rows($zone_query) == 1) {
                    $zone = tep_db_fetch_array($zone_query);
                    $zone_id = $zone['zone_id'];
                } elseif (ACCOUNT_STATE == 'required_register') {
                    $error = true;
                    $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
                }
            } else {
                if (strlen($state) < ENTRY_STATE_MIN_LENGTH && ACCOUNT_STATE == 'required_register') {
                    $error = true;
                    $messageStack->add('create_account', sprintf(ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                }
            }
        }
        
        $email_address = tep_db_prepare_input(Yii::$app->request->post('email_address'));
        if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $error = true;
            $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
        } elseif (\common\helpers\Validations::validate_email($email_address) == false) {
            $error = true;
            $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
        } else {
            $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and opc_temp_account=0");
            $check_email = tep_db_fetch_array($check_email_query);
            if ($check_email['total'] > 0) {
                $error = true;
                $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
            }
        }

        $password = tep_db_prepare_input(Yii::$app->request->post('password'));
        $confirmation = tep_db_prepare_input(Yii::$app->request->post('confirmation'));
        if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
            $error = true;
            $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
        } elseif ($password != $confirmation) {
            $error = true;
            $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
        }
        
        $newsletter = (int)Yii::$app->request->post('newsletter');
        
        if ($error == false) {
            $login = true;
            if ($group != 0 && \common\helpers\Customer::check_customer_groups($group, 'new_approve')) {
                $login = false;
            }
            $sql_data_array = array(
                'customers_email_address' => $email_address,
                'customers_newsletter' => $newsletter,
                'platform_id' => \common\classes\platform::currentId(),
                'groups_id' => $group,
                'customers_status' => ($login ? 1 : 0),
                'customers_password' => \common\helpers\Password::encrypt_password($password),
            );

            if (isset($gender)) {
                $sql_data_array['customers_gender'] = $gender;
            }
            if (isset($firstname)) {
                $sql_data_array['customers_firstname'] = $firstname;
            }
            if (isset($lastname)) {
                $sql_data_array['customers_lastname'] = $lastname;
            }
            if (isset($dob)) {
                $sql_data_array['customers_dob'] = \common\helpers\Date::date_raw($dob);
            }
            if (isset($telephone)) {
                $sql_data_array['customers_telephone'] = $telephone;
            }
            if (isset($landline)) {
                $sql_data_array['customers_landline'] = $landline;
            }
            if (isset($company)) {
                $sql_data_array['customers_company'] = $company;
            }
            if (isset($company_vat)) {
                $sql_data_array['customers_company_vat'] = $company_vat;
            }

            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
            $customer_id = tep_db_insert_id();

            $sql_data_array = array(
                'customers_id' => $customer_id,
                'entry_country_id' => (isset($country) ? $country : STORE_COUNTRY),
            );

            if (isset($gender)) {
                $sql_data_array['entry_gender'] = $gender;
            }
            if (isset($firstname)) {
                $sql_data_array['entry_firstname'] = $firstname;
            }
            if (isset($lastname)) {
                $sql_data_array['entry_lastname'] = $lastname;
            }
            if (isset($postcode)) {
                $sql_data_array['entry_postcode'] = $postcode;
            }
            if (isset($street_address)) {
                $sql_data_array['entry_street_address'] = $street_address;
            }
            if (isset($suburb))  {
                $sql_data_array['entry_suburb'] = $suburb;
            }
            if (isset($city)) {
                $sql_data_array['entry_city'] = $city;
            }
            if ($zone_id > 0) {
                $sql_data_array['entry_zone_id'] = $zone_id;
                $sql_data_array['entry_state'] = '';
            } else {
                $sql_data_array['entry_zone_id'] = '0';
                $sql_data_array['entry_state'] = isset($state) ? $state : '';
            }
            
            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
            $address_id = tep_db_insert_id();

            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");

            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $customer_id . "', '0', now())");

            if (SESSION_RECREATE == 'True') {
                tep_session_recreate();
            }

            $customer_first_name = isset($firstname) ? $firstname : '';
            $customer_default_address_id = $address_id;
            $customer_country_id = $country;
            $customer_zone_id = $zone_id;
            if (!defined('DEFAULT_USER_LOGIN_GROUP')) {
                define(DEFAULT_USER_LOGIN_GROUP, 0);
            }
            $customer_groups_id = DEFAULT_USER_LOGIN_GROUP;

            if ($login) {
                tep_session_register('customer_id');
                tep_session_register('customer_first_name');
                tep_session_register('customer_default_address_id');
                tep_session_register('customer_country_id');
                tep_session_register('customer_zone_id');
                tep_session_register('customer_groups_id');

                $cart->restore_contents();
                if (is_object($wish_list) && method_exists($wish_list, 'restore_contents')) {
                    $wish_list->restore_contents();
                }
                
                if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_track_after_customer_create')){
                    $ext::rf_track_after_customer_create($customer_id);
                }
            }

            $name = $firstname . ' ' . $lastname;

            if ($gender == 'm') {
                $user_greeting = sprintf(EMAIL_GREET_MR, $lastname);
            } elseif ($gender == 'f' || $gender == 's') {
                $user_greeting = sprintf(EMAIL_GREET_MS, $lastname);
            } else {
                $user_greeting = sprintf(EMAIL_GREET_NONE, $firstname);
            }

            $email_params = array();
            $email_params['STORE_NAME'] = STORE_NAME;
            $email_params['USER_GREETING'] = trim($user_greeting);
            $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Confirmation', $email_params);

            \common\helpers\Mail::send($name, $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            if (!$login) {
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }

            if (sizeof($navigation->snapshot) > 0) {
                $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                $navigation->clear_snapshot();
                tep_redirect($origin_href);
            } else {
//          if ($cart->count_contents() >= 1) {
//            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
//          } else {
                tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
//          }
            }
        }

        $messages_account_create = '';
        if ($messageStack->size('create_account') > 0 && $_GET['action'] == 'process') {
            $messages_account_create = $messageStack->output('create_account');
        }

        if (in_array('required_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } elseif (in_array('visible_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } else {
            $showAddress = false;
        }
        return $this->render('login.tpl', [
                    'action' => tep_href_link('account/login', 'action=process', 'SSL'),
                    'messages_account_create' => $messages_account_create,
                    'custom_gender' => isset($gender) ? $gender : '',
                    'customers_first_name' => isset($firstname) ? $firstname : '',
                    'customers_last_name' => isset($lastname) ? $lastname : '',
                    'customers_dob' => ((isset($dob) && !empty($dob)) ? \common\helpers\Date::datepicker_date($dob) : ''),
                    'telephone' => isset($telephone) ? $telephone : '',
                    'landline' => isset($landline) ? $landline : '',
                    'company' => isset($company) ? $company : '',
                    'company_vat' => isset($company_vat) ? $company_vat : '',
                    'showAddress' => $showAddress,
                    'postcode' => isset($postcode) ? $postcode : '',
                    'street_address' => isset($street_address) ? $street_address : '',
                    'city' => isset($city) ? $city : '',
                    'country' => isset($country) ? $country : STORE_COUNTRY,
                    'state' => isset($state) ? $state : '',
                    'suburb' => isset($suburb) ? $suburb : '',
                    'customers_email_address' => isset($email_address) ? $email_address : '',
                    'customers_newsletter' => !!$newsletter,
                    'create_tab_active' => true,
        ]);
    }

    public function actionCreateSuccess(){
      global $cart;

      global $breadcrumb;
      $breadcrumb->add(TEXT_MY_ACCOUNT);
      $breadcrumb->add(NAVBAR_TITLE_2);
      
      //$after_create_go = tep_href_link(FILENAME_ACCOUNT, '', 'SSL'); 
      $after_create_go = tep_href_link(FILENAME_DEFAULT, '', 'SSL');
      if ($cart->count_contents() >= 1) {
        $after_create_go = tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');
      }

      return $this->render('success.tpl', [
        'title' => HEADING_TITLE,
        'description' => sprintf(TEXT_ACCOUNT_CREATED, tep_href_link(FILENAME_CONTACT_US), tep_href_link(FILENAME_CONTACT_US)),
        'next_page' => $after_create_go, 
      ]);
    }

    public function actionLogoff()
    {
      global $breadcrumb, $cart, $customer_groups_id;

      if (tep_session_is_registered('customer_id')){

        tep_session_unregister('customer_id');
        tep_session_unregister('customer_default_address_id');
        tep_session_unregister('customer_first_name');
        tep_session_unregister('customer_country_id');
        tep_session_unregister('customer_zone_id');
        tep_session_unregister('comments');
        tep_session_unregister('customer_groups_id');
        tep_session_unregister('cart_address_id');

        tep_session_unregister('billto');
        tep_session_unregister('sendto');

        tep_session_unregister('shipping');
        tep_session_unregister('payment');

        foreach( preg_grep('/^one_page_checkout_/',array_keys($_SESSION)) as $_clean_key){
          tep_session_unregister($_clean_key);
        }

        //ICW - logout -> unregister GIFT VOUCHER sessions - Thanks Fredrik
        tep_session_unregister('gv_id');
        tep_session_unregister('cc_id');
        //ICW - logout -> unregister GIFT VOUCHER sessions  - Thanks Fredrik

        $customer_groups_id = DEFAULT_USER_GROUP;
        $cart->reset();
        tep_redirect(tep_href_link(FILENAME_LOGOFF));
      }

      $breadcrumb->add(NAVBAR_TITLE);
      
      return $this->render('logoff.tpl', ['link_continue_href' => tep_href_link(FILENAME_DEFAULT,'','NONSSL')]);
    }


    public function actionPassword()
    {
      global $navigation, $messageStack, $customer_id;
      global $breadcrumb;
      
      if (!tep_session_is_registered('customer_id')) {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link('account/login', '', 'SSL'));
      }

      $error = false;
      if ( Yii::$app->request->isPost /*isset($_POST['action']) && ($_POST['action'] == 'process')*/) {
        $password_current = tep_db_prepare_input($_POST['password_current']);
        $password_new = tep_db_prepare_input($_POST['password_new']);
        $password_confirmation = tep_db_prepare_input($_POST['password_confirmation']);

        if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;

          $messageStack->add('account_password', ENTRY_PASSWORD_CURRENT_ERROR);
        } elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;

          $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
        } elseif ($password_new != $password_confirmation) {
          $error = true;

          $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
        }

        if ($error == false) {
          $check_customer_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
          $check_customer = tep_db_fetch_array($check_customer_query);

          if (\common\helpers\Password::validate_password($password_current, $check_customer['customers_password'])) {
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . \common\helpers\Password::encrypt_password($password_new) . "' where customers_id = '" . (int)$customer_id . "'");

            tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");

            $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');

            tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
          } else {
            $error = true;

            $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
          }
        }
      }

      $message_account_password = '';
      if ($messageStack->size('account_password')>0) {
        $message_account_password = $messageStack->output('account_password');
      }

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));

      return $this->render('password.tpl',[
        'account_password_action' => tep_href_link(FILENAME_ACCOUNT_PASSWORD, 'action=process', 'SSL'),
        'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
        'message_account_password' => $message_account_password,
      ]);

    }

    public function actionPasswordForgotten(){
      global $messageStack, $affiliate_ref, $breadcrumb;

      $email_address = '';
      if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
        $email_address = tep_db_prepare_input($_POST['email_address']);

        if ( empty($email_address) ) {
          $check_customer_query = tep_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE 1=0");
        }else {
          $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' ORDER BY opc_temp_account ASC");
        }
        if (tep_db_num_rows($check_customer_query)) {
          $check_customer = tep_db_fetch_array($check_customer_query);
          if ( opc::is_temp_customer($check_customer['customers_id']) ){
            $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
            //opc::remove_temp_customer( $check_customer['customers_id'] );
          }else{
            $new_password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
            $crypted_password = \common\helpers\Password::encrypt_password($new_password);

            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");

            // {{
            $email_params = array();
            $email_params['STORE_NAME'] = STORE_NAME;
            $email_params['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
            $email_params['NEW_PASSWORD'] = $new_password;
            $email_params['CUSTOMER_FIRSTNAME'] = $check_customer['customers_firstname'];
            $e = explode("://", HTTP_SERVER);
            $email_params['HTTP_HOST'] = '<a href="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . '">' . $e[1] . '</a>';
            $email_params['CUSTOMER_EMAIL'] = $email_address;
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Password Forgotten', $email_params);
            // }}
            \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_params);

            $messageStack->add_session('login', SUCCESS_PASSWORD_SENT, 'success');

            tep_redirect(tep_href_link('account/login', '', 'SSL'));
          }
        } else {
          $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
        }
      }

      $messages_password_forgotten = '';
      if ( $messageStack->size('password_forgotten')>0 ) {
        $messages_password_forgotten = $messageStack->output('password_forgotten'); 
      }

      $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account/login', '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account/password-forgotten', '', 'SSL'));

      return $this->render('password_forgotten.tpl',[
        'messages_password_forgotten' => $messages_password_forgotten,
        'email_address' => $email_address,
        'account_password_forgotten_action' => tep_href_link('account/password-forgotten', 'action=process', 'SSL'),
        'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
      ]);
    }

  public function actionNewsletters()
  {
    global $messageStack, $navigation, $customer_id, $breadcrumb;
    
    if (!tep_session_is_registered('customer_id')) {
      $navigation->set_snapshot();
      tep_redirect(tep_href_link('account/login', '', 'SSL'));
    }

    $newsletter_query = tep_db_query("select customers_newsletter from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
      if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
        $newsletter_general = tep_db_prepare_input($_POST['newsletter_general']);
      } else {
        $newsletter_general = '0';
      }

      if ($newsletter_general != $newsletter['customers_newsletter']) {
        $newsletter_general = (($newsletter['customers_newsletter'] == '1') ? '0' : '1');

        tep_db_query("update " . TABLE_CUSTOMERS . " set customers_newsletter = '" . (int)$newsletter_general . "' where customers_id = '" . (int)$customer_id . "'");
      }

      $messageStack->add_session('account', SUCCESS_NEWSLETTER_UPDATED, 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }

    $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'));

    return $this->render('newsletters.tpl',[
      'account_newsletter_action' => tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, 'action=process', 'SSL'),
      'newsletter_general' => $newsletter['customers_newsletter']!=0,
      'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
    ]);
  }



  public function actionEdit() {
        global $languages_id, $language, $navigation, $breadcrumb, $messageStack, $customer_id;
        global $customer_default_address_id, $customer_first_name;

      \common\helpers\Translation::init('js');

        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }

        if (isset($_GET['action']) && ($_GET['action'] == 'process')) {

            $error = false;

            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $gender = tep_db_prepare_input(Yii::$app->request->post('gender'));
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register'])) {
                    if (($gender != 'm') && ($gender != 'f') && ($gender != 's')) {
                        $error = true;
                        $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
                    }
                }
            }
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $firstname = tep_db_prepare_input(Yii::$app->request->post('firstname'));
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('account_edit', sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                    }
                }
            }
            if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $lastname = tep_db_prepare_input(Yii::$app->request->post('lastname'));
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])) {
                    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('account_edit', sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                    }
                }
            }
            if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $dob = tep_db_prepare_input(Yii::$app->request->post('dob'));
                if (!empty($dob)) {
                    $dob = \common\helpers\Date::date_raw($dob);
                    if (!checkdate(date('m', strftime($dob)), date('d', strftime($dob)), date('Y', strftime($dob)))) {
                        if (in_array(ACCOUNT_DOB, ['required', 'required_register'])) {
                            $error = true;
                            $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
                        } else {
                            $dob = '0000-00-00';
                        }
                    }
                } else {
                    if (in_array(ACCOUNT_DOB, ['required', 'required_register'])) {
                        $error = true;
                        $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
                    } else {
                        $dob = '0000-00-00';
                    }
                }
            }
            if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $telephone = tep_db_prepare_input(Yii::$app->request->post('telephone'));
                if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register']) && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('account_edit', sprintf(ENTRY_TELEPHONE_NUMBER_ERROR, ENTRY_TELEPHONE_MIN_LENGTH));
                }
            }
            if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $landline = tep_db_prepare_input(Yii::$app->request->post('landline'));
                if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register']) && strlen($landline) < ENTRY_LANDLINE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('account_edit', sprintf(ENTRY_LANDLINE_NUMBER_ERROR, ENTRY_LANDLINE_MIN_LENGTH));
                }
            }
            
            if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $company = tep_db_prepare_input(Yii::$app->request->post('company'));
                if (in_array(ACCOUNT_COMPANY, ['required', 'required_register']) && empty($company)) {
                    $error = true;
                    $messageStack->add('account_edit', ENTRY_COMPANY_ERROR);
                }
            }

            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])) {
                $company_vat = tep_db_prepare_input(Yii::$app->request->post('company_vat'));
                if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register']) &&  (empty($company_vat) || !\common\helpers\Validations::checkVAT($company_vat))) {
                    $error = true;
                    $messageStack->add('account_edit', ENTRY_VAT_ID_ERROR);
                }
            }
            
            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])) {
                if (!empty($company_vat) and ( !\common\helpers\Validations::checkVAT($company_vat))) {
                    $error = true;
                    $messageStack->add('account_edit', ENTRY_VAT_ID_ERROR);
                }
            }

            $email_address = tep_db_prepare_input($_POST['email_address']);
            if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
                $error = true;

                $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
            }
            if (!\common\helpers\Validations::validate_email($email_address)) {
                $error = true;
                $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
            }
            $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_id != '" . (int) $customer_id . "' and opc_temp_account=0");
            $check_email = tep_db_fetch_array($check_email_query);
            if ($check_email['total'] > 0) {
                $error = true;
                $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
            }

            if ($error == false) {
                $sql_data_array = [
                    'customers_email_address' => $email_address,
                ];

                if (isset($gender)) {
                    $sql_data_array['customers_gender'] = $gender;
                }
                if (isset($firstname)) {
                    $sql_data_array['customers_firstname'] = $firstname;
                }
                if (isset($lastname)) {
                    $sql_data_array['customers_lastname'] = $lastname;
                }
                if (isset($dob)) {
                    $sql_data_array['customers_dob'] = \common\helpers\Date::date_raw($dob);
                }
                if (isset($telephone)) {
                    $sql_data_array['customers_telephone'] = $telephone;
                }
                if (isset($landline)) {
                    $sql_data_array['customers_landline'] = $landline;
                }
                if (isset($company)) {
                    $sql_data_array['customers_company'] = $company;
                }
                if (isset($company_vat)) {
                    $sql_data_array['customers_company_vat'] = $company_vat;
                }

                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "'");

                tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int) $customer_id . "'");

                $sql_data_array = array(
                    'entry_firstname' => $firstname,
                    'entry_lastname' => $lastname
                );
                if (ACCOUNT_GENDER == 'required' || ACCOUNT_GENDER == 'required_register' || ACCOUNT_GENDER == 'visible' || ACCOUNT_GENDER == 'visible_register')
                    $sql_data_array['entry_gender'] = $gender;

                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "' and address_book_id = '" . (int) $customer_default_address_id . "'");

// reset the session variables
                $customer_first_name = $firstname;

                $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');

                tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
            }
        }

        $account_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
        $account = tep_db_fetch_array($account_query);
        $account_array = array();
        if ($messageStack->size('account_edit') > 0) {
            $account_array['message'] = '<div class="main">' . $messageStack->output('account_edit') . '</div>';
        }

        if (ACCOUNT_GENDER == 'required' || ACCOUNT_GENDER == 'required_register' || ACCOUNT_GENDER == 'visible' || ACCOUNT_GENDER == 'visible_register') {
            $custom_gender = (isset($gender) ? $gender : $account['customers_gender']);
        }

        $back_link = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));

        return $this->render('accountedit.tpl', ['description' => '', 'account_array' => $account_array, 'action' => tep_href_link('account/edit', 'action=process'),
                    'custom_gender' => $custom_gender,
                    'process' => 'process',
                    'firstname' => $account['customers_firstname'],
                    'lastname' => $account['customers_lastname'],
                    'customers_dob' => \common\helpers\Date::date_short($account['customers_dob']),
                    'email_address' => $account['customers_email_address'],
                    'telephone' => $account['customers_telephone'],
                    'fax' => $account['customers_fax'],
                    'landline' => $account['customers_landline'],
                    'back_link' => $back_link,
                    'customers_company' => $account['customers_company'],
                    'customers_company_vat' => $account['customers_company_vat'],
        ]);
    }

    public function actionHistoryInfo() {
        global $cart;
        global $languages_id, $language, $navigation, $customer_id, $breadcrumb, $currencies;
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }

        if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        }
        $historyOrderId = (int)$_GET['order_id'];

        $customer_info_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$historyOrderId . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);
        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']), tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $historyOrderId, 'SSL'));

        $order = new order($historyOrderId);
        $order_info = array();
        $order_title = $historyOrderId;
        $order_date = \common\helpers\Date::date_long($order->info['date_purchased']);
        $order_info_status = $order->info['orders_status_name'];
        $order_info_total = $order->info['total'];
        $order_delivery_address = '';
        $order_shipping_method = '';
        if ($order->delivery != false) {
            $order_delivery_address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
            if (tep_not_null($order->info['shipping_method'])) {
                $order_shipping_method = $order->info['shipping_method'];
            }
        }
        $order_info['tax_groups'] = '';
        $tax_groups = sizeof($order->info['tax_groups']);
        $order_product = array();
        //echo '<pre>';print_r($order->products);
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_img = tep_db_fetch_array(tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $order->products[$i]['id'] . "'"));
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = $order->products[$i]['qty'];
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = array();
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                //$order_info_attr['size'] = sizeof($order->products[$i]['attributes']);
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    $order_info_attr[$j]['order_pr_option'] = str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option']));
                    $order_info_attr[$j]['order_pr_value'] = ($order->products[$i]['attributes'][$j]['value'] ? htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                }
            }
            $order_info['attr_array'] = $order_info_attr;
            if (sizeof($order->info['tax_groups']) > 1) {
                $order_info['order_products_tax'] = \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%';
            }
            $order_info['final_price'] = $currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
            $order_product[] = $order_info;
        }
        $order_billing = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $payment_method = $order->info['payment_method'];
        $order_info_array = array();
        $order_info_ar = array();
        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'payLink')) {
            $pay_link = $ext::payLink($historyOrderId);
        }
        $reorder_link = tep_href_link('checkout/reorder', 'order_id=' . (int)$historyOrderId, 'SSL');
        if ($pay_link) {
            $reorder_link = false;
        }
        
        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {

            if (file_exists( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php')) {
                include_once( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php');
            }

            if (class_exists($order->totals[$i]['class'])) {
                $object = new $order->totals[$i]['class'];
                if (method_exists($object, 'visibility')) {
                    if (true == $object->visibility(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT')) {
                        if (method_exists($object, 'visibility')) {
                            $order_info_ar[] = $object->displayText(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT', $order->totals[$i]);
                        } else {
                            $order_info_ar[] = $total;
                        }
                    }
                }
            }
        }
        
        $statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$historyOrderId . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int) $languages_id . "' order by osh.date_added");
        $order_statusses = array();
        while ($statuses = tep_db_fetch_array($statuses_query)) {
            $statuses['date'] = \common\helpers\Date::date_short($statuses['date_added']);
            $statuses['status_name'] = $statuses['orders_status_name'];
            $statuses['comments_new'] = (empty($statuses['comments']) ? '&nbsp;' : nl2br(\common\helpers\Output::output_string_protected($statuses['comments'])));
            $order_statusses[] = $statuses;
        }
        if (DOWNLOAD_ENABLED == 'true')
            include(DIR_WS_MODULES . 'downloads.php');
        $print_order_link = tep_href_link(FILENAME_ORDERS_PRINTABLE, \common\helpers\Output::get_all_get_params(array('orders_id')) . 'orders_id=' . $historyOrderId);
        $back_link = tep_href_link('account/history', \common\helpers\Output::get_all_get_params(array('order_id')), 'SSL');
        return $this->render('historyinfo.tpl', [
                    'description' => '',
                    'order' => $order,
                    'order_delivery_address' => $order_delivery_address,
                    'order_shipping_method' => $order_shipping_method,
                    'tax_groups' => $tax_groups,
                    'order_product' => $order_product,
                    'order_info_ar' => $order_info_ar,
                    'order_statusses' => $order_statusses,
                    'print_order_link' => $print_order_link,
                    'back_link' => $back_link,
                    'order_title' => $order_title,
                    'order_date' => $order_date,
                    'order_info_total' => $order_info_total,
                    'order_info_status' => $order_info_status,
                    'order_billing' => $order_billing,
                    'payment_method' => $payment_method,
                    'reorder_link' => $reorder_link,
                    'reorder_confirm' => ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : ''),
                    'pay_link' => $pay_link,
        ]);
    }

    public function actionHistory() {
        global $cart, $languages_id, $language, $navigation, $customer_id, $breadcrumb;

        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        $orders_total = \common\helpers\Customer::count_customer_orders();
        $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int) $customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' and o.orders_status != '99999' order by orders_id DESC";
        $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
        $history_query = tep_db_query($history_split->sql_query);
        $history_links = $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y')), 'account/history');
        $history_array = array();
        while ($history = tep_db_fetch_array($history_query)) {
            $products_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $history['orders_id'] . "'");
            $products = tep_db_fetch_array($products_query);

            if (tep_not_null($history['delivery_name'])) {
                $history['type'] = TEXT_ORDER_SHIPPED_TO;
                $history['name'] = $history['delivery_name'];
            } else {
                $history['type'] = TEXT_ORDER_BILLED_TO;
                $history['name'] = $history['billing_name'];
            }
            $history['count'] = $products['count'];
            $history['date'] = \common\helpers\Date::date_long($history['date_purchased']);
            $history['link'] = tep_href_link('account/history-info', (isset($_GET['page']) ? 'page=' . (int)$_GET['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL');

            $history['reorder_link'] = tep_href_link('checkout/reorder', 'order_id=' . (int) $history['orders_id'], 'SSL');
            $history['reorder_confirm'] = ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : '');

            $history_array[] = $history;
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));

        return $this->render('history.tpl', ['description' => '', 'orders_total' => $orders_total, 'history_array' => $history_array, 'number_of_rows' => $history_split->number_of_rows, 'links' => $history_links, 'history_count' => $history_split->display_count(LISTING_PAGINATION), 'account_back' => '<a class="btn" href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . IMAGE_BUTTON_BACK . '</a>']);
    }

    public function actionAddressBook() {
        global $languages_id, $language, $navigation, $breadcrumb, $customer_id, $messageStack, $customer_default_address_id;

        if ($messageStack->size('addressbook') > 0) {
            $message = $messageStack->output('addressbook');
        }
        $addresses_query = tep_db_query("select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer_id . "' order by firstname, lastname");
        $address_array = array();
        while ($addresses = tep_db_fetch_array($addresses_query)) {
            $format_id = \common\helpers\Address::get_address_format_id($addresses['country_id']);
            $addresses['text'] = $addresses['city'] . ' ' . $addresses['postcode'] . ' ' . \common\helpers\Country::get_country_name($addresses['country_id']);
            $addresses['format'] = $addresses['street_address'] . ' ' . $addresses['suburb'] . ' ' . $addresses['city'] . ' ' . $addresses['state'] . ' ' . $addresses['postcode'] . ' ' . \common\helpers\Country::get_country_name($addresses['country_id']);
            $addresses['link_edit'] = tep_href_link('account/address-book-process', 'edit=' . $addresses['address_book_id'], 'SSL');
            $addresses['link_delete'] = tep_href_link('account/address-book-process', 'delete=' . $addresses['address_book_id'], 'SSL');
            $addresses['default_address'] = $customer_default_address_id;
            $addresses['customers'] = \common\helpers\Output::output_string_protected($addresses['firstname'] . ' ' . $addresses['lastname']);
            $address_array[] = $addresses;
        }
        if (\common\helpers\Customer::count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
            $addr_process = tep_href_link('account/address-book-process', '', 'SSL');
        }
        $link_back = tep_href_link('account', '', 'SSL');
        $max_val = sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES);

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(TEXT_ADDRESS_BOOK, tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

        return $this->render('address_book.tpl', ['message' => $message, 'address_array' => $address_array, 'addr_process' => $addr_process, 'link_back' => $link_back, 'max_val' => $max_val, 'customer_id' => $customer_id]);
    }

    public function actionAddressBookProcess(){
        global $languages_id, $language, $customer_groups_id, $breadcrumb, $navigation, $customer_id, $messageStack, $customer_default_address_id;
        global $customer_first_name, $customer_country_id, $customer_zone_id;

        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        $group = $customer_groups_id;

        if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int) $_GET['delete'] . "' and customers_id = '" . (int) $customer_id . "'");

            $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

            tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
        }

// error checking when updating or adding an entry
        $process = false;
        if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {
            $process = true;
            $error = false;

            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $gender = tep_db_prepare_input(Yii::$app->request->post('gender'));
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register']) && empty($gender)) {
                    $error = true;
                    $messageStack->add('addressbook', ENTRY_GENDER_ERROR);
                }
            }
            
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $firstname = tep_db_prepare_input(Yii::$app->request->post('firstname'));
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register']) && strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('addressbook', sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                }
            }

            if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $lastname = tep_db_prepare_input(Yii::$app->request->post('lastname'));
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register']) && strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('addressbook', sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                }
            }
            
            if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $postcode = tep_db_prepare_input(Yii::$app->request->post('postcode'));
                if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register']) && strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('addressbook', sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH));
                }
            }
            
            if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                $street_address = tep_db_prepare_input(Yii::$app->request->post('street_address_line1'));
                if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register']) && strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('addressbook', sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                }
            }
            
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $suburb = tep_db_prepare_input(Yii::$app->request->post('street_address_line2'));
                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register']) &&  empty($suburb)) {
                    $error = true;
                    $messageStack->add('addressbook', ENTRY_SUBURB_ERROR);
                }
            }
            
            if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $city = tep_db_prepare_input(Yii::$app->request->post('city'));
                if (in_array(ACCOUNT_CITY, ['required', 'required_register']) && strlen($city) < ENTRY_CITY_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('addressbook', sprintf(ENTRY_CITY_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                }
            }
            
            if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $country = tep_db_prepare_input(Yii::$app->request->post('country'));
                if (is_numeric($country) == false) {
                    if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register'])) {
                        $error = true;
                        $messageStack->add('addressbook', ENTRY_COUNTRY_ERROR);
                    } else {
                        $country = (int) STORE_COUNTRY;
                        $zone_id = (int) STORE_ZONE;
                    }
                }
            } else {
                $country = (int) STORE_COUNTRY;
                $zone_id = (int) STORE_ZONE;
            }
            
            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $state = tep_db_prepare_input(Yii::$app->request->post('state'));
                $zone_id = 0;
                $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
                $check = tep_db_fetch_array($check_query);
                $entry_state_has_zones = ($check['total'] > 0);
                if ($entry_state_has_zones == true) {
                    $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and zone_name = '" . tep_db_input($state) . "'");
                    if (tep_db_num_rows($zone_query) == 1) {
                        $zone = tep_db_fetch_array($zone_query);
                        $zone_id = $zone['zone_id'];
                    } elseif (ACCOUNT_STATE == 'required_register') {
                        $error = true;
                        $messageStack->add('addressbook', ENTRY_STATE_ERROR_SELECT);
                    }
                } else {
                    if (strlen($state) < ENTRY_STATE_MIN_LENGTH && ACCOUNT_STATE == 'required_register') {
                        $error = true;
                        $messageStack->add('addressbook', sprintf(ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                    }
                }
            }

            if ($error == false) {
                
                $sql_data_array = array(
                    'customers_id' => $customer_id,
                    'entry_country_id' => (isset($country) ? $country : STORE_COUNTRY),
                );

                if (isset($gender)) {
                    $sql_data_array['entry_gender'] = $gender;
                }
                if (isset($firstname)) {
                    $sql_data_array['entry_firstname'] = $firstname;
                }
                if (isset($lastname)) {
                    $sql_data_array['entry_lastname'] = $lastname;
                }
                if (isset($postcode)) {
                    $sql_data_array['entry_postcode'] = $postcode;
                }
                if (isset($street_address)) {
                    $sql_data_array['entry_street_address'] = $street_address;
                }
                if (isset($suburb))  {
                    $sql_data_array['entry_suburb'] = $suburb;
                }
                if (isset($city)) {
                    $sql_data_array['entry_city'] = $city;
                }
                if ($zone_id > 0) {
                    $sql_data_array['entry_zone_id'] = $zone_id;
                    $sql_data_array['entry_state'] = '';
                } else {
                    $sql_data_array['entry_zone_id'] = '0';
                    $sql_data_array['entry_state'] = isset($state) ? $state : '';
                }
                
                if ($_POST['action'] == 'update') {
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int) $_GET['edit'] . "' and customers_id ='" . (int) $customer_id . "'");

// reregister session variables
                    if ((isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $customer_default_address_id)) {
                        $customer_first_name = $firstname;
                        $customer_country_id = $country;
                        $customer_zone_id = (($zone_id > 0) ? (int) $zone_id : '0');
                        $customer_default_address_id = (int) $_GET['edit'];

                        $sql_data_array = array('customers_firstname' => $firstname,
                            'customers_lastname' => $lastname,
                            'customers_default_address_id' => (int) $_GET['edit']);

                        if (isset($gender)) {
                            $sql_data_array['customers_gender'] = $gender;
                        }

                        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "'");
                    }
                } else {
                    $sql_data_array['customers_id'] = (int) $customer_id;
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

                    $new_address_book_id = tep_db_insert_id();

// reregister session variables
                    if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
                        $customer_first_name = $firstname;
                        $customer_country_id = $country;
                        $customer_zone_id = (($zone_id > 0) ? (int) $zone_id : '0');
                        if (isset($_POST['primary']) && ($_POST['primary'] == 'on'))
                            $customer_default_address_id = $new_address_book_id;

                        $sql_data_array = array('customers_firstname' => $firstname,
                            'customers_lastname' => $lastname);

                        if (isset($gender)) {
                            $sql_data_array['customers_gender'] = $gender;
                        }
                        if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
                            $sql_data_array['customers_default_address_id'] = $new_address_book_id;
                        }

                        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "'");
                    }
                }

                $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');

                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            }
        }

        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $entry_query = tep_db_query("select entry_gender, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer_id . "' and address_book_id = '" . (int) $_GET['edit'] . "'");

            if (!tep_db_num_rows($entry_query)) {
                $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            }

            $entry = tep_db_fetch_array($entry_query);
        } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            if ($_GET['delete'] == $customer_default_address_id) {
                $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            } else {
                $check_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int) $_GET['delete'] . "' and customers_id = '" . (int) $customer_id . "'");
                $check = tep_db_fetch_array($check_query);

                if ($check['total'] < 1) {
                    $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

                    tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
                }
            }
        } else {
            $entry = array();
        }

        if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
            if (\common\helpers\Customer::count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
                $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            }
        }

        $get_delete = $_GET['delete'];
        $get_edit = $_GET['edit'];
        $action = tep_href_link('account/address-book-process', (isset($_GET['edit']) ? 'edit=' . $_GET['edit'] : ''), 'SSL');
        $title = (isset($_GET['edit']) ? HEADING_TITLE_MODIFY_ENTRY : (isset($_GET['delete']) ? HEADING_TITLE_DELETE_ENTRY : HEADING_TITLE_ADD_ENTRY));
        $message = '';
        if ($messageStack->size('addressbook') > 0) {
            $message = $messageStack->output('addressbook');
        }
        $address_label = '';
        if (isset($_GET['delete'])) {
            $address_label = \common\helpers\Address::address_label($customer_id, $_GET['delete'], true, ' ', '<br>');
        }
        $link_address_book = tep_href_link('account/address-book', '', 'SSL');
        $link_address_delete = tep_href_link('account/address-book-process', 'delete=' . $_GET['delete'] . '&action=deleteconfirm', 'SSL');
        if (!isset($process))
            $process = false;
        $zones_array = array();
        $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country . "' order by zone_name");
        $zone_selected = 0;
        while ($zones_values = tep_db_fetch_array($zones_query)) {
            if ($state != '' && $zone_selected == 0) {
                if (strpos($state, $zones_values['zone_name']) === 0) {
                    $zone_selected = $zones_values['zone_id'];
                }
            }
            $zones_array[] = array('id' => $zones_values['zone_id'], 'text' => $zones_values['zone_name']);
        }
        if ($process == true) {
            $tmp_country = $country;
        } else {
            $tmp_country = $entry['entry_country_id'];
        }
        if ((isset($_GET['edit']) && ($customer_default_address_id != $_GET['edit'])) || (isset($_GET['edit']) == false)) {
            $set_primary = 1;
        } else {
            $set_primary = 0;
        }
        $links = array();
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $links['back_url'] = tep_href_link('account/address-book', '', 'SSL');
            $links['back_text'] = IMAGE_BUTTON_BACK;
            $links['update'] = tep_draw_hidden_field('action', 'update') . tep_draw_hidden_field('edit', $_GET['edit']) . '<button class="btn-2">' . IMAGE_BUTTON_UPDATE . '</button>';
        } else {
            if (sizeof($navigation->snapshot) > 0) {
                $back_link = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
            } else {
                $back_link = tep_href_link('account/address-book', '', 'SSL');
            }
            $links['back_url'] = $back_link;
            $links['back_text'] = IMAGE_BUTTON_BACK;
            $links['update'] = tep_draw_hidden_field('action', 'process') . '<button class="btn-2">' . IMAGE_BUTTON_CONTINUE . '</button>';
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(TEXT_ADDRESS_BOOK, tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $_GET['edit'], 'SSL'));
        } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $_GET['delete'], 'SSL'));
        } else {
            $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL'));
        }

        return $this->render('address_book_process.tpl', ['get_delete' => $get_delete, 'get_edit' => $get_edit, 'action' => $action, 'title' => $title, 'link_address_book' => $link_address_book, 'link_address_delete' => $link_address_delete, 'entry_gender' => $entry['entry_gender'], 'entry_firstname' => $entry['entry_firstname'], 'entry_lastname' => $entry['entry_lastname'], 'entry_suburb' => $entry['entry_suburb'], 'entry_postcode' => $entry['entry_postcode'], 'street_address_line1' => $entry['entry_street_address'], 'city' => $entry['entry_city'], 'get_zone_name' => \common\helpers\Zones::get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']), 'entry_state_has_zones' => $entry_state_has_zones, 'zones_array' => $zones_array, 'state' => $state, 'country' => tep_get_country_list('country', $tmp_country, 'id="country"'), 'links' => $links, 'address_label' => $address_label, 'process' => $process, 'message' => $message, 'set_primary' => $set_primary]);
    }

    public function actionInvoice() {
        
        global $languages_id, $language, $customer_id;

        if ( !tep_session_is_registered('customer_id') ) {
          tep_redirect(tep_href_link('account/login','','SSL'));
        }
  

        $this->layout = false;
        
        $oID = Yii::$app->request->get('order_id');
        
        //$customer_number_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". tep_db_input(tep_db_prepare_input($oID)) . "'");
        //$customer_number = tep_db_fetch_array($customer_number_query);

        $payment_info_query = tep_db_query("select * from " . TABLE_ORDERS . " where customers_id='".(int)$customer_id."' AND orders_id = '". intval($oID) . "'");
        if ( tep_db_num_rows($payment_info_query)==0 ) {
          die;
        }
        $payment_info = tep_db_fetch_array($payment_info_query);
        //$payment_info = $payment_info['payment_info'];
        
        $currencies = new currencies();

        $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . tep_db_input($oID) . "'");

        $order = new order($oID);


?>
<!DOCTYPE html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE . ' - ' . TITLE_PRINT_ORDER . $oID; ?></title>
<base href="<?php echo (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="<?php echo Info::themeFile('/css/print.css'); ?>">
</head>
<body marginwidth="10" marginheight="10" topmargin="10" bottommargin="10" leftmargin="10" rightmargin="10" onLoad="window.focus();">
<?php
$template_name = 'Original'; // By default
?>
<div id="wrapper">
<div id="container">
<!-- body_text //-->
<table cellpadding="0" cellspacing="0" width="685" align="center">
    <tr>
        <td>
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="logo"><img src="<?php echo Yii::$app->view->theme->baseUrl.'/img/logo.png'; ?>" alt="logo"></td>
                    <td align="right" class="store_address">
											<div><?php echo STORE_NAME_ADDRESS; ?></div>
											<table cellspacing="0" cellpadding="0" width="100%" class="store_address_table">
												<tr>
													<td><?php echo ENTITY_PHONE_NUMBER;?></td>
													<td><?php echo STORE_PHONE;?></td>
												</tr>
												<tr>
													<td><?php echo TEXT_EMAIL;?>:</td>
													<td><?php echo STORE_OWNER_EMAIL_ADDRESS;?></td>
												</tr>
												<tr>
													<td><?php echo ENTITY_WEBSITE;?></td>
													<td><?php echo tep_href_link('/');?></td>
												</tr>
											</table>
										</td>
                </tr>
            </table>
            <table cellspacing="0" cellpadding="0" width="100%" class="shipping_table_data">
                <tr>
                    <td width="33%" valign="top" class="ship_data_bg">
                        <?php echo '<div class="shipTitle"><strong>' . CATEGORY_SHIPPING_ADDRESS . '</strong></div>'; ?>
                        <?php echo '<div>' . \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '&nbsp;', '<br>') . '</div>'; ?>
                    </td>
                    <td width="33%" valign="top">
                        <div class="smallTextTitle"><strong><?php echo ENTRY_CUSTOMER;?></strong></div>
                        <div class="smallTextDesc"><?php echo $payment_info['customers_name'];?></div>
                        <div class="smallTextTitle"><strong><?php echo ENTRY_TELEPHONE_NUMBER;?></strong></div>
                        <div class="smallTextDesc"><?php echo $payment_info['customers_telephone'];?></div>
                        <div class="smallTextTitle"><strong><?php echo ENTRY_EMAIL_ADDRESS;?></strong></div>
                        <div class="smallTextDesc"><?php echo $payment_info['customers_email_address'];?></div>
                    </td>
                    <td width="33%" rowspan="2" valign="top" class="barcode_td">
                        <div class="title_order"><?php echo TITLE_INVOICE_ORDER  . $oID; ?></div>
                        <div class="title_payment_date"><strong><?php echo ENTRY_PAYMENT_DATE;?></strong><br><?php echo  \common\helpers\Date::date_short($payment_info['date_purchased']);?></div>
                        <div class="title_payment_method"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong><br><?php echo strip_tags($order->info['payment_method']); ?></div>
                        <div class="barcode"><img alt="<?php echo $oID; ?>" src="<?php echo tep_href_link('account/order-barcode', 'oID=' . (int)$oID); ?>"></div>
                    </td>
                </tr>
                <tr>
                    <td width="33%">
                        <div class="shipServ"><strong><?php echo TEXT_SHIPPING_VIA;?></strong><br><?php echo $payment_info['shipping_method'];?></div>
                    </td>
                    <td width="33%">
                        <div class="shipServ"><strong><?php echo TEXT_SHIPPING_SERVICE;?></strong><br><?php echo $payment_info['shipping_method'];?></div>
                    </td>
                </tr>
            </table>
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo ENTRY_INVOICE_QTY;?></td>
                    <td class="dataTableHeadingContent" width="25%"><?php echo TABLE_TEXT_NAME; ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_TAX; ?></td>
                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
                </tr>
                <?php
                for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                echo '      <tr class="dataTableRow">' . "\n" .
                '        <td class="dataTableContent dataTableContent_border" valign="middle" align="left">' . $order->products[$i]['qty'] . '</td>' . "\n" .
                '        <td class="dataTableContentBorder dataTableContent_border" valign="middle">' . $order->products[$i]['name'] . '<br>';

                if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
                for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                //        echo '<nobr><small>&nbsp;<i> - ' . htmlspecialchars($order->products[$i]['attributes'][$j]['option']) . ': ' . htmlspecialchars($order->products[$i]['attributes'][$j]['value']) . '</i><br></small></nobr>';
                echo '<nobr><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option'])) . ($order->products[$i]['attributes'][$j]['value'] ? ': ' . htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '') . '</i><br></small></nobr>';
                }
                }

                echo '        </td>' . "\n" .
                '        <td class="dataTableContentBorder dataTableContent_border" valign="middle">' . $order->products[$i]['model'] . '</td>' . "\n";
                echo '        <td class="dataTableContentBorder dataTableContent_border" align="center" valign="middle">' . \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
                '        <td class="dataTableContentBorder dataTableContent_border" align="right" valign="middle">' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
                '        <td class="dataTableContentBorder dataTableContent_border" align="right" valign="middle">' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
                '        <td class="dataTableContent dataTableContent_border" align="right" valign="middle"><b>' . $currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
                echo '      </tr>' . "\n";
                }
                ?>
            </table>
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td width="33%" class="shipBottomTd">
                        <?php echo '<div class="shipBottomTitle"><strong>' . CATEGORY_SHIPPING_ADDRESS . '</strong></div>'; ?>
                        <?php echo '<div class="addresBottomShip">' . \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '&nbsp;', '<br>') . '</div>'; ?>
                    </td>
                    <td width="66%" class="shipBottomBg">
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                            <?php
                            for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
                            echo '          <tr>' . "\n" .
                            '            <td align="right" class="smallText">' . $order->totals[$i]['title'] . '</td>' . "\n" .
                            '            <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
                            '          </tr>' . "\n";
                            }
                            ?>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</div>
<div class="footer_info">
    <table cellspacing="0" cellpadding="0" width="685" align="center">
        <tr>
            <td width="33%" align="left">
                <div><strong><?php echo ENTITY_UNDELIVERED_RETURN;?></strong></div>
                <div><?php echo (STORE_NAME_ADDRESS); ?></div>
            </td>
            <td width="33%" align="center">
                <img src="<?php echo Yii::$app->view->theme->baseUrl.'/img/logo_small.png'; ?>">
                <div class="thanks_block"><?php echo ENTITY_THANKS;?></div>
            </td>
            <td width="33%" align="right">
                <div><strong><?php echo TEXT_INVOICE_INFO;?></strong></div>
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td align="right"><?php echo ENTITY_PHONE_NUMBER;?></td>
                        <td align="right"><?php echo STORE_PHONE;?></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo TEXT_EMAIL;?>:</td>
                        <td align="right"><?php echo STORE_OWNER_EMAIL_ADDRESS;?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</div>
<!-- body_text_eof //-->
</body>
</html>
<?php
    }

    public function actionGv_send(){
      //TODO
      /*/ {{
      $email_params = array();
      $email_params['STORE_NAME'] = STORE_NAME;
      $email_params['MESSAGE_TEXT'] = tep_db_prepare_input($HTTP_POST_VARS['message']);
      $email_params['GV_AMOUNT'] = $currencies->format($HTTP_POST_VARS['amount']);
      $email_params['CUSTOMERS_NAME'] = tep_db_prepare_input($HTTP_POST_VARS['send_name']);
      $email_params['FRIEND_NAME'] = tep_db_prepare_input($HTTP_POST_VARS['to_name']);
      $email_params['GV_CODE'] = $id1;
      $email_params['GV_REDEEM_URL'] = tep_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $id1, 'NONSSL', false);
      
      list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('GV Send to Friend', $email_params);
      // }} */
    }
  
    public function actionWishlist()
    {
      global $messageStack, $breadcrumb, $wish_list, $currencies, $navigation;

      if ( !tep_session_is_registered('customer_id') ) {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link('account/login','','SSL'));
      }

      $message_wish_list = '';
      if ( $messageStack->size('wishlist')>0 ){
        $message_wish_list = $messageStack->output('wishlist');
      }

      $products = $wish_list->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $products[$i]['image'] = Images::getImageUrl($products[$i]['id'], 'Small');
        $products[$i]['link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']);
        $products[$i]['final_price_formatted'] = $currencies->display_price($products[$i]['final_price'], \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id']));
        $products[$i]['remove_link'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products[$i]['id'].'&action=remove_wishlist','SSL');
        $products[$i]['move_in_cart'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products[$i]['id'].'&action=wishlist_move_to_cart','SSL');

        $products[$i]['oos'] = false;
        if (STOCK_ALLOW_CHECKOUT != 'true' && !(\common\helpers\Product::get_products_stock($products[$i]['id']) > 0)) {
          $products[$i]['oos'] = true;
        }
      }

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_WISHLIST, '', 'SSL'));

      if (Yii::$app->request->isAjax){
        return $this->render('wishlist_popup.tpl', [
                'message_wish_list' => $message_wish_list,
                'link_back_href' => tep_href_link(FILENAME_ACCOUNT,'','NONSSL'),
                'products' => $products,
        ]);
      } else {
        return $this->render('wishlist.tpl', [
                'message_wish_list' => $message_wish_list,
                'link_back_href' => tep_href_link(FILENAME_ACCOUNT,'','NONSSL'),
                'products' => $products,
        ]);
      }
    }

    public function actionOrderBarcode()
    {
        global $customer_id;
        $oID = intval(Yii::$app->request->get('oID'));
        $cID = intval(Yii::$app->request->get('cID', $customer_id));
        $check = tep_db_fetch_array(tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". (int)$oID . "'"));
        if ($cID > 0 && $check['customers_id'] == $cID) {
            tep_draw_barcode('', str_pad($oID, 8, '0', STR_PAD_LEFT));
        }
    }

    public function actionOrderQrcode()
    {

        $oID = intval(Yii::$app->request->get('oID'));
        $cID = intval(Yii::$app->request->get('cID', (isset($_SESSION['customer_id'])?$_SESSION['customer_id']:0)));
        $cID = (int)$cID;
        $check = tep_db_fetch_array(tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". (int)$oID . "'"));
        if ($cID > 0 && $check['customers_id'] == $cID) {
            $order = new order($oID);
            if (Yii::$app->request->get('tracking')) {
                \common\classes\qrcode\QRcode::png(TRACKING_NUMBER_URL . $order->info['tracking_number']);
            } else {
                \common\classes\qrcode\QRcode::png(\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n"));
            }
        }
    }
		public function actionSwitchPrimary()
    {
			global $messageStack, $customer_default_address_id, $navigation, $customer_id;
      if ( !tep_session_is_registered('customer_id') ) {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link('account/login','','SSL'));
      }

        $id = intval(Yii::$app->request->post('is_default'));

				$sql_data_array = array('customers_default_address_id'=>(int)$id);
      $check_book = tep_db_fetch_array(tep_db_query(
        "SELECT COUNT(*) AS check_own FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id='" . (int)$customer_id . "' AND address_book_id='".(int)$id."' "
      ));
      if ( $check_book['check_own'] ) {
				$customer_default_address_id = (int)$id;
				tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");
      }
      if ( tep_session_is_registered('sendto') ) tep_session_unregister('sendto');
      if ( tep_session_is_registered('billto') ) tep_session_unregister('billto');

				tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
				//return $this->render('address_book.tpl', ['message' => $message]);
    }
		public function actionSwitchNewsletter()
    {
			global $messageStack, $customer_id, $navigation;
      if ( !tep_session_is_registered('customer_id') ) {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link('account/login','','SSL'));
      }

      $newsletter_general = tep_db_prepare_input(Yii::$app->request->post('newsletter_general'));
      //$id = Yii::$app->request->post('id');
      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_newsletter = '" . ($newsletter_general == 'true' ? 1 : 0) . "' where customers_id = '" . (int)$customer_id . "'");   

			tep_redirect(tep_href_link('account', '', 'SSL'));
			//return $this->render('index.tpl', ['message' => $message]);
		}

    public function actionProductsReviews()
    {
      global $language, $breadcrumb, $customer_id, $navigation;

      if ( !tep_session_is_registered('customer_id') ) {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link('account/login','','SSL'));
      }
      
      $history_query_raw = 
        "select r.* ".
        "from " . TABLE_REVIEWS . " r " .
        " inner join ".TABLE_PRODUCTS." p on p.products_id=r.products_id ".
        "where r.customers_id = '" . (int)$customer_id . "' ".
        "order by r.reviews_id DESC";
      $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_NEW_REVIEWS);
      $history_query = tep_db_query($history_split->sql_query);
      $history_links = $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y')), 'account/products-reviews');
      $customer_reviews = array();
      while ($customer_review = tep_db_fetch_array($history_query)) {
        $customer_review['products_link'] = '';
        if ( \common\helpers\Product::check_product($customer_review['products_id']) ) {
          $customer_review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$customer_review['products_id'],'');
        }
        $customer_review['products_name'] = \common\helpers\Product::get_products_name($customer_review['products_id']);
        $customer_review['reviews_rating'];
        $customer_review['date_added_str'] = \common\helpers\Date::date_short($customer_review['date_added']);
        if ($customer_review['status']){
          $customer_review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
        }else{
          $customer_review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
        }
        $customer_review['view'] = tep_href_link('reviews/info','reviews_id='.$customer_review['reviews_id'].'&back=account-products-reviews'.(isset($_GET['page']) && (int)$_GET['page']>1?'-'.(int)$_GET['page']:''));
        //$back = array('account/products-reviews', isset($_GET['page'])?'page='.$_GET['page']:'','SSL');
        $customer_reviews[] = $customer_review;
      }

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE, tep_href_link('account/products-reviews', '', 'SSL'));

      $params = array(
        'listing_split' => $history_split,
        'this_filename' => 'account/products-reviews',
        'listing_display_count_format' => TEXT_DISPLAY_NUMBER_OF_REVIEWS,
      );

      return $this->render('products-reviews.tpl', ['reviews'=> $customer_reviews, 'params' => ['params'=>$params],'account_back_link'=>tep_href_link(FILENAME_ACCOUNT,'','SSL')]);
		} 
    public function actionCreditAmount(){
        global $language, $breadcrumb, $customer_id, $navigation;
        if ( !tep_session_is_registered('customer_id') ) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login','','SSL'));
          }        
        $this->layout = false;
        
        $currencies = new \common\classes\currencies();
          
        $history = [];
        $customer_history_query = tep_db_query("select * from " . TABLE_CUSTOMERS_CREDIT_HISTORY . " where customers_id='" . (int)$customer_id . "' order by customers_credit_history_id");
        while ($customer_history = tep_db_fetch_array($customer_history_query)) {
            $admin = '';
            if ($customer_history['admin_id'] > 0) {
                $check_admin_query = tep_db_query( "select * from admin where admin_id = '" . (int)$customer_history['admin_id'] . "'" );
                $check_admin = tep_db_fetch_array( $check_admin_query );
                if (is_array($check_admin)) {
                    $admin =  $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                }
            }
            $history[] = [
                'date' => \common\helpers\Date::datetime_short($customer_history['date_added']),
                'credit' => $customer_history['credit_prefix'] . $currencies->format($customer_history['credit_amount'], true, $customer_history['currency'], $customer_history['currency_value']),
                'notified' => $customer_history['customer_notified'],
                'comments' => $customer_history['comments'],
                'admin' => $admin,
            ];
        }
        return $this->render('credit-history.tpl', ['history' => $history]);
    }

    public function actionAddressState() {
      $term = tep_db_prepare_input(Yii::$app->request->get('term'));
      $country = tep_db_prepare_input(Yii::$app->request->get('country'));

      $search = '';
      if (!empty($term)) {
        $search = " and (zone_name like '%" . tep_db_input($term) . "%' or zone_code like '%" . tep_db_input($term) . "%') ";
      }

      $zones = array();
      $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . tep_db_input($country) . "' " . $search . " order by zone_name");
      while ($response = tep_db_fetch_array($zones_query)) {
        $zones[] = $response['zone_name'];
      }
      echo json_encode($zones);
    }



  public function actionTradeForm() {
    global $customer_id;
    \common\helpers\Translation::init('admin/customers');

    if (!$customer_id){
      tep_redirect(tep_href_link('account/login', '', 'SSL'));
    }


    $additionalFields = \common\helpers\Customer::get_additional_fields_tree($customer_id);
    $addresses = \common\helpers\Customer::get_address_book_data($customer_id);

    $customer = tep_db_fetch_array(tep_db_query("select customers_firstname, customers_lastname, platform_id, customers_email_address, customers_telephone, customers_company, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'"));


    foreach ($addresses as $key => $item){

      $addresses[$key]['address'] = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($item['country_id']), $item, 1, ' ', ',');

    }



    $countries = \common\helpers\Country::get_countries();
    $fields = array();
    foreach ($additionalFields as $group){
      foreach ($group['child'] as $item){
        $item['group'] = $group['name'];
        $fields[$item['code']] = $item;
      }
    }
    return $this->render('trade-form.tpl', [
      'customers_id' => $customer_id,
      'additionalFields' => $additionalFields,
      'addresses' => $addresses,
      'customer' => $customer,
      'fields' => $fields,
      'countries' => $countries
    ]);
  }

  public function actionTradeFormSubmit() {
    global $customer_id;
    $customers_id = $customer_id;
    $fields = tep_db_prepare_input(Yii::$app->request->post('field'));

    \common\helpers\Translation::init('admin/customers');

    foreach ($fields as $id => $value) {
      $check_query = tep_db_query("SELECT value FROM " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " WHERE customers_id = '" . (int)$customers_id . "' AND additional_fields_id = '" . (int)$id . "'");
      if (tep_db_num_rows($check_query) > 0) {
        tep_db_query("update " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " set value = '" . tep_db_input($value) . "' where customers_id = '" . (int)$customers_id . "' AND additional_fields_id = '" . (int)$id . "'");
      } else {
        $sql_data_array = [
          'additional_fields_id' => (int) $id,
          'customers_id' => (int) $customers_id,
          'value' => $value,
        ];
        tep_db_perform(TABLE_CUSTOMERS_ADDITIONAL_FIELDS, $sql_data_array);
      }
    }

  }

  public function cutStr($str, $len = 70, $spacer = ' '){

    $arr = array();
    if (strlen($str) > $len) {
      $n = stripos($str, $spacer, $len);
      if ($n !== false) {
        $arr[] = ltrim(substr($str, 0, $n+1));
        $str = ltrim(substr($str, $n+1));
        $arr = array_merge($arr, $this->cutStr($str, $len, $spacer));
      } else {
        $arr[] = $str;
      }
    } else {
      $arr[] = $str;
    }
    return $arr;
  }

  public function actionTradeAcc() {

    \common\helpers\Translation::init('admin/customers');
    $get = tep_db_prepare_input(Yii::$app->request->get());
    $additionalFields = \common\helpers\Customer::get_additional_fields($get['customers_id']);

    $customer = tep_db_fetch_array(tep_db_query("select customers_firstname, customers_lastname, platform_id, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$get['customers_id'] . "'"));

    $platform = tep_db_fetch_array(tep_db_query("
            select 
                p.platform_owner, 
                p.platform_name, 
                p.platform_telephone, 
                p.platform_landline, 
                p.platform_email_address,
                a.entry_street_address,
                a.entry_suburb,
                a.entry_city,
                a.entry_postcode
            from " . TABLE_PLATFORMS . " p, " . TABLE_PLATFORMS_ADDRESS_BOOK . " a 
            where p.is_default = 1 and a.platform_id = p.platform_id and a.is_default
            "));

    $padding_right = 8.5;
    $padding_left = 8.5;
    $width = 210 - $padding_right - $padding_left;

    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Holbi');
    $pdf->SetTitle('');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins($padding_left, 9.5, $padding_right);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->AddPage();


    $pdf->SetFont('promptblacki', '', '25');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell($width/2 + 2, 11, $platform['platform_name'], 0, 'L', 1, 0);


    $pdf->SetFont('promptblacki', '', '17');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(5,3,0,0);
    $pdf->MultiCell($width/2, 11, 'New Account Form', 0, 'L', 1, 1);

    $text = $platform['entry_street_address'] . ', ' . $platform['entry_suburb'] . ',
' . $platform['entry_city'] . ', ' . $platform['entry_postcode'] . '
Tel: ' . $platform['platform_telephone'] . ($platform['platform_landline'] ? ' Fax: ' : '') . $platform['platform_landline'] . '
E-mail: ' . $platform['platform_email_address'];

    $pdf->SetFont('prompt', '', '10');
    $pdf->SetTextColor(0,0,0);
    $pdf->setCellPaddings(2,1,0,5);
    $pdf->MultiCell('', '', $text, 0, 'L', 0, 1);

    $pdf->SetFont('promptblacki', '', '15');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell('', '', 'Customer Details', 0, 'L', 1, 1);


    $pdf->SetFont('prompt', '', '11.1');
    $pdf->SetTextColor(0,0,0);

    $pdf->setCellPaddings(0,1,1,5);
    $pdf->MultiCell(99, '', ' ', 0, 'L', 0, 0);

    $pdf->setCellPaddings(0,1,1,5);
    $pdf->MultiCell(40, '', 'Limited Company', 0, 'L', 0, 0);
    if ($additionalFields['limited_company']) {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 6.5, '', '', '', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 6.5, '', '', '', '', 300, '', false, false, 0, 'LB');
    }

    $pdf->setCellPaddings(17,1,0,5);
    $pdf->MultiCell(46, '', 'Sole Trader', 0, 'L', 0, 0);
    if ($additionalFields['sole_trader']) {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 6.5, '', '', 'N', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 6.5, '', '', 'N', '', 300, '', false, false, 0, 'LB');
    }

    $pdf->setCellMargins(0,2,0);
    $pdf->setCellPaddings(3,0,0,0);
    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Business Name', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['name'], 'B', 'L', 0, 1);

    $pdf->setCellMargins(0,0.2,0);

    $data = [
      'street_address' => $additionalFields['street_address'],
      'suburb' => $additionalFields['suburb'],
      'city' => $additionalFields['city'],
      'state' => $additionalFields['state'],
      'country_id' => $additionalFields['country']
    ];
    $address = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($additionalFields['country']), $data, 1, ' ', ',');

    $address_arr = $this->cutStr($address, 70, ' ');

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

    if (count($address_arr) > 1){
      for($i = 1; $i < count($address_arr); $i++){
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
      }
    }

    /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
    $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Postcode', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['postcode'], 'B', 'L', 0, 1);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Telephone No.', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell(($width-70)/2, '', $additionalFields['phone'], 'B', 'L', 0, 0);
    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(25, '', 'Fax No.', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell(($width-70)/2, '', $additionalFields['fax'], 'B', 'L', 0, 1);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'e-mail address', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $customer['customers_email_address'], 'B', 'L', 0, 1);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Nature of Business', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['nature_business'], 'B', 'L', 0, 1);

    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Owner’s Name', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['owners_firstname'] . ' ' . $additionalFields['owners_lastname'], 'B', 'L', 0, 1);

    $pdf->setCellMargins(0,0.2,0);

    $data = [
      'street_address' => $additionalFields['owners_street_address'],
      'suburb' => $additionalFields['owners_suburb'],
      'city' => $additionalFields['owners_city'],
      'state' => $additionalFields['owners_state'],
      'country_id' => $additionalFields['owners_country']
    ];
    $address = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($additionalFields['country']), $data, 1, ' ', ',');

    $address_arr = $this->cutStr($address, 70, ' ');

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

    if (count($address_arr) > 1){
      for($i = 1; $i < count($address_arr); $i++){
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
      }
    }

    /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
    $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Postcode', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['owners_postcode'], 'B', 'L', 0, 1);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Telephone No.', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['owners_phone'], 'B', 'L', 0, 1);


    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('promptblacki', '', '15');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell('', '', 'Discount', 0, 'L', 1, 1);


    $pdf->SetFont('prompt', '', '10');
    $pdf->SetTextColor(0,0,0);

    $pdf->setCellMargins(0,2,0);
    $pdf->setCellPaddings(0,1,3,5);
    $pdf->MultiCell(45, '', '33.33% Sale or Return', 0, 'R', 0, 0);
    if ($additionalFields['sale_return']) {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    }

    $pdf->MultiCell(32, '', '35% Firm', 0, 'R', 0, 0);
    if ($additionalFields['firm']) {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    }

    $pdf->MultiCell(56, '', '38.25% Cash With Order', 0, 'R', 0, 0);
    if ($additionalFields['cash_with_order']) {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
    }

    $pdf->MultiCell(46, '', '40% Cash & Carry', 0, 'R', 0, 0);
    if ($additionalFields['cash_carry']) {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', 'N', '', 300, '', false, false, 0, 'LB');
    } else {
      $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', 'N', '', 300, '', false, false, 0, 'LB');
    }


    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('promptblacki', '', '15');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell('', '', 'Bank Account Details', 0, 'L', 1, 1);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->SetTextColor(0,0,0);

    $pdf->setCellMargins(0,2,0);

    $pdf->MultiCell(45, '', 'Bank Name', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['bank_name'], 'B', 'L', 0, 1);

    $pdf->setCellMargins(0,0.2,0);

    $address_arr = $this->cutStr($additionalFields['bank_address'], 70, ' ');

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

    if (count($address_arr) > 1){
      for($i = 1; $i < count($address_arr); $i++){
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
      }
    }


    /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
    $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Account No', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell($width - 45, '', $additionalFields['bank_account_no'], 'B', 'L', 0, 1);


    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('promptblacki', '', '15');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell('', '', 'Trade References', 0, 'L', 1, 1);

    $pdf->SetTextColor(0,0,0);
    $pdf->setCellMargins(0,2,0);

    if ($additionalFields['trade_name_2']) {
      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_name_1'], 'B', 'L', 0, 0);

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_name_2'], 'B', 'L', 0, 1);
      $pdf->setCellMargins(0, 0.2, 0);

      $address_arr_1 = $this->cutStr($additionalFields['trade_address_1'], 18, ' ');
      $address_arr_2 = $this->cutStr($additionalFields['trade_address_2'], 18, ' ');

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.1');
      $pdf->MultiCell(($width - 90) / 2, '', $address_arr_1[0], 'B', 'L', 0, 0);

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.1');
      $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_address_2'], 'B', 'L', 0, 1);

      for ($i = 1; $i < 10; $i++) {
        if ($address_arr_1[$i] || $address_arr_2[$i]) {
          $pdf->SetFont('prompt', '', '11.1');
          $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
          $pdf->SetFont('courgette', '', '11.1');
          $pdf->MultiCell(($width - 90) / 2, '', $address_arr_1[$i] . ' ', 'B', 'L', 0, 0);

          $pdf->SetFont('prompt', '', '11.1');
          $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
          $pdf->SetFont('courgette', '', '11.1');
          $pdf->MultiCell(($width - 90) / 2, '', $address_arr_2[$i] . ' ', 'B', 'L', 0, 1);
        }
      }

      /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
      $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
      $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
      $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);

      $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
      $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
      $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
      $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);*/

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_phone_1'], 'B', 'L', 0, 0);
      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_phone_2'], 'B', 'L', 0, 1);
    } else {

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell($width - 45, '', $additionalFields['trade_name_1'], 'B', 'L', 0, 1);

      $pdf->setCellMargins(0,0.2,0);

      $address_arr = $this->cutStr($additionalFields['trade_address_1'], 70, ' ');

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

      if (count($address_arr) > 1){
        for($i = 1; $i < count($address_arr); $i++){
          $pdf->SetFont('prompt', '', '11.1');
          $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
          $pdf->SetFont('courgette', '', '11.6');
          $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
        }
      }


      /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
      $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

      $pdf->SetFont('prompt', '', '11.1');
      $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
      $pdf->SetFont('courgette', '', '11.6');
      $pdf->MultiCell($width - 45, '', $additionalFields['trade_phone_1'], 'B', 'L', 0, 1);
    }


    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('promptblacki', '', '15');
    $pdf->SetFillColor(0,0,0);
    $pdf->SetTextColor(255,255,255);
    $pdf->setCellPaddings(2,0,0,0);
    $pdf->MultiCell('', '', 'Declaration', 0, 'L', 1, 1);

    $pdf->SetFont('prompt', '', '10');
    $pdf->SetTextColor(0,0,0);
    $pdf->setCellMargins(0,2,0);

    $pdf->MultiCell('', '', 'I would like to open a trade account with ' . $platform['platform_owner'] . ' and agree to abide by their General Terms & Conditions – a signed copy of which is returned to them with this form in the supplied SAE. I have retained a copy for my reference. I am authorised to sign on behalf of the company I represent.', 0, 'L', 0, 1);


    $pdf->SetFont('prompt', '', '11.1');

    $pdf->MultiCell(45, '', 'Signed', 0, 'R', 0, 0);
    $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
    $pdf->MultiCell(45, '', 'Date', 0, 'R', 0, 0);
    $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);
    $pdf->setCellMargins(0,1.7,0);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Name in Full', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell(($width-90)/2, '', $additionalFields['name_in_full'], 'B', 'L', 0, 0);

    $pdf->SetFont('prompt', '', '11.1');
    $pdf->MultiCell(45, '', 'Position', 0, 'R', 0, 0);
    $pdf->SetFont('courgette', '', '11.6');
    $pdf->MultiCell(($width-90)/2, '', $additionalFields['position'], 'B', 'L', 0, 1);

    $pdf->setCellMargins(0,6,0);
    $pdf->SetFont('courgette', '', '17');
    $pdf->MultiCell('', '', 'Scottish books of exceptional value', 0, 'C', 0, 0);






    $pdf->Output('trade_acc_form', 'I');
    die;
  }
  
  public function actionSubscriptionHistory() {
        global $cart, $languages_id, $language, $navigation, $customer_id, $breadcrumb;

        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        
        \common\helpers\Translation::init('account/history');
        
        $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_SUBSCRIPTION . " where customers_id = '" . (int) $customer_id . "'");
        $orders_check = tep_db_fetch_array($orders_check_query);
        $orders_total = $orders_check['total'];
        
        $history_query_raw = "select o.*, s.orders_status_name from " . TABLE_SUBSCRIPTION . " o, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int) $customer_id . "'  and  o.subscription_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' and o.subscription_status != '99999' order by orders_id DESC";
        $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
        $history_query = tep_db_query($history_split->sql_query);
        $history_links = $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y')), 'account/history');
        $history_array = array();
        while ($history = tep_db_fetch_array($history_query)) {
            $history['date'] = \common\helpers\Date::date_long($history['date_purchased']);
            $history['link'] = tep_href_link('account/subscription-history-info', (isset($_GET['page']) ? 'page=' . (int)$_GET['page'] . '&' : '') . 'subscription_id=' . $history['orders_id'], 'SSL');
            $history_array[] = $history;
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));

        return $this->render('subscription-history.tpl', ['description' => '', 'orders_total' => $orders_total, 'history_array' => $history_array, 'number_of_rows' => $history_split->number_of_rows, 'links' => $history_links, 'history_count' => $history_split->display_count(Yii::t('app', 'Items %s to %s of %s total')), 'account_back' => '<a class="btn" href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . IMAGE_BUTTON_BACK . '</a>']);
    }
    
    public function actionSubscriptionHistoryInfo() {
        global $cart;
        global $languages_id, $language, $navigation, $customer_id, $breadcrumb, $currencies;
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }

        \common\helpers\Translation::init('account/history-info');
        
        if (!isset($_GET['subscription_id']) || (isset($_GET['subscription_id']) && !is_numeric($_GET['subscription_id']))) {
            tep_redirect(tep_href_link('account/subscription-history-info', '', 'SSL'));
        }
        $historySubscriptionId = (int)$_GET['subscription_id'];

        $customer_info_query = tep_db_query("select customers_id from " . TABLE_SUBSCRIPTION . " where subscription_id = '" . (int) $historySubscriptionId . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);
        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $historySubscriptionId), tep_href_link('account/subscription-history-info', 'subscription_id=' . $historySubscriptionId, 'SSL'));

        $order = new \common\classes\subscription($historySubscriptionId);
        $order_info = array();
        $order_title = $historySubscriptionId;
        $order_date = \common\helpers\Date::date_long($order->info['date_purchased']);
        $order_info_status = $order->info['orders_status'];
        $order_info_total = $order->info['total'];
        $order_delivery_address = '';
        $order_shipping_method = '';
        if ($order->delivery != false) {
            $order_delivery_address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
            if (tep_not_null($order->info['shipping_method'])) {
                $order_shipping_method = $order->info['shipping_method'];
            }
        }
        $order_info['tax_groups'] = '';
        $tax_groups = sizeof($order->info['tax_groups']);
        $order_product = array();
        //echo '<pre>';print_r($order->products);
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_img = tep_db_fetch_array(tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $order->products[$i]['id'] . "'"));
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = $order->products[$i]['qty'];
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = array();
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                //$order_info_attr['size'] = sizeof($order->products[$i]['attributes']);
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    $order_info_attr[$j]['order_pr_option'] = str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option']));
                    $order_info_attr[$j]['order_pr_value'] = ($order->products[$i]['attributes'][$j]['value'] ? htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                }
            }
            $order_info['attr_array'] = $order_info_attr;
            if (sizeof($order->info['tax_groups']) > 1) {
                $order_info['order_products_tax'] = \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%';
            }
            $order_info['final_price'] = $currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
            $order_product[] = $order_info;
        }
        $order_billing = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $payment_method = $order->info['payment_method'];
        $order_info_array = array();
        $order_info_ar = array();
        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
            $order_info_array['totals_tile'] = $order->totals[$i]['title'];
            $order_info_array['totals_text'] = $order->totals[$i]['text'];
            $order_info_ar[] = $order_info_array;
        }
        $statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_SUBSCRIPTION_STATUS_HISTORY . " osh where osh.subscription_id = '" . (int) $_GET['subscription_id'] . "' and osh.subscription_status_id = os.orders_status_id and os.language_id = '" . (int) $languages_id . "' order by osh.date_added");
        $order_statusses = array();
        while ($statuses = tep_db_fetch_array($statuses_query)) {
            $statuses['date'] = \common\helpers\Date::date_short($statuses['date_added']);
            $statuses['status_name'] = $statuses['orders_status_name'];
            $statuses['comments_new'] = (empty($statuses['comments']) ? '&nbsp;' : nl2br(\common\helpers\Output::output_string_protected($statuses['comments'])));
            $order_statusses[] = $statuses;
        }
       
        $print_order_link = tep_href_link('account/subscription-invoice', 'subscription_id=' . $historySubscriptionId);
        $back_link = tep_href_link('account/subscription-history', \common\helpers\Output::get_all_get_params(array('subscription_id')), 'SSL');
        
        if ($order_info_status != 'Canceled') {
            $reorder_link = tep_href_link('account/subscription-cancel', 'subscription_id=' . (int) $historySubscriptionId, 'SSL');
        } else {
            $reorder_link = '';
        }
        
        return $this->render('subscription-historyinfo.tpl', [
                    'description' => '',
                    'order' => $order,
                    'order_delivery_address' => $order_delivery_address,
                    'order_shipping_method' => $order_shipping_method,
                    'tax_groups' => $tax_groups,
                    'order_product' => $order_product,
                    'order_info_ar' => $order_info_ar,
                    'order_statusses' => $order_statusses,
                    'print_order_link' => $print_order_link,
                    'back_link' => $back_link,
                    'order_title' => $order_title,
                    'order_date' => $order_date,
                    'order_info_total' => $order_info_total,
                    'order_info_status' => $order_info_status,
                    'order_billing' => $order_billing,
                    'payment_method' => $payment_method,
                    'reorder_link' => $reorder_link,
                    'reorder_confirm' => ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : ''),
        ]);
    }
    
    public function actionSubscriptionInvoice($subscription_id) {
        global $customer_id;
        
        $customer_info_query = tep_db_query("select * from " . TABLE_SUBSCRIPTION . " where subscription_id = '" . (int)$subscription_id . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);
        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link('account/subscription-history', '', 'SSL'));
        }
        
        $set = 'payment';
        $module = $customer_info['payment_class'];
        $id = $customer_info['transaction_id'];
        $this->layout = false;
        
        if (file_exists(DIR_WS_MODULES . $set . '/' . $module . '.php')) {
            require_once(DIR_WS_MODULES . $set . '/' . $module . '.php');
            Yii::$app->get('platform')->config(PLATFORM_ID)->constant_up();
            $payment = new $module();
            $payment->download_invoice($id, true);
        }
        exit();
    }
    
    public function actionSubscriptionCancel($subscription_id) {
        global $customer_id;
        
        $customer_info_query = tep_db_query("select * from " . TABLE_SUBSCRIPTION . " where subscription_id = '" . (int)$subscription_id . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);
        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link('account/subscription-history', '', 'SSL'));
        }
        
        $set = 'payment';
        $module = $customer_info['payment_class'];
        $id = $customer_info['transaction_id'];
        $this->layout = false;
        
        if (file_exists(DIR_WS_MODULES . $set . '/' . $module . '.php')) {
            require_once(DIR_WS_MODULES . $set . '/' . $module . '.php');
            Yii::$app->get('platform')->config(PLATFORM_ID)->constant_up();
            $payment = new $module();
            $payment->cancel_subscription($customer_info['transaction_id']);
            
            $status = 100003;
            tep_db_query("update " . TABLE_SUBSCRIPTION . " set subscription_status = '" . tep_db_input($status) . "', last_modified = now() where subscription_id = '" . (int)$subscription_id . "'");
            
            tep_db_perform(TABLE_SUBSCRIPTION_STATUS_HISTORY, array(
                'subscription_id' => (int) $subscription_id,
                'subscription_status_id' => (int) $status,
                'date_added' => 'now()',
                'customer_notified' => 0,
                'comments' => '',
                'admin_id' => 0,
            ));
            
            
        }
        
        tep_redirect(tep_href_link('account/subscription-history-info', 'subscription_id=' . (int)$subscription_id, 'SSL'));
        
    }
    
    public function actionOrderPay() {
        global $languages_id, $language, $navigation, $customer_id, $breadcrumb, $currencies;
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        
        if (isset($_SESSION['pay_order_id']) && !isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))){
            $_GET['order_id'] = $_SESSION['pay_order_id'];
        }

        if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
            tep_redirect(tep_href_link('account/history', '', 'SSL'));
        }
        $payOrderId = (int)$_GET['order_id'];
        
        \common\helpers\Translation::init('account/history-info');

        $customer_info_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '" . (int) $payOrderId . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);
        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link('account/history', '', 'SSL'));
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link('account', '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account/history', '', 'SSL'));
        $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $payOrderId), tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $payOrderId, 'SSL'));

        global $order;
        $order = new order($payOrderId);
        
        $order_info = array();
        $order_title = $payOrderId;
        $order_date = \common\helpers\Date::date_long($order->info['date_purchased']);
        $order_info_status = $order->info['orders_status_name'];
        //$order->info['total'];
        $order_delivery_address = '';
        $order_shipping_method = '';
        if ($order->delivery != false) {
            $order_delivery_address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
            if (tep_not_null($order->info['shipping_method'])) {
                $order_shipping_method = $order->info['shipping_method'];
            }
        }
        $order_info['tax_groups'] = '';
        $tax_groups = sizeof($order->info['tax_groups']);
        $order_product = array();
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_img = tep_db_fetch_array(tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $order->products[$i]['id'] . "'"));
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = $order->products[$i]['qty'];
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = array();
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    $order_info_attr[$j]['order_pr_option'] = str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option']));
                    $order_info_attr[$j]['order_pr_value'] = ($order->products[$i]['attributes'][$j]['value'] ? htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                }
            }
            $order_info['attr_array'] = $order_info_attr;
            if (sizeof($order->info['tax_groups']) > 1) {
                $order_info['order_products_tax'] = \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%';
            }
            $order_info['final_price'] = $currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
            $order_product[] = $order_info;
        }
        $order_billing = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $payment_method = $order->info['payment_method'];
        $order_info_array = array();
        $order_info_ar = array();
        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {

            if (file_exists( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php')) {
                include_once( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php');
            }

            if (class_exists($order->totals[$i]['class'])) {
                $object = new $order->totals[$i]['class'];
                if (method_exists($object, 'visibility')) {
                    if (true == $object->visibility(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT')) {
                        if (method_exists($object, 'visibility')) {
                            $order_info_ar[] = $object->displayText(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT', $order->totals[$i]);
                        } else {
                            $order_info_ar[] = $total;
                        }
                    }
                }
            }
            if ($order->totals[$i]['class'] == 'ot_due' && $order->totals[$i]['value'] > 0) {
                $pay_link = tep_href_link('account/order-pay', 'order_id=' . (int) $payOrderId, 'SSL');
                $reorder_link = false;
            }            
        }        
        
        $payment_modules = new \common\classes\payment();
        $payment_modules->update_status();
        
        
        $payment_error = '';
        if (isset($_GET['payment_error']) && is_object($GLOBALS[$_GET['payment_error']]) && method_exists($GLOBALS[$_GET['payment_error']],'get_error')) {
          if (is_object($payment_modules)){
            $payment_modules->selected_module = $_GET['payment_error'];
            $payment_error = $payment_modules->get_error();
          } else {
            $payment_error = $GLOBALS[$_GET['payment_error']]->get_error();
          }
        }
        
        $_selected_payment = isset($_SESSION['payment']) && !empty($_SESSION['payment']) ? $_SESSION['payment'] : '';
        if (isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout') && isset($_POST['payment'])) {
            $_selected_payment = tep_db_prepare_input($_POST['payment']);
        }
        $selection = $payment_modules->selection(false, true);
        $_confirm_payment_selected = false;
        for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
            $payment_show = true;
            if (isset($selection[$i]['module_status'])) {
                $payment_show = $selection[$i]['module_status'];
            }
            if (!$payment_show) {
                $selection[$i]['hide_row'] = true;
            }
            if ($payment_show && $_selected_payment == $selection[$i]['id']) {
                $selection[$i]['checked'] = true;
                $_confirm_payment_selected = $selection[$i]['id'];
            }
            //methods
            if (isset($selection[$i]['methods']) && is_array($selection[$i]['methods'])) {
                for ($j = 0, $m = sizeof($selection[$i]['methods']); $j < $m; $j++) {
                    if ($payment_show && $_selected_payment == $selection[$i]['methods'][$j]['id']) {
                        $selection[$i]['methods'][$j]['checked'] = true;
                    }
                }
            }

            if (sizeof($selection) <= 1) {
                $selection[$i]['hide_input'] = true;
            }
        }
        if ($_confirm_payment_selected === false) {
            for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
                if (!$selection[$i]['hide_row']) {
                    $selection[$i]['checked'] = true;
                    $_confirm_payment_selected = $selection[$i]['id'];
                    break;
                }
            }
        }
        if (!isset($_SESSION['payment']) || empty($_SESSION['payment'])) {
            $_SESSION['payment'] = $_confirm_payment_selected;
        }

        $payment_javascript_validation = '';
        if (!defined('ONE_PAGE_POST_PAYMENT')) {
            ob_start();
            $javascript_validation = $payment_modules->javascript_validation();
            echo str_replace('document.checkout_payment', 'document.one_page_checkout', $javascript_validation);
            $payment_javascript_validation = ob_get_clean();
        }

        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'orderPay')) {
            return $ext::orderPay([
                    'order_id' => (int)$payOrderId,
                    'order' => $order,
                    'order_delivery_address' => $order_delivery_address,
                    'order_shipping_method' => $order_shipping_method,
                    'tax_groups' => $tax_groups,
                    'order_product' => $order_product,
                    'order_info_ar' => $order_info_ar,
                    'order_title' => $order_title,
                    'order_date' => $order_date,
                    'order_info_status' => $order_info_status,
                    'order_billing' => $order_billing,
                    'payment_method' => $payment_method,
                    'selection' => $selection,
                    'checkout_process_link' => Yii::$app->urlManager->createUrl(['account/order-pay']),
                    'payment_error' => $payment_error,
            ]);
        }
    }
    
    public function actionOrderConfirmation() {
        $this->layout = false;
        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'orderConfirmation')) {
            return $ext::orderConfirmation();
        }
    }
    
    public function actionOrderProcess() {
        global $payment, $customer_id, $navigation, $HTTP_GET_VARS;
        
        $order_id = (int)Yii::$app->request->post('order_id');
        if ($order_id == 0) {
            $order_id = $_SESSION['pay_order_id'];
        } else {
            if (!tep_session_is_registered('pay_order_id')) tep_session_register('pay_order_id');
            $_SESSION['pay_order_id'] = $order_id;
        }
        $redirectURL = tep_href_link('account/order-pay', 'order_id=' . $order_id, 'SSL');
        
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => $redirectURL));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }
        
        // sendto billto

        if ( (tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!tep_session_is_registered('payment')) ) {
            tep_redirect(tep_href_link($redirectURL, '', 'SSL'));
        }


        global $payment, $shipping, $order, $customer_id, $billto, $sendto, $languages_id, $currencies;
        global $insert_id, $order_totals, $order_total_modules;

        $payment_modules = new \common\classes\payment($payment);

        if ( defined('ONE_PAGE_POST_PAYMENT') && preg_match("/".preg_quote('account/order-confirmation',"/")."/",$_SERVER['HTTP_REFERER'])){
            if (is_array($payment_modules->modules)) {
                $payment_modules->pre_confirmation_check();
            }
        }

        //$shipping_modules = new \common\classes\shipping($shipping);

        $order = new order($order_id);

        $order_total_modules = new \common\classes\order_total();
        
        $order_totals = $order->totals;//$order_total_modules->process();
        $paid_key = -1;
        if (is_array($order->totals)) {
            foreach ($order->totals as $key => $total) {
                $order_totals[$key]['sort_order'] = $GLOBALS[$total['class']]->sort_order;
                if ($total['class'] == 'ot_paid'){
                    $paid_key = $key;
                }
                if ($total['class'] == 'ot_due') {
                    $order->info['total'] = $total['value_inc_tax'];
                    
                    if ($paid_key != -1){
                        $order->info['total_inc_tax'] = $order_totals[$paid_key]['value_inc_tax'] + $total['value_inc_tax'];
                        $order->info['total_exc_tax'] = $order_totals[$paid_key]['value_exc_vat'] + $total['value_exc_vat'];
                    }
                    break;
                }
                
            }
        }
        
        tep_db_query("update " . TABLE_ORDERS . " set payment_class='" . tep_db_input($GLOBALS[$payment]->code) . "', payment_method = '" . tep_db_input($GLOBALS[$payment]->title)  . "' where orders_id = '" . (int)$order_id . "'");
        
        $payment_modules->before_process();
        
        if (!in_array($payment, ['paypalipn'])){
            $order->update_piad_information();
        }
        
        //$insert_id = $order->save_order($order_id);
        $insert_id = $order_id;

        $order->save_details();

        //$order->save_products();

        //$order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
        
        //\common\helpers\System::ga_detection($insert_id);
        
        $payment_modules->after_process();
        

        //tep_session_unregister('sendto');
        //tep_session_unregister('billto');
        //tep_session_unregister('shipping');
        tep_session_unregister('payment');
        tep_session_unregister('pay_order_id');
        //tep_session_unregister('comments');
        //if(tep_session_is_registered('credit_covers')) tep_session_unregister('credit_covers');
        $order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id='. $insert_id, 'SSL'));
        
    }
    
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }
    
    public function actionAuth(){
        
    }

    public function onAuthSuccess($client)
    {
        \common\helpers\Translation::init('account/login');
        (new Socials($client))->handle();
    }
}
