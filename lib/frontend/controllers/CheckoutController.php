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
use common\classes\opc_order;
use common\classes\opc;
use common\classes\payment;
use common\models\Customer;
use common\models\Socials;

/**
 * Site controller
 */
class CheckoutController extends Sceleton {

    public function actionIndex() {

        $session = new Session();

        // customer login details
        global $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $customer_groups_id;

        global $wish_list, $breadcrumb;
        global $session_started, $cart, $order, $language, $navigation, $messageStack, $affiliate_ref, $currencies;
        global $total_weight, $total_count, $shipping, $comments;

        global $billto, $sendto;

        global $opc_sendto, $opc_billto;
        $opc_sendto = false;
        $opc_billto = false;

        global $cot_gv, $credit_covers;

        if (GROUPS_DISABLE_CHECKOUT) {
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
        if ($session_started == false) {
            tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
        }
        if ($cart->count_contents() < 1) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        //$breadcrumb->add(NAVBAR_TITLE);
        //tep_session_unregister("cot_gv");
        tep_session_unregister("credit_covers");
        //tep_session_unregister("cc_id");

        $create_temp_account = (isset($_SESSION['guest_email_address']) && !empty($_SESSION['guest_email_address']));

        if (!($create_temp_account || tep_session_is_registered('customer_id'))) {
            tep_redirect(tep_href_link('checkout/login', '', 'SSL'));
        }

        $error = false;
        $errorName = array();

        if (isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) {
            $order = new order();

            if (isset($_POST['xwidth']) && isset($_POST['xheight'])) {
                $_SESSION['resolution'] = (int)$_POST['xwidth'] . 'x' . (int)$_POST['xheight'];
            }

            $error = false;

            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $gender = tep_db_prepare_input(Yii::$app->request->post('gender'));
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register'])) {
                    if (($gender != 'm') && ($gender != 'f') && ($gender != 's')) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_GENDER_ERROR);
                        $errorName[] = 'gender';
                    }
                }
            }

            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $firstname = tep_db_prepare_input(Yii::$app->request->post('firstname'));
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                        $errorName[] = 'firstname';
                    }
                }
            }

            if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $lastname = tep_db_prepare_input(Yii::$app->request->post('lastname'));
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])) {
                    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                        $errorName[] = 'lastname';
                    }
                }
            }

            if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $company = tep_db_prepare_input(Yii::$app->request->post('customer_company'));
                if (in_array(ACCOUNT_COMPANY, ['required', 'required_register']) && empty($company)) {
                    $error = true;
                    $messageStack->add('one_page_checkout', ENTRY_COMPANY_ERROR);
                    $errorName[] = 'customer_company';
                }
            }

            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])) {
                $company_vat = tep_db_prepare_input(Yii::$app->request->post('customer_company_vat'));
                if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register']) && (empty($company_vat) || !\common\helpers\Validations::checkVAT($company_vat))) {
                    $error = true;
                    $messageStack->add('one_page_checkout', ENTRY_VAT_ID_ERROR);
                    $errorName[] = 'customer_company_vat';
                }
            }

            if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $postcode = tep_db_prepare_input(Yii::$app->request->post('postcode'));
                if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register']) && strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH));
                    $errorName[] = 'postcode';
                }
            }

            if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                $street_address = tep_db_prepare_input(Yii::$app->request->post('street_address_line1'));
                if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register']) && strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                    $errorName[] = 'street_address_line1';
                }
            }

            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $suburb = tep_db_prepare_input(Yii::$app->request->post('street_address_line2'));
                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register']) && empty($suburb)) {
                    $error = true;
                    $messageStack->add('one_page_checkout', ENTRY_SUBURB_ERROR);
                    $errorName[] = 'street_address_line2';
                }
            }

            if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $city = tep_db_prepare_input(Yii::$app->request->post('city'));
                if (in_array(ACCOUNT_CITY, ['required', 'required_register']) && strlen($city) < ENTRY_CITY_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_CITY_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                    $errorName[] = 'city';
                }
            }

            if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $country = tep_db_prepare_input(Yii::$app->request->post('country'));
                if (is_numeric($country) == false) {
                    if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register'])) {
                        $error = true;
                        $messageStack->add('country', ENTRY_COUNTRY_ERROR);
                        $errorName[] = 'country';
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
                $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country . "'");
                $check = tep_db_fetch_array($check_query);
                $entry_state_has_zones = ($check['total'] > 0);
                if ($entry_state_has_zones == true) {
                    $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country . "' and zone_name = '" . tep_db_input($state) . "'");
                    if (tep_db_num_rows($zone_query) == 1) {
                        $zone = tep_db_fetch_array($zone_query);
                        $zone_id = $zone['zone_id'];
                    } elseif (in_array(ACCOUNT_STATE, ['required', 'required_register'])) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_STATE_ERROR_SELECT);
                        $errorName[] = 'state';
                    }
                } else {
                    if (strlen($state) < ENTRY_STATE_MIN_LENGTH && in_array(ACCOUNT_STATE, ['required', 'required_register'])) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                        $errorName[] = 'state';
                    }
                }
            }

            if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $telephone = tep_db_prepare_input(Yii::$app->request->post('telephone'));
                if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register']) && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_TELEPHONE_NUMBER_ERROR, ENTRY_TELEPHONE_MIN_LENGTH));
                    $errorName[] = 'telephone';
                }
            }

            if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $landline = tep_db_prepare_input(Yii::$app->request->post('landline'));
                if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register']) && strlen($landline) < ENTRY_LANDLINE_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_LANDLINE_NUMBER_ERROR, ENTRY_LANDLINE_MIN_LENGTH));
                    $errorName[] = 'landline';
                }
            }

            $billto = tep_db_prepare_input($_POST['billto']);
            if (!tep_session_is_registered('customer_id') && defined('ONE_PAGE_CREATE_ACCOUNT') && (ONE_PAGE_CREATE_ACCOUNT == 'pass' || (ONE_PAGE_CREATE_ACCOUNT == 'onebuy' || $create_temp_account))) {
                $new_password = tep_db_prepare_input($_POST['password_new']);
                $confirmation_new = tep_db_prepare_input($_POST['confirmation_new']); // DRF
                $check_for_error = ( ONE_PAGE_CREATE_ACCOUNT == 'pass' ) || ( (ONE_PAGE_CREATE_ACCOUNT == 'onebuy' || $create_temp_account) && isset($_POST['create_account']) && $_POST['create_account'] == 1 );

                if ($check_for_error && ($new_password != $confirmation_new)) {
                    $error = true;
                    $messageStack->add('one_page_checkout', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
                    $errorName[] = 'password_error_not_matching';
                }

                if ($check_for_error && strlen($new_password) < ENTRY_PASSWORD_MIN_LENGTH) {
                    $error = true;
                    $messageStack->add('one_page_checkout', sprintf(ENTRY_PASSWORD_ERROR, ENTRY_PASSWORD_MIN_LENGTH));  // DRF
                    $errorName[] = 'password_error';
                }
            }

            if (!tep_session_is_registered('customer_id') && defined('ONE_PAGE_CREATE_ACCOUNT') && ONE_PAGE_CREATE_ACCOUNT != 'false' && (ACCOUNT_DOB == 'required' || ACCOUNT_DOB == 'required_register' || ACCOUNT_DOB == 'visible' || ACCOUNT_DOB == 'visible_register')) {
                $dob = tep_db_prepare_input($_POST['dob']);
                if (!empty($dob)) {
                    $dob = \common\helpers\Date::date_raw($dob);
                    if (!checkdate(date('m', strftime($dob)), date('d', strftime($dob)), date('Y', strftime($dob)))) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_DATE_OF_BIRTH_ERROR);
                        $errorName[] = 'dob';
                    }
                } else {
                    $dob = '0000-00-00';
                }
            }

            $email_address = tep_db_prepare_input(Yii::$app->request->post('email_address'));
            if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
                $error = true;
                $messageStack->add('one_page_checkout', sprintf(ENTRY_EMAIL_ADDRESS_ERROR, ENTRY_EMAIL_ADDRESS_MIN_LENGTH));
                $errorName[] = 'email_address';
            } elseif (\common\helpers\Validations::validate_email($email_address) == false) {
                $error = true;
                $messageStack->add('one_page_checkout', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
                $errorName[] = 'email_address';
            } else {
                if (!tep_session_is_registered('customer_id')) {
                    $check_email_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
                    $total_c = 0;
                    while ($check_email = tep_db_fetch_array($check_email_query)) {
                        if (opc::is_temp_customer($check_email['customers_id'])) {
                            opc::remove_temp_customer($check_email['customers_id']);
                        } else {
                            $total_c++;
                        }
                    }
                    if ($total_c > 0 && !$create_temp_account) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                        $errorName[] = 'email_address';
                    }
                } else {
                    $check_email_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_id != '" . (int) $customer_id . "'");
                    $total_c = 0;
                    while ($check_email = tep_db_fetch_array($check_email_query)) {
                        if (opc::is_temp_customer($check_email['customers_id'])) {
                            opc::remove_temp_customer($check_email['customers_id']);
                        } else {
                            $total_c++;
                        }
                    }
                    if ($total_c > 0 && !$create_temp_account) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                        $errorName[] = 'email_address';
                    }
                }
            }

            if (is_numeric($country) == false) {
                $error = true;
                $messageStack->add('one_page_checkout', ENTRY_COUNTRY_ERROR);
                $errorName[] = 'country';
            }

            $sendto = tep_db_prepare_input($_POST['sendto'] ? $_POST['sendto'] : $_SESSION['sendto']);
            if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {
                if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'prepareDeliveryDate')){
                    $response = $ext::prepareDeliveryDate();
                    if ($response){
                        $error = true;
                    }
                }
                
                $sendto = tep_db_prepare_input($_POST['sendto']);

                if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $shipping_gender = tep_db_prepare_input(Yii::$app->request->post('shipping_gender'));
                    if (in_array(ACCOUNT_GENDER, ['required', 'required_register'])) {
                        if (($shipping_gender != 'm') && ($shipping_gender != 'f') && ($shipping_gender != 's')) {
                            $error = true;
                            $messageStack->add('one_page_checkout', ENTRY_GENDER_ERROR);
                            $errorName[] = 'shipping_gender';
                        }
                    }
                }

                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_firstname = tep_db_prepare_input(Yii::$app->request->post('ship_firstname'));
                    if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                        if (strlen($ship_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                            $error = true;
                            $messageStack->add('one_page_checkout', sprintf(SHIP_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                            $errorName[] = 'ship_firstname';
                        }
                    }
                }

                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_lastname = tep_db_prepare_input(Yii::$app->request->post('ship_lastname'));
                    if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])) {
                        if (strlen($ship_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                            $error = true;
                            $messageStack->add('one_page_checkout', sprintf(SHIP_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                            $errorName[] = 'required_register';
                        }
                    }
                }

                if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_postcode = tep_db_prepare_input(Yii::$app->request->post('ship_postcode'));
                    if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register']) && strlen($ship_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(SHIP_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH));
                        $errorName[] = 'ship_postcode';
                    }
                }

                if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_street_address = tep_db_prepare_input(Yii::$app->request->post('ship_street_address_line1'));
                    if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register']) && strlen($ship_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(SHIP_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                        $errorName[] = 'ship_street_address_line1';
                    }
                }

                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_suburb = tep_db_prepare_input(Yii::$app->request->post('ship_street_address_line2'));
                    if (in_array(ACCOUNT_SUBURB, ['required', 'required_register']) && empty($ship_suburb)) {
                        $error = true;
                        $messageStack->add('one_page_checkout', ENTRY_SUBURB_ERROR);
                        $errorName[] = 'ship_street_address_line2';
                    }
                }

                if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_city = tep_db_prepare_input(Yii::$app->request->post('ship_city'));
                    if (in_array(ACCOUNT_CITY, ['required', 'required_register']) && strlen($ship_city) < ENTRY_CITY_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('one_page_checkout', sprintf(SHIP_CITY_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                        $errorName[] = 'ship_city';
                    }
                }

                if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_country = tep_db_prepare_input(Yii::$app->request->post('ship_country'));
                    if (is_numeric($ship_country) == false) {
                        if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register'])) {
                            $error = true;
                            $messageStack->add('one_page_checkout', SHIP_COUNTRY_ERROR);
                            $errorName[] = 'ship_country';
                        } else {
                            $ship_country = (int) STORE_COUNTRY;
                            $zone_id = (int) STORE_ZONE;
                        }
                    }
                } else {
                    $ship_country = (int) STORE_COUNTRY;
                    $zone_id = (int) STORE_ZONE;
                }

                if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $ship_state = tep_db_prepare_input(Yii::$app->request->post('ship_state'));
                    $ship_zone_id = 0;
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $ship_country . "'");
                    $check = tep_db_fetch_array($check_query);
                    $ship_state_has_zones = ($check['total'] > 0);
                    if ($ship_state_has_zones == true) {
                        $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $ship_country . "' and zone_name = '" . tep_db_input($ship_state) . "'");
                        if (tep_db_num_rows($zone_query) == 1) {
                            $zone = tep_db_fetch_array($zone_query);
                            $ship_zone_id = $zone['zone_id'];
                        } elseif (ACCOUNT_STATE == 'required') {
                            $error = true;
                            $messageStack->add('one_page_checkout', SHIP_STATE_ERROR_SELECT);
                            $errorName[] = 'ship_state';
                        }
                    } else {
                        if (strlen($ship_state) < ENTRY_STATE_MIN_LENGTH && in_array(ACCOUNT_STATE, ['required', 'required_register'])) {
                            $error = true;
                            $messageStack->add('one_page_checkout', sprintf(SHIP_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                            $errorName[] = 'ship_state';
                        }
                    }
                }
                if (is_numeric($ship_country) == false) {
                    $error = true;
                    $messageStack->add('one_page_checkout', SHIP_COUNTRY_ERROR);
                    $errorName[] = 'ship_country';
                }
            }
        }               

        if (($error == false) && isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) {
            if (tep_session_is_registered('guest_email_address'))
                tep_session_unregister('guest_email_address');

            if (!tep_session_is_registered('customer_id')) { // New Customer
                $opc_temp_account = 0;
                if ($create_temp_account || defined('ONE_PAGE_CREATE_ACCOUNT')) {
                    if (ONE_PAGE_CREATE_ACCOUNT == 'false' ||
                            ( (ONE_PAGE_CREATE_ACCOUNT == 'onebuy' || $create_temp_account) && strlen($new_password) == 0 )
                    ) {
                        $new_password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                    }
                    if ((ONE_PAGE_CREATE_ACCOUNT == 'onebuy' || $create_temp_account) && !isset($_POST['create_account']))
                        $opc_temp_account = 1;
                }else {
                    $new_password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                }

                $sql_data_array = array(
                    'customers_email_address' => $email_address,
                    'affiliate_id' => $affiliate_ref,
                    'platform_id' => \common\classes\platform::currentId(),
                    'groups_id' => (int) DEFAULT_USER_LOGIN_GROUP,
                    'opc_temp_account' => $opc_temp_account,
                    'customers_password' => \common\helpers\Password::encrypt_password($new_password)
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
                if (isset($suburb)) {
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
                $billto = $address_id;

                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");

                tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $customer_id . "', '0', now())");

                if (SESSION_RECREATE == 'True') {
                    tep_session_recreate();
                }

                $customer_first_name = $firstname;
                $customer_default_address_id = $address_id;
                $customer_country_id = $country;
                $customer_zone_id = $zone_id;
                if (CUSTOMERS_GROUPS_ENABLE == 'True') {
                    $customer_groups_id = DEFAULT_USER_LOGIN_GROUP;
                } else {
                    $customer_groups_id = 0;
                }

                tep_session_register('customer_id');
                tep_session_register('customer_first_name');
                tep_session_register('customer_default_address_id');
                tep_session_register('customer_country_id');
                tep_session_register('customer_zone_id');
                tep_session_register('customer_groups_id');

                // restore cart contents
                $cart->restore_contents();
                if (is_object($wish_list) && method_exists($wish_list, 'restore_contents')) {
                    $wish_list->restore_contents();
                }
                
                if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_track_after_customer_create')){
                    $ext::rf_track_after_customer_create($customer_id);
                }

                if ($opc_temp_account != 1) {
                    // build the message content
//                    // {{
//                    $email_params = array();
//                    $email_params['STORE_NAME'] = STORE_NAME;
//                    $email_params['USER_GREETING'] = sprintf(EMAIL_GREET_NONE, $firstname . ' ' . $lastname);
//                    $email_params['PASSWORD'] = $new_password;
//                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
//                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Confirmation', $email_params);
//                    // }}
//                    \common\helpers\Mail::send($firstname . ' ' . $lastname, $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
//                    
                    $email_text = sprintf(EMAIL_GREET_NONE, $firstname . ' ' . $lastname) . EMAIL_TEXT0 . sprintf(EMAIL_LOGIN, $email_address) . sprintf(EMAIL_PASSWORD, $new_password) . EMAIL_TEXT1 . EMAIL_WARNING;
                    \common\helpers\Mail::send($firstname . ' ' . $lastname, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                }
            } else { // Existing Customer
                $sql_data_array = array(
                    'customers_email_address' => $email_address,
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

                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "'");

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
                if (isset($suburb)) {
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

                if ($billto) {
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "' and address_book_id = '" . (int) $billto . "'");
                } else {
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                    $billto = tep_db_insert_id();
                }

// reset the session variables
                $customer_first_name = $firstname;
                $customer_country_id = $country;
                $customer_zone_id = $zone_id;
            }

            if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {
                // Shipping is not same as billing address
                if ((string) $gender != (string) $shipping_gender || (string) $ship_firstname != (string) $firstname || (string) $ship_lastname != (string) $lastname ||
                        (string) $ship_street_address != (string) $street_address || (string) $ship_suburb != (string) $suburb || (string) $ship_postcode != (string) $postcode ||
                        (string) $ship_city != (string) $city ||
                        (string) $ship_state != (string) $state || (string) $ship_zone_id != (string) $zone_id || (string) $ship_country != (string) $country) {
                    $sql_data_array = array(
                        'customers_id' => $customer_id,
                        'entry_country_id' => (isset($ship_country) ? $ship_country : STORE_COUNTRY),
                    );

                    if (isset($shipping_gender)) {
                        $sql_data_array['entry_gender'] = $shipping_gender;
                    }
                    if (isset($ship_firstname)) {
                        $sql_data_array['entry_firstname'] = $ship_firstname;
                    }
                    if (isset($ship_lastname)) {
                        $sql_data_array['entry_lastname'] = $ship_lastname;
                    }
                    if (isset($ship_postcode)) {
                        $sql_data_array['entry_postcode'] = $ship_postcode;
                    }
                    if (isset($ship_street_address)) {
                        $sql_data_array['entry_street_address'] = $ship_street_address;
                    }
                    if (isset($ship_suburb)) {
                        $sql_data_array['entry_suburb'] = $ship_suburb;
                    }
                    if (isset($ship_city)) {
                        $sql_data_array['entry_city'] = $ship_city;
                    }
                    if ($ship_zone_id > 0) {
                        $sql_data_array['entry_zone_id'] = $ship_zone_id;
                        $sql_data_array['entry_state'] = '';
                    } else {
                        $sql_data_array['entry_zone_id'] = '0';
                        $sql_data_array['entry_state'] = isset($ship_state) ? $ship_state : '';
                    }

                    if ($sendto && $sendto != $billto) {
                        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int) $customer_id . "' and address_book_id = '" . (int) $sendto . "'");
                    } else {
                        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                        $sendto = tep_db_insert_id();
                    }
                } else {
                    $sendto = $billto;
                }
            } else {
                // virtual
                $sendto = $billto;
            }

            if (!tep_session_is_registered('billto'))
                tep_session_register('billto');
            if (!tep_session_is_registered('sendto'))
                tep_session_register('sendto');

            // Clear stored POST params in Session
            foreach ($_SESSION as $key => $val) {
                if (substr($key, 0, 18) == 'one_page_checkout_') {
                    //global $$key;
                    tep_session_unregister($key);
                    //unset($_SESSION[$key]);
                }
            }
            $order = new opc_order();
        }// if (($error == false) && isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout'))
        elseif ($error == true && isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) {
            // populate posted data into order
            $__country_id = isset($country) ? $country : STORE_COUNTRY;
            $_country_info = \common\helpers\Country::get_countries($__country_id, true);
            $opc_billto = array(
                //'address_book_id' => ,
                'gender' => isset($gender) ? $gender : null,
                'firstname' => isset($firstname) ? $firstname : '',
                'lastname' => isset($lastname) ? $lastname : '',
                'company' => isset($company) ? $company : '',
                'company_vat' => isset($company_vat) ? $company_vat : '',
                'street_address' => isset($street_address) ? $street_address : '',
                'suburb' => isset($suburb) ? $suburb : '',
                'city' => isset($city) ? $city : '',
                'postcode' => isset($postcode) ? $postcode : '',
                'state' => isset($state) ? $state : '',
                'zone_id' => isset($zone_id) ? $zone_id : 0,
                'country' => array(
                    'id' => $__country_id,
                    'title' => $_country_info['countries_name'],
                    'iso_code_2' => $_country_info['countries_iso_code_2'],
                    'iso_code_3' => $_country_info['countries_iso_code_3'],
                ),
                'country_id' => $__country_id,
                'format_id' => \common\helpers\Address::get_address_format_id($__country_id),
            );
            if (isset($_POST['billto'])) {
                $opc_billto['address_book_id'] = $billto;
            }

            $__ship_country_id = isset($ship_country) ? $ship_country : STORE_COUNTRY;
            $_country_info = \common\helpers\Country::get_countries($__ship_country_id, true);
            $opc_sendto = array(
                //'address_book_id' => ,
                'gender' => isset($shipping_gender) ? $shipping_gender : null,
                'firstname' => isset($ship_firstname) ? $ship_firstname : '',
                'lastname' => isset($ship_lastname) ? $ship_lastname : '',
                'company' => isset($company) ? $company : '',
                'company_vat' => isset($company_vat) ? $company_vat : '',
                'street_address' => isset($ship_street_address) ? $ship_street_address : '',
                'suburb' => isset($ship_suburb) ? $ship_suburb : '',
                'city' => isset($ship_city) ? $ship_city : '',
                'postcode' => isset($ship_postcode) ? $ship_postcode : '',
                'state' => isset($ship_state) ? $ship_state : '',
                'zone_id' => isset($ship_zone_id) ? $ship_zone_id : 0,
                'country' => array(
                    'id' => $__ship_country_id,
                    'title' => $_country_info['countries_name'],
                    'iso_code_2' => $_country_info['countries_iso_code_2'],
                    'iso_code_3' => $_country_info['countries_iso_code_3'],
                ),
                'country_id' => $__ship_country_id,
                'format_id' => \common\helpers\Address::get_address_format_id($__ship_country_id),
            );
            if (isset($_POST['sendto'])) {
                $opc_sendto['address_book_id'] = $sendto;
            }

            $order = new opc_order();
            $order->customer['email_address'] = $email_address;
        } else {
            if ((isset($billto) && is_array($billto)) || (isset($sendto) && is_array($sendto))) {
                if ((isset($billto) && is_array($billto))) {
                    $opc_billto = $billto;
                }
                if ((isset($sendto) && is_array($sendto))) {
                    $opc_sendto = $sendto;
                }
                if (!tep_session_is_registered('billto'))
                    tep_session_register('billto');
                if (!tep_session_is_registered('sendto'))
                    tep_session_register('sendto');
            }else
            if (tep_session_is_registered('customer_id')) {
                if (!isset($billto) || intval($billto) == 0)
                    $billto = $customer_default_address_id;
                if (!isset($sendto) || intval($sendto) == 0)
                    $sendto = $customer_default_address_id;
                if (!tep_session_is_registered('billto'))
                    tep_session_register('billto');
                if (!tep_session_is_registered('sendto'))
                    tep_session_register('sendto');
            }else {
                $__ship_country_id = STORE_COUNTRY;
                $_country_info = \common\helpers\Country::get_countries($__ship_country_id, true);
                $opc_sendto = array(
                    'zone_id' => isset($ship_zone_id) ? $ship_zone_id : 0,
                    'country' => array(
                        'id' => $__ship_country_id,
                        'title' => $_country_info['countries_name'],
                        'iso_code_2' => $_country_info['countries_iso_code_2'],
                        'iso_code_3' => $_country_info['countries_iso_code_3'],
                    ),
                    'country_id' => $__ship_country_id,
                    'format_id' => \common\helpers\Address::get_address_format_id($__ship_country_id),
                );
                $opc_billto = $opc_sendto;
            }
            $order = new opc_order();
            if (isset($_SESSION['guest_email_address']) && !empty($_SESSION['guest_email_address'])) {
                if (tep_session_is_registered('customer_id')) {
                    unset($_SESSION['guest_email_address']);
                } else {
                    $order->customer['email_address'] = $_SESSION['guest_email_address'];
                }
            }
        }
// {{
        if (false) {
            if ($session->has('customer_id')) {
                // user is logged in
                if (!$session['billto'] && !isset($_POST['billto']))
                    $GLOBALS['billto'] = $session['customer_default_address_id'];
                if (!$session['sendto'] && !isset($_POST['sendto']))
                    $GLOBALS['sendto'] = $session['customer_default_address_id'];
                // the order class (uses the sendto !)

                $order = new order();

                if (!isset($_POST['country']))
                    $country = $order->billing['country']['id'];
                if (!isset($_POST['ship_country']))
                    $ship_country = $order->delivery['country']['id'];

                if (isset($_POST['ship_country'])) {
                    // country is selected
                    $country_info = \common\helpers\Country::get_countries(tep_db_prepare_input($_POST['ship_country']), true);
                    $order->delivery['postcode'] = tep_db_prepare_input($_POST['ship_postcode']);
                    $order->delivery['country'] = array('id' => tep_db_prepare_input($_POST['ship_country']), 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']);
                    $order->delivery['country_id'] = tep_db_prepare_input($_POST['ship_country']);
                    $order->delivery['format_id'] = \common\helpers\Address::get_address_format_id(tep_db_prepare_input($_POST['ship_country']));
                }
            } else {
                $order = new order();

// user not logged in !
                $country = isset($_POST['country']) ? intval($_POST['country']) : STORE_COUNTRY;
                $ship_country = isset($_POST['ship_country']) ? intval($_POST['ship_country']) : STORE_COUNTRY;
                $ship_zone = isset($_POST['ship_state']) ? intval($ship_zone_id) : STORE_ZONE;
// WebMakers.com Added: changes
// changed from STORE_ORIGIN_ZIP to SHIPPING_ORIGIN_ZIP
                $country_info = \common\helpers\Country::get_countries($ship_country, true);
                $order->delivery['postcode'] = SHIPPING_ORIGIN_ZIP;
                $order->delivery['country'] = array('id' => $ship_country, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']);
                $order->delivery['country_id'] = $ship_country;
                $order->delivery['format_id'] = \common\helpers\Address::get_address_format_id($ship_country);
                $order->delivery['zone_id'] = $ship_zone;

                $country_info = \common\helpers\Country::get_countries($country, true);
                $order->billing['country'] = array('id' => $country, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']);
            }
        }
        if (!tep_session_is_registered('cartID'))
            tep_session_register('cartID');
        $_SESSION['cartID'] = $cart->cartID;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
// ICW CREDIT CLASS GV AMENDE LINE BELOW
//  if ($order->content_type == 'virtual') {
        if (($order->content_type == 'virtual') || ($order->content_type == 'virtual_weight')) {
            if (!tep_session_is_registered('shipping'))
                tep_session_register('shipping');
            $shipping = false;
            $sendto = $customer_default_address_id; //???????
        }
        // weight and count needed for shipping !
        $total_weight = $cart->show_weight();
        $total_count = $cart->count_contents();

        $order_total_modules = new \common\classes\order_total(); // load ot lang
// load all enabled shipping modules

        if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {
//            tep_session_unregister('shipping');
//            $shipping = false;

            $shipping_modules = new \common\classes\shipping();

            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                $pass = false;

                switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                    case 'national':
                        if ($order->delivery['country_id'] == STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'international':
                        if ($order->delivery['country_id'] != STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'both':
                        $pass = true;
                        break;
                }

                $free_shipping = false;
                if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                    $free_shipping = true;
                }
            } else {
                $free_shipping = false;
            }
// process the selected shipping method
            if (($error == false) && isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) {
                if (!tep_session_is_registered('comments'))
                    tep_session_register('comments');
                if (tep_not_null($_POST['comments'])) {
                    $comments = tep_db_prepare_input($_POST['comments']);
                }

                if (!tep_session_is_registered('shipping'))
                    tep_session_register('shipping');

                if ((\common\helpers\Modules::count_shipping_modules() > 0) || ($free_shipping == true)) {
                    if ((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_') !== false)) {
                        //$session['shipping'] = $_POST['shipping'];
                        //?????$session['selected_shipping'] = $session['shipping'];
                        list($module, $method) = explode('_', $_POST['shipping']);
                        if ((is_object($GLOBALS[$module]) && $GLOBALS[$module]->enabled) || ($_POST['shipping'] == 'free_free')) {
                            if ($_POST['shipping'] == 'free_free') {
                                $quote = array(
                                    array(
                                        'id' => 'free',
                                        'methods' => array(
                                            array(
                                                'id' => 'free',
                                                'title' => FREE_SHIPPING_TITLE,
                                                'cost' => '0',
                                            ),
                                        ),
                                    )
                                );
                                //$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                                //$quote[0]['methods'][0]['cost'] = '0';
                            } else {
                                $quote = $shipping_modules->quote($method, $module);
                            }
                            if (isset($quote[0]['error'])) {
                                tep_session_unregister('shipping');
                            } else {
                                if (isset($quote[0]['methods'][$method]['title']) && isset($quote[0]['methods'][$method]['cost'])) {
                                    $shipping = array(
                                        'id' => $module . '_' . $method,
                                        'title' => (($free_shipping == true) ? $quote[0]['methods'][$method]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][$method]['title'] . ')'),
                                        'cost' => $quote[0]['methods'][$method]['cost'],
                                        'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote[0]['methods'][$method]['cost'], (isset($quote[0]['tax']) ? $quote[0]['tax'] : 0))
                                    );
                                } else if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                                    $shipping = array(
                                        'id' => $module . '_' . $method,
                                        'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                        'cost' => $quote[0]['methods'][0]['cost'],
                                        'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote[0]['methods'][0]['cost'], (isset($quote[0]['tax']) ? $quote[0]['tax'] : 0))
                                    );
                                } else {
                                    $shipping = false;
                                };
                                $_SESSION['shipping'] = $shipping;
                            }
                        } else {
                            tep_session_unregister('shipping');
                        }
                    } else {
                        $shipping = false;
                        tep_session_unregister('shipping');
                    }
                } else {
                    $shipping = false;
                    $_SESSION['shipping'] = $shipping;
                }
            }
// get all available shipping quotes
            $quotes = $shipping_modules->quote();
        }
// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
        //if ( !$session->has('shipping') || ( $session->has('shipping') && ($session['shipping'] == false) && (\common\helpers\Modules::count_shipping_modules() > 1) ) && ($shipping_modules->cheapest() != false) && !$session->has('selected_shipping')) $session['shipping'] = $shipping_modules->cheapest();
        if (!tep_session_is_registered('shipping') && (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) && (\common\helpers\Modules::count_shipping_modules() > 1) && ($shipping_modules->cheapest() != false)) {
            if (!isset($HTTP_GET_VARS['action']) || ($HTTP_GET_VARS['action'] != 'one_page_checkout')) {
                $shipping = $shipping_modules->cheapest();
                if (!tep_session_is_registered('shipping'))
                    tep_session_register('shipping');
            }
        }

        //shipping error
        // on this stage shipping must be array in any case (free or real)  
        if (
                (isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) &&
                (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) &&
                (!tep_session_is_registered('shipping') || !is_array($_SESSION['shipping']))
        ) {
            $messageStack->add('one_page_checkout', TEXT_CHOOSE_SHIPPING_METHOD);
            $error = true;
            if (tep_session_is_registered('shipping'))
                tep_session_unregister('shipping');
            unset($shipping);
        }
        //\shipping error
        $payment_modules = new payment(); // $payment_modules - for selected country (was update_status)


        $payment_modules->update_status(); //???
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules = new \common\classes\order_total();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->collect_posts();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->pre_confirmation_check();

        $order_total_output = $order_total_modules->process();

        $result = [];
        foreach ($order_total_output as $total) {
            if (class_exists($total['code'])) {
                if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                    if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_CHECKOUT')) {
                        if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                            $result[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_CHECKOUT', $total);
                        } else {
                            $result[] = $total;
                        }
                    }
                }
            }
        }
        $order_total_output = $result;

        if (($error == false) && isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout')) {
            foreach ($_POST as $key => $val) {
                if (!in_array($key, array('ship_as_bill', 'firstname', 'lastname', 'email_address', 'street_address', 'postcode', 'city', 'state', 'country', 'telephone', 'landline', 'ship_firstname', 'ship_lastname', 'ship_street_address', 'ship_postcode', 'ship_city', 'ship_state', 'ship_country', 'billto', 'sendto', 'gender', 'shipping_gender', 'condition', 'street_address_line1', 'street_address_line2', 'ship_street_address_line1', 'ship_street_address_line2', 'company', 'company_vat', 'dob'))) {
                    $_SESSION['one_page_checkout_' . $key] = $val;
//                    global ${'one_page_checkout_' . $key};
//                    $session['one_page_checkout_' . $key] = $val;
                }
            }
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        }

        $payment_error = '';
        if (isset($_GET['payment_error']) && is_object($GLOBALS[$_GET['payment_error']]) && method_exists($GLOBALS[$_GET['payment_error']], 'get_error')) {
            if (is_object($payment_modules)) {
                $payment_modules->selected_module = $_GET['payment_error'];
                $payment_error = $payment_modules->get_error();
            } else {
                $payment_error = $GLOBALS[$_GET['payment_error']]->get_error();
            }
        }

        if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
            $messageStack->add('one_page_checkout', tep_db_prepare_input($_GET['error_message']));
        }

        $message = '';
        if ($messageStack->size('one_page_checkout') > 0) {
            $message = $messageStack->output('one_page_checkout');
        }


        $addresses_array = array();
        $addresses_json = array();
        if (tep_session_is_registered('customer_id')) {
            $cust_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
            $cust_data = tep_db_fetch_array($cust_query);
            $addresses_query = tep_db_query("select address_book_id, entry_gender, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street, entry_suburb as suburb, entry_postcode as postcode, entry_city as city, entry_postcode as postcode, if(length(zone_name),zone_name,entry_state) as state, entry_zone_id as zone_id, entry_country_id as country_id, zone_name from " . TABLE_ADDRESS_BOOK . " left JOIN " . TABLE_ZONES . " z on z.zone_id = entry_zone_id and z.zone_country_id = entry_country_id where customers_id = '" . $customer_id . "'");
            while ($addresses = tep_db_fetch_array($addresses_query)) {
                if ($billto == $addresses['address_book_id']) {
                    $p_gender = $addresses['entry_gender'];
                }
                $addresses['id'] = $addresses['address_book_id'];
                $addresses['text'] = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($addresses['country_id']), $addresses, 0, ' ', ' ');
                $addresses_array[] = $addresses;
                $addresses_json[$addresses['address_book_id']] = array(
                    'gender' => $addresses['entry_gender'],
                    'firstname' => $addresses['firstname'],
                    'lastname' => $addresses['lastname'],
                    'street_address_line1' => $addresses['street'],
                    'street_address_line2' => $addresses['suburb'],
                    'postcode' => $addresses['postcode'],
                    'city' => $addresses['city'],
                    'state' => $addresses['state'],
                    'country' => $addresses['country_id'],
                );
            }
        } else {
            $addresses_array = '';
        }


        if ($entry_state_has_zones == true) {
            $zones_array = array();
            $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . intval($_POST['country'] ? $_POST['country'] : $order->billing['country']['id']) . "' order by zone_name");
            $zones_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
            while ($zones_values = tep_db_fetch_array($zones_query)) {
                $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
            }
        }


        $quotes_radio_buttons = 0;
        if ($free_shipping) {
            $quotes = array(
                array(
                    'id' => 'free',
                    'module' => FREE_SHIPPING_TITLE,
                    'methods' => array(
                        array(
                            'id' => 'free',
                            'selected' => true,
                            'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)),
                            'code' => 'free_free',
                            'cost_f' => '&nbsp;',
                            'cost' => 0,
                        ),
                    ),
                ),
            );
            $quotes_radio_buttons++;
        } else {
            global $select_shipping;
            if (empty($select_shipping)) {
                $select_shipping = is_array($shipping) ? $shipping['id'] : '';
            }
            if (isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout') && isset($_POST['shipping'])) {
                $select_shipping = tep_db_prepare_input($_POST['shipping']);
            }
            for ($i = 0, $n = sizeof($quotes); $i < $n; $i++) {
                if (!isset($quotes[$i]['error'])) {
                    for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j++) {
                        $quotes[$i]['methods'][$j]['cost_f'] = $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
                        $quotes[$i]['methods'][$j]['code'] = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
                        $quotes[$i]['methods'][$j]['selected'] = ($select_shipping == $quotes[$i]['methods'][$j]['code']);
                        $quotes_radio_buttons++;
                    }
                }
            }
            if ($quotes_radio_buttons == 1) {
                $quotes[0]['methods'][0]['selected'] = true;
            }
        }



        $_selected_payment = isset($_SESSION['payment']) && !empty($_SESSION['payment']) ? $_SESSION['payment'] : '';
        if (isset($_GET['action']) && ($_GET['action'] == 'one_page_checkout') && isset($_POST['payment'])) {
            $_selected_payment = tep_db_prepare_input($_POST['payment']);
        }
        $selection = $payment_modules->selection(true);
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
        global $currency;
        $credit_modules = array(
            'ot_coupon' => false,
            'ot_gv' => false,
            'applied_coupon_code' => (isset($_SESSION['cc_id']) && $_SESSION['cc_id'] > 0) ? \common\helpers\Coupon::get_coupon_name($_SESSION['cc_id']) : '',
            'credit_amount_formatted' => $currencies->format(0),
            'credit_amount' => 0,
            'cot_gv_active' => isset($_SESSION['cot_gv']),
            'custom_gv_amount' => (isset($_SESSION['cot_gv']) && is_numeric($_SESSION['cot_gv'])) ? round($_SESSION['cot_gv'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency), 2) : ''
        );
        if (isset($GLOBALS['ot_coupon']) && is_object($GLOBALS['ot_coupon']) && $GLOBALS['ot_coupon']->enabled && $GLOBALS['ot_coupon']->credit_class) {
            $credit_modules['ot_coupon'] = true;
        }
        if (isset($GLOBALS['ot_gv']) && is_object($GLOBALS['ot_gv']) && $GLOBALS['ot_gv']->enabled && $GLOBALS['ot_coupon']->credit_class) {
            $credit_modules['ot_gv'] = true;
            if (tep_session_is_registered('customer_id')) {
                $_customer_credit_amount = tep_db_fetch_array(tep_db_query("SELECT credit_amount FROM " . TABLE_CUSTOMERS . " WHERE customers_id='" . (int) $customer_id . "'"));
                $credit_modules['credit_amount'] = $currencies->format_clear($_customer_credit_amount['credit_amount']);
                if (!$credit_modules['credit_amount'])
                    $credit_modules['credit_amount'] = 0;
                $credit_modules['credit_amount_formatted'] = $currencies->format($_customer_credit_amount['credit_amount']);
            }
        }

        $render_data = [
            'payment_error' => $payment_error,
            'message' => $message,
            'checkout_process_link' => Yii::$app->urlManager->createUrl(['checkout/', 'action' => 'one_page_checkout']),
            'addresses_array' => $addresses_array,
            'addresses_json' => json_encode($addresses_json),
            'payment_javascript_validation' => $payment_javascript_validation,
            'is_logged_customer' => tep_session_is_registered('customer_id'),
            'email_address' => $order->customer['email_address'],
            'telephone' => $order->customer['telephone'],
            'landline' => $order->customer['landline'],
            'customer_company' => $order->customer['company'],
            'customer_company_vat' => $order->customer['company_vat'],
            'billto' => $billto,
            'billing_address_book_id' => (isset($order->billing['address_book_id']) ? $order->billing['address_book_id'] : $customer_default_address_id),
            'billing_gender' => $order->billing['gender'],
            'billing_firstname' => $order->billing['firstname'],
            'billing_lastname' => $order->billing['lastname'],
            'billing_street_address' => $order->billing['street_address'],
            'billing_suburb' => $order->billing['suburb'],
            'billing_postcode' => $order->billing['postcode'],
            'billing_city' => $order->billing['city'],
            'billing_state' => ($_POST['state'] ? tep_db_prepare_input($_POST['state']) : $order->billing['state']),
//          'zones_array' => $zones_array,
            'entry_state_has_zones' => false, // $entry_state_has_zones,
            'billing_country' => ($_POST['country'] ? tep_db_prepare_input($_POST['country']) : $order->billing['country']['id']),
            'ship_address_book_id' => (isset($order->delivery['address_book_id']) ? $order->delivery['address_book_id'] : $customer_default_address_id),
            'ship_gender' => $order->delivery['gender'],
            'ship_firstname' => $order->delivery['firstname'],
            'ship_lastname' => $order->delivery['lastname'],
            'ship_street_address' => $order->delivery['street_address'],
            'ship_suburb' => $order->delivery['suburb'],
            'ship_postcode' => $order->delivery['postcode'],
            'ship_city' => $order->delivery['city'],
            'ship_state' => ($_POST['ship_state'] ? tep_db_prepare_input($_POST['ship_state']) : $order->delivery['state']),
            'ship_country' => ($_POST['ship_country'] ? tep_db_prepare_input($_POST['ship_country']) : $order->delivery['country']['id']),
            'is_shipping' => ($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight'),
            'free_shipping' => $free_shipping,
            'quotes' => $quotes,
            'quotes_radio_buttons' => $quotes_radio_buttons,
            'shipping' => $shipping,
            'selection' => $selection,
            //'countries' => \common\helpers\Country::get_countries(),
            'ship_countries' => \common\helpers\Country::get_countries('', false, '', 'ship'),
            'bill_countries' => \common\helpers\Country::get_countries('', false, '', 'bill'),
            'order_total_output' => $order_total_output,
            'comments' => $order->info['comments'],
            'ajax_server_url' => tep_href_link('checkout/recalculate', '', 'SSL'),
            'credit_modules' => $credit_modules,
        ];
        $bill_same_as_ship = true;
        if (isset($_POST['ship_as_bill'])) {
            $bill_same_as_ship = true;
        } else {
            $bill_same_as_ship = true;
            foreach (array(
        'billing_address_book_id' => 'ship_address_book_id',
        'billing_gender' => 'ship_gender',
        'billing_firstname' => 'ship_firstname',
        'billing_lastname' => 'ship_lastname',
        'billing_street_address' => 'ship_street_address',
        'billing_suburb' => 'ship_suburb',
        'billing_postcode' => 'ship_postcode',
        'billing_city' => 'ship_city',
        'billing_state' => 'ship_state',
        'billing_country' => 'ship_country',
            ) as $check_mk1 => $check_mk2) {
                if (strval($render_data[$check_mk1]) != strval($render_data[$check_mk2])) {
                    $bill_same_as_ship = false;
                    break;
                }
            }
        }
        $render_data['bill_not_ship'] = !$bill_same_as_ship;

        if (
                Info::themeSetting('checkout_view') == 1 &&
                $error == true && isset($_GET['action']) &&
                $_GET['action'] == 'one_page_checkout'
        ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = [
                'payment_error' => $payment_error,
                'message' => $message,
                'error_name' => $errorName
            ];
            return json_encode($data);
        } elseif (Info::themeSetting('checkout_view') == 1) {
            $tpl = 'index_2.tpl';
        } else {
            $tpl = 'index.tpl';
        }
        return $this->render($tpl, $render_data);
    }

    function actionRecalculate() {
        global $currencies;
        global $total_weight, $total_count, $order, $shipping, $select_shipping;

        global $cc_id, $cot_gv;
        if (tep_session_is_registered('cot_gv')) {
            tep_session_unregister('cot_gv');
            unset($cot_gv);
        }
        if (tep_session_is_registered('cc_id')) {
            tep_session_unregister('cc_id');
            unset($cc_id);
        }

        if (isset($_POST['payment'])) {
            $_SESSION['payment'] = tep_db_prepare_input($_POST['payment']);
        }

        $country = isset($_POST['country']) ? (int) $_POST['country'] : STORE_COUNTRY;
        $ship_country = isset($_POST['ship_country']) ? (int) $_POST['ship_country'] : STORE_COUNTRY;

        $zones_variants = array();
        $ship_zones_variants = array();
        if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
            $get_country_zones_r = tep_db_query(
                    "SELECT zone_name AS id, zone_name AS text " .
                    "FROM " . TABLE_ZONES . " " .
                    "WHERE zone_country_id='" . (int) $ship_country . "'"
            );
            if (tep_db_num_rows($get_country_zones_r) > 0) {
                $ship_zones_variants[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
                while ($_country_zone = tep_db_fetch_array($get_country_zones_r)) {
                    $ship_zones_variants[] = $_country_zone;
                }
                //$state = \common\helpers\Zones::get_zone_name($__ship_country_id,\common\helpers\Zones::get_zone_id($__ship_country_id,$state),$state);
            }
            if ((int) $country == (int) $ship_country) {
                $zones_variants = $ship_zones_variants;
            } else {
                $get_country_zones_r = tep_db_query(
                        "SELECT zone_name AS id, zone_name AS text " .
                        "FROM " . TABLE_ZONES . " " .
                        "WHERE zone_country_id='" . (int) $country . "'"
                );
                if (tep_db_num_rows($get_country_zones_r) > 0) {
                    $zones_variants[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
                    while ($_country_zone = tep_db_fetch_array($get_country_zones_r)) {
                        $zones_variants[] = $_country_zone;
                    }
                }
            }
        }

        $postcode = tep_db_prepare_input($_POST['postcode']);
        $city = tep_db_prepare_input($_POST['city']);
        if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
            $state = tep_db_prepare_input($_POST['state']);
            if (is_int($_POST['state'])) {
                $zone_id = tep_db_prepare_input($_POST['state']);
            } else {
                $zone_id = 0;
                if (count($zones_variants) > 0) {
                    $get_selected_zone_r = tep_db_query(
                            "SELECT zone_id, zone_name " .
                            "FROM " . TABLE_ZONES . " " .
                            "WHERE zone_country_id='" . (int) $country . "' " .
                            " AND (zone_code='" . tep_db_input($state) . "' OR zone_name='" . tep_db_input($state) . "') " .
                            "LIMIT 1"
                    );
                    if (tep_db_num_rows($get_selected_zone_r) > 0) {
                        $selected_zone = tep_db_fetch_array($get_selected_zone_r);
                        $zone_id = $selected_zone['zone_id'];
                        $state = $selected_zone['zone_name'];
                    }
                }
            }
        }

        $ship_postcode = tep_db_prepare_input($_POST['ship_postcode']);
        if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
            $ship_state = tep_db_prepare_input($_POST['ship_state']);
            if (is_int($_POST['ship_state'])) {
                $ship_zone_id = tep_db_prepare_input($_POST['ship_state']);
            } else {
                $ship_zone_id = 0;
                if (count($zones_variants) > 0) {
                    $get_selected_zone_r = tep_db_query(
                            "SELECT zone_id, zone_name " .
                            "FROM " . TABLE_ZONES . " " .
                            "WHERE zone_country_id='" . (int) $ship_country . "' " .
                            " AND (zone_code='" . tep_db_input($ship_state) . "' OR zone_name='" . tep_db_input($ship_state) . "') " .
                            "LIMIT 1"
                    );
                    if (tep_db_num_rows($get_selected_zone_r) > 0) {
                        $selected_zone = tep_db_fetch_array($get_selected_zone_r);
                        $ship_zone_id = $selected_zone['zone_id'];
                        $ship_state = $selected_zone['zone_name'];
                    }
                }
            }
        }

        // populate posted data into order
        global $opc_billto, $opc_sendto;

        $_country_info = \common\helpers\Country::get_countries($country, true);
        $opc_billto = array(
            'gender' => isset($gender) ? $gender : null,
            'firstname' => isset($firstname) ? $firstname : '',
            'lastname' => isset($lastname) ? $lastname : '',
            'street_address' => isset($street_address) ? $street_address : '',
            'suburb' => isset($suburb) ? $suburb : '',
            'city' => isset($city) ? $city : '',
            'postcode' => isset($postcode) ? $postcode : '',
            'state' => isset($state) ? $state : '',
            'zone_id' => isset($zone_id) ? $zone_id : 0,
            'country' => array(
                'id' => $country,
                'title' => $_country_info['countries_name'],
                'iso_code_2' => $_country_info['countries_iso_code_2'],
                'iso_code_3' => $_country_info['countries_iso_code_3'],
            ),
            'country_id' => $country,
            'format_id' => \common\helpers\Address::get_address_format_id($country),
        );


        $_country_info = \common\helpers\Country::get_countries($ship_country, true);
        $opc_sendto = array(
            'gender' => isset($shipping_gender) ? $shipping_gender : null,
            'firstname' => isset($ship_firstname) ? $ship_firstname : '',
            'lastname' => isset($ship_lastname) ? $ship_lastname : '',
            'street_address' => isset($ship_street_address) ? $ship_street_address : '',
            'suburb' => isset($ship_suburb) ? $ship_suburb : '',
            'city' => isset($ship_city) ? $ship_city : '',
            'postcode' => isset($ship_postcode) ? $ship_postcode : '',
            'state' => isset($ship_state) ? $ship_state : '',
            'zone_id' => isset($ship_zone_id) ? $ship_zone_id : 0,
            'country' => array(
                'id' => $ship_country,
                'title' => $_country_info['countries_name'],
                'iso_code_2' => $_country_info['countries_iso_code_2'],
                'iso_code_3' => $_country_info['countries_iso_code_3'],
            ),
            'country_id' => $ship_country,
            'format_id' => \common\helpers\Address::get_address_format_id($ship_country),
        );
        $order = new opc_order();

        $company_vat_status = 0;
        $customer_company_vat_status = '&nbsp;';
        if (isset($_POST['customer_company_vat'])) {
            $order->customer['company_vat'] = tep_db_prepare_input($_POST['customer_company_vat']);
        }
        if ($ext = \common\helpers\Acl::checkExtension('\common\extensions\VatOnOrder', 'update_vat_status')) {
            list($company_vat_status, $customer_company_vat_status) = $ext::update_vat_status($order);
            $order->customer['company_vat_status'] = $company_vat_status;
        }

        $cart = $_SESSION['cart'];
        /**
         * @var $cart \shoppingCart
         */
        // weight and count needed for shipping !
        $total_weight = $cart->show_weight();
        $total_count = $cart->count_contents();

        $order_total_modules = new \common\classes\order_total();

        $free_shipping = false;
        $quotes = array();
        if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {

            $shipping_modules = new \common\classes\shipping();

            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                $pass = false;

                switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                    case 'national':
                        if ($order->delivery['country_id'] == STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'international':
                        if ($order->delivery['country_id'] != STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'both':
                        $pass = true;
                        break;
                }

                $free_shipping = false;
                if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                    $free_shipping = true;
                }
            } else {
                $free_shipping = false;
            }
            // get all available shipping quotes
            $quotes = $shipping_modules->quote();
        }

        $quotes_radio_buttons = 0;
        if ($free_shipping) {
            $quotes = array(
                array(
                    'id' => 'free',
                    'module' => FREE_SHIPPING_TITLE,
                    'methods' => array(
                        array(
                            'id' => 'free',
                            'selected' => true,
                            'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)),
                            'code' => 'free_free',
                            'cost_f' => '&nbsp;',
                            'cost' => 0,
                        ),
                    ),
                ),
            );
        } else {
            $useChapest = false;
            if (!isset($_POST['shipping'])) {
                $useChapest = true;
            }
            $i_chapest = 0;
            $j_chapest = 0;
            $cost_chapest = 999999;
            for ($i = 0, $n = sizeof($quotes); $i < $n; $i++) {
                if (!isset($quotes[$i]['error'])) {
                    for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j++) {
                        if ($useChapest && $quotes[$i]['methods'][$j]['cost'] < $cost_chapest) {
                            $cost_chapest = $quotes[$i]['methods'][$j]['cost'];
                            $i_chapest = $i;
                            $j_chapest = $j;
                        }
                        $quotes[$i]['methods'][$j]['cost_f'] = $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
                        $quotes[$i]['methods'][$j]['code'] = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
                        $quotes[$i]['methods'][$j]['selected'] = (isset($_POST['shipping']) && $_POST['shipping'] == $quotes[$i]['methods'][$j]['code']);
                        $quotes_radio_buttons++;
                    }
                }
            }
            if ($useChapest) {
                $quotes[$i_chapest]['methods'][$j_chapest]['selected'] = true;
            }
        }
        $keep_shipping = $shipping;
        foreach ($quotes as $quote_info) {
            if (!is_array($quote_info['methods']))
                continue;
            foreach ($quote_info['methods'] as $quote_method) {
                if ($quote_method['selected']) {
                    $shipping = array(
                        'id' => $quote_method['code'],
                        'title' => $quote_info['module'] . (empty($quote_method['title']) ? '' : ' (' . $quote_method['title'] . ')'),
                        'cost' => $quote_method['cost'],
                        'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote_method['cost'], (isset($quote_info['tax']) ? $quote_info['tax'] : 0))
                    );
                    $order->change_shipping($shipping);
                    $select_shipping = $quote_method['code'];
                    tep_session_register('select_shipping');
                }
            }
        }

        //\shipping error
        $payment_modules = new payment(); // $payment_modules - for selected country (was update_status)

        $payment_modules->update_status(); //???
        $selection = $payment_modules->selection();
        $jspayments = array();
        if (is_array($selection))
            foreach ($selection as $p_sel) {
                if (isset($p_sel['methods']) && is_array($p_sel['methods'])) {
                    foreach ($p_sel['methods'] as $p_sel_method) {
                        $jspayments[] = $p_sel_method['id'];
                    }
                }
                $jspayments[] = $p_sel['id'];
            }
        if (count($jspayments) == 0)
            $jspayments[] = 'none';

//ICW ADDED FOR CREDIT CLASS SYSTEM
        global $opc_coupon_pool;
        $opc_coupon_pool = array();
        $order_total_modules = new \common\classes\order_total(array(
            'ONE_PAGE_CHECKOUT' => 'True',
            'ONE_PAGE_SHOW_TOTALS' => 'true',
        ));
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->collect_posts();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->pre_confirmation_check();

        $order_total_output = $order_total_modules->process();

        $result = [];
        foreach ($order_total_output as $total) {
            if (class_exists($total['code'])) {
                if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                    if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_CHECKOUT')) {
                        if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                            $result[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_CHECKOUT', $total);
                        } else {
                            $result[] = $total;
                        }
                    }
                }
            }
        }
        $order_total_output = $result;

        $opc_coupon_pool['message'];
        $opc_coupon_pool['error'];

        $shipping = $keep_shipping;

        $response = array(
            'replace' => array(
                'shipping_method' => $this->render('shipping.tpl', [
                    'quotes_radio_buttons' => $quotes_radio_buttons,
                    'quotes' => $quotes,
                ]),
                'order_totals' => $this->render('totals.tpl', [
                    'order_total_output' => $order_total_output,
                ]),
                'company_vat_status' => $customer_company_vat_status,
            ),
            'credit_modules' => $opc_coupon_pool,
            'payment_allowed' => $jspayments,
                /*
                  'zones' => array(
                  'state' => count($zones_variants)>0?tep_draw_pull_down_menu('state', $zones_variants,$state):tep_draw_input_field('state',$state),
                  'ship_state' => count($ship_zones_variants)>0?tep_draw_pull_down_menu('ship_state', $ship_zones_variants,$ship_state):tep_draw_input_field('ship_state',$state),
                  ),
                 */
        );

        echo json_encode($response);

        die;
    }

    public function actionLogin() {
        global $cart, $navigation, $messageStack, $currencies, $affiliate_ref;
        global $wish_list;
        // customer login details
        global $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $customer_groups_id;

        \common\helpers\Translation::init('js');

        if (isset($_SESSION['guest_email_address'])) {
            unset($_SESSION['guest_email_address']);
        }

        if ($cart->count_contents() == 0) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        if (tep_session_is_registered('customer_id')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }

        global $breadcrumb;
        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        $error = false;
        $checkout_login = 'as_guest';

        if (Yii::$app->request->isPost) {
            $checkout_login = Yii::$app->request->post('checkout_login', 'as_guest');

            switch ($checkout_login) {
                case 'login':

                    $email_address = tep_db_prepare_input($_POST['email_address']);
                    $password = tep_db_prepare_input($_POST['password']);

// Check if email exists
                    $customer = new Customer(Customer::LOGIN_STANDALONE);
                    if (!$customer->loginCustomer($email_address, $password)) {
                        $error = true;
                    } else {
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                    }

                    if ($error == true) {
                        $messageStack->add('checkout_login', TEXT_LOGIN_ERROR);
                        $messageStack->add('checkout_login', '<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN_S . '</a>');
                    }
                    /*
                      $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, groups_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_status = 1 " . (isset($affiliate_ref)?" and affiliate_id = '" . (int)$affiliate_ref . "'":''));
                      if (!tep_db_num_rows($check_customer_query)) {
                      $error = true;
                      $messageStack->add('checkout_login', TEXT_LOGIN_ERROR, 'error');
                      } else {
                      $check_customer = tep_db_fetch_array($check_customer_query);
                      if ( opc::is_temp_customer($check_customer['customers_id']) ){
                      $error = true;
                      $check_customer['customers_password'] = ' ';
                      opc::remove_temp_customer( $check_customer['customers_id'] );
                      };
                      // Check that password is good
                      if (!\common\helpers\Password::validate_password($password, $check_customer['customers_password'])) {
                      $error = true;
                      $messageStack->add('checkout_login', TEXT_LOGIN_ERROR, 'error');
                      $messageStack->add('checkout_login', sprintf(TEXT_PASSWORD_FORGOTTEN_S, tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL')));
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

                      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");

                      // restore cart contents
                      $cart->restore_contents();
                      if ( is_object($wish_list) && method_exists($wish_list, 'restore_contents') ) {
                      $wish_list->restore_contents();
                      }

                      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                      }
                      } */
                    break;
                case 'create_account':

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
                        if (ACCOUNT_COMPANY_VAT_ID == 'required_register' && (empty($company_vat) || !\common\helpers\Validations::checkVAT($company_vat))) {
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
                        if (ACCOUNT_SUBURB == 'required_register' && empty($suburb)) {
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
                        $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country . "'");
                        $check = tep_db_fetch_array($check_query);
                        $entry_state_has_zones = ($check['total'] > 0);
                        if ($entry_state_has_zones == true) {
                            $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country . "' and zone_name = '" . tep_db_input($state) . "'");
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

                    $newsletter = (int) Yii::$app->request->post('newsletter');

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
                        if (isset($suburb)) {
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

                        tep_redirect(tep_href_link('checkout/', '', 'SSL'));
                    }
                    break;
                case 'as_guest':
                default:
                    $email_address = tep_db_prepare_input(Yii::$app->request->post('email_address', ''));
                    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
                        $error = true;
                        $messageStack->add('checkout_as_guest', ENTRY_EMAIL_ADDRESS_ERROR);
                    } elseif (\common\helpers\Validations::validate_email($email_address) == false) {
                        $error = true;
                        $messageStack->add('checkout_as_guest', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
                    } else {
//                        $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and affiliate_id = '" . (int)$affiliate_ref. "'");
//                        $check_email = tep_db_fetch_array($check_email_query);
//                        if ($check_email['total'] > 0) {
//                            $error = true;
//                            $messageStack->add('checkout_as_guest', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
//                        }
                        $_SESSION['guest_email_address'] = $email_address;

                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                    }

                    break;
            }
        }
        $message_checkout_login = '';
        if ($error) {
            if ($messageStack->size('checkout_login') > 0) {
                $message_checkout_login = $messageStack->output('checkout_login');
            };
        }
        $message_checkout_create_account = '';
        if ($error) {
            if ($messageStack->size('create_account') > 0) {
                $message_checkout_create_account = $messageStack->output('create_account');
            };
        }
        $message_checkout_as_guest = '';
        if ($error) {
            if ($messageStack->size('checkout_as_guest') > 0) {
                $message_checkout_as_guest = $messageStack->output('checkout_as_guest');
            };
        }

        if (in_array('required_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } elseif (in_array('visible_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
            $showAddress = true;
        } else {
            $showAddress = false;
        }

        if (Info::themeSetting('checkout_view')) {
            $tpl = 'login_2.tpl';
        } else {
            $tpl = 'login.tpl';
        }

        return $this->render($tpl, [
                    'action' => tep_href_link('checkout/login', '', 'SSL'),
                    'active_tab' => $checkout_login,
                    'message_checkout_login' => $message_checkout_login,
                    'message_checkout_create_account' => $message_checkout_create_account,
                    'message_checkout_as_guest' => $message_checkout_as_guest,
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
                    'customers_newsletter' => isset($newsletter) ? !!$newsletter : true,
        ]);
    }

    public function actionPayment() {

        return $this->render('payment.tpl', ['products' => '']);
    }

    public function actionPaymentAddress() {

        return $this->render('payment-address.tpl', ['products' => '']);
    }

    public function actionShipping() {

        return $this->render('shipping.tpl', ['products' => '']);
    }

    public function actionShippingAddress() {

        return $this->render('shipping-address.tpl', ['products' => '']);
    }

    public function actionConfirmation() {
        global $payment, $shipping, $order, $navigation, $customer_groups_id, $cart, $cartID, $order_total_modules;
        global $comments, $credit_covers;

        global $billto, $sendto;


        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkDisableCheckout')) {
            $ext::checkDisableCheckout($customer_groups_id);
        }
        if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_disable_checkout')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
// if there is nothing in the customers cart, redirect them to the shopping cart page
        if ($cart->count_contents() < 1) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

// avoid hack attempts during the checkout procedure by checking the internal cartID
        if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
            if ($cart->cartID != $cartID) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
            }
        }

        if (!tep_session_is_registered('sendto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
        if (!tep_session_is_registered('billto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
        if (!tep_session_is_registered('shipping')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode(ERROR_NO_SHIPPING_METHOD), 'SSL'));
        }

        if (defined('ONE_PAGE_CHECKOUT') && ONE_PAGE_CHECKOUT == 'True') {
            foreach ($_SESSION as $key => $val) {
                if ($key != '' && strpos($key, 'one_page_checkout_') === 0) {
                    $HTTP_POST_VARS[str_replace('one_page_checkout_', '', $key)] = $val;
                    $_POST[str_replace('one_page_checkout_', '', $key)] = $val;
                }
            }
        }

        if (defined('GERMAN_SITE') && GERMAN_SITE == 'True') {
            if ($_POST['conditions'] == false) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_CONDITIONS_NOT_ACCEPTED), 'SSL', true, false));
            }
        }

        if (!tep_session_is_registered('payment'))
            tep_session_register('payment');
        if (isset($HTTP_POST_VARS['payment'])) {
            $payment = $_SESSION['payment'] = tep_db_prepare_input($HTTP_POST_VARS['payment']);
        }

        if (!tep_session_is_registered('comments'))
            tep_session_register('comments');
        if (tep_not_null($HTTP_POST_VARS['comments'])) {
            $comments = $_SESSION['comments'] = tep_db_prepare_input($HTTP_POST_VARS['comments']);
        }


        // load the selected payment module
        //if ($credit_covers) $payment=''; //ICW added for CREDIT CLASS
        $payment_modules = new payment($payment);

//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order = new order;

        $payment_modules->update_status();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules = new \common\classes\order_total();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->collect_posts();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $order_total_modules->pre_confirmation_check();

// ICW CREDIT CLASS Amended Line
//  if ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) {

        if ((is_array($payment_modules->modules)) && (sizeof($payment_modules->modules) > 1) && (!is_object($GLOBALS[$payment])) && (!$credit_covers) || (is_object($GLOBALS[$payment]) && ($GLOBALS[$payment]->enabled == false))) {
            if ((!is_object($GLOBALS[substr($payment, 0, strpos($payment, '_'))])) || (is_object($GLOBALS[$payment]) && ($GLOBALS[$payment]->enabled == false)) || (is_object($GLOBALS[substr($payment, 0, strpos($payment, '_'))]) && ($GLOBALS[substr($payment, 0, strpos($payment, '_'))]->enabled == false))) {
                tep_session_unregister('payment');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
            }
        }
        if (!defined('ONE_PAGE_POST_PAYMENT')) {
            if (is_array($payment_modules->modules)) {
                $payment_modules->pre_confirmation_check();
            }
        }

// load the selected shipping module

        $shipping_modules = new \common\classes\shipping($shipping);

//ICW Credit class amendment Lines below repositioned
// Stock Check
        if (!$order->stockAllowCheckout()) {
            // Out of Stock
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        $order_total_output = $order_total_modules->process();

        $result = [];
        foreach ($order_total_output as $total) {
            if (class_exists($total['code'])) {
                if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                    if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_CHECKOUT')) {
                        if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                            $result[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_CHECKOUT', $total);
                        } else {
                            $result[] = $total;
                        }
                    }
                }
            }
        }
        $order_total_output = $result;


        if (isset($GLOBALS[$payment]->form_action_url)) {
            $form_action_url = $GLOBALS[$payment]->form_action_url;
        } else {
            $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
        }

        $payment_confirmation = $payment_modules->confirmation();
        if (!is_array($payment_confirmation))
            $payment_confirmation = array();

        if (is_array($payment_confirmation) && isset($payment_confirmation['title']) && !empty($payment_confirmation['title'])) {
            $_SESSION['payment_info'] = $payment_confirmation['title'];
        }

        $payment_process_button_hidden = '';
        if (is_array($payment_modules->modules)) {
            $payment_process_button_hidden = $payment_modules->process_button();
        }

        global $breadcrumb;
        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        if (Info::themeSetting('checkout_view') == 1) {
            $tpl = 'confirmation_2.tpl';
        } else {
            $tpl = 'confirmation.tpl';
        }

        return $this->render($tpl, [
                    'shipping_address_link' => tep_href_link('checkout/index#shipping_address'),
                    'billing_address_link' => tep_href_link('checkout/index#billing_address'),
                    'shipping_method_link' => tep_href_link('checkout/index#shipping_method'),
                    'payment_method_link' => tep_href_link('checkout/index#payment_method'),
                    'cart_link' => tep_href_link('shopping-cart'),
                    'address_label_delivery' => \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'),
                    'address_label_billing' => \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'),
                    'order' => $order,
                    'is_shipable_order' => (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')),
                    'order_total_output' => $order_total_output,
                    'form_action_url' => $form_action_url,
                    'payment_process_button_hidden' => $payment_process_button_hidden,
                    'payment_confirmation' => $payment_confirmation,
        ]);
    }

    public function actionSuccess() {
        global $customer_id, $breadcrumb, $order, $platform_code;

        if (!tep_session_is_registered('customer_id') && !\frontend\design\Info::isAdmin()) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        if (tep_session_is_registered('platform_code')) {
            $platform_code = '';
        }

        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        $order_info = false;

        $order_id = intval(Yii::$app->request->getQueryParam('order_id', 0));

        if ($order_id) {
            $order_info = tep_db_fetch_array(tep_db_query(
                            "SELECT orders_id, orders_status " .
                            "FROM " . TABLE_ORDERS . " " .
                            "WHERE orders_id='" . (int) $order_id . "' AND customers_id = '" . (int) $customer_id . "'"
            ));
        }
        if (!is_array($order_info)) {
            $orders_query = tep_db_query(
                    "select orders_id, orders_status " .
                    "from " . TABLE_ORDERS . " " .
                    "where customers_id = '" . (int) $customer_id . "' " .
                    "order by /*date_purchased*/ orders_id desc limit 1"
            );
            if (tep_db_num_rows($orders_query)) {
                $order_info = tep_db_fetch_array($orders_query);
            }
        }
        $order_info_data = array(
            'order_id' => 0,
            'print_order_href' => (Info::isAdmin() ? '1111' : ''),
            'order' => false,
        );
        if (is_array($order_info)) {
            $order = new order($order_info['orders_id']);
            $order->info['order_id'] = $order_info['orders_id'];
            $order_info_data = array(
                'order_id' => $order_info['orders_id'],
                'print_order_href' => tep_href_link('email-template/invoice', \common\helpers\Output::get_all_get_params(array('order_id')) . 'orders_id=' . $order_info['orders_id'], 'SSL'),
                'order' => $order,
            );
        }

        return $this->render('success.tpl', array_merge([
                    'products' => '',
                    'continue_href' => tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'),
                    'params' => $order_info_data
                                ], $order_info_data));
    }

    public function actionProcess() {
        global $navigation, $cart;
        // if the customer is not logged on, redirect them to the login page
        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        if (!tep_session_is_registered('sendto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

        if ((tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!tep_session_is_registered('payment'))) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

        if (!tep_session_is_registered('sendto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
        if (!tep_session_is_registered('billto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

// avoid hack attempts during the checkout procedure by checking the internal cartID
        if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
            if ($cart->cartID != (string) $_SESSION['cartID']) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
            }
        }

        global $credit_covers, $payment, $shipping, $order, $customer_id, $billto, $sendto, $languages_id, $currencies;
        global $insert_id, $order_totals, $order_total_modules;

// load selected payment module

        if ($credit_covers)
            $payment = ''; //ICW added for CREDIT CLASS
        $payment_modules = new payment($payment);

        if (defined('ONE_PAGE_POST_PAYMENT') && preg_match("/" . preg_quote(FILENAME_CHECKOUT_CONFIRMATION, "/") . "/", $_SERVER['HTTP_REFERER'])) {
            if (is_array($payment_modules->modules)) {
                $payment_modules->pre_confirmation_check();
            }
        }

// load the selected shipping module

        $shipping_modules = new \common\classes\shipping($shipping);

        $order = new order();

        if ($credit_covers) {
            $order->info['order_status'] = MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID_COVERS;
        }

        $order_total_modules = new \common\classes\order_total();

        $order_totals = $order_total_modules->process();

// load the before_process function from the payment modules
        $payment_modules->before_process();

        $order->update_piad_information();

        $insert_id = $order->save_order();

        $order->save_details();

        $order->save_products();

        $order_total_modules->apply_credit(); //ICW ADDED FOR CREDIT CLASS SYSTEM

        foreach ($order->products as $i => $product) {
            $uuid = $payment_modules->before_subscription($i);
            if ($uuid != false) {
                $info = $payment_modules->get_subscription_info($uuid);
                $subscription_id = $order->save_subscription(0, $insert_id, $i, $uuid, $info);
            }
        }

        \common\helpers\System::ga_detection($insert_id);

        $payment_modules->after_process();
        $cart->reset(true);

        tep_session_unregister('sendto');
        tep_session_unregister('billto');
        tep_session_unregister('shipping');
        tep_session_unregister('payment');
        tep_session_unregister('comments');
        if (tep_session_is_registered('credit_covers'))
            tep_session_unregister('credit_covers');
        $order_total_modules->clear_posts();
        
        if($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_after_order_placed')){
            $ext::rf_after_order_placed($insert_id);
        }
        
        if($ext = \common\helpers\Acl::checkExtension('CustomerLoyalty', 'afterOrderCreate')){
            $ext::afterOrderCreate($insert_id);
        }

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id=' . $insert_id, 'SSL'));
    }

    public function actionReorder() {
        global $navigation, $customer_id, $messageStack, $currencies;
        global $cart, $opc_sendto, $opc_billto, $sendto, $billto, $payment, $shipping;
        global $total_weight, $total_count;

        if (!tep_session_is_registered('customer_id')) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => 'checkout/reorder', 'get' => 'order_id=' . (int) (isset($_GET['order_id']) ? $_GET['order_id'] : 0)));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        $oID = (int) $_GET['order_id'];

        $get_order_info_r = tep_db_query(
                "SELECT orders_id, shipping_class, payment_class " .
                "FROM " . TABLE_ORDERS . " " .
                "WHERE orders_id='" . (int) $oID . "' AND customers_id='" . (int) $customer_id . "' "
        );

        if (tep_db_num_rows($get_order_info_r) == 0) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        }

        $_order_info = tep_db_fetch_array($get_order_info_r);

        $get_products_r = tep_db_query(
                "SELECT * " .
                "FROM " . TABLE_ORDERS_PRODUCTS . " " .
                "WHERE orders_id='{$_order_info['orders_id']}' " .
                "ORDER BY is_giveaway, orders_products_id"
        );
        while ($get_product = tep_db_fetch_array($get_products_r)) {
            if (!$get_product['is_giveaway'] && !\common\helpers\Product::check_product((int) $get_product['uprid'])) {
                $messageStack->add('shopping_cart', sprintf(ERROR_REORDER_PRODUCT_DISABLED_S, $get_product['products_name']), 'warning');
                continue;
            }
            if ($get_product['is_giveaway'] && !\common\helpers\Product::is_giveaway((int) $get_product['uprid'])) {
                $messageStack->add('shopping_cart', sprintf(ERROR_REORDER_PRODUCT_DISABLED_S, $get_product['products_name']), 'warning');
                continue;
            }

            $attr = '';
            if (strpos($get_product['uprid'], '{') !== false && preg_match_all('/{(\d+)}(\d+)/', $get_product['uprid'], $attr_parts)) {
                $attr = array();
                foreach ($attr_parts[1] as $_idx => $opt) {
                    $attr[$opt] = $attr_parts[2][$_idx];
                }
            }
            if (!$cart->is_valid_product_data((int) $get_product['uprid'], $attr)) {
                $messageStack->add('shopping_cart', sprintf(ERROR_REORDER_PRODUCT_VARIATION_MISSING_S, $get_product['products_name']), 'warning');
                continue;
            }
            if ($get_product['is_giveaway']) {
                $cart->add_cart((int) $get_product['uprid'], /* $cart->get_quantity(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid((int)$get_product['uprid'], $attr)),1)+ */ $get_product['products_quantity'], $attr, true, 1);
            } else {
                $cart->add_cart((int) $get_product['uprid'], $cart->get_quantity(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid((int) $get_product['uprid'], $attr))) + $get_product['products_quantity'], $attr, false, 0, !!$get_product['gift_wrapped']);
            }
        }
        
        $cart->setReference($oID);

        if (!tep_session_is_registered('cartID'))
            tep_session_register('cartID');
        $_SESSION['cartID'] = $cart->cartID;

        $order_sendto = \common\helpers\Address::find_order_ab($_order_info['orders_id'], 'delivery');
        $order_billto = \common\helpers\Address::find_order_ab($_order_info['orders_id'], 'billing');
        if (is_numeric($order_billto) || is_array($order_billto)) {
            $billto = $order_billto;
            if (!tep_session_is_registered('billto'))
                tep_session_register('billto');
        }
        if (is_numeric($order_sendto) || is_array($order_sendto)) {
            $sendto = $order_sendto;
            if (!tep_session_is_registered('sendto'))
                tep_session_register('sendto');
        }

        if ($messageStack->size('shopping_cart') > 0) {
            $messageStack->convert_to_session('shopping_cart');
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        } elseif (is_array($sendto) || is_array($billto)) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }

        $payment = $_order_info['payment_class'];
        if (!tep_session_is_registered('payment'))
            tep_session_register('payment');

        $order = new order();
        $total_weight = $cart->show_weight();
        $total_count = $cart->count_contents();

        $order_total_modules = new \common\classes\order_total();

        $shipping = false;
        if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {
            list($method, $module) = explode('_', $_order_info['shipping_class']);

            $shipping_modules = new \common\classes\shipping();

            $free_shipping = false;
            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                $pass = false;

                switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                    case 'national':
                        if ($order->delivery['country_id'] == STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'international':
                        if ($order->delivery['country_id'] != STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'both':
                        $pass = true;
                        break;
                }

                if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                    $free_shipping = true;
                }
            }
            // get all available shipping quotes
            if ($free_shipping) {
                $quotes = array(
                    array(
                        'id' => 'free',
                        'module' => FREE_SHIPPING_TITLE,
                        'methods' => array(
                            array(
                                'id' => 'free',
                                'selected' => true,
                                'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)),
                                'code' => 'free_free',
                                'cost_f' => '&nbsp;',
                                'cost' => 0,
                            ),
                        ),
                    ),
                );
            } else {
                $quote = $shipping_modules->quote($method, $module);
            }

            if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                $shipping = array(
                    'id' => $module . '_' . $method,
                    'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                    'cost' => $quote[0]['methods'][0]['cost'],
                    'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote[0]['methods'][0]['cost'], (isset($quote['tax']) ? $quote['tax'] : 0))
                );
            } else {
                $shipping = false;
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
            }

            if (!tep_session_is_registered('shipping'))
                tep_session_register('shipping');
        }



        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
    }

    public function actionNotifyAdmin() {
        $type = Yii::$app->request->post('type', null);
        if (!is_null($type)) {
            if ($type == 'need_analytics') {
                if (class_exists('\common\models\Google')) {
                    \common\models\Google::notify();
                }
            }
        }
        exit();
    }
    
    private $use_social = false;
    
    public function __construct($id, $module){
        parent::__construct($id, $module);
        
        $platform_config = new \common\classes\platform_config(PLATFORM_ID);
        
        $this->use_social = $platform_config->checkNeedSocials();
        if ($this->use_social){
            \common\models\Socials::loadComponents(PLATFORM_ID);
        }
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

    public function onAuthSuccess($client)
    {
        \common\helpers\Translation::init('account/login');
        (new Socials($client))->handle();
    }
    

}
