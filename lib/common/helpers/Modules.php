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

class Modules {

    public static function count_modules($modules = '') {
        $count = 0;

        if (empty($modules))
            return $count;

        $modules_array = explode(';', $modules);

        for ($i = 0, $n = sizeof($modules_array); $i < $n; $i++) {
            $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

            if (is_object($GLOBALS[$class])) {
                if ($GLOBALS[$class]->enabled) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public static function count_payment_modules() {
        return self::count_modules(MODULE_PAYMENT_INSTALLED);
    }

    public static function count_shipping_modules() {
        return self::count_modules(MODULE_SHIPPING_INSTALLED);
    }

}
