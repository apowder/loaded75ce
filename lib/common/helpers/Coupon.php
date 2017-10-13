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

class Coupon {

    public static function get_coupon_name($cc_id) {
        $coupon = tep_db_fetch_array(tep_db_query("select coupon_code from " . TABLE_COUPONS . " where coupon_id = '" . $cc_id . "'"));
        return $coupon['coupon_code'];
    }

}
