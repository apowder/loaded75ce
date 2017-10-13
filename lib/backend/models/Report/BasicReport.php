<?php

namespace backend\models\Report;

use Yii;

class BasicReport {

    protected $request = [];
    protected $orders_avg = false;
    protected $total_avg = false;
    protected $interval = 0;

    public function __construct($data) {
        $this->request = $data;
        $this->all_params['modules'] = $this->loadOtModules();
    }

    public function getRawData($where = "", $for_map = false) {
        $_join = '';
        $_summ = '';
        if (isset($this->all_params['modules']) && is_array($this->all_params['modules'])) {
            foreach ($this->all_params['modules'] as $_module_var) {
                $_module = $_module_var['class'];
                $_summ .= ", ifnull(sum({$_module}.value_inc_tax * o.currency_value),0) as {$_module}";
                $_join .= " left join " . TABLE_ORDERS_TOTAL . " {$_module} on o.orders_id = {$_module}.orders_id and {$_module}.class='" . $_module . "' ";
            }
        }
        //echo '<pre>';print_r($this);die;
        if (isset($this->request['status'])){
            if (is_array($this->request['status']) && count($this->request['status'])){
                $where .= " and o.orders_status in (" . implode(",", $this->request['status']) . ")";
            }            
        }
        
        if (isset($this->request['payment_methods'])){
            if (is_array($this->request['payment_methods']) && count($this->request['payment_methods'])){
                $where .= " and o.payment_class in ('" . implode("','", $this->request['payment_methods']) . "')";
            }            
        }
        
        if (isset($this->request['shipping_methods'])){
            if (is_array($this->request['shipping_methods']) && count($this->request['shipping_methods'])){
                $where .= " and o.shipping_class in ('" . implode("','", $this->request['shipping_methods']) . "')";
            }            
        }
        
        if (isset($this->request['platforms'])){
            if (is_array($this->request['platforms']) && count($this->request['platforms'])){
                $where .= " and o.platform_id in (" . implode(",", $this->request['platforms']) . ")";
            }            
        }
        
        if (isset($this->request['zones'])){
            if (is_array($this->request['zones']) && count($this->request['zones'])){
                $where .= " and (o.delivery_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('". implode("','", $this->request['zones']) . "') ) or"
                        . " o.billing_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('". implode("','", $this->request['zones']) . "') )  )";
            }            
        }
            
        $group_by = "";
        if (isset($this->sql_params['group']) && is_array($this->sql_params['group'])){
            $group_by = " group by ";
            foreach ($this->sql_params['group'] as $_group){
                $group_by .= $_group."(o.date_purchased),";
            }
            $group_by = substr($group_by, 0, -1);
        }
        
        //need convert to main currency
        if ($for_map){
            $sql = "select o.lat, o.lng, o.delivery_address_format_id, o.delivery_street_address, o.delivery_suburb, o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country from " . TABLE_ORDERS . " o " . $_join . " where {$where} and o.lat not in (0 , 9999) and o.lng not in (0 , 9999) order by o.date_purchased";
        } else {
            $sql = "select {$this->sql_params['select_period']}(o.date_purchased) as period, count(o.orders_id) as orders {$_summ} from " . TABLE_ORDERS . " o " . $_join . " where {$where} " . $group_by . " order by o.date_purchased";
        }
        
        
        $_query = tep_db_query($sql);
        $data = [];
        //echo $sql;die;
        if (tep_db_num_rows($_query)) {
            while ($row = tep_db_fetch_array($_query)) {
                $row['period_full'] = date('m/d/Y H:i:s', strtotime($row['period']));
                array_push($data, $row);
            }
        } else {
            $ot = yii\helpers\ArrayHelper::getColumn($this->all_params['modules'], 'class');
            $empty_row = array_merge(['period', 'orders', 'period_full'], $ot);
            $empty_row = array_flip($empty_row);
            foreach($empty_row as $k => $value){
                $empty_row[$k] = '';
            }            
            $data[0] = $empty_row;
        }
        //echo'<pre>';print_r($empty_row);die;
        return $data;

        //$sql = "select dayofmonth(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where month(o.date_purchased) = '" . $month . "' and year(o.date_purchased) = '" . $year . "' " . self::$sel_status_sql . " group by dayofmonth(o.date_purchased), ot.class order by report_day, ot.sort_order ";
        //$orders"select month(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where year(o.date_purchased) = '" . $year . "' " . self::$sel_status_sql . " group by month(o.date_purchased), ot.class order by report_day, ot.sort_order");
        //               "select year(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." ". self::$sel_status_sql . " group by year(o.date_purchased), ot.class order by report_day, ot.sort_order "
    }

    public function loadOtModules() {

        $_query = tep_db_query("select class, if(sort_order, sort_order, 50) as sort_order from " . TABLE_ORDERS_TOTAL . " where 1 group by class order by sort_order");
        $data = [];
        $chart_items = $this->getFilteredModules();
        $order_total_modules = new \common\classes\order_total;
        if (tep_db_num_rows($_query)) {
            while ($row = tep_db_fetch_array($_query)) {
                $ot_module = $row['class'];
                if (empty($ot_module))
                    continue;
                global $$ot_module;
                if (is_array($chart_items) && !in_array($ot_module, $chart_items))
                    continue;
                $sort = 0;
                if (is_object($$ot_module)){
                    $sort = $$ot_module->sort_order;
                }
                $data[$sort] = ['class' => $ot_module, 'title' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_" . strtoupper(substr($ot_module, 3)) . "_TITLE", 'ordertotal')];
            }
        }
        ksort($data);
        $_data = array_values($data);
        return $_data;
    }

    public function getFilteredModules() {
        if (is_array($this->request['chart_group_item']) && count($this->request['chart_group_item'])) {
            $keys = array_keys($this->request['chart_group_item']);
            return $keys;
        }
        return false;
    }

    public function getOtModules() {
        return $this->all_params['modules'];
    }
    
        
    public function getYearsList(){
        $years_query = tep_db_query("select distinct year(date_purchased) as year from " . TABLE_ORDERS ." where 1 order by date_purchased");
        $years = [];
        $_prevous = null;
        if (tep_db_num_rows($years_query)){
            while($year = tep_db_fetch_array($years_query)){
                if (!is_null($_prevous) && $_prevous != $year['year']){
                    if ($year['year'] - $_prevous > 1){
                        $range = range ($_prevous+1, $year['year']-1);
                        if (is_array($range) && count($range)){
                            foreach($range as $y){
                                $years[$y] = $y;
                            }
                        }                        
                    }
                }
                $years[$year['year']] = $year['year'];
                $_prevous = $year['year'];
            }
        }
        return $years;
    }
    
    public function convertColumnTitle($value){
        if ($value == 'orders_avg') {
            return TEXT_ORDERS_AVG;
        }
        if ($value == 'total_avg') {
            return TEXT_TOTAL_AVG;
        }
        return ucfirst($value);
    }
    
    public function prepareDaysRange($pattern = [], $date_pattern = ''){
        $start = date('Y-m-d', mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year));
        $end = date('Y-m-d', mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year));
        $date_start = new \DateTime($start);
        $date_end = new \DateTime($end);
        $interval = $date_end->diff($date_start);
        $result = [];
        if ($interval->days > 0){
            $this->interval = $interval->days;
            for( $i=0; $i<$interval->days+1; $i++){
                $date = new \DateTime($start);
                $date->add(new \DateInterval('P'.$i.'D'));
                if (!empty($date_pattern)){
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                }
                $result[$date->format('d-m-Y')] = $pattern;
            }
        } else {
            $date = new \DateTime($start);
            $date->add(new \DateInterval('P0D'));
            if (!empty($date_pattern)){
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
            }
            $result[$date->format('d-m-Y')] = $pattern;
        }
        return $result;
    }
    
    public function prepareMonthRange($pattern = [], $date_pattern = ''){
        $start = date('Y-m-d', mktime(0, 0, 0, $this->start_month, 1, $this->start_year));
        $end = date('Y-m-d', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        $date_start = new \DateTime($start);
        $date_end = new \DateTime($end);        
        $interval = $date_end->diff($date_start);
        $result = [];
        
        if ($interval->days > 0){
            $this->interval = $interval->days;
            for( $i=0; $i<($interval->m+1 + $interval->y*12); $i++){
                $date = new \DateTime($start);
                $date->add(new \DateInterval('P'.$i.'M'));
                if (!empty($date_pattern)){
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                }
                $result[$date->format('m-Y')] = $pattern;
            }
        } else {
            $date = new \DateTime($start);
            $date->add(new \DateInterval('P0M'));
            if (!empty($date_pattern)){
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
            }
            $result[$date->format('m-Y')] = $pattern;
        }
        return $result;        
    }
    
    public function prepareYearsRange($pattern = [], $date_pattern = ''){
        $start = $this->start_year;
        $end   = $this->end_year;
        $result = [];
        if (is_numeric($end) && is_numeric($start)){
            for($i = $start; $i<= $end; $i++){
                $date = new \DateTime($i."-01-01");
                $pattern['period'] = $date->format($date_pattern);
                $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                $result[$i] = $pattern;
            }
        }        
        return $result;
    }    
        
    public function checkMonthDayYear(){
        if (!checkdate($this->start_month, $this->start_day, $this->start_year)) {
            $this->start_day = date("d");
            $this->start_month = date("m");
            $this->start_year = date("Y");
        }

        if (!checkdate($this->end_month, $this->end_day, $this->end_year)) {
            $this->end_day = date("d");
            $this->end_month = date("m");
            $this->end_year = date("Y");
        }
        
        $check_start = mktime(0,0,0,$this->start_month,$this->start_day,$this->start_year);
        $check_end = mktime(0,0,0,$this->end_month,$this->end_day,$this->end_year);       
        
        if ($check_start > $check_end){
            $this->swapDates();            
        }
        return $this;
    }
    
    public function swapDates(){
        if (property_exists($this, 'start_day') && property_exists($this, 'end_day')){
            $_start_day = $this->start_day;
            $this->start_day = $this->end_day;
            $this->end_day = $_start_day;
        }
        if (property_exists($this, 'start_month') && property_exists($this, 'end_month')){
            $_start_month = $this->start_month;
            $this->start_month = $this->end_month;
            $this->end_month = $_start_month;
        }
        if (property_exists($this, 'start_year') && property_exists($this, 'end_year')){
            $_start_year = $this->start_year;
            $this->start_year = $this->end_year;
            $this->end_year = $_start_year;
        }
    }
       
    public function insertAt(&$mas, $after, $key, $value){
        $keys = array_keys($mas);
        $values = array_values($mas);
        if ($pos = array_search($after, $keys)){
            $keys_head = array_slice($keys, 0, $pos+1);
            $keys_tail = array_slice($keys, $pos+1);
            
            $vals_head = array_slice($values, 0, $pos+1);
            $vals_tail = array_slice($values, $pos+1);
            
            array_push($keys_head, $key);            
            array_push($vals_head, $value);
            
            $keys = array_merge($keys_head, $keys_tail);
            $vals = array_merge($vals_head, $vals_tail);
            
            $mas = array_combine($keys, $vals);
        }
    }
}
