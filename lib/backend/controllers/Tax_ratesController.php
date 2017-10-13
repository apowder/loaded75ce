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
class Tax_ratesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_TAX_RATES'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'taxes', 'tax_rates');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tax_rates/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="#" class="create_item" onclick="return taxEdit(0)">'.TEXT_INFO_HEADING_NEW_TAX_RATE.'</a>';
	  
	  $this->view->tax_ratesTable = array(
		array(
			'title' => TABLE_HEADING_TAX_RATE_PRIORITY,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_TAX_CLASS_TITLE,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_ZONE,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_TAX_RATE,
			'not_important' => 0,
		),			
	  );

      return $this->render('index');
    }

	public function actionList(){
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (tax_class_title like '%" . $keywords . "%' or tax_class_description like '%" . $keywords . "%')";
        }
		
		$current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "r.tax_priority " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "tc.tax_class_title " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "z.geo_zone_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;					
                default:
                    $orderBy = "tc.tax_class_title";
                    break;
            }
        } else {
            $orderBy = "tc.tax_class_title";
        }		

	    $rates_query_raw = "select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id order by ". $orderBy;
	    $rates_split = new \splitPageResults($current_page_number, $length, $rates_query_raw, $rates_query_numrows);
	    $rates_query = tep_db_query($rates_query_raw);
		
		while ($rates = tep_db_fetch_array($rates_query)) {
	
			$responseList[] = array(
				$rates['tax_priority'] . tep_draw_hidden_field('id', $rates['tax_rates_id'], 'class="cell_identify"'),
				$rates['tax_class_title'],
				$rates['geo_zone_name'],
				\common\helpers\Tax::display_tax_value($rates['tax_rate']).'%',
			);
		}
		
		$response = array(
            'draw' => $draw,
            'recordsTotal' => $rates_query_numrows,
            'recordsFiltered' => $rates_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
	}
	
	public function actionTax_ratesactions(){
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/tax_rates');
		
		$tax_rates_id = Yii::$app->request->post('tax_rates_id', 0);
		$this->layout = false;
		if ($tax_rates_id){
			$rates = tep_db_fetch_array(tep_db_query("select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id and r.tax_rates_id = '" . (int)$tax_rates_id . "'"));
			$trInfo = new \objectInfo($rates);
			$heading = array();
			$contents = array();		

			$heading[] = array('text' => '<b>' . $trInfo->tax_class_title . '</b>');
			echo '<div class="or_box_head">' . $trInfo->tax_class_title . '</div>';
			echo '<div class="row_or_wrapp">';
				echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' .  \common\helpers\Date::date_short($trInfo->date_added) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($trInfo->last_modified) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_RATE_DESCRIPTION . '</div><div>' . $trInfo->tax_description . '</div></div>';
				echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo '<button class="btn btn-edit btn-no-margin" onclick="taxEdit('.$tax_rates_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="taxDelete('.$tax_rates_id.')">' . IMAGE_DELETE . '</button>';
			echo '</div>';
			$contents[] = array('align' => 'center', 'text' => '<input type="button" value="' . IMAGE_EDIT . '" class="btn btn-primary" onclick="taxEdit('.$tax_rates_id.')">&nbsp;'.
			'<input type="button" value="' . IMAGE_DELETE . '" class="btn btn-primary" onclick="taxDelete('.$tax_rates_id.')">');

			$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . \common\helpers\Date::date_short($trInfo->date_added));
			$contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . \common\helpers\Date::date_short($trInfo->last_modified));
			$contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . $trInfo->tax_description);
			/* $box = new \box;
			echo $box->infoBox($heading, $contents); */
		}
	  
	}
	
    public function actionEdit(){
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/tax_rates');
	  
	  $tax_rates_id = Yii::$app->request->get('tax_rates_id', 0);
	  $rates = tep_db_fetch_array(tep_db_query("select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id and r.tax_rates_id = '" . (int)$tax_rates_id . "'"));
	  $trInfo = new \objectInfo($rates);

	  $heading = array();
	  $contents = array();	  
	  
	  if($tax_rates_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</div>';
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</div>';
	  }
		echo tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save');
		echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CLASS_TITLE . '</div><div class="main_value">' . \common\helpers\Tax::tax_classes_pull_down('name="tax_class_id" style="font-size:10px" class="form-control"', $trInfo->tax_class_id) . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ZONE_NAME . '</div><div class="main_value">' . \common\helpers\Zones::geo_zones_pull_down('name="tax_zone_id" style="font-size:10px" class="form-control"', $trInfo->geo_zone_id) . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_TAX_RATE . '</div><div class="main_value">' . tep_draw_input_field('tax_rate', $trInfo->tax_rate, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_RATE_DESCRIPTION . '</div><div class="main_value">' . tep_draw_input_field('tax_description', $trInfo->tax_description, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_TAX_RATE_PRIORITY . '</div><div class="main_value">' . tep_draw_input_field('tax_priority', $trInfo->tax_priority, 'class="form-control"') . '</div></div>';
		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="taxSave('.($trInfo->tax_rates_id?$trInfo->tax_rates_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';
      $contents = array('form' => tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . \common\helpers\Tax::tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $trInfo->tax_class_id));
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . \common\helpers\Zones::geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $trInfo->geo_zone_id));
      $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE . '<br>' . tep_draw_input_field('tax_rate', $trInfo->tax_rate));
      $contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . tep_draw_input_field('tax_description', $trInfo->tax_description));
      $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE_PRIORITY . '<br>' . tep_draw_input_field('tax_priority', $trInfo->tax_priority));

      $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-primary" onclick="taxSave('.($trInfo->tax_rates_id?$trInfo->tax_rates_id:0).')">
	  <input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-primary" onclick="resetStatement()">');

  	/*  $box = new \box;
	  echo $box->infoBox($heading, $contents);*/
	  
	}
	
    public function actionSave(){
      global $language;
      \common\helpers\Translation::init('admin/tax_rates');	
	  
	  $tax_rates_id = Yii::$app->request->get('tax_rates_id', 0);

	  if($tax_rates_id == 0){
        $tax_zone_id = tep_db_prepare_input($_POST['tax_zone_id']);
        $tax_class_id = tep_db_prepare_input($_POST['tax_class_id']);
        $tax_rate = tep_db_prepare_input($_POST['tax_rate']);
        $tax_description = tep_db_prepare_input($_POST['tax_description']);
        $tax_priority = tep_db_prepare_input($_POST['tax_priority']);

        tep_db_query("insert into " . TABLE_TAX_RATES . " (tax_zone_id, tax_class_id, tax_rate, tax_description, tax_priority, date_added) values ('" . (int)$tax_zone_id . "', '" . (int)$tax_class_id . "', '" . tep_db_input($tax_rate) . "', '" . tep_db_input($tax_description) . "', '" . tep_db_input($tax_priority) . "', now())");

		$action		 = 'added';
	  } else {
        $tax_zone_id = tep_db_prepare_input($_POST['tax_zone_id']);
        $tax_class_id = tep_db_prepare_input($_POST['tax_class_id']);
        $tax_rate = tep_db_prepare_input($_POST['tax_rate']);
        $tax_description = tep_db_prepare_input($_POST['tax_description']);
        $tax_priority = tep_db_prepare_input($_POST['tax_priority']);

        tep_db_query("update " . TABLE_TAX_RATES . " set tax_rates_id = '" . (int)$tax_rates_id . "', tax_zone_id = '" . (int)$tax_zone_id . "', tax_class_id = '" . (int)$tax_class_id . "', tax_rate = '" . tep_db_input($tax_rate) . "', tax_description = '" . tep_db_input($tax_description) . "', tax_priority = '" . tep_db_input($tax_priority) . "', last_modified = now() where tax_rates_id = '" . (int)$tax_rates_id . "'");

		$action		 = 'updated';
	  }
	
	echo json_encode(array('message' => 'Tax rate is ' . $action, 'messageType' => 'alert-success'));
        
	}

	
    public function actionDelete(){
      global $language;
      \common\helpers\Translation::init('admin/tax_rates');	
        $tax_rates_id = Yii::$app->request->post('tax_rates_id', 0);
		
		if ($tax_rates_id)
			tep_db_query("delete from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$tax_rates_id . "'");

		echo 'reset';
		
	}
}
