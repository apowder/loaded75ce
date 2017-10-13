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

class Group {

    public static function get_customer_groups() {
        static $groups = false;
        if (!is_array($groups)) {
            $groups = array();
            $get_groups_r = tep_db_query("SELECT * FROM " . TABLE_GROUPS . " ORDER BY groups_name");
            if (tep_db_num_rows($get_groups_r) > 0) {
                while ($get_group = tep_db_fetch_array($get_groups_r)) {
                    $groups[] = $get_group;
                }
            }
        }
        return $groups;
    }

    public static function get_user_group_name($Value) {
        if ($Value == 0) {
            return TEXT_NONE;
        } else {
            $status = tep_db_fetch_array(tep_db_query("select * from " . TABLE_GROUPS . " where groups_id = '" . $Value . "'"));
            return $status['groups_name'];
        }
    }

}
