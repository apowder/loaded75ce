<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class Address {

    public static function html_quotes($string) {
        return str_replace("'", "&#39;", $string);
    }

    public static function html_unquote($string) {
        return str_replace("&#39;", "'", $string);
    }

    public static function show_address_entry($prefix, $entry, $hidden = false, $params = '') {
        global $languages_id;
        if ($hidden) {
            $str = '';
            $str .= '        <table width="100%" border="0">';
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_FIRST_NAME . '</nobr></td>';
            $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['firstname']) . tep_draw_hidden_field($prefix . 'firstname', $entry['firsname']) . '</td></tr>';
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_LAST_NAME . '</nobr></td>';
            $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['lastname']) . tep_draw_hidden_field($prefix . 'lastname', $entry['lastname']) . '</td></tr>';

            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_COMPANY . '</nobr></td>';
            $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['company']) . tep_draw_hidden_field($prefix . 'company', $entry['company']) . '</td></tr>';
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_STREET_ADDRESS . '</nobr></td>';
            $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['street_address']) . tep_draw_hidden_field($prefix . 'street_address', $entry['street_address']) . '</td></tr>';
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_SUBURB . '</nobr></td>';
                $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['suburb']) . tep_draw_hidden_field($prefix . 'suburb', $entry['suburb']) . '</td></tr>';
            }
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_CITY . '</nobr></td>';
            $str .= '          <td width="50%" class="main"><nobr>' . self::html_quotes($entry['city']) . tep_draw_hidden_field($prefix . 'city', $entry['city']) . '</nobr></td></tr>';
            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_STATE . (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'required_register' ? '<span class="required">'.REQUIRED_TEXT.'</span>' : '') . '</nobr></td>';
                $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['state']) . tep_draw_hidden_field($prefix . 'state', $entry['state']) . '</td></tr>';
            }
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_POST_CODE . '</nobr></td>';
            $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['postcode']) . tep_draw_hidden_field($prefix . 'postcode', $entry['postcode']) . '</td></tr>';
            $str .= '          <tr><td width="50%" class="main"><nobr>' . ENTRY_COUNTRY . '</nobr></td>';
            if (is_array($entry['country'])) {
                $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['country']['title']) . tep_draw_hidden_field($prefix . 'country', $entry['country']['title']) . '</td></tr>';
            } else {
                $str .= '          <td width="50%" class="main">' . self::html_quotes($entry['country']) . tep_draw_hidden_field($prefix . 'country', $entry['country']) . '</td></tr>';
            }
            $str .= '          </table>';
        } else {
            $str = '';
            $str .= '<div class="widget-content widget-content-top-border">';
            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= '<div class="w-line-row w-line-row-1"><div class="wl-td"><label>' . ENTRY_GENDER . (ACCOUNT_GENDER == 'required' || ACCOUNT_GENDER == 'required_register' ? '<span class="required">'.REQUIRED_TEXT.'</span>' : '') . '</label><label class="radio-inline"><input type="radio" name="' . $prefix . 'gender" value="m" ' . ($entry['gender'] == 'm' ? 'checked=""' : '') . ' class="radio-inline">' . T_MR . '</label><label class="radio-inline"><input type="radio" name="' . $prefix . 'gender" value="f" ' . ($entry['gender'] == 'f' ? 'checked=""' : '') . ' class="radio-inline">' . T_MRS . '</label><label class="radio-inline"><input type="radio" name="' . $prefix . 'gender" value="s" ' . ($entry['gender'] == 's' ? 'checked=""' : '') . ' class="radio-inline">' . T_MISS . '</label></div></div>';
            }
            $str .= '<div class="w-line-row w-line-row-2">
                 <div>
                    <div class="wl-td">
                        <label>' . ENTRY_FIRST_NAME . '<span class="fieldRequired">*</span></label>' . tep_draw_input_field($prefix . 'firstname', $entry['firstname'], 'size="25" class="form-control"' . $params) . '                              
                    </div>
                </div>
                <div>
                    <div class="wl-td">
                        <label>' . ENTRY_LAST_NAME . '<span class="fieldRequired">*</span></label>' . tep_draw_input_field($prefix . 'lastname', $entry['lastname'], 'size="25" class="form-control"' . $params) . '                              
                    </div>
                </div>
             </div>';
            $str .= '</div>';
            $str .= '<div class="widget-content widget-content-top-border">';
            $str .= '<div class="w-line-row w-line-row-2">
                 <div>
                    <div class="wl-td">
                        <label>' . ENTRY_POST_CODE . '<span class="fieldRequired">*</span></label>' . tep_draw_input_field($prefix . 'postcode', $entry['postcode'], 'size="25" class="form-control"' . $params) . '                              
                    </div>
                </div>
                <div>
                    <div class="wl-td">
                        <label>' . ENTRY_STREET_ADDRESS . '<span class="fieldRequired">*</span></label>' . tep_draw_input_field($prefix . 'street_address', $entry['street_address'], 'size="25" class="form-control"' . $params) . '                              
                    </div>
                </div>
             </div>';
            $str .= '<div class="w-line-row w-line-row-2">
                 <div>';
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= '<div class="wl-td">
                        <label>' . ENTRY_SUBURB . '</label>' . tep_draw_input_field($prefix . 'suburb', $entry['suburb'], 'size="25" class="form-control"' . $params) . '                              
                    </div>';
            }
            $str .= '</div>
                <div>';
            if (is_array($entry['country'])) {
                $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_id = '" . $entry['country']['id'] . "' and language_id = '" . (int) $languages_id . "'");
            } else {
                $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_name like '" . tep_db_input($entry['country']) . "' and language_id = '" . (int) $languages_id . "'");
            }
            $d = tep_db_fetch_array($res);

            if (true) {
                $str .= '   <div class="wl-td">
                        <label>' . ENTRY_CITY . '<span class="fieldRequired">*</span></label>' . tep_draw_input_field($prefix . 'city', $entry['city'], 'size="25" class="form-control"' . $params) . '                              
                    </div>';
            }
            $str .= '</div>
             </div>';
            $str .= '<div class="w-line-row w-line-row-2">
                 <div>';
            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $str .= '<div class="wl-td">
                        <label>' . ENTRY_STATE . (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'required_register' ? '<span class="required">'.REQUIRED_TEXT.'</span>' : '') . '</label>';
            $str .= tep_draw_input_field($prefix . 'state', $entry['state'], 'size="25" class="form-control"' . $params);
            $str .= '                              
                    </div>';
            }
            $str .= '</div>
                <div>
                    <div class="wl-td">
                        <label>' . ENTRY_COUNTRY . '<span class="fieldRequired">*</span></label>';
            if (is_array($d) && $d['countries_id']) {
                $countries_query = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' order by countries_name");
                $countries_array = array();
                while ($countries = tep_db_fetch_array($countries_query)) {
                    $countries_array[] = array('id' => $countries['countries_name'],
                        'text' => $countries['countries_name']);
                }
                $str .= tep_draw_pull_down_menu($prefix . 'country', $countries_array, $d['countries_name'], ' class="form-control"' . $params) . '</td></tr>';
            } else {
                $str .= tep_draw_input_field($prefix . 'country', (isset($entry['country']) ? (is_array($entry['country']) ? $entry['country']['title'] : strval($entry['country'])) : ''), ' class="form-control"' . $params);
            }
            $str .= '                              
                    </div>
                </div>
             </div><div class="w-line-row w-line-row-1"><span style="color: #f2353c; margin: 22px 0 0; display: block;">' . ENTRY_REQUIRED_FIELDS . '</span></div>';
            $str .= '</div>';
        }
        return $str;
    }

    public static function show_address_entry_new($prefix, $entry, $hidden = false) {
        global $languages_id;
        if ($hidden) {
            $str = '';
            $str .= self::html_quotes($entry['firstname']) . tep_draw_hidden_field($prefix . 'firstname', $entry['firsname']) . '&nbsp;';
            $str .= self::html_quotes($entry['lastname']) . tep_draw_hidden_field($prefix . 'lastname', $entry['lastname']) . '<br>';
            $str .= self::html_quotes($entry['company']) . tep_draw_hidden_field($prefix . 'company', $entry['company']) . (self::html_quotes($entry['company']) == '' ? '' : ', ');
            $str .= self::html_quotes($entry['street_address']) . tep_draw_hidden_field($prefix . 'street_address', $entry['street_address']) . ', ';
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= self::html_quotes($entry['suburb']) . tep_draw_hidden_field($prefix . 'suburb', $entry['suburb']) . (self::html_quotes($entry['suburb']) == '' ? '' : ', ');
            }
            $str .= self::html_quotes($entry['city']) . tep_draw_hidden_field($prefix . 'city', $entry['city']) . (self::html_quotes($entry['city']) == '' ? '' : ', ');
            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= self::html_quotes($entry['state']) . tep_draw_hidden_field($prefix . 'state', $entry['state']) . (self::html_quotes($entry['state']) == '' ? '' : ', ');
            }
            $str .= self::html_quotes($entry['postcode']) . tep_draw_hidden_field($prefix . 'postcode', $entry['postcode']) . (self::html_quotes($entry['postcode']) == '' ? '' : ', ');
            if (is_array($entry['country'])) {
                $str .= self::html_quotes($entry['country']['title']) . tep_draw_hidden_field($prefix . 'country', $entry['country']['title']);
            } else {
                $str .= self::html_quotes($entry['country']) . tep_draw_hidden_field($prefix . 'country', $entry['country']) . (self::html_quotes($entry['country']) == '' ? '' : ', ');
            }
        } else {
            $str = '';
            $str .= tep_draw_input_field($prefix . 'firstname', $entry['firstname'], 'size="25"');
            $str .= tep_draw_input_field($prefix . 'lastname', $entry['lastname'], 'size="25"');
            $str .= tep_draw_input_field($prefix . 'company', $entry['company'], 'size="25"');

            $str .= tep_draw_input_field($prefix . 'street_address', $entry['street_address'], 'size="25"');
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $str .= tep_draw_input_field($prefix . 'suburb', $entry['suburb'], 'size="25"');
            }
            $str .= tep_draw_input_field($prefix . 'city', $entry['city'], 'size="25"');

            if (is_array($entry['country'])) {
                $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_id = '" . $entry['country']['id'] . "' and language_id = '" . (int) $languages_id . "'");
            } else {
                $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_name like '" . tep_db_input($entry['country']) . "' and language_id = '" . (int) $languages_id . "'");
            }
            $d = tep_db_fetch_array($res);
            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $res = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id='" . $d['countries_id'] . "'");
                $check = tep_db_fetch_array($res);
                if ($check['total'] > 0) {
                    $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . $d['countries_id'] . "' order by zone_name");
                    $zones_array = array();
                    while ($zones = tep_db_fetch_array($zones_query)) {
                        $zones_array[] = array('id' => $zones['zone_name'],
                            'text' => $zones['zone_name']);
                    }
                    $str .= tep_draw_pull_down_menu($prefix . 'state', $zones_array, $entry['state'], 'style="width:165px"');
                } else {
                    $str .= tep_draw_input_field($prefix . 'state', $entry['state'], 'size="25"');
                }
            }
            $str .= tep_draw_input_field($prefix . 'postcode', $entry['postcode'], 'size="25"');

            if ($d['countries_id']) {
                $countries_query = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' order by countries_name");
                $countries_array = array();
                while ($countries = tep_db_fetch_array($countries_query)) {
                    $countries_array[] = array('id' => $countries['countries_name'],
                        'text' => $countries['countries_name']);
                }
                $str .= tep_draw_pull_down_menu($prefix . 'country', $countries_array, $d['countries_name'], 'style="width:165px"');
            } else {
                $str .= tep_draw_input_field($prefix . 'country', $entry['country'], 'style="width:100px"');
            }
        }
        return $str;
    }

    public static function address_format($address_format_id, $address, $html, $boln, $eoln, $microData = false) {
        $address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . (int) $address_format_id . "'");
        $address_format = tep_db_fetch_array($address_format_query);

        $company = \common\helpers\Output::output_string_protected($address['company']);
        $company_vat = \common\helpers\Output::output_string_protected($address['company_vat']);
        if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
            $firstname = \common\helpers\Output::output_string_protected($address['firstname']);
            $lastname = \common\helpers\Output::output_string_protected($address['lastname']);
        } elseif (isset($address['name']) && tep_not_null($address['name'])) {
            $firstname = \common\helpers\Output::output_string_protected($address['name']);
            $lastname = '';
        } else {
            $firstname = '';
            $lastname = '';
        }
        $street = \common\helpers\Output::output_string_protected($address['street_address']);
        $suburb = \common\helpers\Output::output_string_protected($address['suburb']);
        $city = \common\helpers\Output::output_string_protected($address['city']);
        $state = \common\helpers\Output::output_string_protected($address['state']);
        $reg_number = \common\helpers\Output::output_string_protected($address['reg_number']);
        if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
            $country = \common\helpers\Country::get_country_name($address['country_id']);

            if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
                $state = \common\helpers\Zones::get_zone_code($address['country_id'], $address['zone_id'], $state);
            }
        } elseif (isset($address['country']) && tep_not_null($address['country']) && is_string($address['country'])) {
            $country = \common\helpers\Output::output_string_protected($address['country']);
        } else {
            $country = '';
        }
        $postcode = \common\helpers\Output::output_string_protected($address['postcode']);
        $zip = $postcode;

        if ($html) {
            // HTML Mode
            $HR = '<hr>';
            $hr = '<hr>';
            if (($boln == '') && ($eoln == "\n")) { // Values not specified, use rational defaults
                $CR = '<br>';
                $cr = '<br>';
                $eoln = $cr;
            } else { // Use values supplied
                $CR = $eoln . $boln;
                $cr = $CR;
            }
        } else {
            // Text Mode
            $CR = $eoln;
            $cr = $CR;
            $HR = '----------------------------------------';
            $hr = '----------------------------------------';
        }

        $statecomma = '';
        $streets = $street;
        if ($suburb != '')
            $streets = $street . $cr . $suburb;
        if ($country == '' && is_string($address['country']))
            $country = \common\helpers\Output::output_string_protected($address['country']);
        if ($state != '')
            $statecomma = $state . ', ';

        $fmt = $address_format['format'];

        if (!$firstname) {
            $pos = strpos($fmt, '$firstname');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
            $n = false;
        }
        if (!$streets) {
            $pos = strpos($fmt, '$streets');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
            $n = false;
        }
        if (!$city) {
            $pos = strpos($fmt, '$city');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
            $n = false;
        }
        if (!$state) {
            $pos = strpos($fmt, '$state');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
            $n = false;
        }
        if (!$postcode) {
            $pos = strpos($fmt, '$postcode') + 1;
            if ($pos !== false) {
                $n = stripos($fmt, '$cr', $pos);
                $n2 = stripos($fmt, '$', $pos);
                if ($n && $n == $n2) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
            }
            $n = false;
        }
        if (!$country) {
            $pos = strpos($fmt, '$country');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
        }
        if (!$reg_number) {
            $pos = strpos($fmt, '$reg_number');
            if ($pos !== false) $n = stripos($fmt, '$cr', $pos);
            if ($n) $fmt = substr($fmt, 0, $n) . substr($fmt, $n + 3);
        }

        if ($microData) {
            $street = $street ? '<span itemprop="streetAddress">' . $street . '</span>' : '';
            $streets = $streets ? '<span itemprop="streetAddress">' . $streets . '</span>' : '';
            $city = $city ? '<span itemprop="addressLocality">' . $city . '</span>' : '';
            $state = $state ? '<span itemprop="addressRegion">' . $state . '</span>' : '';
            $country = $country ? '<span itemprop="addressCountry">' . $country . '</span>' : '';
            $postcode = $postcode ? '<span itemprop="postalCode">' . $postcode . '</span>' : '';
            $reg_number = $reg_number ? '<span itemprop="leiCode">' . $reg_number . '</span>' : '';
            $zip = $postcode;
        }
        $a = "$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country$cr" . ($reg_number ? TEXT_REG_NUMBER . ' ' : '') . "$reg_number";

        eval("\$address = \"$fmt\";");

        if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register']) && (tep_not_null($company_vat) && !$microData)) {
            $address = $company_vat . $cr . $address;
        }
        if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']) && (tep_not_null($company) && !$microData)) {
            $address = $company . $cr . $address;
        }

        if (substr($address, 0-strlen($cr)) == $cr){
            $address = substr($address, 0, 0-strlen($cr));
        }

        return $address;
    }

    public static function address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
        $address_query = tep_db_query("select entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customers_id . "' and address_book_id = '" . (int) $address_id . "'");
        $address = tep_db_fetch_array($address_query);

        $format_id = self::get_address_format_id($address['country_id']);

        return self::address_format($format_id, $address, $html, $boln, $eoln);
    }
    
    public static function get_address_format_id($country_id) {
        global $languages_id;
        $address_format_query = tep_db_query("select address_format_id as format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $country_id . "' and language_id = '" . (int) $languages_id . "'");
        if (tep_db_num_rows($address_format_query)) {
            $address_format = tep_db_fetch_array($address_format_query);
            return $address_format['format_id'];
        } else {
            return '1';
        }
    }
    
    public static function find_order_ab($order_id, $ab = 'delivery') {
        $order_column_prefix = 'delivery_';
        if (in_array($ab, array('customers', 'billing', 'delivery'))) {
            $order_column_prefix = $ab . '_';
        }

        $columns = array();
        $ab_compare = array();
        if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
            $ab_compare[] = "IFNULL(o.{$order_column_prefix}gender,'')=IFNULL(ab.entry_gender,'') ";
            $columns[] = "o.{$order_column_prefix}gender AS order_gender";
        } else {
            $columns[] = "'' AS order_gender";
        }
        $ab_compare[] = "o.{$order_column_prefix}name=TRIM(CONCAT(ab.entry_firstname,' ',ab.entry_lastname)) ";
        $columns[] = "o.{$order_column_prefix}name AS order_name";
        $columns[] = "o.{$order_column_prefix}firstname AS order_firstname";
        $columns[] = "o.{$order_column_prefix}lastname AS order_lastname";

        /*
        company stored in customers table
        if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])) {
            $ab_compare[] = "IFNULL(o.{$order_column_prefix}company,'')=IFNULL(ab.entry_company,'') ";
            $columns[] = "IFNULL(o.{$order_column_prefix}company,'') AS order_company";
        } else {
            $columns[] = "'' AS order_company";
        }
        */
        $ab_compare[] = "IFNULL(o.{$order_column_prefix}street_address,'')=IFNULL(ab.entry_street_address,'') ";
        $columns[] = "o.{$order_column_prefix}street_address AS order_street_address";
        if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $ab_compare[] = "IFNULL(o.{$order_column_prefix}suburb,'')=IFNULL(ab.entry_suburb,'') ";
            $columns[] = "o.{$order_column_prefix}suburb AS order_suburb";
        } else {
            $columns[] = "'' AS order_suburb";
        }
        $ab_compare[] = "IFNULL(o.{$order_column_prefix}city,'')=IFNULL(ab.entry_city,'') ";
        $columns[] = "o.{$order_column_prefix}city AS order_city";
        $ab_compare[] = "IFNULL(o.{$order_column_prefix}postcode,'')=IFNULL(ab.entry_postcode,'') ";
        $columns[] = "o.{$order_column_prefix}postcode AS order_postcode";

        $columns[] = "o.{$order_column_prefix}state AS order_state";
        $columns[] = "o.{$order_column_prefix}country AS order_country";

        $find_ab_sql = "SELECT ab.*, " . implode(", ", $columns) . ", IFNULL(z.zone_name, ab.entry_state) AS entry_state " .
                "FROM " . TABLE_ORDERS . " o " .
                " LEFT JOIN " . TABLE_ADDRESS_BOOK . " ab ON ab.customers_id=o.customers_id AND (" . implode('AND ', $ab_compare) . ") " .
                " LEFT JOIN " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) " .
                "WHERE o.orders_id='" . (int) $order_id . "'";

        $get_ab_r = tep_db_query($find_ab_sql);

        $ab_data = false;
        if (tep_db_num_rows($get_ab_r) > 0) {
            while ($_ab = tep_db_fetch_array($get_ab_r)) {

                if ($ab_data === false) {
                    $country_match = tep_db_fetch_array(tep_db_query(
                                    "SELECT countries_id, countries_iso_code_2, countries_iso_code_3 " .
                                    "FROM " . TABLE_COUNTRIES . " " .
                                    "WHERE countries_name='" . tep_db_input($_ab['order_country']) . "' " .
                                    "LIMIT 1"
                    ));
                    $ab_data = array('address_book_id' => '');
                    foreach (preg_grep('/^order_/', array_keys($_ab)) as $_key) {
                        $ab_data[str_replace('order_', '', $_key)] = $_ab[$_key];
                    }
                    $ab_data['country_id'] = $country_match['countries_id'];
                    $country_info = \common\helpers\Country::get_countries($ab_data['country_id'], true);
                    $ab_data['country'] = array(
                        'id' => $ab_data['country_id'],
                        'title' => $country_info['countries_name'],
                        'iso_code_2' => $country_info['countries_iso_code_2'],
                        'iso_code_3' => $country_info['countries_iso_code_3'],
                    );

                    $ab_data['zone_id'] = 0;
                    if (!empty($_ab['order_state'])) {
                        $_check_zone_id_r = tep_db_query(
                                "SELECT zone_id " .
                                "FROM " . TABLE_ZONES . " " .
                                "WHERE zone_name='" . tep_db_input($_ab['order_state']) . "' AND zone_country_id='" . $ab_data['country_id'] . "' " .
                                "LIMIT 1"
                        );
                        if (tep_db_num_rows($_check_zone_id_r) > 0) {
                            $_check_zone_id = tep_db_fetch_array($_check_zone_id_r);
                            $ab_data['zone_id'] = (int) $_check_zone_id['zone_id'];
                        }
                    }
                }
                if (is_null($_ab['address_book_id'])) {
                    break;
                }
                $state_match = true;
                if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $state_match = ( $_ab['entry_state'] == $ab_data['state']);
                }
                if ((int) $_ab['entry_country_id'] == (int) $ab_data['country_id'] && $state_match) {
                    $ab_data = $_ab['address_book_id'];
                    break;
                }
            }
        }

        return $ab_data;
    }

    public static function get_address_formats() {
        $address_format_query = tep_db_query("select address_format_id from " . TABLE_ADDRESS_FORMAT . " order by address_format_id");
        $address_format_array = array();
        while ($address_format_values = tep_db_fetch_array($address_format_query)) {
            $address_format_array[] = array(
                'id' => $address_format_values['address_format_id'],
                'text' => $address_format_values['address_format_id']
            );
        }
        return $address_format_array;
    }

}
