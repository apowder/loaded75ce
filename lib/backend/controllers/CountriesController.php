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
class CountriesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_TAXES_COUNTRIES'];
    
    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('settings', 'locations', 'countries');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('countries/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return countryEdit(0)">'.IMAGE_INSERT.'</a>';

        $this->view->countriesTable = array(
            array(
                'title' => '<input type="checkbox" class="uniform">',
                'not_important' => 2
            ),
            array(
                'title' => TABLE_HEADING_COUNTRY_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_COUNTRY_CODES,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_ACTIVE,
                'not_important' => 0,
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)$_GET['row'];

        return $this->render('index');
    }

    public function actionList(){
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_prepare_input($_GET['search']['value']);
            $search = " and (countries_name like '%" . tep_db_input($keywords) . "%' or countries_iso_code_2 like '%" . tep_db_input($keywords) . "%' or countries_iso_code_3 like '%" . tep_db_input($keywords) . "%')";
        }
		
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "countries_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "countries_iso_code_2 " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "sort_order, countries_name";
                    break;
            }
        } else {
            $orderBy = "countries_name";
        }
		
		$current_page_number = ($start / $length) + 1;
        $responseList = array();
		
 	    $countries_query_raw = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id, status, sort_order, lat, lng, zoom from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' " . $search . "order by ".$orderBy;
		$countries_split = new \splitPageResults($current_page_number, $length, $countries_query_raw, $countries_query_numrows);
		$countries_query = tep_db_query($countries_query_raw);
		  
		while ($countries = tep_db_fetch_array($countries_query)) {
	
			$responseList[] = array(
                                '<input type="checkbox" class="uniform">' . tep_draw_hidden_field('id', $countries['countries_id'], 'class="cell_identify"'),
				$countries['countries_name'],
				$countries['countries_iso_code_2']. '&nbsp;-&nbsp;' . $countries['countries_iso_code_3'],
				'<input type="checkbox" value="'.$countries['countries_id'].'" name="categories_status" class="check_on_off"' . ($countries['status'] == 1 ? ' checked="checked"' : '') . '>'
			);
		}
		
		$response = array(
            'draw' => $draw,
            'recordsTotal' => $countries_query_numrows,
            'recordsFiltered' => $countries_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
	}
	
    public function actionCountriesactions(){
        global $language, $languages_id;
        \common\helpers\Translation::init('admin/countries');
		
        $countries_id = Yii::$app->request->post('countries_id', 0);
        $this->layout = false;
        if ($countries_id){
            $country = tep_db_fetch_array(tep_db_query("select * from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' and countries_id ='" . (int)$countries_id . "'"));
            $cInfo = new \objectInfo($country, false);
            
            $codeTypes = [
                0 => 'numeric',
                1 => 'alphanumeric',
                2 => 'alphabetical',
            ];
            echo '<div class="or_box_head">' . $cInfo->countries_name . '</div>';
            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_NAME . '</div><div>' . $cInfo->countries_name . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_CODE_2 . '</div><div>' . $cInfo->countries_iso_code_2 . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_CODE_3 . '</div><div>' . $cInfo->countries_iso_code_3 . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_STATUS . '</div><div>' . ($cInfo->status == 1 ? "Yes" : "No") . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_SORT_ORDER . '</div><div>' . $cInfo->sort_order . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_ADDRESS_FORMAT . '</div><div>' . $cInfo->address_format_id . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_VAT_CODE_PREFIX . '</div><div>' . $cInfo->vat_code_prefix . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_VAT_CODE_TYPE . '</div><div>' . $codeTypes[$cInfo->vat_code_type] . '</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_VAT_CODE_CHARS . '</div><div>' . $cInfo->vat_code_chars . '</div></div>';
            echo '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<button class="btn btn-edit btn-no-margin" onclick="countryEdit('.$countries_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="countryDelete('.$countries_id.')">' . IMAGE_DELETE . '</button>';
            echo '</div>';
        }
	  
	}
	
    public function actionEdit(){
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/countries');
	  
	  $countries_id = Yii::$app->request->get('countries_id', 0);
 	  $country = tep_db_fetch_array(tep_db_query("select * from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' and countries_id ='" . (int)$countries_id . "'"));
	  $cInfo = new \objectInfo($country, false);

	  $heading = array();
	  $contents = array();	  
	  
	  if($countries_id){
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</b>');
	  } else {
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_COUNTRY . '</b>');
	  }
      

	  $contents = array('form' => tep_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
 	  $languages = \common\helpers\Language::get_languages();
	  $text = '';
	  for ($i=0; $i<sizeof($languages); $i++) {
		if ($i == 0) $text .= '<div class="col_desc">' . TEXT_INFO_COUNTRY_NAME . '</div>';
		$text .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('countries_name[' . $languages[$i]['id'] . ']', \common\helpers\Country::get_country_name($cInfo->countries_id, $languages[$i]['id'])) . '</div>';
	  }
      $contents[] = array('text' => $text);
//      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . tep_draw_input_field('countries_name', $cInfo->countries_name));
      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_2 . '<br>' . tep_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2));
      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_3 . '<br>' . tep_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3));
      $contents[] = array('text' => '<br>' . TEXT_INFO_ADDRESS_FORMAT . '<br>' . tep_draw_pull_down_menu('address_format_id', \common\helpers\Address::get_address_formats(), $cInfo->address_format_id));
      $contents[] = array('text' => '<br>' . TEXT_INFO_STATUS . '<br>' . tep_draw_checkbox_field('status', 1, $cInfo->status));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $cInfo->sort_order));

      $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-primary" onclick="countrySave('.($cInfo->countries_id?$cInfo->countries_id:0).')">
	  <input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-primary" onclick="resetStatement('.$countries_id.')">');

  	  /*$box = new \box;
	  echo $box->infoBox($heading, $contents);*/
      echo tep_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=save');
      if($countries_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</div>';
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_COUNTRY . '</div>';
	  }
      echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
      echo $text;
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_COUNTRY_CODE_2 . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_COUNTRY_CODE_3 . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_ADDRESS_FORMAT . '</div>';
      echo '<div class="main_value">' . tep_draw_pull_down_menu('address_format_id', \common\helpers\Address::get_address_formats(), $cInfo->address_format_id) . '</div>';
      echo '</div>';      
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_SORT_ORDER . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('sort_order', $cInfo->sort_order) . '</div>';
      echo '</div>';

      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_VAT_CODE_PREFIX . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('vat_code_prefix', $cInfo->vat_code_prefix) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_VAT_CODE_TYPE . '</div>';
      echo '<div class="main_value">' . tep_draw_pull_down_menu('vat_code_type', [['id' => 0 , 'text'=> 'numeric'], ['id' => 1 , 'text'=> 'alphanumeric'], ['id' => 2 , 'text'=> 'alphabetical']], $cInfo->vat_code_type) . '</div>';
      echo '</div>';      
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_VAT_CODE_CHARS . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('vat_code_chars', $cInfo->vat_code_chars) . '</div>';
      echo '</div>';
      
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_LATITUDE . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('lat', $cInfo->lat) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_LANGITUTE . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('lng', $cInfo->lng) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_ZOOM . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('zoom', $cInfo->zoom) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      \common\helpers\Translation::init('admin/sms');
      echo '<div class="main_title">' . TEXT_COUNTRY_PHONE_PREFIX . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('dialling_prefix', $cInfo->dialling_prefix) . '</div>';
      echo '</div>';  
	  
      echo '<div class="check_linear">';
      echo tep_draw_checkbox_field('status', 1, $cInfo->status) . '<span>' . TEXT_INFO_STATUS . '</span>';      
      echo '</div>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="countrySave('.($cInfo->countries_id?$cInfo->countries_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement('.$countries_id.')">';
      echo '</div>';
      echo '</form>';
      
	  
	}
	
    public function actionSave() {
        global $language;
        \common\helpers\Translation::init('admin/countries');
        $countries_id = Yii::$app->request->get('countries_id', 0);

//      $countries_name = tep_db_prepare_input($_POST['countries_name']);
        $countries_iso_code_2 = tep_db_prepare_input($_POST['countries_iso_code_2']);
        $countries_iso_code_3 = tep_db_prepare_input($_POST['countries_iso_code_3']);
        $lat = tep_db_prepare_input($_POST['lat']);
        $lng = tep_db_prepare_input($_POST['lng']);
        $zoom = tep_db_prepare_input($_POST['zoom']);
        $address_format_id = tep_db_prepare_input($_POST['address_format_id']);
        $status = tep_db_prepare_input($_POST['status']);
        $sort_order = tep_db_prepare_input($_POST['sort_order']);
        $dialling_prefix = tep_db_prepare_input($_POST['dialling_prefix']);

        $vat_code_prefix = Yii::$app->request->post('vat_code_prefix');
        $vat_code_type = (int) Yii::$app->request->post('vat_code_type');
        $vat_code_chars = (int) Yii::$app->request->post('vat_code_chars');

        $languages = \common\helpers\Language::get_languages();
        if ($countries_id == 0) {
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $countries_name_array = $_POST['countries_name'];

                $language_id = $languages[$i]['id'];

                $countries_name = tep_db_prepare_input($countries_name_array[$language_id]);
                if ($i == 0) {
                    tep_db_query("insert into " . TABLE_COUNTRIES . " (countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id, language_id, status, sort_order, lat, lng, zoom, vat_code_prefix, vat_code_type, vat_code_chars, dialling_prefix) values ('" . tep_db_input($countries_name) . "', '" . tep_db_input($countries_iso_code_2) . "', '" . tep_db_input($countries_iso_code_3) . "', '" . (int) $address_format_id . "', '" . $language_id . "', '" . (int) $status . "', '" . (int) $sort_order . "', '" . (float) $lat . "', '" . (float) $lng . "', '" . (float) $zoom . "', '" . (float) $vat_code_prefix . "', '" . (float) $vat_code_type . "', '" . (float) $vat_code_chars . "', '" . tep_db_input($dialling_prefix) . "')");
                    $id = tep_db_insert_id();
                } else {
                    tep_db_query("insert into " . TABLE_COUNTRIES . " (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id, language_id, status, sort_order, lat, lng, zoom, vat_code_prefix, vat_code_type, vat_code_chars, dialling_prefix) values (" . $id . ", '" . tep_db_input($countries_name) . "', '" . tep_db_input($countries_iso_code_2) . "', '" . tep_db_input($countries_iso_code_3) . "', '" . (int) $address_format_id . "', '" . $language_id . "', '" . (int) $status . "', '" . (int) $sort_order . "', '" . (float) $lat . "', '" . (float) $lng . "', '" . (float) $zoom . "', '" . (float) $vat_code_prefix . "', '" . (float) $vat_code_type . "', '" . (float) $vat_code_chars . "', '" . tep_db_input($dialling_prefix) . "')");
                }
            }
            $action = 'added';
        } else {

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $countries_name_array = $_POST['countries_name'];

                $language_id = $languages[$i]['id'];

                $countries_name = tep_db_prepare_input($countries_name_array[$language_id]);
                tep_db_query("update " . TABLE_COUNTRIES . " set countries_name = '" . tep_db_input($countries_name) . "', countries_iso_code_2 = '" . tep_db_input($countries_iso_code_2) . "', countries_iso_code_3 = '" . tep_db_input($countries_iso_code_3) . "', address_format_id = '" . (int) $address_format_id . "', status='" . $status . "', sort_order='" . $sort_order . "', lat = '" . tep_db_input($lat) . "', lng = '" . tep_db_input($lng) . "', zoom ='" . tep_db_input($zoom) . "', vat_code_prefix ='" . $vat_code_prefix . "', vat_code_type ='" . $vat_code_type . "', vat_code_chars ='" . $vat_code_chars . "', dialling_prefix = '" . tep_db_input($dialling_prefix) . "' where countries_id = '" . (int) $countries_id . "' and language_id='" . $language_id . "'");
            }
            $action = 'updated';
        }


        echo json_encode(array('message' => 'County ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete() {
        global $language;
        \common\helpers\Translation::init('admin/countries');
        $countries_id = Yii::$app->request->post('countries_id', 0);

        if ($countries_id)
            tep_db_query("delete from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "'");

        echo 'reset';
    }
    
    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        tep_db_query("update " . TABLE_COUNTRIES . " set status = '" . ($status == 'true' ? 1 : 0) . "' where countries_id = '" . (int)$id . "'");
    }

    public function actionDeleteSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("delete from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$id . "'");
        }
    }
    
    public function actionApproveSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("update " . TABLE_COUNTRIES . " set status = '1' where countries_id = '" . (int)$id . "'");
        }
    }
    
    public function actionDeclineSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("update " . TABLE_COUNTRIES . " set status = '0' where countries_id = '" . (int)$id . "'");
        }
    }
}
