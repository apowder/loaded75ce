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

use common\classes\platform_config;
use common\classes\platform;
use common\classes\order;
use common\classes\opc_order;
use common\classes\shopping_cart;
use common\classes\currencies;
use common\classes\order_total;
use common\classes\shipping;
use common\classes\payment;
use common\models\Customer;
use common\helpers\Acl;
use common\helpers\Output;
use backend\models\AdminCarts;
use Yii;

//require(DIR_WS_CLASSES . 'http_client.php');
/**
 * default controller to handle user requests.
 */
class OrdersController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS'];

    /**
     * Index action is the default action in a controller.
     */
    public function __construct($id, $module = '') {
        global $messageStack;
        if (!is_object($messageStack) || !($messageStack instanceof \common\classes\message_stack))
            $messageStack = new \common\classes\message_stack();

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkCustomerGroups')) {
            $ext::checkCustomerGroups();
        }
        define('GROUPS_IS_SHOW_PRICE', true);
        define('GROUPS_DISABLE_CHECKOUT', false);
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        global $languages_id, $language;

        $this->selectedMenu = array('customers', 'orders');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['orders/create', 'back' => 'orders']) . '" class="create_item"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_OREDER . '</a>';
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->ordersTable = array(
            array(
                'title' => '<input type="checkbox" class="uniform">',
                'not_important' => 2
            ),
            array(
                'title' => TABLE_HEADING_CUSTOMERS,
            ),
            array(
                'title' => TABLE_HEADING_ORDER_TOTAL,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DETAILS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_PURCHASED,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 1
            ),
                /* array(
                  'title' => TABLE_HEADING_ACTION,
                  'not_important' => 0
                  ), */
        );


        $this->view->filters = new \stdClass();

        $by = [
            [
                'name' => TEXT_ANY,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_ORDER_ID,
                'value' => 'oID',
                'selected' => '',
            ],
            [
                'name' => TEXT_CUSTOMER_ID,
                'value' => 'cID',
                'selected' => '',
            ],
            [
                'name' => TEXT_MODEL,
                'value' => 'model',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NAME,
                'value' => 'name',
                'selected' => '',
            ],
            /* [
              'name' => 'Brand',
              'value' => 'brand',
              'selected' => '',
              ], */
            [
                'name' => TEXT_CLIENT_NAME,
                'value' => 'fullname',
                'selected' => '',
            ],
            [
                'name' => TEXT_CLIENT_EMAIL,
                'value' => 'email',
                'selected' => '',
            ],
        ];
        foreach ($by as $key => $value) {
            if (isset($_GET['by']) && $value['value'] == $_GET['by']) {
                $by[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->by = $by;

        $search = '';
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        $this->view->filters->search = $search;

        if (isset($_GET['date']) && $_GET['date'] == 'exact') {
            $this->view->filters->presel = false;
            $this->view->filters->exact = true;
        } else {
            $this->view->filters->presel = true;
            $this->view->filters->exact = false;
        }

        $interval = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_TODAY,
                'value' => '1',
                'selected' => '',
            ],
            [
                'name' => TEXT_WEEK,
                'value' => 'week',
                'selected' => '',
            ],
            [
                'name' => TEXT_THIS_MONTH,
                'value' => 'month',
                'selected' => '',
            ],
            [
                'name' => TEXT_THIS_YEAR,
                'value' => 'year',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_THREE_DAYS,
                'value' => '3',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_SEVEN_DAYS,
                'value' => '7',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_FOURTEEN_DAYS,
                'value' => '14',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_THIRTY_DAYS,
                'value' => '30',
                'selected' => '',
            ],
        ];
        foreach ($interval as $key => $value) {
            if (isset($_GET['interval']) && $value['value'] == $_GET['interval']) {
                $interval[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->interval = $interval;

        $status = [];
        $status[] = [
            'name' => TEXT_ALL_ORDERS,
            'value' => '',
            'selected' => '',
        ];
        $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "' order by orders_status_groups_id");
        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
            $status[] = [
                'name' => $orders_status_groups['orders_status_groups_name'],
                'value' => 'group_' . $orders_status_groups['orders_status_groups_id'],
                'selected' => '',
            ];
            $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "' and orders_status_groups_id='" . (int)$orders_status_groups['orders_status_groups_id'] . "' order by orders_status_name");
            while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                $status[] = [
                    'name' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $orders_status['orders_status_name'],
                    'value' => 'status_' . $orders_status['orders_status_id'],
                    'selected' => '',
                ];
            }
        }
        foreach ($status as $key => $value) {
            if (isset($_GET['status']) && $value['value'] == $_GET['status']) {
                $status[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->status = $status;

        $payments = [];
        $payments[] = [
            'name' => TEXT_ANY,
            'value' => '',
            'selected' => '',
        ];
        $payment_method_query = tep_db_query("select payment_method from " . TABLE_ORDERS . " where 1 group by payment_method order by payment_method");
        while ($payment_method = tep_db_fetch_array($payment_method_query)) {
            $payments[] = [
                'name' => $payment_method['payment_method'],
                'value' => $payment_method['payment_method'],
                'selected' => '',
            ];
        }
        foreach ($payments as $key => $value) {
            if (isset($_GET['payments']) && $value['value'] == $_GET['payments']) {
                $payments[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->payments = $payments;

        $shipping = [];
        $shipping[] = [
            'name' => TEXT_ANY,
            'value' => '',
            'selected' => '',
        ];
        $shipping_method_query = tep_db_query("select shipping_method from " . TABLE_ORDERS . " where 1 group by shipping_method order by shipping_method");
        while ($shipping_method = tep_db_fetch_array($shipping_method_query)) {
            $shipping[] = [
                'name' => $shipping_method['shipping_method'],
                'value' => $shipping_method['shipping_method'],
                'selected' => '',
            ];
        }
        foreach ($shipping as $key => $value) {
            if (isset($_GET['shipping']) && $value['value'] == $_GET['shipping']) {
                $shipping[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->shipping = $shipping;

        $delivery_country = '';
        if (isset($_GET['delivery_country'])) {
            $delivery_country = $_GET['delivery_country'];
        }
        $this->view->filters->delivery_country = $delivery_country;

        $delivery_state = '';
        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $this->view->showState = true;
        } else {
            $this->view->showState = false;
        }
        if (isset($_GET['delivery_state'])) {
            $delivery_state = $_GET['delivery_state'];
        }
        $this->view->filters->delivery_state = $delivery_state;

        $from = '';
        if (isset($_GET['from'])) {
            $from = $_GET['from'];
        }
        $this->view->filters->from = $from;

        $to = '';
        if (isset($_GET['to'])) {
            $to = $_GET['to'];
        }
        $this->view->filters->to = $to;

        $this->view->filters->row = (int) $_GET['row'];
        $fs = 'closed';
        if (isset($_GET['fs'])) {
            $fs = $_GET['fs'];
        }
        $this->view->filters->fs = $fs;

        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }
        
        $admin = new AdminCarts;
        $admin->loadCustomersBaskets();
        $ids = $admin->getVirtualCartIDs();
        $this->view->filters->admin_choice = [];
        if ($ids) {
            foreach ($ids as $_ids) {
                $this->view->filters->admin_choice[] = $this->renderAjax('mini', [
                    'ids' => $_ids,
                    'customer' => \common\helpers\Customer::getCustomerData($_ids)]
                );
            }
        }

        return $this->render('index', [
                    'isMultiPlatform' => \common\classes\platform::isMulti(),
                    'platforms' => \common\classes\platform::getList(),
        ]);
    }

    public function actionOrderHistory() {
        $this->layout = false;

        $orders_id = Yii::$app->request->get('orders_id');

        \common\helpers\Translation::init('admin/orders');

        $params = [];

        $history = [];
        $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_HISTORY . " where orders_id='" . (int) $orders_id . "' order by orders_history_id desc");
        while ($orders_history = tep_db_fetch_array($orders_history_query)) {
            $history[] = [
                'date' => \common\helpers\Date::datetime_short($orders_history['date_added']),
                'comments' => $orders_history['comments'], //Edited by Name of admin
            ];
        }


        $cid = Yii::$app->request->get('cid', 0);

        \common\helpers\Translation::init('admin/recover_cart_sales');

        $params['history'] = $history;

        if (RCS_SHOW_AT_ORDERS == 'true' && $orders_id && $cid) {
            $ga = tep_db_fetch_array(tep_db_query("select * from " . TABLE_GA . " where orders_id='" . (int) $orders_id . "'"));
            $params['ga'] = $params['ua'] = [];
            if ($ga) {
                $obj = new \StdClass();
                $sz = unserialize($ga['user_agent']);
                $obj->agent_name = @$sz['browser'] . ' ' . @$sz['version'];
                $obj->os_name = @$sz['platform'];

                $ga['origin'] = (tep_not_null($ga['utmgclid']) && $ga['utmgclid'] == 'recoveryemail' ? BOX_TOOLS_RECOVER_CART : $ga['utmcsr']);
                $ga['java'] = tep_not_null($ga['resolution']) ? TEXT_BTN_YES : TEXT_BTN_NO;
                $params['ga'] = $ga;
                $params['ua'] = $obj;
            }

            //errors
            $errors = tep_db_query("select ce.* from " . TABLE_CUSTOMERS_ERRORS . " ce inner join " . TABLE_ORDERS . " o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = ce.basket_id and ce.customers_id = '" . (int) $cid . "' order by error_date desc");
            if (tep_db_num_rows($errors)) {
                $_errors = [];
                while ($er = tep_db_fetch_array($errors)) {
                    $_errors[] = $er;
                }
                $params['errors'] = $_errors;
            }
            tep_db_free_result($errors);
            //contacts
            $scart = tep_db_query("select * from " . TABLE_SCART . " s inner join " . TABLE_ORDERS . " o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = s.basket_id and s.customers_id = '" . (int) $cid . "'");
            if (tep_db_num_rows($scart)) {
                $_scart = tep_db_fetch_array($scart);
                $_scart['recovered'] = $_scart['recovered'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['contacted'] = $_scart['contacted'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['workedout'] = $_scart['workedout'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $params['scart'] = $_scart;
                //gv && cc
                $coupons = tep_db_query("select cet.coupon_id, cet.sent_firstname, cet.sent_lastname, cet.date_sent, c.coupon_code, c.coupon_amount, c.coupon_currency, c.coupon_type, c.coupon_active from " . TABLE_COUPON_EMAIL_TRACK . " cet left join " . TABLE_COUPONS . " c on c.coupon_id = cet.coupon_id inner join " . TABLE_ORDERS . " o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = cet.basket_id and cet.customer_id_sent = '" . (int) $cid . "'");
                if (tep_db_num_rows($coupons)) {
                    $_cops = [];
                    $currencies = new \common\classes\currencies();
                    while ($cop = tep_db_fetch_array($coupons)) {
                        $_cops[$cop['coupon_id']] = $cop;
                        $_cops[$cop['coupon_id']]['coupon_amount'] = $cop['coupon_code'] . ' (' . ($cop['coupon_type'] == 'F' || $cop['coupon_type'] == 'G' ? $currencies->format($cop['coupon_amount'], false, $cop['coupon_currency']) : ($cop['coupon_type'] == 'P' ? round($cop['coupon_amount'], 2) . '%' : '')) . ') ' . ($cop['coupon_type'] == 'G' && $cop['coupon_active'] == 'N' ? TEXT_USED : '') . ' - ' . $cop['sent_firstname'] . ' ' . $cop['sent_lastname'];
                        $_cops[$cop['coupon_id']]['coupon_type'] = ($cop['coupon_type'] == 'G' ? GIFT_CERTIFICATE : DISCOUNT_COUPON);
                    }
                    $params['coupons'] = $_cops;
                }
                tep_db_free_result($coupons);
            }
        }
        return $this->renderAjax('recovery', $params);

        //return $this->render('order-history.tpl');
    }

    public function actionOrderlist() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/orders');

        //include(DIR_WS_CLASSES . 'order.php');

        $draw = Yii::$app->request->get('draw');
        $start = Yii::$app->request->get('start');
        $length = Yii::$app->request->get('length');

        if ($length == -1)
            $length = 10000;

        $_session = Yii::$app->session;

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " and (o.customers_lastname like '%" . $keywords . "%' or o.customers_firstname like '%" . $keywords . "%' or o.customers_email_address like '%" . $keywords . "%' or o.orders_id='" . $keywords . "' or op.products_model like '%" . tep_db_input($keywords) . "%' or op.products_name like '%" . tep_db_input($keywords) . "%') ";
        } else {
            $search_condition = "";
        }
        $_session->set('search_condition', $search_condition);

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $filter = '';

        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        }

        if (count($filter_by_platform) > 0) {
            $filter .= " and o.platform_id IN ('" . implode("', '", $filter_by_platform) . "') ";
        }

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            switch ($output['by']) {
                case 'cID':
                    $filter .= " and o.customers_id = '" . (int) $search . "' ";
                    break;
                case 'oID':
                    $filter .= " and o.orders_id = '" . (int) $search . "' ";
                    break;
                case 'model': default:
                    $filter .= " and op.products_model like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'name':
                    $filter .= " and op.products_name like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'brand':
                    break;
                case 'fullname':
                    $filter .= " and o.customers_name like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'email':
                    $filter .= " and o.customers_email_address like '%" . tep_db_input($search) . "%' ";
                    break;
                case '':
                case 'any':
                    $filter .= " and (";
                    $filter .= " o.orders_id = '" . tep_db_input($search) . "' ";
                    $filter .= " or op.products_model like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or op.products_name like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or o.customers_name like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or o.customers_email_address like '%" . tep_db_input($search) . "%' ";
                    $filter .= ") ";
                    break;
            }
        }
        if (tep_not_null($output['delivery_country'])) {
            $filter .= " and o.delivery_country='" . tep_db_input($output['delivery_country']) . "'";
        }
        if (tep_not_null($output['delivery_state'])) {
            $filter .= " and o.delivery_state='" . tep_db_input($output['delivery_state']) . "'";
        }
        if (tep_not_null($output['status'])) {
            list($type, $itemId) = explode("_", $output['status']);
            switch ($type) {
                case 'group':
                    $filter .= " and s.orders_status_groups_id = '" . (int) $itemId . "' ";
                    break;
                case 'status':
                    $filter .= " and s.orders_status_id = '" . (int) $itemId . "' ";
                    break;

                default:
                    break;
            }
        }
        if (tep_not_null($output['date'])) {
            switch ($output['date']) {
                case 'exact':
                    if (tep_not_null($output['from'])) {
                        $from = tep_db_prepare_input($output['from']);
                        $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $from);
                        $filter .= " and to_days(o.date_purchased) >= to_days('" . $date->format('Y-m-d') . "')";
                    }
                    if (tep_not_null($output['to'])) {
                        $to = tep_db_prepare_input($output['to']);
                        $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $to);
                        $filter .= " and to_days(o.date_purchased) <= to_days('" . $date->format('Y-m-d') . "')";
                    }
                    break;
                case 'presel':
                    if (tep_not_null($output['interval'])) {
                        switch ($output['interval']) {
                            case 'week':
                                $filter .= " and o.date_purchased >= '" . date('Y-m-d', strtotime('monday this week')) . "'";
                                break;
                            case 'month':
                                $filter .= " and o.date_purchased >= '" . date('Y-m-d', strtotime('first day of this month')) . "'";
                                break;
                            case 'year':
                                $filter .= " and o.date_purchased >= '" . date("Y") . "-01-01" . "'";
                                break;
                            case '1':
                                $filter .= " and o.date_purchased >= '" . date('Y-m-d') . "'";
                                break;
                            case '3':
                            case '7':
                            case '14':
                            case '30':
                                $filter .= " and o.date_purchased >= date_sub(now(), interval " . (int) $output['interval'] . " day)";
                                break;
                        }
                    }
                    break;
            }
        }

        if (tep_not_null($output['payments'])) {
            $filter .= " and o.payment_method='" . tep_db_input($output['payments']) . "'";
        }

        if (tep_not_null($output['shipping'])) {
            $filter .= " and o.shipping_method='" . tep_db_input($output['shipping']) . "'";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir'] && $_GET['draw'] != 1) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "o.customers_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "ot.text " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "o.date_purchased " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 3:
                    $orderBy = "s.orders_status_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "o.orders_id desc";
                    break;
            }
        } else {
            $orderBy = "o.orders_id desc";
        }

        $_session->set('filter', $filter);

        $CutOffTime = new \common\classes\CutOffTime();

        //if (isset($_GET['cID'])) {
        //$cID = tep_db_prepare_input($_GET['cID']);
        //$orders_query_raw = "select o.settlement_date, o.approval_code, o.last_xml_export, o.customers_postcode, o.customers_street_address, o.customers_city, o.customers_state, o.customers_country, o.transaction_id, o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, sg.orders_status_groups_name, sg.orders_status_groups_color, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS_STATUS_GROUPS  . " sg, ". TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.customers_id = '" . (int) $cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' and s.orders_status_groups_id = sg.orders_status_groups_id and sg.language_id = '" . (int)$languages_id . "' " . (tep_session_is_registered('login_affiliate') ? " and asales.affiliate_orders_id = o.orders_id and asales.affiliate_id = '" . $login_id . "'" : '') . " and ot.class = 'ot_total' " . $search_condition . " order by " . $orderBy;
        //} else { 
//            $orders_query_raw = "select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' " . (tep_session_is_registered('login_affiliate') ? " and asales.affiliate_orders_id = o.orders_id and asales.affiliate_id = '" . $login_id . "'" : '') . " and ot.class = 'ot_total' " . $search_condition . " order by " . $orderBy;
        $orders_query_raw = "select o.delivery_date, o.settlement_date, o.platform_id, c.customers_gender, o.approval_code, o.last_xml_export, o.customers_postcode, o.customers_street_address, o.customers_city, o.customers_state, o.customers_country, o.transaction_id, o.orders_status, o.orders_id, o.customers_id, o.customers_name, o.customers_email_address, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, sg.orders_status_groups_name, sg.orders_status_groups_color, ot.text_inc_tax as order_total " . ((tep_not_null($_GET['in_stock']) && $_GET['in_stock'] != '') ? ", BIT_AND(" . (PRODUCTS_INVENTORY == 'True' ? "if(i.products_quantity is not null,if((i.products_quantity>=op.products_quantity),1,0),if((p.products_quantity>=op.products_quantity),1,0))" : "if((p.products_quantity>=op.products_quantity),1,0)") . ") as in_stock " : '') . " from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS_STATUS_GROUPS . " sg, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') left join " . TABLE_ORDERS_PRODUCTS . " op on (op.orders_id = o.orders_id) " . ((tep_not_null($_GET['in_stock']) && $_GET['in_stock'] != '') ? "left join " . TABLE_PRODUCTS . " p on (p.products_id = op.products_id) " . (PRODUCTS_INVENTORY == 'True' ? " left join " . TABLE_INVENTORY . " i on (i.prid = op.products_id and i.products_id = op.uprid) " : '') : '') . " LEFT JOIN  " . TABLE_CUSTOMERS . " c on (o.customers_id = c.customers_id) where o.orders_status = s.orders_status_id " . $search_condition . " and s.language_id = '" . (int) $languages_id . "' and s.orders_status_groups_id = sg.orders_status_groups_id and sg.language_id = '" . (int) $languages_id . "' " . (tep_session_is_registered('login_affiliate') ? " and asales.affiliate_orders_id = o.orders_id and asales.affiliate_id = '" . (int) $login_id . "'" : (tep_not_null($_GET['affiliate_id']) ? " and asales.affiliate_orders_id = o.orders_id and asales.affiliate_id = '" . (int) $_GET['affiliate_id'] . "'" : '')) . $filter . " group by o.orders_id " . ((tep_not_null($_GET['in_stock']) && $_GET['in_stock'] != '') ? " having in_stock " . ($_GET['in_stock'] > 0 ? " > 0" : " < 1") : '') . " order by " . $orderBy;
        //}
        //and o.customers_id = c.customers_id
        //   echo $orders_query_raw;
        $current_page_number = ($start / $length) + 1;
        $orders_split = new \splitPageResults($current_page_number, $length, $orders_query_raw, $orders_query_numrows, 'o.orders_id');
        $orders_query = tep_db_query($orders_query_raw);
        $responseList = array();
        $stack = [];
        while ($orders = tep_db_fetch_array($orders_query)) {
            $products_query = tep_db_query("select products_id, products_model, products_name, products_price, final_price, products_tax, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $orders['orders_id'] . "'");
            $p_list = '';
            $p_list2 = '';
            $counter = 0;
            $max_view = MAX_PRODUCTS_IN_ORDERS;
            while ($products = tep_db_fetch_array($products_query)) {
                $products['products_name'] = htmlentities($products['products_name']);
                $counter++;
                $p_list_tmp = '<div class="ord-desc-row"><div>' . $products['products_quantity'] . ' x ' . (strlen($products['products_name']) > 48 ? substr($products['products_name'], 0, 48) . '...' : $products['products_name']) . '</div><div class="order_pr_model">' . 'SKU: ' . (strlen($products['products_model']) > 8 ? substr($products['products_model'], 0, 8) . '...' : $products['products_model']) . ($products['products_model'] ? '<span>' . $products['products_model'] . '</span>' : '') . '</div></div>';
                if ($counter <= $max_view) {
                    $p_list .= $p_list_tmp;
                }
                if ($counter == $max_view + 1) {
                    $p_list2 = $p_list_tmp;
                }
            }
            if ($counter == $max_view + 1) {
                $p_list .= $p_list2;
            }
            if ($counter > $max_view + 1) {
                $p_list .= '<div class="ord-desc-row ord-desc-row-more"><div>...</div></div>';
                $p_list .= '<div class="ord-desc-row ord-desc-row-more"><div>' . $max_view . ' ' . TEXT_OF_TOTAL . ' ' . $counter . '</div></div>';
            }

            $deliveryInfo = '';
            $timestamp = strtotime($orders['date_purchased']);
            if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'showDeliveryDate')){
                $deliveryInfo = $ext::showDeliveryDate($orders['delivery_date']);
            }
            if (date('Y-m-d', $timestamp) == date('Y-m-d') && strlen($deliveryInfo) == 0) {
                $deliveryInfo .= '</div><div class="ord-date-purch-delivery' . ($CutOffTime->isTodayDelivery($orders['date_purchased'], $orders['platform_id']) ? ' ord-date-purch-delivery-check' : '') . '">' . TEXT_TODAY_DELIVERY . ':</div><div class="ord-date-purch-delivery' . ($CutOffTime->isNextDayDelivery($orders['date_purchased'], $orders['platform_id']) ? ' ord-date-purch-delivery-check' : '') . '">' . TEXT_NEXT_DELIVERY . ':</div>';
            }

            //------
            $customers_email_address = $orders['customers_email_address'];
            $w = preg_quote(trim($search));
            if (!empty($w)) {
                $regexp = "/($w)(?![^<]+>)/i";
                $replacement = '<b style="color:#ff0000">\\1</b>';
                $orders['customers_name'] = preg_replace($regexp, $replacement, $orders['customers_name']);
                $p_list = preg_replace($regexp, $replacement, $p_list);
                $customers_email_address = preg_replace($regexp, $replacement, $orders['customers_email_address']);
            }
            //------
            $orderTotals = '';
            $totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $orders['orders_id'] . "' order by sort_order");

            while ($totals = tep_db_fetch_array($totals_query)) {
                if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals['class'] . '.php')) {
                    include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals['class'] . '.php');
                }
                if (class_exists($totals['class'])) {
                    if (!array_key_exists($totals['class'], $stack)) {
                        $stack[$totals['class']] = new $totals['class'];
                    }
                    $object = $stack[$totals['class']];
                    if (!is_object($object)) {
                        $object = new $totals['class'];
                    }

                    if (method_exists($object, 'visibility')) {
                        if (true == $object->visibility(platform::defaultId(), 'TEXT_ADMINORDER')) {
                            if (method_exists($object, 'visibility')) {
                                $result = $object->displayText(platform::defaultId(), 'TEXT_ADMINORDER', $totals);
                                $orderTotals .= '<div class="' . $result['class'] . ($result['show_line'] ? ' totals-line' : '') . '"><span>' . $result['title'] . '</span><span>' . $result['text'] . '</span></div>';
                            } else {
                                $orderTotals .= '<div><span>' . $totals['title'] . '</span><span>' . $totals['text'] . '</span></div>';
                            }
                        }
                    }
                }
            }

            $responseList[] = array(
                '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">',
                '<div class="ord-name ord-gender ord-gender-' . $orders['customers_gender'] . ' click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $orders['customers_id']]) . '">' . $orders['customers_name'] . '</a></div><a href="mailto:' . $orders['customers_email_address'] . '" class="ord-name-email">' . $customers_email_address . '</a><div class="ord-location" style="margin-top: 5px;">' . $orders['customers_postcode'] . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . $orders['customers_name'] . '</b>' . $orders['customers_street_address'] . '<br>' . $orders['customers_city'] . ', ' . $orders['customers_state'] . '&nbsp;' . $orders['customers_postcode'] . '<br>' . $orders['customers_country'] . '</div></div>',
                '<div class="ord-total click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $orders['order_total'] . '<div class="ord-total-info"><div class="ord-box-img"></div>' . $orderTotals . '</div></div>',
                '<div class="ord-desc-tab click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><span class="ord-id">' . TEXT_ORDER_NUM . $orders['orders_id'] . (admin_id > 0 ? '&nbsp;by admin' : (\common\classes\platform::isMulti() >= 0 ? '&nbsp;' . TEXT_FROM . ' ' . \common\classes\platform::name($orders['platform_id']) : '')) . (tep_not_null($orders['payment_method']) ? ' ' . TEXT_VIA . ' ' . strip_tags($orders['payment_method']) : '') . '</span></a>' . $p_list . '</div>',
                '<div class="ord-date-purch click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . \common\helpers\Date::datetime_short($orders['date_purchased']) . $deliveryInfo,
                '<div class="ord-status click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><span><i style="background: ' . $orders['orders_status_groups_color'] . ';"></i>' . $orders['orders_status_groups_name'] . '</span><div>' . $orders['orders_status_name'] . '</div></div>'
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_query_numrows,
            'recordsFiltered' => $orders_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
        //die();
    }

    public function actionOrderactions() {

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.platform_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.language_id, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Please select order.");
        }

        $oInfo = new \objectInfo($orders);

        echo '<div class="or_box_head">' . TEXT_ORDER_NUM . $oInfo->orders_id . '</div>';
        echo '<div class="row_or"><div>' . TEXT_DATE_ORDER_CREATED . '</div><div>' . \common\helpers\Date::datetime_short($oInfo->date_purchased) . '</div></div>';
        if (tep_not_null($oInfo->last_modified))
            echo '<div class="row_or"><div>' . TEXT_DATE_ORDER_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($oInfo->last_modified) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_PAYMENT_METHOD . '</div><div>' . $oInfo->payment_method . '</div></div>';
        echo '<div class="btn-toolbar btn-toolbar-order"><a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $oInfo->orders_id]) . '">' . TEXT_PROCESS_ORDER_BUTTON . '</a><span class="disable_wr"><span class="dis_popup"><span class="dis_popup_img"></span><span class="dis_popup_content">' . TEXT_COMPLITED . '</span></span><a class="btn btn-no-margin btn-edit" href="' . \Yii::$app->urlManager->createUrl(['orders/order-edit', 'orders_id' => $oInfo->orders_id]) . '">' . IMAGE_EDIT . '</a></span>' . (!tep_session_is_registered('login_affiliate') ? '<button class="btn btn-delete" onclick="confirmDeleteOrder(' . $oInfo->orders_id . ')">' . IMAGE_DELETE . '</button><br>' : '') . '<a href="' . \Yii::$app->urlManager->createUrl(['../email-template/invoice', 'orders_id' => $oInfo->orders_id, 'platform_id' => $oInfo->platform_id, 'language' => \common\classes\language::get_code($oInfo->language_id)]) . '" TARGET="_blank" class="btn btn-no-margin">' . TEXT_INVOICE . '</a><a href="' . \Yii::$app->urlManager->createUrl(['../email-template/packingslip', 'orders_id' => $oInfo->orders_id, 'platform_id' => $oInfo->platform_id, 'language' => \common\classes\language::get_code($oInfo->language_id)]) . '" TARGET="_blank" class="btn">' . IMAGE_ORDERS_PACKINGSLIP . '</a><input type="button" class="btn btn-primary btn-process-order" value="' . IMAGE_REASSIGN . '" onclick="reassignOrder(' . $oInfo->orders_id . ')"></div>';
    }

    public function actionOrderReassign() {
        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Wrong order data.");
        }

        $oInfo = new \objectInfo($orders);

        echo tep_draw_form('orders', 'orders', \common\helpers\Output::get_all_get_params(array('action')) . 'action=confirmed-order-reassign', 'post', 'id="orders_edit" onSubmit="return confirmedReassignOrder();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_REASSIGN_ORDER . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_REASSIGN_INTRO . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA . ':</div><div>' . $oInfo->customers_name . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA_OID . ':</div><div>' . $oInfo->orders_id . '</div></div>';
        echo '</div>';
        //echo '<div class="col_desc_check">'.tep_draw_checkbox_field('restock').'<span>'.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY .'</span></div>';
        echo '<div class="customer_in auto-wrapp" style="position: relative; width: 100%;">';
        echo '<input value="" name="keywords" id="selectCustomer" autocomplete="off" type="text">';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_REASSIGN . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()">';
        echo tep_draw_hidden_field('orders_id', $oInfo->orders_id);
        echo '</div>';
        echo '<input type="hidden" name="customers_id" id="customers_id" value="0">';
        echo '</form>';
        ?>
        <script type="text/javascript">
            (function ($) {
                $(function () {
                    $('#selectCustomer').autocomplete({
                        source: "<?php echo \Yii::$app->urlManager->createUrl('orders/customer') ?>",
                        minLength: 0,
                        autoFocus: true,
                        delay: 0,
                        appendTo: '.auto-wrapp',
                        open: function (e, ui) {
                            if ($(this).val().length > 0) {
                                var acData = $(this).data('ui-autocomplete');
                                acData.menu.element.find('a').each(function () {
                                    var me = $(this);
                                    var keywords = acData.term.split(' ').join('|');
                                    me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                                });
                            }
                        },
                        select: function (event, ui) {
                            if (ui.item.id != null) {
                                $("#customers_id").val(ui.item.id);
                            }
                        },
                    }).focus(function () {
                        $(this).autocomplete("search");
                    });
                })
            })(jQuery)
        </script>
        <?php
    }

    public function actionConfirmedOrderReassign() {
        $customers_id = Yii::$app->request->post('customers_id');
        $orders_id = Yii::$app->request->post('orders_id');

        $customers_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);
        if (is_array($customers) && $orders_id > 0) {
            tep_db_query("update " . TABLE_ORDERS . " set customers_id = '" . (int) $customers_id . "', customers_name = '" . tep_db_input($customers['customers_firstname'] . ' ' . $customers['customers_lastname']) . "', customers_firstname = '" . tep_db_input($customers['customers_firstname']) . "', customers_lastname = '" . tep_db_input($customers['customers_lastname']) . "', customers_email_address = '" . tep_db_input($customers['customers_email_address']) . "' where orders_id = '" . (int) $orders_id . "';");
        }
    }

    public function actionProcessOrder() {

        global $currencies, $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/orders');

        $this->selectedMenu = array('customers', 'orders');
        //$this->layout = false;

        if (Yii::$app->request->isPost) {
            $oID = Yii::$app->request->post('orders_id');
        } else {
            $oID = Yii::$app->request->get('orders_id');
        }

        $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'");
        if (!tep_db_num_rows($orders_query)) {
            return $this->redirect(\Yii::$app->urlManager->createUrl(['orders/', 'by' => 'oID', 'search' => (int) $oID]));
        }

        ob_start();

        $orders_statuses = array();
        $orders_status_array = array();
        $orders_status_group_array = array();
        $orders_status_query = tep_db_query("select os.orders_status_id, os.orders_status_name, osg.orders_status_groups_name, osg.orders_status_groups_color, os.automated from " . TABLE_ORDERS_STATUS . " as os left join " . TABLE_ORDERS_STATUS_GROUPS . " as osg ON os.orders_status_groups_id = osg.orders_status_groups_id where os.language_id = '" . (int) $languages_id . "' and osg.language_id = '" . (int) $languages_id . "' and osg.orders_status_groups_id != '6'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            if ($orders_status['automated'] == 0) {
                $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                    'text' => $orders_status['orders_status_name']);
            }
            $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
            $orders_status_group_array[$orders_status['orders_status_id']] = '<i style="background: ' . $orders_status['orders_status_groups_color'] . ';"></i>' . $orders_status['orders_status_groups_name'];
        }

        $currencies = new \common\classes\currencies();

        $order = new order($oID);
        ?>
        <?php echo tep_draw_form('status', FILENAME_ORDERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update_order', 'post', 'id="status_edit" onSubmit="return check_form();"'); ?>

        <?php /* if (($order->info['orders_status']==DEFAULT_ORDERS_STATUS_ID) && ($order->info['transaction_id']==0) && !tep_session_is_registered("login_affiliate")) echo '<a href="' . \Yii::$app->urlManager->createUrl(['orders/order-edit', 'orders_id' => $oID]) . '" class="btn btn-no-margin btn-edit">' . IMAGE_EDIT . '</a> &nbsp; '; */ ?>

        <div class="widget box box-no-shadow">
            <div class="widget-header widget-header-address">
                <h4><?php echo T_ADD_DET; ?></h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div id="order_management_data" class="widget-content fields_style">
                <div class="pr-add-det-wrapp after <?php echo ($order->delivery['address_book_id'] == $order->billing['address_book_id'] ? 'pr-add-det-wrapp2' : '') ?>">
                    <div class="pr-add-det-box pr-add-det-box01 after">
                        <?php
                        if ($order->delivery['address_book_id'] != $order->billing['address_book_id']) {
                            ?>           
                            <div class="cr-ord-cust">
                                <span><?php echo T_CUSTOMER; ?></span>
                                <div><?php echo '<a href="' . Yii::$app->urlManager->createUrl(['customers/customeredit?customers_id=' . $order->customer['customer_id']]) . '">' . \common\helpers\Address::address_format($order->customer['format_id'], $order->customer, 1, '', '<br>') . '</a>'; ?></div>
                            </div>
                            <?php
                        }
                        ?>                

                        <?php
                        if ($order->delivery['address_book_id'] == $order->billing['address_book_id']) {
                            ?>                
                            <div class="cr-ord-cust">
                                <span><?php echo T_CUSTOMER; ?></span>
                                <div><?php echo '<a href="' . Yii::$app->urlManager->createUrl(['customers/customeredit?customers_id=' . $order->customer['customer_id']]) . '">' . $order->customer['name'] . '</a>'; ?></div>
                            </div>
                            <?php
                        }

                        $key = tep_db_fetch_array(tep_db_query("select info as setting_code from " . TABLE_GOOGLE_SETTINGS . " where module='mapskey'"));
                        ?>                
                        <div class="cr-ord-cust cr-ord-cust-phone">
                            <span><?php echo ENTRY_TELEPHONE_NUMBER; ?></span>
                            <div><?php echo $order->customer['telephone']; ?></div>
                        </div>
                        <div class="cr-ord-cust cr-ord-cust-email">
                            <span><?php echo ENTRY_EMAIL_ADDRESS; ?></span>
                            <div><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></div>
                        </div>
                    </div>
                    <?php
                    if ($order->delivery['postcode'] != $order->billing['postcode']) {
                        $zoom_d = max((int) $order->delivery['country']['zoom'], 8);
                        $zoom_b = max((int) $order->billing['country']['zoom'], 8);
                        ?>
                        <div class="pr-add-det-box pr-add-det-box02 after">
                            <div class="pra-sub-box after">
                                <div class="pra-sub-box-map">
                                    <div class="cr-ord-cust cr-ord-cust-saddress">
                                        <span><?php echo T_SHIP_ADDRESS; ?></span>
                                        <div><?php echo \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></div>
                                    </div>
                                    <div class="cr-ord-cust cr-ord-cust-smethod">
                                        <span><?php echo T_SHIP_METH; ?></span>
                                        <div><?php echo $order->info['shipping_method']; ?></div>
                                        <?php
                                        if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'showDeliveryDate')){
                                            echo $ext::showDeliveryDate($order->info['delivery_date']);
                                        }
                                        ?>
                                        <div class="tracking_number"><a href="<?php echo \Yii::$app->urlManager->createUrl(['orders/gettracking?orders_id=' . (int) $oID]) ?>" class="edit-tracking"><i class="icon-pencil"></i></a><?php echo '<span class="tracknum">' . ($order->info['tracking_number'] ? '<a href="' . TRACKING_NUMBER_URL . $order->info['tracking_number'] . '" target="_blank">' . $order->info['tracking_number'] . '</a>' : TEXT_TRACKING_NUMBER) . '</span>'; ?></div>
                                    </div>
                                    <div class="barcode">
                                        <?php if (tep_not_null($order->info['tracking_number'])) { ?>
                                            <a href="<?php echo TRACKING_NUMBER_URL . $order->info['tracking_number']; ?>" target="_blank"><img alt="<?php echo $order->info['tracking_number']; ?>" src="<?php echo HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'account/order-qrcode?oID=' . (int) $oID . '&cID=' . (int) $order->customer['customer_id'] . '&tracking=1'; ?>"></a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="pra-sub-box-map">
                                    <div id="floating-panel">
                                        <input id="hid-add1" type="hidden" value="<?php echo /* $order->delivery['postcode'] . ' ' . */$order->delivery['street_address'] . ' ' . $order->delivery['city'] . ' ' . $order->delivery['country']['title']; ?>">
                                        <input id="hid-add1-zip" type="hidden" value="<?php echo $order->delivery['postcode']; ?>">
                                    </div>
                                    <div class="gmaps-wrap"><div id="gmap_markers1" class="gmaps"></div></div>
                                </div>                    
                            </div>
                            <div class="pra-sub-box after">
                                <div class="pra-sub-box-map">
                                    <div class="cr-ord-cust cr-ord-cust-baddress">
                                        <span><?php echo TEXT_BILLING_ADDRESS; ?></span>
                                        <div><?php echo \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></div>
                                    </div>
                                    <div class="cr-ord-cust cr-ord-cust-bmethod">
                                        <span><?php echo T_BILL_METH; ?></span>
                                        <div><?php echo $order->info['payment_method']; ?></div>
                                    </div>
                                </div>
                                <div class="pra-sub-box-map">
                                    <div id="floating-panel">
                                        <input id="hid-add2" type="hidden" value="<?php echo /* $order->billing['postcode'] . ' ' . */ $order->billing['street_address'] . ' ' . $order->billing['city'] . ' ' . $order->billing['country']['title']; ?>">
                                        <input id="hid-add2-zip" type="hidden" value="<?php echo $order->billing['postcode']; ?>">
                                    </div>
                                    <div class="gmaps-wrap"><div id="gmap_markers2" class="gmaps"></div></div>
                                    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $key['setting_code']; ?>&callback=initMap" async defer></script>
                                    <script>
                $(function () {
                    var click_map = false;
                    $('body').on('click', function () {
                        setTimeout(function () {
                            if (click_map) {
                                $('.map_dashboard-hide').remove()
                            } else {
                                if (!$('.map_dashboard-hide').hasClass('map_dashboard-hide')) {
                                    $('.gmaps-wrap').append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
                                }
                            }
                            click_map = false
                        }, 200)
                    });
                    $('.gmaps-wrap')
                            .css('position', 'relative')
                            .append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
                            .on('click', function () {
                                setTimeout(function () {
                                    click_map = true
                                }, 100)
                            })
                });

                function initMap() {
                    var map1 = new google.maps.Map(document.getElementById('gmap_markers1'), {
                        zoom: <?= $zoom_d ?>,
                        center: {lat: -34.397, lng: 150.644}
                    });
                    var map2 = new google.maps.Map(document.getElementById('gmap_markers2'), {
                        zoom: <?= $zoom_b ?>,
                        center: {lat: -34.397, lng: 150.644}
                    });
                    var geocoder = new google.maps.Geocoder();

                    geocodeAddress1(geocoder, map1);
                    geocodeAddress2(geocoder, map2);
                }

                function geocodeAddress1(geocoder, resultsMap) {
                    var address1 = document.getElementById('hid-add1').value;
                    geocoder.geocode({'address': address1}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            resultsMap.setCenter(results[0].geometry.location);
                            var marker = new google.maps.Marker({
                                map: resultsMap,
                                position: results[0].geometry.location
                            });
                        } else {
                            address2 = document.getElementById('hid-add1-zip').value;
                            geocoder.geocode({'address': address2}, function (results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    resultsMap.setCenter(results[0].geometry.location);
                                    var marker = new google.maps.Marker({
                                        map: resultsMap,
                                        position: results[0].geometry.location
                                    });
                                }
                            });
                        }
                    });
                }

                function geocodeAddress2(geocoder, resultsMap) {
                    var address2 = document.getElementById('hid-add2').value;
                    geocoder.geocode({'address': address2}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            resultsMap.setCenter(results[0].geometry.location);
                            var marker = new google.maps.Marker({
                                map: resultsMap,
                                position: results[0].geometry.location
                            });
                        } else {
                            address2 = document.getElementById('hid-add2-zip').value;
                            geocoder.geocode({'address': address2}, function (results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    resultsMap.setCenter(results[0].geometry.location);
                                    var marker = new google.maps.Marker({
                                        map: resultsMap,
                                        position: results[0].geometry.location
                                    });
                                }
                            });
                        }
                    });
                }
                                    </script>
                                </div>
                            </div>
                        </div>     
                        <?php
                    } else {
                        $zoom = max((int) $order->delivery['country']['zoom'], 8);
                        ?>
                        <div class="pr-add-det-box pr-add-det-box02 pr-add-det-box03 after">
                            <div class="pra-sub-box after">
                                <div class="pra-sub-box-map">
                                    <div class="cr-ord-cust cr-ord-cust-saddress">
                                        <span><?php echo T_SHIP_BILL_ADDRESS; ?></span>
                                        <div><?php echo \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></div>
                                    </div>
                                    <div class="cr-ord-cust cr-ord-cust-smethod">
                                        <span><?php echo T_SHIP_METH; ?></span>
                                        <div><?php echo $order->info['shipping_method']; ?></div>
                                        <?php
                                        if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'showDeliveryDate')){
                                            echo $ext::showDeliveryDate($order->info['delivery_date']);
                                        }
                                        ?>
                                        <div class="tracking_number"><a href="<?php echo \Yii::$app->urlManager->createUrl(['orders/gettracking?orders_id=' . (int) $oID]) ?>" class="edit-tracking"><i class="icon-pencil"></i></a><?php echo '<span class="tracknum">' . ($order->info['tracking_number'] ? '<a href="' . TRACKING_NUMBER_URL . $order->info['tracking_number'] . '" target="_blank">' . $order->info['tracking_number'] . '</a>' : TEXT_TRACKING_NUMBER) . '</span>'; ?></div>
                                    </div>
                                    <div class="cr-ord-cust cr-ord-cust-bmethod">
                                        <span><?php echo T_BILL_METH; ?></span>
                                        <div><?php echo $order->info['payment_method']; ?></div>
                                    </div>
                                    <div class="barcode">
                                        <?php if (tep_not_null($order->info['tracking_number'])) { ?>
                                            <a href="<?php echo TRACKING_NUMBER_URL . $order->info['tracking_number']; ?>" target="_blank"><img alt="<?php echo $order->info['tracking_number']; ?>" src="<?php echo HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'account/order-qrcode?oID=' . (int) $oID . '&cID=' . (int) $order->customer['customer_id'] . '&tracking=1'; ?>"></a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="pra-sub-box-map">
                                    <div id="floating-panel">
                                        <input id="hid-add" type="hidden" value="<?php echo /* $order->delivery['postcode'] . ' ' . */ $order->delivery['street_address'] . ' ' . $order->delivery['city'] . ' ' . $order->delivery['country']['title']; ?>">
                                        <input id="hid-add-zip" type="hidden" value="<?php echo $order->delivery['postcode']; ?>">
                                    </div>
                                    <div class="gmaps-wrap"><div id="gmap_markers" class="gmaps"></div></div>
                                    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $key['setting_code']; ?>&callback=initMap" async defer></script>
                                    <script>
                $(function () {
                    var click_map = false;
                    $('body').on('click', function () {
                        setTimeout(function () {
                            if (click_map) {
                                $('.map_dashboard-hide').remove()
                            } else {
                                if (!$('.map_dashboard-hide').hasClass('map_dashboard-hide')) {
                                    $('.gmaps-wrap').append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
                                }
                            }
                            click_map = false
                        }, 200)
                    });
                    $('.gmaps-wrap')
                            .css('position', 'relative')
                            .append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
                            .on('click', function () {
                                setTimeout(function () {
                                    click_map = true
                                }, 100)
                            })
                });

                function initMap() {
                    var map = new google.maps.Map(document.getElementById('gmap_markers'), {
                        zoom: <?= $zoom ?>,
                        center: {lat: -34.397, lng: 150.644}
                    });
                    var geocoder = new google.maps.Geocoder();

                    geocodeAddress(geocoder, map);
                }

                function geocodeAddress(geocoder, resultsMap) {
                    var address = document.getElementById('hid-add').value;
                    geocoder.geocode({'address': address}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            resultsMap.setCenter(results[0].geometry.location);
                            var marker = new google.maps.Marker({
                                map: resultsMap,
                                position: results[0].geometry.location
                            });
                        } else {
                            address2 = document.getElementById('hid-add-zip').value;
                            geocoder.geocode({'address': address2}, function (results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    resultsMap.setCenter(results[0].geometry.location);
                                    var marker = new google.maps.Marker({
                                        map: resultsMap,
                                        position: results[0].geometry.location
                                    });
                                }
                            });
                        }
                    });
                }
                                    </script>
                                </div>                    
                            </div>
                        </div>    
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="box-or-prod-wrap">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-prod">
                    <h4><?php echo TEXT_PROD_DET; ?></h4>
                    <div class="toolbar no-padding">
                        <div class="btn-group">
                            <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-prod">
                    <table border="0" class="table table-process" width="100%" cellspacing="0" cellpadding="2">
                        <thead>
                            <tr class="dataTableHeadingRow">
                                <th class="dataTableHeadingContent" colspan="3"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
                            </tr>
                        </thead>

                        <?php
                        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                            $image = \common\classes\Images::getImage($order->products[$i]['id']);
                            echo '          <tr class="dataTableRow">' . "\n" .
                            '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
                            '            <td class="dataTableContent" valign="top" align="center"><div class="table-image-cell"><a href="' . \common\classes\Images::getImageUrl($order->products[$i]['id'], 'Large') . '" class="fancybox">' . $image . '</a></div></td>' . "\n" .
                            '            <td class="dataTableContent" valign="top"><span style="cursor: pointer" onclick="window.open(\'' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $order->products[$i]['id']) . '\')">' . $order->products[$i]['name'] . '</span>';
                            if ($ext = Acl::checkExtension('PackUnits', 'queryOrderProcessAdmin')) {
                                echo $ext::queryOrderProcessAdmin($order->products, $i);
                            }
                            if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
                                for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                                    echo '<br><nobr><small>&nbsp;&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option'])) . ($order->products[$i]['attributes'][$j]['value'] ? ': ' . htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                                    if ($order->products[$i]['attributes'][$j]['price'] != '0')
                                        echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], (USE_MARKET_PRICES == 'True' ? false : true), $order->info['currency'], $order->info['currency_value']) . ')';
                                    echo '</i></small></nobr>';
                                }
                            }
                            $gv_state_label = '';
                            if ($order->products[$i]['gv_state'] != 'none') {
                                $_inner_gv_state_label = (defined('TEXT_ORDERED_GV_STATE_' . strtoupper($order->products[$i]['gv_state'])) ? constant('TEXT_ORDERED_GV_STATE_' . strtoupper($order->products[$i]['gv_state'])) : $order->products[$i]['gv_state']);
                                if ($order->products[$i]['gv_state'] == 'pending' || $order->products[$i]['gv_state'] == 'canceled') {
                                    $_inner_gv_state_label = '<a class="js_gv_state_popup" href="' . Yii::$app->urlManager->createUrl(['orders/gv-change-state', 'opID' => $order->products[$i]['orders_products_id']]) . '">' . $_inner_gv_state_label . '</a>';
                                }
                                $gv_state_label = '<span class="ordered_gv_state ordered_gv_state-' . $order->products[$i]['gv_state'] . '">' . $_inner_gv_state_label . '</span>';
                            }
                            echo '            </td>' . "\n" .
                            '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . $gv_state_label . '</td>' . "\n" .
                            '            <td class="dataTableContent" align="right" valign="top">' . \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
                            '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'], (USE_MARKET_PRICES == 'True' ? false : true), $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
                            '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(\common\helpers\Tax::add_tax_always($order->products[$i]['final_price'], $order->products[$i]['tax']), (USE_MARKET_PRICES == 'True' ? false : true), $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
                            '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], (USE_MARKET_PRICES == 'True' ? false : true), $order->info['currency'], $order->info['currency_value'], true) . '</b></td>' . "\n" .
                            '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(\common\helpers\Tax::add_tax_always($order->products[$i]['final_price'] * $order->products[$i]['qty'], $order->products[$i]['tax']), (USE_MARKET_PRICES == 'True' ? false : true), $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
                            echo '          </tr>' . "\n";
                        }
                        ?>
                        <?php
                        $check_vat = '<span class="dis_module">' . ENTRY_BUSINESS . '</span>';
                        if ($ext = Acl::checkExtension('VatOnOrder', 'process_order_message')) {
                            $check_vat = $ext::process_order_message($order);
                        }
                        $shipping_weight = '';
                        if ($order->info['shipping_weight'] > 0) {
                            $shipping_weight = 'Shipping weight: <b>' . $order->info['shipping_weight'] . ' kg</b>';
                        }
                        $items = 0;
                        foreach ($order->products as $item) {
                            $items += $item['qty'];
                        }
                        ?>
                    </table>

                    <div class="order-total-items">
                        <div><b><span><?php echo $items; ?></span> <?php echo ITEMS_IN_TOTAL; ?></b></div>
                        <div><?php echo $check_vat; ?></div>
                        <div><?php echo $shipping_weight; ?></div>
                    </div>
                    <div class="order-sub-totals">
                        <table>
                            <?php
                            //echo '<pre>';print_r($order->totals);
                            $result = [];
                            $ot_paid_exist = false;
                            $ot_paid_value = 0;
                            $ot_paid_value = 0;
                            for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {

                                if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php')) {
                                    include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php');
                                }

                                if (class_exists($order->totals[$i]['class'])) {
                                    $object = new $order->totals[$i]['class'];
                                    if ($object->code == 'ot_paid') {
                                        $ot_paid_exist = true;
                                        $ot_paid_value = $order->totals[$i]['value_inc_tax'];
                                    }
                                    if ($object->code == 'ot_total') {
                                        $ot_total_value = $order->totals[$i]['value_inc_tax'];
                                    }
                                    if (method_exists($object, 'visibility')) {
                                        if (true == $object->visibility(platform::defaultId(), 'TEXT_ADMINORDER')) {
                                            if (method_exists($object, 'visibility')) {
                                                $result[] = $object->displayText(platform::defaultId(), 'TEXT_ADMINORDER', $order->totals[$i]);
                                            } else {
                                                $result[] = $total;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($ot_paid_exist) {
                                if (number_format($ot_total_value, 2) <= number_format($ot_paid_value, 2)) {
                                    $ot_paid_exist = false;
                                }
                            }
                            for ($i = 0, $n = sizeof($result); $i < $n; $i++) {
                                echo '              <tr class="' . $result[$i]['class'] . ($result[$i]['show_line'] ? ' totals-line' : '') . '">' . "\n" .
                                '                <td>' . $result[$i]['title'] . '</td>' . "\n" .
                                '                <td>' . $result[$i]['text'] . '</td>' . "\n" .
                                '              </tr>' . "\n";
                            }
                            ?>
                        </table>
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
            <?php
            $query = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int)$oID . "'");
            if (tep_db_num_rows($query)) {
                ?>
                <table border="0" cellpadding="5" cellspacing="0">
                    <?php
                    while ($data = tep_db_fetch_array($query)) {
                        echo '<tr><td class="main">' . $data['orders_products_name'] . '</td>';
                        echo '<td class="main">' . $data['orders_products_filename'] . ' ' . $data['download_count_1'] . ' ' . TEXT_DOWNLOAD . '</td></tr>';
                    }
                    ?>          
                </table>
                <?php
            }
            
            if ($rf = Acl::checkExtension('ReferFriend', 'allowed')){
                $rfBlock = $rf::getAdminOrderView($oID);
                    if ( $rfBlock ) {
                        echo $rfBlock;
                    }
            }
            
            if ($loyalty = Acl::checkExtension('CustomerLoyalty', 'allowed')){
                $loyaltyBlock = $loyalty::getAdminOrderView($order);
                    if ( $loyaltyBlock ) {
                        echo $loyaltyBlock;
                    }
            }
            ?>  
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-order-status">
                    <h4><?php echo TEXT_ORDER_STATUS; ?></h4>
                    <div class="toolbar no-padding">
                        <div class="btn-group">
                            <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <?php
                    if ($sms = Acl::checkExtension('SMS', 'allowed')){
                            \common\helpers\Translation::init('admin/sms');
                        }
                    ?>
                    <table class="table table-st" border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                            <tr>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></th>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_STATUS; ?></th>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_COMMENTS; ?></th>
                                <?php if ($sms = Acl::checkExtension('SMS', 'allowed')){
                                ?>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_SMSCOMMENTS; ?></th>
                                <?php
                                }
                                ?>
                                <th class="smallText" align="left"><?php echo TABLE_HEADING_PROCESSED_BY; ?></th>
                            </tr>
                        </thead>
                        <?php
                        $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $oID . "' order by date_added");
                        if (tep_db_num_rows($orders_history_query)) {
                            while ($orders_history = tep_db_fetch_array($orders_history_query)) {
                                echo '          <tr>' . "\n" .
                                '            <td>' . \common\helpers\Date::datetime_short($orders_history['date_added']) . '</td>' . "\n" .
                                '            <td>';
                                if ($orders_history['customer_notified'] == '1') {
                                    echo '<span class="st-true"></span></td>';
                                } else {
                                    echo '<span class="st-false"></span></td>';
                                }
                                echo '            <td><span class="or-st-color">' . $orders_status_group_array[$orders_history['orders_status_id']] . '/&nbsp;</span>' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
                                '            <td>' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' . "\n";
                                if ($sms = Acl::checkExtension('SMS', 'allowed')){
                                     echo ' <td>' . nl2br(tep_db_output($orders_history['smscomments'])) . '&nbsp;</td>' . "\n";
                                }
                                if ($orders_history['admin_id'] > 0) {
                                    $check_admin_query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . (int) $orders_history['admin_id'] . "'");
                                    $check_admin = tep_db_fetch_array($check_admin_query);
                                    if (is_array($check_admin)) {
                                        echo '<td>' . $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'] . '</td>';
                                    } else {
                                        echo '<td></td>';
                                    }
                                } else {
                                    echo '<td></td>';
                                }
                                echo '          </tr>' . "\n";
                            }
                        } else {
                            echo '          <tr>' . "\n" .
                            '            <td colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
                            '          </tr>' . "\n";
                        }
                        ?>
                    </table>
                    <div class="widget box box-wrapp-blue filter-wrapp">
                        <div class="widget-header upd-sc-title">
                            <h4><?php echo TABLE_HEADING_COMMENTS_STATUS; ?></h4>
                        </div>
                        <div class="widget-content usc-box usc-box2">
                            <div class="f_tab">
                                <div class="f_row">
                                    <div class="f_td">
                                        <label><?php echo ENTRY_STATUS; ?></label>
                                    </div>
                                    <div class="f_td">
                                        <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['order_status'], 'class="form-control"'); ?>        
                                        
                                    </div>
                                    
                                </div>
                                <?php if ($ot_paid_exist) { ?>
                                    <div class="f_row">
                                        <div class="f_td">
                                        </div>
                                        <div class="f_td">
                                         <?php 
                                            echo \yii\helpers\Html::checkbox('use_update_amount', false, ['label' => TEXT_UPDATE_PAID_AMOUNT, 'class' => 'uniform upade_paid_on_process']);
                                            echo \yii\helpers\Html::input('hidden', "update_paid_amount", 0, ['class' => 'form-control', 'style' => 'margin-left:5px; width: 100px; display: inline-block;']);
                                            ?>                                                
                                             <script>
                                                (function ($) {
                                                    $('.upade_paid_on_process').change(function (e) {
                                                        e.preventDefault();
                                                        if ($(this).prop('checked')){
                                                            $('input[name=update_paid_amount]').attr('type', 'input');
                                                        } else {
                                                            $('input[name=update_paid_amount]').attr('type', 'hidden');
                                                        }
                                                    })
                                                }(jQuery))
                                            </script>
                                            
                                        </div>
                                    </div>
                                    <?php 
                                        }
                                    ?>
                                <div class="f_row">
                                    <div class="f_td">
                                        <label><?php echo TABLE_HEADING_COMMENTS; ?>:</label>
                                    </div>
                                    <div class="f_td">
                                        <?php echo tep_draw_textarea_field('comments', 'soft', '60', '5', '', 'class="form-control"', false); ?>                             </div>
                                </div>
                                <?php if ($TrustpilotClass = Acl::checkExtension('Trustpilot', 'viewOrder')) { ?>
                                    <?php
                                    $TrustpilotBlock = $TrustpilotClass::viewOrder($order);
                                    if ( $TrustpilotBlock ) {
                                        ?>

                                        <div class="f_row">
                                            <div class="f_td"></div>
                                            <div class="f_td"><?php echo $TrustpilotBlock; ?></div>
                                        </div>

                                        <?php
                                    }
                                    ?>
                                <?php }

                                if ($sms = Acl::checkExtension('SMS', 'viewOrder')){
                                    $smsBlock = $sms::viewOrder($order);
                                    if ($smsBlock){
                                        echo $smsBlock;
                                    }
                                }
                                ?>
                                <div class="f_row">
                                    <div class="f_td"></div>
                                    <div class="f_td">
                                        <?php echo tep_draw_checkbox_field('notify', '', true, '', 'class="uniform"'); ?><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b><?php echo tep_draw_checkbox_field('notify_comments', '', true, '', 'class="uniform"'); ?><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b>
                                        <?php if (!tep_session_is_registered('login_affiliate')) { ?>
                                            <?php echo '<input type="submit" style="float: right; margin-right: -9px;" class="btn btn-confirm" value="' . IMAGE_UPDATE . '" >'; ?>
                                            <?php
                                            //echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_INSERT . '" >';
                                            echo tep_draw_hidden_field('orders_id', $oID);
                                            ?>
                                            <?php
                                        }
                                        ?>
                                        <div>
                                            <?php
                                            if (\common\helpers\Order::is_stock_updated((int) $oID)) {
                                                echo '<span class="st-true st-with-text"><span>' . TEXT_ORDER_STOCK_UPDATED . '</span></span>';
                                            } else {
                                                echo '<label>' . tep_draw_checkbox_field('update_order_stock', '1', false, '', 'class="uniform"') . '<b>' . TEXT_ASK_UPDATE_ORDER_STOCK . '</b></label>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $orders_not_processed = tep_db_fetch_array(tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id != '" . (int) $oID . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' order by orders_id DESC limit 1"));
            echo '<div class="btn-bar" style="padding: 0; text-align: center;">' . '<div class="btn-left"><a href="javascript:void(0)" onclick="return resetStatement();" class="btn btn-back">' . IMAGE_BACK . '</a></div><div class="btn-right">' . (isset($orders_not_processed['orders_id']) ? '<a href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_not_processed['orders_id']]) . '" class="btn btn-next-unprocess">' . TEXT_BUTTON_NEXT_ORDER . '</a>' : '') . '</div><a href="' . \Yii::$app->urlManager->createUrl(['../email-template/invoice', 'orders_id' => $oID, 'platform_id' => $order->info['platform_id'], 'language' => \common\classes\language::get_code($order->info['language_id'])]) . '" TARGET="_blank" class="btn btn-mar-right btn-primary">' . TEXT_INVOICE . '</a><a href="' . \Yii::$app->urlManager->createUrl(['../email-template/packingslip', 'orders_id' => $oID, 'platform_id' => $order->info['platform_id'], 'language' => \common\classes\language::get_code($order->info['language_id'])]) . '" TARGET="_blank" class="btn btn-primary">' . IMAGE_ORDERS_PACKINGSLIP . '</a></div>';
            ?>
        </div>

        </form>
        <script type="text/javascript">
            $(document).ready(function () {
                $("a.js_gv_state_popup").popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading pup-head'><?php echo POPUP_TITLE_GV_STATE_SWITCH; ?></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });

            });

        <?php
        if (\common\helpers\Order::is_stock_updated(intval($oID))) {
            $restock_disabled = '';
            $restock_selected = ' checked ';
        } else {
            $restock_disabled = ' disabled="disabled" readonly="readonly" ';
            $restock_selected = '';
        }
        ?>
            function deleteOrder(orders_id) {
                bootbox.dialog({
                    message: '<?php echo TEXT_INFO_DELETE_INTRO; ?><br /><br /><div class="restock"><label class="restock"><input type="checkbox" class="uniform" name="restock" id="restock" value="1" <?php echo $restock_disabled . ' ' . $restock_selected; ?>> <?php echo TEXT_INFO_RESTOCK_PRODUCT_QUANTITY; ?></label></div>',
                    title: "<?php echo TEXT_INFO_HEADING_DELETE_ORDER; ?>",
                    buttons: {
                        success: {
                            label: "<?php echo TEXT_BTN_OK; ?>",
                            className: "btn-delete",
                            callback: function () {
                                $.post("<?php echo \Yii::$app->urlManager->createUrl('orders/orderdelete'); ?>", {
                                    'orders_id': orders_id, 'restock': ($("#restock").is(':checked') ? 'on' : 0)
                                }, function (data, status) {
                                    if (status == "success") {
                                        $("#order_management_data").html('');
                                        window.location.href = "<?php echo \Yii::$app->urlManager->createUrl('orders/'); ?>";
                                    } else {
                                        alert("Request error.");
                                    }
                                }, "html");
                            }
                        }
                    }
                });
                return false;
            }
        </script>

        <?php
        //echo '<a href="javascript:popupWindow(\'' .  (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_INVOICE) . '?' . (\common\helpers\Output::get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . '\')">' . tep_image_button('button_invoice.gif', TEXT_INVOICE) . '</a><a href="javascript:popupWindow(\'' .  (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_PACKINGSLIP) . '?' . (\common\helpers\Output::get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . '\')">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>';
        //echo '<input type="button" class="btn btn-primary" value="' . IMAGE_BACK . '" onClick="return resetStatement()">'; 
        ?>
        <?php
        $content = ob_get_clean();
        if (Yii::$app->request->isPost) {
            return $content;
        }
        $_session = Yii::$app->session;
        $filter = $search_condition = '';
        if ($_session->has('filter')) {
            $filter = $_session->get('filter');
        }
        if ($_session->has('search_condition')) {
            $search_condition = $_session->get('search_condition');
        }

        $order_next = tep_db_fetch_array(tep_db_query("select o.orders_id from " . TABLE_ORDERS . " o " . (strlen($filter) > 0 ? "left join " . TABLE_ORDERS_PRODUCTS . " op on o.orders_id = op.orders_id left join " . TABLE_ORDERS_STATUS . " s on o.orders_status=s.orders_status_id " : '') . " where o.orders_id > '" . (int) $oID . "' " . $search_condition . " " . $filter . " order by orders_id ASC limit 1"));
        $order_prev = tep_db_fetch_array(tep_db_query("select o.orders_id from " . TABLE_ORDERS . " o " . (strlen($filter) > 0 ? "left join " . TABLE_ORDERS_PRODUCTS . " op on o.orders_id = op.orders_id left join " . TABLE_ORDERS_STATUS . " s on o.orders_status=s.orders_status_id " : '') . " where o.orders_id < '" . (int) $oID . "' " . $search_condition . " " . $filter . " order by orders_id DESC limit 1"));
        $this->view->order_next = ( isset($order_next['orders_id']) ? $order_next['orders_id'] : 0);
        $this->view->order_prev = ( isset($order_prev['orders_id']) ? $order_prev['orders_id'] : 0);

        $order_platform_id = $order->info['platform_id'];
        $order_language = \common\classes\language::get_code($order->info['language_id']);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/process-order?orders_id=' . $oID), 'title' => TEXT_PROCESS_ORDER . ' #' . $oID . ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . '' . $order->info['date_purchased'] . '</div><div class="order-platform">' . TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($order_platform_id) . '</div>');
        return $this->render('update', ['content' => $content, 'orders_id' => $oID, 'customer_id' => (int) $order->customer["customer_id"], 'qr_img_url' => HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "account/order-qrcode?oID=" . (int) $oID . "&cID=" . (int) $order->customer["customer_id"] . "&tracking=1", 'order_platform_id' => $order_platform_id, 'order_language' => $order_language, 'ref_id' => $order->getReferenceId()]);
    }

    public function actionOrdersubmit() {

        global $languages_id, $language, $login_id, $currencies;

        \common\helpers\Translation::init('admin/orders');

        $admin_id = $login_id;

        $this->layout = false;

        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }

        $orders_statuses = array();
        $orders_status_array = array();
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                'text' => $orders_status['orders_status_name']);
            $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
        }

        $oID = Yii::$app->request->post('orders_id');

        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased, platform_id, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'");
        if (!tep_db_num_rows($check_status_query)) {
            die("Wrong order data.");
        }
        $check_status = tep_db_fetch_array($check_status_query);
        $order_updated = false;
        $status = tep_db_prepare_input($_POST['status']);
        $comments = tep_db_prepare_input($_POST['comments']);
        $update_paid_amount = (float) Yii::$app->request->post('update_paid_amount', 0);
        if (is_numeric($update_paid_amount) && $update_paid_amount) {
            $_paid = tep_db_fetch_array(tep_db_query("select value_inc_tax from " . TABLE_ORDERS_TOTAL . " where orders_id ='" . (int) $oID . "' and class='ot_paid'"));
            $value = $_paid['value_inc_tax'] + $update_paid_amount * $currencies->get_market_price_rate($check_status['currency'], DEFAULT_CURRENCY);
            $text = $currencies->format($value, true, $check_status['currency'], $check_status['currency_value']);
            $comments .= " " . TEXT_PAID_AMOUNT . " " . $currencies->format($update_paid_amount, true, $check_status['currency'], $check_status['currency_value']);
            tep_db_query("update " . TABLE_ORDERS_TOTAL . " set value_inc_tax = '" . tep_db_input($value) . "', text_inc_tax = '" . tep_db_input($text) . "' where orders_id ='" . (int) $oID . "' and class='ot_paid' ");
            $_due = tep_db_fetch_array(tep_db_query("select value_inc_tax from " . TABLE_ORDERS_TOTAL . " where orders_id ='" . (int) $oID . "' and class='ot_due'"));
            $value = $_due['value_inc_tax'] - $update_paid_amount * $currencies->get_market_price_rate($check_status['currency'], DEFAULT_CURRENCY);
            if ($value < 0)
                $value = 0;
            $text = $currencies->format($value, true, $check_status['currency'], $check_status['currency_value']);
            tep_db_query("update " . TABLE_ORDERS_TOTAL . " set value_inc_tax = '" . tep_db_input($value) . "', text_inc_tax = '" . tep_db_input($text) . "' where orders_id ='" . (int) $oID . "' and class='ot_due' ");

            $order_updated = true;
        }



        $order_stock_updated_flag = false;
        $update_order_stock = Yii::$app->request->post('update_order_stock', 0);
        if ($update_order_stock && !\common\helpers\Order::is_stock_updated((int) $oID)) {
            $_get_order_products_r = tep_db_query(
                    "select IF(LENGTH(uprid)>0,uprid,products_id) AS uprid, products_quantity " .
                    "from " . TABLE_ORDERS_PRODUCTS . " " .
                    "where orders_id='" . (int) $oID . "'"
            );
            while ($ordered_uprid = tep_db_fetch_array($_get_order_products_r)) {
                \common\helpers\Product::update_stock($ordered_uprid['uprid'], 0, $ordered_uprid['products_quantity']);
            }
            tep_db_query("UPDATE " . TABLE_ORDERS . " SET stock_updated=1 WHERE orders_id='" . (int) $oID . "'");

            $order_stock_updated_flag = true;
        }


// BOF: WebMakers.com Added: Downloads Controller
// always update date and time on order_status
// original        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
        $messages = [];
        if (($check_status['orders_status'] != $status) || $comments != '' || ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) || (isset($_POST['smscomments']) && !empty($_POST['smscomments']))) {
            tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int) $oID . "'");
            $check_status_query2 = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'");
            $check_status2 = tep_db_fetch_array($check_status_query2);
            if ($check_status2['orders_status'] == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {
                tep_db_query("update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays = '" . tep_db_input(\common\helpers\Configuration::get_configuration_key_value('DOWNLOAD_MAX_DAYS')) . "', download_count = '" . tep_db_input(\common\helpers\Configuration::get_configuration_key_value('DOWNLOAD_MAX_COUNT')) . "' where orders_id = '" . (int) $oID . "'");
            }
// EOF: WebMakers.com Added: Downloads Controller

            $email_headers = '';
            if ($TrustpilotClass = Acl::checkExtension('Trustpilot', 'onOrderUpdateEmail')) {
                $email_headers = $TrustpilotClass::onOrderUpdateEmail((int)$oID, $email_headers);
            }
            $customer_notified = '0';
            if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
                /**
                 * @var $platform_config platform_config
                 */
                $platform_config = Yii::$app->get('platform')->config($check_status['platform_id']);

                $notify_comments = '';
                if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on')) {
                    $notify_comments = trim(sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments)) . "\n\n";
                }

                $eMail_store = $platform_config->const_value('STORE_NAME');
                $eMail_address = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                $eMail_store_owner = $platform_config->const_value('STORE_OWNER');

                // {{
                $email_params = array();
                $email_params['STORE_NAME'] = $eMail_store;
                $email_params['ORDER_NUMBER'] = $oID;
                $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link('account/historyinfo', 'order_id=' . $oID, 'SSL'/* , $store['store_url'] */));
                $email_params['ORDER_DATE_LONG'] = \common\helpers\Date::date_long($check_status['date_purchased']);
                $email_params['ORDER_COMMENTS'] = $notify_comments;
                $email_params['NEW_ORDER_STATUS'] = $orders_status_array[$status];

                $emailTemplate = 'Order Status Update';
                $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_template from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "' and orders_status_id='" . (int) $status . "'"));
                if (!empty($ostatus['orders_status_template'])) {
                    $get_template_r = tep_db_query("select * from " . TABLE_EMAIL_TEMPLATES . " where email_templates_key='" . tep_db_input($ostatus['orders_status_template']) . "'");
                    if (tep_db_num_rows($get_template_r) > 0) {
                        $emailTemplate = $ostatus['orders_status_template'];
                    }
                }

                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($emailTemplate, $email_params, -1, $check_status['platform_id']);
                // }}


                \common\helpers\Mail::send($check_status['customers_name'], $check_status['customers_email_address'], $email_subject, $email_text, $eMail_store_owner, $eMail_address, [], $email_headers);

                $customer_notified = '1';
            }

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
                'orders_id' => (int) $oID,
                'orders_status_id' => (int) $status,
                'date_added' => 'now()',
                'customer_notified' => $customer_notified,
                'comments' => $comments,
                'admin_id' => $admin_id,
            ));
            
            if ($sms = Acl::checkExtension('SMS', 'sendSMS')){
                $commentid = tep_db_insert_id();
                $response = $sms::sendSMS($oID, $commentid);
                if (is_array($response) && count($response)){
                    $messages[] = ['message' => $response['message'], 'messageType' => $response['messageType']];
                }
            }
            
            if ($ext = Acl::checkExtension('ReferFriend', 'rf_release_reference')){
                $ext::rf_release_reference((int)$oID);
            }
            
            if ($ext = Acl::checkExtension('CustomerLoyalty', 'afterOrderUpdate')){
                $ext::afterOrderUpdate((int)$oID);
            }

            if (function_exists('tl_credit_order_check_state')) {
                tl_credit_order_check_state((int) $oID);
            }

            $order_updated = true;
        }

        if ($order_updated == true || $order_stock_updated_flag) {
            $messageType = 'success';
            if ($order_stock_updated_flag) {
                $message = '<p>' . TEXT_MESSAGE_ORDER_STOCK_UPDATED . '</p>';
            }
            if ($order_updated) {
                $message = '<p>' . SUCCESS_ORDER_UPDATED . '</p>';
            }
            $messages[] = ['messageType' => 'success', 'message' => $message];
            //$messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
            $message = '<p>'.WARNING_ORDER_NOT_UPDATED.'</p>';
            $messages[] = ['messageType' => 'warning', 'message' => $message];
            //$messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        if ($TrustpilotClass = Acl::checkExtension('Trustpilot', 'onOrderUpdate')) {
            try {
                $TrustpilotClass::onOrderUpdate((int)$oID);
            }catch (\Exception $exception){
                if ( $exception->getCode()===222 ){
                    $message = '<p>'.sprintf(WARNING_TRUSTPILOT_TOKEN_EXPIRED, Yii::$app->urlManager->createUrl(['platforms/edit','id'=>$check_status['platform_id']])).'</p>';
                    $messages[] = ['messageType' => 'warning', 'message' => $message];
                }elseif ( $exception->getMessage()!='' ) {
                    $message = '<p>'.WARNING_TRUSTPILOT_ERROR.' - '.$exception->getMessage().'</p>';
                    $messages[] = ['messageType' => 'warning', 'message' => $message];
                }
            }
        }

        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <?php
                    if (is_array($messages) && count($messages)){
                        foreach($messages as $message){
                    ?>
                    <div class="popup-content pop-mess-cont alert-<?= $message['messageType'] ?>">
                        <?= $message['message'] ?>
                    </div>
                    <?php                     
                        }
                    }
                    ?>
                </div>   
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div>  
            <script>
                //$('body, html').scrollTop(0);
                $('.popup-box-wrap.pop-mess').css('top', (window.scrollY + 200) + 'px');
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>

        <?php
        return $this->actionProcessOrder();
    }

    public function actionSetAddress() {
        global $sendto, $billto, $cart_address_id, $cart;

        $address_id = Yii::$app->request->post('address_id', 0);
        $prefix = Yii::$app->request->post('prefix', '');
        $csa = Yii::$app->request->post('csa', 'true');
        if (!tep_session_is_registered('sendto'))
            tep_session_register('sendto');
        if (!tep_session_is_registered('billto'))
            tep_session_register('billto');

        if (tep_not_null($prefix)) { // delivery
            $_SESSION['sendto'] = $address_id;
            if (!tep_session_is_registered('cart_address_id'))
                tep_session_register('cart_address_id');
            $cart_address_id = $address_id;
            if ($csa != 'false') {
                $_SESSION['billto'] = $address_id;
            }
        } else {
            $_SESSION['billto'] = $address_id;
        }

        $admin = new AdminCarts();
        $admin->loadCustomersBaskets();
        $currentCart = Yii::$app->request->getBodyParam('currentCart', '');        
        if (tep_not_null($currentCart)){
            $admin->setCurrentCartID($currentCart, true);
            $admin->saveCustomerBasket($cart);
        }

        echo json_encode(['sendto' => $sendto, 'billto' => $billto]);
        exit();
    }


    public function actionSetPayment() {
        global $payment, $cart;
        if (!tep_session_is_registered('payment'))
            tep_session_register('payment');
        $payment = $_SESSION['payment'] = Yii::$app->request->post('payment');
        if (is_object($cart))
            $cart->clearTotalKey('ot_paymentfee');
        $_GET['current_cart'] = true;
        $cart->setAdjusted();
        $admin = new AdminCarts();
        $admin->saveCustomerBasket($cart);
        $this->actionOrderEdit();
    }

    public function actionOrderEdit() {
        global $languages_id, $language, $login_id;
        global $cart, $order, $shipping_weight, $shipping_num_boxes, $currencies, $sendto, $billto, $cart_address_id, $select_shipping;
        global $shipping_modules, $shipping, $payment, $messageStack, $tax_rates_array, $order_total_modules, $total_weight, $total_count;
        global $cot_gv, $cc_id;
        global $update_totals_custom, $adress_details, $admin_notified, $currentCart;

        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('admin/orders/create');

        $messageType = '';
        $admin_id = $login_id;

        $admin = new AdminCarts();
        $admin->loadCustomersBaskets();
        $currentCart = Yii::$app->request->getBodyParam('currentCart', '');
                    
        $admin_message = '';

        $this->selectedMenu = array('customers', 'orders');

        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }

        $this->topButtons[] = '';
        $this->view->headingTitle = HEADING_TITLE;
        if (isset($_GET['new'])) {
            $this->view->newOrder = true;
        } else {
            $this->view->newOrder = false;
        }
        if (isset($_GET['back'])) {
            $this->view->backOption = $_GET['back'];
        } else {
            $this->view->backOption = 'orders';
        }

        if (!tep_session_is_registered('adress_details')) {
            tep_session_register('adress_details');
        }
        if (!tep_session_is_registered('select_shipping')) {
            tep_session_register('select_shipping');
        }

        if (!tep_session_is_registered('shipping')) {
            tep_session_register('shipping');
        }

        if (!tep_session_is_registered('payment')) {
            tep_session_register('payment');
        }

        if (!tep_session_is_registered('admin_notified')) {
            $admin_notified = false;
            tep_session_register('admin_notified');
        }

        if (Yii::$app->session->hasFlash('error')) {
            $messageStack->add('one_page_checkout', Yii::$app->session->getFlash('error'));
            Yii::$app->session->removeAllFlashes();
        }
        
        $ids = $admin->getVirtualCartIDs();

        $oID = Yii::$app->request->get('orders_id');
        if (tep_not_null($currentCart)) {
            $admin->setCurrentCartID($currentCart, (tep_not_null($oID)?false:true));
        }        
        
        if (tep_not_null($oID)) { //existed order
            $info = tep_db_fetch_array(tep_db_query("select customers_id, language_id, platform_id, currency, basket_id, delivery_address_book_id, billing_address_book_id, shipping_class, payment_class, date_purchased, delivery_date from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'"));
            if (!$info) {
                return $this->redirect(['orders/']);
            }

            $customer_id = $info['customers_id'];
            $currency = $info['currency'];
            $language_id = $info['language_id'];
            $basket_id = $info['basket_id'];
            $platform_id = $info['platform_id'];

            if (tep_session_is_registered('cart')) {
                $cart = &$_SESSION['cart'];

                if ($cart->order_id != $oID) {
                    $cart = new \common\classes\shopping_cart($oID);
                    //tep_session_unregister('cot_gv');
                    //tep_session_unregister('cc_id');
                    tep_session_unregister('update_totals_custom');
                    // tep_session_unregister('shipping');
                    //tep_session_unregister('payment');
                    //tep_session_unregister('billto');
                    //tep_session_unregister('sendto');
                    // tep_session_unregister('select_shipping');!!
                    tep_session_unregister('admin_notified');
                    //unset($select_shipping);
                    //unset($payment);
                    //unset($shipping);
                    //unset($sendto);
                    //unset($billto);
                    //$adress_details = $this->checkDetails();
                    //unset($adress_details['data']['shipto']);
                    //unset($adress_details['data']['billto']);
                }
            } else {
                tep_session_register('cart');
                //tep_session_unregister('cot_gv');
                $cart = new \common\classes\shopping_cart($oID);
                //tep_session_unregister('cc_id');
                tep_session_unregister('update_totals_custom');
                //tep_session_unregister('shipping');
                //tep_session_unregister('payment');
                //tep_session_unregister('billto');
                //tep_session_unregister('sendto');
                //tep_session_unregister('select_shipping');!!
                //tep_session_unregister('adress_details');
                tep_session_unregister('admin_notified');
                //unset($adress_details);
                //unset($select_shipping);
                //unset($payment);
                //unset($shipping);
                //unset($sendto);
                //unset($billto);
                //$adress_details = $this->checkDetails();
                //unset($adress_details['data']['shipto']);
                //unset($adress_details['data']['billto']);
            }
            $adress_details = $this->checkDetails();
            unset($adress_details['data']['shipto']);
            unset($adress_details['data']['billto']);
            $cart->setPlatform($platform_id)
                    ->setCurrency($currency)
                    ->setLanguage($language_id)
                    ->setAdmin($admin_id)
                    ->setBasketID($basket_id)
                    ->setCustomer($customer_id);
            $sendto = $info['delivery_address_book_id'];
            $billto = $info['billing_address_book_id'];
            if (($status = $admin->updateCustomersBasket($cart)) === false) {
                $name = $admin->getAdminByCart($cart);
                $admin_message = 'This order is busy by ' . $name . '. Do you want to assign this order to your account?';
            }
        } else { //new order
            $cart = &$_SESSION['cart'];

            if (!is_object($cart) || !($cart instanceof \common\classes\shopping_cart)) {
                $messageStack->add_session('create', TEXT_CREATE_NEW_OREDER, 'warning');
                return $this->redirect(['orders/create']);
            }

            if (is_null($cart->order_id)) {
                $cart->order_id = -1;
                //tep_session_unregister('shipping');!!
                //tep_session_unregister('select_shipping');!!
                //tep_session_unregister('payment');
                //tep_session_unregister('cc_id');
                //unset($payment);
                //unset($select_shipping);
                //unset($shipping);
                //unset($cc_id);
                $adress_details = $this->checkDetails();
                unset($adress_details['data']['shipto']);
                unset($adress_details['data']['billto']);
            }

            if ($ids != false) { //has virtual carts (with zero order)
                if (count($ids) == 1) {
                    $admin->setCurrentCartID($ids[0], true);
                } else {
                    $admin->getLastVirtualID(true);
                }
            }

            $admin->loadCurrentCart();

            if (is_null($cart)) {
                $messageStack->add('one_page_checkout', 'Please create order <a href="orders/create">click here</a>');
                return $this->render('message', ['messagestack' => $messageStack]);
            }
            $info['delivery_address_book_id'] = $cart->address['sendto'];
            $info['billing_address_book_id'] = $cart->address['billto'];
            $info['payment_class'] = '';
            $info['shipping_class'] = '';
            $customer_id = $cart->customer_id;
            $currency = $cart->currency;
            $language_id = $cart->language_id;
            $platform_id = $cart->platform_id;
        }
        $admin_choice = [];
        $currentCart = $admin->getCurrentCartID();
        if ($ids) {
            foreach ($ids as $_ids) {
                //if ($_ids == $currentCart)                    continue;
                $admin_choice[] = $this->renderAjax('mini', [
                    'ids' => $_ids,
                    'customer' => \common\helpers\Customer::getCustomerData($_ids),
                    'opened' => ($_ids == $currentCart),
                    ]                    
                );
            }
        }

        

        if (tep_not_null($info['shipping_class']) && is_null($select_shipping)) {
            $select_shipping = $info['shipping_class'];
        }
        if (isset($_POST['shipping'])) {
            $select_shipping = Yii::$app->request->post('shipping');
            $cart->clearTotalKey('ot_shipping');
            $cart->clearHiddenModule('ot_shipping');
            $cart->clearTotalKey('ot_shippingfee');
            $cart->clearHiddenModule('ot_shippingfee');
            if (!isset($_POST['action']))
                $cart->setAdjusted();
        }

        if (!tep_session_is_registered('sendto')) {
            tep_session_register('sendto');
        }
        if (!tep_session_is_registered('billto')) {
            tep_session_register('billto');
        }

        if (is_null($sendto) || !$sendto)
            $sendto = $info['delivery_address_book_id'];
        if (!tep_session_is_registered('cart_address_id')) {
            tep_session_register('cart_address_id');
            $cart_address_id = $sendto;
        }
        if (is_null($billto) || !$billto)
            $billto = $info['billing_address_book_id'];

        $customer = new Customer();
        $customer_loaded = true;

        $session = new \yii\web\Session;
        $session['platform_id'] = $platform_id;

        if ($customer->loadCustomer($customer_id)) {
            $session['customer_id'] = $customer_id;
            $customer->setParam('sendto', $sendto);
            $customer->setParam('billto', $billto);
            $customer->setParam('currency', $currency);
            $customer->setParam('languages_id', $language_id);
            $customer->setParam('currencies_id', $currencies->currencies[$currency]['id']);
            $customer->convertToSession();
            $customer->clearParam('sendto');
            $customer->clearParam('billto');
        } else { // customer doesn't exist
            if (!$admin_notified) {
                $messageStack->add('one_page_checkout', ERROR_INVALID_CUSTOMER_ACCOUNT, 'error');
                $admin_notified = true;
            }
            $customer_loaded = false;
        }

        if (!$payment) {
            $payment = $_SESSION['payment'] = $info['payment_class'];
        }

        $platform_config = new platform_config($cart->platform_id);
        $platform_config->constant_up();

        global $order_totals;
        $update_has_errors = false;

        if (Yii::$app->request->isPost) {
            if (isset($_POST['saID']) || isset($_POST['aID'])) {
                $adress_details = $this->checkDetails();
                if ($adress_details['error']) {
                    $update_has_errors = true;
                }
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'update' && $customer_loaded) {
            $company = tep_db_prepare_input($_POST['customers_company']);
            $company_vat = tep_db_prepare_input($_POST['customers_company_vat']);
            $sql_data_array = [];
            if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['customers_company'] = $company;
            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['customers_company_vat'] = $company_vat;
            $sql_data_array['customers_email_address'] = tep_db_prepare_input($_POST['update_customer_email_address']);
            $sql_data_array['customers_telephone'] = tep_db_prepare_input($_POST['update_customer_telephone']);
            $sql_data_array['customers_landline'] = tep_db_prepare_input($_POST['update_customer_landline']);

            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', 'customers_id="' . (int) $customer_id . '"');
            $adress_details = $this->checkDetails();

            if (!$adress_details['error']) {
                $sa = $this->updateAddress($adress_details);
                $sendto = $sa['saID'];
                $billto = $sa['aID'];

                if ($adress_details['data']['csa']) {
                    $billto = $adress_details['data']['shipto']['saID'];
                }
            } else {
                $update_has_errors = true;
                $messageStack->add('one_page_checkout', 'check address', 'error');
            }
        }
        global $order;
        if (!$customer_loaded) {
            $order = new order($oID);
        } else {
            $order = new order();
            if ($info && tep_not_null($info['date_purchased']))
                $order->info['date_purchased'] = $info['date_purchased'];
            if ($info && tep_not_null($info['delivery_date']))
                $order->info['delivery_date'] = $info['delivery_date'];
            $order->order_id = $oID;
        }

        $this->navigation[] = array('title' => (tep_not_null($oID) ? HEADING_TITLE : TEXT_CREATE_NEW_OREDER) . (tep_not_null($oID) ? ' #' . $oID . ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . ' ' . $order->info['date_purchased'] . '</div>' : '') . '<div class="order-platform">' . TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($order->info['platform_id']) . '</div>');

        $shipping_modules = new shipping();

        $address = [];

        $ads = [];
        $temp = [];
        $retrieve = [];
        $address = $order->delivery;
        if (is_array($adress_details['data']['shipto'])) {
            foreach ($adress_details['data']['shipto'] as $_k => $v) {
                $address[$_k] = $v;
            }
            if (tep_not_null($adress_details['data']['shipto']['saID']) && !tep_not_null($adress_details['data']['shipto']['address_book_id'])) {
                $adress_details['data']['shipto']['address_book_id'] = $adress_details['data']['shipto']['saID'];
            }
            $address['country'] = \common\helpers\Country::get_country_info_by_name(\common\helpers\Country::get_country_name($adress_details['data']['shipto']['country_id']), $language_id);
            $order->delivery = $address;
            if (is_array($address)) {
                array_walk($address, function($value, $key, $prefix) use (&$temp) {
                    $temp[$prefix . $key] = $value;
                }, 's_entry_');
            }
        } else {
            if (is_array($address)) {
                array_walk($address, function($value, $key, $prefix) use (&$temp) {
                    $temp[$prefix . $key] = $value;
                }, 's_entry_');
            }
            $temp['s_entry_country'] = $temp['s_entry_country']['title'];
            $retrieve = $temp;
            $retrieve['saID'] = $address['address_book_id'];
        }

        $ads = $temp;
        $temp = [];
        $address = $order->billing;

        if (is_array($adress_details['data']['billto'])) {
            if (!$adress_details['data']['csa']) {
                foreach ($adress_details['data']['billto'] as $_k => $v) {
                    $address[$_k] = $v;
                }
                if (tep_not_null($adress_details['data']['billto']['aID']) && !tep_not_null($adress_details['data']['billto']['address_book_id'])) {
                    $adress_details['data']['billto']['address_book_id'] = $adress_details['data']['billto']['aID'];
                }
                $address['country'] = \common\helpers\Country::get_country_info_by_name(\common\helpers\Country::get_country_name($adress_details['data']['billto']['country_id']), $language_id);
                $order->billing = $address;
            } else if (is_array($adress_details['data']['shipto'])) {
                foreach ($adress_details['data']['shipto'] as $_k => $v) {
                    $address[$_k] = $v;
                }
                if (tep_not_null($adress_details['data']['billto']['aID']) && !tep_not_null($adress_details['data']['billto']['address_book_id'])) {
                    $adress_details['data']['billto']['address_book_id'] = $adress_details['data']['billto']['aID'];
                }
                $address['country'] = \common\helpers\Country::get_country_info_by_name(\common\helpers\Country::get_country_name($adress_details['data']['shipto']['country_id']), $language_id);
                $order->billing = $order->delivery;
            }
            if (is_array($address)) {
                array_walk($address, function($value, $key, $prefix) use (&$temp) {
                    $temp[$prefix . $key] = $value;
                }, 'entry_');
            }
        } else {
            if (is_array($address)) {
                array_walk($address, function($value, $key, $prefix) use (&$temp) {
                    $temp[$prefix . $key] = $value;
                }, 'entry_');
            }
            $temp['entry_country'] = $temp['entry_country']['title'];
            $retrieve = array_merge($retrieve, $temp);
            //$retrieve['saID'] = $address['address_book_id'];
            $retrieve['aID'] = $address['address_book_id'];
            if ($order->billing['address_book_id'] == $order->delivery['address_book_id'])
                $retrieve['csa'] = 'on';
        }

        if (is_array($retrieve) && count($retrieve)) {
            foreach ($retrieve as $_k => $_v) {
                $_POST[$_k] = $_v;
            }
            $adress_details = $this->checkDetails();
        }


        $ads = array_merge($ads, $temp);

        $info_array = array_merge($ads, $order->customer);
        $cInfo = new \objectInfo($info_array);
        $cInfo->platform_id = $platform_id;
        if ($customer_loaded) {
            $result = $this->getAddresses($cInfo->customer_id);
        } else if (tep_not_null($oID)) {
            $result = $this->getOrderAddresses($oID);
        }
        $js_arrs = $result[0];
        $addresses = $result[1];

        $entry = new \stdClass;
        $entry->zones_array = null;
        $entry->countries = \common\helpers\Country::get_countries();
        $zones = \common\helpers\Zones::get_country_zones($order->billing['country']['id']);

        $entry->entry_state_has_zones = false;
        if (is_array($zones) && count($zones)) {
            $entry->entry_state_has_zones = true;
            $entry->zones_array = $zones;
            $entry->entry_state = $order->billing['zone_id'];
        } else {
            $entry->entry_state = $order->billing['state'];
        }
        $zones = \common\helpers\Zones::get_country_zones($order->delivery['country']['id']);

        $entry->s_entry_state_has_zones = false;
        if (is_array($zones) && count($zones)) {
            $entry->s_entry_state_has_zones = true;
            $entry->s_zones_array = $zones;
            $entry->s_entry_state = $order->delivery['zone_id'];
        } else {
            $entry->s_entry_state = $order->delivery['state'];
        }

        $payment_modules = new payment();

        $selection = $payment_modules->selection();
        if (is_array($selection) && !$payment) {
            $payment = $selection[0]['id'];
        }
        $order->info['payment_class'] = $payment;

        if (isset($_POST['action']) && $_POST['action'] == 'update_gv_amount') {
            if (strtolower($_POST['cot_gv']) == 'on') {
                tep_session_register('cot_gv');
                $cart->clearHiddenModule('ot_gv');
                $cart->clearTotalKey('ot_gv');
            } else {
                tep_session_unregister('cot_gv');
            }
        }

        if (is_null($select_shipping)) {
            $cheapest = $shipping_modules->cheapest();
            if (is_array($cheapest)) {
                $select_shipping = $cheapest[0]['id'];
            }
        }

        $_POST['estimate'] = ['country_id' => $order->delivery['country_id'], 'post_code' => $order->delivery['postcode'], 'shipping' => $select_shipping];

        $shipping_details = \frontend\controllers\ShoppingCartController::prepareEstimateData();
        if ($select_shipping != $order->info['shipping_class'] && is_array($shipping)) {
            $order->change_shipping($shipping);
            $cart->setTotalKey('ot_shipping', ['ex' => $order->info['shipping_cost_exc_tax'], 'in' => $order->info['shipping_cost_inc_tax']]);
        }

        $order->order_id = $oID;


        $total_weight = $cart->show_weight();
        $total_count = $cart->count_contents();

        $tax_class_array = \common\helpers\Tax::get_complex_classes_list();

        $order_total_modules = new \common\classes\order_total(array(
            'ONE_PAGE_CHECKOUT' => 'True',
            'ONE_PAGE_SHOW_TOTALS' => 'false',
            'COUPON_SUCCESS_APPLY' => 'true',
            'GV_SOLO_APPLY' => 'true',
        ));

        if ((isset($_POST['action']) && $_POST['action'] == 'update_amount')) {
            $value = (float) $_POST['paid_amount'] * $currencies->get_market_price_rate($currency, DEFAULT_CURRENCY);
            $cart->setTotalPaid($value, $_POST['comment']);
        }

        if (!tep_session_is_registered('update_totals_custom'))
            tep_session_register('update_totals_custom');

        $reset_totals = (isset($_POST['reset_totals']) && strtolower($_POST['reset_totals']) == 'on');
        if ($reset_totals) {
            if (($_gv = $cart->getTotalKey('ot_gv')) != false && $cot_gv && $customer_loaded) {
                if (is_numeric($_gv)) {
                    $sql_data_array = [
                        'customers_id' => $customer_id,
                        'credit_prefix' => '+',
                        'credit_amount' => $_gv,
                        'currency' => $currency,
                        'currency_value' => $currencies->currencies[$currency]['value'],
                        'customer_notified' => '0',
                        'comments' => '',
                        'date_added' => 'now()',
                        'admin_id' => $login_id,
                    ];

                    tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, $sql_data_array);
                    tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = credit_amount + " . $_gv . " where customers_id =" . (int) $customer_id);
                }
                unset($_SESSION['cot_gv']);
            }

            $cart->clearTotals(false);
            $cart->clearHiddenModules();
            $cart->setAdjusted();
            if (tep_not_null($oID)) {
                $cart->restoreTotals();
            } else {
                $order->info['total_paid_inc_tax'] = 0;
                $order->info['total_paid_exc_tax'] = 0;
            }
            if (/* $select_shipping != $order->info['shipping_class'] && */ is_array($shipping)) {
                $order->change_shipping($shipping);
                $cart->setTotalKey('ot_shipping', ['ex' => $order->info['shipping_cost_exc_tax'], 'in' => $order->info['shipping_cost_inc_tax']]);
            }
            //$update_totals = [];
            unset($_SESSION['update_totals_custom']);
        }

        if ((isset($_POST['action']) && $_POST['action'] == 'adjust_tax')) {
            $prefix = $_POST['adjust_prefix'];
            $cart->setTotalTax('ot_tax', ['in' => 0.01, 'ex' => 0.01], $prefix);
        }

        $update_totals = [];
        if (!$update_has_errors) {
            $_update_totals = $cart->getAllTotals();
            if (is_array($_update_totals)) {
                foreach ($_update_totals as $_k => $_v) {
                    if (is_array($_v['value'])) {
                        $update_totals[$_k]['value']['in'] = $_v['value']['in'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency);
                        $update_totals[$_k]['value']['ex'] = $_v['value']['ex'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency);
                    } else {
                        $update_totals[$_k] = $_v['value'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency);
                    }
                }
            }
        }

        if ((isset($_POST['action']) && $_POST['action'] == 'remove_module')) {
            $_module = $_POST['module'];
            if (tep_not_null($_module)) {
                $cart->addHiddenModule($_module);
                $cart->clearTotalKey($_module);
                $cart->setAdjusted();
            }
        }

        $update_totals_custom = [];

        if (!$update_has_errors && ((isset($_POST['action']) && $_POST['action'] == 'update') || (isset($_POST['action']) && $_POST['action'] == 'new_module'))) {

            foreach ($_POST['update_totals'] as $_k => $v) {
                if (in_array($_k, ['ot_paid', 'ot_due']))
                    continue;
                if (isset($_POST['action']) && $_POST['action'] == 'new_module') {
                    $cart->clearHiddenModule($_k);
                }
                if (!is_array($update_totals[$_k]))
                    $update_totals[$_k] = [];
                if ($_k != 'ot_tax')
                    $update_totals[$_k]['value'] = $v;
            }

            $update_totals_custom = $_POST['update_totals_custom'];
        }

        if ($ext = Acl::checkExtension('CouponsAndVauchers', 'orderEditCouponVoucher')) {
            $ext::orderEditCouponVoucher();
        }

        $order_total_modules->pre_confirmation_check();

        $order_totals = $order_total_modules->processInAdmin($update_totals);

        if (Yii::$app->request->isPost) {

            if ($ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'prepareDeliveryDate')){
                    global $order_delivery_date;
                    $dd_result = $ext::prepareDeliveryDate(true);
                    if ($dd_result){
                        $update_has_errors = true;
                    }
            }
                
            if (!$update_has_errors && isset($_POST['action']) && $_POST['action'] == 'update') {

                $order->info['customers_email_address'] = tep_db_prepare_input($_POST['update_customer_email_address']);
                $order->info['customers_telephone'] = tep_db_prepare_input($_POST['update_customer_telephone']);
                $order->info['customers_landline'] = tep_db_prepare_input($_POST['update_customer_landline']);

                //echo '<pre>';print_r($cart);die;
                $order->info['comments'] = TEXT_MESSEAGE_SUCCESS;
                if (isset($_POST['comment']) && !empty($_POST['comment'])) {
                    $order->info['comments'] = tep_db_prepare_input($_POST['comment']);
                }

                if (number_format($order->info['total_inc_tax'], 2) > number_format($order->info['total_paid_inc_tax'], 2)) {
                    if (defined('ORDER_STATUS_PART_AMOUNT') && (int) ORDER_STATUS_PART_AMOUNT > 0)
                        $cart->setOrderStatus(ORDER_STATUS_PART_AMOUNT);
                }
                if (number_format($order->info['total_inc_tax'], 2) == number_format($order->info['total_paid_inc_tax'], 2)) {
                    if (defined('ORDER_STATUS_FULL_AMOUNT') && (int) ORDER_STATUS_FULL_AMOUNT > 0)
                        $cart->setOrderStatus(ORDER_STATUS_FULL_AMOUNT);
                }

                if ($customer_loaded) {
                    if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'checkStatus')) {
                        $ext::checkStatus();
                    }
                }
                if (empty($order->info['order_status']) || !$order->info['order_status']) {
                    $order->info['order_status'] = (int) DEFAULT_ORDERS_STATUS_ID;
                }

                if (tep_not_null($oID)) {
                    $order->save_order($oID);
                } else {
                    $order->save_order();
                }

                if ($customer_loaded) {
                    if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'saveOrder')) {
                        $ext::saveOrder();
                    }
                }

                $order->save_details();

                $notify = (strtolower($_POST['notify']) == 'on' ? true : false);

                $order->save_products($notify);

                $order_total_modules->apply_credit(); //ICW ADDED FOR CREDIT CLASS SYSTEM
                $cart->order_id = $order->order_id;
                $admin->saveCustomerBasket($cart);
                
                if ($ext = Acl::checkExtension('CustomerLoyalty', 'afterOrderUpdate')){
                    $ext::afterOrderUpdate((int)$order->order_id);
                }
                
                //unset($_SESSION['cot_gv']);				
                $subaction = Yii::$app->request->post('subaction', '');
                $cart->restoreTotals();

                if (!tep_not_null($oID)) {
                    $oID = $order->order_id;
                    $messageStack->add_session('one_page_checkout', SUCCESS_ORDER_UPDATED, 'success');
                    if ($subaction == 'return') {
                        echo json_encode(['reload' => \yii\helpers\Url::to(['orders/process-order', 'orders_id' => $oID])]);
                    } else {
                        echo json_encode(['reload' => \yii\helpers\Url::to(['orders/order-edit', 'orders_id' => $oID])]);
                    }
                    exit();
                } else {
                    $messageStack->add('one_page_checkout', SUCCESS_ORDER_UPDATED, 'success');
                    if ($subaction == 'return') {
                        echo json_encode(['reload' => \yii\helpers\Url::to(['orders/process-order', 'orders_id' => $oID])]);
                        exit();
                    }
                }
            }
        }
        if ($admin->checkCartOwnerClear($cart)){
            $admin->saveCustomerBasket($cart);
        }

        $gv_amount_current = 0;
        $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $order->customer['customer_id'] . "'");
        if ($gv_result = tep_db_fetch_array($gv_query)) {
            $gv_amount_current = $currencies->format($gv_result['amount'], true, $order->info['currency'], $order->info['currency_value']);
        }

        // New "Status History" table has different format.
        //$OldNewStatusValues = (tep_db_field_exists(TABLE_ORDERS_STATUS_HISTORY, "old_value") && tep_db_field_exists(TABLE_ORDERS_STATUS_HISTORY, "new_value"));
        $CommentsWithStatus = tep_db_field_exists(TABLE_ORDERS_STATUS_HISTORY, "comments");
        $_history = [];
        $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
        if (tep_db_num_rows($orders_history_query)) {
            while ($orders_history = tep_db_fetch_array($orders_history_query)) {
                if ($orders_history['admin_id'] > 0) {
                    $check_admin_query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . (int) $orders_history['admin_id'] . "'");
                    $check_admin = tep_db_fetch_array($check_admin_query);
                    if (is_array($check_admin)) {
                        $orders_history['admin'] = $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                    } else {
                        $orders_history['admin'] = '';
                    }
                }
                $_history[] = $orders_history;
            }
        }
        $orders_statuses = array();
        $orders_status_array = array();
        $orders_status_group_array = array();
        $orders_status_query = tep_db_query("select os.orders_status_id, os.orders_status_name, osg.orders_status_groups_name, osg.orders_status_groups_color from " . TABLE_ORDERS_STATUS . " as os left join " . TABLE_ORDERS_STATUS_GROUPS . " as osg ON os.orders_status_groups_id = osg.orders_status_groups_id where os.language_id = '" . (int) $languages_id . "' and osg.language_id = '" . (int) $languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                'text' => $orders_status['orders_status_name']);
            $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
            $orders_status_group_array[$orders_status['orders_status_id']] = '<i style="background: ' . $orders_status['orders_status_groups_color'] . ';"></i>' . $orders_status['orders_status_groups_name'];
        }

        $response = \common\helpers\Gifts::getGiveAwaysQuery();
        $giveaway_query = $response['giveaway_query'];
        $products = $order->products;
        if (is_array($products)) {
            foreach ($products as $kp => $vp) {
                $products[$kp]['stock_limits'] = \common\helpers\Product::get_product_order_quantity($vp['id']);
                $product_qty = \common\helpers\Product::get_products_stock($vp['id']);
                $stock_indicator = \common\classes\StockIndication::product_info(array(
                            'products_id' => $vp['id'],
                            'products_quantity' => $product_qty,
                ));
                $products[$kp]['stock_info'] = $stock_indicator;
            }
        }

        if (Yii::$app->request->isAjax) {
            echo json_encode([
                'admin_message' => $admin_message,
                'admin_choice' => $admin_choice,
                'cart' => $cart,
                'currentCart' => $currentCart,
                'address_details' => $this->renderAjax('address_details', [
                    'js_arrs' => $js_arrs,
                    'cInfo' => $cInfo,
                    'addresses' => $addresses,
                    'customer_loaded' => $customer_loaded,
                    'aID' => $order->billing['address_book_id'],
                    'saID' => $order->delivery['address_book_id'],
                    'error' => $adress_details['error'],
                    'errors' => $adress_details['errors'],
                    'csa' => (isset($adress_details['data']['csa']) ? $adress_details['data']['csa'] : $order->billing['address_book_id'] == $order->delivery['address_book_id']),
                    'entry_state' => \common\helpers\Zones::get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state),
                    'entry' => $entry,
                ]),
                'shipping_details' => $this->renderAjax('shipping', ['quotes' => $shipping_details['quotes'], 'quotes_radio_buttons' => $shipping_details['quotes_radio_buttons'], 'order' => $order]),
                'payment_details' => $this->renderAjax('payment', ['selection' => $selection,
                    'order' => $order,
                    'gv_amount_current' => $gv_amount_current,
                    'payment' => $payment,
                    'oID' => $oID,
                    'cot_gv_active' => isset($_SESSION['cot_gv']),
                    'custom_gv_amount' => (isset($_SESSION['cot_gv']) && is_numeric($_SESSION['cot_gv'])) ? round($_SESSION['cot_gv'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency), 2) : '',
                    'gv_redeem_code' => ($cc_id ? \common\helpers\Coupon::get_coupon_name($cc_id) : ''),
                ]),
                'products_details' => $this->renderAjax('product_listing', [
                    'products' => $products,
                    'tax_class_array' => $tax_class_array,
                    'currencies' => $currencies,
                    'recalculate' => (USE_MARKET_PRICES == 'True' ? false : true),
                    'oID' => $oID,
                    'cart' => $cart,
                    'giveaway' => [
                        'count' => tep_db_num_rows($giveaway_query)
                    ],
                    'giftWrapExist' => $cart->cart_allow_giftwrap(),
                ]),
                'oID' => $oID,
                'order_total_details' => $this->renderAjax('order_totals', ['inputs' => $order_total_modules->get_all_totals_list(), 'oID' => $oID, 'orders_statuses' => $orders_statuses, 'current_status' => $order->info['order_status'], 'currency' => $currency]),
                'order_statuses' => $this->renderAjax('order_statuses', ['CommentsWithStatus' => $CommentsWithStatus, 'orders_history_items' => $_history, 'orders_statuses' => $orders_statuses, 'orders_status_array' => $orders_status_array, 'orders_status_group_array' => $orders_status_group_array]),
                'message' => (count($messageStack->messages) ? $this->renderAjax('message', ['messagestack' => $messageStack]) : ''),
                'gv_redeem_code' => ($cc_id ? \common\helpers\Coupon::get_coupon_name($cc_id) : ''),
            ]);
            $customer->convertBackSession();
            exit();
        } else {
            $rendering = $this->render('edit', [
                'admin_message' => $admin_message,
                'admin_choice' => $admin_choice,
                'cart' => $cart,
                'currentCart' => $currentCart,
                'address_details' => $this->renderAjax('address_details', [
                    'js_arrs' => $js_arrs,
                    'cInfo' => $cInfo,
                    'addresses' => $addresses,
                    'customer_loaded' => $customer_loaded,
                    'aID' => $order->billing['address_book_id'],
                    'saID' => $order->delivery['address_book_id'],
                    'error' => $adress_details['error'],
                    'errors' => $adress_details['errors'],
                    'csa' => (isset($adress_details['data']['csa']) ? $adress_details['data']['csa'] : $order->billing['address_book_id'] == $order->delivery['address_book_id']),
                    'entry_state' => \common\helpers\Zones::get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state),
                    'entry' => $entry,
                ]),
                'content' => '',
                'form_params' => \common\helpers\Output::get_all_get_params(array('action', 'paycc')) . 'action=update_order',
                'oID' => $oID,
                'order' => $order,
                'shipping_details' => $shipping_details,
                'selection' => $selection,
                'gv_amount_current' => $gv_amount_current,
                'shipping' => $shipping,
                'payment' => $payment,
                'products_details' => $this->renderAjax('product_listing', [
                    'products' => $products,
                    'tax_class_array' => $tax_class_array,
                    'currencies' => $currencies,
                    'recalculate' => (USE_MARKET_PRICES == 'True' ? false : true),
                    'oID' => $oID,
                    'cart' => $cart,
                    'giveaway' => [
                        'count' => tep_db_num_rows($giveaway_query)
                    ],
                    'giftWrapExist' => $cart->cart_allow_giftwrap(),
                ]),
                'order_total_details' => $this->renderAjax('order_totals', ['inputs' => $order_total_modules->get_all_totals_list(), 'oID' => $oID, 'orders_statuses' => $orders_statuses, 'current_status' => $order->info['order_status'], 'currency' => $currency]),
                'order_statuses' => $this->renderAjax('order_statuses', ['CommentsWithStatus' => $CommentsWithStatus, 'orders_history_items' => $_history, 'orders_statuses' => $orders_statuses, 'orders_status_array' => $orders_status_array, 'orders_status_group_array' => $orders_status_group_array]),
                'message' => (count($messageStack->messages) ? $this->renderAjax('message', ['messagestack' => $messageStack]) : ''),
                'gv_redeem_code' => ($cc_id ? \common\helpers\Coupon::get_coupon_name($cc_id) : ''),
                'cot_gv_active' => isset($_SESSION['cot_gv']),
                'custom_gv_amount' => (isset($_SESSION['cot_gv']) && is_numeric($_SESSION['cot_gv'])) ? round($_SESSION['cot_gv'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency), 2) : ''
            ]);
            $customer->convertBackSession();
            return $rendering;
        }
    }

    public function actionResetAdmin() {
        $basket_id = Yii::$app->request->post('basket_id');
        $customer_id = Yii::$app->request->post('customer_id');
        $orders_id = Yii::$app->request->post('orders_id', 0);
        $admin = new AdminCarts();
        if ($basket_id && $customer_id) {
            $admin->relocateCart($basket_id, $customer_id);
        }
        if ($orders_id) {
            $reload = \yii\helpers\Url::to(['order-edit', 'orders_id' => $orders_id]);
        } else {
            $reload = \yii\helpers\Url::to(['order-edit']);
        }
        echo json_encode(['reload' => $reload]);
        exit();
    }

    public function actionResetCart() {
        $id = Yii::$app->request->get('id');
        $admin = new AdminCarts();
        $admin->setLastVirtualID($id);
        return $this->redirect('order-edit');
    }
    
    public function actionDeletecart(){
        $id = Yii::$app->request->post('deleteCart');
        $admin = new AdminCarts();
        $_cb = explode("-", $id);
        if ($admin->deleteCartByBC($_cb[0], $_cb[1])){
            $ids = $admin->getVirtualCartIDs();
            if ($ids){
                $_last = $admin->getLastVirtualID();
                if (!in_array($_last, $ids)){ // last was deleted
                    echo json_encode(['goto' => \yii\helpers\Url::to(['orders/order-edit', 'currentCart'  =>$ids[0]]) ]);
                    exit();
                }
            } else {
                echo json_encode(['goto' => \yii\helpers\Url::to(['orders/']) ]);
                exit();
            }
        }
        echo json_encode(['reload' => true]);
        exit();
    }

    public function actionOrderupdatesubmit() {

        global $languages_id, $language, $GLOBALS;

        $this->layout = false;

        \common\helpers\Translation::init('admin/orders/order-edit');

        $order_updated = true;
        if ($order_updated == true) {
            $messageType = 'success';
            $message = SUCCESS_ORDER_UPDATED;
            //$messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
            $messageType = 'warning';
            $message = WARNING_ORDER_NOT_UPDATED;
            //$messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
                        <?= $message ?>
                    </div> 
                </div>   
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div>  
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>

        <?php
        $this->actionOrderEdit();
    }

    public function actionOrderdelete() {

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');
        
        $admin = new AdminCarts;
        $admin->deleteCartByOrder($orders_id);

        \common\helpers\Order::remove_order($orders_id, $_POST['restock']);
    }

    public function actionConfirmorderdelete() {

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Wrong order data.");
        }

        $oInfo = new \objectInfo($orders);

        echo tep_draw_form('orders', FILENAME_ORDERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=deleteconfirm', 'post', 'id="orders_edit" onSubmit="return deleteOrder();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_ORDER . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA . ':</div><div>' . $oInfo->customers_name . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA_OID . ':</div><div>' . $oInfo->orders_id . '</div></div>';

        echo '</div>';
        $order_stock_updated = \common\helpers\Order::is_stock_updated($oInfo->orders_id);
        echo '<div class="col_desc_check">' .
        tep_draw_checkbox_field('restock', 'on', $order_stock_updated, '', ($order_stock_updated ? '' : 'disabled="disabled" readonly="readonly"')) . '<span>' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY . '</span>' .
        '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <?php
            echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()">';
            echo tep_draw_hidden_field('orders_id', $oInfo->orders_id);
            ?>
        </div>
        </form>
        <?php
    }

    public function actionCreate() {
        global $language, $messageStack;
        $back = 'orders';
        if (isset($_GET['back'])) {
            $back = $_GET['back'];
        }
        $this->view->backOption = $back;

        $this->selectedMenu = array('customers', 'orders/createorder');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->convert = false;

        if (isset($_GET['Customer'])) {
            $account_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_GET['Customer'] . "'");
            $account = tep_db_fetch_array($account_query);
            if (isset($account['customers_id'])) {
                if (isset($_GET['convert'])) {
                    $this->view->convert = true;
                }
            }
            $customer = $account['customers_id'];
            $own_aid = 0;
            $aID = 0;
            $address = [];
            if ($_GET['aID'] > 0) {
                $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where address_book_id='" . (int) $_GET['aID'] . "'");
                $own_aid = tep_db_num_rows($address_query);
                $address = tep_db_fetch_array($address_query);
                $aID = $address['address_book_id'];
            }

            if ($address['customers_id'] != $_GET['Customer']) {
                $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_GET['Customer'] . "' and address_book_id='" . (int)$account['customers_default_address_id'] . "'");
                $address = tep_db_fetch_array($address_query);
                $aID = $address['address_book_id'];
            }
            $temp = [];
            array_walk($address, function($value, $key, $prefix) use (&$temp) {
                $temp[$prefix . $key] = $value;
            }, 's_');
            $address = array_merge($temp, $address);
            $info_array = array_merge($address, $account);

            $cInfo = new \objectInfo($info_array);
            $cInfo->platform_id = $account['platform_id'];
            $resault = $this->getAddresses($customer);
            $js_arrs = $resault[0];
            $addresses = $resault[1];

            $entry = new \stdClass();
            $entry->customer_id = $customer;
            $entry->zones_array = null;
            $entry->countries = \common\helpers\Country::get_countries();
            $zones = \common\helpers\Zones::get_country_zones($cInfo->entry_country_id);
            $entry->entry_state_has_zones = false;
            $entry->s_entry_state_has_zones = false;
            if (is_array($zones) && count($zones)) {
                $entry->entry_state_has_zones = true;
                $entry->s_entry_state_has_zones = true;
                $entry->zones_array = $zones;
                $entry->s_zones_array = $zones;
                $entry->entry_state = $cInfo->entry_zone_id;
                $entry->s_entry_state = $entry->entry_state;
            } else {
                $entry->entry_state = \common\helpers\Zones::get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
                $entry->s_entry_state = $entry->entry_state;
            }


            $this->loadPlatformDetails($entry);

            $saID = $aID;
            $error = false;
            $errors = new \stdClass;

            if (Yii::$app->request->isAjax) {

                return $this->renderAjax('customer_details', [
                            'js_arrs' => $js_arrs,
                            'cInfo' => $cInfo,
                            'addresses' => $addresses,
                            'aID' => $aID,
                            'saID' => $saID,
                            'error' => $error,
                            'errors' => $errors,
                            'entry' => $entry,
                            'csa' => $saID == $aID,
                            'message' => (count($messageStack->messages) ? $this->renderAjax('message', ['messagestack' => $messageStack]) : ''),
                ]);
            }

            //$content = '<div class="box-show-customer">Please choose Customer.</div>';
            //$content .= '<button class="btn btn-back" onclick="return resetStatement()">' . IMAGE_BACK . '</button>';
            return $this->render('create', ['content' => $this->renderAjax('customer_details', [
                            'js_arrs' => $js_arrs,
                            'cInfo' => $cInfo,
                            'addresses' => $addresses,
                            'aID' => $aID,
                            'saID' => $saID,
                            'error' => $error,
                            'errors' => $errors,
                            'entry' => $entry,
                            'csa' => $saID == $aID,
                            'message' => (count($messageStack->messages) ? $this->renderAjax('message', ['messagestack' => $messageStack]) : ''),
                        ])
            ]);
        }

        return $this->render('create', ['content' => $content, 'message' => (count($messageStack->messages) ? $this->renderAjax('message', ['messagestack' => $messageStack]) : '')]);
    }

    public function LoadPlatformDetails($entry, $platform = 0) {
        $entry->platforms = platform::getList(false);
        if (!$platform) {
            $platform = platform::defaultId();
        }
        $entry->default_platform = $platform;
        $platform_config = new platform_config($entry->default_platform);

        //currency
        $platform_currencies = $platform_config->getAllowedCurrencies();
        if ($platform_currencies) {
            $_tmp = [];
            foreach ($platform_currencies as $pc) {
                $_tmp[] = ['id' => $pc, 'text' => $pc];
            }
            $entry->platform_currencies = $_tmp;
        } else {
            $entry->platform_currencies = [['id' => DEFAULT_CURRENCY, 'text' => DEFAULT_CURRENCY]];
        }
        if ($this->view->convert && isset($_GET['basket_id'])) {
            $params = tep_db_fetch_array(tep_db_query("select currency, language_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $entry->customer_id . "' and basket_id = '" . (int) $_GET['basket_id'] . "'"));
        }
        if ($this->view->convert && isset($params['currency']) && tep_not_null($params['currency'])) {
            $entry->defualt_platform_currency = $params['currency'];
        } elseif ($c = $platform_config->getDefaultCurrency()) {
            $entry->defualt_platform_currency = $c;
        } else {
            $entry->defualt_platform_currency = DEFAULT_CURRENCY;
        }

        //language
        global $lng;
        $platform_languages = $platform_config->getAllowedLanguages();
        if ($platform_languages) {
            $_tmp = [];
            foreach ($platform_languages as $pl) {
                $_tmp[] = ['id' => $lng->catalog_languages[$pl]['id'], 'text' => $lng->catalog_languages[$pl]['name']];
            }
            $entry->platform_languages = $_tmp;
        } else {
            $entry->platform_languages = [['id' => $lng->catalog_languages[DEFAULT_LANGUAGE]['id'], 'text' => $lng->catalog_languages[DEFAULT_LANGUAGE]['name']]];
        }

        if ($this->view->convert && isset($params['language_id']) && $params['language_id'] > 0) {
            $entry->defualt_platform_language = $params['language_id'];
        } elseif ($c = $platform_config->getDefaultLanguage()) {
            $entry->defualt_platform_language = $lng->catalog_languages[$c]['id'];
        } else {
            $entry->defualt_platform_language = $lng->catalog_languages[DEFAULT_LANGUAGE]['id'];
        }
    }

    public function actionGetPlatformDetails() {
        $paltform_id = Yii::$app->request->get('platform_id', 0);
        if ($paltform_id) {
            $entry = new \stdClass();
            $this->loadPlatformDetails($entry, $paltform_id);
            return $this->renderAjax('currency_language', ['entry' => $entry]);
        }
        return '';
    }

    public function getAddresses($customer) {
        global $languages_id;
        $fields = array("entry_street_address", "entry_firstname", "entry_lastname", "entry_city", "entry_postcode", "entry_country_id", "entry_country");
        if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_gender";
        }
        /* if (ACCOUNT_COMPANY == 'true') {
          $fields[] = "entry_company";
          } */
        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_state";
        }
        if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_suburb";
        }

        $js_arrs = 'var fields = new Array("' . implode('", "', $fields) . '");' . "\n";

        foreach ($fields as $field) {
            $js_arrs .= 'var ' . $field . ' = new Array();' . "\n";
        }

        $address_query = tep_db_query("select ab.*, c.countries_name  from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int) $languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where customers_id = '" . (int)$customer . "' ");
        $addresses = array();
        foreach ($fields as $field) {
            $js_arrs .= '' . $field . '[0] = "";' . "\n";
        }
        while ($d = tep_db_fetch_array($address_query)) {
            $state = $d['entry_state'];
            foreach ($fields as $field) {
                if ($field == "entry_state" && !tep_not_null($d['entry_state']) && $d['entry_zone_id']) {
                    $d[$field] = $d['entry_zone_id'];
                    $state = \common\helpers\Zones::get_zone_name($d['entry_country_id'], $d['entry_zone_id'], '');
                }
                if ($field == "entry_country")
                    $d[$field] = \common\helpers\Country::get_country_name($d['entry_country_id']);
                $js_arrs .= '' . $field . '[' . $d['address_book_id'] . '] = "' . $d[$field] . '";' . "\n";
            }
            $addresses[] = array(
                'id' => $d['address_book_id'],
                'text' => $d['entry_company'] . ' ' . $d['entry_firstname'] . ' ' . $d['entry_lastname'] . ' ' . $d['entry_suburb'] . ' ' . $d['entry_city'] . ' ' . $state . ' ' . $d['entry_postcode'] . ' ' . $d['countries_name'],
            );
        }
        return [$js_arrs, $addresses];
    }

    public function getOrderAddresses($oID) {
        global $languages_id;
        $fields = array("entry_street_address", "entry_firstname", "entry_lastname", "entry_city", "entry_postcode", "entry_country_id", "entry_country");
        if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_gender";
        }
        /* if (ACCOUNT_COMPANY == 'true') {
          $fields[] = "entry_company";
          } */
        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_state";
        }
        if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $fields[] = "entry_suburb";
        }

        $js_arrs = 'var fields = new Array("' . implode('", "', $fields) . '");' . "\n";

        foreach ($fields as $field) {
            //if ($field == 'entry_country_id') $field = "entry_country";
            $js_arrs .= 'var ' . $field . ' = new Array();' . "\n";
        }

        $address_query = tep_db_query("select * from " . TABLE_ORDERS . "  where orders_id = '" . (int) $oID . "' ");
        $addresses = array();
        foreach ($fields as $field) {
            $js_arrs .= '' . $field . '[0] = "";' . "\n";
        }
        $d = $t = tep_db_fetch_array($address_query);
        foreach ($fields as $field) {
            if ($field == "entry_state" && !tep_not_null($d['delivery_state'])) {
                if ($zone_id = \common\helpers\Zones::get_zone_id(\common\helpers\Country::get_country_id($d['delivery_country']), $d['delivery_state'])) {
                    $d[$field] = $zone_id;
                }
            }
            //if ($field == 'entry_country_id') $field = "entry_country";
            $js_arrs .= '' . $field . '[' . $d['delivery_address_book_id'] . '] = "' . $d['delivery_' . substr($field, 6)] . '";' . "\n";
        }
        $addresses[] = array(
            'id' => $d['delivery_address_book_id'],
            'text' => $d['delivery_company'] . ' ' . $d['delivery_firstname'] . ' ' . $d['delivery_lastname'] . ' ' . $d['delivery_suburb'] . ' ' . $d['delivery_city'] . ' ' . $d['delivery_state'] . ' ' . $d['delivery_postcode'] . ' ' . $d['delivery_country'],
        );
        $d = $t;
        if ($d['delivery_address_book_id'] != $d['billing_address_book_id']) {
            foreach ($fields as $field) {
                if ($field == "entry_state" && !tep_not_null($d['billing_state'])) {
                    if ($zone_id = \common\helpers\Zones::get_zone_id(\common\helpers\Country::get_country_id($d['billing_country']), $d['billing_state'])) {
                        $d[$field] = $zone_id;
                    }
                }
                //if ($field == 'entry_country_id') $field = "entry_country";			  
                $js_arrs .= '' . $field . '[' . $d['billing_address_book_id'] . '] = "' . $d['billing_' . substr($field, 6)] . '";' . "\n";
            }

            $addresses[] = array(
                'id' => $d['billing_address_book_id'],
                'text' => $d['billing_company'] . ' ' . $d['billing_firstname'] . ' ' . $d['billing_lastname'] . ' ' . $d['billing_suburb'] . ' ' . $d['billing_city'] . ' ' . $d['billing_state'] . ' ' . $d['billing_postcode'] . ' ' . $d['billing_country'],
            );
        }


        return [$js_arrs, $addresses];
    }

    public function actionGetStates() {
        $response = '';
        if (Yii::$app->request->isPost) {
            $country_id = Yii::$app->request->post('country_id', 0);
            $prefix = Yii::$app->request->post('prefix', 0);
            $value = Yii::$app->request->post('value', '');
            $def_country_id = Yii::$app->request->post('def_country_id', 0);
            if ($country_id) {
                $zones = \common\helpers\Zones::get_country_zones($country_id);
                if (is_array($zones) && count($zones)) {
                    if (!is_numeric($value)) {
                        $value = \common\helpers\Zones::get_zone_id($country_id, $value);
                    }
                    $response = tep_draw_pull_down_menu($prefix . 'entry_state', $zones, $value, 'class="form-control"');
                } else {
                    $def_zones = \common\helpers\Zones::get_country_zones($def_country_id);
                    if (is_array($def_zones) && count($def_zones)) {
                        $hepler = \yii\helpers\ArrayHelper::map($def_zones, 'id', 'text');
                        $value = $hepler[$value];
                    }
                    $response = tep_draw_input_field($prefix . 'entry_state', $value, 'class="form-control"');
                }
            }
        }
        echo $response;
        exit();
    }

    public function checkDetails() {

        $error = false;
        $saID = (int) $_POST['saID'];
        $aID = (int) $_POST['aID'];
        $csa = strtolower($_POST['csa']) == 'on' ? true : false;
        if ($csa)
            $aID = $saID;

        $company = tep_db_prepare_input($_POST['customers_company']);
        $company_vat = tep_db_prepare_input($_POST['customers_company_vat']);

        $errors = new \stdClass;
        $data = ['shipto' => ['saID' => $saID],
            'billto' => ['aID' => $aID],
            'csa' => $csa,
            'company' => $company,
            'company_vat' => $company_vat,
        ];

        if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_gender = tep_db_prepare_input($_POST['s_entry_gender']);
            if (in_array(ACCOUNT_GENDER, ['required', 'required_register'])) {
                if (($s_gender != 'm') && ($s_gender != 'f') && ($s_gender != 's')) {
                    $error = true;
                    $errors->s_entry_gender_error = true;
                }
            }
            $data['shipto']['gender'] = $s_gender;
        }

        //if (!$saID){
        if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_firstname = tep_db_prepare_input($_POST['s_entry_firstname']);
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                if (strlen($s_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                    $error = true;
                    $errors->s_entry_firstname_error = true;
                }
            }
            $data['shipto']['firstname'] = $s_firstname;
        }

        if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_lastname = tep_db_prepare_input($_POST['s_entry_lastname']);
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                if (strlen($s_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                    $error = true;
                    $errors->s_entry_lastname_error = true;
                }
            }
            $data['shipto']['lastname'] = $s_lastname;
        }

        if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_postcode = tep_db_prepare_input($_POST['s_entry_postcode']);
            if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register']) && strlen($s_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                $error = true;
                $errors->s_entry_post_code_error = true;
            }
            $data['shipto']['postcode'] = $s_postcode;
        }

        if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_street_address = tep_db_prepare_input($_POST['s_entry_street_address']);
            if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register']) && strlen($s_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                $error = true;
                $errors->s_entry_street_address_error = true;
            }
            $data['shipto']['street_address'] = $s_street_address;
        }

        if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_suburb = tep_db_prepare_input($_POST['s_entry_suburb']);
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register']) && empty($s_suburb)) {
                $error = true;
                $errors->s_entry_suburb_error = true;
            }
            $data['shipto']['suburb'] = $s_suburb;
        }

        if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_city = tep_db_prepare_input($_POST['s_entry_city']);
            if (in_array(ACCOUNT_CITY, ['required', 'required_register']) && strlen($s_city) < ENTRY_CITY_MIN_LENGTH) {
                $error = true;
                $errors->s_entry_city_error = true;
            }
            $data['shipto']['city'] = $s_city;
        }

        $s_entry_country_id = tep_db_prepare_input($_POST['s_entry_country_id']);
        if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
            if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register']) && (int) $s_entry_country_id == 0) {
                $error = true;
                $errors->s_entry_country_error = true;
            }
            $data['shipto']['country_id'] = $s_entry_country_id;
        }

        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $s_state = tep_db_prepare_input($_POST['s_entry_state']);
            $zones = \common\helpers\Zones::get_country_zones($s_entry_country_id);
            if (is_array($zones) && count($zones)) {
                if (isset($_POST['s_entry_zone_id'])) {
                    $s_state = tep_db_prepare_input($_POST['s_entry_zone_id']);
                }
                $zones = \yii\helpers\ArrayHelper::map($zones, 'id', 'id');
                if (in_array(ACCOUNT_STATE, ['required', 'required_register']) && !in_array($s_state, $zones)) {
                    $error = true;
                    $errors->s_entry_state_error = true;
                } else {
                    $data['shipto']['zone_id'] = $s_state;
                    $data['shipto']['state'] = \common\helpers\Zones::get_zone_name($s_entry_country_id, $s_state, '');
                }
            } else if (strlen($s_state) < ENTRY_STATE_MIN_LENGTH && in_array(ACCOUNT_STATE, ['required', 'required_register'])) {
                $error = true;
                $errors->s_entry_state_error = true;
            } else {
                $data['shipto']['zone_id'] = 0;
                $data['shipto']['state'] = $s_state;
            }
        }
        //}

        if (!$csa) {

            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $gender = tep_db_prepare_input($_POST['entry_gender']);
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register'])) {
                    if (($gender != 'm') && ($gender != 'f') && ($gender != 's')) {
                        $error = true;
                        $errors->entry_gender_error = true;
                    }
                }
                $data['billto']['gender'] = $gender;
            }

            //if (!$saID){
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $firstname = tep_db_prepare_input($_POST['entry_firstname']);
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                        $error = true;
                        $errors->entry_firstname_error = true;
                    }
                }
                $data['billto']['firstname'] = $firstname;
            }

            if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $lastname = tep_db_prepare_input($_POST['entry_lastname']);
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])) {
                    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                        $error = true;
                        $errors->entry_lastname_error = true;
                    }
                }
                $data['billto']['lastname'] = $lastname;
            }

            if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $postcode = tep_db_prepare_input($_POST['entry_postcode']);
                if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register']) && strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                    $error = true;
                    $errors->entry_post_code_error = true;
                }
                $data['billto']['postcode'] = $postcode;
            }

            if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                $street_address = tep_db_prepare_input($_POST['entry_street_address']);
                if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register']) && strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                    $error = true;
                    $errors->entry_street_address_error = true;
                }
                $data['billto']['street_address'] = $street_address;
            }

            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                $suburb = tep_db_prepare_input($_POST['entry_suburb']);
                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register']) && empty($suburb)) {
                    $error = true;
                    $errors->entry_suburb_error = true;
                }
                $data['billto']['suburb'] = $suburb;
            }

            if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $city = tep_db_prepare_input($_POST['entry_city']);
                if (in_array(ACCOUNT_CITY, ['required', 'required_register']) && strlen($city) < ENTRY_CITY_MIN_LENGTH) {
                    $error = true;
                    $errors->entry_city_error = true;
                }
                $data['billto']['city'] = $city;
            }

            $entry_country_id = tep_db_prepare_input($_POST['entry_country_id']);
            if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
                if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register']) && (int) $entry_country_id == 0) {
                    $error = true;
                    $errors->entry_country_error = true;
                }
                $data['billto']['country_id'] = $entry_country_id;
            }

            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $state = tep_db_prepare_input($_POST['entry_state']);
                $zones = \common\helpers\Zones::get_country_zones($entry_country_id);
                if (is_array($zones) && count($zones)) {
                    if (isset($_POST['entry_zone_id'])) {
                        $state = tep_db_prepare_input($_POST['entry_zone_id']);
                    }
                    $zones = \yii\helpers\ArrayHelper::map($zones, 'id', 'id');
                    if (in_array(ACCOUNT_STATE, ['required', 'required_register']) && !in_array($state, $zones)) {
                        $error = true;
                        $errors->entry_state_error = true;
                    } else {
                        $data['billto']['zone_id'] = $state;
                        $data['billto']['state'] = \common\helpers\Zones::get_zone_name($entry_country_id, $state, '');
                    }
                } else if (strlen($state) < ENTRY_STATE_MIN_LENGTH && in_array(ACCOUNT_STATE, ['required', 'required_register'])) {
                    $error = true;
                    $errors->entry_state_error = true;
                } else {
                    $data['billto']['zone_id'] = 0;
                    $data['billto']['state'] = $state;
                }
            }
        }

        return ['error' => $error, 'errors' => $errors, 'data' => $data];
    }

    public function updateAddress($result) {
        global $customer_id;
        if (is_array($result) && $customer_id) {
//shipping addres
            $company = $result['data']['customers_company'];
            $company_vat = $result['data']['customers_company_vat'];
            $sql_data_array = [
                'entry_firstname' => $result['data']['shipto']['firstname'],
                'entry_lastname' => $result['data']['shipto']['lastname'],
                'entry_street_address' => $result['data']['shipto']['street_address'],
                'entry_postcode' => $result['data']['shipto']['postcode'],
                'entry_city' => $result['data']['shipto']['city'],
                'entry_country_id' => $result['data']['shipto']['country_id'],
                'entry_state' => $result['data']['shipto']['state'],
                'entry_zone_id' => $result['data']['shipto']['zone_id'],
            ];
            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['entry_gender'] = $result['data']['shipto']['gender'];
            if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['entry_suburb'] = $result['data']['shipto']['suburb'];
            if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['entry_company'] = $company;
            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register']))
                $sql_data_array['entry_company_vat'] = $company_vat;
            if ($result['data']['shipto']['saID'] > 0) {
                //update

                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int) $result['data']['shipto']['saID'] . "'");
                $saID = $result['data']['shipto']['saID'];
            } else {
                //insert
                $sql_data_array['customers_id'] = $customer_id;
                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                $saID = tep_db_insert_id();
            }

            if (!$result['data']['csa']) {
                $sql_data_array = [
                    'entry_firstname' => $result['data']['billto']['firstname'],
                    'entry_lastname' => $result['data']['billto']['lastname'],
                    'entry_street_address' => $result['data']['billto']['street_address'],
                    'entry_postcode' => $result['data']['billto']['postcode'],
                    'entry_city' => $result['data']['billto']['city'],
                    'entry_country_id' => $result['data']['billto']['country_id'],
                    'entry_state' => $result['data']['billto']['state'],
                    'entry_zone_id' => $result['data']['billto']['zone_id'],
                ];
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register']))
                    $sql_data_array['entry_gender'] = $result['data']['billto']['gender'];
                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register']))
                    $sql_data_array['entry_suburb'] = $result['data']['billto']['suburb'];
                if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']))
                    $sql_data_array['entry_company'] = $company;
                if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register']))
                    $sql_data_array['entry_company_vat'] = $company_vat;
                if ($result['data']['billto']['aID'] > 0) {
                    //update
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int) $result['data']['billto']['aID'] . "'");
                    $aID = $result['data']['billto']['aID'];
                } else {
                    //insert
                    $sql_data_array['customers_id'] = $customer_id;
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
                    $aID = tep_db_insert_id();
                }
            } else {
                $aID = $saID;
            }
        }
        return ['aID' => $aID, 'saID' => $saID];
    }

    public function actionCreateorderprocess() {
        global $languages_id, $language, $currencies, $login_id, $GLOBALS, $cart_address_id, $sendto, $billto;
        global $cart, $order, $shipping_weight, $shipping_num_boxes, $adress_details;
        global $shipping_modules, $shipping, $messageStack;

        \common\helpers\Translation::init('admin/orders');

        if (!isset($_POST['customers_id']) || !($_POST['customers_id'] > 0)) {
            $messageStack->add_session(TEXT_CUSTOMER_IS_NOT_SELECTED, 'warning');
            $this->redirect(FILENAME_ORDERS . '/createorder');
        }

        if (!is_object($currencies)) {
            $currencies = new currencies();
        }

        $orders_type = tep_db_prepare_input($_POST['orders_type']);
        $platform_id = (int) $_POST['platform_id'];

        $admin_id = $login_id;

        if (!tep_session_is_registered('adress_details')) {
            tep_session_register('adress_details');
        }
        if (!tep_session_is_registered('sendto'))
            tep_session_register('sendto');
        if (!tep_session_is_registered('billto'))
            tep_session_register('billto');


        $customer_id = tep_db_prepare_input($_POST['customers_id']);
        $account_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
        $account = tep_db_fetch_array($account_query);
        if (isset($account['customers_id'])) {
            $this->view->autoSubmit = true;
            if (isset($_GET['convert'])) {
                $this->view->convert = true;
            }
        }

        $aID = (int) $_POST['aID'];
        $saID = (int) $_POST['saID'];
        $adress_details = $this->checkDetails();
        if ($adress_details['data']['csa']) {
            $aID = $saID;
        }

        $render_template = 'customer_details';

        if ($adress_details['error']) {
            $address = [];
            \common\helpers\Translation::init('admin/orders/create');
            $own_aid = 0;
            $ads = [];
            $temp = [];
            if ($_POST['saID'] > 0) {
                $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where address_book_id='" . (int) $_POST['saID'] . "'");
                $own_aid = tep_db_num_rows($address_query);
                $address = tep_db_fetch_array($address_query);
                $saID = $address['address_book_id'];
            } else {
                $address['entry_street_address'] = $_POST['s_entry_street_address'];
                $address['entry_suburb'] = $_POST['s_entry_suburb'];
                $address['entry_postcode'] = $_POST['s_entry_postcode'];
                $address['entry_city'] = $_POST['s_entry_city'];
                $address['entry_state'] = $_POST['s_entry_state'];
                $address['entry_gender'] = $_POST['s_entry_gender'];
            }
            array_walk($address, function($value, $key, $prefix) use (&$temp) {
                $temp[$prefix . $key] = $value;
            }, 's_');
            $ads = $temp;

            if ($_POST['aID'] > 0) {
                $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where address_book_id='" . (int) $_POST['aID'] . "'");
                $own_aid = tep_db_num_rows($address_query);
                $address = tep_db_fetch_array($address_query);
                $aID = $address['address_book_id'];
            } else {
                $address['entry_street_address'] = $_POST['entry_street_address'];
                $address['entry_suburb'] = $_POST['entry_suburb'];
                $address['entry_postcode'] = $_POST['entry_postcode'];
                $address['entry_city'] = $_POST['entry_city'];
                $address['entry_state'] = $_POST['entry_state'];
                $address['entry_country_id'] = $_POST['entry_country_id'];
                $address['entry_gender'] = $_POST['entry_gender'];
            }
            $ads = array_merge($ads, $address);

            $info_array = array_merge($ads, $account);
            $cInfo = new \objectInfo($info_array);
            $cInfo->platform_id = $platform_id;
            $_a = $this->getAddresses($customer_id);
            $js_arrs = $_a[0];
            $addresses = $_a[1];
            $entry = new \stdClass;
            $entry->zones_array = null;
            $entry->countries = \common\helpers\Country::get_countries();
            $zones = \common\helpers\Zones::get_country_zones($cInfo->entry_country_id);
            $entry->entry_state_has_zones = false;
            if (is_array($zones) && count($zones)) {
                $entry->entry_state_has_zones = true;
                $entry->zones_array = $zones;
            }

            $this->loadPlatformDetails($entry);

            if (isset($_POST['action']) && $_POST['action'] == 'only_address') {
                $render_template = 'address_details';
            }
            $render = $this->renderAjax($render_template, [
                'js_arrs' => $js_arrs,
                'cInfo' => $cInfo,
                'addresses' => $addresses,
                'aID' => $aID,
                'sID' => $saID,
                'error' => $adress_details['error'],
                'errors' => $adress_details['errors'],
                'csa' => $adress_details['data']['csa'],
                'entry_state' => \common\helpers\Zones::get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state),
                'entry' => $entry,
            ]);

            if (isset($_POST['action']) && $_POST['action'] == 'only_address') {
                echo json_encode(['error' => true, 'data' => $render]);
                exit();
            } else {
                return $render;
            }
        } else {

            if (is_array($adress_details['data'])) {

                $sa = $this->updateAddress($adress_details);
                $saID = $sa['saID'];
                $aID = $sa['aID'];
                $sendto = $saID;
                $billto = $aID;

                if (isset($_POST['action']) && $_POST['action'] == 'only_address') {
                    $_GET['orders_id'] = $_POST['orders_id'];
                    $_SESSION['sendto'] = $saID;
                    if (!tep_session_is_registered('cart_address_id'))
                        tep_session_register('cart_address_id');
                    $cart_address_id = $saID;
                    if (strtolower($_POST['csa']) == 'on') {
                        $_SESSION['billto'] = $saID;
                    } else {
                        $_SESSION['billto'] = $aID;
                    }

                    return $this->actionOrderEdit();
                }
            }
        }

        global $cart;

        $customer = new Customer();
        if ($customer->loadCustomer($customer_id)) {
            $customer->setParam('sendto', $saID);
            $customer->setParam('billto', $aID);
            $customer->convertToSession();
        }
        $session = new \yii\web\Session;

        $session['customer_id'] = $customer_id;

        $cart = new \common\classes\shopping_cart();
        $_SESSION['cart'] = &$cart;
        $cart->address = ['sendto' => $saID, 'billto' => $aID];
        tep_session_unregister('cot_gv');
        tep_session_unregister('cc_id');

        $platform_config = new platform_config($platform_id);
        $platform_config->constant_up();
        $currency = $_POST['currency'];
        $language_id = $_POST['language_id'];

        $cart->setPlatform($platform_id)
                ->setCurrency($currency)
                ->setLanguage($language_id)
                ->setAdmin($admin_id)
                ->setCustomer($customer_id)
                ->setBasketID(0);

        if ($ext = Acl::checkExtension('RecoverShoppingCart', 'convertCart')) {
            $ext::convertCart();
        }

        $admin = new AdminCarts();
        $admin->updateCustomersBasket($cart);
        $admin->setCurrentCartID($cart->customer_id . '-' . $cart->basketID, true);

        $message = 'New order created. Please wait before continuing to edit.';
        $messageType = 'success';
        Yii::$app->session->setFlash($message, $messageType);
        if ($customer) {
            $customer->convertBackSession();
        }

        echo '<script>window.location.href="' . \yii\helpers\Url::to(['orders/order-edit', 'orders_id' => $order->order_id]) . '"</script>';
        exit();
    }

    public function actionAddproduct() {
        global $order, $languages_id, $language, $currencies, $cart, $customer_groups_id, $currency, $messageStack;

        $oID = Yii::$app->request->get('orders_id', '');
        $currentCart = isset($_GET['currentCart'])?$_GET['currentCart']:$_POST['currentCart'];

        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('admin/orders/order-edit');

        if (!is_object($currencies)) {
            $currencies = new currencies();
        }

        $admin = new AdminCarts();
        $admin->loadCustomersBaskets();
        if (tep_not_null($oID)) {
            $admin->setCurrentCartID($currentCart);
        } else{
            $admin->setCurrentCartID($currentCart, true);
        }        
        $admin->loadCurrentCart();

        $params['oID'] = $oID;
        $params['search'] = $_GET['search'];
        $params['action'] = $_GET['action'];

        /* if (tep_session_is_registered('cart')) {
          $cart = &$_SESSION['cart'];
          } else {
          //	$cart = new \common\classes\shopping_cart($oID);
          //	$_SESSION['cart'] = &$cart;
          } */

        if (tep_not_null($oID)) {
            $info = tep_db_fetch_array(tep_db_query("select customers_id, language_id, platform_id, currency, basket_id, delivery_address_book_id, billing_address_book_id from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'"));
            $customer_id = $info['customers_id'];
            $currency = $info['currency'];
            $language_id = $info['language_id'];
            $paltform_id = $info['platform_id'];
            $basket_id = $info['basket_id'];
        } else {
            $customer_id = $cart->customer_id;
            $currency = $cart->currency;
            $language_id = $cart->language_id;
            $paltform_id = $cart->platform_id;
            $basket_id = $cart->basketID;
        }

        $platform_config = new platform_config($paltform_id);
        $platform_config->constant_up();
        //$order = new order($oID);
        $customer_loaded = false;
        $customer = new Customer();
        if ($customer->loadCustomer($customer_id)) {
            $customer->setParam('languages_id', $language_id);
            $customer->setParam('currency', $currency);
            $customer->setParam('platform_id', $paltform_id);
            $customer->convertToSession();
            $customer_loaded = true;
        }

        if (!$customer_loaded) {
            $order = new order($oID);
        } else {
            $order = new order();
            $order->order_id = $oID;
        }
        //echo '<pre>';print_r($order);die;
        //$cart->clearTotals();
        //echo '<pre>';print_r($cart);die;
        if (is_object($cart))
            $cart->clearTotalKey('ot_shipping');
        if (isset($_POST['action']) && $_POST['action'] == 'add_product') {
            if (isset($_POST['products_id']) && is_numeric($_POST['products_id']) /* && tep_check_product((int)$_POST['products_id']) */) {
                $attributes = [];
                $uprid = urldecode($_POST['uprid']);
                $old_uprid = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::normalize_id($uprid), $attributes); // bundles sorting attributes need twice normalizing

                if (isset($_POST['id']) && is_array($_POST['id']) && count($_POST['id'])) {
                    $attributes = [];
                    foreach ($_POST['id'] as $_k => $_v) {
                        if (tep_not_null($_v) && $_v > 0) {
                            $attributes[$_k] = $_v;
                        }
                    }
                    $uprid = \common\helpers\Inventory::get_uprid($_POST['products_id'], $attributes);
                    $uprid = \common\helpers\Inventory::normalize_id($uprid, $attributes);
                }

                $_qty = (int) (is_array($_POST['qty']) ? array_sum($_POST['qty']) : $_POST['qty']);
                $_uprid = \common\helpers\Inventory::get_uprid($_POST['products_id'], $attributes);
                //$add_qty = /*$cart->get_quantity($_uprid)+*/$_qty;
                $reserved_qty = $cart->get_reserved_quantity($_uprid);
                if (defined('STOCK_CHECK') && STOCK_CHECK == 'true') {
                    $product_qty = \common\helpers\Product::get_products_stock($_uprid);
                    $stock_indicator = \common\classes\StockIndication::product_info(array(
                                'products_id' => $_uprid,
                                'products_quantity' => $product_qty,
                    ));

                    if ($_qty > $reserved_qty) {
                        if ($_qty > $product_qty && !$stock_indicator['allow_out_of_stock_add_to_cart']) {
                            $_qty = $product_qty;
                        }
                        if ($_qty < 1) {
                            $customer->convertBackSession();
                            if (Yii::$app->request->isAjax) {
                                $messageStack->add('one_page_checkout', TEXT_PRODUCT_OUT_STOCK, 'error');
                                return $this->actionOrderEdit();
                            } else {
                                Yii::$app->session->setFlash('error', TEXT_PRODUCT_OUT_STOCK);
                                if (tep_not_null($oID)) {
                                    $url = \yii\helpers\Url::to(['orders/order-edit', 'orders_id' => $oID]) . '#products';
                                    return $this->redirect($url);
                                } else {
                                    $url = \yii\helpers\Url::to(['orders/order-edit']) . '#products';
                                    return $this->redirect($url);
                                }
                            }
                        }
                    }
                }

                $tax = (isset($_POST['tax']) ? $_POST['tax'] : null);
                $final_price = (isset($_POST['final_price']) ? (float) $_POST['final_price'] : null);
                $use_default_price = (strtolower($_POST['use_default_price']) == 'on' ? true : false);
                $name = (isset($_POST['name']) ? stripslashes($_POST['name']) : null);
                $use_default_name = (strtolower($_POST['use_default_name']) == 'on' ? true : false);

                if ($cart->in_cart($old_uprid) && strpos($old_uprid, '(GA)') === false) {
                    //$products_id = \common\helpers\Inventory::get_uprid((int) $_POST['products_id'], $attributes);
                    $products_id = $old_uprid; //\common\helpers\Inventory::normalize_id($old_uprid);
                    if ($products_id != $uprid) {
                        $cart->remove($old_uprid);
                    }
                }

                $gift_wrap = (isset($_POST['gift_wrap']) ? $_POST['gift_wrap'] : null);
                if (!is_null($gift_wrap)) {
                    $gift_wrap = (in_array(strtolower($gift_wrap), ['true', 'on']) ? true : false);
                } else {
                    $gift_wrap = false;
                }

                global $new_products_id_in_cart;
                if (is_array($_POST['qty'])) {
                    $packQty = [
                        'qty' => array_sum($_POST['qty']),
                        'unit' => (int) $_POST['qty'][0] / max(1, (int) $_POST['qty_'][0]),
                        'pack_unit' => (int) $_POST['qty'][1] / max(1, (int) $_POST['qty_'][1]),
                            //'packaging' => (int)$_POST['qty'][2] / max(1, (int)$_POST['qty_'][2]),
                    ];
                    $packQty['packaging'] = (int) $_POST['qty'][2] / ($_POST['qty_'][1] > 0 ? max(1, (int) $_POST['qty_'][2] * $_POST['qty_'][1]) : max(1, (int) $_POST['qty_'][2]) );
                } else {
                    $packQty = $_qty;
                }
                $cart->add_cart((int) $_POST['products_id'], $packQty, $attributes, true, 0, $gift_wrap);
                if (tep_not_null($new_products_id_in_cart)) {
                    $uprid = $new_products_id_in_cart;
                }

                if (!is_null($tax)) {
                    $ex = explode("_", $tax);
                    $tax_value = 0;
                    if (count($ex) == 2) {
                        $tax_value = \common\helpers\Tax::get_tax_rate_value_edit_order($ex[0], $ex[1]);
                        $cart->setOverwrite($uprid, 'tax_selected', $tax);
                        $cart->setOverwrite($uprid, 'tax', $tax_value);
                        $cart->setOverwrite($uprid, 'tax_class_id', $ex[0]);
                        $cart->setOverwrite($uprid, 'tax_description', \common\helpers\Tax::get_tax_description($ex[0], $order->tax_address['entry_country_id'], $ex[1]));
                    } else {
                        $cart->setOverwrite($uprid, 'tax_selected', 0);
                        $cart->setOverwrite($uprid, 'tax', 0);
                        $cart->setOverwrite($uprid, 'tax_class_id', 0);
                        $cart->setOverwrite($uprid, 'tax_description', '');
                    }
                }
                if (!is_null($final_price)) {
                    $final_price = $final_price * $currencies->get_market_price_rate($currency, DEFAULT_CURRENCY);
                    if (!is_null($tax)) {
                        //$final_price = \common\helpers\Tax::get_untaxed_value($final_price, $tax_value);
                    }
                    $cart->setOverwrite($uprid, 'final_price', $final_price);
                }
                if ($use_default_price) {
                    $cart->clearOverwritenKey($uprid, 'final_price');
                }
                if (is_array($packQty)) {
                    if ($ext = Acl::checkExtension('PackUnits', 'saveItemsIntoCart')) {
                        $ext::saveItemsIntoCart($uprid, $packQty);
                    }
                    if ($ext = Acl::checkExtension('PackUnits', 'getProductsCartFrontend')) {
                        $data = $ext::getProductsCartFrontend($uprid, $cart->contents);
                        if ($ext = Acl::checkExtension('PackUnits', 'savePriceIntoCart')) {
                            $ext::savePriceIntoCart($uprid, $data);
                        }
                    }
                }
                if (!is_null($name)) {
                    $cart->setOverwrite($uprid, 'name', $name);
                }
                if ($use_default_name) {
                    $cart->clearOverwritenKey($uprid, 'name');
                }
                $cart->setAdjusted();
            }
            $cart->clearTotalKey('ot_tax');
            $cart->clearTotalKey('ot_gift_wrap');
            //$cart->clearTotalKey('ot_coupon');
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();

            if (Yii::$app->request->isAjax) {
                return $this->actionOrderEdit();
            } else {
                if (tep_not_null($oID)) {
                    $url = \yii\helpers\Url::to(['orders/order-edit', 'orders_id' => $oID]) . '#products';
                    return $this->redirect($url);
                } else {
                    $url = \yii\helpers\Url::to(['orders/order-edit']) . '#products';
                    return $this->redirect($url);
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] == 'remove_product') {
            if (isset($_POST['products_id'])) {
                $uprid = urldecode($_POST['products_id']);
                $uprid = \common\helpers\Inventory::normalize_id($uprid);
                $cart->remove($uprid);
            }
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            if (Yii::$app->request->isAjax) {
                return $this->actionOrderEdit();
            } else {
                return $this->redirect(['orders/order-edit', 'orders_id' => $oID]);
            }
        } elseif (isset($_GET['action']) && $_GET['action'] == 'show_giveaways') {
            $giveaways = \common\helpers\Gifts::getGiveAways();
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            return $this->renderAjax('give-away', ['products' => $giveaways, 'oID' => $oID, 'nopopup' => false]);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'add_giveaway') {
            if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
                if (isset($_POST['giveaway_switch'])) {
                    foreach ($_POST['giveaway_switch'] as $gaw_id => $data) {
                        //like radio if ( $data != 10 ) continue;
                        if (!isset($_POST['giveaways'][$gaw_id]) || !isset($_POST['giveaways'][$gaw_id]['products_id']))
                            continue;
                        if ($_POST['giveaways'][$gaw_id]['products_id'] != $_POST['product_id'])
                            continue;
                        $ga_data = $_POST['giveaways'][$gaw_id];
                        if ($cart->is_valid_product_data($ga_data['products_id'], isset($ga_data['id']) ? $ga_data['id'] : '')) {
                            $cart->add_cart($ga_data['products_id'], \common\helpers\Gifts::get_max_quantity($ga_data['products_id'], $gaw_id)['qty'], isset($ga_data['id']) ? $ga_data['id'] : '', false, $gaw_id);
                        }
                    }
                }
            }
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            return $this->redirect(['orders/order-edit', 'orders_id' => $oID]);
        } else if (isset($_POST['action']) && $_POST['action'] == 'remove_giveaway') {
            if (isset($_POST['products_id'])) {
                $uprid = urldecode($_POST['products_id']);
                $cart->remove_giveaway($uprid);
            }
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            if (Yii::$app->request->isAjax) {
                return $this->actionOrderEdit();
            } else {
                return $this->redirect(['orders/order-edit', 'orders_id' => $oID]);
            }
        } else if (strlen($_GET['search'])) {
            $products_array = array();
            $products2c_join = '';
            if ($paltform_id) {
                $products2c_join .= " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . $paltform_id . "' " .
                        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id left join " . TABLE_CATEGORIES . " c on c.categories_id = p2c.categories_id and c.categories_status = 1 " .
                        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . $paltform_id . "' ";
            }
            $products_query = tep_db_query("select distinct p.products_id, pd.products_name, p.products_price, p.products_tax_class_id from " . TABLE_PRODUCTS . " p {$products2c_join}, " . TABLE_PRODUCTS_DESCRIPTION . " pd  where p.products_status =1 and p.products_id = pd.products_id and (pd.products_name like '%" . tep_db_input(tep_db_prepare_input($_GET['search'])) . "%' or p.products_model like '%" . tep_db_input(tep_db_prepare_input($_GET['search'])) . "%' or p.products_price like '%" . tep_db_input(tep_db_prepare_input($_GET['search'])) . "%' ) and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 order by products_name");

            if (tep_db_num_rows($products_query)) {

                while ($products = tep_db_fetch_array($products_query)) {
                    // $products['products_price'] = tep_get_products_price_edit_order($products['products_id'], $currencies->currencies[$order->info['currency']]['id'], $group_id, 1, true);
                    $products_array[] = array('id' => $products['products_id'], 'text' => $products['products_name'], 'image' => \common\classes\Images::getImageUrl($products['products_id']), 'price' => $currencies->format(\common\helpers\Product::get_products_price($products['products_id'], 1), true, $currency), 'tax_class_id' => $products['products_tax_class_id']);
                }
            } else {
                $products_array[] = array('id' => '0', 'text' => TEXT_NO_PRODUCTS_FOUND, 'price' => '', 'image' => '', 'tax_class_id' => 0);
            }
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            echo json_encode($products_array);
            exit();
        } else if (($_GET['products_id']) && $_GET['products_id'] > 0 && $_GET['details']) {
            $products_id = Yii::$app->request->get('products_id');
            $qty = Yii::$app->request->post('qty', 1);
            $has_inventory = \common\helpers\Inventory::product_has_inventory($products_id);
            $params['isAjax'] = true;
            $params['qty'] = $qty;
            $result['has_inventory'] = $has_inventory;
            $attributes = Yii::$app->request->post('id');
            $uprid = $products_id;
            if (is_array($attributes)) {
                $prepare = [];
                for ($i = 0; $i < count($attributes); $i++) {
                    if (tep_not_null($attributes[$i]))
                        $prepare[$i] = $attributes[$i];
                }
                $attributes = $prepare;
                $uprid = \common\helpers\Inventory::normalize_id($products_id, $attributes);
            } else {
                parse_str(Yii::$app->request->post('id', array()), $attributes);
                if (isset($attributes['id'])) {
                    $attributes = $attributes['id'];
                    $uprid = \common\helpers\Inventory::get_uprid((int) $products_id, $attributes);
                    $uprid = \common\helpers\Inventory::normalize_id($uprid, $attributes);
                } else {
                    $_uprid = Yii::$app->request->post('id');
                    if (strpos($_uprid, '{') !== false) {
                        $uprid = \common\helpers\Inventory::normalize_id($_uprid, $attributes);
                    } else {
                        $uprid = $products_id;
                        $uprid = \common\helpers\Inventory::normalize_id($uprid, $attributes);
                    }
                }
            }
            if (!is_array($attributes))
                $attributes = [];
            $_attributes = $attributes;

            $result = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);
            if (isset($result['stock_indicator']) && is_array($result['stock_indicator'])) {
                $result['stock_indicator']['quantity_max'] += $cart->get_reserved_quantity($uprid);
            }

            $result['product_attributes'] = $this->renderAjax('attributes', ['attributes' => $result['attributes_array']]);
            $result['current_attributes'] = $attributes;

            $result['order_quantity'] = \common\helpers\Product::get_product_order_quantity($products_id);

            $bundles = \common\helpers\Bundles::getDetails(['products_id' => $products_id, 'id' => $_attributes]);
            $result['bundles_block'] = '';
            $result['bundles'] = array();
            if ($bundles) {
                $result['bundles'] = $bundles;
                $result['bundles_block'] = $this->renderAjax('bundle', ['products' => $bundles]);
            }

            $discounts = array();
            $dt = \common\helpers\Product::get_products_discount_table($products_id);
            if ($dt && is_array($dt)) {
                $discounts[] = array(
                    'count' => 1,
                    'price' => \common\helpers\Product::get_products_price($products_id),
                    'price_with_tax' => $currencies->display_price($dt[$i + 1], \common\helpers\Tax::get_tax_rate(\common\helpers\Product::get_products_info($products_id, 'products_tax_class_id')))
                );
                for ($i = 0, $n = sizeof($dt); $i < $n; $i = $i + 2) {
                    if ($dt[$i] > 0) {
                        $discounts[] = array(
                            'count' => $dt[$i],
                            'price' => $dt[$i + 1],
                            'price_with_tax' => $currencies->display_price($dt[$i + 1], \common\helpers\Tax::get_tax_rate(\common\helpers\Product::get_products_info($products_id, 'products_tax_class_id')))
                        );
                    }
                }
            }
            $result['discount_table_data'] = $discounts;
            $result['discount_table_view'] = (count($discounts) ? $this->renderAjax('quantity-discounts', ['discounts' => $discounts]) : '');
            if ($ext = Acl::checkExtension('PackUnits', 'quantityBoxFrontend')) {
                $result['product_details'] = $ext::quantityBoxFrontend($params, ['products_id' => $products_id]);
                if ($ext = Acl::checkExtension('PackUnits', 'getPricePack')) {
                    $data = $ext::getPricePack($products_id, true);
                    $result['product_details']['single_price_data'] = $data;
                }
            }
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            echo json_encode($result);
            exit();
        } else if (isset($_GET['products_id']) && strlen($_GET['products_id']) > 0) {
            $params['products_id'] = $_GET['products_id'];
            $params['tax_class_id'] = \common\helpers\Product::get_products_info($params['products_id'], 'products_tax_class_id');
            $rate = \common\helpers\Tax::get_tax_rate($params['tax_class_id'], $order->tax_address['entry_country_id'], $order->tax_address['entry_zone_id']);
            if (!$rate) {
                $params['tax_class_id'] = 0;
            }
            $pa_options = [];
            $params['options'] = $pa_options;
            $tax_class_array = \common\helpers\Tax::get_complex_classes_list();
            $rates_query = tep_db_query("select tr.tax_class_id, tr.tax_zone_id, tr.tax_rate from " . TABLE_TAX_RATES . " tr inner join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where 1 group by tr.tax_class_id, tr.tax_zone_id");
            $rates = [];
            if (tep_db_num_rows($rates_query)) {
                while ($row = tep_db_fetch_array($rates_query)) {
                    $rates[$row['tax_class_id'] . '_' . $row['tax_zone_id']] = $row['tax_rate'];
                }
            }
            $params['has_inventory'] = false; //\common\helpers\Inventory::product_has_inventory($params['products_id']) > 0;
            $params['rates'] = $rates;
            $params['image'] = \common\classes\Images::getImage((int) $params['products_id'], 'Small');
            //check giveaway
            $result['ga'] = \common\helpers\Gifts::getGiveAways($params['products_id']);

            /* if (!is_null($result['ga'])) {
              $result['ga'] = $result['ga'][0]['price_b']; //$this->renderAjax('give-away', ['products' => $result['ga'], 'nopopup' => true]);
              } else {
              $result['ga'] = '';
              } */
            $params['ga'] = $result['ga'];
            $params['gift_wrap_allowed'] = \common\helpers\Gifts::allow_gift_wrap($params['products_id']);
            $params['gift_wrap_price'] = 0;
            if ($params['gift_wrap_allowed']) {
                $params['gift_wrap_price'] = \common\helpers\Gifts::get_gift_wrap_price($params['products_id']) * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency); //$currencies->format_clear(\common\helpers\Gifts::get_gift_wrap_price($params['products_id']), true, $currency);
            }

            $params['product_name'] = \common\helpers\Product::get_products_name($params['products_id'], $language_id);
            $params['currency'] = $currency;
            $params['is_editing'] = false;
            $params['product']['qty'] = 1;
            $params['product']['units'] = 0;
            $params['product']['packs'] = 0;
            $params['product']['packagings'] = 0;
            $render = 'product_details';
            if (isset($_GET['action']) && $_GET['action'] == 'edit_product') {
                $uprid = urldecode($params['products_id']);
                $uprid = \common\helpers\Inventory::normalize_id($uprid);
                $params['product'] = null;
                if ($cart->in_cart($uprid) /* && strpos($uprid, '(GA)') === false */) {
                    $products = $cart->get_products();

                    if (count($products)) {
                        foreach ($products as $_p) {
                            if ($_p['id'] == $uprid && !$_p['ga']) {
                                $_p['products_id'] = (int) $_p['id'];
                                $_p['final_price'] = $_p['final_price'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency);
                                $_p['old_name'] = addslashes(\common\helpers\Product::get_products_name($_p['id'], $language_id));
                                $_p['name'] = addslashes($_p['name']);
                                $_p['qty'] = (int) $_p['quantity'];
                                $ov = $cart->getOwerwritten($uprid);
                                $_p['selected_rate'] = 0;
                                if (isset($ov['tax_selected']))
                                    $_p['selected_rate'] = $ov['tax_selected'];

                                $_p['price_manualy_modified'] = ($cart->getOwerwrittenKey($_p['id'], 'final_price') ? 'true' : 'false');
                                $params['product'] = $_p;
                                break;
                            }
                        }
                    }
                }
                $render = 'edit_product';
                $params['is_editing'] = true;
            }
            if ($ext = Acl::checkExtension('PackUnits', 'quantityBoxFrontend')) {
                $params['product_details'] = $ext::quantityBoxFrontend($params['product'], $params);
            }
            $currentCart = $admin->getCurrentCartID();
            $admin->saveCustomerBasket($cart);
            $customer->convertBackSession();
            return $this->renderAjax($render, ['params' => $params, 'cart' => $cart, 'tax_class_array' => $tax_class_array, 'currentCart' => $currentCart]);
        }
        $admin->saveCustomerBasket($cart);
        $currentCart = $admin->getCurrentCartID();
        $customer->convertBackSession();

        \common\helpers\Translation::init('admin/platforms');

        $platform_id = (int) $cart->platform_id;

        $category_tree_array = [];
        $category_tree_array = \common\helpers\Categories::get_full_category_tree(0, '', '', $category_tree_array, false, $cart->platform_id, true);

        $params['category_tree_array'] = $category_tree_array;
        $params['searchsuggest'] = (count($category_tree_array) > 5000);

        return $this->renderAjax('product', ['params' => $params, 'currentCart' => $currentCart]);
    }

    private function tep_get_category_children(&$children, $platform_id, $categories_id, $search = '') {
        if (!is_array($children))
            $children = array();
        $l = \common\helpers\Categories::load_tree_slice($platform_id, $categories_id, true, $search, true);
        foreach ($l as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, $platform_id, intval(substr($item['key'], 1)));
            }
        }
    }

    public function actionAddproductprocess() {
        global $currencies, $languages_id;
        global $cart, $order, $shipping_weight, $shipping_num_boxes, $currencies;
        global $shipping_modules, $shipping, $messageStack;
        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }
        if (!is_object($order)) {
            require_once(DIR_WS_CLASSES . 'order.php');
        }
        // Get Order Info
        $oID = tep_db_prepare_input($_POST['oID']);
        $qty = tep_db_prepare_input($_POST['add_product_quantity']);
        $order = new \order($oID);
        $platform_config = new platform_config($order->info['platform_id']);
        $platform_config->constant_up();

        $AddedOptionsPrice = 0;
        $add_product_options = tep_db_prepare_input($_POST['add_product_options']);
        //print_r($add_product_options);
        $add_product_products_id = (int)$_POST['add_product_products_id'];
        $uprid = \common\helpers\Inventory::get_uprid($add_product_products_id, $add_product_options);
        $uprid = \common\helpers\Inventory::normalize_id($uprid);
        // Get Product Attribute Info
        if (is_array($add_product_options)) {
            ksort($add_product_options);
            foreach ($add_product_options as $option_id => $option_value_id) {
                $result = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON po.products_options_id=pa.options_id and po.language_id = '" . (int) $languages_id . "' LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON pov.products_options_values_id=pa.options_values_id and pov.language_id = '" . (int) $languages_id . "' WHERE products_id='" . (int)$add_product_products_id . "' and options_id=" . (int)$option_id . " and options_values_id='" . (int)$option_value_id . "'");
                $row = tep_db_fetch_array($result);
                extract($row, EXTR_PREFIX_ALL, "opt");
                $opt_options_values_price = \common\helpers\Attributes::get_attributes_price_edit_order($opt_products_attributes_id, $currencies->currencies[$order->info['currency']]['id'], \common\helpers\Customer::get_customers_group($order->customer['customer_id']), 1, true);

                if ($opt_price_prefix == '+') {
                    $AddedOptionsPrice += $opt_options_values_price;
                } else {
                    $AddedOptionsPrice -= $opt_options_values_price;
                }
                $option_value_details[$option_id][$option_value_id] = array("options_values_price" => $opt_options_values_price);
                $option_names[$option_id] = $opt_products_options_name;
                $option_values_names[$option_value_id] = $opt_products_options_values_name;
                $option_values_price_prefix[$option_value_id] = $opt_price_prefix;
            }
        }
        \common\helpers\Product::update_stock($uprid, 0, $qty);

        // Get Product Info
        $InfoQuery = "select p.is_virtual, p.products_model, p.products_price, pd.products_name, p.products_tax_class_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id=p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 where p.products_id='$add_product_products_id'";
        $result = tep_db_query($InfoQuery);
        if (tep_db_num_rows($result) > 0) {
            $row = tep_db_fetch_array($result);
            extract($row, EXTR_PREFIX_ALL, "p");

            $ProductsTax = \common\helpers\Tax::get_tax_rate($p_products_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
            if (\common\helpers\Product::get_products_special_price_edit_order($add_product_products_id, $currencies->currencies[$order->info['currency']]['id'], \common\helpers\Customer::get_customers_group($order->customer['customer_id']))) {
                $p_products_price = \common\helpers\Product::get_products_special_price_edit_order($add_product_products_id, $currencies->currencies[$order->info['currency']]['id'], \common\helpers\Customer::get_customers_group($order->customer['customer_id']));
            } else {
                $p_products_price = \common\helpers\Product::get_products_price_edit_order($add_product_products_id, $currencies->currencies[$order->info['currency']]['id'], \common\helpers\Customer::get_customers_group($order->customer['customer_id']), 1, true);
            }
            // inventory
            if (PRODUCTS_INVENTORY == 'True') {
                $r = tep_db_query("select products_model from " . TABLE_INVENTORY . " where products_id='" . tep_db_input($uprid) . "'");
                if ($inventory = tep_db_fetch_array($r)) {
                    if ($inventory['products_model']) {
                        $p_products_model = $inventory['products_model'];
                    }
                }
            }
            // inventory eof
            tep_db_perform(TABLE_ORDERS_PRODUCTS, array(
                'orders_id' => (int) $oID,
                'products_id' => $add_product_products_id,
                'products_model' => $p_products_model,
                'products_name' => $p_products_name,
                'products_price' => $p_products_price,
                'final_price' => ($p_products_price + $AddedOptionsPrice),
                'products_tax' => $ProductsTax,
                'products_quantity' => $qty,
                'is_virtual' => $p_is_virtual,
                'gv_state' => (preg_match('/^GIFT/', $p_products_model) ? 'pending' : 'none'),
                'uprid' => $uprid,
            ));
            $new_product_id = tep_db_insert_id();
            if (is_array($add_product_options)) {
                foreach ($add_product_options as $option_id => $option_value_id) {
                    $Query = "insert into " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " set orders_id = " . (int)$oID . ", orders_products_id = " . (int)$new_product_id . ", products_options = '" . tep_db_input($option_names[$option_id]) . "', products_options_values = '" . tep_db_input($option_values_names[$option_value_id]) . "', options_values_price = '" . tep_db_input($option_value_details[$option_id][$option_value_id]["options_values_price"]) . "', price_prefix = '" . tep_db_input($option_values_price_prefix[$option_value_id]) . "'";
                    tep_db_query($Query);
                }
            }
        }
///////////////////////////////////////
// update totals
        $cart = new \shoppingCart($oID);
        $cart->calculate();
        $order = new \order($oID);
        require_once(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new \order_total;
        $order_totals = $order_total_modules->process();
        $query_d = "delete from " . TABLE_ORDERS_TOTAL . " where class='ot_subtotal' and orders_id='" . (int)$oID . "'";
        tep_db_query($query_d);
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            if ($order_totals[$i]['code'] == 'ot_subtotal') {
                $sql_data_array = array('orders_id' => $oID,
                    'title' => $order_totals[$i]['title'],
                    'text' => $order_totals[$i]['text'],
                    'value' => $order_totals[$i]['value'],
                    'class' => $order_totals[$i]['code'],
                    'sort_order' => $order_totals[$i]['sort_order']);
                tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
            }
        }

        $shipping_fee = 0;
        $cart = new \shoppingCart($oID);
        $cart->calculate();
        $shipping_weight = $cart->show_weight();
        $total_weight = $shipping_weight;
        $total_count = $cart->count_contents();
        require_once(DIR_WS_CLASSES . 'shipping.php');
        $shipping_modules = new \shipping();
        $shipping = $order->info['shipping_class'];
        list($module, $method) = explode('_', $shipping);
        if (is_object($GLOBALS[$module]) || ($shipping == 'free_free')) {
            if ($shipping == 'free_free') {
                $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                $quote[0]['methods'][0]['cost'] = '0';
            } else {
                $quote = $shipping_modules->quote($method, $module);
            }
            if (isset($quote['error'])) {
                $messageStack->add_session("Please select another shipping method", 'error');
                $order->info['shipping_class'] = "";
                $query_u = "update " . TABLE_ORDERS . " set shipping_method='', shipping_class='' where orders_id='" . $oID . "'";
                tep_db_query($query_u);
            } else {
                if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                    $shipping_fee = $quote[0]['methods'][0]['cost'];
                    $order->info['shipping_method'] = $quote[0]['module'] . (tep_not_null($quote[0]['methods'][0]['title']) ? ' (' . $quote[0]['methods'][0]['title'] . ')' : '');
                    $quote[0]['id'] = $shipping;
                    $shipping = $quote[0];
                }
            }
        }
        if (!is_array($shipping)) {
            $shipping = array(array('id'));
        }
        // recreate order in order to recalculate totals and update shipping costs
        $order = new \order($oID);
        $order->info['shipping_cost'] = $shipping_fee;
        $order->info['total'] += $shipping_fee;
        require_once(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new \order_total;
        $order_totals = $order_total_modules->process();
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $res = tep_db_query("select count(*) as total from " . TABLE_ORDERS_TOTAL . " where orders_id='" . (int)$oID . "' and class='" . tep_db_input($order_totals[$i]['code']) . "'");
            $d = tep_db_fetch_array($res);
            $sql_data_array = array('title' => $order_totals[$i]['title'],
                'text' => $order_totals[$i]['text'],
                'value' => $order_totals[$i]['value'],
                'sort_order' => $order_totals[$i]['sort_order']);
            if ($d['total'] == 0) {
                $sql_data_array['orders_id'] = (int)$oID;
                $sql_data_array['class'] = $order_totals[$i]['code'];
                tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
            } else {
                tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array, 'update', "orders_id='" . (int)$oID . "' and class='" . tep_db_input($order_totals[$i]['code']) . "'");
            }
        }

        echo json_encode(array('redirect' => Yii::$app->urlManager->createUrl('orders/order-edit?orders_id=' . $oID)));
    }

    public function actionOrderPreedit() {
        global $languages_id, $language, $login_id, $GLOBALS;
        global $cart, $order, $shipping_weight, $shipping_num_boxes, $currencies;
        global $shipping_modules, $shipping, $messageStack;

        \common\helpers\Translation::init('admin/orders/order-edit');

        require_once(DIR_WS_CLASSES . 'order.php');

        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }
        $oID = tep_db_prepare_input($_GET['oID']);

        $order = new \order($oID);
        $platform_config = new platform_config($order->info['platform_id']);
        $platform_config->constant_up();


        $update_products = tep_db_prepare_input($_POST['update_products']);
        if (is_array($update_products)) {
            foreach ($update_products as $orders_products_id => $products_details) {
                // Update orders_products Table
                if (($products_details["qty"] > 0)) {
                    /* $tax=0;
                      $products_tax_class_id_query = "select p.products_tax_class_id from " . TABLE_PRODUCTS . " p, ".TABLE_ORDERS_PRODUCTS."  op where op.orders_products_id='".intval($orders_products_id)."' and op.products_id=p.products_id";
                      $products_tax_class_id_result = tep_db_query($products_tax_class_id_query);
                      if(tep_db_num_rows($products_tax_class_id_result)>0)
                      {
                      $products_tax_class_id_array = tep_db_fetch_array($products_tax_class_id_result);
                      $products_tax_class_id = $products_tax_class_id_array['products_tax_class_id'];
                      $tax = \common\helpers\Tax::get_tax_rate($products_tax_class_id, $order->delivery['country']['id'], $order->delivery['zone_id']);
                      }
                      else
                      {
                      $ar= explode('_', $products_details["tax"]);
                      $tax = \common\helpers\Tax::get_tax_rate_value_edit_order($ar[0], $ar[1]);
                      } */
                    $ar = explode('_', $products_details["tax"]);
                    $tax = \common\helpers\Tax::get_tax_rate_value_edit_order($ar[0], $ar[1]);

                    tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set products_model = '" . tep_db_input($products_details["model"]) . "', products_name = '" . tep_db_input($products_details["name"]) . "', final_price = '" . tep_db_input($products_details["final_price"]) . "', products_tax = '" . tep_db_input($tax) . "', products_quantity = '" . tep_db_input($products_details["qty"]) . "' where orders_products_id = '" . intval($orders_products_id) . "'");
                    // Update Any Attributes
                    if (is_array($products_details['attributes'])) {
                        foreach ($products_details["attributes"] as $orders_products_attributes_id => $attributes_details) {
                            tep_db_query("update " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " set products_options = '" . tep_db_input($attributes_details["option"]) . "', products_options_values = '" . tep_db_input($attributes_details["value"]) . "' where orders_products_attributes_id = '" . (int)$orders_products_attributes_id . "';");
                        }
                    }
                } elseif (($products_details["qty"] == 0) || ($products_details["qty"] == '')) {
                    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
                    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_products_id = '" . (int)$orders_products_id . "'");
                }
            }
            $order = new \order($oID);
        }


        /**
         * Shipping
         */
        $customer_id = $order->customer['customer_id'];
        $shipping = $_POST['shipping'];
        $shipping_fee = 0;
        if ($shipping != '') {
            $order->info['shipping_class'] = $shipping;
            $order->info['shipping_method'] = "";
            $cart = new \shoppingCart($oID);
            $cart->calculate();
            $shipping_weight = $cart->show_weight();
            $total_weight = $shipping_weight;
            $total_count = $cart->count_contents();
            require_once(DIR_WS_CLASSES . 'shipping.php');
            $shipping_modules = new \shipping();

            list($module, $method) = explode('_', $shipping);
            if (is_object($GLOBALS[$module]) || ($shipping == 'free_free')) {
                if ($shipping == 'free_free') {
                    $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                    $quote[0]['methods'][0]['cost'] = '0';
                } else {
                    $quote = $shipping_modules->quote($method, $module);
                }
                if (isset($quote[0]['error'])) {
                    //$messageStack->add_session("Please select another shipping method", 'error');
                    $order->info['shipping_class'] = "";
                    //$query_u="update " . TABLE_ORDERS . " set shipping_method='', shipping_class='' where orders_id='".$oID."'";
                    //tep_db_query($query_u);
                } else {
                    if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                        //tep_db_query(" update " . TABLE_ORDERS . " set shipping_class='" . $shipping . "',  shipping_method='" . $quote[0]['methods'][0]['title'] . "' where orders_id='" . $oID . "'");

                        /* $result=tep_db_query("select  orders_total_id from " . TABLE_ORDERS_TOTAL . " where orders_id='" . $oID . "' and class ='ot_shipping'");

                          if(tep_db_num_rows($result)>0) {
                          $sql_data_shipping_array = array('title' => $quote[0]['module'] . (tep_not_null($quote[0]['methods'][0]['title'])?' (' . $quote[0]['methods'][0]['title'] . ')':''));
                          tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_shipping_array, 'update', ' orders_id="' . $oID . '" and class ="ot_shipping"');
                          }  else {
                          $query="insert into " . TABLE_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . $oID . "', '" . tep_db_input($quote[0]['module'] . (tep_not_null($quote[0]['methods'][0]['title'])?' (' . $quote[0]['methods'][0]['title'] . ')':'')) . "', '', '','ot_shipping','" . $sort_order . "')";
                          $result=tep_db_query($query);
                          } */

                        $shipping_fee = $quote[0]['methods'][0]['cost'];
                        $order->info['shipping_method'] = $quote[0]['module'] . (tep_not_null($quote[0]['methods'][0]['title']) ? ' (' . $quote[0]['methods'][0]['title'] . ')' : '');
                        $order->info['shipping_cost'] = $shipping_fee;
                        $order->info['total'] += $shipping_fee;
                        $quote[0]['id'] = $shipping;
                        $shipping = $quote[0];
                    }
                }
            }
        }

        require_once(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new \order_total;
        $order_totals = $order_total_modules->process();
        $order_total_modules->apply_credit();

        //tep_db_query("delete from ".TABLE_ORDERS_TOTAL." where orders_id='" . $oID . "'");
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $sql_data_array = array('orders_id' => $oID,
                'title' => $order_totals[$i]['title'],
                'text' => $order_totals[$i]['text'],
                'value' => $order_totals[$i]['value'],
                'class' => $order_totals[$i]['code'],
                'sort_order' => $order_totals[$i]['sort_order']);
            /* echo "<pre>";
              print_r($sql_data_array);
              echo "</pre>"; */

            //tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }
        ?>
        <!-- Begin Order Total Block -->
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td align='left' valign='top' class=main><?= '<input type="button" class="btn btn-primary" value="' . TEXT_ADD_A_NEW_PRODUCT . '" onclick="addProduct(' . $oID . ')">' ?></td>
                <td align='right'><table border="0" cellspacing="0" cellpadding="0" class="p-or-t-tab">
                        <?php
// Override order.php Class's Field Limitations
                        $sort_orders = array();

                        //$order->totals = array();
                        $TotalsArray = array();
                        if (is_array($order_total_modules->modules)) {
                            reset($order_total_modules->modules);
                            while (list(, $value) = each($order_total_modules->modules)) {
                                $class = substr($value, 0, strrpos($value, '.'));
                                if ($GLOBALS[$class]->enabled) {
                                    /* if($order->info['edit_orders_recalculate_totals']==1)
                                      {
                                      $GLOBALS[$class]->process();
                                      } */

                                    if (sizeof($GLOBALS[$class]->output)) {
                                        for ($i = 0, $n = sizeof($GLOBALS[$class]->output); $i < $n; $i++) {
                                            if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                                                /* $order->totals[] = array('class' => $GLOBALS[$class]->code,
                                                  'title' => $GLOBALS[$class]->output[$i]['title'],
                                                  'text' => $GLOBALS[$class]->output[$i]['text'],
                                                  'value' => $GLOBALS[$class]->output[$i]['value'],
                                                  'sort_order' => $GLOBALS[$class]->sort_order); */
                                                $sort_orders[] = $GLOBALS[$class]->sort_order;
                                                $TotalsArray[] = array("Name" => $GLOBALS[$class]->output[$i]['title'],
                                                    //"Price" => number_format($GLOBALS[$class]->output[$i]['value'], 2, '.', ''),
                                                    "Price" => $currencies->format_clear($GLOBALS[$class]->output[$i]['value'], true, $order->info['currency'], $order->info['currency_value']),
                                                    "Class" => $GLOBALS[$class]->code,
                                                    "sort_order" => $GLOBALS[$class]->sort_order,
                                                    "TotalID" => $GLOBALS[$class]->sort_order);
                                            }

                                            /* $query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = '" . $GLOBALS[$class]->code . "' order by sort_order");
                                              if (tep_db_num_rows($query)){
                                              while ($data = tep_db_fetch_array($query)){
                                              $order->totals[sizeof($order->totals) - 1]['orders_total_id'] = $data['orders_total_id'];
                                              $TotalsArray[sizeof($TotalsArray) - 1]['TotalID'] = $data['orders_total_id'];
                                              $TotalsArray[sizeof($TotalsArray) - 1]['Name'] = $data['title'];
                                              }
                                              } */
                                        }
                                    } else {
                                        /* $order->totals[] = array(
                                          'class' => $GLOBALS[$class]->code,
                                          'title' => $GLOBALS[$class]->title . ':',
                                          'text' => '',
                                          'value' => '',
                                          'sort_order' => $GLOBALS[$class]->sort_order
                                          ); */
                                        $sort_orders[] = $GLOBALS[$class]->sort_order;
                                        $TotalsArray[] = array(
                                            "Name" => $GLOBALS[$class]->title . ':',
                                            "Price" => '',
                                            "Class" => $GLOBALS[$class]->code,
                                            "sort_order" => $GLOBALS[$class]->sort_order
                                        );
                                        /* if($order->info['edit_orders_recalculate_totals']!=1)
                                          {
                                          $query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = '" . $GLOBALS[$class]->code . "' order by sort_order");
                                          if (tep_db_num_rows($query)){
                                          $data = tep_db_fetch_array($query);
                                          $order->totals[sizeof($order->totals) - 1]['orders_total_id'] = $data['orders_total_id'];
                                          $order->totals[sizeof($order->totals) - 1]['value'] = number_format($totals['value'], 2, '.', '');
                                          $TotalsArray[sizeof($TotalsArray) - 1]['TotalID'] = $data['orders_total_id'];
                                          $TotalsArray[sizeof($TotalsArray) - 1]['Name'] = $data['title'];
                                          $TotalsArray[sizeof($TotalsArray) - 1]['Price'] = number_format($data['value'], 2, '.', '');
                                          }
                                          } */
                                    }
                                }
                            }
                        }
                        /* if($order->info['edit_orders_recalculate_totals']==1)
                          {
                          if($cot_gv==1)
                          {
                          $gv_update=tep_db_query("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . tep_db_input($total_gv_amount_old) . "' where customer_id = '" . (int)$customer_id . "'");
                          tep_session_unregister('cot_gv');
                          }
                          } */

                        rsort($sort_orders);
                        if (($sort_orders[0] - 1) <= $sort_orders[1]) {
                            $new_sort_order = $sort_orders[0];
                            $TotalsArray[count($TotalsArray) - 1]["sort_order"] = $new_sort_order + 1;
                        } else {
                            $new_sort_order = $sort_orders[1] + 1;
                        }
                        $TotalsArray[] = array(
                            "Name" => "",
                            "Price" => "",
                            "Class" => "ot_custom",
                            "TotalID" => "0",
                            'sort_order' => $new_sort_order
                        );
                        foreach ($TotalsArray as $TotalIndex => $TotalDetails) {
                            $TotalStyle = "smallText";
                            if (($TotalDetails["Class"] == "ot_subtotal")) {
                                echo '       <tr>' . "\n" .
                                '   <td class="main p-or-t" align="right"><b>' . $TotalDetails["Name"] . '</b></td>' .
                                '   <td class="main p-or-t" style="text-align: right;"><b>' . $TotalDetails["Price"] .
                                "<input name='update_totals[$TotalIndex][title]' type='hidden' value='" . Output::output_string(trim($TotalDetails["Name"])) . "' size='" . strlen($TotalDetails["Name"]) . "' >" .
                                "<input name='update_totals[$TotalIndex][value]' type='hidden' value='" . Output::output_string($TotalDetails["Price"]) . "' size='6' >" .
                                "<input name='update_totals[$TotalIndex][class]' type='hidden' value='" . Output::output_string($TotalDetails["Class"]) . "'>\n" .
                                "<input type='hidden' name='update_totals[$TotalIndex][total_id]' value='" . Output::output_string($TotalDetails["TotalID"]) . "'>" . '</b></td>' .
                                '       </tr>' . "\n";
                                echo tep_draw_hidden_field('update_totals[' . $TotalIndex . '][sort_order]', $TotalDetails['sort_order']);
                            } else {
                                echo '       <tr>' . "\n" .
                                '   <td align="right" class="' . $TotalStyle . '">' . "<input name='update_totals[$TotalIndex][title]' size='" . strlen(trim($TotalDetails["Name"])) . "' value='" . Output::output_string(trim($TotalDetails["Name"])) . "' class='form-control'>" . '</td>' . "\n" .
                                '   <td align="right" class="' . $TotalStyle . '">' . "<input name='update_totals[$TotalIndex][value]' size='6' value='" . Output::output_string($TotalDetails["Price"]) . "' class='form-control'>" .
                                "<input type='hidden' name='update_totals[$TotalIndex][class]' value='" . Output::output_string($TotalDetails["Class"]) . "'>" .
                                "<input type='hidden' name='update_totals[$TotalIndex][total_id]' value='" . Output::output_string($TotalDetails["TotalID"]) . "'>" .
                                '</td>' . "\n" .
                                '       </tr>' . "\n";
                                echo tep_draw_hidden_field('update_totals[' . $TotalIndex . '][sort_order]', $TotalDetails['sort_order']);
                            }
                        }
                        ?>
                        <tr>              
                            <td class=main align="right"></td>
                        </tr>
                    </table></td>
            </tr>
        </table>
        <!-- End Order Total Block -->
        <?php
        //return $this->actionOrderEdit();
    }

    public function actionCountries() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {

            $search = "delivery_country like '%" . tep_db_input($term) . "%'";
        }

        $delivery_countries = array();
        $query_delivery_countries = "select delivery_country from " . TABLE_ORDERS . " where " . $search . " group by delivery_country order by delivery_country";
        $result_delivery_countries = tep_db_query($query_delivery_countries);
        while ($array_delivery_countries = tep_db_fetch_array($result_delivery_countries)) {
            $delivery_countries[] = $array_delivery_countries['delivery_country'];
        }
        echo json_encode($delivery_countries);
    }

    public function actionState() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $search = "1";
        if (!empty($country)) {
            $search .= " and delivery_country like '%" . tep_db_input($country) . "%'";
        }
        if (!empty($term)) {
            $search .= " and delivery_state like '%" . tep_db_input($term) . "%'";
        }

        $delivery_states = array();
        $query_delivery_states = "select delivery_state from " . TABLE_ORDERS . " where " . $search . " group by delivery_state order by delivery_state";
        $result_delivery_states = tep_db_query($query_delivery_states);
        while ($array_delivery_states = tep_db_fetch_array($result_delivery_states)) {
            $delivery_states[] = $array_delivery_states['delivery_state'];
        }
        echo json_encode($delivery_states);
    }

    public function actionInvoice() {

        global $languages_id, $language;

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        //$customer_number_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". tep_db_input(tep_db_prepare_input($oID)) . "'");
        //$customer_number = tep_db_fetch_array($customer_number_query);

        $payment_info_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . intval($oID) . "'");
        $payment_info = tep_db_fetch_array($payment_info_query);
        //$payment_info = $payment_info['payment_info'];

        $currencies = new \common\classes\currencies();

        $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . intval($oID) . "'");

        include(DIR_WS_CLASSES . 'order.php');
        $order = new \order($oID);

        if ($_GET['theme_name']) {
            $theme = tep_db_prepare_input($_GET['theme_name']);
        } else {
            $theme_array = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES . " where is_default = 1"));
            if ($theme_array['theme_name']) {
                $theme = $theme_array['theme_name'];
            } else {
                $theme = 'theme-1';
            }
        }
        define('THEME_NAME', $theme);

        return $this->render('invoice.tpl', [
                    'order' => $order,
                    'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
                    'payment_info' => $payment_info,
                    'oID' => $oID,
                    'currencies' => $currencies,
        ]);
    }

    public function actionPackingslip() {

        global $languages_id, $language;

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        $currencies = new \common\classes\currencies();

        //$orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . tep_db_input($oID) . "'");

        include(DIR_WS_CLASSES . 'order.php');
        $order = new \order($oID);

        return $this->render('packingslip.tpl', [
                    'order' => $order,
                    'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
                    'oID' => $oID,
                    'currencies' => $currencies,
        ]);
    }

    public function actionOrdersdelete() {

        $this->layout = false;

        $selected_ids = Yii::$app->request->post('selected_ids');

        foreach ($selected_ids as $orders_id) {
            \common\helpers\Order::remove_order((int) $orders_id, (int) $_POST['restock']);
        }
    }

    public function actionOrdersbatch() {

        \common\helpers\Translation::init('main');

        require_once (DIR_WS_CLASSES . 'order.php');
        //require_once(DIR_WS_INCLUDES . 'mc_table.php');

        global $currencies;
        $currencies = new \common\classes\currencies();

        /* $vendorDir = dirname(dirname(__FILE__));
          $baseDir = dirname($vendorDir);
          include_once ($baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'html2pdf' . DIRECTORY_SEPARATOR . 'html2pdf.class.php');
          $html2pdf = new \HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(14, 5, 5, 8));
          $content_start = "<page>";
          $content_end = "</page>"; */
        $html = "";

        /* $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
          $pdf->SetCreator('TCPDF');
          $pdf->SetAuthor('Holbi');
          $pdf->SetTitle('TrueLoaded');
          $pdf->SetSubject('Invoice');
          $pdf->SetKeywords('Invoice');
          $pdf->setPrintHeader(false);
          $pdf->setPrintFooter(false); */

//        $pdf->AddPage();
//        $html = "content";//$this->render('tenancy-agreement-template.tpl', []);
//        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
//        $pdf->Output('example.pdf', 'I');
//        die();


        /* $pdf = new \PDF_MC_Table();
          //$pdf->Open();
          $pdf->SetFont('Times','',12);
          $pdf->SetDisplayMode('real');
          //$pdf->AliasNbPages(); */

        $use_pdf = true;

        $html_document_start = '';
        $html_document_end = '';
        if (!$use_pdf) {
            $content_start = "";
            $content_end = "";
        }

        $pages = array();

        $filename = 'document';

        if (isset($_GET['pdf']) && $_GET['pdf'] == 'invoice') {
            if ($_GET['action'] == 'selected' && tep_not_null($_POST['orders'])) {
                $orders_query = tep_db_query("select orders_id, platform_id, language_id from " . TABLE_ORDERS . " where orders_id in(" . $_POST['orders'] . ")");
                while ($orders = tep_db_fetch_array($orders_query)) {

                    $order = new order($orders['orders_id']);
                    $lan_id = $orders['language_id'] ? $orders['language_id'] : \common\classes\language::defaultId();
                    //$html .= $content_start . $orders['orders_id'] . $content_end;
                    $pages[] = ['name' => 'invoice', 'params' => [
                            'orders_id' => $orders['orders_id'],
                            'platform_id' => $orders['platform_id'] ? $orders['platform_id'] : 1,
                            'language_id' => $lan_id,
                            'order' => $order,
                            'currencies' => $currencies,
                            'oID' => $orders['orders_id']
                    ]];
                    $filename = 'invoice';
                    $platform_id = $orders['platform_id'];

                    $document_content = file_get_contents(tep_catalog_href_link('email-template/invoice', 'key=UNJfMzvmwE6EVbL6' . ($use_pdf ? '&to_pdf=1' : '') . '&orders_id=' . $orders['orders_id'] . '&platform_id=' . $orders['platform_id'] . '&language=' . \common\classes\language::get_code($lan_id)));
                    if (!$use_pdf && empty($html_document_start)) {
                        $body_start = stripos($document_content, '<body');
                        if ($body_start !== false) {
                            $body_start = strpos($document_content, '>', $body_start) + 1;
                            $html_document_start = substr($document_content, 0, $body_start);
                        }
                        $body_end = stripos($document_content, '</body>');
                        if ($body_end !== false) {
                            $html_document_end = substr($document_content, $body_end);
                        }
                    }
                    $html .= $content_start . $document_content . $content_end;

                    if (!$use_pdf && empty($content_start))
                        $content_start .= '<p style="page-break-after:always;"></p>';
                }
            } if (isset($_GET['oID']) && !empty($_GET['oID'])) {
                $orders_query = tep_db_query("select orders_id, platform_id, language_id from " . TABLE_ORDERS . " where orders_id ='" . (int) $_GET['oID'] . "'");
                if (tep_db_num_rows($orders_query) > 0) {
                    $get = tep_db_fetch_array($orders_query);

                    $order = new order($orders['orders_id']);

                    $lan_id = $orders['language_id'] ? $orders['language_id'] : \common\classes\language::defaultId();
                    $pages[] = ['name' => 'invoice', 'params' => [
                            'orders_id' => $orders['orders_id'],
                            'platform_id' => $orders['platform_id'],
                            'language_id' => $lan_id,
                            'order' => $order,
                            'currencies' => $currencies,
                            'oID' => $orders['orders_id']
                    ]];
                    $filename = 'invoice';
                    $platform_id = $orders['platform_id'];

                    $document_content = file_get_contents(tep_catalog_href_link('email-template/invoice', 'key=UNJfMzvmwE6EVbL6' . ($use_pdf ? '&to_pdf=1' : '') . '&orders_id=' . $get['orders_id'] . '&platform_id=' . $get['platform_id'] . '&language=' . \common\classes\language::get_code($lan_id)));
                    if (!$use_pdf && empty($html_document_start)) {
                        $body_start = stripos($document_content, '<body');
                        if ($body_start !== false) {
                            $body_start = strpos($document_content, '>', $body_start) + 1;
                            $html_document_start = substr($document_content, 0, $body_start);
                        }
                        $body_end = stripos($document_content, '</body>');
                        if ($body_end !== false) {
                            $html_document_end = substr($document_content, $body_end);
                        }
                    }

                    $html .= $content_start . $document_content . $content_end;

                    if (!$use_pdf && empty($content_start))
                        $content_start .= '<p style="page-break-after:always;"></p>';
                }
            }
        } else {
            if ($_GET['action'] == 'selected' && tep_not_null($_POST['orders'])) {
                $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_id in(" . $_POST['orders'] . ")");
            } else if (isset($_GET['oID']) && !empty($_GET['oID'])) {
                $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_id ='" . (int) $_GET['oID'] . "'");
            } else {
                $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_status = 1");
            }
            while ($orders = tep_db_fetch_array($orders_query)) {

                $order = new order($orders['orders_id']);
                $lan_id = $orders['language_id'] ? $orders['language_id'] : \common\classes\language::defaultId();

                $pages[] = ['name' => 'packingslip', 'params' => [
                        'orders_id' => $orders['orders_id'],
                        'platform_id' => $orders['platform_id'],
                        'language_id' => $lan_id,
                        'order' => $order,
                        'currencies' => $currencies,
                        'oID' => $orders['orders_id']
                ]];
                $filename = 'packingslip';
                $platform_id = $orders['platform_id'];

                $document_content = file_get_contents(tep_catalog_href_link('email-template/packingslip', 'key=UNJfMzvmwE6EVbL6' . ($use_pdf ? '&to_pdf=1' : '') . '&orders_id=' . $orders['orders_id'] . '&platform_id=' . $orders['platform_id'] . '&language=' . \common\classes\language::get_code($lan_id)));
                if (!$use_pdf && empty($html_document_start)) {
                    $body_start = stripos($document_content, '<body');
                    if ($body_start !== false) {
                        $body_start = strpos($document_content, '>', $body_start) + 1;
                        $html_document_start = substr($document_content, 0, $body_start);
                    }
                    $body_end = stripos($document_content, '</body>');
                    if ($body_end !== false) {
                        $html_document_end = substr($document_content, $body_end);
                    }
                }
                $html .= $content_start . $document_content . $content_end;

                if (!$use_pdf && empty($content_start))
                    $content_start .= '<p style="page-break-after:always;"></p>';
            }
        }

        if (!$use_pdf) {
            echo $html_document_start;
            echo $html;
            echo $html_document_end;
            die;
        }

        if ($platform_id) {
            $theme = tep_db_fetch_array(tep_db_query("select t.theme_name from " . TABLE_THEMES . " t, " . TABLE_PLATFORMS_TO_THEMES . " p2t where t.id = p2t.theme_id and p2t.platform_id='" . $platform_id . "'"));
        } else {
            $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));
        }
        \backend\design\PDFBlock::widget([
            'pages' => $pages,
            'params' => [
                'theme_name' => $theme['theme_name'],
                'document_name' => $filename,
            ]
        ]);
    }

    public function actionCustomer() {
        $name = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($name)) {
            $search = "customers_lastname like '%" . tep_db_input($name) . "%' or customers_firstname like '%" . tep_db_input($name) . "%' or customers_email_address like '%" . tep_db_input($name) . "%'";
        }

        $customers = array();
        $query_delivery_countries = "select customers_id, customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where " . $search . " group by customers_id order by customers_lastname";
        $result_delivery_countries = tep_db_query($query_delivery_countries);
        while ($db_Row = tep_db_fetch_array($result_delivery_countries)) {
            $customers[] = array('id' => $db_Row["customers_id"], 'value' => $db_Row["customers_lastname"] . "  " . $db_Row["customers_firstname"] . ' ' . $db_Row['customers_email_address']);
        }
        echo json_encode($customers);
    }

    public function actionUpdatepay() {
        global $languages_id, $language, $currencies, $order_total_modules;

        if (!is_object($currencies)) {
            $currencies = new \common\classes\currencies();
        }
        $session = new \yii\web\Session;

        $this->view->headingTitle = HEADING_TITLE;
        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/orders/order-edit');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/index'), 'title' => HEADING_TITLE);
        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id', 0);

        if ($orders_id) {
            $result = tep_db_fetch_array(tep_db_query("select customers_id, platform_id from " . TABLE_ORDERS . " where orders_id='" . (int) $orders_id . "'"));
            $customers_id = $result['customers_id'];
            $platform_id = $result['platform_id'];
        } else {
            $platform_id = $session['platform_id'];
            $customers_id = $session['customer_id'];
        }

        $platform_config = new platform_config($platform_id);
        $platform_config->constant_up();

        $result = tep_db_fetch_array(tep_db_query("select credit_amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'"));
        $credit_amount = $result['credit_amount'];

        //$new_ot_total = 0;
        $_c = tep_db_fetch_array(tep_db_query("select code, value from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$_POST['currency_id'] . "'"));
        $new_ot_total = Yii::$app->request->post('ot_total') * $currencies->get_market_price_rate($_c['code'], DEFAULT_CURRENCY);
        $ot_paid = (float) Yii::$app->request->post('ot_paid') * $currencies->get_market_price_rate($_c['code'], DEFAULT_CURRENCY);
        /* foreach ($update_totals as $key => $value) {
          if ($value['class'] == 'ot_total') {
          $new_ot_total = $value['value'];
          break;
          }
          } */

        //$result = tep_db_fetch_array(tep_db_query("select value_inc_tax from " . TABLE_ORDERS_TOTAL . " where orders_id='" . $orders_id . "' and class ='ot_total'"));
        $old_ot_total = $ot_paid;

        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'getActions')) {
            return $ext::getActions($old_ot_total, $new_ot_total);
        }
        
        $difference_ot_total = $old_ot_total - $new_ot_total;
        $difference = ($difference_ot_total >= 0 ? true : false);

        return $this->render('updatepay', [
                    'new_ot_total' => $currencies->format($new_ot_total, true, $_c['code'], $_c['value']),
                    'old_ot_total' => $currencies->format($old_ot_total, true, $_c['code'], $_c['value']),
                    'difference_ot_total' => $currencies->format($difference_ot_total, true, $_c['code'], $_c['value']),
                    'pay_difference' => $difference_ot_total,
                    'difference' => $difference,
                    'difference_desc' => $difference ? CREDIT_AMOUNT : TEXT_AMOUNT_DUE,
        ]);
    }

    function actionGettracking() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/orders');
        $this->layout = false;
        $this->view->usePopupMode = true;
        if (Yii::$app->request->isPost) {
            $oID = Yii::$app->request->post('orders_id');
        } else {
            $oID = Yii::$app->request->get('orders_id');
        }
        $get_tracking = tep_db_query("select tracking_number from " . TABLE_ORDERS . " where orders_id = " . (int) $oID);
        //die("select tracking_number from " . TABLE_ORDERS . " where orders_id = " . (int)$_GET['orders_id']);
        if (tep_db_num_rows($get_tracking) > 0) {
            $result_tracking = tep_db_fetch_array($get_tracking);
            $html = '<div id="trackingNumber"><form name="savetrack" method="post" onSubmit="return saveTracking();"><div class="trackingBox"><input name="tracking_number" type="text" value="' . ($result_tracking['tracking_number'] ? $result_tracking['tracking_number'] : '') . '" class="form-control"><input type="hidden" name="orders_id" value="' . (int) $_GET['orders_id'] . '"></div><div class="btn-bar edit-btn-bar"><div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return closePopup();">' . IMAGE_CANCEL . '</a></div><div class="btn-right"><button class="btn btn-primary">' . IMAGE_SAVE . '</button></div></div></form></div>';
            return $html;
        } else {
            return false;
        }
    }

    function actionSavetracking() {
        global $languages_id, $language, $admin_id;
        \common\helpers\Translation::init('admin/orders');
        $messageType = '';
        $message = '';
        if (Yii::$app->request->isPost) {
            $oID = intval(Yii::$app->request->post('orders_id'));
            $tracking_number = tep_db_prepare_input(Yii::$app->request->post('tracking_number'));
        } else {
            $oID = intval(Yii::$app->request->get('orders_id'));
            $tracking_number = tep_db_prepare_input(Yii::$app->request->get('tracking_number'));
        }
        \common\helpers\Translation::init('admin/orders');

        if (tep_not_null($tracking_number)) {
// {{
            require_once(DIR_WS_CLASSES . 'order.php');
            $order = new \order($oID);
            $platform_config = Yii::$app->get('platform')->config($order->info['platform_id']);
            if ($order->info['tracking_number'] != $tracking_number) {
                $notify_comments = TEXT_TRACKING_NUMBER . ': ' . $tracking_number;

                $STORE_NAME = $platform_config->const_value('STORE_NAME');
                $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                $eMail_store = $STORE_NAME;
                $eMail_address = $STORE_OWNER_EMAIL_ADDRESS;
                $eMail_store_owner = $STORE_OWNER;

                $email = $eMail_store . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" .
                        EMAIL_TEXT_INVOICE_URL . ' ' . \common\helpers\Output::get_clickable_link(tep_catalog_href_link('account/history-info', 'order_id=' . $oID, 'SSL')) . "\n" .
                        EMAIL_TEXT_DATE_ORDERED . ' ' . \common\helpers\Date::date_long($order->info['date_purchased']) . "\n\n" .
                        $notify_comments . "\n\n" .
                        '<a href="' . $platform_config->const_value('TRACKING_NUMBER_URL') . $tracking_number . '" target="_blank"><img border="0" alt="' . $tracking_number . '" src="' . tep_catalog_href_link('account/order-qrcode', 'oID=' . (int) $oID . '&cID=' . (int) $order->customer['customer_id'] . '&tracking=1', 'SSL') . '"></a>';

                \common\helpers\Mail::send($order->customer['name'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email, $STORE_OWNER, $eMail_address);

                $sql_data_array = array('tracking_number' => $tracking_number);
                tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . (int) $oID . "'");

                tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_id) values ('" . (int) $oID . "', '" . tep_db_input($order->info['order_status']) . "', now(), '1', '" . tep_db_input($notify_comments) . "', '" . tep_db_input($admin_id) . "')");
            }
// }}
            $messageType = 'success';
            $message = TEXT_TRACKING_MESSAGE_SUCCESS;
        } else {
            $messageType = 'warning';
            $message = TEXT_TRACKING_MESSAGE_WARNING;
        }

        $html = '<div class="alert alert-' . $messageType . ' fade in"><i data-dismiss="alert" class="icon-remove close"></i>' . $message . '</div>';
        return $html;
    }

    public function actionOrdersexport() {
        require_once (DIR_WS_CLASSES . 'order.php');
        if (tep_not_null($_POST['orders'])) {
            $separator = "\t";
            $filename = 'orders_' . strftime('%Y%b%d_%H%I') . '.csv';

            header('Content-Type: application/vnd.ms-excel');
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            } else {
                header('Pragma: no-cache');
            }

            echo chr(0xff) . chr(0xfe);

            $csv_str = '"Order ID"' . $separator . '"Ship Method"' . $separator . '"Shipping Company"' . $separator . '"Shipping Street 1"' . $separator . '"Shipping Street 2"' . $separator . '"Shipping Suburb"' . $separator . '"Shipping State"' . $separator . '"Shipping Zip"' . $separator . '"Shipping Country"' . $separator . '"Shipping Name"' . "\r\n";

            $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id in ('" . implode("','", array_map('intval', explode(',', $_POST['orders']))) . "')");
            while ($orders = tep_db_fetch_array($orders_query)) {
                $order = new \order($orders['orders_id']);

                $csv_str .= '"' . $this->saveText($orders['orders_id']) . '"' . $separator . '"' . $this->saveText($order->info['shipping_method']) . '"' . $separator . '"' . $this->saveText($order->delivery['company']) . '"' . $separator . '"' . $this->saveText($order->delivery['street_address']) . '"' . $separator . '"' . $this->saveText($order->delivery['suburb']) . '"' . $separator . '"' . $this->saveText($order->delivery['city']) . '"' . $separator . '"' . $this->saveText($order->delivery['state']) . '"' . $separator . '"' . $this->saveText($order->delivery['postcode']) . '"' . $separator . '"' . $this->saveText($order->delivery['country']['title']) . '"' . $separator . '"' . $this->saveText($order->delivery['name']) . '"' . "\r\n";
            }

            $csv_str = mb_convert_encoding($csv_str, 'UTF-16LE', 'UTF-8');
            echo $csv_str;
        }
        exit;
    }

    function saveText($thetext) {
        if (!tep_not_null($thetext))
            return '';
        $thetext = str_replace("\r", '\r', $thetext);
        $thetext = str_replace("\n", '\n', $thetext);
        $thetext = str_replace("\t", '\t', $thetext);
        $thetext = str_replace('\"', '"', $thetext);
        $thetext = str_replace('"', '""', $thetext);

        return $thetext;
    }

    public function actionGvChangeState() {
        \common\helpers\Translation::init('admin/orders');

        $opID = intval(Yii::$app->request->get('opID', 0));
        $_order_id = tep_db_fetch_array(tep_db_query("SELECT orders_id, gv_state FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_products_id='" . (int) $opID . "'"));
        if (Yii::$app->request->isPost) {
            tl_credit_order_manual_update_state($opID, Yii::$app->request->post('new_gv_state', $_order_id['gv_state']));
            echo 'ok';
        }
        ?>
        <?php echo tep_draw_form('update_gv', 'orders/gv-change-state', \common\helpers\Output::get_all_get_params(), 'post', 'id="frmGvChangeState"'); ?>
        <div class="pop-up-content">
            <div class="popup-content">
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'pending', $_order_id['gv_state'] == 'pending', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_PENDING ?></label></div>
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'released', $_order_id['gv_state'] == 'released', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_RELEASED ?></label></div>
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'canceled', $_order_id['gv_state'] == 'canceled', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_CANCELED ?></label></div>
            </div>
        </div>
        <div class="noti-btn">
            <div><span class="btn btn-cancel"><?php echo IMAGE_CANCEL; ?></span></div>
            <div><span class="btn btn-primary" id="btnGvChangeState"><?php echo IMAGE_UPDATE; ?></span></div>
        </div>
        </form>
        <script type="text/javascript">
            $('#btnGvChangeState').on('click', function () {
                $.ajax({
                    type: "POST",
                    url: $('#frmGvChangeState').attr('action'),
                    data: $('#frmGvChangeState').serializeArray(),
                    success: function (data) {
                        window.location.href = window.location.href;
                        $('#frmGvChangeState .btn-cancel').trigger('click');
                    }
                });
            });
        </script>
        <?php
    }

}
