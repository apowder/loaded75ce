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

class Configuration {

    public static function get_configuration_key_value($lookup) {
        $configuration_query_raw = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $lookup . "'");
        $configuration_query = tep_db_fetch_array($configuration_query_raw);
        $lookup_value = $configuration_query['configuration_value'];
        return $lookup_value;
    }

}
