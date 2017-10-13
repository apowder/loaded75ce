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

class Properties {

    public static function is_property_in_array($properties_id, $properties_array) {
        if (in_array($properties_id, $properties_array)) {
            return true;
        } else {
            $properties_query = tep_db_query("select properties_id from " . TABLE_PROPERTIES . " where parent_id = '" . (int) $properties_id . "'");
            if (tep_db_num_rows($properties_query) > 0) {
                while ($properties = tep_db_fetch_array($properties_query)) {
                    if (self::is_property_in_array($properties['properties_id'], $properties_array)) {
                        return true;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public static function generate_properties_tree($parent_id = '0', $properties_array = array(), $values_array = array(), $properties_tree_array = '', $throughoutID = '') {
        global $languages_id;
        if (!is_array($properties_tree_array))
            $properties_tree_array = array();
        $properties_query = tep_db_query("select p.properties_id, p.properties_type, p.decimals, p.parent_id, pd.properties_name, pd.properties_image, pu.properties_units_title from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd left join " . TABLE_PROPERTIES_UNITS . " pu on pu.properties_units_id = pd.properties_units_id where p.parent_id = '" . (int) $parent_id . "' and p.properties_id = pd.properties_id and pd.language_id = '" . (int) $languages_id . "' order by (p.properties_type = 'category'), p.sort_order, pd.properties_name");
        $sort = 1;
        while ($properties = tep_db_fetch_array($properties_query)) {
            if (/* count($properties_array) == 0 || */self::is_property_in_array($properties['properties_id'], $properties_array)) {

                $values = array();
                if (is_array($values_array[$properties['properties_id']])) {
                    if ($properties['properties_type'] == 'flag') {
                        if ($values_array[$properties['properties_id']][0]) {
                            $values[1] = TEXT_YES;
                        } else {
                            $values[0] = TEXT_NO;
                        }
                    } else {
                        $properties_values_query = tep_db_query("select values_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int) $properties['properties_id'] . "' and values_id in ('" . implode("','", $values_array[$properties['properties_id']]) . "') and language_id = '" . (int) $languages_id . "' order by " . ($properties['properties_type'] == 'number' || $properties['properties_type'] == 'interval' ? 'values_number' : 'values_text'));
                        while ($properties_values = tep_db_fetch_array($properties_values_query)) {
                            if ($properties['properties_type'] == 'interval') {
                                $properties_values['values'] = number_format($properties_values['values_number'], $properties['decimals'], '.', '') . ' - ' . number_format($properties_values['values_number_upto'], $properties['decimals'], '.', '');
                            } elseif ($properties['properties_type'] == 'number') {
                                $properties_values['values'] = number_format($properties_values['values_number'], $properties['decimals'], '.', '');
                            } else {
                                $properties_values['values'] = $properties_values['values_text'];
                            }
                            $values[$properties_values['values_id']] = $properties_values['values'];
                        }
                    }
                }

                $temp_throughoutID = $throughoutID;
                if ($temp_throughoutID != '') {
                    $temp_throughoutID .= '.';
                }
                $temp_throughoutID .= $sort;
                $properties_tree_array[$properties['properties_id']] = array(
                            'properties_id' => $properties['properties_id'],
                            'properties_name' => $properties['properties_name'] . (tep_not_null($properties['properties_units_title']) ? ' (' . $properties['properties_units_title'] . ')' : ''),
                            'properties_image' => $properties['properties_image'],
                            'properties_type' => $properties['properties_type'],
                            'parent_id' => $parent_id,
                            'throughoutID' => $temp_throughoutID,
                            'values' => $values
                );
                $properties_tree_array = self::generate_properties_tree($properties['properties_id'], $properties_array, $values_array, $properties_tree_array, $temp_throughoutID);
                $sort++;
            }
        }
        return $properties_tree_array;
    }

    public static function get_properties_name($properties_id, $language_id) {
        $data = tep_db_fetch_array(tep_db_query("select properties_name from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $language_id . "'"));
        return $data['properties_name'];
    }

    public static function get_properties_description($properties_id, $language_id) {
        $data = tep_db_fetch_array(tep_db_query("select properties_description from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $language_id . "'"));
        return $data['properties_description'];
    }

    public static function get_properties_image($properties_id, $language_id) {
        $data = tep_db_fetch_array(tep_db_query("select properties_image from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $language_id . "'"));
        return $data['properties_image'];
    }

    public static function get_properties_units_title($properties_id, $language_id) {
        $data = tep_db_fetch_array(tep_db_query("select pu.properties_units_title from " . TABLE_PROPERTIES_DESCRIPTION . " pd left join " . TABLE_PROPERTIES_UNITS . " pu on pu.properties_units_id = pd.properties_units_id where pd.properties_id = '" . (int) $properties_id . "' and pd.language_id = '" . (int) $language_id . "'"));
        return $data['properties_units_title'];
    }

    public static function get_properties_seo_page_name($properties_id, $language_id) {
        $data = tep_db_fetch_array(tep_db_query("select properties_seo_page_name from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $language_id . "'"));
        return $data['properties_seo_page_name'];
    }

    public static function remove_property($properties_id) {
        $child_properties_query = tep_db_query("select properties_id from " . TABLE_PROPERTIES . " where parent_id = '" . (int) $properties_id . "'");
        while ($child_properties = tep_db_fetch_array($child_properties_query)) {
            self::remove_property($child_properties['properties_id']);
        }
        tep_db_query("delete from " . TABLE_PROPERTIES . " where properties_id = '" . (int) $properties_id . "'");
        tep_db_query("delete from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int) $properties_id . "'");
        tep_db_query("delete from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int) $properties_id . "'");
        tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where properties_id = '" . (int) $properties_id . "'");
    }

    public static function get_properties_tree($parent_id = '0', $spacing = '', $properties_tree_array = '', $categories_only = true) {
        global $languages_id;

        if (!is_array($properties_tree_array))
            $properties_tree_array = array();
        if ((sizeof($properties_tree_array) < 1))
            $properties_tree_array[] = array('id' => '0', 'text' => TEXT_TOP, 'type' => 'category');

        $properties_query = tep_db_query("select p.properties_id, pd.properties_name, p.parent_id, p.properties_type from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd where p.properties_id = pd.properties_id and pd.language_id = '" . (int) $languages_id . "' and p.parent_id = '" . (int) $parent_id . "'" . ($categories_only ? " and p.properties_type = 'category'" : '') . " order by (p.properties_type = 'category'), p.sort_order, pd.properties_name");
        while ($properties = tep_db_fetch_array($properties_query)) {
            $properties_tree_array[] = array('id' => $properties['properties_id'], 'text' => $spacing . $properties['properties_name'], 'type' => $properties['properties_type']);
            $properties_tree_array = self::get_properties_tree($properties['properties_id'], $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;', $properties_tree_array, $categories_only);
        }

        return $properties_tree_array;
    }

}
