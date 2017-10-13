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

class Currencies {

    public static function quote_google_currency($to, $from = DEFAULT_CURRENCY) {
        $url = "https://www.google.com/finance/converter?a=1&from=$from&to=$to";
        $request = curl_init();
        $timeOut = 0;
        curl_setopt ($request, CURLOPT_URL, $url);
        curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($request, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt ($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
        $response = curl_exec($request);
        curl_close($request);

        if ( $response && preg_match('#\<span class=bld\>(.+?)\<\/span\>#s',$response, $finalData) ) {
            return preg_replace('/[^\d\.]/','',$finalData[0]);
        }
        return false;
    }
    
    public static function quote_oanda_currency($code, $base = DEFAULT_CURRENCY) {
        $context = stream_context_create(array(
            'http'=>array(
                'method'=>"GET",
                'header'=>
                    "User-Agent: Mozilla/5.0 (0) Gecko/20100101 Firefox/51.0\r\n".
                    "Accept-language: en\r\n" .
                    "Accept: text/javascript, text/html, application/xml, text/xml, */*\r\n".
                    "X-Requested-With: XMLHttpRequest\r\n"
            )
        ));
        $page = @file_get_contents(
            'https://www.oanda.com/currency/converter/update?'.
            'base_currency_0='.$code.'&quote_currency='.$base.
            '&end_date='.date('Y-m-d').'&view=details&id=2&action=C&',
            false, $context
        );
        if ( $page ) {
            $page_data = json_decode($page,true);
            if ( isset($page_data['data']) && isset($page_data['data']['bid_ask_data']) && isset($page_data['data']['bid_ask_data']['bid']) ) {
                return $page_data['data']['bid_ask_data']['bid'];
            }
        }
        return false;
    }

    public static function quote_xe_currency($to, $from = DEFAULT_CURRENCY) {
        $page = file('http://www.xe.com/currencyconverter/convert/?Amount=1&From=' . $from . '&To=' . $to);

        $match = array();

        preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);

        if (sizeof($match) > 0) {
            return $match[1];
        } else {
            return false;
        }
    }

    public static function currency_exists($code) {
        $code = tep_db_prepare_input($code);
        $currency_code = tep_db_query("select currencies_id, code from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "' and status = 1");
        if ($d = tep_db_fetch_array($currency_code)) {
            return $d['code'];
        } else {
            return false;
        }
    }

    public static function getCurrencyId($code) {
        $currency_code = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "'");
        if ($d = tep_db_fetch_array($currency_code)) {
            return $d['currencies_id'];
        } else {
            return false;
        }
    }

    public static function get_currencies($only_code = 0, $default = '') {
        $currencies_array = array();
        if ($default) {
            $currencies_array[] = array('id' => '', 'text' => $default);
        }
        $currencies_query = tep_db_query("select currencies_id, code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES . " where status = 1 order by sort_order, title");
        while ($currency = tep_db_fetch_array($currencies_query)) {
            $currencies_array[] = array(
                'id' => ($only_code < 0 ? $currency['id'] : $currency['code']),
                'text' => ($only_code > 0 ? $currency['code'] : $currency['title'] . ' [' . $currency['code'] . ']'),
                'code' => $currency['code'],
                'currencies_id' => $currency['currencies_id'],
            );
        }
        return $currencies_array;
    }
    
    public static function correctPlatformLanguages(){
        $currencies_query = tep_db_query("select code from " . TABLE_CURRENCIES ." where status = 0");
        if (tep_db_num_rows($currencies_query)){
            $disabled = [];
            while($row = tep_db_fetch_array($currencies_query)) {
                $disabled[] = $row['code'];
            }
            $platforms = tep_db_query("select platform_id, defined_currencies, default_currency from " . TABLE_PLATFORMS);
            if (tep_db_num_rows($platforms)){
                while ($row = tep_db_fetch_array($platforms)){
                    $defined = $row['defined_currencies'];
                    if (!empty($defined)){
                        $curs = explode(",", $defined);
                        $pl_defined = array_diff($curs, $disabled);
                        tep_db_query("update " . TABLE_PLATFORMS . " set defined_currencies = '" . implode(",", $pl_defined) . "' where platform_id = '" . (int)$row['platform_id'] . "'");
                    }
                    $default = $row['default_currency'];
                    if (!empty($default) && in_array($default, $disabled)) {
                        tep_db_query("update " . TABLE_PLATFORMS . " set default_currency = '" . DEFAULT_CURRENCY . "' where platform_id = '" . (int)$row['platform_id'] . "'");
                    }
                }
            }
        }
    }

}
