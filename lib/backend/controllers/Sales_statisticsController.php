<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
use backend\components\Graphs;
use backend\models\Report;

class Sales_statisticsController extends Sceleton {

    public $acl = ['BOX_HEADING_REPORTS', 'BOX_REPORTS_SALES'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('ordertotal');
        \common\helpers\Translation::init('admin/sales_statistics');
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language, $languages_id, $currencies;

        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }

        $this->selectedMenu = array('reports', 'sales_statistics');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('sales_statistics/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        $this->view->filter = new \stdClass();

        $report = new Report($_GET);

        $this->view->filter->precision = $report->precisionList();
        $this->view->filter->precision_selected = $report->getPrecision();

        $this->view->filter->statuses = $report->getStatuses();
        $this->view->filter->payment_methods = $report->getPayments();
        $this->view->filter->shipping_methods = $report->getShippings();
        $this->view->filter->platforms = $report->getPlatforms();
        $this->view->filter->zones = $report->getGeoZones();

        $this->view->filter->charts = $report->getChartsGroups();


        $model = $report->getReportModel();

        $m_titles = $model->getOtModules();

        $data = $model->loadPurchases();

        $columns = [];
        if (is_array($data) && count($data)) {
            $_columns = array_keys($data[0]);
            foreach ($_columns as $v) {
                $columns[] = ['class' => $v];
            }
            $m_titles = \yii\helpers\ArrayHelper::map($m_titles, 'class', 'title');
            foreach ($columns as $k => $c) {
                if (isset($m_titles[$c['class']])) {
                    $columns[$k]['title'] = $m_titles[$c['class']];
                } else {
                    $columns[$k]['title'] = $model->convertColumnTitle($columns[$k]['class']);
                }
            }
        }

        $params = [
            'options' => $model->getOptions(),
            'data' => $data,
            'columns' => $columns,
            'range' => $this->renderAjax('range', ['range' => $model->getRange()]),
            'rows' => $model->getRowsCount(),
            'table_title' => $model->getTableTitle(),
            'filters' => $report->getFilters(),
            'selected_filter' => \yii\helpers\Url::to(['sales_statistics/index']) . '?' . $_SERVER["QUERY_STRING"],
            'selected_statuses' => $report->getSelectedStatuses(),
            'selected_payments' => $report->getSelectedPayments(),
            'selected_shippings' => $report->getSelectedShippings(),
            'selected_platforms' => $report->getSelectedPlatforms(),
            'selected_zones' => $report->getSelectedZones(),
            'undisabled' => $report->getUndisabledCharts(),
        ];
        //echo '<pre>';print_r($params);die;

        if (Yii::$app->request->isAjax) {
            echo json_encode($params);
            exit();
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionLoadOptions() {
        $type = Yii::$app->request->get('type');
        $options = '';
        $undisabled = [];
        if ($type) {
            $report = new Report(['type' => $type]);
            $options = $report->getReportModel()->getOptions();
            $undisabled = $report->getUndisabledCharts();
        }
        echo json_encode(['options' => $options, 'undisabled' => $undisabled]);
    }

    public function actionSaveFilter() {
        $params = Yii::$app->request->getBodyParams();
        $message = '';

        //$params['options'] = urldecode($params['options']);

        if (is_array($params)) {
            if (isset($params['filter_name']) && !empty($params['filter_name']) && isset($params['options']) && !empty($params['options'])) {
                tep_db_query("insert into " . TABLE_SALES_FILTERS . " set sales_filter_name = '" . tep_db_input($params['filter_name']) . "', sales_filter_vals = '" . tep_db_input($params['options']) . "'");
                $message = TEXT_MESSEAGE_SUCCESS;
            } else {
                $message = TEXT_MESSAGE_ERROR;
            }
        } else {
            $message = TEXT_MESSAGE_ERROR;
        }
        echo json_encode(['message' => $message]);
        exit();
    }

    public function actionMapShow() {
        $key = tep_db_fetch_array(tep_db_query("select info as setting_code from " . TABLE_GOOGLE_SETTINGS . " where module='mapskey'"));

        $origPlace = array(0, 0, 2);
        $country_info = tep_db_fetch_array(tep_db_query("select ab.entry_country_id from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab inner join " . TABLE_PLATFORMS . " p on p.is_default = 1 and p.platform_id = ab.platform_id where ab.is_default = 1"));
        $_country = (int) STORE_COUNTRY;
        if ($country_info) {
            $_country = $country_info['entry_country_id'];
        }
        if (defined('STORE_COUNTRY') && (int) STORE_COUNTRY > 0) {
            $origPlace = tep_db_fetch_array(tep_db_query("select lat, lng, zoom from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $_country . "'"));
        }
        return $this->renderAjax('map', ['mapskey' => $key['setting_code'], 'origPlace' => $origPlace]);
    }

    public function actionMap() {
        $report = new Report($_GET);
        $model = $report->getReportModel();
        $data = $model->loadPurchases(true);

        echo json_encode(['data' => $data]);
        exit();
    }

    public function actionExport() {
        $report = new Report($_GET);
        $model = $report->getReportModel();
        $data = $model->loadPurchases(false);

        $ex_type = Yii::$app->request->get('ex_type');
        $ex_data = explode("|", Yii::$app->request->get('ex_data'));
        $report->export($data, ['modules' => $ex_data, 'type' => $ex_type]);
        exit();
    }

}
