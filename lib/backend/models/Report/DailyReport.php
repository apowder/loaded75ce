<?php

namespace backend\models\Report;

use Yii;

class DailyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = -1;

    protected $start_day = '01';
    protected $end_day;
    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;
    protected $month_year;
    protected $start_custom;
    protected $end_custom;
    protected $all_params = [];
    private $name = 'daily';
    protected $sql_params = [
        'group' => ['dayofmonth', 'month', 'year'],
        'select_period' => 'date',
    ];

    public function __construct($data) {
        if (isset($data['month_year']) && !empty($data['month_year'])) {
            $this->month_year = $data['month_year'];
            $month_year = $this->parseDate($data['month_year']);
            $this->start_month = $month_year['month'];
            $this->start_year = $month_year['year'];
            $this->end_month = $month_year['month'];
            $this->end_year = $month_year['year'];
            $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        }
        if (isset($data['start_custom']) && !empty($data['start_custom'])) {
            $this->start_custom = $data['start_custom'];
            $start_custom = $this->parseDate($data['start_custom']);
            $this->start_month = $start_custom['month'];
            $this->start_year = $start_custom['year'];
            $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        }

        if (isset($data['end_custom']) && !empty($data['end_custom'])) {
            $this->end_custom = $data['end_custom'];
            $end_custom = $this->parseDate($data['end_custom']);
            $this->end_month = $end_custom['month'];
            $this->end_year = $end_custom['year'];
            $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        }

        if (empty($this->start_month))
            $this->start_month = date("m");
        if (empty($this->end_month))
            $this->end_month = date("m");
        if (empty($this->start_year))
            $this->start_year = date("Y");
        if (empty($this->end_year))
            $this->end_year = date("Y");
        if (empty($this->end_day))
            $this->end_day = date("t");

        if (empty($this->start_custom)) {
            $this->month_year = $this->start_month . self::DELIMETER . $this->start_year;
        } else {
            $this->month_year = '';
        }

        $this->checkMonthDayYear();
        $this->start_day = '01';
        $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));

        parent::__construct($data);
    }

    public function getOptions() {
        return Yii::$app->controller->renderAjax('daily_options', [
                    'month_year' => $this->month_year,
                    'start_custom' => $this->start_custom,
                    'end_custom' => $this->end_custom,
        ]);
    }

    public function parseDate($month_year) {
        $ex = explode(self::DELIMETER, $month_year);
        return ['month' => $ex[0], 'year' => $ex[1]];
    }

    public function loadPurchases($for_map = false) {
        global $currencies, $currency;
        $where = " ( o.date_purchased between '" . $this->start_year . "-" . $this->start_month . "-01 00:00:00' and '" . $this->end_year . "-" . $this->end_month . "-31 23:59:59' ) ";
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
                    $new_data = $this->prepareDaysRange($template, "d M Y");
                    $filled = true;
                }
                if (!empty($v['period'])) {
                    $data[$k]['period'] = date("d M Y", strtotime($v['period']));
                    $data[$k]['period_full'] = date("m/d/Y H:00:00", strtotime($v['period']));
                    $new_data[date("d-m-Y", strtotime($v['period']))] = $data[$k];
                }
            }

            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                $_temp[] = $vday;
            }
            $data = $_temp;
        }
        return $data;
    }

    public function getRange() {
        if ($this->start_month == $this->end_month && $this->start_year == $this->end_year) {
            return date("M, Y", mktime(0, 0, 0, $this->start_month, 1, $this->start_year));
        }
        return date("M, Y", mktime(0, 0, 0, $this->start_month, 1, $this->start_year)) . ' - ' . date("M, Y", mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_DAILY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return TEXT_DAY;
        }
        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {
        if (($this->start_month != $this->end_month &&
                $this->start_year == $this->end_year) ||
                ($this->start_month == $this->end_month &&
                $this->start_year != $this->end_year) ||
                ($this->start_month != $this->end_month &&
                $this->start_year != $this->end_year)
        ) {
            return 25;
        }
        return self::SHOW_ROWS;
    }

}
