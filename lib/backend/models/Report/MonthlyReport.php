<?php

namespace backend\models\Report;

use Yii;

class MonthlyReport extends BasicReport implements ReportInterface{

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 10;

    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;
    private $start_custom;
    private $end_custom;
    protected $all_params = [];
    private $name = 'monthly';
    protected $sql_params = [
        'group' => ['month', 'year'],
        'select_period' => 'date',
    ];

    public function __construct($data) {
        if (isset($data['year'])) {
            $this->start_month = '01';
            $this->start_year = $data['year'];
            $this->end_month = '12';
            $this->end_year = $data['year'];
        }
        if (isset($data['start_custom']) && !empty($data['start_custom'])) {
            $start_custom = $this->parseDate($data['start_custom']);
            $this->start_custom = $data['start_custom'];
            $this->start_month = $start_custom['month'];
            $this->start_year = $start_custom['year'];
        }

        if (isset($data['end_custom']) && !empty($data['end_custom'])) {
            $end_custom = $this->parseDate($data['end_custom']);
            $this->end_custom = $data['end_custom'];
            $this->end_month = $end_custom['month'];
            $this->end_year = $end_custom['year'];
        }

        if (empty($this->start_month))
            $this->start_month = '01';
        if (empty($this->end_month))
            $this->end_month = '12';
        if (empty($this->start_year))
            $this->start_year = date("Y");
        if (empty($this->end_year))
            $this->end_year = date("Y");
        
        //need ordering check 

        parent::__construct($data);
    }

    public function getOptions() {
        return Yii::$app->controller->renderAjax('monthly_options', [
            'start_custom' => $this->start_custom,
            'end_custom' => $this->end_custom,
            'year' => (!empty($this->start_custom)|| !empty($this->end_custom)?"":$this->start_year), 
            'years' => $this->getYearsList(),
            ]);
    }

    public function parseDate($month_year) {
        $ex = explode(self::DELIMETER, $month_year);
        return ['month' => $ex[0], 'year' => $ex[1]];
    }

    public function loadPurchases($for_map = false) {
        global $currencies, $currency;
        $where = " ( o.date_purchased between '" . $this->start_year ."-". $this->start_month . "-01 00:00:00' and '" . $this->end_year ."-". $this->end_month . "-31 23:59:59' ) ";
        $data = $this->getRawData($where, $for_map);
        if (is_array($data)) {
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
                    $new_data = $this->prepareMonthRange($template, "M Y");
                    $filled = true;
                }
                if (!empty($v['period'])) {
                    $data[$k]['period'] = date("M Y", strtotime($v['period']));
                    $data[$k]['period_full'] = date("m/d/Y H:00:00", strtotime($v['period']));
                    $new_data[date("m-Y", strtotime($v['period']))] = $data[$k];                    
                }
            }
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                $_temp[] = $vday;
            }
            $data = $_temp;
            //$this->end_month = date("m", strtotime($v['period']));
        }
        return $data;
    }

    public function getRange() {
        return date("M, Y", mktime(0, 0, 0, $this->start_month, 1, $this->start_year)) . ' - ' . date("M, Y", mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
    }

    public function getTableTitle(){
        return TEXT_SALES_MONTHLY_STATISTICS;
    }
    
    public function convertColumnTitle($value){
        if ($value == 'period'){
            return parent::convertColumnTitle(TEXT_MONTH_COMMON);
        }
        return parent::convertColumnTitle($value);
    }
    
    public function getRowsCount(){
        if (($this->start_month != $this->end_month &&
            $this->start_year == $this->end_year) ||
            ($this->start_month == $this->end_month &&
            $this->start_year != $this->end_year)
            ){
            return 25;
        }
        return self::SHOW_ROWS;
    }

}
