<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

////
// Class to handle currencies
// TABLES: currencies
  class currencies {
    var $currencies;

// class constructor
    function __construct() {
      $this->currencies = array();
      $currencies_query = tep_db_query("select currencies_id, code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES . " order by sort_order, title");
      while ($currencies = tep_db_fetch_array($currencies_query)) {
	    $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                                                       'id' => $currencies['currencies_id'],
                                                       'symbol_left' => $currencies['symbol_left'],
                                                       'symbol_right' => $currencies['symbol_right'],
                                                       'decimal_point' => $currencies['decimal_point'],
                                                       'thousands_point' => $currencies['thousands_point'],
                                                       'decimal_places' => (int)$currencies['decimal_places'],
                                                       'value' => $currencies['value']);
      }
      if (USE_MARKET_PRICES == 'True') {
        global $currency;
        if (empty($currency)) 
          $currency = DEFAULT_CURRENCY;
        $currency_value = $this->currencies[$currency]['value'];
        foreach ($this->currencies as $code => $curr) {
          $this->currencies[$code]['value'] /= $currency_value;
        }
      }
    }

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '', $round_value = false) {
      if (isset($_SESSION['currency']) && tep_not_null($_SESSION['currency']) && $_SESSION['currency'] != $currency_type) $currency_type = $_SESSION['currency'];
      if ($round_value){
        $number = round($number, $this->currencies[$currency_type]['decimal_places']);
      }
      if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
        $rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
// if the selected currency is in the european euro-conversion and the default currency is euro,
// the currency will displayed in the national currency and euro currency
        if ( (DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD') ) {
          $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
        }
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }

      return $format_string;
    }

    function format_clear($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '', $round_values = false, $unclear = false) {      
      if ($round_value){
        $number = round($number, $this->currencies[$currency_type]['decimal_places']);
      }      
      if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = number_format(round(($unclear?($number / $rate):($number * $rate)), $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
      } else {
        $format_string = number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
      }
      return $format_string;
    }    

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format(\common\helpers\Tax::add_tax($products_price, $products_tax) * $quantity);
    }
    
    function get_market_price_rate($from_currency, $to_currency) {
      return  $this->get_value($to_currency) / ($c = $this->get_value($from_currency)? $c : 1);
    }
    
  }
