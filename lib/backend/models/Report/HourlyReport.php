<?php

namespace backend\models\Report;

use Yii;

class HourlyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 25;

    protected $start_day;
    protected $end_day;
    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;
    protected $start_custom;
    protected $end_custom;
    protected $day;
    protected $all_params = [];
    private $name = 'hourly';
    protected $orders_avg = false;
    protected $total_avg = false;
    protected $interval = 0;
    protected $sql_params = [
        'group' => ['hour', 'dayofmonth', 'month', 'year'],
        'select_period' => '',
    ];

    public function __construct($data) {
        if (isset($data['day']) && !empty($data['day'])) {
            $day = $this->parseDate($data['day']);
            $this->start_day = $day['day'];
            $this->start_month = $day['month'];
            $this->start_year = $day['year'];
            $this->end_day = $day['day'];
            $this->end_month = $day['month'];
            $this->end_year = $day['year'];
        }
        if (isset($data['start_custom']) && !empty($data['start_custom'])) {
            $this->start_custom = $data['start_custom'];
            $start_custom = $this->parseDate($data['start_custom']);
            $this->start_day = $start_custom['day'];
            $this->start_month = $start_custom['month'];
            $this->start_year = $start_custom['year'];
        }

        if (isset($data['end_custom']) && !empty($data['end_custom'])) {
            $this->end_custom = $data['end_custom'];
            $end_custom = $this->parseDate($data['end_custom']);
            $this->end_day = $end_custom['day'];
            $this->end_month = $end_custom['month'];
            $this->end_year = $end_custom['year'];
        }

        if (empty($this->start_day))
            $this->start_day = date("d");
        if (empty($this->end_day))
            $this->end_day = date("d");
        if (empty($this->start_month))
            $this->start_month = date("m");
        if (empty($this->end_month))
            $this->end_month = date("m");
        if (empty($this->start_year))
            $this->start_year = date("Y");
        if (empty($this->end_year))
            $this->end_year = date("Y");

        if (empty($this->start_custom)) {
            $this->day = $this->start_day . self::DELIMETER . $this->start_month . self::DELIMETER . $this->start_year;
        } else {
            $this->day = '';
        }

        $this->checkMonthDayYear();
        
        if (isset($data['chart_group_item']['orders_avg'])){
            $this->orders_avg = true;
        }
        
        if (isset($data['chart_group_item']['total_avg'])){
            $this->total_avg = true;
        }
        
        parent::__construct($data);
    }

    public function getOptions() {
        return Yii::$app->controller->renderAjax('hourly_options', [
                    'day' => $this->day,
                    'start_custom' => $this->start_custom,
                    'end_custom' => $this->end_custom,
        ]);
    }

    public function parseDate($day) {
        $ex = explode(self::DELIMETER, $day);
        return ['day' => $ex[0], 'month' => $ex[1], 'year' => $ex[2]];
    }

    public function fillTheTime(&$data, $info) {
        static $last_day;
        $str = strtotime($info['period']);
        $d = date("d-m-Y", $str);
        $_period = '';
        if ($last_day != $d) {
            //$_period = date("d M Y H:00",  mktime(0, 0, 0, date("m", $str), date("d", $str), date("Y", $str)) );
            //$data[$d]['00:00']['period'] = $_period;
            $info['period'] = date("H:00", strtotime($info['period']));
            $last_day = date("d-m-Y", $str);
        } else {
            $info['period'] = date("H:00", strtotime($info['period']));
        }
        $data[$d][date("H:00", mktime(date("H", $str), 0, 0, date("m", $str), date("d", $str), date("Y", $str)))] = $info;

        return;
    }

    public function fillTimeGaps($data, $pattern) {
        $_hours = [];

        for ($i = 0; $i < 24; $i++) {
            $pattern['period'] = date("H:00", mktime($i, 0, 0, date("m"), date("d"), date("Y")));
            $pattern['period_full'] = date("m/d/Y H:00:00", mktime($i, 0, 0, date("m", strtotime($pattern['period_full'])), date("d", strtotime($pattern['period_full'])), date("Y", strtotime($pattern['period_full']))));
            $_hours[$pattern['period']] = $pattern;
        }

        foreach ($data as $day => $value) {
            $data[$day] = $_hours;
            //04/01/2017 12:31:07
            $data[$day]['00:00']['period'] = date("d M Y 00:00", mktime(0, 0, 0, date("m", strtotime($day)), date("d", strtotime($day)), date("Y", strtotime($day))));
        }

        return $data;
    }
    
    public function calculateAVG($data){
        if (is_array($data)){
            $avg = ['orders' => [], 'total' => []];
            foreach($data as $day => $hours){                
                foreach($hours as $hour => $value){
                    if ($this->orders_avg){
                        $avg['orders'][$hour] += (int)$value['orders'];
                    }
                    if ($this->total_avg){
                        $avg['total'][$hour] += (int)$value['ot_total'];
                    }
                }
            }
            foreach($data as $day => $hours){                
                foreach($hours as $hour => $value){
                    if ($this->orders_avg){
                        $this->insertAt($data[$day][$hour], 'orders', 'orders_avg', round($avg['orders'][$hour]/count($data), 2) );
                    }
                    if ($this->total_avg){
                        $this->insertAt($data[$day][$hour], 'ot_total', 'total_avg', round($avg['total'][$hour]/count($data),2) );
                    }
                }
            }
        }
        //echo '<pre>';print_r($data);die;
        return $data;
    }    

    public function loadPurchases($for_map = false) {
        global $currencies, $currency;

        $where = " ( o.date_purchased between '" . $this->start_year . "-" . $this->start_month . "-" . $this->start_day . " 00:00:00' and '" . $this->end_year . "-" . $this->end_month . "-" . $this->end_day . "  23:59:59' ) ";
        $data = $this->getRawData($where, $for_map);
        
        $new_data = $this->prepareDaysRange();

        if (is_array($data)) {
            $filled = false;
            foreach ($data as $k => $v) {
                if (!$filled) {
                    $template = $v;
                    foreach ($template as $key => $value) {
                        if ($key != 'period_full') {
                            $template[$key] = '';
                        }
                    }
                    $new_data = $this->fillTimeGaps($new_data, $template);
                    $filled = true;
                }
                $this_day = date("d-m-Y", strtotime($v['period']));
                if (!empty($v['period'])){
                    $this->fillTheTime($new_data, $v);
                }
            }
            
            if ( ($this->orders_avg || $this->total_avg) && $this->interval > 0 ){
                $new_data = $this->calculateAVG($new_data);
            }            
//echo'<pre>';print_r($this);die;
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                foreach ($vday as $day) {
                    $_temp[] = $day;
                }
            }
            $data = $_temp;
        }
        
        return $data;
    }

    public function getRange() {
        if ($this->start_day == $this->end_day && $this->start_month == $this->end_month && $this->start_year == $this->end_year) {
            return date("d M Y", mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year));
        }
        return date("d M Y", mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year)) . ' - ' . date("d M Y", mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_HOURLY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return TEXT_TIME_DATE;
        }
                
        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {        
        return self::SHOW_ROWS;
    }

}
