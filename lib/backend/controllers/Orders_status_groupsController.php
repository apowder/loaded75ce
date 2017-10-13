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
class Orders_status_groupsController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_ORDERS_STATUS_GROUPS'];
    
    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('settings', 'status', 'orders_status_groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_status_groups/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return statusGroupEdit(0)">'.TEXT_INFO_HEADING_NEW_ORDERS_STATUS_GROUP.'</a>';

        $this->view->StatusGroupTable = array(
            array(
                'title' => TABLE_HEADING_ORDERS_STATUS_GROUP,
                'not_important' => 0,
            ),
        );

        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList() {
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (orders_status_groups_name like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);
        if ($filter['osgID'] > 0) {
            $search .= " and orders_status_groups_id = '" . (int) $filter['osgID'] . "'";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "orders_status_groups_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "orders_status_groups_id";
                    break;
            }
        } else {
            $orderBy = "orders_status_groups_id";
        }

        $orders_status_groups_query_raw = "select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int)$languages_id . "' " . $search . " order by " . $orderBy;
        $orders_status_groups_split = new \splitPageResults($current_page_number, $length, $orders_status_groups_query_raw, $orders_status_groups_query_numrows);
        $orders_status_groups_query = tep_db_query($orders_status_groups_query_raw);

        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {

            $responseList[] = array(
                $orders_status_groups['orders_status_groups_name'] . tep_draw_hidden_field('id', $orders_status_groups['orders_status_groups_id'], 'class="cell_identify"'),
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_groups_query_numrows,
            'recordsFiltered' => $orders_status_groups_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions() {
        global $language, $languages_id;
        \common\helpers\Translation::init('admin/orders_status_groups');

        $orders_status_groups_id = Yii::$app->request->post('orders_status_groups_id', 0);
        $this->layout = false;
        if ($orders_status_groups_id) {
            $ostatus_groups = tep_db_fetch_array(tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = " . (int)$languages_id ));
            $oInfo = new \objectInfo($ostatus_groups, false);
            $heading = array();
            $contents = array();

            if (is_object($oInfo)) {
                echo '<div class="or_box_head">' . $oInfo->orders_status_groups_name . '</div>';

                $orders_status_inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::orders_status_groups_name($oInfo->orders_status_groups_id, $languages[$i]['id']) . '</div>';
                }
                echo $orders_status_inputs_string;

                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<button class="btn btn-edit btn-no-margin" onclick="statusGroupEdit(' . $orders_status_groups_id . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="statusGroupDelete(' . $orders_status_groups_id . ')">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }

        }
    }

    public function actionEdit() {
        global $language, $languages_id;
        \common\helpers\Translation::init('admin/orders_status_groups');

        $orders_status_groups_id = Yii::$app->request->get('orders_status_groups_id', 0);
        $ostatus_groups = tep_db_fetch_array(tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "'"));
        $oInfo = new \objectInfo($ostatus_groups, false);

        echo tep_draw_form('status_group', FILENAME_ORDERS_STATUS_GROUPS . '/save', 'orders_status_groups_id=' . $oInfo->orders_status_groups_id);
        if ($orders_status_groups_id) {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS_GROUP . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS_GROUP . '</div>';
        }

        $orders_status_inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $orders_status_inputs_string .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('orders_status_groups_name[' . $languages[$i]['id'] . ']', \common\helpers\Order::orders_status_groups_name($oInfo->orders_status_groups_id, $languages[$i]['id'])) . '</div>';
        }
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_NAME . '</div><div class="main_value">' . $orders_status_inputs_string . '</div></div>';

        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_COLOR . '</div><div class="main_value">' . tep_draw_input_field('orders_status_groups_color', $oInfo->orders_status_groups_color) . '</div></div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="statusGroupSave(' . ($oInfo->orders_status_groups_id ? $oInfo->orders_status_groups_id : 0) . ')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
        echo '</div>';
        echo '</form>';
    }

    public function actionSave() {
        global $language, $languages_id;
        \common\helpers\Translation::init('admin/orders_status_groups');
        $orders_status_groups_id = intval(Yii::$app->request->get('orders_status_groups_id', 0));
        $orders_status_groups_name = tep_db_prepare_input(Yii::$app->request->post('orders_status_groups_name', array()));
        $orders_status_groups_color = tep_db_prepare_input(Yii::$app->request->post('orders_status_groups_color', ''));

        if ($orders_status_groups_id == 0) {
            $next_id_query = tep_db_query("select max(orders_status_groups_id) as orders_status_groups_id from " . TABLE_ORDERS_STATUS_GROUPS . " where 1");
            $next_id = tep_db_fetch_array($next_id_query);
            $insert_id = $next_id['orders_status_groups_id'] + 1;
        }

        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('orders_status_groups_name' => $orders_status_groups_name[$language_id],
                                    'orders_status_groups_color' => $orders_status_groups_color);

            if ($orders_status_groups_id == 0) {
                $insert_sql_data = array('orders_status_groups_id' => $insert_id,
                                         'language_id' => $language_id);
                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array);
                $action = 'added';
            } else {
                $check = tep_db_fetch_array(tep_db_query("select count(orders_status_groups_id) as orders_status_groups_exists from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int)$language_id . "'"));
                if (!$check['orders_status_groups_exists']) {
                    $insert_sql_data = array('orders_status_groups_id' => $orders_status_groups_id,
                                             'language_id' => $language_id);
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array);
                } else {
                    tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array, 'update', "orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int)$language_id . "'");
                }
                $action = 'updated';
            }

            if ($orders_status_groups_id == 0) {
                $orders_status_groups_id = tep_db_insert_id();
            }
        }

        echo json_encode(array('message' => 'Status Group ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete() {
        global $language;
        \common\helpers\Translation::init('admin/orders_status_groups');

        $orders_status_groups_id = Yii::$app->request->post('orders_status_groups_id', 0);

        if ($orders_status_groups_id) {

            $remove_status_group = true;
            $error = array();
            $status_group_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "'");
            $status_group = tep_db_fetch_array($status_group_query);
            if ($status_group['count'] > 0) {
                $remove_status_group = false;
                $error = array('message' => ERROR_STATUS_GROUPS_USED_IN_ORDERS_STATUS, 'messageType' => 'alert-danger');
            }
            if (!$remove_status_group) {
                ?>
                <div class="alert fade in <?= $error['messageType'] ?>">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"><?= $error['message'] ?></span>
                </div>       
                <?php
            } else {
                tep_db_query("delete from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . tep_db_input($orders_status_groups_id) . "'");
                echo 'reset';
            }
        }
    }

}
