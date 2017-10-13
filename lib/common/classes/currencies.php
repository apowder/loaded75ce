<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;
use \frontend\design\Info;

  class currencies {
    var $currencies;
    var $platform_currencies = [];
    var $dp_currency;

// class constructor
    function __construct() {
      $this->currencies = array();
      
      if (!Info::isTotallyAdmin()){
        $this->platform_currencies = Info::platformCurrencies();
        $this->dp_currency = \frontend\design\Info::platformDefCurrency();
        if (!is_array($this->platform_currencies) || count($this->platform_currencies) == 0){
          $this->platform_currencies = array(DEFAULT_CURRENCY);
          $this->dp_currency = DEFAULT_CURRENCY;
        }
      } else {
        $this->dp_currency = DEFAULT_CURRENCY;
        $this->platform_currencies = array(DEFAULT_CURRENCY);
      }
      
      $currencies_query = tep_db_query("select currencies_id, code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES . " where status = 1 order by sort_order, title");
      while ($currencies = tep_db_fetch_array($currencies_query)) {

        $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                                                       'id' => $currencies['currencies_id'],
                                                       'code' => $currencies['code'],
                                                       'symbol_left' => $currencies['symbol_left'],
                                                       'symbol_right' => $currencies['symbol_right'],
                                                       'decimal_point' => $currencies['decimal_point'],
                                                       'thousands_point' => $currencies['thousands_point'],
                                                       'decimal_places' => (int)$currencies['decimal_places'],
                                                       'value' => $currencies['value']);
      }
      
      if (USE_MARKET_PRICES == 'True') {
        global $currency;
        
        if (empty($currency)) $currency = DEFAULT_CURRENCY;
        
        $currency_value = $this->currencies[$currency]['value'];
        foreach ($this->currencies as $code => $curr) {
          $this->currencies[$code]['value'] /= $currency_value;
        }
      }
    }

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '', $microdata = false) {
      global $currency;
	  
	  if (\frontend\design\Info::isTotallyAdmin() && is_null($currency)) $currency = DEFAULT_CURRENCY;

      if (empty($currency_type)) $currency_type = $currency;

      $format_string = '';

      if ($this->currencies[$currency_type]['symbol_left']) {
        if ($microdata) {
          $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_left'] . '</span>';
        } else {
          $format_string .= $this->currencies[$currency_type]['symbol_left'];
        }
      }

      if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string .=
          ($microdata ? '<span itemprop="price" content="' . number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), 2, '.', '') . '">' : '') .
          number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']).
          ($microdata ? '</span>' : '');
// if the selected currency is in the european euro-conversion and the default currency is euro,
// the currency will displayed in the national currency and euro currency
        if ( (DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD') ) {
          $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
        }
      } else {
        $format_string .= number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
      }

      if ($this->currencies[$currency_type]['symbol_right']) {
        if ($microdata) {
          $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_right'] . '</span>';
        } else {
          $format_string .= $this->currencies[$currency_type]['symbol_right'];
        }
      }

// BOF: WebMakers.com Added: Down for Maintenance
      if (DOWN_FOR_MAINTENANCE=='true' && DOWN_FOR_MAINTENANCE_PRICES_OFF=='true') {
        $format_string= '';
      }
// BOF: WebMakers.com Added: Down for Maintenance

        return $format_string;
    }

    function format_clear($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '', $unclear = false) {
      global $currency;
	  
	  if (\frontend\design\Info::isTotallyAdmin() && is_null($currency)) $currency = DEFAULT_CURRENCY;
	  
      if (empty($currency_type)) $currency_type = $currency;
	  
      if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = number_format(round(($unclear?($number / $rate):($number * $rate)), $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
      } else {
        $format_string = number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');

      }
      if (DOWN_FOR_MAINTENANCE=='true' && DOWN_FOR_MAINTENANCE_PRICES_OFF=='true') {
        $format_string= '';
      }
      return $format_string;
    }

    function is_set($code) {
      if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
        return true;
      } else {
        return false;
      }
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }

    function display_price($products_price, $products_tax, $quantity = 1, $microdata = false) {
      if ($products_price === false){
        return '';
      }else{
        return $this->format(\common\helpers\Tax::add_tax($products_price, $products_tax) * $quantity, true, '', '', $microdata);
      }
    }

    function display_price_clear($products_price, $products_tax, $quantity = 1) {
      if ($products_price === false){
        return '';
      }else{
        return $this->format_clear(\common\helpers\Tax::add_tax($products_price, $products_tax) * $quantity);
      }
    }

    function display_gift_card_price($products_price, $products_tax, $gift_card_currency = '') {
      global $currency;
      if (tep_not_null($gift_card_currency) && \common\helpers\Currencies::currency_exists($gift_card_currency)) {
        $old_currency = $currency;
        $currency = $gift_card_currency;
      }
      $old_decimal_places = $this->currencies[$currency]['decimal_places'];
      $this->currencies[$currency]['decimal_places'] = 0;
      $return = $this->format(\common\helpers\Tax::add_tax($products_price, $products_tax));
      $this->currencies[$currency]['decimal_places'] = $old_decimal_places;
      if (tep_not_null($gift_card_currency) && \common\helpers\Currencies::currency_exists($gift_card_currency)) {
        $currency = $old_currency;
      }
      return $return;
    }

    function get_market_price_rate($from_currency, $to_currency) {
      $div = $this->get_value($from_currency);
      if (!$div) $div = 1;
      return  $this->get_value($to_currency) / $div;
    }
  }
?>
