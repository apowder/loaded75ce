<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models;

use Yii;
use yii\web\Session;
use backend\models\Report\HourlyReport;
use backend\models\Report\DailyReport;
use backend\models\Report\MonthlyReport;
use backend\models\Report\YearlyReport;
use backend\models\Report\QuarterlyReport;
use backend\models\Report\WeeklyReport;

class Report {

    private $_precision = 'daily';
    private $data = [];
    private $_report;

    public function __construct($vars) {
        $this->data = $vars;
        if (isset($vars['type']))
            $this->setPrecision($vars['type']);

        $platform_config = new \common\classes\platform_config(\common\classes\platform::defaultId());
        $platform_config->constant_up();
        //echo'<pre>';print_r($this->data);die;
    }

    public function setPrecision($value) {
        if (!array_key_exists($value, $this->precisionList()))
            return;
        $this->_precision = $value;
    }

    public function getPrecision() {
        return $this->_precision;
    }

    public function precisionList() {
        return [
            'hourly' => STATISTICS_TYPE_HOURLY,
            'daily' => STATISTICS_TYPE_DAILY,
            'weekly' => STATISTICS_TYPE_WEEKLY,
            'monthly' => STATISTICS_TYPE_MONTHLY,
            'quarterly' => STATISTICS_TYPE_QUARTERLY,
            'yearly' => STATISTICS_TYPE_YEARLY,
        ];
    }

    public function getChartsGroups() {
        return
                [
                    ['orders' => ['label' => TEXT_ORDERS, 'selected' => $this->isSelectedChart('orders'), 'color' => '#005dc3',]],
                    ['orders_avg' => ['label' => 'Average number of orders', 'selected' => $this->isSelectedChart('orders_avg', false), 'color' => '#2a6ebe', 'disabled' => $this->isDisabledStatus()]],
                    ['ot_tax' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_TAX_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_tax'), 'color' => '#619193']],
                    [
                        'ot_subtotal' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_SUBTOTAL_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_subtotal'), 'color' => '#24b71e'],
                        'ot_total' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_TOTAL_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_total'), 'color' => '#ed3d05'],
                    ],
                    ['total_avg' => ['label' => 'Average Total', 'selected' => $this->isSelectedChart('total_avg', false), 'color' => '#2a6ebe', 'disabled' => $this->isDisabledStatus()]],
                    ['ot_shipping' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_SHIPPING_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_shipping'), 'color' => '#1aa69b']],
                    [
                        'ot_paid' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_PAID_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_paid'), 'color' => '#24b71e'],
                        'ot_due' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_DUE_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_due'), 'color' => '#ed3d05'],
                    ],
                    [
                        'ot_gift_wrap' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_GIFT_WRAP_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_gift_wrap'), 'color' => '#fe9f00'],
                        'ot_coupon' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_COUPON_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_coupon'), 'color' => '#065d60'],
                        'ot_loworderfee' => ['label' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE", 'ordertotal'), 'selected' => $this->isSelectedChart('ot_loworderfee'), 'color' => '#1ab8f9'],
                    ]
        ];
    }

    public function isSelectedChart($chart, $default = true) {
        $status = $default;
        if (isset($this->data['chart_group_item']) && is_array($this->data['chart_group_item'])) {
            $status = isset($this->data['chart_group_item'][$chart]);
        }
        return $status;
    }

    public function getReportModel() {
        switch ($this->_precision) {
            case 'hourly': $this->_report = new HourlyReport($this->data);
                break;
            case 'weekly': $this->_report = new WeeklyReport($this->data);
                break;
            case 'monthly': $this->_report = new MonthlyReport($this->data);
                break;
            case 'quarterly': $this->_report = new QuarterlyReport($this->data);
                break;
            case 'yearly': $this->_report = new YearlyReport($this->data);
                break;
            default:
            case 'daily': $this->_report = new DailyReport($this->data);
                break;
        }

        return $this->_report;
    }

    public function getStatuses() {
        global $languages_id;
        $_statuses = [];
        $statuses_query = tep_db_query("select og.orders_status_groups_name, og.orders_status_groups_id, o.orders_status_id, o.orders_status_name from " . TABLE_ORDERS_STATUS_GROUPS . " og, " . TABLE_ORDERS_STATUS . " o where og.language_id = '" . (int) $languages_id . "' and og.orders_status_groups_id = o.orders_status_groups_id and o.language_id = '" . (int) $languages_id . "'");
        while ($row = tep_db_fetch_array($statuses_query)) {
            $_statuses[] = $row;
        }
        $statuses = \yii\helpers\ArrayHelper::map($_statuses, 'orders_status_id', 'orders_status_name', 'orders_status_groups_name');
        return $statuses;
    }

    public function getSelectedStatuses() {
        if (isset($this->data['status'])) {
            return $this->data['status'];
        }
        return [];
    }

    public function getSelectedPayments() {
        if (isset($this->data['payment_methods'])) {
            return $this->data['payment_methods'];
        }
        return [];
    }

    public function getSelectedShippings() {
        if (isset($this->data['shipping_methods'])) {
            return $this->data['shipping_methods'];
        }
        return [];
    }

    public function getSelectedPlatforms() {
        if (isset($this->data['platforms'])) {
            return $this->data['platforms'];
        }
        return [];
    }

    public function getSelectedZones() {
        if (isset($this->data['zones'])) {
            return $this->data['zones'];
        }
        return [];
    }

    public function getShippings() {
        $shipping_methods = [];
        $shipping_methods_query = tep_db_query("select distinct shipping_class from " . TABLE_ORDERS . " where 1 order by shipping_class");
        if (tep_db_num_rows($shipping_methods_query)) {
            $shipping_modules = new \common\classes\shipping();
            while ($row = tep_db_fetch_array($shipping_methods_query)) {
                $_shipping = $row['shipping_class'];
                if (empty($_shipping))
                    continue;
                $modules = explode("_", $_shipping);
                $module = $modules[0];
                global $$module;
                if (is_object($$module)) {
                    $shipping_methods[$_shipping] = $$module->getTitle($_shipping);
                } else {
                    $shipping_methods[$_shipping] = $_shipping;
                }
            }
        }
        return $shipping_methods;
    }

    public function getPayments() {
        $payment_methods = [];
        $payment_methods_query = tep_db_query("select distinct payment_class from " . TABLE_ORDERS . " where 1 order by payment_class");
        if (tep_db_num_rows($payment_methods_query)) {
            $payment_modules = new \common\classes\payment();

            while ($row = tep_db_fetch_array($payment_methods_query)) {
                $_payment = $row['payment_class'];

                if (empty($_payment))
                    continue;
                global $$_payment;
                //echo '<pre>';var_dump(strpos($_payment, 'offline') !== false , $$_payment, is_object('offline'));
                if (strpos($_payment, 'offline') !== false && is_object($GLOBALS[offline])) {
                    $payment_methods[$_payment] = $GLOBALS[offline]->getTitle($_payment);
                } else if (is_object($$_payment)) {
                    $payment_methods[$_payment] = $$_payment->getTitle($_payment);
                } else {
                    $payment_methods[$_payment] = $_payment;
                }
            }
        }

        return $payment_methods;
    }

    public function getPlatforms() {
        $_platforms = \common\classes\platform::getList();
        $platforms = \yii\helpers\ArrayHelper::map($_platforms, 'id', 'text');
        return $platforms;
    }

    public function getGeoZones() {
        global $languages_id;
        $_zones = [];
        $zone_query = tep_db_query("select gz.geo_zone_id, gz.geo_zone_name, c.countries_name, zgz.zone_country_id from " . TABLE_GEO_ZONES . " gz, " . TABLE_ZONES_TO_GEO_ZONES . " zgz left join " . TABLE_COUNTRIES . " c on c.countries_id = zgz.zone_country_id and c.language_id = '" . (int) $languages_id . "' where gz.geo_zone_id = zgz.geo_zone_id order by geo_zone_name, countries_name");
        while ($row = tep_db_fetch_array($zone_query)) {
            $_zones[] = $row;
        }
        $zones = \yii\helpers\ArrayHelper::map($_zones, 'zone_country_id', 'countries_name', 'geo_zone_name');
        return $zones;
    }

    public function isDisabledStatus() {
        if (in_array($this->getPrecision(), $this->getUndisabledCharts())) {
            return false;
        }
        return true;
    }

    public function getUndisabledCharts() {
        return ['hourly', 'quarterly', /* 'yearly' */];
    }

    public static function getFilters() {
        $filters_query = tep_db_query("select sales_filter_vals, sales_filter_name from " . TABLE_SALES_FILTERS . " order by sales_filter_name");
        $filters = [];
        while ($d = tep_db_fetch_array($filters_query)) {
            $filters[\yii\helpers\Url::to(['sales_statistics/index']) . '?' . $d['sales_filter_vals']] = $d['sales_filter_name'];
        }
        return $filters;
    }

    public function filterData($enabled, $data) {
        if (is_array($enabled) && count($enabled)) {
            $_temp = [];
            foreach ($data as $block) {
                unset($block['period_full']);
                foreach ($block as $key => $items) {
                    if (!in_array($key, $enabled) && $key != 'period') {
                        unset($block[$key]);
                    }
                }
                $_temp[] = $block;
            }
            $data = $_temp;
        }
        return $data;
    }

    public function export($data, array $params) {
        if (!isset($params['type']))
            $params['type'] = 'CSV';

        //if (!isset($params['modules']))
        //    $params['modules'] = [];
        //$data = $this->filterData($params['modules'], $data);

        switch ($params['type']) {
            case 'XLS':
            case 'CSV':
            default:
                $this->_export_csv($data);
                break;
        }
    }

    private function _getFilename($type) {
        return "sale_statistics_" . date("dmY_His") . "." . $type;
    }

    private function _setHeaders($filename) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Pragma: public'); // HTTP/1.0
        header('Cache-Control: cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Filename: ' . $filename);
    }

    private function _export_csv($data) {
        $filename = $this->_getFilename('csv');

        $this->_setHeaders($filename);

        $CSV = new \backend\models\EP\Formatter\CSV('write', array(), $filename);
        if (is_array($data) && count($data)) {
            $headers = [];

            foreach (array_keys($data[0]) as $key) {
                if ($key == 'period_full')
                    continue;
                if (strpos($key, 'ot_') !== false)
                    $key = substr($key, 3);
                $headers[] = $this->_report->convertColumnTitle($key);
            }
            $CSV->write_array($headers);
            foreach ($data as $row) {
                unset($row['period_full']);
                $CSV->write_array($row);
            }
        }
    }

}
