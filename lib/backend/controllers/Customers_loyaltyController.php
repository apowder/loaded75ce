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
use common\extensions\CustomerLoyalty\CustomerLoyalty;

/**
 * default controller to handle user requests.
 */
class Customers_loyaltyController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_LOYALTY'];

    public function __construct($id, $module = null) {
        if (false === \common\helpers\Acl::checkExtension('CustomerLoyalty', 'allowed')) {
            $this->redirect(array('/'));
        }
        \common\helpers\Translation::init('admin/loyalty');

        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('customers', 'customers_loyalty');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('sms_messages/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = BOX_TAXES_COUNTRIES;
        $action = Yii::$app->request->get('action', '');
        $cID = Yii::$app->request->get('cID', 0);
        $filter = Yii::$app->request->get('filter', '');

        if (empty($action) && isset($cID) && $cID > 0) {
            $this->topButtons[] = '<a href="#" class="create_item" onclick="return newLoyalty(' . $cID . ')">' . IMAGE_INSERT . '</a>';
            if ( $filter == 'to_dispatch' && CustomerLoyalty::havePointsForDispatch($cID) ) {
                    $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['customers_loyalty/manual-dispatch', 'cID' => $cID]).'" class="create_item">' . TEXT_DISPATCH_NOW . '</a>';
            }
        }

        $this->view->loyaltyTable = array(
            array(
                'title' => TABLE_HEADING_CUSTOMERS,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LOYALTY_STATE,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_ORDER,
                'not_important' => 0,
            ),
            array(
                'title' => BOX_HEADING_GV_ADMIN,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_POINTS,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_DATE_ADDED,
                'not_important' => 0,
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int) $_GET['row'];
        $this->view->filters->cid = $cID;
        $this->view->filters->filter = $filter;
        $this->view->filters->filters = $this->getFilters();
        
        $messages = [];
        $_messages = Yii::$app->session->getAllFlashes();
        if (is_array($_messages) && count($_messages)){
            foreach($_messages as $type => $_mes){
                $messages[] = ['messageType' => 'alert-'.$type, 'message' => $_mes];
            }
        }
        //echo'<pre>';print_r($messages);die;
        Yii::$app->session->removeAllFlashes();

        return $this->render('index', ['messages' => $messages] );
    }

    public function getFilters() {
        return [
            'to_dispatch' => TEXT_READY_FOR_DESPATCH,
            'new' => TEXT_PENDING,
            'coupons' => TEXT_COUPONS
        ];
    }

    public function actionList() {
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);
        parse_str(Yii::$app->request->get('filter', ''), $output);
        if (isset($output['cID']) && !empty($output['cID'])){
            $cID = $output['cID'];
        }
        if (isset($output['filter']) && !empty($output['filter'])){
            $filter = $output['filter'];
        }
        
        $filter_map = array(
            'new' => "AND lp.loyalty_state IN('new') ",
            'to_dispatch' => "AND lp.loyalty_state IN('count') ",
            'coupons' => "AND lp.loyalty_state IN ('dispatch') ",
        );
        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_prepare_input($_GET['search']['value']);
            $search = " and (c.customers_firstname like '%" . tep_db_input($keywords) . "%' or c.customers_lastname like '%" . tep_db_input($keywords) . "%')";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "c.customers_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ', c.customers_lastname ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "o.orders_id " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "cp.coupon_code " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 4:
                    $orderBy = "lp.loyalty_points " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 5:
                    $orderBy = "lp.date_added " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "sort_order, countries_name";
                    break;
            }
        } else {
            $orderBy = "lp.date_added desc, lp.loyalty_history_id desc";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        $loyalty_query_raw = "select lp.*, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, c.customers_email_address, " .
                "o.orders_id as order_ref_id, " .
                "cp.coupon_code " .
                "from " . TABLE_CUSTOMERS_LOYALTY_HISTORY . " lp " .
                "left join " . TABLE_CUSTOMERS . " c on lp.customers_id=c.customers_id " .
                "left join " . TABLE_ORDERS . " o on lp.orders_id=o.orders_id " .
                "left join " . TABLE_COUPONS . " cp on lp.coupon_id=cp.coupon_id " .
                "where 1 " . $search . 
                ((isset($cID) && $cID > 0) ? "and lp.customers_id='" . (int) $cID . "' " : '') .
                ((isset($filter) && isset($filter_map[$filter])) ? $filter_map[$filter] . " " : '') . " order by " . $orderBy;

        $loyalty_split = new \splitPageResults($current_page_number, $length, $loyalty_query_raw, $loyalty_query_numrows);

        $loyalty_query = tep_db_query($loyalty_query_raw);

        while ($loyalty = tep_db_fetch_array($loyalty_query)) {

            $responseList[] = array(
                $loyalty['customers_name'] . tep_draw_hidden_field('id', $loyalty['loyalty_history_id'], 'class="cell_identify"'),
                $loyalty['loyalty_state'],
                ($loyalty['order_ref_id']>0 ? $loyalty['order_ref_id'] : '--'),
                $loyalty['coupon_code'],
                $loyalty['loyalty_points'],
                $loyalty['date_added'],
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $loyalty_query_numrows,
            'recordsFiltered' => $loyalty_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionView() {
        global $language, $languages_id;

        $loyalty_id = Yii::$app->request->post('loyalty_id', 0);
        $cID = Yii::$app->request->get('cID', 0);
        $filter = Yii::$app->request->get('filter', '');
        $this->layout = false;
        if ($loyalty_id) {
            $loyalty = tep_db_fetch_array(tep_db_query("select lp.*, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, c.customers_email_address, " .
                            "o.orders_id as order_ref_id, " .
                            "cp.coupon_code " .
                            "from " . TABLE_CUSTOMERS_LOYALTY_HISTORY . " lp " .
                            "left join " . TABLE_CUSTOMERS . " c on lp.customers_id=c.customers_id " .
                            "left join " . TABLE_ORDERS . " o on lp.orders_id=o.orders_id " .
                            "left join " . TABLE_COUPONS . " cp on lp.coupon_id=cp.coupon_id " .
                            "where loyalty_history_id = '" . (int) $loyalty_id . "' " .
                            ((isset($cID) && $cID > 0) ? "and lp.customers_id='" . (int) $cID . "' " : '') .
                            ((!empty($filter) && isset($filter_map[$filter])) ? $filter_map[$filter] . " " : '')));

            $lInfo = new \objectInfo($loyalty, false);
        }
        return CustomerLoyalty::renderViewLoyality($lInfo);
    }

    public function actionEdit() {
        global $language, $languages_id;

        $cid = Yii::$app->request->get('cid', 0);
        $get_customer_info = false;
        if ($cid) {
            $get_customer_info_r = tep_db_query(
                    "SELECT c.customers_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, " .
                    "c.customers_email_address " .
                    "FROM " . TABLE_CUSTOMERS . " c " .
                    "WHERE c.customers_id='" . intval($cid) . "'"
            );
            if (tep_db_num_rows($get_customer_info_r)) {
                $get_customer_info = tep_db_fetch_array($get_customer_info_r);
            }
        }

        $customer_variants = array();
        if ($get_customer_info === false) {
            $get_customer_variants_r = tep_db_query(
                    "SELECT c.customers_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, " .
                    "c.customers_email_address " .
                    "FROM " . TABLE_CUSTOMERS . " c " .
                    "ORDER BY customers_name"
            );
            while ($get_customer_variant = tep_db_fetch_array($get_customer_variants_r)) {
                $customer_variants[$get_customer_variant['customers_id']] = $get_customer_variant['customers_name'];
            }
        }
        return CustomerLoyalty::renderEditLoyality($get_customer_info, $customer_variants);
    }

    public function actionSave() {
        global $language;

        $customers_id = (int) Yii::$app->request->post('customers_id');
        $loyalty_points = tep_db_prepare_input(Yii::$app->request->post('loyalty_points'), '');
        $public_comment = tep_db_prepare_input(Yii::$app->request->post('public_comment'), '');
        $internal_comment = tep_db_prepare_input(Yii::$app->request->post('internal_comment'), '');

        if ($customers_id) {
            $sql_array = array(
                'customers_id' => $customers_id,
                'orders_id' => '0',
                'loyalty_state' => CustomerLoyalty::NOT_DISPATCHED_STATE,
                'loyalty_points' => $loyalty_points,
                'loyalty_point_rate' => 1,
                'public_comment' => $public_comment,
                'internal_comment' => $internal_comment,
                'date_added' => 'now()',
            );

            tep_db_perform(TABLE_CUSTOMERS_LOYALTY_HISTORY, $sql_array);
        }

        echo json_encode(array('message' => TEXT_MESSEAGE_SUCCESS, 'messageType' => 'alert-success'));
        
    }
    
    public function actionManualDispatch(){
        $cID = Yii::$app->request->get('cID', 0);
        if ($cID){
            CustomerLoyalty::dispatchPoints((int)$cID);
        }
        return $this->redirect(['customers_loyalty/', 'filter' => 'coupons']);
    }
    
    public function actionResend(){
        $lpID = Yii::$app->request->get('lpID', 0);
        if (CustomerLoyalty::mailDispatch($lpID)){
          Yii::$app->session->setFlash('success', TEXT_MAIL_SENT);
        }else{
          Yii::$app->session->setFlash('error', TEXT_MAIL_NOT_SENT);
        }
        return $this->redirect('index');
    }

}
