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

class Date
{

    const DATE_FORMAT = 'j M Y';
    const DATE_TIME_FORMAT = 'j M Y, g:i a';
    const CALENDAR_DATE_FORMAT = 'j M Y'; // d-m-Y j M Y
    const DATABASE_DATE_FORMAT = 'Y-m-d';
    const JS_DATE_FORMAT = 'D MMM YYYY'; // DD-MM-YYYY D MMM YYYY

    public static function formatDate($date) {
        if ($date == '0000-00-00' || empty($date)) {
            return '';
        }
        return date(self::DATE_FORMAT, strtotime($date));
    }

    public static function formatDateTime($date) {
        if ($date == '0000-00-00 00:00:00' || empty($date)) {
            return '';
        }
        return date(self::DATE_TIME_FORMAT, strtotime($date));
    }

    public static function formatCalendarDate($date) {
        if ($date == '0000-00-00' || empty($date)) {
            return '';
        }
        return date(self::CALENDAR_DATE_FORMAT, strtotime($date));
    }

    public static function unformatCalendarDate($date) {
        if ($date == '0000-00-00' || empty($date)) {
            return '';
        }
        return date(self::DATABASE_DATE_FORMAT, strtotime($date));
    }

    public static function getDateRange($start_date, $end_date) {
        if ($start_date == '0000-00-00' || empty($start_date)) {
            return '';
        }
        if ($end_date == '0000-00-00' || empty($end_date)) {
            return '';
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));

        $datetime1 = new \DateTime($start_date);
        $datetime2 = new \DateTime($end_date);
        $difference = $datetime1->diff($datetime2);

        $response = '';

        if ($difference->y == 1) {
            $response .= $difference->y . ' year ';
        } elseif ($difference->y > 1) {
            $response .= $difference->y . ' years ';
        }

        if ($difference->m == 1) {
            $response .= $difference->m . ' ' . TEXT_MONTH_COMMON . ' ';
        } elseif ($difference->m > 1) {
            $response .= $difference->m . ' ' . TEXT_MONTHS_COMMON . ' ';
        }

        if ($difference->d == 1) {
            $response .= '1 ' . TEXT_DAY_COMMON;
        } elseif ($difference->d == 7) {
            $response .= '1 ' . TEXT_WEEK_COMMON;
        } elseif ($difference->d == 14) {
            $response .= '2 ' . TEXT_WEEKS_COMMON;
        } elseif ($difference->d == 21) {
            $response .= '3 ' . TEXT_WEEKS_COMMON;
        } elseif ($difference->d == 28) {
            $response .= '4 ' . TEXT_WEEKS_COMMON;
        } elseif ($difference->d > 1) {
            $response .= $difference->d . ' ' . TEXT_DAYS_COMMON;
        }

        if ($difference->invert == 1) {
            $response .= ' ' . TEXT_AGO_COMMON;
        }

        if (empty($response)) {
            $response = TEXT_TODAY_COMMON;
        }

        return $response;
    }

    public static function date_long($raw_date, $format = DATE_FORMAT_LONG) {
        if (($raw_date == '0000-00-00 00:00:00') || ($raw_date == ''))
            return false;

        $year = (int) substr($raw_date, 0, 4);
        $month = (int) substr($raw_date, 5, 2);
        $day = (int) substr($raw_date, 8, 2);
        $hour = (int) substr($raw_date, 11, 2);
        $minute = (int) substr($raw_date, 14, 2);
        $second = (int) substr($raw_date, 17, 2);

        return strftime($format, mktime($hour, $minute, $second, $month, $day, $year));
    }

    public static function date_short($raw_date) {
        if (($raw_date == '0000-00-00 00:00:00') || ($raw_date == ''))
            return false;

        $year = substr($raw_date, 0, 4);
        $month = (int) substr($raw_date, 5, 2);
        $day = (int) substr($raw_date, 8, 2);
        $hour = (int) substr($raw_date, 11, 2);
        $minute = (int) substr($raw_date, 14, 2);
        $second = (int) substr($raw_date, 17, 2);

        return strftime(DATE_FORMAT_SHORT, mktime($hour, $minute, $second, $month, $day, $year));
    }

    public static function datetime_short($raw_datetime) {
        if (($raw_datetime == '0000-00-00 00:00:00') || ($raw_datetime == ''))
            return false;

        $year = (int) substr($raw_datetime, 0, 4);
        $month = (int) substr($raw_datetime, 5, 2);
        $day = (int) substr($raw_datetime, 8, 2);
        $hour = (int) substr($raw_datetime, 11, 2);
        $minute = (int) substr($raw_datetime, 14, 2);
        $second = (int) substr($raw_datetime, 17, 2);

        return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    }

    public static function date_raw($date, $reverse = false) {
        if ($reverse) {
            return substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 4);
        } else {
            return date("Y-m-d H:i:s", strtotime($date));
            //return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
        }
    }

    public static function date_format($raw_date, $format) {
        if (($raw_date == '0000-00-00 00:00:00') || ($raw_date == ''))
            return false;

        $year = (int) substr($raw_date, 0, 4);
        $month = (int) substr($raw_date, 5, 2);
        $day = (int) substr($raw_date, 8, 2);
        $hour = (int) substr($raw_date, 11, 2);
        $minute = (int) substr($raw_date, 14, 2);
        $second = (int) substr($raw_date, 17, 2);

        return strftime($format, mktime($hour, $minute, $second, $month, $day, $year));
    }

    public static function datepicker_date($date) {
        if (($date == '0000-00-00 00:00:00') || ($date == '0000-00-00') || ($date == ''))
            return false;
        return date(DATE_FORMAT_DATEPICKER_PHP, strtotime($date));
    }

    static public function getDefaultServerTimeZone()
    {
        return defined('TIMEZONE_SERVER')?TIMEZONE_SERVER:'Europe/London';
    }
    
    static public function setServerTimeZone( $new_zone='' )
    {
        if ( !empty($new_zone) ) {
            $status = date_default_timezone_set($new_zone);
            if ( $status===false ) return;
        }
        $can_db_date_set = true;
        /*
        $db_ver = tep_db_fetch_array(tep_db_query("select version() as v"));
        if ( preg_match('/^(\d+)\.(\d+)/',$db_ver['v'], $db_ver) ) {
            $can_db_date_set = $db_ver[1]>=5;
        }
        */
        if ( $can_db_date_set ) {
          $php_tz = date('O');
          $php_tz = substr($php_tz, 0, -2).':'.substr($php_tz, -2);
          tep_db_query("SET SESSION time_zone = '".$php_tz."'");
        }
    }
}
