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

class Configurator {

    public static function elements_name($elements_id, $language_id = '') {
        global $languages_id;

        if (!$language_id) {
            $language_id = $languages_id;
        }

        $elements_query = tep_db_query("select elements_name from " . TABLE_ELEMENTS . " where elements_id = '" . (int)$elements_id . "' and language_id = '" . (int)$language_id . "'");
        $elements = tep_db_fetch_array($elements_query);

        return $elements['elements_name'];
    }

    public static function pctemplates_description($pctemplates_id, $language_id = '') {
        global $languages_id;

        if (!$language_id) {
            $language_id = $languages_id;
        }

        $pctemplates_query = tep_db_query("select pctemplates_description from " . TABLE_PCTEMPLATES_INFO . " where pctemplates_id = '" . (int)$pctemplates_id . "' and languages_id = '" . (int)$language_id . "'");
        $pctemplates = tep_db_fetch_array($pctemplates_query);

        return $pctemplates['pctemplates_description'];
    }

}
