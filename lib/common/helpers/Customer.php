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

class Customer {

    public static function check_customer_groups($groups_id, $field) {
        static $cached = array();
        if (!isset($cached[(int) $groups_id])) {
            $query = tep_db_query("select * from " . TABLE_GROUPS . " where groups_id = '" . (int) $groups_id . "'");
            $cached[(int) $groups_id] = tep_db_fetch_array($query);
        }
        return $cached[(int) $groups_id][$field];
    }

    public static function count_customer_address_book_entries($id = '', $check_session = true) {
        global $customer_id;

        if (is_numeric($id) == false) {
            if (tep_session_is_registered('customer_id')) {
                $id = $customer_id;
            } else {
                return 0;
            }
        }

        if ($check_session == true) {
            if ((tep_session_is_registered('customer_id') == false) || ($id != $customer_id)) {
                return 0;
            }
        }

        $addresses_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $id . "'");
        $addresses = tep_db_fetch_array($addresses_query);

        return $addresses['total'];
    }

    public static function is_customer_exist($customer_id) {
        if (tep_db_num_rows(tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_id='" . (int) $customer_id . "'")) > 0) {
            return true;
        }
        return false;
    }

    public static function count_customer_orders($id = '', $check_session = true) {
        global $customer_id;

        if (is_numeric($id) == false) {
            if (tep_session_is_registered('customer_id')) {
                $id = $customer_id;
            } else {
                return 0;
            }
        }

        if ($check_session == true) {
            if ((tep_session_is_registered('customer_id') == false) || ($id != $customer_id)) {
                return 0;
            }
        }

        $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where customers_id = '" . (int) $id . "'");
        $orders_check = tep_db_fetch_array($orders_check_query);

        return $orders_check['total'];
    }
    
    public static function get_customers_group($customer_id) {
        if (CUSTOMERS_GROUPS_ENABLE == 'True') {
            $check = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . tep_db_input($customer_id) . "'");
            $checkData = tep_db_fetch_array($check);
            return $checkData['groups_id'];
        } else {
            return 0;
        }
    }
    
    public static function get_additional_fields_tree($customer_id = 0) {
        $additionalFields = [];
        
        $group_query = tep_db_query("SELECT fgd.* FROM " . TABLE_ADDITIONAL_FIELDS_GROUP . " fg INNER JOIN " . TABLE_ADDITIONAL_FIELDS_GROUP_DESCRIPTION . " fgd ON fgd.additional_fields_group_id=fg.additional_fields_group_id WHERE fgd.language_id = '" . 1 . "' order by fg.sort_order, fgd.title");
        while ($group = tep_db_fetch_array($group_query)) {

            $child = [];
            $fields_query = tep_db_query("SELECT afd.*, af.additional_fields_code, af.field_type FROM " . TABLE_ADDITIONAL_FIELDS . " af INNER JOIN " . TABLE_ADDITIONAL_FIELDS_DESCRIPTION . " afd ON afd.additional_fields_id=af.additional_fields_id WHERE additional_fields_group_id = '" . $group['additional_fields_group_id'] . "' AND afd.language_id = '" . 1 . "' order by af.sort_order, afd.title");
            while ($fields = tep_db_fetch_array($fields_query)) {
                $value = '';
                $check_query = tep_db_query("SELECT value FROM " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " WHERE customers_id = '" . (int)$customer_id . "' AND additional_fields_id = '" . (int)$fields['additional_fields_id'] . "'");
                if ($check = tep_db_fetch_array($check_query)) {
                    $value = $check['value'];
                }
                $child[] = [
                    'id' =>  $fields['additional_fields_id'],
                    'name' => $fields['title'],
                    'code' => $fields['additional_fields_code'],
                    'field_type' => $fields['field_type'],
                    'value' => $value,
                ];
            }
            
            if (count($child) > 0) {
                $additionalFields[] = [
                    'name' => $group['title'],
                    'child' => $child,
                ];
            }
        }
        
        return $additionalFields;
    }

    public static function get_additional_fields($customer_id = 0) {
        $additionalFields = [];
        
        $fields_query = tep_db_query("SELECT * FROM " . TABLE_ADDITIONAL_FIELDS . " WHERE 1");
        while ($fields = tep_db_fetch_array($fields_query)) {
            $value = '';
            $check_query = tep_db_query("SELECT value FROM " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " WHERE customers_id = '" . (int)$customer_id . "' AND additional_fields_id = '" . (int)$fields['additional_fields_id'] . "'");
            if ($check = tep_db_fetch_array($check_query)) {
                $value = $check['value'];
            }
            $additionalFields[$fields['additional_fields_code']] = $value;
        }
        
        return $additionalFields;
    }

    public  static function get_address_book_data($customer_id = 0){
        global $languages_id;
        $addresses = array();

        $query = tep_db_query("
            select 
                a.address_book_id as id,
                a.entry_gender as gender,
                a.entry_company as company,
                a.entry_firstname as firstname,
                a.entry_lastname as lastname,
                a.entry_street_address as street_address,
                a.entry_suburb as suburb,
                a.entry_postcode as postcode,
                a.entry_city as city,
                if (a.entry_zone_id, z.zone_name, a.entry_state) as state,
                a.entry_zone_id as zone_id,
                a.entry_company_vat as company_vat,
                a.entry_country_id as country_id,
                c.countries_name as country
            from
                " . TABLE_ADDRESS_BOOK . " a left join " . TABLE_ZONES . " z on a.entry_zone_id = z.zone_id,
                " . TABLE_COUNTRIES . " c 
            where
                a.entry_country_id = c.countries_id and
                c.language_id = '" . $languages_id . "' and 
                a.customers_id = '" . $customer_id . "'
        ");
        while ($item = tep_db_fetch_array($query)){
            $addresses[] = $item;
        }
        
        return $addresses;
    }
    
    public static function getCustomerData($id){
        $_details = tep_db_fetch_array(tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$id . "'"));
        return $_details;
    }
}
