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

class Manufacturers {

    public static function get_manufacturers($manufacturers_array = '') {
        if (!is_array($manufacturers_array))
            $manufacturers_array = array();

        $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
        }

        return $manufacturers_array;
    }

    public static function get_manufacturer_info($field, $manufacturers_id) {
        $manufacturers_query = tep_db_fetch_array(tep_db_query("select {$field} from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'"));
        return $manufacturers_query[$field];
    }
    
    public static function get_manufacturer_meta_descr($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_description from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_description'];
    }

    public static function get_manufacturer_meta_key($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_key from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_key'];
    }

    public static function get_manufacturer_meta_title($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_title from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_title'];
    }

    public static function get_manufacturer_seo_name($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_seo_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_seo_name'];
    }

    public static function get_manufacturer_url($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_url'];
    }

}
