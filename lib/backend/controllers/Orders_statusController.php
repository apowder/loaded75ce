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
class Orders_statusController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_LOCALIZATION_ORDERS_STATUS'];
    
    private $orders_status_groups_array = array();

    function __construct($id, $module=null) {
        global $languages_id;
        $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int)$languages_id . "'");
        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
          $this->orders_status_groups_array[] = array('id' => $orders_status_groups['orders_status_groups_id'],
                                                      'text' => $orders_status_groups['orders_status_groups_name']);
        }
        parent::__construct($id, $module);
    }

    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'status', 'orders_status');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_status/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="#" class="create_item" onclick="return statusEdit(0)">'.TEXT_INFO_HEADING_NEW_ORDERS_STATUS.'</a>';
      
      $this->view->StatusTable = array(
        array(
            'title' => TABLE_HEADING_ORDERS_STATUS,
            'not_important' => 0,
        ),
      );

        $this->view->filterStatusGroups = tep_draw_pull_down_menu('osgID', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS_STATUS_GROUPS)), $this->orders_status_groups_array), $_GET['osgID'], 'class="form-control" onchange="return applyFilter();"');

        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
      
    }

    public function actionList() {
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (orders_status_name like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);
        if ($filter['osgID'] > 0) {
            $search .= " and orders_status_groups_id = '" . (int)$filter['osgID'] . "'";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "orders_status_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "orders_status_id";
                    break;
            }
        } else {
            $orderBy = "orders_status_id";
        }    
        
        $orders_status_query_raw = "select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' " . $search . " order by " . $orderBy;
        $orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $orders_status_query_numrows);
        $orders_status_query = tep_db_query($orders_status_query_raw);
        
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    
            $responseList[] = array(
                (DEFAULT_ORDERS_STATUS_ID == $orders_status['orders_status_id']? '<b>' . $orders_status['orders_status_name'] . ' (' . TEXT_DEFAULT . ')</b>': $orders_status['orders_status_name']) . tep_draw_hidden_field('id', $orders_status['orders_status_id'], 'class="cell_identify"'),
            );
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_query_numrows,
            'recordsFiltered' => $orders_status_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);          
        
    }
    
    public function actionStatusactions() {
      global $language, $languages_id;
      
      \common\helpers\Translation::init('admin/orders_status');
        
        $orders_status_id = Yii::$app->request->post('orders_status_id', 0);
        $this->layout = false;
        if ($orders_status_id) {
            $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' and orders_status_id='" . (int)$orders_status_id . "'"));
            $oInfo = new \objectInfo($ostatus, false);
            $heading = array();
            $contents = array();        
            
            if (is_object($oInfo)) {
                $heading[] = array('text' => '<b>' . $oInfo->orders_status_name . '</b>');
                echo '<div class="or_box_head">' . $oInfo->orders_status_name . '</div>';
                  $status_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . (int)$orders_status_id . "'");
                $status = tep_db_fetch_array($status_query);

                $contents[] = array('align' => 'center', 'text' => '<input type="button" value="' . IMAGE_EDIT . '" class="btn btn-primary" onclick="statusEdit('.$orders_status_id.')">&nbsp;'.
                     '<input type="button" value="' . IMAGE_DELETE . '" class="btn btn-primary" onclick="statusDelete('.$orders_status_id.')">' );


                $orders_status_inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::get_order_status_name($oInfo->orders_status_id, $languages[$i]['id']) . '</div>';
                }
                echo $orders_status_inputs_string;
                //$contents[] = array('text' => $orders_status_inputs_string);
                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<button class="btn btn-edit btn-no-margin" onclick="statusEdit('.$orders_status_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="statusDelete('.$orders_status_id.')">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }

            /*$box = new \box;
            echo $box->infoBox($heading, $contents);*/
        }
      
    }
    
    public function actionEdit() {
        global $language, $languages_id;
        \common\helpers\Translation::init('admin/orders_status');
        \common\helpers\Translation::init('admin/email/templates');
      
        $orders_status_template = [];
        $orders_status_template[] = ['id' => '', 'text' => ''];
        $orders_status_templates_query = tep_db_query("select * from " . TABLE_EMAIL_TEMPLATES . " where 1 group by email_templates_key");
        while ($email_templates = tep_db_fetch_array($orders_status_templates_query)) {
            $name_key = 'TEXT_EMAIL_'.str_replace(' ','_',strtoupper($email_templates['email_templates_key']));
            $email_templates_key = ( defined($name_key)?constant($name_key):$email_templates['email_templates_key'] );
            $orders_status_template[] = array('id' => $email_templates['email_templates_key'],
                                            'text' => $email_templates_key);
        }
      
      $orders_status_id = Yii::$app->request->get('orders_status_id', 0);
      $ostatus = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' and orders_status_id='" . (int)$orders_status_id . "'"));
      $oInfo = new \objectInfo($ostatus, false);

      $heading = array();
      $contents = array();      
      
      if ($orders_status_id) {
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</b>');
      } else {
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</b>');  
      }
      

      $contents = array('form' => tep_draw_form('status', FILENAME_ORDERS_STATUS. '/save', 'orders_status_id=' . $oInfo->orders_status_id));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $contents[] = array('text' => '<br>&nbsp;' . TEXT_ORDERS_STATUS_GROUP . '<br>&nbsp;' . tep_draw_pull_down_menu('orders_status_groups_id', $this->orders_status_groups_array, $oInfo->orders_status_groups_id)); 

      $orders_status_inputs_string = '';
      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $orders_status_inputs_string .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', \common\helpers\Order::get_order_status_name($oInfo->orders_status_id, $languages[$i]['id'])) . '</div>';
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
      if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      
      $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-primary" onclick="statusSave('.($oInfo->orders_status_id?$oInfo->orders_status_id:0).')">
      <input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-primary" onclick="resetStatement()">');

       /* $box = new \box;
      echo $box->infoBox($heading, $contents);*/
      echo tep_draw_form('status', FILENAME_ORDERS_STATUS. '/save', 'orders_status_id=' . $oInfo->orders_status_id);
      if ($orders_status_id) {
          echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</div>';
      } else {
          echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</div>';
      }
      echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_ORDERS_STATUS_GROUP . '</div>';
      echo '<div class="main_value">' . tep_draw_pull_down_menu('orders_status_groups_id', $this->orders_status_groups_array, $oInfo->orders_status_groups_id) . '</div>';
      echo '<div class="main_title">' . TEXT_ORDERS_STATUS_TEMPLATE . '</div>';
      echo '<div class="main_value">' . tep_draw_pull_down_menu('orders_status_template', $orders_status_template, $oInfo->orders_status_template) . '</div>';
      echo '</div>';
      echo '<div class="col_desc">' . TEXT_INFO_ORDERS_STATUS_NAME . '</div>';
      echo $orders_status_inputs_string;
      if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) echo '<div class="check_linear">' . tep_draw_checkbox_field('default') . '<span>' . TEXT_SET_DEFAULT . '</span></div>';
      echo '<div class="check_linear">' . tep_draw_checkbox_field('automated', '1', $oInfo->automated) . '<span>' . TEXT_AUTOMATED . '</span></div>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="statusSave('.($oInfo->orders_status_id?$oInfo->orders_status_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
      echo '</div>';
      echo '</form>';
    }
    
    public function actionSave() {
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/orders_status');    
        $orders_status_id = intval(Yii::$app->request->get('orders_status_id', 0));
        $orders_status_groups_id = intval(Yii::$app->request->post('orders_status_groups_id', 0));

        if ($orders_status_id == 0) {
            $next_id_query = tep_db_query("select max(orders_status_id) as orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_id <> '99999'");//paypal
            $next_id = tep_db_fetch_array($next_id_query);
            $insert_id = $next_id['orders_status_id'] + 1;
        }
             
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $orders_status_name_array = $_POST['orders_status_name'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('orders_status_name' => tep_db_prepare_input($orders_status_name_array[$language_id]),
                                  'orders_status_groups_id' => $orders_status_groups_id,
                                  'orders_status_template' => tep_db_prepare_input(Yii::$app->request->post('orders_status_template')),
                                  'automated' => (int)Yii::$app->request->post('automated'),
                  );

          if ($orders_status_id == 0) {

            $insert_sql_data = array('orders_status_id' => $insert_id,
                                     'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
            $action = 'added';
          } else {
            tep_db_perform(TABLE_ORDERS_STATUS, $sql_data_array, 'update', "orders_status_id = '" . (int)$orders_status_id . "' and language_id = '" . (int)$language_id . "'");
            $action = 'updated';
          }
        }
        if ($orders_status_id == 0) {$orders_status_id = $insert_id;}

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($orders_status_id) . "' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        }
        
        echo json_encode(array('message' => 'Status ' . $action, 'messageType' => 'alert-success'));
    }
    
    
    public function actionDelete() {
      global $language;
      \common\helpers\Translation::init('admin/orders_status');
      
        $orders_status_id =  Yii::$app->request->post('orders_status_id', 0);
        
        if($orders_status_id) {
            
                $remove_status = true;
                $error = array();
                if ($orders_status_id == DEFAULT_ORDERS_STATUS_ID) {
                  $remove_status = false;
                  $error = array('message' => ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'messageType' => 'alert-danger');
                } elseif ($status['count'] > 0) {
                  $remove_status = false;
                  $error = array('message' => ERROR_STATUS_USED_IN_ORDERS, 'messageType' => 'alert-danger');
                } else {
                  $history_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_status_id = '" . (int)$oID . "'");
                  $history = tep_db_fetch_array($history_query);
                  if ($history['count'] > 0) {
                    $remove_status = false;
                    $error = array('message' => ERROR_STATUS_USED_IN_HISTORY, 'messageType' => 'alert-danger');
                  }
                }    
            if (!$remove_status) {
                ?>
              <div class="alert fade in <?=$error['messageType']?>">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"><?=$error['message']?></span>
              </div>       
                <?php
                
            } else {
                $orders_status_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
                $orders_status = tep_db_fetch_array($orders_status_query);

                if ($orders_status['configuration_value'] == $orders_status_id) {
                  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
                }

                tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . tep_db_input($orders_status_id) . "'");
                echo 'reset';
            }
            
        }
        
    }
}
