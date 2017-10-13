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

class Country {

    public static function new_get_countries($default = '', $showDisabled = true) {
        Global $languages_id;
        $countries_array = array();
        if ($default) {
            $countries_array[''] = $default;
        }
        $filter = '';
        if ($showDisabled == false) {
            $filter = ' and status=1';
        }
        $countries_query = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "'" . $filter . " order by countries_name");
        while ($countries = tep_db_fetch_array($countries_query)) {
            $countries_array[$countries['countries_id']] = $countries['countries_name'];
        }

        return $countries_array;
    }

    public static function get_countries($countries_id = '', $with_iso_codes = false, $default = '', $type = '') {
        global $languages_id;
        $countries_array = array();
        $first = 0;
        if ($default) {
            $countries_array[] = array(
                'id' => '',
                'text' => $default
            );
            $first = 1;
        }
        if (tep_not_null($countries_id)) {
            if ($with_iso_codes == true) {
                $countries = tep_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3, lat, lng, zoom from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "' and language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                $countries_values = tep_db_fetch_array($countries);
                $countries_array = array(
                    'id' => $countries_values['countries_id'],
                    'text' => $countries_values['countries_name'],
                    'countries_name' => $countries_values['countries_name'],
                    'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
                    'countries_iso_code_3' => $countries_values['countries_iso_code_3'],
                    'latitude' => $countries_values['lat'],
                    'longitude' => $countries_values['lng'],
                    'zoom' => $countries_values['zoom'],
                );
            } else {
                $countries = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "' and language_id = '" . (int) $languages_id . "' and status=1");
                $countries_values = tep_db_fetch_array($countries);
                $countries_array = array('countries_name' => $countries_values['countries_name']);
            }
        } else {
            $data = \frontend\design\Info::platformData();
            if ($data['country_id']) {
                $countries_array[$first] = array();
            }
            switch ($type) {
                case 'ship':
                    $geo_zones_query = tep_db_query("select z2gz.zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " as z2gz left join " . TABLE_GEO_ZONES . " as gz ON (z2gz.geo_zone_id=gz.geo_zone_id) where gz.shipping_status = '1' group by zone_country_id");
                    $geo_zones_ids = [];
                    while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
                        $geo_zones_ids[] = $geo_zones['zone_country_id'];
                    }
                    if (in_array(0, $geo_zones_ids)) {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    } elseif (count($geo_zones_ids) > 0) {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id IN (" . implode(", ", $geo_zones_ids) . ") and status=1 order by sort_order, countries_name");
                    } else {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id = '" . (int) $data['country_id'] . "' and status=1 order by sort_order, countries_name");
                    }
                    break;
                case 'bill':
                    $geo_zones_query = tep_db_query("select z2gz.zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " as z2gz left join " . TABLE_GEO_ZONES . " as gz ON (z2gz.geo_zone_id=gz.geo_zone_id) where gz.billing_status = '1' group by zone_country_id");
                    $geo_zones_ids = [];
                    while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
                        $geo_zones_ids[] = $geo_zones['zone_country_id'];
                    }
                    if (in_array(0, $geo_zones_ids)) {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    } elseif (count($geo_zones_ids) > 0) {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id IN (" . implode(", ", $geo_zones_ids) . ") and status=1 order by sort_order, countries_name");
                    } else {
                        $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id = '" . (int) $data['country_id'] . "' and status=1 order by sort_order, countries_name");
                    }
                    break;
                default :
                    $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    break;
            }
            while ($countries_values = tep_db_fetch_array($countries)) {
                $country = array(
                    'id' => $countries_values['countries_id'],
                    'text' => $countries_values['countries_name'],
                    'countries_id' => $countries_values['countries_id'],
                    'countries_name' => $countries_values['countries_name']
                );
                if ($data['country_id'] === $countries_values['countries_id']) {
                    $countries_array[$first] = $country;
                } else {
                    $countries_array[] = $country;
                }
            }
        }

        return $countries_array;
    }

    public static function get_country_id($country_name) {

        $country_id_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "'");

        if (!tep_db_num_rows($country_id_query)) {
            return 0;
        } else {
            $country_id_row = tep_db_fetch_array($country_id_query);
            return $country_id_row['countries_id'];
        }
    }

    public static function get_country_name($country_id, $lan_id = 0) {
        global $languages_id;
        if ($lan_id == 0) {
            $lan_id = $languages_id;
        }
        $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $country_id . "' and language_id = '" . (int) $lan_id . "'");
        if (!tep_db_num_rows($country_query)) {
            return $country_id;
        } else {
            $country = tep_db_fetch_array($country_query);
            return $country['countries_name'];
        }
    }

    public static function get_country_info_by_id($country_id) {
        $country_array = self::get_countries($country_id, true);
        return $country_array;
    }

    public static function get_country_info_by_name($country_name, $language_id = '') {
        global $languages_id;
        if ($language_id == '' || $language_id == 0) {
            $language_id = $languages_id;
        }
        $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "' and language_id = '" . (int) $language_id . "'");
        $ret = array();

        if ($d = tep_db_fetch_array($res)) {
            $ret = array('id' => $d['countries_id'],
                'title' => $d['countries_name'],
                'iso_code_2' => $d['countries_iso_code_2'],
                'iso_code_3' => $d['countries_iso_code_3'],
                'zoom' => $d['zoom'],
                'lng' => $d['lng'],
                'lat' => $d['lat']
            );
        } else {
            $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where soundex(countries_name) = soundex('" . tep_db_input($country_name) . "') or countries_iso_code_2 like '" . preg_replace("/\W/", "", tep_db_input($country_name)) . "' or countries_iso_code_3 like '" . preg_replace("/\W/", "", tep_db_input($country_name)) . "'");
            if ($d = tep_db_fetch_array($res)) {
                $ret = array('id' => $d['countries_id'],
                    'title' => $d['countries_name'],
                    'iso_code_2' => $d['countries_iso_code_2'],
                    'iso_code_3' => $d['countries_iso_code_3'],
                    'zoom' => $d['zoom'],
                    'lng' => $d['lng'],
                    'lat' => $d['lat']
                );
            } else {
                $ret = $country_name;
            }
        }


        return $ret;
    }

}
