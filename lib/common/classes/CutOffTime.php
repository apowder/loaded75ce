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

/**
 * CutOffTime object
 */
class CutOffTime {

    /**
     * Data [platform_id][day_of_week] = time
     */
    public $today = [];
    public $nextday = [];

    /**
     * Constructor
     */
    public function __construct() {
        $today = [];
        $nextday = [];
        $cut_off_times_query = tep_db_query("select * from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where 1");
        while ($record = tep_db_fetch_array($cut_off_times_query)) {
            if (!isset($today[$record['platform_id']])) {
                $today[$record['platform_id']] = [];
            }
            if (!isset($nextday[$record['platform_id']])) {
                $nextday[$record['platform_id']] = [];
            }
            $cut_off_times_days = explode(",", $record['cut_off_times_days']);
            if (is_array($cut_off_times_days)) {
                foreach ($cut_off_times_days as $day) {
                    if ($day == 0) {
                        //everyday
                        for ($i = 1; $i <= 7;$i++) {
                            if (!isset($today[$record['platform_id']][$i]) && !empty($record['cut_off_times_today'])) {
                                $today[$record['platform_id']][$i] = $record['cut_off_times_today'];
                            }
                            if (!isset($nextday[$record['platform_id']][$i]) && !empty($record['cut_off_times_next_day'])) {
                                $nextday[$record['platform_id']][$i] = $record['cut_off_times_next_day'];
                            }
                        }
                    } else {
                        if (!isset($today[$record['platform_id']][$day]) && !empty($record['cut_off_times_today'])) {
                            $today[$record['platform_id']][$day] = $record['cut_off_times_today'];
                        }
                        if (!isset($nextday[$record['platform_id']][$day]) && !empty($record['cut_off_times_next_day'])) {
                            $nextday[$record['platform_id']][$day] = $record['cut_off_times_next_day'];
                        }
                    }
                }
            }
            
        }
        $this->today = $today;
        $this->nextday = $nextday;
        
    }
    
    public function isTodayDelivery($date = '', $platform_id = 0) {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return false;
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->today[$platform_id][$dayOfWeek])) {
            return false;
        }
        $todayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->today[$platform_id][$dayOfWeek]);
        if ($timestamp <= $todayDeliveryStamp) {
            return true;
        }
        return false;
    }
    
    public function isNextDayDelivery($date = '', $platform_id = 0) {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return false;
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->nextday[$platform_id][$dayOfWeek])) {
            return false;
        }
        $nextdayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->nextday[$platform_id][$dayOfWeek]);
        if ($timestamp <= $nextdayDeliveryStamp) {
            return true;
        }
        return false;
    }
    
    public function getTodayDeliveryDate($platform_id = 0) {
        $date = date('Y-m-d H:i:s');
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return '';
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->today[$platform_id][$dayOfWeek])) {
            return '';
        }
        $todayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->today[$platform_id][$dayOfWeek]);
        return date('Y-m-d H:i:s' ,$todayDeliveryStamp);
    }
    
    public function getNextDayDeliveryDate($platform_id = 0) {
        $date = date('Y-m-d H:i:s');
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return '';
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->nextday[$platform_id][$dayOfWeek])) {
            return '';
        }
        $nextdayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->nextday[$platform_id][$dayOfWeek]);
        return date('Y-m-d H:i:s' ,$nextdayDeliveryStamp);
    }

}
