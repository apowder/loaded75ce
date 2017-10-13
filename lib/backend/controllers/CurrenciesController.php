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
class CurrenciesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCALIZATION', 'BOX_LOCALIZATION_CURRENCIES'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'localization', 'currencies');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('currencies/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="currencies/update" class="create_item">'.TEXT_UPDATE_CURRENCIES.'</a><a href="#" class="create_item" onclick="return currencyEdit(0)">'.TEXT_INFO_HEADING_NEW_CURRENCY.'</a>';
	  
	  $this->view->currenciesTable = array(
		array(
			'title' => TABLE_HEADING_CURRENCY_NAME,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_CURRENCY_CODES,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_CURRENCY_VALUE,
			'not_important' => 0,
		),
              array(
			'title' => TABLE_HEADING_STATUS,
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
        $cID = Yii::$app->request->get('cID', 0);
        
        if( $length == -1 ) $length = 1000;

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (title like '%" . tep_db_input($keywords) . "%' or code like '%" . tep_db_input($keywords) . "%')";
        }
		
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "title " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "code " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "title";
                    break;
            }
        } else {
            $orderBy = "sort_order, title";
        }	
		
        $current_page_number = ($start / $length) + 1;
        $responseList = array();
		
        $currency_query_raw = "select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value, status from " . TABLE_CURRENCIES . " where 1 " . $search . " order by " . $orderBy;
        $currency_split = new \splitPageResults($current_page_number, $length, $currency_query_raw, $currency_query_numrows);
        $currency_query = tep_db_query($currency_query_raw);
        while ($currency = tep_db_fetch_array($currency_query)) {

              $responseList[] = array(
                      '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                      (DEFAULT_CURRENCY == $currency['code'] ? '<b>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</b>':  $currency['title']) . tep_draw_hidden_field('id', $currency['currencies_id'], 'class="cell_identify"') . '<input class="cell_type" type="hidden" value="curr" >',
                      $currency['code'],
                       number_format($currency['value'], 8),
                      ('<input type="checkbox" value="' . $currency['currencies_id'] . '" name="status" class="check_on_off"' . ($currency['status'] == 1 ? ' checked="checked"' : '') . '>')
              );
        }

      $response = array(
            'draw' => $draw,
            'recordsTotal' => $currency_query_numrows,
            'recordsFiltered' => $currency_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
    }
	
    public function actionCurrencyactions(){
      global $language;
      \common\helpers\Translation::init('admin/currencies');
				
		$currencies = new \common\classes\currencies();
		
		$currencies_id = Yii::$app->request->post('currencies_id', 0);
		$this->layout = false;
		if ($currencies_id){
			$currency = tep_db_fetch_array(tep_db_query("select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value, status from " . TABLE_CURRENCIES . " where currencies_id ='" . (int)$currencies_id . "'"));
			$cInfo = new \objectInfo($currency, false);
			/* $heading = array();
			$contents = array();		
			
			$heading[] = array('text' => '<b>' . $cInfo->title . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<input type="button" value="' . IMAGE_EDIT . '" class="btn btn-primary" onclick="currencyEdit('.$currencies_id.')">&nbsp;'.
			'<input type="button" value="' . IMAGE_DELETE . '" class="btn btn-primary" onclick="currencyDelete('.$currencies_id.')">');
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
			$contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
			$contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
			$contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
			$contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . \common\helpers\Date::date_short($cInfo->last_updated));
			$contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
			if (tep_not_null($cInfo->code) && tep_not_null(DEFAULT_CURRENCY)){
				$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_EXAMPLE . '<br>' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
			} */
			
			echo '<div class="or_box_head">' . $cInfo->title . '</div>';
			echo '<div class="row_or_wrapp">';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_TITLE . '</div><div>' . $cInfo->title . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_CODE . '</div><div>' . $cInfo->code . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '</div><div>' . $cInfo->symbol_left . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '</div><div>' . $cInfo->symbol_right . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '</div><div>' . $cInfo->decimal_point . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '</div><div>' . $cInfo->thousands_point . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '</div><div>' . $cInfo->decimal_places . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_LAST_UPDATED . '</div><div>' . \common\helpers\Date::date_short($cInfo->last_updated) . '</div></div>';
			echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_VALUE . '</div><div>' . number_format($cInfo->value, 8) . '</div></div>';
			if (tep_not_null($cInfo->code) && tep_not_null(DEFAULT_CURRENCY)){
				echo '<div class="row_or"><div>' . TEXT_INFO_CURRENCY_EXAMPLE . '</div><div>' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code) . '</div></div>';
			}
			echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo '<button class="btn btn-edit btn-no-margin" onclick="currencyEdit('.$currencies_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="currencyDelete('.$currencies_id.')">' . IMAGE_DELETE . '</button>';
			echo '</div>';
			
			/* $box = new \box;
			echo $box->infoBox($heading, $contents); */
		}
	  
	}
	
    public function actionEdit(){
      global $language;
      \common\helpers\Translation::init('admin/currencies');
	  
	  $currencies_id = Yii::$app->request->get('currencies_id', 0);
  	  $currency = tep_db_fetch_array(tep_db_query("select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value, status from " . TABLE_CURRENCIES . " where currencies_id ='" . (int)$currencies_id . "'"));	  
	  $cInfo = new \objectInfo($currency, false);

	  $heading = array();
	  $contents = array();	  
	  
	  if($currencies_id){
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</b>');  
	  } else {
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</b>');
	  }
      

      $contents = array('form' => tep_draw_form('currencies', FILENAME_CURRENCIES.'/save', 'currencies_id=' . $cInfo->currencies_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . tep_draw_input_field('title', $cInfo->title));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . tep_draw_input_field('code', $cInfo->code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br>' . tep_draw_input_field('symbol_left', $cInfo->symbol_left));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br>' . tep_draw_input_field('symbol_right', $cInfo->symbol_right));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . tep_draw_input_field('decimal_point', $cInfo->decimal_point));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . tep_draw_input_field('thousands_point', $cInfo->thousands_point));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . tep_draw_input_field('decimal_places', $cInfo->decimal_places));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . tep_draw_input_field('value', $cInfo->value));
      if (DEFAULT_CURRENCY != $cInfo->code) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-primary" onclick="currencySave('.($cInfo->currencies_id?$cInfo->currencies_id:0).')">
	  <input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-primary" onclick="resetStatement()">');
		
		echo tep_draw_form('currencies', FILENAME_CURRENCIES.'/save', 'currencies_id=' . $cInfo->currencies_id . '&action=save');
		if($currencies_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</div>';  
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_CURRENCY . '</div>';
	  }
		echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_TITLE . '</div><div class="main_value">' . tep_draw_input_field('title', $cInfo->title, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_CODE . '</div><div class="main_value">' . tep_draw_input_field('code', $cInfo->code, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '</div><div class="main_value">' .  tep_draw_input_field('symbol_left', $cInfo->symbol_left, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '</div><div class="main_value">' . tep_draw_input_field('symbol_right', $cInfo->symbol_right, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '</div><div class="main_value">' . tep_draw_input_field('decimal_point', $cInfo->decimal_point, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '</div><div class="main_value">' . tep_draw_input_field('thousands_point', $cInfo->thousands_point, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '</div><div class="main_value">' . tep_draw_input_field('decimal_places', $cInfo->decimal_places, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CURRENCY_VALUE . '</div><div class="main_value">' . tep_draw_input_field('value', $cInfo->value, 'class="form-control"') . '</div></div>';
                echo '<div class="main_row"><div class="main_title">' . substr(TEXT_STATUS, 0,-1) . ' (' . ($cInfo->status? IMAGE_ICON_STATUS_GREEN: IMAGE_ICON_STATUS_RED) . ')' . '&nbsp;' . tep_draw_checkbox_field('status', 1, $cInfo->status) . '</div></div>';
		if (DEFAULT_CURRENCY != $cInfo->code) echo  '<div class="main_bottom">' . tep_draw_checkbox_field('default') . '<span>' . TEXT_INFO_SET_AS_DEFAULT . '</span></div>';
		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="currencySave('.($cInfo->currencies_id?$cInfo->currencies_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';
		
		
  	/*$box = new \box;
	  echo $box->infoBox($heading, $contents);*/
	  
	}
	
    public function actionSave(){
        global $language;
        \common\helpers\Translation::init('admin/currencies');	
        
        $currency_id = Yii::$app->request->get('currencies_id', 0);

        $title = tep_db_prepare_input($_POST['title']);
        $code = tep_db_prepare_input($_POST['code']);
        $symbol_left = tep_db_prepare_input($_POST['symbol_left'], false);
        $symbol_right = tep_db_prepare_input($_POST['symbol_right'], false);
        $decimal_point = tep_db_prepare_input($_POST['decimal_point']);
        $thousands_point = tep_db_prepare_input($_POST['thousands_point']);
        $decimal_places = tep_db_prepare_input($_POST['decimal_places']);
        $value = tep_db_prepare_input($_POST['value']);
        $status = tep_db_prepare_input($_POST['status']);
        
        $check = tep_db_query("select * from " . TABLE_CURRENCIES . " where code = '" . $code  . "'" . ($currency_id?" and currencies_id != '" . $currency_id . "'":"")); 
        if (tep_db_num_rows($check)){
		  echo json_encode(array('message' => sprintf(TEXT_CURRENCY_CODE_ALREADY_EXISTS, $code), 'messageType' => 'alert-warning'));
          exit();
        }
        
        if ($code == DEFAULT_CURRENCY){
            if (!$status){
               echo json_encode(array('message' => ERROR_DEFAULT_CURRENCY_INACTIVE, 'messageType' => 'alert-danger'));
               exit();
            }
        }

        $sql_data_array = array('title' => $title,
                                'code' => $code,
                                'symbol_left' => $symbol_left,
                                'symbol_right' => $symbol_right,
                                'decimal_point' => $decimal_point,
                                'thousands_point' => $thousands_point,
                                'decimal_places' => $decimal_places,
                                'value' => $value,
                                'status' => $status,
            );

        if ($currency_id == 0) {
		  $action = 'added';
          tep_db_perform(TABLE_CURRENCIES, $sql_data_array);
          $currency_id = tep_db_insert_id();
        } elseif ($currency_id) {
		  $action = 'updated';
          tep_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency_id . "'");
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_CURRENCY'");
          tep_db_query("update " . TABLE_CURRENCIES . " set status = 1 where currencies_id = '" . (int)$currency_id . "'");
        }
		echo json_encode(array('message' => 'Currency is ' . $action, 'messageType' => 'alert-success'));
        
	}
	
    public function actionUpdate(){
        global $language;
        \common\helpers\Translation::init('admin/currencies');
		
        $messages = array();
        $currency_query = tep_db_query("select currencies_id, code, title from " . TABLE_CURRENCIES);
        while ($currency = tep_db_fetch_array($currency_query)) {
            $server_used = 'xe';
            $rate = \common\helpers\Currencies::quote_xe_currency($currency['code']);
            if (empty($rate)) {
                $messages[] = array('message' => sprintf(WARNING_PRIMARY_SERVER_FAILED, $server_used, $currency['title'], $currency['code']), 'messageType' => 'alert-warning');
                $rate = \common\helpers\Currencies::quote_google_currency($currency['code']);
                $server_used = 'google';
            }
            if (tep_not_null($rate)) {
                tep_db_query("update " . TABLE_CURRENCIES . " set value = '" . tep_db_input($rate) . "', last_updated = now() where currencies_id = '" . (int) $currency['currencies_id'] . "'");
                $messages[] = array('message' => sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency['title'], $currency['code'], $server_used), 'messageType' => 'alert-success');
            } else {
                $messages[] = array('message' => sprintf(ERROR_CURRENCY_INVALID, $currency['title'], $currency['code'], $server_used), 'messageType' => 'alert-danger');
            }
        }
        $_SESSION['messages'] = $messages;
        $this->redirect(array('currencies/index'));
    }

    public function actionDelete(){
        global $language;
        \common\helpers\Translation::init('admin/currencies');
      
        $messages = array();
        $currencies_id = tep_db_prepare_input(Yii::$app->request->post('currencies_id'));

        $currency_query = tep_db_query("select code from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");
        $currency = tep_db_fetch_array($currency_query);

      $remove_currency = true;
      if ($currency['code'] == DEFAULT_CURRENCY) {
        $remove_currency = false;
?>
              <div class="alert fade in alert-danger">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"><?=ERROR_REMOVE_DEFAULT_CURRENCY?></span>
              </div>
<?php		
      }
	  if (!$remove_currency){
		  $heading = array();
		  $contents = array();		  
		  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</b>');

		  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
		  $contents[] = array('text' => '<br><b>' . $cInfo->title . '</b>');
		  $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" class="btn btn-primary" value="' . IMAGE_CANCEL . '" onclick="resetStatement()">');

		  $box = new box;
		  echo $box->infoBox($heading, $contents);
		  
	  } else {
        $currency_query = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . DEFAULT_CURRENCY . "'");
        $currency = tep_db_fetch_array($currency_query);

        if ($currency['currencies_id'] == $currencies_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_db_query("delete from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");
		echo 'reset';
	  }
		
    }

    public function actionSortOrder()
    {
        $moved_id = (int)$_POST['sort_curr'];
        $ref_array = (isset($_POST['curr']) && is_array($_POST['curr']))?array_map('intval',$_POST['curr']):array();
        if ( $moved_id && in_array($moved_id, $ref_array) ) {
            // {{ normalize
          $order_counter = 0;
          $order_list_r = tep_db_query(
            "SELECT currencies_id, sort_order ".
            "FROM ". TABLE_CURRENCIES ." ".
            "WHERE 1 ".
            "ORDER BY sort_order, title"
          );
          while( $order_list = tep_db_fetch_array($order_list_r) ){
            $order_counter++;
            tep_db_query("UPDATE ".TABLE_CURRENCIES." SET sort_order='{$order_counter}' WHERE currencies_id='{$order_list['currencies_id']}' ");
          }
          // }} normalize
          $get_current_order_r = tep_db_query(
            "SELECT currencies_id, sort_order ".
            "FROM ".TABLE_CURRENCIES." ".
            "WHERE currencies_id IN('".implode("','",$ref_array)."') ".
            "ORDER BY sort_order"
          );
          $ref_ids = array();
          $ref_so = array();
          while($_current_order = tep_db_fetch_array($get_current_order_r)){
            $ref_ids[] = (int)$_current_order['currencies_id'];
            $ref_so[] = (int)$_current_order['sort_order'];
          }

          foreach( $ref_array as $_idx=>$id ) {
            tep_db_query("UPDATE ".TABLE_CURRENCIES." SET sort_order='{$ref_so[$_idx]}' WHERE currencies_id='{$id}' ");
          }

        }
    }
    
    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $code = tep_db_fetch_array(tep_db_query(
            "SELECT code ".
            "FROM ". TABLE_CURRENCIES ." ".
            "WHERE currencies_id = '" . (int)$id . "'"
          ));
        
        if ($code['code'] == DEFAULT_CURRENCY){
            if ($status != 'true'){
               \common\helpers\Translation::init('admin/currencies');
               echo json_encode(array('message' => ERROR_DEFAULT_CURRENCY_INACTIVE, 'messageType' => 'alert-danger'));
               exit();
            }
        }
        tep_db_query("update " . TABLE_CURRENCIES . " set status = '" . ($status == 'true' ? 1 : 0) . "' where currencies_id = '" . (int)$id . "'");
        \common\helpers\Currencies::correctPlatformLanguages();
        echo json_encode(array('message' => 'Currency is updated', 'messageType' => 'alert-success'));
        exit();
    }
    
    
}
