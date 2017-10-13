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

class Zones {

    public static function get_zone_class_title($zone_class_id) {
        if ($zone_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select geo_zone_name from " . TABLE_TAX_ZONES . " where geo_zone_id = '" . (int) $zone_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);
            return $classes['geo_zone_name'];
        }
    }
    
    public static function get_geo_zone_class_title($zone_class_id) {
        if ($zone_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select geo_zone_name from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int) $zone_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);
            return $classes['geo_zone_name'];
        }
    }

    public static function get_zone_code($country_id, $zone_id, $default_zone) {
        $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' and zone_id = '" . (int) $zone_id . "'");
        if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);
            return $zone['zone_code'];
        } else {
            return $default_zone;
        }
    }

    public static function get_zone_name($country_id, $zone_id, $default_zone) {
        $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' and zone_id = '" . (int) $zone_id . "'");
        if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);
            return $zone['zone_name'];
        } else {
            return $default_zone;
        }
    }

    public static function get_ship_options_name($ship_options_id, $language_id = '') {
        global $languages_id;
        if (!is_numeric($language_id))
            $language_id = $languages_id;
        $status_query = tep_db_query("select ship_options_name from " . TABLE_SHIP_OPTIONS . " where ship_options_id = '" . (int) $ship_options_id . "' and language_id = '" . (int) $language_id . "'");
        $status = tep_db_fetch_array($status_query);
        return $status['ship_options_name'];
    }

    public static function get_country_zones($country_id) {
        $zones_array = array();
        $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' order by zone_name");
        while ($zones = tep_db_fetch_array($zones_query)) {
            $zones_array[] = array('id' => $zones['zone_id'],
                'text' => $zones['zone_name']);
        }

        return $zones_array;
    }

    public static function prepare_country_zones_pull_down($country_id = '') {
        // preset the width of the drop-down for Netscape
        $pre = '';
        if ((!\common\helpers\System::browser_detect('MSIE')) && (\common\helpers\System::browser_detect('Mozilla/4'))) {
            for ($i = 0; $i < 45; $i++)
                $pre .= '&nbsp;';
        }

        $zones = self::get_country_zones($country_id);

        if (sizeof($zones) > 0) {
            $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
            $zones = array_merge($zones_select, $zones);
        } else {
            $zones = array(array('id' => '', 'text' => TYPE_BELOW));
            // create dummy options for Netscape to preset the height of the drop-down
            if ((!\common\helpers\System::browser_detect('MSIE')) && (\common\helpers\System::browser_detect('Mozilla/4'))) {
                for ($i = 0; $i < 9; $i++) {
                    $zones[] = array('id' => '', 'text' => $pre);
                }
            }
        }

        return $zones;
    }

    public static function get_zone_id($country_id, $zone_name) {

        $zone_id_query = tep_db_query("select * from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' and zone_name = '" . tep_db_input($zone_name) . "'");

        if (!tep_db_num_rows($zone_id_query)) {
            return 0;
        } else {
            $zone_id_row = tep_db_fetch_array($zone_id_query);
            return $zone_id_row['zone_id'];
        }
    }

    public static function ship_zones_pull_down($parameters, $selected = '', $platform_id = 0) {
        $select_string = '<select ' . $parameters . '>';
        $zones_query = tep_db_query("select ship_zone_id, ship_zone_name from " . TABLE_SHIP_ZONES . " where platform_id='" . (int)$platform_id . "' order by ship_zone_name");
        while ($zones = tep_db_fetch_array($zones_query)) {
            $select_string .= '<option value="' . $zones['ship_zone_id'] . '"';
            if ($selected == $zones['ship_zone_id'])
                $select_string .= ' SELECTED';
            $select_string .= '>' . $zones['ship_zone_name'] . '</option>';
        }
        $select_string .= '</select>';

        return $select_string;
    }

    public static function geo_zones_pull_down($parameters, $selected = '') {
        $select_string = '<select ' . $parameters . '>';
        $zones_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_TAX_ZONES . " order by geo_zone_name");
        while ($zones = tep_db_fetch_array($zones_query)) {
            $select_string .= '<option value="' . $zones['geo_zone_id'] . '"';
            if ($selected == $zones['geo_zone_id'])
                $select_string .= ' SELECTED';
            $select_string .= '>' . $zones['geo_zone_name'] . '</option>';
        }
        $select_string .= '</select>';

        return $select_string;
    }

    public static function stick_shipping_rates($rates_array) {
        $check_secret = preg_split('/[:;]/', $rates_array[0]);
        if (sizeof($check_secret) > 2) {
            return str_replace(',', '.', $rates_array[0]);
        }

        for ($i = 0; $i < sizeof($rates_array); $i+=2) {
            if ((float) $rates_array[$i] > 0) {
                $output .= (float) str_replace(',', '.', $rates_array[$i]) . ':' . (float) str_replace(',', '.', $rates_array[$i + 1]) . ';';
            }
        }
        return $output;
    }

}
