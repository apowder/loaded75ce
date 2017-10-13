<?php

namespace backend\models\Report;

use Yii;

class QuarterlyReport extends BasicReport implements ReportInterface{

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 25;

    protected $start_month;
    protected $end_month;
    protected $start_quarter;
    protected $end_quarter;
    protected $start_year;
    protected $end_year;
    private $start_custom_quarter;
    private $end_custom_quarter;
    private $start_custom_year;
    private $end_custom_year;
    protected $all_params = [];
    private $name = 'yearly';
    protected $sql_params = [
        'group' => ['month', 'year'],
        'select_period' => 'date',
    ];
    private $quarters = ['1' => 'Q1', '2' => 'Q2', '3'=> 'Q3', '4'=>'Q4'];

    public function __construct($data) {
        
        if (isset($data['quarter']) && !empty($data['quarter'])) {
            $this->start_quarter = $data['quarter'];
            $this->end_quarter = $data['quarter'];
        }
        if (isset($data['year']) && !empty($data['year'])) {
            $this->start_year = $data['year'];
            $this->end_year = $data['year'];
        }

        if (isset($data['start_custom_quarter']) && !empty($data['start_custom_quarter'])) {
            $this->start_quarter = $data['start_custom_quarter'];
            $this->start_custom_quarter = $data['start_custom_quarter'];
        }
        if (isset($data['start_custom_year']) && !empty($data['start_custom_year'])) {
            $this->start_year = $data['start_custom_year'];
            $this->start_custom_year = $data['start_custom_year'];
        }
        if (isset($data['end_custom_quarter']) && !empty($data['end_custom_quarter'])) {
            $this->end_quarter = $data['end_custom_quarter'];
            $this->end_custom_quarter = $data['end_custom_quarter'];
        }
        if (isset($data['end_custom_year']) && !empty($data['end_custom_year'])) {
            $this->end_year = $data['end_custom_year'];
            $this->end_custom_year = $data['end_custom_year'];
        }

        if (empty($this->start_quarter)){
            $q = tep_db_fetch_array(tep_db_query("select quarter(now()) as quarter"));
            $this->start_quarter = $q['quarter'];
        }
        if (empty($this->end_quarter) && isset($data['start_custom_quarter']) && !empty($data['start_custom_quarter'])){
            $this->end_quarter = ceil(date("n")/3);
        }
        
        if (empty($this->end_quarter)){
            $this->end_quarter = $this->start_quarter;
        }
        
        if (!empty($this->start_year) && !empty($this->end_year)){
            if ($this->end_year < $this->start_year){
                $this->end_year = $this->start_year;
            }
        }
        
        $years = $this->getYearsList();
        $years = array_values($years);
        if (empty($this->start_year))
            $this->start_year = $years[0];
        if (empty($this->end_year))
            $this->end_year = $years[sizeof($years)-1];
        
        $this->start_month = ($this->start_quarter *3 - 2);
        $this->end_month = ($this->end_quarter *3);
        //need ordering check 

        parent::__construct($data);

    }

    public function getOptions() {
        return Yii::$app->controller->renderAjax('quaterly_options', [
            'start_custom_quarter' => $this->start_custom_quarter,
            'end_custom_quarter' => $this->end_custom_quarter,
            'start_custom_year' => $this->start_custom_year,
            'end_custom_year' => $this->end_custom_year,
            'quarter' => (!tep_not_null($this->start_custom_quarter)?$this->start_quarter:''),
            'year' => (!tep_not_null($this->end_custom_year)?$this->end_year:'' ), 
            'years' => $this->getYearsList(), 
            'quarters' => $this->quarters]);
    }

    public function updateQuarterPeriod(&$data){
        if (is_array($data)){
            $q = [];
            foreach($data as $month => $value){
                $ex = explode("-", $month);
                $cq = ceil((int)$ex[0]/3);               
                if (!isset($q[$cq.$ex[1]])){
                    $data[$month]['period'] = 'Q' . $cq . " " . $ex[1] . " " . $value['period'];
                    $q[$cq.$ex[1]] = true;
                }
            }
        }        
    }

    public function loadPurchases($for_map = false) {
        global $currencies, $currency;
        $where = " ( o.date_purchased between '" . $this->start_year ."-". $this->start_month . "-01 00:00:00' and '" . $this->end_year ."-". $this->end_month . "-31 23:59:59' ) ";
        $data = $this->getRawData($where, $for_map);
        if (is_array($data)) {
            $_q = [];
            $filled = false;
            $new_data = [];
            foreach ($data as $k => $v) {
                if (!$filled) {
                    $template = $v;
                    foreach ($template as $key => $value) {
                        if ($key != 'period_full') {
                            $template[$key] = '';
                        }
                    }
                    $new_data = $this->prepareMonthRange($template, "M");
                    $this->updateQuarterPeriod($new_data);
                    $filled = true;        
                }

                if (!empty($v['period'])) {
                    
                    $q = tep_db_fetch_array(tep_db_query("select quarter('{$v['period']}') as quarter"));
                    if ($q){
                        $strtotime = strtotime($v['period']);
                        if (!in_array($q['quarter'].date("Y", $strtotime), $_q)){
                            $data[$k]['period'] = $new_data[date("m-Y", $strtotime)]['period'];
                            $_q[] = $q['quarter'].date("Y", $strtotime);
                        } else {
                            $data[$k]['period'] = date("M", $strtotime);
                        }
                        $new_data[date("m-Y", $strtotime)] = $data[$k];
                    } 
                    
                }
            }
               // echo '<pre>';print_r($new_data);die;
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                $_temp[] = $vday;
            }
            $data = $_temp;
        }
        
        return $data;
    }

    public function getRange() {
        if ($this->start_quarter == $this->end_quarter && $this->start_year == $this->end_year){
            return "Q". $this->end_quarter . " " . date("Y", mktime(0, 0, 0, 12, 1, $this->end_year));
        }
        return "Q".$this->start_quarter . " " . date("Y", mktime(0, 0, 0, 1, 1, $this->start_year)) . ' - ' . "Q". $this->end_quarter . " " . date("Y", mktime(0, 0, 0, 12, 1, $this->end_year));
    }

    public function getTableTitle(){
        return TEXT_SALES_QUARTERLY_STATISTICS;
    }
    
    public function convertColumnTitle($value){
        if ($value == 'period'){
            return parent::convertColumnTitle(TEXT_MONTH_COMMON);
        }
        return parent::convertColumnTitle($value);
    }
    
    public function getRowsCount(){
        return self::SHOW_ROWS;
    }

}
