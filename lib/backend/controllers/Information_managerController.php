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
use backend\components\Information;
use \common\helpers\Translation;
//Yii::import('application.components.Information', true);
/**
 * default controller to handle user requests.
 */
class Information_managerController extends Sceleton  {
	  
    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_INFORMATION_MANAGER'];
    
    public function __construct($id, $module=null){
      Translation::init('admin/information_manager');
      parent::__construct($id, $module);
    }    
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('design_controls', 'information_manager');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/index'), 'title' => MANAGER_INFORMATION);
      $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['information_manager/create']).'" class="create_item"><i class="icon-file-o"></i>'.TEXT_ADD_NEW_PAGE.'</a>';
      
      $this->view->headingTitle = MANAGER_INFORMATION;
      $this->view->infoTable = array(     
            array(
                'title' => '<input type="checkbox" class="uniform">',
                'not_important' => 2
            ),
            array(
                'title' => TITLE_INFORMATION,
                'not_important' => 0
            ),
      );
      if ( \common\classes\platform::isMulti() ) {
        $this->view->infoTable[] = array(
          'title' => TABLE_HEAD_PLATFORM_NAME,
          'not_important' => 0.
        );
        $this->view->infoTable[] = array(
          'title' => TABLE_HEAD_PLATFORM_PAGE_ASSIGN,
          'not_important' => 0.
        );
      }else{
        $this->view->infoTable[] = array(
          'title' => PUBLIC_INFORMATION,
          'not_important' => 0,
        );
      }

	  return $this->render('index', array(
      'platforms' => \common\classes\platform::getList(false),
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
    ));
    }
  
  public function actionList(){
    global $languages_id; 
     $draw = Yii::$app->request->get('draw', 1);
     $start = Yii::$app->request->get('start', 0);
     $length = Yii::$app->request->get('length', 10);
     if( $length == -1 ) $length = 10000;
     
       $search = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = "(info_title like '%" . $keywords . "%' or description like '%" . $keywords . "%')";
        } else {
            $search_condition = "1";
        }
        $list_query_raw = 
                "select * ".
                "from " . TABLE_INFORMATION . " ".
                "where languages_id='".$languages_id."' and platform_id=".\common\classes\platform::firstId()." and affiliate_id = 0 ".
                " and {$search_condition} ".
                "order by v_order, info_title ";
        $current_page_number = ($start / $length) + 1;
        $listing_split = new \splitPageResults(
          $current_page_number, $length, $list_query_raw, $listing_query_numrows, 'information_id'
        );

        $db_query = tep_db_query($list_query_raw);
     
        $responseList = array();
        if (tep_db_num_rows($db_query)>0){
          $visible_flags = array();
          if ( \common\classes\platform::isMulti() ) {
            $get_visible_flags_r = tep_db_query(
              "select information_id, platform_id " .
              "from " . TABLE_INFORMATION . " " .
              "where languages_id='" . $languages_id . "' and affiliate_id = 0 and visible=1 " .
              ""
            );
            while ($_visible = tep_db_fetch_array($get_visible_flags_r)) {
              $visible_flags[$_visible['information_id'] . '^' . $_visible['platform_id']] = 1;
            }
          }else{
            $visible_flags = array();
          }
          while( $val = tep_db_fetch_array($db_query) ) {
            $row = array(
              '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $val['information_id'] . '">',
              '<div class="tp_title click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['information_manager/edit', 'info_id' => $val['information_id']]) . '">'.($val['page_title']?$val['page_title']:$val['info_title']).'</div>',
            );
            if ( \common\classes\platform::isMulti() ) {
              $platforms = '';
              $public_checkbox = '';
              foreach ( \common\classes\platform::getList(false) as $platform_variant ) {
                $platforms .= '<div id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '"' . (isset($visible_flags[$val['information_id']."^".$platform_variant['id']]) ? '' : ' class="platform-disable"') . '>'.$platform_variant['text'].'</div>';

                $public_checkbox .= '<div>'.
                  (isset($visible_flags[$val['information_id']."^".$platform_variant['id']])?
                    '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status['.$platform_variant['id'].']" class="check_on_off" checked="checked" data-id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '">' :
                    '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status['.$platform_variant['id'].']" class="check_on_off" data-id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '">'
                  ).'</div>';
              }
              $row[] = '<div class="platforms-cell">'.$platforms.'</div>';
              $row[] = '<div class="platforms-cell-checkbox">'.$public_checkbox.'</div>';
            }else{
              $row[] = ($val['visible'] == 1 ? '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status" class="check_on_off">');
            }
            $responseList[] = $row;
          }
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => intval($listing_query_numrows),
            'recordsFiltered' => intval($listing_query_numrows),
            'data' => $responseList
        );
        echo json_encode($response);
  }
    

  public function actionCreate()
  {
    global $language;

    $back = 'information_manager';
    if (isset($_GET['back'])) {
      $back = $_GET['back'];
    }
    $this->view->backOption = $back;

    $this->selectedMenu = array('design_controls', 'information_manager');

    $pages_data = array();
    $languages = \common\helpers\Language::get_languages();
    $edit_page_title = '';
    $platforms = \common\classes\platform::getList(false);
    foreach( $platforms as $platform ) {
      $page_data = array();
      foreach ($languages as $i => $_language) {
        $_lang_id = $_language['id'];
        $page_data[$i] = array();
        $page_data[$i]['code'] = $_language['code'];

        $languages[$i]['logo'] = $_language['image'];

        $page_data[$i]['c_page_title'] = tep_draw_input_field('page_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['page_title']) ? $data['page_title'] : '', 'class="form-control"');
        $page_data[$i]['c_info_title'] = tep_draw_input_field('info_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['info_title']) ? $data['info_title'] : '', 'class="form-control"');
        $page_data[$i]['c_links'] = '<a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/pagelinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_PAGE_LINKS.'</a><a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/productslinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_PRODUCTS_LINKS.'</a><a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/categorieslinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_CATEGORIES_LINKS.'</a>';
        $page_data[$i]['c_description'] = tep_draw_textarea_field('description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['description']) ? $data['description'] : '', 'class="form-control ckeditor" id="desc'.$_lang_id.$platform['id'].'"');

        $page_data[$i]['c_seo_page_name'] = tep_draw_input_field('seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['seo_page_name']) ? $data['seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['c_old_seo_page_name'] = tep_draw_input_field('old_seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_title'] = tep_draw_input_field('meta_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['meta_title']) ? $data['meta_title'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_key'] = tep_draw_textarea_field('meta_key[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_key']) ? $data['meta_key'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_description'] = tep_draw_textarea_field('meta_description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_description']) ? $data['meta_description'] : '', 'class="form-control"');
      }
      $pages_data[$platform['id']]['lang'] = $page_data;
      $pages_data[$platform['id']]['visible'] = 0;
      $pages_data[$platform['id']]['platform_id'] = $platform['id'];
    }
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/edit'), 'title' => TEXT_ADD_NEW_PAGE);

    return $this->render('edit', array(
      'information_id' => 0,
      'languages' => $languages,
      'platforms' => \common\classes\platform::getList(false),
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
      'pages_data' => $pages_data,
      'link_href_back' => \Yii::$app->urlManager->createUrl(['information_manager/index', 'back' => 'information_manager']),
    ));
  }

  public function actionEdit()
  {
    global $language, $languages_id;

    //$this->layout = false;

    $info_id = Yii::$app->request->get('info_id', 0);

    //$this->selectedMenu = array('seo_cms', 'cms', 'information_manager');
    $this->selectedMenu = array('design_controls', 'information_manager');

    $pages_data = array();
    $languages = \common\helpers\Language::get_languages();
    $edit_page_title = '';
    $platforms = \common\classes\platform::getList(false);
    foreach( $platforms as $platform ) {
      $page_data = array();
      $data_visible = false;
      $no_logged = false;
      foreach ($languages as $i => $_language) {
        $_lang_id = $_language['id'];
        $languages[$i]['logo'] = $_language['image'];

        $data = Information::read_data($info_id, $_lang_id, $platform['id']);
        if ($_lang_id == $languages_id) {
          //$edit_page_title = $data['page_title'];
        }

        $page_data[$i] = $data;
        if (!is_array($page_data[$i])) $page_data[$i] = array();
        $page_data[$i]['code'] = $_language['code'];

        $page_data[$i]['c_page_title'] = tep_draw_input_field('page_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['page_title']) ? $data['page_title'] : '', 'class="form-control"');
        $page_data[$i]['c_info_title'] = tep_draw_input_field('info_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['info_title']) ? $data['info_title'] : '', 'class="form-control"');
        $page_data[$i]['c_links'] = '<a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/pagelinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_PAGE_LINKS.'</a><a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/productslinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_PRODUCTS_LINKS.'</a><a href=" ' . \Yii::$app->urlManager->createUrl(['information_manager/categorieslinks', 'id_ckeditor'=>'desc'.$_lang_id.$platform['id']]) . '" class="btn popupLinks">'.TEXT_CATEGORIES_LINKS.'</a>';
        $page_data[$i]['c_description'] = tep_draw_textarea_field('description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['description']) ? $data['description'] : '', 'class="form-control ckeditor" id="desc'.$_lang_id.$platform['id'].'"');

        $page_data[$i]['c_seo_page_name'] = tep_draw_input_field('seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['seo_page_name']) ? $data['seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['c_old_seo_page_name'] = tep_draw_input_field('old_seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '', 'class="form-control seo-input-field"');
        $page_data[$i]['c_old_seo_page_name_clear'] = (isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '');
        $page_data[$i]['c_meta_title'] = tep_draw_input_field('meta_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['meta_title']) ? $data['meta_title'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_key'] = tep_draw_textarea_field('meta_key[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_key']) ? $data['meta_key'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_description'] = tep_draw_textarea_field('meta_description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_description']) ? $data['meta_description'] : '', 'class="form-control"');

        if ( $data['visible'] ) {
          $data_visible = true;
        }
        if ( $data['no_logged'] ) {
          $no_logged = true;
        }
      }
      $pages_data[$platform['id']]['lang'] = $page_data;
      $pages_data[$platform['id']]['visible'] = $data_visible?'1':'0';;
      $pages_data[$platform['id']]['platform_id'] = $platform['id'];
      $pages_data[$platform['id']]['no_logged'] = $no_logged?'1':'0';
    }

    $information_id = Yii::$app->request->get('info_id', 0);

    //$button=array("Update");
    //$title=($information_id ?  "" . EDIT_ID_INFORMATION . " $information_id" : "" . ADD_QUEUE_INFORMATION);
//    echo tep_draw_form('edit_info',FILENAME_INFORMATION_MANAGER.'/update', '', 'post', 'id="edit_info"');
//    echo tep_draw_hidden_field('information_id', "$information_id");
    //$this->view->tabList = Information::form(($information_id ? 'edit': 'Added'), $information_id, $title);

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/edit'), 'title' => TEXT_EDITING . ' '.$edit_page_title.' page');

    $platforms = \common\classes\platform::getList(false);
    $some_need_login = false;
    foreach ($platforms as $item){
      if ($item['need_login']){
        $some_need_login = true;
      }
    }


    return $this->render('edit', array(
      'some_need_login' => $some_need_login,
      'information_id' => $information_id,
      'languages' => $languages,
      'platforms' => $platforms,
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
      'pages_data' => $pages_data,
      'link_href_back' => \Yii::$app->urlManager->createUrl(['information_manager/', 'info_id' => $information_id, 'back' => 'information_manager']),
    ));
  }

  public function actionPageSave(){
    global $languages_id, $language, $messageStack;

    $this->view->errorMessageType = 'success';
    $this->view->errorMessage = '';
    $this->layout = false;

    //$popup = (int) Yii::$app->request->post('popup');
    $info_id = (int)Yii::$app->request->post('information_id');

    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    $platforms = \common\classes\platform::getList(false);

    $show_edit = false;
    if ($info_id > 0) {
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        foreach( $platforms as $platform ) {
          Information::update_information($_POST, $languages[$i]['id'], $platform['id']);
          Information::update_no_logged($_POST['no_logged'], $languages[$i]['id'], $platform['id'], $_POST['information_id']);
        }
      }
      Yii::$app->request->setQueryParams(['info_id'=>$info_id]);
      //$this->view->errorMessage = UPDATE_ID_INFORMATION.$info_id;
      $this->view->errorMessage = MESSAGE_INFORMATION_UPDATED;
    } else {
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        foreach( $platforms as $platform ) {
          $_POST['information_id'] = Information::update_information($_POST, $languages[$i]['id'], $platform['id']);
          $info_id = Information::update_no_logged($_POST['no_logged'], $languages[$i]['id'], $platform['id'], $_POST['information_id']);
        }
      }
      $this->view->errorMessage = defined('TEXT_INFO_SAVED')?TEXT_INFO_SAVED:'Page added';
      $show_edit = true;
      Yii::$app->request->setQueryParams(['info_id'=>$_POST['information_id']]);
    }
    
    if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
        $ext::saveInfoLinks($info_id, $_POST);
    }
//    if ($popup == 1) {
//      return $this->render('cat_main_box');
//    }

    if ($messageStack->size > 0) {
      $this->view->errorMessage = $messageStack->output(true);
      $this->view->errorMessageType = $messageStack->messageType;
    }
    echo $this->render('error');
    if ( $show_edit ) {
      return $this->actionEdit();
    }

  }

  public function actionPageactions(){
    global $language, $languages_id;

    $this->layout = false;
//
    $information_id = Yii::$app->request->post('info_id');

    $info_data = tep_db_fetch_array(tep_db_query(
      "SELECT information_id, info_title, /*page,*/ date_added, last_modified ".
      "FROM ".TABLE_INFORMATION." ".
      "WHERE information_id='".(int)$information_id."' ".
      " AND languages_id='".(int)$languages_id."' ".
      " AND platform_id='".\common\classes\platform::firstId()."' ".
      " AND affiliate_id=0"
    ));
    if ( !is_array($info_data) ) $info_data = array();
    if ( $info_data['information_id']>0 ) {
      $info_data['link_href_edit'] = \Yii::$app->urlManager->createUrl(['information_manager/edit', 'info_id' => $info_data['information_id'], 'back' => 'information_manager']);
      $info_data['link_href_delete'] = \Yii::$app->urlManager->createUrl(['information_manager/delete_confirm', 'info_id' => $info_data['information_id'], 'back' => 'information_manager']);
    }

    $iInfo = new \objectInfo($info_data);


//    $heading = array();
//    $contents = array();

    return $this->render('pageactions.tpl', ['iInfo' => $iInfo]);

}

  public function actionConfirmDelete()
  {
    global $language, $languages_id;

    $this->layout = false;
//
    $information_id = Yii::$app->request->post('info_id');

    $info_data = tep_db_fetch_array(tep_db_query(
      "SELECT information_id, info_title, /*page,*/ date_added, last_modified ".
      "FROM ".TABLE_INFORMATION." ".
      "WHERE information_id='".(int)$information_id."' ".
      " AND languages_id='".(int)$languages_id."' ".
      " AND platform_id='".\common\classes\platform::firstId()."' ".
      " AND affiliate_id=0"
    ));
    if ( !is_array($info_data) ) $info_data = array();
 
    $iInfo = new \objectInfo($info_data);

    return $this->render('pageactions_delete_confirm.tpl', ['iInfo' => $iInfo]);
  }
  
  public function actionUpdate(){
    global $language;
    
    $this->layout = false;

    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      Information::update_information($_POST, $languages[$i]['id']);
    }
    $this->redirect('list');
  }

  public function actionStatusChangeSelected(){
    global $language;
    $this->layout = false;

    $selected_ids = Yii::$app->request->post('selected_ids',array());
    $status = Yii::$app->request->post('status');
    foreach( $selected_ids as $info_id ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')));
    }
    //$this->redirect('list');
  }
  public function actionSwitchStatus()
  {
    global $language;

    $this->layout = false;

    $switch_type = Yii::$app->request->post('type','page_status');
    $info_id = Yii::$app->request->post('id',0);
    $status = Yii::$app->request->post('status');
    if ( $switch_type=='page_status' ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')));
    }elseif ( preg_match('/^page_status\[(\d+)\]$/',$switch_type, $get_platform) ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')), $get_platform[1]);
    }
    $this->redirect('list');
  }

  public function actionAdd(){
    global $language;  
    
    $this->layout = false;
    
    $insert_id = '';
    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      Information::add_information($_POST, $languages[$i]['id']);
    }
    $this->redirect('list');    
  }
  
  public function actionDelete(){
    
    $this->layout = false;
    
    $information_id = Yii::$app->request->getBodyParam('info_id', 0);
    
    if ($information_id>0) {
      Information::delete_information($information_id);
      if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
          $ext::deleteInfoLinks($information_id);
      }
    }
    $this->actionList();
    //$this->redirect('list');
  }
  public function actionDeleteSelected(){
    $this->layout = false;

    $selected_ids = Yii::$app->request->getBodyParam('selected_ids', array());

    foreach ($selected_ids as $selected_id) {
      Information::delete_information($selected_id);
    }

  }
	public function actionPagelinks(){
		global $languages_id;
		$this->layout = false;
		$list_query_raw = 
                "select * ".
                "from " . TABLE_INFORMATION . " ".
                "where languages_id='".$languages_id."' and platform_id=".\common\classes\platform::firstId()." and affiliate_id = 0 ".
                "order by v_order, info_title ";
		$db_query = tep_db_query($list_query_raw);
    $response = array();
    if (tep_db_num_rows($db_query)>0){
      while( $val = tep_db_fetch_array($db_query) ) {
				$response[] = $val;
			}
		}
		return $this->render('pagelinks.tpl', ['response' => $response]);
	}

    function actionProductslinks()
        {
            $this->layout = FALSE;

            global $languages_id, $language;
            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $currencies = new \common\classes\currencies();

            $header     = '';
            $script     = '';
            $delete_btn = '';
            $form_html  = '';

            $fields = array();

            $languages = \common\helpers\Language::get_languages();
                // Insert
            $header = IMAGE_INSERT;
            $gapInfo   = new \objectInfo( array() );
            $gap_product_html = '<div class="search"><input type="text" value="" placeholder="Enter your keywords" name="keywords" autocomplete="off" class="form-control" onpaste="return false"></div>';
            $gap_product_html .= tep_draw_hidden_field( 'products_id', 0 ) . tep_draw_hidden_field('products_link');
            $fields[] = array( 'type' => 'field', 'title' => '', 'value' => $gap_product_html );
            $script = '
                        <script type="text/javascript">

                        </script>
                        ';
            ?>
                            <?php
                                foreach( $fields as $field ) {
                                    if( isset( $field['title'] ) ) $field_title = $field['title']; else $field_title = '';
                                    if( isset( $field['name'] ) ) $field_name = $field['name']; else $field_name = '';
                                    if( isset( $field['value'] ) ) $field_value = $field['value']; else $field_value = '';
                                    if( isset( $field['type'] ) ) $field_type = $field['type']; else $field_type = 'text';
                                    if( isset( $field['class'] ) ) $field_class = $field['class']; else $field_class = '';
                                    if( isset( $field['required'] ) ) $field_required = '<span class="fieldRequired">* Required</span>'; else $field_required = '';
                                    if( isset( $field['maxlength'] ) ) $field_maxlength = 'maxlength="' . $field['maxlength'] . '"'; else $field_maxlength = '';
                                    if( isset( $field['size'] ) ) $field_size = 'size="' . $field['size'] . '"'; else $field_size = '';
                                    if( isset( $field['post_html'] ) ) $field_post_html = $field['post_html']; else $field_post_html = '';
                                    if( isset( $field['pre_html'] ) ) $field_pre_html = $field['pre_html']; else $field_pre_html = '';
                                    if( isset( $field['cols'] ) ) $field_cols = $field['cols']; else $field_cols = '70';
                                    if( isset( $field['rows'] ) ) $field_rows = $field['rows']; else $field_rows = '15';

                                    if( $field_type == 'hidden' ) {
                                        $form_html .= tep_draw_hidden_field( $field_name, $field_value );
                                    } elseif( $field_type == 'field' ) {
                                        echo '<div class="pageLinksWrapper">' . $field_value . '</div>';
                                    } elseif( $field_type == 'textarea' ) {

                                        $field_html = tep_draw_textarea_field( $field_name, 'soft', $field_cols, $field_rows, $field_value );

                                        echo '<div class="main_row">';
                                        echo '<div class="main_title">' . $field_title . '</div>';
                                        echo '<div class="main_value">' . $field_pre_html . $field_html .  $field_required . $field_post_html . '</div>';
                                        echo ' </div>';
                                    } else {
                                        echo '<div class="main_row">';
                                        echo '<div class="main_title">' . $field_title . '</div>';
                                        echo '<div class="main_value">' . $field_pre_html . '<input type="' . $field_type . '" name="'.$field_name . '" value="' . $field_value . ' ' . $field_maxlength . ' ' . $field_size . '" class="'.$field_class.'" onpaste="return false"></div>';
                                        echo ' </div>';
                                    }
                                }
                            ?>
            <div class="pageLinksButton">
                <button class="btn btn-primary"><?php echo IMAGE_INSERT;?></button>
            </div>

            <?php echo $form_html; ?>

<script type="text/javascript">
    function searchSuggestSelected(id, value) {
        $('input[name="keywords"]').val(value);
        $('input[name="products_id"]').val(id);
        $('input[name="products_link"]').val('catalog/product?products_id='+id);
        return false;
    }
  (function($){
    $(function(){
      var input_s = $('.search input');
      input_s.attr({
        autocomplete:"off"
      });

      input_s.keyup(function(e){
        jQuery.get('index/search-suggest', {
          keywords: $(this).val()
        }, function(data){
          $('.suggest').remove();
          $('.search').append('<div class="suggest">'+data+'</div>')
        })
      });
      input_s.blur(function(){
        setTimeout(function(){
          $('.suggest').hide()
        }, 200)
      });
      input_s.focus(function(){
        $('.suggest').show()
      })
      var oEditor = CKEDITOR.instances.<?php echo $_GET['id_ckeditor']?>;
      oEditor.focus();
			if(oEditor.mode == 'wysiwyg'){
      $('.pageLinksButton .btn').click(function(){
          /**/
        if($('input[name="keywords"]').val() != ''){
            jQuery.get('information_manager/seoproductsname', {
          products_id: $('input[name="products_id"]').val()
        }, function(data){            
          $('input[name="products_link"]').val(data);
          //$('.search').append('<div class="suggest">'+data+'</div>')
        })
        //console.log($('input[name="products_link"]').val());
            oEditor.focus();
            if(oEditor.getSelection().getRanges()[0].collapsed == false){
                var fragment = oEditor.getSelection().getRanges()[0].extractContents();
                var container = CKEDITOR.dom.element.createFromHtml("<a href='"+$('input[name="products_link"]').val()+"' />", oEditor.document);
                fragment.appendTo(container);
                oEditor.insertElement(container);
            }else{
                var html = "<a href='"+$('input[name="products_link"]').val()+"'>"+$('input[name="keywords"]').val()+"</a>";       
                var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                oEditor.insertElement( newElement );
            }
        }
        $(this).parents('.popup-box-wrap').remove();
    })
			}else{
				$('.pageLinksWrapper').html('<?php echo TEXT_PLEASE_TURN;?>');
				$('.pageLinksButton').hide();
			}
    })
  })(jQuery)
</script>
            <?php echo $script; ?>
        <?php
        }
function actionCategorieslinks()
        {
            $this->layout = FALSE;

            global $languages_id, $language;
            $item_id = (int) Yii::$app->request->post( 'item_id' );
            echo '<div class="pageLinksWrapper">'.tep_draw_pull_down_menu('category_id', \common\helpers\Categories::get_category_path(), '', 'class="form-control"') . '</div>';
            ?>

            <div class="pageLinksButton">
                <button class="btn btn-primary"><?php echo IMAGE_INSERT;?></button>
            </div>

<script type="text/javascript">
  (function($){
    $(function(){
			var oEditor = CKEDITOR.instances.<?php echo $_GET['id_ckeditor']?>;
			if(oEditor.mode == 'wysiwyg'){			
      $('.pageLinksButton .btn').click(function(){
        if($('select[name="category_id"]').val() != ''){            
            oEditor.focus();
            if(oEditor.getSelection().getRanges()[0].collapsed == false){
            var fragment = oEditor.getSelection().getRanges()[0].extractContents();
            var container = CKEDITOR.dom.element.createFromHtml("<a href='"+$('select[name="category_id"]').val()+"' />", oEditor.document);
            fragment.appendTo(container);
            oEditor.insertElement(container);
            }else{
            var html = "<a href='"+$('select[name="category_id"]').val()+"'>"+$('select[name="category_id"] option:selected').text()+"</a>";
            var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
            oEditor.insertElement( newElement );
            }
        }
        $(this).parents('.popup-box-wrap').remove();
    })
		}else{
				$('.pageLinksWrapper').html('<?php echo TEXT_PLEASE_TURN;?>');
				$('.pageLinksButton').hide();
		}
    })
  })(jQuery)
</script>
            <?php //echo $script; ?>
        <?php
        }
function actionSeoproductsname($products_id){
    global $languages_id, $HTTP_SESSION_VARS;
  $product_query = tep_db_query("select p.products_seo_page_name as products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION .  " pd1 on pd.products_id = pd1.products_id and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' and pd1.language_id = '" . (int)$languages_id . "' where pd.products_id = '" . (int)$products_id . "' and pd.language_id = '" . (int)$languages_id . "' and pd.affiliate_id = '0' and p.products_id = pd.products_id");

  $product = tep_db_fetch_array($product_query);
  
    if($product['products_name']){
        $url_product = $product['products_name'];
    }else{
        $url_product = '/catalog/product?products_id='.$products_id;
    }
    return $url_product;
}
}
