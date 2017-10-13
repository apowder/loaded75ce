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
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use frontend\design\Info;
use yii\web\Session;
use common\classes\opc;
/**
 * Site controller
 */
class IndexController extends Sceleton
{
    /**
     * @inheritdoc
     */
    /*public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }*/

    /**
     * @inheritdoc
     */
    /*public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }*/

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        global $currency, $currency_id, $HTTP_SESSION_VARS;
        \common\helpers\Translation::init('account/login');
        \common\helpers\Translation::init('account/password-forgotten');
        global $cart, $navigation, $affiliate_ref, $messageStack;
        global $wish_list;
        // customer login details
        global $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $customer_groups_id;

        global $breadcrumb;
        $messages_login = '';
      
        if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
            
            $error = false;
            
            $account_login = tep_db_prepare_input(Yii::$app->request->post('account_login','login'));
            if ( $account_login == 'contact' ) {
                $name = STORE_OWNER;
                $email_address = STORE_OWNER_EMAIL_ADDRESS;
                $enquiry = tep_db_prepare_input(Yii::$app->request->post('content'));

                $email_params = array();
                $email_params['USER_NAME'] = tep_db_prepare_input(Yii::$app->request->post('name'));
                $email_params['COMPANY_NAME'] = tep_db_prepare_input(Yii::$app->request->post('company_name'));
                $email_params['USER_EMAIL'] = tep_db_prepare_input(Yii::$app->request->post('email'));
                $email_params['USER_PHONE'] = tep_db_prepare_input(Yii::$app->request->post('phone'));
                $email_params['ENQUIRY'] = $enquiry;
                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Enquiries', $email_params);

                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, $name, $email_address);
            } elseif ( $account_login == 'create_account' ) {
                $firstname = tep_db_prepare_input(Yii::$app->request->post('firstname'));
                $lastname = tep_db_prepare_input(Yii::$app->request->post('lastname'));
                $email_address = tep_db_prepare_input(Yii::$app->request->post('email_address'));
                $password = tep_db_prepare_input(Yii::$app->request->post('password'));
                $confirmation = tep_db_prepare_input(Yii::$app->request->post('confirmation'));

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
                if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                  $error = true;
                  $messageStack->add('login', sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                }
                if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                  $error = true;
                  $messageStack->add('login', sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                }
                if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
                  $error = true;
                  $messageStack->add('login', sprintf(ENTRY_EMAIL_ADDRESS_ERROR, ENTRY_EMAIL_ADDRESS_MIN_LENGTH));
                } elseif (\common\helpers\Validations::validate_email($email_address) == false) {
                  $error = true;
                  $messageStack->add('login', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
                } else {
                  $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and affiliate_id = '" . (int)$affiliate_ref. "' and opc_temp_account=0");
                  $check_email = tep_db_fetch_array($check_email_query);
                  if ($check_email['total'] > 0) {
                    $error = true;
                    $messageStack->add('login', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                  }
                }
                if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('login', sprintf(ENTRY_PASSWORD_ERROR, ENTRY_PASSWORD_MIN_LENGTH));
                } elseif ($password != $confirmation) {
                    $error = true;

                    $messageStack->add('login', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
                }
                
                if ($error == false) {
                    $login = true;
                    if ($group != 0 && \common\helpers\Customer::check_customer_groups($group, 'new_approve')){
                      $login = false;
                    }
                    $sql_data_array = array(
                      'customers_firstname' => $firstname,
                      'customers_lastname' => $lastname,
                      'customers_email_address' => $email_address,
                      //'customers_telephone' => isset($telephone)?$telephone:'',
                      //'customers_fax' => isset($fax)?$fax:'',
                      //'customers_newsletter' => $newsletter,
                      'platform_id' => \common\classes\platform::currentId(),
                      'affiliate_id' => (int)$affiliate_ref,
                      'groups_id' => $group,
                      'customers_status' => ($login?1:0),
                      'customers_password' => \common\helpers\Password::encrypt_password($password),
                    );

                    //if ((ACCOUNT_GENDER == 'required_register' || ACCOUNT_GENDER == 'visible_register') && isset($gender)) $sql_data_array['customers_gender'] = $gender;
                    //if (ACCOUNT_DOB == 'required_register' || ACCOUNT_DOB == 'visible_register') $sql_data_array['customers_dob'] = \common\helpers\Date::date_raw($dob);

                    tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                    $customer_id = tep_db_insert_id();

                    $customer_country_id = isset($country)?$country:STORE_COUNTRY;
                    $customer_zone_id = STORE_ZONE;

                    $sql_data_array = array(
                      'customers_id' => $customer_id,
                      'entry_firstname' => $firstname,
                      'entry_lastname' => $lastname,
                      //'entry_street_address' => isset($street_address_line1)?$street_address_line1:'',
                      //'entry_postcode' => isset($postcode)?$postcode:'',
                      //'entry_city' => isset($city)?$city:'',
                      'entry_zone_id' => $customer_zone_id,
                      'entry_country_id' => $customer_country_id,
                    );

                    //if ((ACCOUNT_GENDER == 'required_register' || ACCOUNT_GENDER == 'visible_register') && isset($gender)) $sql_data_array['entry_gender'] = $gender;
                    //if (ACCOUNT_COMPANY == 'true'&& isset($company)) $sql_data_array['entry_company'] = $company;
                    //if (ACCOUNT_SUBURB == 'true' && isset($street_address_line2)) $sql_data_array['entry_suburb'] = $street_address_line2;
                    //if (ACCOUNT_COMPANY_VAT_ID == 'true' && isset($entry_company_vat)) $sql_data_array['entry_company_vat'] = $entry_company_vat;
                    /*if ((ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') && (isset($zone_id) || isset($state)) ) {
                      if ($zone_id > 0) {
                        $sql_data_array['entry_zone_id'] = $zone_id;
                        $sql_data_array['entry_state'] = '';
                      } else {
                        $sql_data_array['entry_zone_id'] = '0';
                        $sql_data_array['entry_state'] = isset($state)?$state:'';
                      }
                    }*/
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                    $address_id = tep_db_insert_id();

                    tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");

                    tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

                    // login

                    if (SESSION_RECREATE == 'True') {
                      tep_session_recreate();
                    }

                    $customer_first_name = $firstname;
                    $customer_default_address_id = $address_id;
                    if (!defined('DEFAULT_USER_LOGIN_GROUP')) define(DEFAULT_USER_LOGIN_GROUP, 0);
                    $customer_groups_id = DEFAULT_USER_LOGIN_GROUP;

                    if ($login){
                      tep_session_register('customer_id');
                      tep_session_register('customer_first_name');
                      tep_session_register('customer_default_address_id');
                      tep_session_register('customer_country_id');
                      tep_session_register('customer_zone_id');
                      tep_session_register('customer_groups_id');

                      // restore cart contents
                      $cart->restore_contents();
                      if ( is_object($wish_list) && method_exists($wish_list, 'restore_contents') ) {
                        $wish_list->restore_contents();
                      }
                    }

                    // build the message content
                    $name = $firstname . ' ' . $lastname;

                    if (in_array(ACCOUNT_GENDER, ['required_register', 'visible_register']) && isset($gender) ) {
                      if ($gender == 'm') {
                        $user_greeting = sprintf(EMAIL_GREET_MR, $lastname);
                      } elseif ($gender == 'f' || $gender == 's') {
                        $user_greeting = sprintf(EMAIL_GREET_MS, $lastname);
                      } else {
                        $user_greeting = sprintf(EMAIL_GREET_NONE, $firstname);
                      }
                    } else {
                      $user_greeting = sprintf(EMAIL_GREET_NONE, $firstname);
                    }

                    // {{
                    $email_params = array();
                    $email_params['STORE_NAME'] = STORE_NAME;
                    $email_params['USER_GREETING'] = trim($user_greeting);
                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Confirmation', $email_params);
                    // }}
                    \common\helpers\Mail::send($name, $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

                    if (!$login){
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
                        //tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
                        tep_redirect(tep_href_link('/'));
            //          }
                    }
                  }
                
                /*$sql_data_array = array(
                    'fullname' => $fullname,
                    'company' => $company,
                    'password' => \common\helpers\Password::encrypt_password($password),
                    'email' => $email_address,
                    'date_added' => 'now()',
                );
                tep_db_perform(TABLE_ACCESS_REQUEST, $sql_data_array);
                $messageStack->add('login', SUCCESS_ACCESS_REQUEST, 'success');*/
            } elseif ($account_login == 'password_forgotten') {
                
                $email_address = tep_db_prepare_input($_POST['email_address']);

                if ( empty($email_address) ) {
                  $check_customer_query = tep_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE 1=0");
                }else {
                  $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' ORDER BY opc_temp_account ASC");
                }
                if (tep_db_num_rows($check_customer_query)) {
                  $check_customer = tep_db_fetch_array($check_customer_query);
                  if ( opc::is_temp_customer($check_customer['customers_id']) ){
                    $messageStack->add('login', TEXT_NO_EMAIL_ADDRESS_FOUND);
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
                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Password Forgotten', $email_params);
                    // }}
                    \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

                    $messageStack->add('login', SUCCESS_PASSWORD_SENT, 'success');

                    //tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                  }
                } else {
                  $messageStack->add('login', TEXT_NO_EMAIL_ADDRESS_FOUND);
                }
            } else {
              $email_address = tep_db_prepare_input($_POST['email_address']);
              $password = tep_db_prepare_input($_POST['password']);

              // {{
              if ($_POST['type'] == 'new_customer') {
                $_SESSION['tmp_email_address'] = $email_address;
                tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
              }
              // }}

              // Check if email exists
              $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, groups_id, customers_currency_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_status = 1 ORDER BY opc_temp_account ASC ");
              if (!tep_db_num_rows($check_customer_query)) {
                $error = true;
              } else {
                $check_customer = tep_db_fetch_array($check_customer_query);
                if (opc::is_temp_customer($check_customer['customers_id'])) {
                  $error = true;
                  $check_customer['customers_password'] = ' ';
                  opc::remove_temp_customer($check_customer['customers_id']);
                };
                // Check that password is good
                if (!\common\helpers\Password::validate_password($password, $check_customer['customers_password'])) {
                  $error = true;
                } else {
                  if (SESSION_RECREATE == 'True') {
                    tep_session_recreate();
                  }

                  $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
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

                  if ($check_customer['customers_currency_id'] != 'null') {
                    if (!tep_session_is_registered('currency')) tep_session_register('currency');
                    if (!tep_session_is_registered('currency_id')) tep_session_register('currency_id');
                    $check_currency_query = tep_db_query("select currencies_id, code from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$check_customer['customers_currency_id'] . "' and status = 1");
                    $check_currency = tep_db_fetch_array($check_currency_query);
                    if (is_array($check_currency)) {
                        $HTTP_SESSION_VARS['currency_id'] = $_SESSION['currency_id'] = $currency_id= $check_currency['currencies_id'];
                        $HTTP_SESSION_VARS['currency'] = $_SESSION['currency'] = $currency = $check_currency['code'];
                    }
                  }

                  tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");

                  // restore cart contents
                  $cart->restore_contents();
                  if ( is_object($wish_list) && method_exists($wish_list, 'restore_contents') ) {
                    $wish_list->restore_contents();
                  }

                  if (sizeof($navigation->snapshot) > 0) {
                    $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                    $navigation->clear_snapshot();
                    tep_redirect($origin_href);
                  } else {
                    //tep_redirect(tep_href_link(FILENAME_ACCOUNT));
                    tep_redirect(tep_href_link('/'));
                  }
                }
              }
            }
            if ($error == true) {
                $messageStack->add('login', TEXT_LOGIN_ERROR);
                //$messageStack->add('login', sprintf(TEXT_PASSWORD_FORGOTTEN_S, tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL')));
            }
            
            
            if ($messageStack->size('login')>0){
              $messages_login = $messageStack->output('login');
            }
            
        }
        
        $this->view->messages_login = $messages_login;
        
        if (Yii::$app->request->isAjax && !Info::isAdmin()) {
            $this->layout = 'ajax.tpl';
        } else {
            if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'check')) {
                $ext::check();
            }
        }
        if ($_GET['page_name']) {
            $page_name = $_GET['page_name'];
        } else {
            $page_name = 'main';
        }

        return $this->render('index.tpl', ['page_name' => $page_name]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    /* public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    } */

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $statusCode = $exception->statusCode;
            $name = $exception->getName();
            $message = $exception->getMessage();
            if ($statusCode == 404) {
                header('HTTP/1.0 404 Not Found');
                return $this->render('404');
                return;
            }
        }
    }

    public function actionRobotsTxt(){
      $this->layout = false;
      if (is_file(DIR_FS_CATALOG.'/.robots.txt')) {

        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->set('Content-Type','text/plain');

        $robots = file_get_contents(DIR_FS_CATALOG.'/.robots.txt');
        $robots = str_replace('#SITEMAP#',"Sitemap: ".Url::toRoute('xmlsitemap/index',true), $robots);
        echo $robots;
      }else{
        throw new \yii\web\NotFoundHttpException();
      }
    }
    
    public function actionLoadLanguagesJs(){
	  //header('X-Content-Type-Options: nosniff');
      $list = \common\helpers\Translation::loadJS('js');
      
      return \common\widgets\JSLanguage::widget(['list' => $list]);
    }


    public function actionDesign()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('design.tpl');
    }
    public function actionBody()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('body.tpl');
    }
    public function actionMenuHorizontal()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-horizontal.tpl');
    }
    public function actionMenuSlideMenu()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-slide-menu.tpl');
    }
    public function actionMenuBigDropdow()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-big-dropdow.tpl');
    }
    public function actionMenuVertical()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-vertical.tpl');
    }
    public function actionMenuHorizontal2()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-horizontal2.tpl');
    }
    public function actionMenuVertical2()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('menu-vertical2.tpl');
    }
    public function actionTabs()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('tabs.tpl');
    }
    public function actionButtons()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('buttons.tpl');
    }
    public function actionFormElements()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('form-elements.tpl');
    }
    public function actionTypography()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('typography.tpl');
    }
    public function actionShoppingCart()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('shopping-cart.tpl');
    }
    public function actionListing_1()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('listing_1.tpl');
    }
    public function actionListing_2()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('listing_2.tpl');
    }
    public function actionListing_1_2()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('listing_1_2.tpl');
    }
    public function actionListing_2_2()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('listing_2_2.tpl');
    }
    public function actionListing_1_3()
    {
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
        return $this->render('listing_1_3.tpl');
    }
    
}
