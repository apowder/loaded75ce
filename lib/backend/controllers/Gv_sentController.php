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

/**
 * GV sent controller to handle user requests.
 */
class Gv_sentController extends Sceleton {

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_SENT'];
    
    function __construct($id, $module = null) {
        if (false === \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'allowed')) {
            $this->redirect(array('/'));
        }
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language;
        
        $this->selectedMenu = array('marketing', 'gv_admin', 'gv_sent');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('gv_sent/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => TABLE_HEADING_SENDERS_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_VOUCHER_VALUE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_VOUCHER_CODE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_SENT,
                'not_important' => 0
            ),
        );
        return $this->render('index', ['cid' => (int)$_GET['cid']]);
    }

    public function actionList() {
        global $languages_id;
        
        $currencies = new \common\classes\currencies();
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cid = Yii::$app->request->get('cid', 0);

        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;
        $responseList = array();

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where c.coupon_id = et.coupon_id and (et.sent_firstname like '%" . $keywords . "%' or et.sent_lastname like '%" . $keywords . "%' or et.emailed_to like '%" . $keywords . "%') ";
        } else {
            $search_condition = " where c.coupon_id = et.coupon_id ";
        }
        if ($cid){
          $search_condition .= " and et.coupon_id = '" . (int)$cid . "'";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "et.sent_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "c.coupon_amount " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "c.coupon_code " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "et.date_sent " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "et.date_sent desc";
                    break;
            }
        } else {
            $orderBy = "et.date_sent desc";
        }

        $gv_query_raw = "select et.unique_id, c.coupon_amount, c.coupon_currency, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et $search_condition ORDER by $orderBy ";
        
        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $gv_query_raw, $query_numrows, 'unique_id');
        $gv_query = tep_db_query($gv_query_raw);
        while ($gv_list = tep_db_fetch_array($gv_query)) {

              
            $responseList[] = array(
                $gv_list['sent_firstname'] . ' ' . $gv_list['sent_lastname'] .
                '<input class="cell_identify" type="hidden" value="' . $gv_list['unique_id'] . '">',
                $currencies->format($gv_list['coupon_amount'], false, $gv_list['coupon_currency']),
                $gv_list['coupon_code'],
                \common\helpers\Date::date_short($gv_list['date_sent']),
            );
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionItempreedit() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/gv_sent');
        
        $currencies = new \common\classes\currencies();

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $gv_query = tep_db_query("select et.unique_id, c.coupon_amount, c.coupon_currency, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et where c.coupon_id = et.coupon_id and et.unique_id = '" . (int) $item_id . "'");
        $gv_list = tep_db_fetch_array($gv_query);
        $gInfo = new \objectInfo($gv_list);
        
        $heading = array();
        $contents = array();

    $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency));
		echo '<div class="or_box_head">[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency) . '</div>';
  $redeem_query = tep_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gInfo->coupon_id . "'");
  $redeemed = 'No';
  if (tep_db_num_rows($redeem_query) > 0) $redeemed = 'Yes';	
  /* $contents[] = array('text' => TEXT_INFO_SENDERS_ID . ' ' . $gInfo->customer_id_sent);
  $contents[] = array('text' => TEXT_INFO_AMOUNT_SENT . ' ' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency));
  $contents[] = array('text' => TEXT_INFO_DATE_SENT . ' ' . \common\helpers\Date::date_short($gInfo->date_sent));
  $contents[] = array('text' => TEXT_INFO_VOUCHER_CODE . ' ' . $gInfo->coupon_code);
  $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to); */
	echo '<div class="row_or_wrapp">';
	echo '<div class="row_or"><div>' . TEXT_INFO_SENDERS_ID . '</div><div>' . $gInfo->customer_id_sent . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_AMOUNT_SENT . '</div><div>' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency) . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_DATE_SENT . '</div><div>' . \common\helpers\Date::date_short($gInfo->date_sent) . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_VOUCHER_CODE . '</div><div>' . $gInfo->coupon_code . '</div></div>';	
	
  if ($redeemed=='Yes') {
    $redeem = tep_db_fetch_array($redeem_query);
    /* $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_REDEEMED . ' ' . \common\helpers\Date::date_short($redeem['redeem_date']));
    $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $redeem['redeem_ip']);
    $contents[] = array('text' => TEXT_INFO_CUSTOMERS_ID . ' ' . $redeem['customer_id']); */
		echo '<div class="row_or"><div>' . TEXT_INFO_DATE_REDEEMED . '</div><div>' . \common\helpers\Date::date_short($redeem['redeem_date']) . '</div></div>';
		echo '<div class="row_or"><div>' . TEXT_INFO_IP_ADDRESS . '</div><div>' . $redeem['redeem_ip'] . '</div></div>';
		echo '<div class="row_or"><div>' . TEXT_INFO_CUSTOMERS_ID . '</div><div>' . $redeem['customer_id'] . '</div></div>';
  } else {
    $contents[] = array('text' => '<br>' . TEXT_INFO_NOT_REDEEMED);
		echo '<div class="row_or">' . TEXT_INFO_NOT_REDEEMED . '</div>';
  }
	
		echo '</div>';
		echo '<div class="row_full">' . TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to . '</div>';
        /* $box = new \box;
        echo $box->infoBox($heading, $contents); */
    }

}
