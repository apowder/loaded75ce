<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use Yii;
use common\classes\order;
use common\classes\opc;
use common\classes\currencies;
use common\models\Socials;

class Customer {

    const LOGIN_STANDALONE = 1;
    const LOGIN_RECOVERY = 2;
    const LOGIN_SOCIALS = 3;

    private $loginType;
    private $data = [];
    private $temporary = [];

    public function __construct($type = 0) {
        $this->loginType = $type;
    }

    public function loginCustomer($email_address, $checkParam) {
        global $cart, $wish_list, $affiliate_ref;
        global $customer_id, $customer_default_address_id, $customer_first_name, $customer_country_id, $customer_zone_id, $customer_groups_id;

        if (!$this->loginType) {
            return false;
        }

        $success = true;

        if (tep_session_is_registered('customer_id'))
            return $success;

        $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, groups_id from " . TABLE_CUSTOMERS . " where (customers_email_address = '" . tep_db_input($email_address) . "' or erp_customer_code = '" . tep_db_input($email_address) . "') and customers_status = 1 " . (isset($affiliate_ref) && get_affiliate_own_customers() ? " and affiliate_id = '" . (int) $affiliate_ref . "'" : ''));

        if (!tep_db_num_rows($check_customer_query)) {
            $success = false;
        } else {
            $check_customer = tep_db_fetch_array($check_customer_query);
            if (opc::is_temp_customer($check_customer['customers_id'])) {
                $success = false;
                $check_customer['customers_password'] = ' ';
                opc::remove_temp_customer($check_customer['customers_id']);
            };

            // Check that password is good
            switch ($this->loginType) {
                case static::LOGIN_STANDALONE :
                    if (!\common\helpers\Password::validate_password($checkParam, $check_customer['customers_password']))
                        $success = false;
                    break;
                case static::LOGIN_RECOVERY :
                    if (!$this->checkValidToken($check_customer['customers_id'], $checkParam))
                        $success = false;
                    break;
                case static::LOGIN_SOCIALS :
                    if ($checkParam != Socials::HASHCODE)
                        $success = false;
                    break;
            }

            if ($success) {
                if (SESSION_RECREATE == 'True') {
                    tep_session_recreate();
                }

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
            }
        }

        return $success;
    }

    private function checkValidToken($cid, $token) {
        $query_token = tep_db_fetch_array(tep_db_query("select token from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int) $cid . "'"));
        if ($query_token) {
            return $query_token['token'] == $token;
        }
        return false;
    }

    public function loadCustomer($customer_id) {
        global $currencies;
        if (!is_object($currencies))
            $currencies = new currencies();
        $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, groups_id, customers_currency_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "' /*and customers_status = 1*/ ");
        if (tep_db_num_rows($check_customer_query)) {
            $check_customer = tep_db_fetch_array($check_customer_query);
            $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $check_customer['customers_id'] . "' and address_book_id = '" . (int) $check_customer['customers_default_address_id'] . "'");
            $check_country = tep_db_fetch_array($check_country_query);
            $this->data['customer_id'] = $check_customer['customers_id'];
            $this->data['customer_default_address_id'] = $check_customer['customers_default_address_id'];
            $this->data['customer_first_name'] = $check_customer['customers_firstname'];
            $this->data['customer_country_id'] = $check_country['entry_country_id'];
            $this->data['customer_zone_id'] = $check_country['entry_zone_id'];
            $this->data['currency_id'] = $check_customer['customers_currency_id'];
            $helper = \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'code');
            $this->data['currency'] = $helper[$check_customer['customers_currency_id']];

            if (CUSTOMERS_GROUPS_ENABLE == 'True') {
                $this->data['customer_groups_id'] = $check_customer['groups_id'];
            } else {
                $this->data['customer_groups_id'] = 0;
            }
            return $this;
        }
        return false;
    }

    public function setParam($name, $value) {
        $this->data[$name] = $value;
    }

    public function clearParam($name) {
        unset($this->data[$name]);
        unset($this->temporary[$name]);
    }

    public function convertToSession() {
        if (is_array($this->data) && count($this->data)) {
            $this->temporary = [];
            foreach ($this->data as $key => $value) {
                //global $$key;
                if (!tep_session_is_registered($key))
                    tep_session_register($key);
                $this->temporary[$key] = $GLOBALS[$key];
                unset($GLOBALS[$key]);
                $_SESSION[$key] = $value;
                $GLOBALS[$key] = &$_SESSION[$key];
            }
        }
    }

    public function convertBackSession() {
        if (is_array($this->temporary) && count($this->temporary)) {
            $this->data = [];
            foreach ($this->temporary as $key => $value) {
                //global $$key;
                if (tep_not_null($value)) {
                    $this->data[$key] = $value;
                    unset($GLOBALS[$key]);
                    $_SESSION[$key] = $value;
                    $GLOBALS[$key] = &$_SESSION[$key];
                }
            }
            $GLOBALS['currency'] = DEFAULT_CURRENCY;
        }
    }

}
