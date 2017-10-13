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
use \common\helpers\Translation;
/**
 * default controller to handle user requests.
 */
class Brand_managerController extends Controller
{
	/**
	 * Index action is the default action in a controller.
	 */
  public function __construct($id, $module=null){
    Translation::init('admin/brand_manager');
    parent::__construct($id, $module);
  }   
   
	public function actionIndex()
	{
    global $languages_id, $language, $login_id;

    $this->selectedMenu = array('catalog', 'brand_manager');
    $this->navigation[] = array('link' => $this->createUrl('brand_manager/'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    $this->view->brandsTable =  array(
            array(
                'title' => TABLE_HEADING_MANUFACTURERS,
                'not_important' => 0
            ),
            array(
                'title' => '',
                'not_important' => 0
            ),
        );
    $length = Yii::app()->request->getParam('length', 10); 
    $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " order by manufacturers_name";

    $manufacturers_query = tep_db_query($manufacturers_query_raw);
    $this->view->brandsTableData = $this->view->productsTableData = array();

    $this->view->productsTable = array(
                                      array('title' => 'PRODUCT CODE'),
                                      array('title' => 'NAME'),
                                      array('title' => 'PRICE'),
                                      array('title' => 'PRICE(gross)'),
                                      array('title' => 'STK'),
                                      array('title' => 'VIS'),
                                      array('title' => 'ACTION'),
                                     );
    $i = 0;
    $mID = Yii::app()->request->getParam('mID', 0);
   
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) 
    {
      $manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers['manufacturers_id'] . "'");
      $manufacturer_products = tep_db_fetch_array($manufacturer_products_query);
      $mInfo_array = array_merge($manufacturers, $manufacturer_products);

      $this->view->brandsTableData[] = array('data' =>  '<b>' . $manufacturers['manufacturers_name'].'</b>'.'<input class="cell_identify" type="hidden" value="' . $manufacturers['manufacturers_id'] . '">',  'not_important' => 0);

      if ($i == 0 && $mID == 0){
        $mID = $manufacturers['manufacturers_id'];
      }
      $i++;
    }

    if($_GET['filter']==1 || $_GET['filter']==''){
      $s1='selected';
    }elseif($_GET['filter']==2){
      $s2='selected';
    }elseif($_GET['filter']==3){
      $s3='selected';
    }elseif($_GET['filter']==4){
      $s4='selected';
    }
    
    $errorMessage = Yii::app()->request->getParam('errorMessage', 0);
    if (tep_not_null($errorMessage)){
      $this->view->errorMessage    = $errorMessage;
      $this->view->errorMessageType = Yii::app()->request->getParam('errorMessageType', 0);
    }
    $this->render('index', array('mID'=>$mID, 'filter' => '<b>Filter:</b> <select name="filter" onchange="return getListproducts()"><option value="1" '.$s1.'>Show Visible</options><option value="2" '.$s2.'>Show Invisible</options><option value="3" '.$s3.'>Products out of stock</options><option value="4" '.$s4.'>Show All</options></select>',
      'total' => sizeof($this->view->brandsTableData), ));
  }

  public function actionListProducts(){
    global $languages_id, $language, $login_id;

    
     $draw = Yii::app()->request->getParam('draw', 1);
     $mID = Yii::app()->request->getParam('mID');
    
            if($_GET['order']=='model'){ $order='p.products_model';
            }elseif($_GET['order']=='name'){ $order='pd.products_name';
            }elseif($_GET['order']=='price'){ $order='p.products_price';
            }elseif($_GET['order']=='stk'){ $order='p.products_quantity';
            }elseif($_GET['order']=='vis'){ $order='p.products_status';
            }else{ $order='p.products_id'; }

            if($_GET['filter']==1 || $_GET['filter']==''){
              $ff=' and p.products_status=1';
            }elseif($_GET['filter']==2){
              $ff=' and p.products_status=0';
            }elseif($_GET['filter']==3){
              $ff= ' and p.products_quantity<=0 ';
            }
            else
              $ff='';

            if (isset($_GET['search'])) 
            {
              $search = tep_db_input(tep_db_prepare_input($_GET['search']));
              $products_query_raw = "select * from ".TABLE_PRODUCTS." p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on (p.products_id = pd.products_id and pd.language_id='".intval($languages_id)."') where pd.affiliate_id = 0 " . (tep_session_is_registered('login_vendor')?" and p.vendor_id = '" . $login_id . "'":''). " and manufacturers_id = '" . intval($mID) ."' and (pd.products_name like '%" . $search . "%' or p.products_model like '%" . $search . "%') " . $ff." group by p.products_id ORDER BY ".$order;
            }
            else
            {
              $products_query_raw = "select * from ".TABLE_PRODUCTS." p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on (p.products_id = pd.products_id and pd.language_id='".intval($languages_id)."') where pd.affiliate_id = 0 " . (tep_session_is_registered('login_vendor')?" and p.vendor_id = '" . $login_id . "'":''). " and manufacturers_id = '" . intval($mID) ."' " . $ff." group by p.products_id ORDER BY ".$order;
            }


            $products_query = tep_db_query($products_query_raw);

            $responseList = array();
            if ($products_query_raw!="" && tep_db_num_rows($products_query)>0)
            {
              while ($row = tep_db_fetch_array($products_query))
              {
                if($price_qwy=tep_db_query("select specials_new_products_price from ".TABLE_SPECIALS." where products_id=".$row['products_id']))
                {
                  if($price_sel=tep_db_fetch_array($price_qwy)){
                    if($row['products_price']>0){
                      $discount=100*($row['products_price'] - $price_sel['specials_new_products_price'])/$row['products_price'];
                    }
                  }
                }else{
                  $discount=0;
                }
                $price_gr=\common\helpers\Tax::add_tax($row['products_price'], \common\helpers\Tax::get_tax_rate($row['products_tax_class_id']));
                $status=$row['products_status'];
                $responseList[] = array('<input type="hidden" name="id[]" value="'.$row['products_id'].'">'.$row['products_model'],
                                        substr($row['products_name'],0,35),
                                        '<input size="6" type="text" name="price[]" value="'.number_format($row['products_price'], 2).'">',
                                        number_format($price_gr,2),
                                        '<input size="5" type="text" name="qty[]" value="'.$row['products_quantity'].'">',
                                        '<input type="checkbox" name="status['.$row['products_id'].']" value="1" '.($status==1?' checked':'').'>',
                                        '<input class="btn btn-primary" type="button" value="Edit" onclick="return editProduct('.$row['products_id'].');">
                                         <input class="btn btn-primary" type="button" value="Delete" onclick="return deleteProduct(\''.tep_href_link(FILENAME_BRAND_MANAGER.'/deleteproduct','mID='.$mID.'&products_id='.$row['products_id']).'\');">',
                                        );
                $count++;
              }   
          }
        if($_GET['filter']==1 || $_GET['filter']==''){
          $s1='selected';
        }elseif($_GET['filter']==2){
          $s2='selected';
        }elseif($_GET['filter']==3){
          $s3='selected';
        }elseif($_GET['filter']==4){
          $s4='selected';
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => $responseList,
            'filter' => '<b>Filter:</b> <select name="filter" onchange="return getListproducts();"><option value="1" '.$s1.'>Show Visible</options><option value="2" '.$s2.'>Show Invisible</options><option value="3" '.$s3.'>Products out of stock</options><option value="4" '.$s4.'>Show All</options></select>',
        );
        echo json_encode($response);          
  }

  public function actionUpdate()
  {
    $mID = Yii::app()->request->getParam('mID');
      for($i=0;$n=sizeof($_POST['id']),$i<$n;$i++)
      {
        $update_products_query="update products set products_price='".$_POST['price'][$i]."', products_quantity='".$_POST['qty'][$i]."', products_status='".$_POST['status'][$_POST['id'][$i]]."' where products_id=".$_POST['id'][$i];
        tep_db_query($update_products_query);
        //echo $update_products_query.'<br><br>';
        if($specials=tep_db_fetch_array(tep_db_query("select specials_id from ".TABLE_SPECIALS." where products_id=".$_POST['id'][$i])) )
        {
        //updte 
          if($_POST['discount'][$i]!=0){
            $new_price=$_POST['price'][$i]-($_POST['discount'][$i]*$_POST['price'][$i]/100);
            tep_db_query("update ".TABLE_SPECIALS." set specials_new_products_price=".$new_price." where products_id=".$_POST['id'][$i]);
          }else{
            tep_db_query("delete from ".TABLE_SPECIALS." where products_id=".$_POST['id'][$i]);
          }
        }else{
        //insert
          if($_POST['discount'][$i]!=0){
            $new_price=$_POST['price'][$i]-($_POST['discount'][$i]*$_POST['price'][$i]/100);
            $products_id=$_POST['id'][$i];
            $insert_specials_query="insert into ".TABLE_SPECIALS." (products_id,specials_new_products_price) values (".$products_id.",'".$new_price."')";
            tep_db_query($insert_specials_query);
          }
        }
      }
      $this->redirect(array('brand_manager/listproducts', 'mID' => $mID));
  }
  
  public function actionSave()
  { 
    global $messageStack, $language;
  
        $action = Yii::app()->request->getParam('action'); 
        
        if (isset($_GET['mID'])) $manufacturers_id = tep_db_prepare_input($_GET['mID']);
        $manufacturers_name = tep_db_prepare_input($_POST['manufacturers_name']);

        $sql_data_array = array('manufacturers_name' => $manufacturers_name);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
          $manufacturers_id = tep_db_insert_id();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        if (is_dir(DIR_FS_CATALOG_IMAGES)) {
            if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
                $this->redirect(array('brand_manager/index', 'mID' => $manufacturers_id , 'errorMessage' => sprintf(ERROR_DIRECTORY_NOT_WRITEABLE, DIR_FS_CATALOG_IMAGES), 'errorMessageType' => 'danger')); 
            } else {
              $manufacturers_image = new upload('manufacturers_image');
              $manufacturers_image->set_destination(DIR_FS_CATALOG_IMAGES);
              if ($manufacturers_image->parse() && $manufacturers_image->save()) {
                tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_image = '" . $manufacturers_image->filename . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");
              }
            }
        } else {
            $this->redirect(array('brand_manager/index', 'mID' => $manufacturers_id , 'errorMessage' => sprintf(ERROR_DIRECTORY_DOES_NOT_EXIST, DIR_FS_CATALOG_IMAGES), 'errorMessageType' => 'danger'));  
        }

        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $manufacturers_url_array = $_POST['manufacturers_url'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('manufacturers_url' => tep_db_prepare_input($manufacturers_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
          } elseif ($action == 'save') {
            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$language_id . "'");
          }
        }

        if (USE_CACHE == 'true') {
          \common\helpers\System::reset_cache_block('manufacturers');
        }

        $this->redirect(array('brand_manager/index', 'mID' => $manufacturers_id)); 
  }
  
  public function actionDelete()
  { 
        $manufacturers_id = Yii::app()->request->getParam('mID');

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $manufacturer_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          $manufacturer = tep_db_fetch_array($manufacturer_query);

          $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        tep_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            \common\helpers\Product::remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '' where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        if (USE_CACHE == 'true') {
          \common\helpers\System::reset_cache_block('manufacturers');
        }

        $this->redirect(array('brand_manager/index'));
  }
  
  public function actionDeleteProduct(){

      $product_id = Yii::app()->request->getParam('products_id');
      $manufacturers_id = Yii::app()->request->getParam('mID');

      if ($product_id > 0) \common\helpers\Product::remove_product($product_id);
      
      $this->redirect(array('brand_manager/index', 'mID' => $manufacturers_id));

  }
  
  public function actionBrandActions()
  {
     global $languages_id, $language, $login_id;
    
      $mID = Yii::app()->request->getParam('mID', 0);
      $action = Yii::app()->request->getParam('action');
      $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " where manufacturers_id= '".(int)$mID."' order by manufacturers_name";
      $manufacturers = tep_db_fetch_array(tep_db_query($manufacturers_query_raw));
      $manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$mID . "'");
      $manufacturer_products = tep_db_fetch_array($manufacturer_products_query);
      $mInfo_array = array_merge($manufacturers, $manufacturer_products);
      $mInfo = new objectInfo($mInfo_array);
      $heading = array();
      $contents = array();

      switch ($action) {
        case 'new':
          $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_MANUFACTURER . '</b>');

          $contents = array('form' => tep_draw_form('manufacturers', FILENAME_BRAND_MANAGER.'/save', 'action=insert', 'post', 'enctype="multipart/form-data"'));
          $contents[] = array('text' => TEXT_NEW_INTRO);
          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_NAME . '<br>' . tep_draw_input_field('manufacturers_name'));
          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_IMAGE . '<br>' . tep_draw_file_field('manufacturers_image'));

          $manufacturer_inputs_string = '';
          $languages = \common\helpers\Language::get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $manufacturer_inputs_string .= '<br>' . $languages[$i]['image'] . '&nbsp;' . tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']');
          }

          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
          $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" class="btn btn-primary" value=' .IMAGE_SAVE . '>  <a href="' . tep_href_link(FILENAME_BRAND_MANAGER.'/brandactions', 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-primary">' . IMAGE_CANCEL . '</a>');
          break;
        case 'edit':
          $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_MANUFACTURER . '</b>');

          $contents = array('form' => tep_draw_form('manufacturers', FILENAME_BRAND_MANAGER.'/save', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
          $contents[] = array('text' => TEXT_EDIT_INTRO);
          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_NAME . '<br>' . tep_draw_input_field('manufacturers_name', $mInfo->manufacturers_name));
          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_IMAGE . '<br>' . tep_draw_file_field('manufacturers_image') . '<br>' . $mInfo->manufacturers_image);

          $manufacturer_inputs_string = '';
          $languages = \common\helpers\Language::get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $manufacturer_inputs_string .= '<br>' . $languages[$i]['image'] . '&nbsp;' . tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']));
          }

          $contents[] = array('text' => '<br>' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
          $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" class="btn btn-primary" value=' .IMAGE_SAVE . '> <a href="' . tep_href_link(FILENAME_BRAND_MANAGER.'/brandactions', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id) . '" class="btn btn-primary">' . IMAGE_CANCEL . '</a>');
          break;
        case 'delete':
          $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');

          $contents = array('form' => tep_draw_form('manufacturers', FILENAME_BRAND_MANAGER.'/delete', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm'));
          $contents[] = array('text' => TEXT_DELETE_INTRO);
          $contents[] = array('text' => '<br><b>' . $mInfo->manufacturers_name . '</b>');
          $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

          if ($mInfo->products_count > 0) {
            $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
            $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
          }

          $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" class="btn btn-primary" value=' . IMAGE_DELETE . '> <a href="' . tep_href_link(FILENAME_BRAND_MANAGER.'/brandactions', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id) . '" class="btn btn-primary">' . IMAGE_CANCEL . '</a>');
          break;
        default:
          if (isset($mInfo) && is_object($mInfo)) {
            $heading[] = array('text' => '<b>' . $mInfo->manufacturers_name . '</b>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BRAND_MANAGER.'/brandactions', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit') . '" class="btn btn-primary">' . IMAGE_EDIT . '</a> <a class="btn btn-primary" href="' . tep_href_link(FILENAME_BRAND_MANAGER.'/brandactions', 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete') . '">' .  IMAGE_DELETE . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . \common\helpers\Date::date_short($mInfo->date_added));
            if (tep_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . \common\helpers\Date::date_short($mInfo->last_modified));
            $contents[] = array('text' => '<br>' . \common\helpers\Image::info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $mInfo->products_count);
          }
          break;
      }
      
      if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
        $box = new box;
        echo $box->infoBox($heading, $contents);

      }      
  }
}
