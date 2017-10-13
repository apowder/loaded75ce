<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

function tep_redirect($url) {
    global $logger;
    if ((strstr($url, "\n") != false) || (strstr($url, "\r") != false)) {
        tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }
    header('Location: ' . $url);
    if (STORE_PAGE_PARSE_TIME == 'true') {
        if (!is_object($logger))
            $logger = new logger;
        $logger->timer_stop();
    }
    exit;
}

function tep_not_null($value) {
    if (is_array($value)) {
        if (sizeof($value) > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        if ((is_string($value) || is_int($value) || is_float($value) || is_bool($value) ) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
            return true;
        } else {
            return false;
        }
    }
}

function tep_admin_check_login() {
    global $navigation, $login_id;
    if (!tep_session_is_registered('login_id')) {
        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
            $navigation->set_snapshot();
        }  
        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}

function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
        return call_user_func($function, $parameter);
    } else {
        return call_user_func(array($object, $function), $parameter);
    }
}

function convert($input){
    return \common\helpers\Seo::transliterate($input);
}  
