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

use common\helpers\Acl;
use Yii;

/**
 * default controller to handle user requests.
 */
class CustomersController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_CUSTOMERS'];

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        $this->selectedMenu = array('customers', 'customers');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('customers/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['orders/create', 'back' => 'customers']).'" class="create_item"><i class="icon-file-text"></i>'.TEXT_CREATE_NEW_OREDER.'</a><a href="'.Yii::$app->urlManager->createUrl('customers/insert').'" class="create_item add_new_cus_item"><i class="icon-user-plus"></i>'.TEXT_ADD_NEW_CUSTOMER.'</a>';
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->customersTable = array(
          array(
            'title' => '<input type="checkbox" class="uniform">',
            'not_important' => 2
          ),
          array(
            'title' => ENTRY_LAST_NAME,
            'not_important' => 0
          ),
          array(
            'title' => ENTRY_FIRST_NAME,
            'not_important' => 0
          ),
          array(
            'title' => TABLE_HEADING_EMAIL . '/' . TABLE_HEADING_PLATFORM,
            'not_important' => 0
          ),
          array(
            'title' => TABLE_HEADING_ACCOUNT_CREATED,
            'not_important' => 1
          ),
          array(
            'title' => TABLE_HEADING_LOCATION,
            'not_important' => 0
          ),
          array(
            'title' => TABLE_HEADING_ORDER_COUNT,
            'not_important' => 0
          ),
          array(
            'title' => TABLE_HEADING_TOTAL_ORDERED,
            'not_important' => 0
          ),
          array(
            'title' => TABLE_HEADING_DATE_LAST_ORDER,
            'not_important' => 0
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
            'name' => ENTRY_FIRST_NAME,
            'value' => 'firstname',
            'selected' => '',
          ],
          [
            'name' => ENTRY_LAST_NAME,
            'value' => 'lastname',
            'selected' => '',
          ],
          [
            'name' => TEXT_EMAIL,
            'value' => 'email',
            'selected' => '',
          ],
          [
            'name' => ENTRY_COMPANY,
            'value' => 'companyname',
            'selected' => '',
          ],
          [
            'name' => ENTRY_TELEPHONE_NUMBER,
            'value' => 'phone',
            'selected' => '',
          ],
          [
            'name' => TEXT_ZIP_CODE,
            'value' => 'postcode',
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

        $this->view->filters->showGroup = (CUSTOMERS_GROUPS_ENABLE == 'True');
        $group = '';
        if (isset($_GET['group'])) {
            $group = $_GET['group'];
        }
        $this->view->filters->group = $group;

        $country = '';
        if (isset($_GET['country'])) {
            $country = $_GET['country'];
        }
        $this->view->filters->country = $country;

        $state = '';
        if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
            $this->view->showState = true;
        } else {
            $this->view->showState = false;
        }
        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        }
        $this->view->filters->state = $state;

        $city = '';
        if (isset($_GET['city'])) {
            $city = $_GET['city'];
        }
        $this->view->filters->city = $city;

        $company = '';
        if (isset($_GET['company'])) {
            $company = $_GET['company'];
        }
        $this->view->filters->company = $company;

        $guest = [
          [
            'name' => TEXT_ALL_CUSTOMERS,
            'value' => '',
            'selected' => '',
          ],
          [
            'name' => TEXT_BTN_YES,
            'value' => 'y',
            'selected' => '',
          ],
          [
            'name' => TEXT_BTN_NO,
            'value' => 'n',
            'selected' => '',
          ],
        ];
        foreach ($guest as $key => $value) {
            if (isset($_GET['guest']) && $value['value'] == $_GET['guest']) {
                $guest[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->guest = $guest;

        $newsletter = [
          [
            'name' => TEXT_ANY,
            'value' => '',
            'selected' => '',
          ],
          [
            'name' => TEXT_SUBSCRIBED,
            'value' => 's',
            'selected' => '',
          ],
          [
            'name' => TEXT_NOT_SUBSCRIBED,
            'value' => 'ns',
            'selected' => '',
          ],
        ];
        foreach ($newsletter as $key => $value) {
            if (isset($_GET['newsletter']) && $value['value'] == $_GET['newsletter']) {
                $newsletter[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->newsletter = $newsletter;

        $status = [
          [
            'name' => TEXT_ALL,
            'value' => '',
            'selected' => '',
          ],
          [
            'name' => TEXT_ACTIVE,
            'value' => 'y',
            'selected' => '',
          ],
          [
            'name' => TEXT_NOT_ACTIVE,
            'value' => 'n',
            'selected' => '',
          ],
        ];
        foreach ($status as $key => $value) {
            if (isset($_GET['status']) && $value['value'] == $_GET['status']) {
                $status[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->status = $status;

        $title = [
          [
            'name' => TEXT_ALL,
            'value' => '',
            'selected' => '',
          ],
          [
            'name' => T_MR,
            'value' => 'm',
            'selected' => '',
          ],
          [
            'name' => T_MRS,
            'value' => 'f',
            'selected' => '',
          ],
          [
            'name' => T_MISS,
            'value' => 's',
            'selected' => '',
          ],
        ];
        foreach ($title as $key => $value) {
            if (isset($_GET['title']) && $value['value'] == $_GET['title']) {
                $title[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->title = $title;

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

        $this->view->filters->platform = array();
        if ( isset($_GET['platform']) && is_array($_GET['platform']) ){
            foreach( $_GET['platform'] as $_platform_id ) if ( (int)$_platform_id>0 ) $this->view->filters->platform[] = (int)$_platform_id;
        }

        $this->view->filters->row = (int)$_GET['row'];

        return $this->render('index',[
          'isMultiPlatform' => \common\classes\platform::isMulti(),
          'platforms' => \common\classes\platform::getList(),
        ]);
    }

    public function actionInsert() {
        global $languages_id, $language, $messageStack;

        \common\helpers\Translation::init('admin/customers');
        $this->selectedMenu = array('customers', 'customers');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('customers/insert'), 'title' => TEXT_ADD_NEW_CUSTOMER);
        $this->view->headingTitle = TEXT_ADD_NEW_CUSTOMER;

        $this->view->showGroup = (CUSTOMERS_GROUPS_ENABLE == 'True');
        if ($this->view->showGroup) {
            $groupStatusArray = [];
            $groupStatusArray[''] = '';
            $status_query = tep_db_query("select * from " . TABLE_GROUPS);
            while ($status = tep_db_fetch_array($status_query)){
                $groupStatusArray[$status['groups_id']] = $status['groups_name'];
            }
            $this->view->groupStatusArray = $groupStatusArray;
        }

        $this->view->showDOB = in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register']);
        $this->view->showState = in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register']);

        if (isset($_GET['redirect'])) {
            $this->view->redirect = $_GET['redirect'];
        } else {
            $this->view->redirect = 'customeredit';
        }

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }

        $platform_variants = array();
        foreach( \common\classes\platform::getList(false) as $_p ) { $platform_variants[$_p['id']] = $_p['text']; }

        return $this->render('insert', ['platforms' => $platform_variants]);
    }

    public function actionCustomerlist() {
        global $languages_id;

        $draw = Yii::$app->request->get('draw');
        $start = Yii::$app->request->get('start');
        $length = Yii::$app->request->get('length');

        if( $length == -1 ) $length = 10000;
        
        $currencies = new \common\classes\currencies();

        $search = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where (c.customers_lastname like '%" . $keywords . "%' or c.customers_firstname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%')";
        } else {
            $search_condition = " where 1 ";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $filter = '';

        $filter_by_platform = array();
        if ( isset($output['platform']) && is_array($output['platform']) ){
            foreach( $output['platform'] as $_platform_id ) if ( (int)$_platform_id>0 ) $filter_by_platform[] = (int)$_platform_id;
        }

        if ( count($filter_by_platform)>0 ) {
            $filter .= " and c.platform_id IN ('" . implode("', '",$filter_by_platform). "') ";
        }

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            switch ($output['by']) {
                case 'firstname':
                    $filter .= " and c.customers_firstname like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'lastname':
                    $filter .= " and c.customers_lastname like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'email': default:
                $filter .= " and c.customers_email_address like '%" . tep_db_input($search) . "%' ";
                break;
                case 'companyname':
                    $filter .= " and a.entry_company like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'phone':
                    $filter .= " and c.customers_telephone like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'postcode':
                    $filter .= " and a.entry_postcode like '%" . tep_db_input($search) . "%' ";
                    break;
                case '':
                case 'any':
                    $filter .= " and (";
                    $filter .= " c.customers_firstname like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or c.customers_lastname like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or c.customers_email_address like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or a.entry_company like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or c.customers_telephone like '%" . tep_db_input($search) . "%' ";
                    $filter .= " or a.entry_postcode like '%" . tep_db_input($search) . "%' ";
                    $filter .= ") ";
                    break;
            }
        }

        if (tep_not_null($output['group'])) {
            $filter .= " and g.groups_name like '%".tep_db_input($output['group'])."%'";
        }

        if (tep_not_null($output['country'])) {
            $filter .= " and cn.countries_name like '%".tep_db_input($output['country'])."%'";
        }
        if (tep_not_null($output['state'])) {
            $filter .= " and (a.entry_state like '%" . tep_db_input($output['state']) . "%' or z.zone_name like '%" . tep_db_input($output['state']) . "%')";
        }
        if (tep_not_null($output['city'])) {
            $filter .= " and a.entry_city like '%".tep_db_input($output['city'])."%'";
        }

        if (tep_not_null($output['company'])) {
            $filter .= " and a.entry_company like '%".tep_db_input($output['company'])."%'";
        }

        if (tep_not_null($output['newsletter'])) {
            switch ($output['newsletter']) {
                case 's':
                    $filter .= " and c.customers_newsletter = '1' ";
                    break;
                case 'ns':
                    $filter .= " and c.customers_newsletter = '0' ";
                    break;
                default:
                    break;
            }
        }

        if (tep_not_null($output['status'])) {
            switch ($output['status']) {
                case 'y':
                    $filter .= " and c.customers_status = '1' ";
                    break;
                case 'n':
                    $filter .= " and c.customers_status = '0' ";
                    break;
                default:
                    break;
            }
        }
        if (tep_not_null($output['guest'])) {
            switch ($output['guest']) {
                case 'y':
                    $filter .= " and c.opc_temp_account = '1' ";
                    break;
                case 'n':
                    $filter .= " and c.opc_temp_account = '0' ";
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
                        $filter .= " and to_days(ci.customers_info_date_account_created) >= to_days('" . $date->format('Y-m-d') . "')";
                    }
                    if (tep_not_null($output['to'])) {
                        $to = tep_db_prepare_input($output['to']);
                        $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $to);
                        $filter .= " and to_days(ci.customers_info_date_account_created) <= to_days('" . $date->format('Y-m-d') . "')";
                    }
                    break;
                case 'presel':
                    if (tep_not_null($output['interval'])) {
                        switch ($output['interval']) {
                            case 'week':
                                $filter .= " and ci.customers_info_date_account_created >= '" . date('Y-m-d', strtotime('monday this week')) . "'";
                                break;
                            case 'month':
                                $filter .= " and ci.customers_info_date_account_created >= '" . date('Y-m-d', strtotime('first day of this month')) . "'";
                                break;
                            case 'year':
                                $filter .= " and ci.customers_info_date_account_created >= '" . date("Y")."-01-01" . "'";
                                break;
                            case '1':
                                $filter .= " and ci.customers_info_date_account_created >= '" . date('Y-m-d') . "'";
                                break;
                            case '3':
                            case '7':
                            case '14':
                            case '30':
                                $filter .= " and ci.customers_info_date_account_created >= date_sub(now(), interval " . (int)$output['interval'] . " day)";
                                break;
                        }
                    }
                    break;
            }
        }

        if (tep_not_null($output['title'])) {
            switch ($output['title']) {
                case 'm':
                case 'f':
                case 's':
                    $filter .= " and c.customers_gender = '".tep_db_input($output['title'])."' ";
                    break;
                default:
                    break;
            }
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "c.customers_lastname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "c.customers_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "c.customers_email_address " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "c.customers_lastname, c.customers_firstname";
                    break;
            }
        } else {
            $orderBy = "c.customers_lastname, c.customers_firstname";
        }

        $customers_query_raw = "select distinct(c.customers_id), c.platform_id, c.customers_default_address_id, customers_info_date_account_created as date_account_created, c.customers_gender, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.groups_id, c.opc_temp_account, a.entry_country_id, a.entry_postcode, a.entry_firstname, a.entry_lastname, a.entry_street_address, a.entry_city, if (LENGTH(a.entry_state), a.entry_state, z.zone_name) as state, cn.countries_name as country from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id=ci.customers_info_id left join " . TABLE_ADDRESS_BOOK . " a on a.customers_id = c.customers_id left join " . TABLE_COUNTRIES . " cn on a.entry_country_id=cn.countries_id  and cn.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=cn.countries_id and a.entry_zone_id=z.zone_id left join " . TABLE_GROUPS . " g on c.groups_id=g.groups_id" . $search_condition . $filter . " group by c.customers_id order by " . $orderBy;
        $current_page_number = ($start / $length) + 1;
        $customers_split = new \splitPageResults($current_page_number, $length, $customers_query_raw, $customers_query_numrows, 'c.customers_id');
        $customers_query = tep_db_query($customers_query_raw);
        $responseList = array();
        while ($customers = tep_db_fetch_array($customers_query)) {

            $address_query = tep_db_query("select a.entry_country_id, a.entry_postcode, a.entry_firstname, a.entry_lastname, a.entry_street_address, a.entry_city, if (LENGTH(a.entry_state), a.entry_state, z.zone_name) as state, cn.countries_name as country from " . TABLE_ADDRESS_BOOK . " a left join " . TABLE_COUNTRIES . " cn on a.entry_country_id=cn.countries_id  and cn.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=cn.countries_id and a.entry_zone_id=z.zone_id where address_book_id = " . $customers['customers_default_address_id']);
            $address = tep_db_fetch_array($address_query);
            if (is_array($address)) {
                foreach ($address as $key => $value) {
                    $customers[$key] = $value;
                }
            }

            $info_query = tep_db_query("select count(*) as total_orders, max(o.date_purchased) as last_purchased, sum(ot.value) as total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." and ot.class='ot_total' and o.customers_id = ". $customers['customers_id']);

            //$info_query = tep_db_query("select count(*) as total_orders, max(date_purchased) as last_purchased from " . TABLE_ORDERS . " where customers_id = '" . $customers['customers_id'] . "'");
            $info = tep_db_fetch_array($info_query);

            //------
            //$customers_email_address = $orders['customers_email_address'];

            if (trim($search)!='') {
                $hilite_function = function ($search, $text) {
                    $w = preg_quote(trim($search),'/');
                    $regexp = "/($w)(?![^<]+>)/i";
                    $replacement = '<b style="color:#ff0000">\\1</b>';
                    return preg_replace($regexp, $replacement, $text);
                };
            }else {
                $hilite_function = function ($search, $text) {
                    return $text;
                };
            }
            //------

            $responseList[] = array(
              '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $customers['customers_id'] . '">',
              ($customers['opc_temp_account'] == 1 ? '<i style="color: #03a2a0;">'.TEXT_GUEST.'</i><br>' : '').'<div class="c-list-name ord-gender click_double ord-gender-'.$customers['customers_gender'].'" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.$hilite_function($search,$customers['customers_lastname']) . '<input class="cell_identify" type="hidden" value="' . $customers['customers_id'] . '"></div>',
              '<div class="c-list-name click_double"  data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.$hilite_function($search,$customers['customers_firstname']).'</div>',
              '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '"><a class="ord-name-email" href="mailto:'.$customers['customers_email_address'].'"><b' . (strlen($customers['customers_email_address'])>30 ? ' title="' . $customers['customers_email_address'] . '"' : '') . '>'.$hilite_function($search,substr($customers['customers_email_address'], 0, 30)). (strlen($customers['customers_email_address'])>30 ? '...' : ''). '</b></a><br>' .(\common\classes\platform::isMulti() >= 1 ? '<b>'.TABLE_HEADING_PLATFORM . ':</b>&nbsp;' . \common\classes\platform::name($customers['platform_id']) : '').'</div>',
              '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.\common\helpers\Date::date_short($customers['date_account_created']).'</div>',
              '<div class="ord-location click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.$hilite_function($search,$customers['entry_postcode']).'<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>'.$customers['entry_firstname'] . ' ' . $customers['entry_lastname'] .'</b>'.$customers['entry_street_address'].'<br>'.$customers['entry_city'].', '.$customers['state']. '&nbsp;' .$customers['entry_postcode'].'<br>'.$customers['country'].'</div></div>',
              '<div class="c-list-count click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.$info['total_orders'].'</div>',
              '<div class="c-list-total click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '">'.$currencies->format($info['total_sum']).'</div>',
              '<div class="c-list-date-last click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers['customers_id']]) . '"><span>'.\common\helpers\Date::datetime_short($info['last_purchased']).'</span>' . \common\helpers\Date::getDateRange(date('Y-m-d'), $info['last_purchased']) .'</div>',
                //'<input type="button" class="btn btn-primary pull-right" value="Edit" onClick="return editCustomer(' . $customers['customers_id'] . ')">'.'<input class="cell_identify" type="hidden" value="' . $customers['customers_id'] . '">'
            );
        }
        $response = array(
          'draw' => $draw,
          'recordsTotal' => $customers_query_numrows,
          'recordsFiltered' => $customers_query_numrows,
          'data' => $responseList
        );
        echo json_encode($response);
        //die();
    }

    public function actionCustomeractions() {

        global $languages_id, $language, $messageStack;

        \common\helpers\Translation::init('admin/customers');

        $currencies = new \common\classes\currencies();

        $this->layout = false;

        $customers_id = Yii::$app->request->post('customers_id');

        $customers_query = tep_db_query("select distinct(c.customers_id), c.last_xml_export, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.groups_id, a.entry_country_id, c.admin_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on  a.address_book_id = c.customers_default_address_id left join " . TABLE_ADMIN . " ad on ad.admin_id=c.admin_id where c.customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);

        if (!is_array($customers)) {
            die("Wrong customer data.");
        }

        $orders_query = tep_db_query("select count(*) as total_orders, max(o.date_purchased) as last_purchased, sum(ot.value) as total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." and ot.class='ot_total' and o.customers_id = ". $customers['customers_id']);
        $orders = tep_db_fetch_array($orders_query);
        if (!is_array($orders)) $orders = [];

        $info_query = tep_db_query("select customers_info_date_account_created as date_account_created, customers_info_date_account_last_modified as date_account_last_modified, customers_info_date_of_last_logon as date_last_logon, customers_info_number_of_logons as number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $customers['customers_id'] . "'");
        $info = tep_db_fetch_array($info_query);
        if (!is_array($info)) $info = [];

        $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $customers['entry_country_id'] . "' and language_id = '" . (int) $languages_id . "'");
        $country = tep_db_fetch_array($country_query);
        if (!is_array($country)) $country = [];

        $reviews_query = tep_db_query("select count(*) as number_of_reviews from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers['customers_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        if (!is_array($reviews)) $reviews = [];

        $customer_info = array_merge($country, $info, $reviews, $orders);
        $cInfo_array = array_merge($customers, $customer_info);
        $cInfo = new \objectInfo($cInfo_array);

        if ($messageStack->size > 0) {
            if ($HTTP_GET_VARS['read'] == 'only') {
            }else{
                echo $messageStack->output();
            }
        }

        echo '<div class="or_box_head">' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_TOTAL_ORDERED . '</div><div>'.$currencies->format($cInfo->total_sum).'</div></div>';
        echo '<div class="row_or"><div>' . TEXT_ORDER_COUNT . '</div><div>'.$cInfo->total_orders.'</div></div>';
        echo '<div class="row_or">
					<div>' . TEXT_DATE_ACCOUNT_CREATED . '</div>
					<div>' . \common\helpers\Date::date_short($cInfo->date_account_created) . '</div>
				</div>';
        /*echo '<div class="update_password">
          <div class="update_password_title">Update customers password:</div>
          <div class="update_password_content"><form name="passw_form" action="' . tep_href_link(FILENAME_CUSTOMERS, \common\helpers\Output::get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=password') . '" method="post" onsubmit="return check_passw_form('.(int)ENTRY_PASSWORD_MIN_LENGTH.');"><input type="hidden" name="cID" value="'.$cInfo->customers_id.'"><input type="text" name="change_pass" class="form-control" size="16" placeholder="New password"><input type="submit" value="Update Password" class="btn"></form></div>
        </div>';*/
        echo '<div class="row_or">
					<div>' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . '</div>
					<div>' . \common\helpers\Date::date_short($cInfo->date_account_last_modified) . '</div>
				</div>';
        echo '<div class="row_or">
					<div>' . TEXT_INFO_DATE_LAST_LOGON . '</div>
					<div>' . \common\helpers\Date::date_short($cInfo->date_last_logon) . '</div>
				</div>';
        echo '<div class="row_or">
					<div>' . TEXT_INFO_COUNTRY . '</div>
					<div>' . $cInfo->countries_name . '</div>
				</div>';
        echo '<div class="row_or">
					<div>' . TEXT_INFO_NUMBER_OF_LOGONS . '</div>
					<div>' . $cInfo->number_of_logons . '</div>
				</div>';
        echo '<div class="row_or">
					<div>' . TEXT_INFO_NUMBER_OF_REVIEWS . '</div>
					<div>' . $cInfo->number_of_reviews . '</div>
				</div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">
				<a href="' . \Yii::$app->urlManager->createUrl(['orders/create', 'Customer' => $cInfo->customers_id, 'back' => 'customers']) . '" class="btn btn-primary btn-process-order btn-process-order-cus">' . TEXT_CREATE_NEW_OREDER . '</a>
				<a class="btn btn-edit btn-no-margin" href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $cInfo->customers_id]) . '">' . IMAGE_EDIT . '</a>' . (!tep_session_is_registered('login_affiliate') ? '<button class="btn btn-delete" onclick="confirmDeleteCustomer(' . $cInfo->customers_id . ')">' . IMAGE_DELETE . '</button>' : '') . '<a class="btn btn-no-margin btn-ord-cus" href="' . \Yii::$app->urlManager->createUrl(['orders/', 'by' => 'cID', 'search' => $cInfo->customers_id]) . '">' . IMAGE_ORDERS . '</a><a class="btn btn-email-cus" href="mailto:' . $cInfo->customers_email_address . '">' . IMAGE_EMAIL . '</a><a href="' . \Yii::$app->urlManager->createUrl(['gv_mail/index', 'customer' => $cInfo->customers_email_address]) . '" class="btn btn-no-margin btn-coup-cus popup">'.T_SEND_COUPON.'</a>
				<a href="' . \Yii::$app->urlManager->createUrl(['customers/customermerge', 'customers_id' => $cInfo->customers_id]) . '" class="btn btn-no-margin btn-coup-cus">'.TEXT_MERGE_CUSTOMER.'</a>';
        if (\common\helpers\Acl::checkExtension('CustomerLoyalty', 'allowed')){
           echo '<a href="' . \yii\helpers\Url::to(['customers_loyalty/', 'cID' => $cInfo->customers_id, 'filter' => 'to_dispatch']) . '" class="btn btn-default btn-coup-cus">' . TEXT_VIEW_LOYALITY_POINTS . '</a>';
        }
        if (ENABLE_TRADE_FORM == 'True') {
            echo '<a href="' . \Yii::$app->urlManager->createUrl(['customers/customer-additional-fields', 'customers_id' => $cInfo->customers_id]) . '" class="btn btn-no-margin btn-coup-cus">' . TRADE_FORM . '</a>';
        }
                                echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order btn-toolbar-pass"><span class="btn btn-pass-cus">'.T_UPDATE_PASS.'</span>
                                <script>
                                $(document).ready(function() { 
                                $("a.popup").popUp();
                                $(".btn-pass-cus").on("click", function(){
                                    alertMessage("<div class=\"popup-heading popup-heading-pass\">' . TEXT_UPDATE_PASSWORD_FOR. ' '.$cInfo->customers_firstname.'&nbsp;'.$cInfo->customers_lastname.'</div><div class=\"popup-content popup-content-pass\"><form name=\"passw_form\" action=\"' . tep_href_link(FILENAME_CUSTOMERS, \common\helpers\Output::get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=password') . '\" method=\"post\" onsubmit=\"return check_passw_form('.(int)ENTRY_PASSWORD_MIN_LENGTH.');\"><label>'.T_NEW_PASS.':</label><input type=\"hidden\" name=\"cID\" value=\"'.$cInfo->customers_id.'\"><input type=\"password\" name=\"change_pass\" class=\"form-control\" size=\"16\"><div class=\"btn-bar\" style=\"padding-bottom: 0;\"><div class=\"btn-left\"><span class=\"btn btn-cancel\">' . IMAGE_CANCEL . '</span></div><div class=\"btn-right\"><input type=\"submit\" value=\"' . IMAGE_UPDATE. '\" class=\"btn btn-primary\"></div></div></form></div>");
                                });
                                });
                                </script>
                                </div>';


        //die();
        //$this->view->cInfo = $cInfo;
        //$this->render('customeractions');
    }

    public function actionCustomeredit() {

        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/customers');

        $currencies = new \common\classes\currencies();

        if (Yii::$app->request->isPost) {
            $customers_id = Yii::$app->request->post('customers_id');
        } else {
            $customers_id = Yii::$app->request->get('customers_id');
        }
        $customers_query = tep_db_query("select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, c.customers_alt_email_address, c.groups_id, c.opc_temp_account, c.customers_company, c.customers_company_vat, c.platform_id, c.erp_customer_id, c.erp_customer_code, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, a.entry_company_vat, c.customers_telephone, c.customers_landline, c.customers_alt_telephone, c.customers_cell, c.customers_status, c.customers_fax, c.customers_newsletter, c.customers_owc_member, c.customers_type_id, c.customers_bonus_points, c.customers_credit_avail, ad.individual_id, c.customers_default_address_id, c.credit_amount from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id left join " . TABLE_ADMIN . " ad on ad.admin_id=c.admin_id where c.customers_id = '" . (int) $customers_id . "' " . (tep_session_is_registered("login_affiliate") ? " and c.affiliate_id = '" . $login_id . "'" : ''));
        $customers = tep_db_fetch_array($customers_query);
        $cInfo = new \objectInfo($customers);
        if ($cInfo->erp_customer_id == 0) {
            $cInfo->erp_customer_id = '';
        }
        $cInfo->credit_amount = $currencies->format($customers['credit_amount']);
        $cInfo->credit_amount_mask = $currencies->format(0);

        $orders_query = tep_db_query("select count(*) as total_orders, max(o.date_purchased) as last_purchased, sum(ot.value) as total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." and ot.class='ot_total' and o.customers_id = ". (int) $customers_id);
        $orders = tep_db_fetch_array($orders_query);

        $cInfo->total_orders = $orders['total_orders'];
        $cInfo->last_purchased = \common\helpers\Date::date_short($orders['last_purchased']);
        $cInfo->last_purchased_days = \common\helpers\Date::getDateRange(date('Y-m-d'), $orders['last_purchased']);
        $cInfo->total_sum = $currencies->format($orders['total_sum']);

        $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where customers_id = '" . (int) $customers_id . "' ");

        $addresses = [];
        while ($d = tep_db_fetch_array($address_query)){
            $addresses[] = [
              'id' => $d['address_book_id'],
              'text' => $d['entry_suburb'] . ' ' . $d['entry_city'] . ' ' . $d['entry_state'] . ' ' . $d['entry_postcode'] . ' ' . $d['countries_name'],
              'entry_postcode' => $d['entry_postcode'],
              'entry_street_address' => $d['entry_street_address'],
              'entry_suburb' => $d['entry_suburb'],
              'entry_city' => $d['entry_city'],
              'entry_state' => $d['entry_state'],
              'entry_country_id' => $d['entry_country_id'],
              'is_default' => ($customers['customers_default_address_id'] == $d['address_book_id']),
            ];
        }

        if (count($addresses) < MAX_ADDRESS_BOOK_ENTRIES) {
            $addresses[] = [
              'id' => 0,
              'text' => IMAGE_NEW,
              'entry_postcode' => '',
              'entry_street_address' => '',
              'entry_suburb' => '',
              'entry_city' => '',
              'entry_state' => '',
              'entry_country_id' => '',
              'is_default' => false,
            ];

        }

        $str_full = strlen($cInfo->customers_firstname . '&nbsp;' . $cInfo->customers_lastname);
        if($str_full > 22){
            $st_full_name = mb_substr($cInfo->customers_firstname . '&nbsp;' . $cInfo->customers_lastname, 0, 22);
            $st_full_name .= '...';
            $st_full_name_view = '<span title="'.$cInfo->customers_firstname . '&nbsp;' . $cInfo->customers_lastname.'">' . $st_full_name . '</span>';
        }else{
            $st_full_name_view = $cInfo->customers_firstname . '&nbsp;' . $cInfo->customers_lastname;
        }


        $this->selectedMenu = array('customers', 'customers');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('customers/insert'), 'title' => T_EDITING_CUS . '&nbsp;"' . $st_full_name_view .'"');
        $this->view->headingTitle = T_EDITING_CUS;
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['customers/send-coupon', 'customers_id' => $cInfo->customers_id]).'" class="create_item popup"><i class="icon-ticket"></i>'.T_SEND_COUPON.'</a><a href="'.Yii::$app->urlManager->createUrl(['orders/create', 'customers_id' => $cInfo->customers_id, 'back' => 'orders']).'" class="create_item"><i class="icon-file-text"></i>'.TEXT_CREATE_NEW_OREDER.'</a>';

        $this->view->showGroup = (CUSTOMERS_GROUPS_ENABLE == 'True');
        if ($this->view->showGroup) {
            $groupStatusArray = [];
            $groupStatusArray[''] = '';
            $status_query = tep_db_query("select * from " . TABLE_GROUPS);
            while ($status = tep_db_fetch_array($status_query)){
                $groupStatusArray[$status['groups_id']] = $status['groups_name'];
            }
            $this->view->groupStatusArray = $groupStatusArray;
        }

        $guestStatusArray =[
          1 => TEXT_BTN_YES,
          0 => TEXT_BTN_NO,
        ];
        $this->view->guestStatusArray = $guestStatusArray;

        $this->view->showDOB = in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register']);
        $this->view->showState = in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register']);

        $platform_variants = array();
        foreach( \common\classes\platform::getList(false) as $_p ) { $platform_variants[$_p['id']] = $_p['text']; }

        return $this->render('edit', ['cInfo' => $cInfo, 'addresses' => $addresses, 'platforms' => $platform_variants]);
    }

    public function actionCustomersubmit() {

        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/customers');

        $error = false;
        $this->layout = false;

        $customers_password = Yii::$app->request->post('customers_password');

        $customers_id = (int)Yii::$app->request->post('customers_id');
        $customers_status = (int)Yii::$app->request->post('customers_status');
        $opc_temp_account = (int)Yii::$app->request->post('opc_temp_account');
        if (CUSTOMERS_GROUPS_ENABLE == 'True') {
            $groups_id = (int)$_POST['groups_id'];
        }
        if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_gender = Yii::$app->request->post('customers_gender');
        }
        if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_firstname = Yii::$app->request->post('customers_firstname');
        }
        if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_lastname = Yii::$app->request->post('customers_lastname');
        }
        if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_dob = Yii::$app->request->post('customers_dob');
        }
        if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_telephone = Yii::$app->request->post('customers_telephone');
        }
        if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $customers_landline = Yii::$app->request->post('customers_landline');
        }
        if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_company = Yii::$app->request->post('customers_company');
        }
        if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_company_vat = Yii::$app->request->post('customers_company_vat');
        }
        $customers_email_address = Yii::$app->request->post('customers_email_address');
        $default_address_id = Yii::$app->request->post('customers_default_address_id');
        //$customers_newsletter = tep_db_prepare_input($_POST['customers_newsletter']);
        $platform_id = intval(Yii::$app->request->post('platform_id',1));
        $individual_id = Yii::$app->request->post('individual_id');
        $erp_customer_id = (int)Yii::$app->request->post('erp_customer_id');
        $erp_customer_code = Yii::$app->request->post('erp_customer_code');
        $admin_id = 0;//$admin_id = $login_id;
        $res = tep_db_query(" select * from " . TABLE_ADMIN . " where individual_id like '" . tep_db_input($individual_id) . "'");
        if ($d = tep_db_fetch_array($res)) {
            $admin_id = $d['admin_id'];
        }

        if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_postcode = Yii::$app->request->post('entry_postcode');
        }
        if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_street_address = Yii::$app->request->post('entry_street_address');
        }
        if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_suburb = Yii::$app->request->post('entry_suburb');
        }
        if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_city = Yii::$app->request->post('entry_city');
        }
        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_state = Yii::$app->request->post('entry_state');
        }
        if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
            $entry_country_id = Yii::$app->request->post('entry_country_id');
        }
        $address_book_ids = Yii::$app->request->post('address_book_id');
        $entry_zone_id = [];

        $entry_post_code_error = false;
        $entry_street_address_error = false;
        $entry_city_error = false;
        $entry_country_error = false;
        $entry_state_error = false;

        foreach ($address_book_ids as $address_book_key => $address_book_id) {

            $skipAddress = false;

            if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                if (strlen($entry_postcode[$address_book_key]) < ENTRY_POSTCODE_MIN_LENGTH) {
                    if ($address_book_id > 0) {
                        //$error = true;
                        $entry_post_code_error = true;
                    }
                    $skipAddress = true;
                }
            }

            if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                if (strlen($entry_street_address[$address_book_key]) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                    if ($address_book_id > 0) {
                        //$error = true;
                        $entry_street_address_error = true;
                    }
                    $skipAddress = true;
                }
            }

            if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                if (strlen($entry_city[$address_book_key]) < ENTRY_CITY_MIN_LENGTH) {
                    if ($address_book_id > 0) {
                        //$error = true;
                        $entry_city_error = true;
                    }
                    $skipAddress = true;
                }
            }

            if (in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])) {
                if ((int)$entry_country_id[$address_book_key] == 0) {
                    $entry_country_id[$address_book_key] = STORE_COUNTRY;
                    /*if ($address_book_id > 0) {
                        $error = true;
                        $entry_country_error = true;
                    }
                    $skipAddress = true;*/
                }
            }

            if ($address_book_id == 0 && $skipAddress) {
                unset($address_book_ids[$address_book_key]);
                continue;
            }

            if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                if ($entry_country_error == true) {
                    //$entry_state_error = true;
                } else {
                    $entry_zone_id[$address_book_key] = 0;
                    //$entry_state_error = false;
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "'");
                    $check_value = tep_db_fetch_array($check_query);
                    $entry_state_has_zones = ($check_value['total'] > 0);
                    if ($entry_state_has_zones == true) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "' and (zone_name like '" . tep_db_input($entry_state[$address_book_key]) . "' or zone_code like '" . tep_db_input($entry_state[$address_book_key]) . "')");
                        if (tep_db_num_rows($zone_query) == 1) {
                            $zone_values = tep_db_fetch_array($zone_query);
                            $entry_zone_id[$address_book_key] = $zone_values['zone_id'];
                        } /*else {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    } else {

                        /*if ($entry_state[$address_book_key] == false) {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    }
                }
            }
        }


        /*if (isset($_POST['entry_zone_id']))
            $entry_zone_id = tep_db_prepare_input($_POST['entry_zone_id']);*/

        /*if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
            $error = true;
            $entry_firstname_error = true;
        } else {
            $entry_firstname_error = false;
        }*/
        /*if (ACCOUNT_COMPANY_VAT_ID == 'true') {
            if (!empty($entry_company_vat) and ( !\common\helpers\Validations::checkVAT($entry_company_vat))) {
                $error = true;
                $entry_company_vat_error = true;
            }
        }*/
        /*if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
            $error = true;
            $entry_lastname_error = true;
        } else {
            $entry_lastname_error = false;
        }*/

        if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $error = true;
            $entry_email_address_error = true;
        } else {
            $entry_email_address_error = false;
        }

        if (!\common\helpers\Validations::validate_email($customers_email_address)) {
            $error = true;
            $entry_email_address_check_error = true;
        } else {
            $entry_email_address_check_error = false;
        }


        /*if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
            $error = true;
            $entry_telephone_error = true;
        } else {
            $entry_telephone_error = false;
        }*/

        $data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'"));

        $check_email = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "' and customers_id != '" . (int) $customers_id . "' and opc_temp_account = '0'");
        if (tep_db_num_rows($check_email)) {
            if ($opc_temp_account == 0) {
                $error = true;
                $entry_email_address_exists = true;
            } else {
                $entry_email_address_exists = true;
            }
        } else {
            $entry_email_address_exists = false;
        }

        if ($error == false) {

            $sql_data_array = [
              'customers_email_address' => $customers_email_address,
              'groups_id' => $groups_id,
              'admin_id' => $admin_id,
              'customers_status' => $customers_status,
                //'customers_newsletter' => $customers_newsletter
              'platform_id' => $platform_id,
              'erp_customer_id' => $erp_customer_id,
              'erp_customer_code' => $erp_customer_code,
              'opc_temp_account' => $opc_temp_account,
            ];

            if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_gender'] = $customers_gender;
            }
            if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_firstname'] = $customers_firstname;
            }
            if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_lastname'] = $customers_lastname;
            }
            if (STRLEN($customers_password) > 0) {
                $sql_data_array['customers_password'] = \common\helpers\Password::encrypt_password($customers_password);
            }
            if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register']) && !empty($customers_dob)) {
                $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $customers_dob);
                $sql_data_array['customers_dob'] = $date->format('Y-m-d');
            }
            if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_telephone'] = $customers_telephone;
            }
            if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_landline'] = $customers_landline;
            }
            if (in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_company'] = $entry_company;
            }
            if (in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])) {
                $sql_data_array['customers_company_vat'] = $entry_company_vat;
            }

            if ($customers_id > 0) {
                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . $customers_id . "'");
                tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . $customers_id . "'");
            } else {
                tep_db_perform(TABLE_CUSTOMERS, array_merge($sql_data_array, array('customers_default_address_id' => 1)));
                $customers_id = tep_db_insert_id();
                tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_created = now(), customers_info_date_account_last_modified = now(), customers_info_id = '" . $customers_id . "'");
            }

            $activeaddress_book_ids = [];
            foreach ($address_book_ids as $address_book_key => $address_book_id) {

                if ($entry_zone_id[$address_book_key] > 0)
                    $entry_state[$address_book_key] = '';

                $sql_data_array = [
                  'entry_country_id' => $entry_country_id[$address_book_key]
                ];

                if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_gender'] = $customers_gender;
                }
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_firstname'] = $customers_firstname;
                }
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_lastname'] = $customers_lastname;
                }
                if (in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_postcode'] = $entry_postcode[$address_book_key];
                }
                if (in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_street_address'] = $entry_street_address[$address_book_key];
                }
                if (in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_suburb'] = $entry_suburb[$address_book_key];
                }
                if (in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $sql_data_array['entry_city'] = $entry_city[$address_book_key];
                }
                if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    if ($entry_zone_id[$address_book_key] > 0) {
                        $sql_data_array['entry_zone_id'] = $entry_zone_id[$address_book_key];
                        $sql_data_array['entry_state'] = '';
                    } else {
                        $sql_data_array['entry_zone_id'] = '0';
                        $sql_data_array['entry_state'] = $entry_state[$address_book_key];
                    }
                }
                if ((int)$address_book_id > 0) {
                    tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int) $customers_id . "' and address_book_id = '" . (int) $address_book_id . "'");
                    $activeaddress_book_ids[] = $address_book_id;
                } else {
                    tep_db_perform(TABLE_ADDRESS_BOOK, array_merge($sql_data_array, array('customers_id' => $customers_id)));
                    $new_customers_address_id = tep_db_insert_id();
                    $activeaddress_book_ids[] = $new_customers_address_id;
                }
            }

            if (count($activeaddress_book_ids) > 0) {
                tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . $customers_id . "' and address_book_id NOT IN (" . implode(", ", $activeaddress_book_ids) . ")");
            }

            if ($default_address_id == 0) {
                $default_address_id = $new_customers_address_id;
            }
            if ($default_address_id > 0) {
                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id='" . (int) $default_address_id . "' where customers_id = '" . $customers_id . "'");
            }

            $credit_amount = number_format(floatval(tep_db_prepare_input($_POST['credit_amount'])),5,'.','');
            if ($credit_amount > 0) {
                if(!is_object($currencies)){
                    $currencies = new \common\classes\currencies();
                }

                $credit_prefix = tep_db_prepare_input($_POST['credit_prefix']);
                $comments = tep_db_prepare_input($_POST['comments']);
                $customer_notified = '0';
                if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
                    $customer_notified = '1';
                    $emailSubject = EMAIL_TEXT_SUBJECT;
                    $emailContent = sprintf(EMAIL_TEXT_UPDATE, $credit_prefix . $currencies->format($credit_amount, true, DEFAULT_CURRENCY, $currencies->currencies[DEFAULT_CURRENCY]['value']));
                    $emailContent .= sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
                    \common\helpers\Mail::send($data['customers_firstname'] . ' ' . $data['customers_lastname'], $data['customers_email_address'], $emailSubject, $emailContent, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                }
                $sql_data_array = [
                  'customers_id' => $customers_id,
                  'credit_prefix' => $credit_prefix,
                  'credit_amount' => $credit_amount,
                  'currency' => DEFAULT_CURRENCY,
                  'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
                  'customer_notified' => $customer_notified,
                  'comments' => $comments,
                  'date_added' => 'now()',
                  'admin_id' => $login_id,
                ];
                tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, $sql_data_array);
                tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = credit_amount " . $credit_prefix . " " . $credit_amount . " where customers_id =" . (int) $customers_id);
            }

            if ($TrustpilotClass = Acl::checkExtension('Trustpilot', 'onCustomerUpdate')) {
                $TrustpilotClass::onCustomerUpdate((int)$customers_id);
            }


            $messageType = 'success';
            $message = SUCCESS_CUSTOMERUPDATED;

            if ($entry_post_code_error == true) {
                $message .= '<br>' . sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH);
            }
            if ($entry_street_address_error == true) {
                $message .= '<br>' . sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH);
            }
            if ($entry_city_error == true) {
                $message .= '<br>' . sprintf(ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH);
            }
            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                            <?= $message ?>
                        </div>
                    </div>
                    <div class="noti-btn">
                        <div></div>
                        <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                    </div>
                </div>
                <script>
                    $('body').scrollTop(0);
                    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                        $(this).parents('.pop-mess').remove();
                    });
                </script>
            </div>

            <?php
            if (isset($_GET['redirect'])) {
                switch ($_GET['redirect']) {
                    case 'neworder':
                        echo '<script> window.location.href="'. Yii::$app->urlManager->createUrl(['orders/create', 'Customer' => $customers_id]) .'";</script>';
                        die();
                        break;
                    case 'customeredit':
                        echo '<script> window.location.href="'. Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $customers_id]) .'";</script>';
                        die();
                        break;
                }
            }
            ?>
            <script type="text/javascript">
                $("div.top_bead").html('<h1><?php echo T_EDITING_CUS . '&nbsp;"' . $customers_firstname . '&nbsp;' . $customers_lastname .'"' ?></h1><?php echo '<a href="'.Yii::$app->urlManager->createUrl(['customers/send-coupon', 'customers_id' => $cInfo->customers_id]).'" class="create_item popup"><i class="icon-ticket"></i>'.T_SEND_COUPON.'</a>'; ?>');
            </script>
            <?php

        } else if ($error == true) {
            $message = 'Cant save customer.';

            if ($entry_email_address_check_error == true) {
                $message .= '<br>' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
            }
            if ($entry_email_address_error == true) {
                $message .= '<br>' . sprintf(ENTRY_EMAIL_ADDRESS_ERROR, ENTRY_EMAIL_ADDRESS_MIN_LENGTH);
            }
            if ($entry_email_address_exists == true) {
                $message .= '<br>' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
            }
            if ($entry_post_code_error == true) {
                $message .= '<br>' . sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH);
            }
            if ($entry_street_address_error == true) {
                $message .= '<br>' . sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH);
            }
            if ($entry_city_error == true) {
                $message .= '<br>' . sprintf(ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH);
            }
            if ($entry_country_error == true) {
                $message .= '<br>' . ENTRY_COUNTRY_ERROR;
            }
            /*if ($entry_state_error == true) {
                $message .= '<br>' . ENTRY_STATE_ERROR;
            }*/
            if ($entry_firstname_error == true) {
                $message .= '<br>' . sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH);
            }
            if ($entry_lastname_error == true) {
                $message .= '<br>' . sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH);
            }

            ?>
            <div class="alert fade in alert-danger" style="display: block;">
                <i class="icon-remove close" data-dismiss="alert"></i>
                <?= $message?>
            </div>
            <?php
            if (isset($_GET['redirect'])) {
                die();
            }
        }

        return $this->actionCustomeredit();
    }

    public function actionCustomerdelete() {

        $this->layout = false;

        $customers_id = Yii::$app->request->post('customers_id');

        if (isset($_POST['delete_reviews']) && ($_POST['delete_reviews'] == 'on')) {
            $reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers_id . "'");
            while ($reviews = tep_db_fetch_array($reviews_query)) {
                tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int) $reviews['reviews_id'] . "'");
            }

            tep_db_query("delete from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers_id . "'");
        } else {
            tep_db_query("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . (int) $customers_id . "'");
        }

        tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int) $customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customers_id . "'");
        tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int) $customers_id . "'");
    }

    public function actionCustomersdelete() {

        $this->layout = false;

        $selected_ids = Yii::$app->request->post('selected_ids');

        foreach ($selected_ids as $customers_id) {
            if (isset($_POST['delete_reviews']) && ($_POST['delete_reviews'] == 'on')) {
                $reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers_id . "'");
                while ($reviews = tep_db_fetch_array($reviews_query)) {
                    tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int) $reviews['reviews_id'] . "'");
                }

                tep_db_query("delete from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers_id . "'");
            } else {
                tep_db_query("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . (int) $customers_id . "'");
            }

            tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customers_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int) $customers_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customers_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $customers_id . "'");
            tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int) $customers_id . "'");
        }
    }

    public function actionConfirmcustomerdelete() {

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/customers');

        $this->layout = false;

        $customers_id = Yii::$app->request->post('customers_id');

        $customers_query = tep_db_query("select distinct(c.customers_id), c.last_xml_export, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.groups_id, a.entry_country_id, c.admin_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on  a.address_book_id = c.customers_default_address_id left join " . TABLE_ADMIN . " ad on ad.admin_id=c.admin_id where c.customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);

        if (!is_array($customers)) {
            die("Wrong customer data.");
        }

        $info_query = tep_db_query("select customers_info_date_account_created as date_account_created, customers_info_date_account_last_modified as date_account_last_modified, customers_info_date_of_last_logon as date_last_logon, customers_info_number_of_logons as number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $customers['customers_id'] . "'");
        $info = tep_db_fetch_array($info_query);

        $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $customers['entry_country_id'] . "' and language_id = '" . (int) $languages_id . "'");
        $country = tep_db_fetch_array($country_query);

        $reviews_query = tep_db_query("select count(*) as number_of_reviews from " . TABLE_REVIEWS . " where customers_id = '" . (int) $customers['customers_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);

        $customer_info = array_merge($country, $info, $reviews);
        $cInfo_array = array_merge($customers, $customer_info);
        $cInfo = new \objectInfo($cInfo_array);

        $heading = array();
        $contents = array();

        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</b>');

        $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
        if (isset($cInfo->number_of_reviews) && ($cInfo->number_of_reviews) > 0) {
            $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
        }
        //$contents[] = array('align' => 'center', 'text' => '<br><a href="#" onclick="deleteCustomer(' . $cInfo->customers_id . ');return false">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="#" onclick="resetStatement(' . $cInfo->customers_id . ');return false">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');


        echo tep_draw_form('customers', FILENAME_CUSTOMERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="customers_edit" onSubmit="return deleteCustomer();"');

        $box = new \box;
        echo $box->infoBox($heading, $contents);
        ?>
        <p class="btn-toolbar">
            <?php
            echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

            echo tep_draw_hidden_field('customers_id', $cInfo->customers_id);
            ?>
        </p>
        </form>
        <?php
    }

    public function actionGeneratepassword(){
        global $messageStack;
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/customers');

        $customers_id = tep_db_prepare_input($_POST['cID']);
        $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id ,customers_email_address, platform_id from " . TABLE_CUSTOMERS . " where customers_id = '" . $customers_id . "'");
        if (tep_db_num_rows($check_customer_query)) {
            $check_customer = tep_db_fetch_array($check_customer_query);
            if (trim($_POST['change_pass']) == '') {
                $new_password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
            } else {
                $new_password = $_POST['change_pass'];
            }
            $crypted_password = \common\helpers\Password::encrypt_password($new_password);
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");

            $platform_config = Yii::$app->get('platform')->config($check_customer['platform_id']);

            $eMail_store = $platform_config->const_value('STORE_NAME');
            $eMail_address = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $eMail_store_owner = $platform_config->const_value('STORE_OWNER');

            $email_text = sprintf(TEXT_EMAIL_ACCOUNT_UPDATE, $check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], HTTP_CATALOG_SERVER . DIR_WS_CATALOG, $new_password, $eMail_store);
            \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $check_customer['customers_email_address'], sprintf(EMAIL_SUBJECT_ACCOUNT_UPDATE, $eMail_store), $email_text, $eMail_store_owner, $eMail_address);
            $messageStack->add_session(PASSWORD_SENT_MESSAGE, 'success');

        }
        //$this->redirect(array('customers/customeractions', 'customers_id'=>  $customers_id));
        echo json_encode(array('customers_id'=>  $customers_id));

    }

    public function actionSendCoupon() {
        global $language, $messageStack;
        $this->layout = false;
        if (Yii::$app->request->isPost){
            $customers_id = Yii::$app->request->post('customers_id', 0);
        } else {
            $customers_id = Yii::$app->request->get('customers_id', 0);
        }

        if ($customers_id){

            \common\helpers\Translation::init('admin/coupon_admin');

            $customers_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADMIN . " ad on ad.admin_id=c.admin_id where c.customers_id = '" . (int) $customers_id . "' " . (tep_session_is_registered("login_affiliate") ? " and c.affiliate_id = '" . $login_id . "'" : ''));
            $customers = tep_db_fetch_array($customers_query);
            if (Yii::$app->request->isPost){

                $email_text = TEXT_VOUCHER_IS . ' ' . $_POST['coupon_code'] ."\n" .
                  TEXT_TO_REDEEM . "\n" .
                  TEXT_REMEMBER ."\n";

                if (tep_not_null($_POST['coupon_message'])){
                    $email_text .= "\n" . strip_tags($_POST['coupon_message']);
                }
                $subject = (tep_not_null($_POST['coupon_subject']) ? $_POST['coupon_subject'] : sprintf(TEXT_SUBJECT_CODE, STORE_NAME));

                \common\helpers\Mail::send($customers['customers_firstname'] . ' ' . $customers['customers_lastname'], $customers['customers_email_address'], $subject , $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

                $messageStack->add_session(MESSAGE_COUPON_SENT, 'success');

                echo json_encode(array('customers_id'=>  $customers_id));

                exit();
            }
        }

        return $this->render('send-coupon.tpl', ['customers' => $customers]);
    }

    /**
     * Autocomplette
     */
    public function actionGroup() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "groups_name like '%" . tep_db_input($term) . "%'";
        }

        $groups = array();
        $groups_query = tep_db_query("select groups_name  from " . TABLE_GROUPS . " where " . $search . " group by groups_name order by groups_name");
        while ($response = tep_db_fetch_array($groups_query)) {
            $groups[] = $response['groups_name'];
        }
        echo json_encode($groups);
    }

    public function actionCountries() {
        global $languages_id;
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "c.countries_name like '%" . tep_db_input($term) . "%'";
        }

        $countries = array();
        $address_query = tep_db_query("select c.countries_name as country from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where " . $search . " group by c.countries_name order by c.countries_name");
        while ($response = tep_db_fetch_array($address_query)) {
            if (!empty($response['country'])) {
                $countries[] = $response['country'];
            }
        }
        echo json_encode($countries);
    }

    public function actionState() {
        global $languages_id;
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $search = "1";
        if (!empty($country)) {
            $search = "c.countries_name like '%" . tep_db_input($country) . "%'";
        }
        if (!empty($term)) {
            $search .= " and (ab.entry_state like '%" . tep_db_input($term) . "%' or z.zone_name like '%" . tep_db_input($term) . "%')";
        }

        $states = array();
        $address_query = tep_db_query("select if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where " . $search . " group by state order by state");
        while ($response = tep_db_fetch_array($address_query)) {
            if (!empty($response['state'])) {
                $states[] = $response['state'];
            }
        }
        echo json_encode($states);
    }

    public function actionCity() {
        global $languages_id;

        global $languages_id;
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));
        $state = tep_db_prepare_input(Yii::$app->request->get('state'));

        $search = "1";
        if (!empty($country)) {
            $search = "c.countries_name like '%" . tep_db_input($country) . "%'";
        }
        if (!empty($state)) {
            $search .= " and (ab.entry_state like '%" . tep_db_input($state) . "%' or z.zone_name like '%" . tep_db_input($state) . "%')";
        }
        if (!empty($term)) {
            $search = "ab.entry_city like '%" . tep_db_input($term) . "%'";
        }

        $cities = array();
        $address_query = tep_db_query("select ab.entry_city as city from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where " . $search . " group by city order by city");
        while ($response = tep_db_fetch_array($address_query)) {
            if (!empty($response['city'])) {
                $cities[] = $response['city'];
            }
        }

        echo json_encode($cities);
    }

    public function actionCompany() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "entry_company like '%" . tep_db_input($term) . "%'";
        }

        $companies = array();
        $address_query = tep_db_query("select entry_company from " . TABLE_ADDRESS_BOOK . " where " . $search . " group by entry_company order by entry_company");
        while ($response = tep_db_fetch_array($address_query)) {
            if (!empty($response['entry_company'])) {
                $companies[] = $response['entry_company'];
            }
        }
        echo json_encode($companies);
    }

    public function actionStates() {
        global $languages_id;
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = (int) Yii::$app->request->get('country');

        $search = "1";
        if ($country > 0) {
            $search = "zone_country_id = '" . $country . "'";
        }
        if (!empty($term)) {
            $search .= " and zone_name like '%" . tep_db_input($term) . "%'";
        }

        $states = array();
        $address_query = tep_db_query("SELECT zone_name FROM " . TABLE_ZONES . " where " . $search . " group by zone_name order by zone_name");
        while ($response = tep_db_fetch_array($address_query)) {
            if (!empty($response['zone_name'])) {
                $states[] = $response['zone_name'];
            }
        }
        echo json_encode($states);
    }
    public function actionCredithistory() {
        global $languages_id, $language;

        $customers_id = (int)Yii::$app->request->get('customers_id');

        $this->view->headingTitle = HEADING_TITLE;
        \common\helpers\Translation::init('admin/customers');

        $this->layout = false;

        $currencies = new \common\classes\currencies();

        $history = [];
        $customer_history_query = tep_db_query("select * from " . TABLE_CUSTOMERS_CREDIT_HISTORY . " where customers_id='" . $customers_id . "' order by customers_credit_history_id");
        while ($customer_history = tep_db_fetch_array($customer_history_query)) {
            $admin = '';
            if ($customer_history['admin_id'] > 0) {
                $check_admin_query = tep_db_query( "select * from " . TABLE_ADMIN . " where admin_id = '" . (int)$customer_history['admin_id'] . "'" );
                $check_admin = tep_db_fetch_array( $check_admin_query );
                if (is_array($check_admin)) {
                    $admin =  $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                }
            }
            $history[] = [
              'date' => \common\helpers\Date::datetime_short($customer_history['date_added']),
              'credit' => $customer_history['credit_prefix'] . $currencies->format($customer_history['credit_amount'], true, $customer_history['currency'], $customer_history['currency_value']),
              'notified' => $customer_history['customer_notified'],
              'comments' => $customer_history['comments'],
              'admin' => $admin,
            ];
        }

        return $this->render('credithistory', ['history' => $history]);

    }
    public function actionCustomermerge() {
        global $languages_id, $language;

        $this->view->headingTitle = HEADING_TITLE_MERGE;
        \common\helpers\Translation::init('admin/customers');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('customers/index'), 'title' => HEADING_TITLE_MERGE);
        $this->selectedMenu = array('customers', 'customers');

        $customers_id = Yii::$app->request->get('customers_id');

        $customers_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        //$customers_query = tep_db_query("select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, c.customers_alt_email_address, c.groups_id, c.customers_company, c.customers_company_vat, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, a.entry_company_vat, c.customers_telephone, c.customers_landline, c.customers_alt_telephone, c.customers_cell, c.customers_status, c.customers_fax, c.customers_newsletter, c.customers_owc_member, c.customers_type_id, c.customers_bonus_points, c.customers_credit_avail, ad.individual_id, c.customers_default_address_id, c.credit_amount from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id left join " . TABLE_ADMIN . " ad on ad.admin_id=c.admin_id where c.customers_id = '" . (int) $customers_id . "' " . (tep_session_is_registered("login_affiliate") ? " and c.affiliate_id = '" . $login_id . "'" : ''));
        $customers = tep_db_fetch_array($customers_query);
        if (!is_array($customers)) {
            return $this->redirect(array('customers/'));
        }
        $cInfo = new \objectInfo($customers);

        $defaultAddress = [];
        $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where customers_id = '" . (int) $customers_id . "' ");
        $addresses = [];
        while ($d = tep_db_fetch_array($address_query)){
            if (($customers['customers_default_address_id'] == $d['address_book_id'])) {
                $defaultAddress = [
                  'id' => $d['address_book_id'],
                  'text' => $d['entry_suburb'] . ' ' . $d['entry_city'] . ' ' . $d['entry_state'] . ' ' . $d['entry_postcode'] . ' ' . $d['countries_name'],
                ];
            } else {
                $addresses[] = [
                  'id' => $d['address_book_id'],
                  'text' => $d['entry_suburb'] . ' ' . $d['entry_city'] . ' ' . $d['entry_state'] . ' ' . $d['entry_postcode'] . ' ' . $d['countries_name'],
                ];
            }
        }

        return $this->render('customermerge', ['cInfo' => $cInfo, 'defaultAddress' => $defaultAddress, 'addresses' => $addresses]);

    }

    public function actionCustomerMergeInfo() {
        global $languages_id;
        $this->layout = false;

        $customers_id = Yii::$app->request->post('customers_id');
        $sacrifice_id = Yii::$app->request->post('sacrifice_id');

        if ($customers_id == $sacrifice_id) {
            echo "Can not be merged with itself.";//TODO add to translate
            exit();
        }

        $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where customers_id = '" . (int) $sacrifice_id . "' ");
        $addresses = [];
        while ($d = tep_db_fetch_array($address_query)){
            $addresses[] = [
              'id' => $d['address_book_id'],
              'text' => $d['entry_suburb'] . ' ' . $d['entry_city'] . ' ' . $d['entry_state'] . ' ' . $d['entry_postcode'] . ' ' . $d['countries_name'],
            ];
        }
        return $this->render('customer-merge-info', ['addresses' => $addresses]);
    }

    public function actionDoCustomerMerge() {
        \common\helpers\Translation::init('admin/customers');

        $customers_id = Yii::$app->request->post('customers_id');
        $sacrifice_id = Yii::$app->request->post('sacrifice_id');

        $address_id = Yii::$app->request->post('address_id');
        if (!is_array($address_id)) {
            $address_id = [];
        }
        $sacrifice_address_id = Yii::$app->request->post('sacrifice_address_id');
        if (!is_array($sacrifice_address_id)) {
            $sacrifice_address_id = [];
        }

        $messageType = 'success';
        $message = SUCCESS_CUSTOMERUPDATED;

        $customers_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);
        if (is_array($customers) && $customers_id != $sacrifice_id && $sacrifice_id > 0) {
            $address_id[] = $customers['customers_default_address_id'];
            tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customers_id . "' AND address_book_id NOT IN (" . implode(", ", $address_id) . ")");
            if (count($sacrifice_address_id) > 0) {
                tep_db_query("update " . TABLE_ADDRESS_BOOK . " set customers_id = '" . (int) $customers_id . "' where customers_id = '" . (int) $sacrifice_id . "' AND address_book_id IN (" . implode(", ", $sacrifice_address_id) . ");");
            }

            tep_db_query("update " . TABLE_ORDERS . " set customers_id = '" . (int) $customers_id . "', customers_name = '" . $customers['customers_firstname'] . ' ' . $customers['customers_lastname'] . "', customers_firstname = '" . $customers['customers_firstname'] . "', customers_lastname = '" . $customers['customers_lastname'] . "', customers_email_address = '" . $customers['customers_email_address'] . "' where customers_id = '" . (int) $sacrifice_id . "';");
            tep_db_query("update " . TABLE_REVIEWS . " set customers_id = '" . (int) $customers_id . "' where customers_id = '" . (int) $sacrifice_id . "'");
            tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $sacrifice_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $sacrifice_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int) $sacrifice_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $sacrifice_id . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $sacrifice_id . "'");
            $customers_query = tep_db_query("select session_id from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int) $sacrifice_id . "'");
            while ($customers = tep_db_fetch_array($customers_query)) {
                tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" . $customers['session_id'] . "'");
            }
            tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int) $sacrifice_id . "'");
        }

        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                        <?= $message ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
            </div>
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>
        <?php
        echo '<script> window.location.href="'. Yii::$app->urlManager->createUrl(['customers/customermerge', 'customers_id' => $customers_id]) .'";</script>';
    }

    public function actionCustomerAdditionalFields() {
        \common\helpers\Translation::init('admin/customers');

        $this->selectedMenu = array('customers', 'customers');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('customers/index'), 'title' => TRADE_FORM);

        $customers_id = intval(Yii::$app->request->get('customers_id'));

        $additionalFields = \common\helpers\Customer::get_additional_fields_tree($customers_id);
        $addresses = \common\helpers\Customer::get_address_book_data($customers_id);

        $customer = tep_db_fetch_array(tep_db_query("select customers_firstname, customers_lastname, platform_id, customers_email_address, customers_telephone, customers_company, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . $customers_id . "'"));


        foreach ($addresses as $key => $item){
            //$addresses[$key]['address'] = $item['street_address'] . ($item['street_address'] ? ', ' : '') . $item['city'] . ($item['city'] ? ', ' : '') . $item['state'] . ($item['state'] ? ', ' : '') . $item['suburb'] . ($item['suburb'] ? ', ' : '') . $item['country'];
            
            $addresses[$key]['address'] = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($item['country_id']), $item, 1, ' ', ',');

        }
        
        $countries = \common\helpers\Country::get_countries();
        $fields = array();
        foreach ($additionalFields as $group){
            foreach ($group['child'] as $item){
                $item['group'] = $group['name'];
                $fields[$item['code']] = $item;
            }
        }
        return $this->render('customer-additional-fields', [
          'customers_id' => $customers_id,
          'additionalFields' => $additionalFields,
          'addresses' => $addresses,
          'customer' => $customer,
          'fields' => $fields,
          'countries' => $countries
        ]);
    }

    public function actionCustomerAdditionalFieldsSubmit() {
        $customers_id = Yii::$app->request->post('customers_id');
        $fields = tep_db_prepare_input(Yii::$app->request->post('field'));

        \common\helpers\Translation::init('admin/customers');
        $messageType = 'success';
        $message = SUCCESS_CUSTOMERUPDATED;

        foreach ($fields as $id => $value) {
            $check_query = tep_db_query("SELECT value FROM " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " WHERE customers_id = '" . (int)$customers_id . "' AND additional_fields_id = '" . (int)$id . "'");
            if (tep_db_num_rows($check_query) > 0) {
                tep_db_query("update " . TABLE_CUSTOMERS_ADDITIONAL_FIELDS . " set value = '" . tep_db_input($value) . "' where customers_id = '" . (int)$customers_id . "' AND additional_fields_id = '" . (int)$id . "'");
            } else {
                $sql_data_array = [
                  'additional_fields_id' => (int) $id,
                  'customers_id' => (int) $customers_id,
                  'value' => $value,
                ];
                tep_db_perform(TABLE_CUSTOMERS_ADDITIONAL_FIELDS, $sql_data_array);
            }
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                        <?= $message ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
            </div>
            <script>
                //$('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
                $('.popup-box-wrap.pop-mess').css('top', $(window).scrollTop() + 200);
                setTimeout(function(){
                    $('.popup-box-wrap.pop-mess').remove();
                }, 1000)
            </script>
        </div>
        <?php
        //echo '<script> window.location.href="'. Yii::$app->urlManager->createUrl(['customers/customer-additional-fields', 'customers_id' => $customers_id]) .'";</script>';

    }

    public function cutStr($str, $len = 70, $spacer = ' '){

        $arr = array();
        if (strlen($str) > $len) {
            $n = stripos($str, $spacer, $len);
            if ($n !== false) {
                $arr[] = ltrim(substr($str, 0, $n+1));
                $str = ltrim(substr($str, $n+1));
                $arr = array_merge($arr, $this->cutStr($str, $len, $spacer));
            } else {
                $arr[] = $str;
            }
        } else {
            $arr[] = $str;
        }
        return $arr;
    }

    public function actionTradeAcc() {

        \common\helpers\Translation::init('admin/customers');
        $get = Yii::$app->request->get();
        $additionalFields = \common\helpers\Customer::get_additional_fields($get['customers_id']);

        $customer = tep_db_fetch_array(tep_db_query("select customers_firstname, customers_lastname, platform_id, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . $get['customers_id'] . "'"));

        $platform = tep_db_fetch_array(tep_db_query("
            select 
                p.platform_owner, 
                p.platform_name, 
                p.platform_telephone, 
                p.platform_landline, 
                p.platform_email_address,
                a.entry_street_address,
                a.entry_suburb,
                a.entry_city,
                a.entry_postcode
            from " . TABLE_PLATFORMS . " p, " . TABLE_PLATFORMS_ADDRESS_BOOK . " a 
            where p.is_default = 1 and a.platform_id = p.platform_id and a.is_default
            "));

        $padding_right = 8.5;
        $padding_left = 8.5;
        $width = 210 - $padding_right - $padding_left;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Holbi');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins($padding_left, 9.5, $padding_right);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();


        $pdf->SetFont('promptblacki', '', '25');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell($width/2 + 2, 11, $platform['platform_name'], 0, 'L', 1, 0);


        $pdf->SetFont('promptblacki', '', '17');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(5,3,0,0);
        $pdf->MultiCell($width/2, 11, 'New Account Form', 0, 'L', 1, 1);

        $text = $platform['entry_street_address'] . ', ' . $platform['entry_suburb'] . ',
' . $platform['entry_city'] . ', ' . $platform['entry_postcode'] . '
Tel: ' . $platform['platform_telephone'] . ($platform['platform_landline'] ? ' Fax: ' : '') . $platform['platform_landline'] . '
E-mail: ' . $platform['platform_email_address'];

        $pdf->SetFont('prompt', '', '10');
        $pdf->SetTextColor(0,0,0);
        $pdf->setCellPaddings(2,1,0,5);
        $pdf->MultiCell('', '', $text, 0, 'L', 0, 1);

        $pdf->SetFont('promptblacki', '', '15');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell('', '', 'Customer Details', 0, 'L', 1, 1);


        $pdf->SetFont('prompt', '', '11.1');
        $pdf->SetTextColor(0,0,0);

        $pdf->setCellPaddings(0,1,1,5);
        $pdf->MultiCell(99, '', ' ', 0, 'L', 0, 0);

        $pdf->setCellPaddings(0,1,1,5);
        $pdf->MultiCell(40, '', 'Limited Company', 0, 'L', 0, 0);
        if ($additionalFields['limited_company']) {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 6.5, '', '', '', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 6.5, '', '', '', '', 300, '', false, false, 0, 'LB');
        }

        $pdf->setCellPaddings(17,1,0,5);
        $pdf->MultiCell(46, '', 'Sole Trader', 0, 'L', 0, 0);
        if ($additionalFields['sole_trader']) {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 6.5, '', '', 'N', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 6.5, '', '', 'N', '', 300, '', false, false, 0, 'LB');
        }

        $pdf->setCellMargins(0,2,0);
        $pdf->setCellPaddings(3,0,0,0);
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Business Name', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['name'], 'B', 'L', 0, 1);

        $pdf->setCellMargins(0,0.2,0);

        $data = [
            'street_address' => $additionalFields['street_address'],
            'suburb' => $additionalFields['suburb'],
            'city' => $additionalFields['city'],
            'state' => $additionalFields['state'],
            'country_id' => $additionalFields['country']
        ];
        $address = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($additionalFields['country']), $data, 1, ' ', ',');

        $address_arr = $this->cutStr($address, 70, ' ');

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

        if (count($address_arr) > 1){
            for($i = 1; $i < count($address_arr); $i++){
                $pdf->SetFont('prompt', '', '11.1');
                $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                $pdf->SetFont('courgette', '', '11.6');
                $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
            }
        }

        /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Postcode', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['postcode'], 'B', 'L', 0, 1);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Telephone No.', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell(($width-70)/2, '', $additionalFields['phone'], 'B', 'L', 0, 0);
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(25, '', 'Fax No.', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell(($width-70)/2, '', $additionalFields['fax'], 'B', 'L', 0, 1);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'e-mail address', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $customer['customers_email_address'], 'B', 'L', 0, 1);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Nature of Business', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['nature_business'], 'B', 'L', 0, 1);

        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Owner’s Name', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['owners_firstname'] . ' ' . $additionalFields['owners_lastname'], 'B', 'L', 0, 1);

        $pdf->setCellMargins(0,0.2,0);

        $data = [
          'street_address' => $additionalFields['owners_street_address'],
          'suburb' => $additionalFields['owners_suburb'],
          'city' => $additionalFields['owners_city'],
          'state' => $additionalFields['owners_state'],
          'country_id' => $additionalFields['owners_country']
        ];
        $address = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($additionalFields['country']), $data, 1, ' ', ',');

        $address_arr = $this->cutStr($address, 70, ' ');

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

        if (count($address_arr) > 1){
            for($i = 1; $i < count($address_arr); $i++){
                $pdf->SetFont('prompt', '', '11.1');
                $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                $pdf->SetFont('courgette', '', '11.6');
                $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
            }
        }

        /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Postcode', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['owners_postcode'], 'B', 'L', 0, 1);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Telephone No.', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['owners_phone'], 'B', 'L', 0, 1);


        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('promptblacki', '', '15');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell('', '', 'Discount', 0, 'L', 1, 1);


        $pdf->SetFont('prompt', '', '10');
        $pdf->SetTextColor(0,0,0);

        $pdf->setCellMargins(0,2,0);
        $pdf->setCellPaddings(0,1,3,5);
        $pdf->MultiCell(45, '', '33.33% Sale or Return', 0, 'R', 0, 0);
        if ($additionalFields['sale_return']) {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot') . '/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        }

        $pdf->MultiCell(32, '', '35% Firm', 0, 'R', 0, 0);
        if ($additionalFields['firm']) {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        }

        $pdf->MultiCell(56, '', '38.25% Cash With Order', 0, 'R', 0, 0);
        if ($additionalFields['cash_with_order']) {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', '', '', 300, '', false, false, 0, 'LB');
        }

        $pdf->MultiCell(46, '', '40% Cash & Carry', 0, 'R', 0, 0);
        if ($additionalFields['cash_carry']) {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/check_.png', '', '', 5, 7.8, '', '', 'N', '', 300, '', false, false, 0, 'LB');
        } else {
            $pdf->Image(Yii::getAlias('@webroot').'/themes/basic/img/checkboks.png', '', '', 5, 7.8, '', '', 'N', '', 300, '', false, false, 0, 'LB');
        }


        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('promptblacki', '', '15');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell('', '', 'Bank Account Details', 0, 'L', 1, 1);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->SetTextColor(0,0,0);

        $pdf->setCellMargins(0,2,0);

        $pdf->MultiCell(45, '', 'Bank Name', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['bank_name'], 'B', 'L', 0, 1);

        $pdf->setCellMargins(0,0.2,0);

        $address_arr = $this->cutStr($additionalFields['bank_address'], 70, ' ');

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

        if (count($address_arr) > 1){
            for($i = 1; $i < count($address_arr); $i++){
                $pdf->SetFont('prompt', '', '11.1');
                $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                $pdf->SetFont('courgette', '', '11.6');
                $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
            }
        }


        /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
        $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Account No', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell($width - 45, '', $additionalFields['bank_account_no'], 'B', 'L', 0, 1);


        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('promptblacki', '', '15');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell('', '', 'Trade References', 0, 'L', 1, 1);

        $pdf->SetTextColor(0,0,0);
        $pdf->setCellMargins(0,2,0);

        if ($additionalFields['trade_name_2']) {
            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_name_1'], 'B', 'L', 0, 0);

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_name_2'], 'B', 'L', 0, 1);
            $pdf->setCellMargins(0, 0.2, 0);

            $address_arr_1 = $this->cutStr($additionalFields['trade_address_1'], 18, ' ');
            $address_arr_2 = $this->cutStr($additionalFields['trade_address_2'], 18, ' ');

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.1');
            $pdf->MultiCell(($width - 90) / 2, '', $address_arr_1[0], 'B', 'L', 0, 0);

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.1');
            $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_address_2'], 'B', 'L', 0, 1);

            for ($i = 1; $i < 10; $i++) {
                if ($address_arr_1[$i] || $address_arr_2[$i]) {
                    $pdf->SetFont('prompt', '', '11.1');
                    $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                    $pdf->SetFont('courgette', '', '11.1');
                    $pdf->MultiCell(($width - 90) / 2, '', $address_arr_1[$i] . ' ', 'B', 'L', 0, 0);

                    $pdf->SetFont('prompt', '', '11.1');
                    $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                    $pdf->SetFont('courgette', '', '11.1');
                    $pdf->MultiCell(($width - 90) / 2, '', $address_arr_2[$i] . ' ', 'B', 'L', 0, 1);
                }
            }

            /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
            $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
            $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
            $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);

            $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
            $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
            $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
            $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);*/

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_phone_1'], 'B', 'L', 0, 0);
            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell(($width - 90) / 2, '', $additionalFields['trade_phone_2'], 'B', 'L', 0, 1);
        } else {

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Name', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell($width - 45, '', $additionalFields['trade_name_1'], 'B', 'L', 0, 1);

            $pdf->setCellMargins(0,0.2,0);

            $address_arr = $this->cutStr($additionalFields['trade_address_1'], 70, ' ');

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Address', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell($width - 45, '', $address_arr[0], 'B', 'L', 0, 1);

            if (count($address_arr) > 1){
                for($i = 1; $i < count($address_arr); $i++){
                    $pdf->SetFont('prompt', '', '11.1');
                    $pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
                    $pdf->SetFont('courgette', '', '11.6');
                    $pdf->MultiCell($width - 45, '', $address_arr[$i], 'B', 'L', 0, 1);
                }
            }


            /*$pdf->MultiCell(45, '', ' ', 0, 'R', 0, 0);
            $pdf->MultiCell($width - 45, '', ' ', 'B', 'L', 0, 1);*/

            $pdf->SetFont('prompt', '', '11.1');
            $pdf->MultiCell(45, '', 'Telephone No', 0, 'R', 0, 0);
            $pdf->SetFont('courgette', '', '11.6');
            $pdf->MultiCell($width - 45, '', $additionalFields['trade_phone_1'], 'B', 'L', 0, 1);
        }


        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('promptblacki', '', '15');
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(255,255,255);
        $pdf->setCellPaddings(2,0,0,0);
        $pdf->MultiCell('', '', 'Declaration', 0, 'L', 1, 1);

        $pdf->SetFont('prompt', '', '10');
        $pdf->SetTextColor(0,0,0);
        $pdf->setCellMargins(0,2,0);

        $pdf->MultiCell('', '', 'I would like to open a trade account with ' . $platform['platform_owner'] . ' and agree to abide by their General Terms & Conditions – a signed copy of which is returned to them with this form in the supplied SAE. I have retained a copy for my reference. I am authorised to sign on behalf of the company I represent.', 0, 'L', 0, 1);


        $pdf->SetFont('prompt', '', '11.1');

        $pdf->MultiCell(45, '', 'Signed', 0, 'R', 0, 0);
        $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 0);
        $pdf->MultiCell(45, '', 'Date', 0, 'R', 0, 0);
        $pdf->MultiCell(($width-90)/2, '', ' ', 'B', 'L', 0, 1);
        $pdf->setCellMargins(0,1.7,0);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Name in Full', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell(($width-90)/2, '', $additionalFields['name_in_full'], 'B', 'L', 0, 0);

        $pdf->SetFont('prompt', '', '11.1');
        $pdf->MultiCell(45, '', 'Position', 0, 'R', 0, 0);
        $pdf->SetFont('courgette', '', '11.6');
        $pdf->MultiCell(($width-90)/2, '', $additionalFields['position'], 'B', 'L', 0, 1);

        $pdf->setCellMargins(0,6,0);
        $pdf->SetFont('courgette', '', '17');
        $pdf->MultiCell('', '', 'Scottish books of exceptional value', 0, 'C', 0, 0);






        $pdf->Output('trade_acc_form', 'I');
        die;
    }
}
