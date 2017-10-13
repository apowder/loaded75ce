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

class Language
{
    public static function get_language_code($id) {
        return tep_db_fetch_array(tep_db_query("select LOWER(code) as code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$id . "'"));
    }
    
    public static function get_language_id($code) {
        return tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($code) . "'"));
    }
    
    public static function get_default_language_id() {
        static $_cached = false;
        if ($_cached === false) {
            $get_id_arr = tep_db_fetch_array(tep_db_query("SELECT languages_id FROM " . TABLE_LANGUAGES . " WHERE code='" . DEFAULT_LANGUAGE . "'"));
            $_cached = is_array($get_id_arr) ? $get_id_arr['languages_id'] : $_SESSION['languages_id'];
        }
        return $_cached;
    }
    
    public static function get_languages($all = false) {
        $_def_id = self::get_default_language_id();
        if ($all) {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where 1 order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        } else {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where languages_status = '1' order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        }
        $languages_array = array();
        $_new = array();
        while ($languages = tep_db_fetch_array($languages_query)) {
            $_tmp = array('id' => $languages['languages_id'],
                'name' => $languages['name'],
                'code' => strtolower($languages['code']),
                'image' => tep_image(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['image'], $languages['name'], '24', '16', 'class="language-icon"'),
                'image_svg' => tep_image(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['image_svg'], $languages['name']),
                'locale' => $languages['locale'],
                'shown_language' => $languages['shown_language'],
                'searchable_language' => $languages['searchable_language'],
                'directory' => $languages['directory']);
            if ($languages['languages_id'] == $_def_id) {
                $_new[] = $_tmp;
            } else {
                $languages_array[] = $_tmp;
            }
        }
        $languages_array = array_merge($_new, $languages_array);

        return $languages_array;
    }

    public static function pull_languages() {
        $languages = self::get_languages();
        $lang = array();
        foreach ($languages as $item) {
            $lang[] = array('id' => $item['code'], 'text' => $item['directory']);
        }
        return $lang;
    }

}
