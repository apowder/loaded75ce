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
 * default controller to handle user requests.
 */
class SuppliersController extends Sceleton  {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_SUPPIERS'];
    
  public function actionIndex() {
    global $language;

    $this->selectedMenu = array('catalog', 'suppliers');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('suppliers/index'), 'title' => HEADING_TITLE);
    $this->topButtons[] = '<a href="#" onclick="return supplierEdit(0)" class="create_item"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_SUPPLIER . '</a>';
    $this->view->headingTitle = HEADING_TITLE;

    $this->view->SupplierTable = array(
        array(
            'title' => TABLE_HEADING_SUPPLIERS,
            'not_important' => 0,
        ),
        array(
            'title' => TABLE_HEADING_SURCHARGE,
            'not_important' => 0,
        ),
        array(
            'title' => TABLE_HEADING_MARGIN,
            'not_important' => 0,
        ),
    );

    $messages = $_SESSION['messages'];
    unset($_SESSION['messages']);

    $sID = Yii::$app->request->get('sID', 0);
    return $this->render('index', array('messages' => $messages, 'sID' => $sID));
  }

  public function actionList() {
    global $languages_id;
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);

    $currencies = new \common\classes\currencies();

    $search = '';
    if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
      $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
      $search .= " and (suppliers_name like '%" . $keywords . "%')";
    }

    $current_page_number = ($start / $length) + 1;
    $responseList = array();

    if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
      switch ($_GET['order'][0]['column']) {
        case 0:
          $orderBy = "suppliers_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
          break;
        case 1:
          $orderBy = "suppliers_surcharge_amount " . tep_db_prepare_input($_GET['order'][0]['dir']);
          break;
        case 2:
          $orderBy = "suppliers_margin_percentage " . tep_db_prepare_input($_GET['order'][0]['dir']);
          break;
        default:
          $orderBy = "suppliers_name";
          break;
      }
    } else {
      $orderBy = "suppliers_id";
    }

    $suppliers_query_raw = "select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage, suppliers_script, date_added, last_modified from " . TABLE_SUPPLIERS . " where 1 " . $search . " order by " . $orderBy;
    $suppliers_split = new \splitPageResults($current_page_number, $length, $suppliers_query_raw, $suppliers_query_numrows);
    $suppliers_query = tep_db_query($suppliers_query_raw);

    while ($suppliers = tep_db_fetch_array($suppliers_query)) {

      $responseList[] = array(
          $suppliers['suppliers_name'] . tep_draw_hidden_field('id', $suppliers['suppliers_id'], 'class="cell_identify"'),
          $currencies->format($suppliers['suppliers_surcharge_amount']),
          $suppliers['suppliers_margin_percentage'] . '%',
      );
    }

    $response = array(
        'draw' => $draw,
        'recordsTotal' => $suppliers_query_numrows,
        'recordsFiltered' => $suppliers_query_numrows,
        'data' => $responseList
    );
    echo json_encode($response);
  }

  public function actionStatusactions() {
    global $language, $languages_id;
    \common\helpers\Translation::init('admin/suppliers');

    $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
    $this->layout = false;

    if ($suppliers_id > 0) {
      $suppliers = tep_db_fetch_array(tep_db_query("select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage, suppliers_script, date_added, last_modified from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int) $suppliers_id . "'"));
      $sInfo = new \objectInfo($suppliers, false);

      if ($sInfo->suppliers_id > 0) {
        echo '<div class="or_box_head">' . $sInfo->suppliers_name . '</div>';
        $supplier_products = tep_db_fetch_array(tep_db_query("select count(*) as products_count from " . TABLE_SUPPLIERS_PRODUCTS . " where suppliers_id = '" . (int)$suppliers_id . "'"));
        echo '<div class="main_row">' . sprintf(TEXT_PRODUCTS_LINKED_TO_SUPPLIER, $supplier_products['products_count']) . '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<button class="btn btn-edit btn-no-margin" onclick="supplierEdit(' . $suppliers_id . ')">' . IMAGE_EDIT . '</button>';
        echo '<button class="btn btn-delete" onclick="supplierDeleteConfirm(' . $suppliers_id . ')">' . IMAGE_DELETE . '</button>';
        echo '</div>';
      }
    }
  }

  public function actionEdit() {
    global $language, $languages_id;
    \common\helpers\Translation::init('admin/suppliers');

    $currencies = new \common\classes\currencies();

    $suppliers_id = Yii::$app->request->get('suppliers_id', 0);
    $suppliers = tep_db_fetch_array(tep_db_query("select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage, suppliers_script, date_added, last_modified from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int) $suppliers_id . "'"));
    $sInfo = new \objectInfo($suppliers, false);

    $heading = array();
    $contents = array();

    echo tep_draw_form('supplier', FILENAME_SUPPLIERS . '/save', 'suppliers_id=' . $sInfo->suppliers_id, 'post', 'onsubmit="return supplierSave(' . ($sInfo->suppliers_id ? $sInfo->suppliers_id : 0) . ');"');

    if ($suppliers_id) {
      echo '<div class="or_box_head">' . TEXT_HEADING_EDIT_SUPPLIER . '</div>';
    } else {
      echo '<div class="or_box_head">' . TEXT_HEADING_NEW_SUPPLIER . '</div>';
    }

    echo '<div class="main_row"><div class="main_title">' . TEXT_SUPPLIERS_NAME . '</div><div class="main_value">' . tep_draw_input_field('suppliers_name', $sInfo->suppliers_name, 'class="form-control"') . '</div></div>';
    echo '<div class="main_row"><div class="main_title">' . TEXT_SUPPLIERS_SURCHARGE_AMOUNT . '</div><div class="main_value">' . tep_draw_input_field('suppliers_surcharge_amount', $sInfo->suppliers_surcharge_amount, 'class="form-control"') . '</div></div>';
    echo '<div class="main_row"><div class="main_title">' . TEXT_SUPPLIERS_MARGIN_PERCENTAGE . '</div><div class="main_value">' . tep_draw_input_field('suppliers_margin_percentage', $sInfo->suppliers_margin_percentage, 'class="form-control"') . '</div></div>';
    //echo '<div class="main_row"><div class="main_title">' . TEXT_SUPPLIERS_SCRIPT . '</div><div class="main_value">' . tep_draw_input_field('suppliers_script', $sInfo->suppliers_script) . '</div></div>';

    echo '<div class="btn-toolbar btn-toolbar-order">';
    if ($suppliers_id) {
      echo '<input type="submit" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement(' . (int)$sInfo->suppliers_id . ')">';
    } else {
     echo '<input type="submit" value="' . IMAGE_NEW . '" class="btn btn-no-margin"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement(' . (int)$sInfo->suppliers_id . ')">';
    }
    
    echo '</div>';
    echo '</form>';
  }

  public function actionSave() {
    global $language, $languages_id;
    \common\helpers\Translation::init('admin/suppliers');

    $suppliers_id = Yii::$app->request->get('suppliers_id', 0);
    $suppliers_name = tep_db_prepare_input(Yii::$app->request->post('suppliers_name', ''));
    $suppliers_surcharge_amount = tep_db_prepare_input(Yii::$app->request->post('suppliers_surcharge_amount', 0));
    $suppliers_margin_percentage = tep_db_prepare_input(Yii::$app->request->post('suppliers_margin_percentage', 0));
//    $suppliers_script = Yii::$app->request->post('suppliers_script', 0);

    $sql_data_array = array('suppliers_name' => $suppliers_name,
                            'suppliers_surcharge_amount' => $suppliers_surcharge_amount,
                            'suppliers_margin_percentage' => $suppliers_margin_percentage,
//                            'suppliers_script' => $suppliers_script,
                            );

    if ($suppliers_id == 0) {
      $insert_sql_data = array('date_added' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
      tep_db_perform(TABLE_SUPPLIERS, $sql_data_array);
      $suppliers_id = tep_db_insert_id();
      $action = 'added';
    } else {
      $update_sql_data = array('last_modified' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $update_sql_data);
      tep_db_perform(TABLE_SUPPLIERS, $sql_data_array, 'update', "suppliers_id = '" . (int)$suppliers_id . "'");
      $action = 'updated';
    }

    echo json_encode(array('message' => 'Supplier ' . $action, 'messageType' => 'alert-success'));
  }

  public function actionConfirmdelete() {
    global $language;
    \common\helpers\Translation::init('admin/suppliers');

    $this->layout = false;

    $suppliers_id = Yii::$app->request->post('suppliers_id');

    if ($suppliers_id > 0) {
      $suppliers = tep_db_fetch_array(tep_db_query("select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage, suppliers_script, date_added, last_modified from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int) $suppliers_id . "'"));
      $sInfo = new \objectInfo($suppliers, false);

      echo tep_draw_form('suppliers', FILENAME_SUPPLIERS, \common\helpers\Output::get_all_get_params(array('sID', 'action')) . 'dID=' . $sInfo->suppliers_id . '&action=deleteconfirm', 'post', 'id="item_delete" onSubmit="return supplierDelete();"');

      echo '<div class="or_box_head">' . TEXT_HEADING_DELETE_SUPPLIER . '</div>';
      echo TEXT_DELETE_INTRO . '<br><br><b>' . $sInfo->suppliers_name . '</b>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<button type="submit" class="btn btn-primary btn-no-margin">' . IMAGE_CONFIRM . '</button>';
      echo '<button class="btn btn-cancel" onClick="return resetStatement(' . (int)$suppliers_id . ')">' . IMAGE_CANCEL . '</button>';      

      echo tep_draw_hidden_field('suppliers_id', $suppliers_id);
      echo '</div></form>';
    }
  }

  public function actionDelete() {
    global $language;
    \common\helpers\Translation::init('admin/suppliers');

    $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
    if ($suppliers_id > 0) {
      tep_db_query("delete from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int)$suppliers_id . "'");
      tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where suppliers_id = '" . (int)$suppliers_id . "'");
      echo 'reset';
    }
  }

}


