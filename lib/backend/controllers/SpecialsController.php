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

class SpecialsController extends Sceleton {
    
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_SPECIALS'];
    
        public function actionIndex()
        {
            global $languages_id, $language;

            $this->selectedMenu        = array( 'marketing', 'specials' );
            $this->navigation[]        = array( 'link' => Yii::$app->urlManager->createUrl( 'specials/index' ), 'title' => HEADING_TITLE );
            $this->topButtons[] = '<a href="#" class="create_item" onclick="return editItem(0)">'.IMAGE_INSERT.'</a>';
            $this->view->headingTitle  = HEADING_TITLE;
            $this->view->specialsTable = array(
                array(
                    'title'         => TABLE_HEADING_PRODUCTS,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_PRODUCTS_PRICE_OLD,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_PRODUCTS_PRICE,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_STATUS,
                    'not_important' => 1
                ),
            );

            return $this->render( 'index' );
        }

        public function actionList()
        {
            global $languages_id;
            $draw   = Yii::$app->request->get( 'draw', 1 );
            $start  = Yii::$app->request->get( 'start', 0 );
            $length = Yii::$app->request->get( 'length', 10 );

            $currencies = new \common\classes\currencies();

            $responseList = array();
            if( $length == -1 ) $length = 10000;
            $query_numrows = 0;

            if( isset( $_GET['search']['value'] ) && tep_not_null( $_GET['search']['value'] ) ) {
                $keywords         = tep_db_input( tep_db_prepare_input( $_GET['search']['value'] ) );
                $search_condition = " where pd.language_id = '$languages_id' and pd.products_name like '%" . $keywords . "%' ";

            } else {
                $search_condition = " where pd.language_id = '" . (int) $languages_id . "'  ";
            }

            if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
                switch( $_GET['order'][0]['column'] ) {
                    case 0:
                        $orderBy = "pd.products_name " . tep_db_prepare_input( $_GET['order'][0]['dir'] );
                        break;
                    case 1:
                        $orderBy = "p.products_price " . tep_db_prepare_input( $_GET['order'][0]['dir'] );
                        break;
                    case 2:
                        $orderBy = "s.specials_new_products_price " . tep_db_prepare_input( $_GET['order'][0]['dir'] );
                        break;
                    default:
                        $orderBy = "pd.products_name";
                        break;
                }
            } else {
                $orderBy = "pd.products_name";
            }

            $specials_query_raw  = "
              select p.products_id, pd.products_name, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status
              from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd
              $search_condition and p.products_id = pd.products_id and pd.affiliate_id = 0  and p.products_id = s.products_id
              order by $orderBy ";

            $current_page_number = ( $start / $length ) + 1;
            $_split              = new \splitPageResults( $current_page_number, $length, $specials_query_raw, $query_numrows, 'p.products_id' );
            $specials_query      = tep_db_query( $specials_query_raw );

            while( $specials = tep_db_fetch_array( $specials_query ) ) {

                $products_query = tep_db_query( "select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $specials['products_id'] . "'" );
                $products       = tep_db_fetch_array( $products_query );
                $sInfo_array    = array_merge( $specials, $products );
                $sInfo          = new \objectInfo( $sInfo_array );

                if( USE_MARKET_PRICES == 'True' ) {
                    $product_price = $currencies->format( \common\helpers\Product::get_products_price( $specials['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) );
                } else {
                    $product_price = $currencies->format( $specials['products_price'] );
                }

                if( USE_MARKET_PRICES == 'True' ) {
                    $specials_product_price = $currencies->format( \common\helpers\Product::get_specials_price( $specials['specials_id'], $currencies->currencies[DEFAULT_CURRENCY]['id'] ) );
                } else {
                    $specials_product_price = $currencies->format( $specials['specials_new_products_price'] );
                }

                if( (int) $sInfo->status > 0 ) {
                    $status = '<input type="checkbox" name="prod_status" class="check_on_off" checked="checked" value="'.$specials['specials_id'].'">';
                } else {
                    $status = '<input type="checkbox" name="prod_status" class="check_on_off"  value="'.$specials['specials_id'].'">';
                }
                $image = \common\classes\Images::getImage($specials['products_id']);
                $responseList[] = array(
                    '<div class="">' .
                    '<div class="prod_name click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['specials/specialedit', 'orders_id' => $specials['orders_id']]) . '">'.
                    (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                    '<span class="prodNameC">' . $sInfo->products_name . '<input class="cell_identify" type="hidden" value="' . $sInfo->specials_id . '"></span>'.
                    '</div>'.
                    '</div>'.
                    '</div>',
                    '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['specials/specialedit', 'orders_id' => $specials['orders_id']]) . '"><span class="oldPrice">' . $product_price . '</span></div>',
                    '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['specials/specialedit', 'orders_id' => $specials['orders_id']]) . '"><span class="specialPrice">' . $specials_product_price . '</span></div>',
                    $status,
                );
            }

            $response = array(
                'draw'            => $draw,
                'recordsTotal'    => $query_numrows,
                'recordsFiltered' => $query_numrows,
                'data'            => $responseList
            );
            echo json_encode( $response );
        }

        function actionItempreedit()
        {
            $this->layout = FALSE;

            global $languages_id, $language;

            \common\helpers\Translation::init('admin/specials');

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $specials_query_raw = "
                  select p.products_id, pd.products_name, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status
                  from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                  where pd.language_id = '" . (int) $languages_id . "' and p.products_id = pd.products_id and s.specials_id = '$item_id' and pd.affiliate_id = 0  and p.products_id = s.products_id
                  ";

            $specials_query     = tep_db_query( $specials_query_raw );
            $specials           = tep_db_fetch_array( $specials_query );

            $product_query = tep_db_query( "select p.*, s.*, pd.* from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'" );
            $product       = tep_db_fetch_array( $product_query );

            $info  = array_merge( $specials, $product );
            $sInfo = new \objectInfo( $info );

            $currencies = new \common\classes\currencies();

            ?>
						<div class="row_or_img"><?php echo \common\helpers\Image::info_image( $sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT ); ?></div>
						<div class="or_box_head or_box_head_no_margin"><?php echo $sInfo->products_name; ?></div>
						<div class="row_or_wrapp">
						<div class="row_or"><div><?php echo TEXT_INFO_DATE_ADDED;?></div><div><?php echo \common\helpers\Date::date_short( $sInfo->specials_date_added );?></div></div>
						<div class="row_or"><div><?php echo TEXT_INFO_LAST_MODIFIED;?></div><div><?php echo \common\helpers\Date::date_short( $sInfo->specials_last_modified );?></div></div>
            <?php
            if( USE_MARKET_PRICES == 'True' ) {
                echo '<div class="row_or"><div>' . TEXT_INFO_NEW_PRICE . '</div><div>' . $currencies->format( \common\helpers\Product::get_specials_price( $sInfo->specials_id, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) ) . '</div></div>';

                if( \common\helpers\Product::get_products_price( $sInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) == 0 ) {
                    echo '<div class="row_or"><div>' . TEXT_INFO_PERCENTAGE . ' 100% </div></div>';
                } else {
                    echo '<div class="row_or"><div>' . TEXT_INFO_PERCENTAGE . '</div><div>' . number_format( 100 - ( \common\helpers\Product::get_specials_price( $sInfo->specials_id, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) / \common\helpers\Product::get_products_price( $sInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) ) * 100 ) . '%' . '</div></div>';
                }
            } else {
                echo '<div class="row_or"><div>' . TEXT_INFO_ORIGINAL_PRICE . '</div><div>' . $currencies->format( $sInfo->products_price ) . '</div></div>';
                echo '<div class="row_or"><div>' . TEXT_INFO_NEW_PRICE . '</div><div>' . $currencies->format( $sInfo->specials_new_products_price ) . '</div></div>';
                if( $sInfo->products_price <= 0 ) {
                    echo '<div class="row_or"><div>' . TEXT_INFO_PERCENTAGE . ' 100% </div></div>';
                } else {
                    echo '<div class="row_or"><div>' . TEXT_INFO_PERCENTAGE . '</div><div>' . number_format( 100 - ( ( $sInfo->specials_new_products_price / $sInfo->products_price ) * 100 ) ) . '%' . '</div></div>';
                }
            }
            ?>

            <?php echo '<div class="row_or"><div>' . TEXT_INFO_EXPIRES_DATE . '</div><div>' . \common\helpers\Date::date_short( $sInfo->expires_date ) . '</div></div>'; ?>
            <?php echo '<div class="row_or"><div>' . TEXT_INFO_STATUS_CHANGE . '</div><div>' . \common\helpers\Date::date_short( $sInfo->date_status_change ) . '</div></div>'; ?>
						</div>
						<div class="btn-toolbar btn-toolbar-order">
                            <a class="btn btn-edit btn-no-margin" onclick="return editItem( <?php echo $item_id; ?>)" href="<?php echo Yii::$app->urlManager->createUrl(['specials/specialedit', 'id' => $sInfo->specials_id]);?>"><?php echo IMAGE_EDIT ?></a><!--<button class="btn btn-edit btn-no-margin" onclick="return editItem( <?php echo $item_id; ?>)">Edit</button>--><button class="btn btn-delete" onclick="return deleteItemConfirm( <?php echo $item_id; ?>)"><?php echo IMAGE_DELETE; ?></button>
						</div>
        <?php
        }

        function actionItemedit()
        {
            $this->layout = FALSE;

            global $languages_id, $language;

            \common\helpers\Translation::init('admin/specials');

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $currencies = new \common\classes\currencies();

            //$_params = Yii::app()->getParams();

            //if( !isset( $_params->currencies ) ) Yii::app()->setParams( array( 'currencies' => $currencies ) );

            $header     = '';
            $script     = '';
            $delete_btn = '';
            $form_html  = '';

            $fields = array();

            $languages = \common\helpers\Language::get_languages();

            if( $item_id === 0 ) {
                // Insert
                $header = 'Insert';

                $sInfo = new \objectInfo( array() );

                $specials_array = array();
                $specials_query = tep_db_query( "select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id" );
                while( $specials = tep_db_fetch_array( $specials_query ) ) {
                    $specials_array[] = $specials['products_id'];
                }

                $special_product_html = \common\helpers\Product::draw_products_pull_down( 'products_id', 'style="font-size:10px"', $specials_array );


                $fields[] = array( 'type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html );

                $fields[] = array( 'name' => 'products_price', 'type' => 'hidden', 'value' => '' );


                if (USE_MARKET_PRICES == 'True'){

                    foreach( $currencies->currencies as $key => $value ) {

                        $specials_products_price_html = tep_draw_input_field(
                            'specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']',
                             \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id']), 'size="20"');
                        $fields[] = array( 'type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
                    }

                    $data_query = tep_db_query( "select * from " . TABLE_GROUPS . " order by groups_id" );
                    while( $data = tep_db_fetch_array( $data_query ) ) {
                        $data_html = tep_draw_input_field( 'specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price( $sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2' ), 'size="20"' );
                        $fields[]  = array( 'type' => 'field', 'title' => $data['groups_name'], 'value' => $data_html );
                    }

                } else {
                    $fields[] = array( 'name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => '' );
                }

                $fields[] = array( 'name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => '' );

            } else {
                // Update
                $header = 'Edit';

                $product_query = tep_db_query( "select p.products_id, s.specials_id, pd.products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'" );
                $product       = tep_db_fetch_array( $product_query );

                $sInfo = new \objectInfo( $product );

                $specials_array = array();
                $specials_query = tep_db_query( "select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id" );
                while( $specials = tep_db_fetch_array( $specials_query ) ) {
                    $specials_array[] = $specials['products_id'];
                }

                if( isset( $sInfo->products_name ) ) {
                    $special_product_html = $sInfo->products_name . ' <small>(' . $currencies->format( \common\helpers\Product::get_products_price( $sInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'] ) ) . ')</small>';

                } else {
                    $special_product_html = \common\helpers\Product::draw_products_pull_down( 'products_id', 'style="font-size:10px"', $specials_array );
                }

                $fields[] = array( 'type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html );


                $status_checked_disabled = FALSE;
                $status_checked_active   = FALSE;

                if( (int) $sInfo->status > 0 ) {
                    $status_checked_active = TRUE;
                } else {
                    $status_checked_disabled = TRUE;
                }

                $status_html = tep_draw_checkbox_field("status", '1', $status_checked_active, '', 'class="check_on_off"');
/*                $status_html .= "Active " . tep_draw_radio_field( 'status', 1, $status_checked_active );
                $status_html .= '<br>';
                $status_html .= "Inactive " . tep_draw_radio_field( 'status', '0', $status_checked_disabled );*/
                                    


                $fields[] = array( 'type' => 'field', 'title' => TABLE_HEADING_STATUS, 'value' => $status_html );

                if( USE_MARKET_PRICES == 'True' ) {

                    $specials_products_price_html = '';
                    foreach ($currencies->currencies as $key => $value){
                        $specials_products_price_html = tep_draw_input_field('specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']', (($specials_new_products_price[$currencies->currencies[$key]['id']]) ? stripslashes($specials_new_products_price[$currencies->currencies[$key]['id']]) : \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'])), 'size="20"');
                        $fields[] = array( 'type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
                    }

                    $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
                    while ($data = tep_db_fetch_array($data_query)){
                        $group_html = tep_draw_input_field('specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2'), 'size="20"');
                        $fields[] = array( 'type' => 'field', 'title' => $data['groups_name'], 'value' => $group_html);
                    }

                } else {
                    $fields[] = array( 'name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => \common\helpers\Product::get_specials_price( $sInfo->specials_id ) );

                    $fields[] = array( 'name' => 'products_price', 'type' => 'hidden', 'value' => ( isset( $sInfo->products_price ) ? $sInfo->products_price : '' ) );
                }

                if($sInfo->expires_date == '0000-00-00 00:00:00'){
                    $expires_date = '';
                } else {
                    $expires_date = explode( "-", $sInfo->expires_date );
                    @$Y = $expires_date[0];
                    @$M = $expires_date[1];
                    @$d = $expires_date[2];
                    @$D = explode( " ", $d );
                    $expires_date = $M . "/" . $D[0] . "/" . $Y;
                }

                if($expires_date == "//") $expires_date = '';

                $fields[] = array( 'name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => \common\helpers\Date::date_short($sInfo->expires_date) );

                $fields[] = array( 'type' => 'field', 'title' => '', 'value' => TEXT_SPECIALS_PRICE_TIP );

            }

            echo tep_draw_form(
                    'save_item_form',
                    'specials/submit',
                    \common\helpers\Output::get_all_get_params( array( 'action' ) ),
                    'post',
                    'id="save_item_form" onSubmit="return saveItem();"' ) .
                tep_draw_hidden_field( 'item_id', $item_id );

            ?>
						<div class="or_box_head"><?php echo $header; ?></div>

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
                                        echo ' <div class="main_row">';
                                        echo '      <div class="main_title">' . $field_title . '</div>';
                                        echo '       <div class="main_value">       ';
                                        echo "        $field_value";
                                        echo '       </div>       ';
                                        echo ' </div>';
                                    } elseif( $field_type == 'textarea' ) {

                                        $field_html = tep_draw_textarea_field( $field_name, 'soft', $field_cols, $field_rows, $field_value );

                                        echo ' <div class="main_row">';
                                        echo '      <div class="main_title">' . $field_title . '</div>       ';
                                        echo '       <div class="main_value">       ';
                                        echo "        $field_pre_html $field_html  $field_required $field_post_html";
                                        echo '       </div>       ';
                                        echo ' </div>';
                                    } else {
                                        echo ' <div class="main_row">';
                                        echo '      <div class="main_title">' . $field_title . '</div>       ';
                                        echo '       <div class="main_value">       ';
                                        echo "        $field_pre_html <input type='$field_type' name='$field_name' value='$field_value' $field_maxlength $field_size class='$field_class'> $field_post_html $field_required";
                                        echo '       </div>       ';
                                        echo ' </div>';
                                    }
                                }
                            ?>
														<div class="btn-toolbar btn-toolbar-order">
                            <input class="btn btn-no-margin" type="submit" value="<?php echo IMAGE_SAVE;?>"><?php echo $delete_btn; ?><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="<?php echo IMAGE_CANCEL;?>">
														</div>

            <?php echo $form_html; ?>
            </form>
            <script>
            $(document).ready(function(){
                $(".widget-content .check_on_off").bootstrapSwitch(
                            {
                            onText: "<?=SW_ON?>",
                            offText: "<?=SW_OFF?>",
                            handleWidth: '20px',
                            labelWidth: '24px'
                            }
                          );                        

                          $( ".datepicker" ).datepicker({
                            changeMonth: true,
                            changeYear: true,
                            showOtherMonths:true,
                            autoSize: false,
                            minDate: '1',
                            dateFormat: '<?=DATE_FORMAT_DATEPICKER?>',
                            
    });
            })
            </script>
        <?php
        }

        function actionSubmit()
        {
            global $languages_id, $language;

            $currencies = new \common\classes\currencies();

            \common\helpers\Translation::init('admin/specials');

            $item_id        = (int) Yii::$app->request->post( 'item_id' );
            $products_price = tep_db_prepare_input( Yii::$app->request->post( 'products_price' ) );
            $specials_price = tep_db_prepare_input( Yii::$app->request->post( 'specials_price' ) );
            $products_id    = tep_db_prepare_input( Yii::$app->request->post( 'products_id', FALSE ) );
            $status         = tep_db_prepare_input( Yii::$app->request->post( 'status', 0 ) );
            $expires_date   = (string)Yii::$app->request->post( 'expires_date' );

            if( isset( $_POST['specials_new_products_price'] ) )
                $specials_new_products_price = $_POST['specials_new_products_price'];
            else
                $specials_new_products_price = array();

            if(  trim($expires_date) === '' OR trim($expires_date) == "//" ){
                $expires_date = "NULL";
            } else {
                $expires_date = date("Y-m-d", strtotime($expires_date));
            }

            //$this->layout = FALSE;
            $error        = FALSE;
            $message      = '';
            $script       = '';
            $delete_btn   = '';

            $messageType = 'success';

            if( $error === FALSE ) {
                if( $item_id > 0 ) {
                    // Update
                    $specials_id = $item_id;

                    if ((string)$status == '1') {
                        return tep_db_query("update " . TABLE_SPECIALS . " set status = '1', expires_date = NULL, date_status_change = now() where specials_id = '" . (int)$item_id . "'");
                    } elseif ($status == '0') {
                        return tep_db_query("update " . TABLE_SPECIALS . " set status = '0', date_status_change = now() where specials_id = '" . (int)$item_id . "'");
                    }

                    if( substr( $specials_price, -1 ) == '%' ) $specials_price = ( $products_price - ( ( $specials_price / 100 ) * $products_price ) );

                    //fb( "update " . TABLE_SPECIALS . " set specials_new_products_price = '" . tep_db_input( $specials_price ) . "', specials_last_modified = now(), expires_date = " .  $expires_date  . " where specials_id = '" . (int) $specials_id . "'" );
                    tep_db_query( "update " . TABLE_SPECIALS . " set specials_new_products_price = '" . tep_db_input( $specials_price ) . "', specials_last_modified = now(), expires_date = '" .  tep_db_input($expires_date)  . "' where specials_id = '" . (int) $specials_id . "'" );


                    if( USE_MARKET_PRICES == 'True' ) {
                        $data_query  = tep_db_query( "select products_id from " . TABLE_SPECIALS . " where specials_id = '" . (int) $specials_id . "'" );
                        $data        = tep_db_fetch_array( $data_query );
                        $products_id = $data['products_id'];

                        foreach( $currencies->currencies as $key => $value ) {
                            if( is_array( $specials_new_products_price ) )
                                if( substr( $specials_new_products_price[$currencies->currencies[$key]['id']], -1 ) == '%' ) {
                                    $new_special_insert_query = tep_db_query( "select products_id, products_group_price from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and currencies_id = '" . (int) $currencies->currencies[$key]['id'] . "'" );
                                    $new_special_insert       = tep_db_fetch_array( $new_special_insert_query );

                                    $products_price                                                   = $new_special_insert['products_group_price'];
                                    $specials_new_products_price[$currencies->currencies[$key]['id']] = ( $products_price - ( ( $specials_new_products_price[$currencies->currencies[$key]['id']] / 100 ) * $products_price ) );

                                }
                        }

                        $def_cur_price = 0;
                        if( is_array( $specials_new_products_price ) ) {
                            if( $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']] != '' ) {
                                $def_cur_price = $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']];
                            } else {
                                foreach( $currencies->currencies as $key => $value ) {
                                    if( $specials_new_products_price[$value['id']] != '' ) {
                                        $def_cur_price                                                                = $specials_new_products_price[$value['id']] / $value['value'];
                                        $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']] = $def_cur_price;
                                        break;
                                    }
                                }
                            }
                        }

                        if( is_array( $specials_new_products_price ) )
                            if( $def_cur_price != 0 ) {
                                foreach( $currencies->currencies as $key => $value ) {
                                    if( $specials_new_products_price[$value['id']] == '' ) {
                                        $specials_new_products_price[$value['id']] = $def_cur_price * $value['value'];
                                    }
                                }
                            }

                        foreach( $currencies->currencies as $key => $value ) {
                            $products_prices = tep_db_query( "select * from " . TABLE_SPECIALS_PRICES . " WHERE specials_id = '" . $specials_id . "' and currencies_id = '" . $currencies->currencies[$key]['id'] . "'" );
                            $prices          = tep_db_fetch_array( $products_prices );

                            if( is_array( $specials_new_products_price ) )
                                if( empty( $prices ) ) {
                                    tep_db_query( "insert into " . TABLE_SPECIALS_PRICES . " (specials_id, currencies_id, specials_new_products_price) values ('" . $specials_id . "', '" . $currencies->currencies[$key]['id'] . "', '" . tep_db_prepare_input( $specials_new_products_price[$currencies->currencies[$key]['id']] ) . "')" );
                                } else {
                                    tep_db_query( "update " . TABLE_SPECIALS_PRICES . " set specials_new_products_price = '" . tep_db_prepare_input( $specials_new_products_price[$currencies->currencies[$key]['id']] ) . "' WHERE specials_id = '" . $specials_id . "' and currencies_id = '" . $currencies->currencies[$key]['id'] . "'" );
                                }

                            tep_db_query( "delete from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . $specials_id . "' and currencies_id = '" . $currencies->currencies[$key]['id'] . "' and groups_id != 0" );
                            $data_query = tep_db_query( "select * from " . TABLE_GROUPS . " order by groups_id" );
                            while( $data = tep_db_fetch_array( $data_query ) ) {
                                $sql_data_array = array( 'specials_id'                 => $specials_id,
                                                         'specials_new_products_price' => tep_db_prepare_input( $_POST['specials_new_products_price_' . $data['groups_id']][$currencies->currencies[$key]['id']] ),
                                                         'groups_id'                   => $data['groups_id'],
                                                         'currencies_id'               => $currencies->currencies[$key]['id'] );
                                tep_db_perform( TABLE_SPECIALS_PRICES, $sql_data_array );
                            }

                        }


                    } else {
                        tep_db_query( "delete from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . $specials_id . "'" );
                        $data_query = tep_db_query( "select * from " . TABLE_GROUPS . " order by groups_id" );
                        while( $data = tep_db_fetch_array( $data_query ) ) {
                            if( isset( $_POST['specials_groups_prices_' . $data['groups_id']] ) ) {
                                $sql_data_array = array( 'specials_id'                 => $specials_id,
                                                         'specials_new_products_price' => tep_db_prepare_input( $_POST['specials_groups_prices_' . $data['groups_id']] ),
                                                         'groups_id'                   => $data['groups_id'],
                                                         'currencies_id'               => '0' );
                                tep_db_perform( TABLE_SPECIALS_PRICES, $sql_data_array );
                            }
                        }
                    }


                    $message = MESSAGE_ITEM_UPDATED;
                } else {
                    // Insert
                    $message = MESSAGE_ITEM_INSERTED;


                    if( substr( $specials_price, -1 ) == '%' ) {
                        $new_special_insert_query = tep_db_query( "select products_id, products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'" );
                        $new_special_insert       = tep_db_fetch_array( $new_special_insert_query );

                        $products_price = $new_special_insert['products_price'];
                        $specials_price = ( $products_price - ( ( $specials_price / 100 ) * $products_price ) );
                    }

                    tep_db_query( "insert into " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, expires_date, status) values ('" . (int) $products_id . "', '" . tep_db_input( $specials_price ) . "', now(), '" . tep_db_input( $expires_date ) . "', '1')" );
                    $specials_id = tep_db_insert_id();

                    if( USE_MARKET_PRICES == 'True' ) {
                        foreach( $currencies->currencies as $key => $value ) {
                            if( substr( $specials_new_products_price[$currencies->currencies[$key]['id']], -1 ) == '%' ) {
                                $new_special_insert_query = tep_db_query( "select products_id, products_group_price from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and currencies_id = '" . (int) $currencies->currencies[$key]['id'] . "'" );
                                $new_special_insert       = tep_db_fetch_array( $new_special_insert_query );

                                $products_price                                                   = $new_special_insert['products_group_price'];
                                $specials_new_products_price[$currencies->currencies[$key]['id']] = ( $products_price - ( ( $specials_new_products_price[$currencies->currencies[$key]['id']] / 100 ) * $products_price ) );
                            }
                        }

                        $def_cur_price = 0;
                        if( $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']] != '' ) {
                            $def_cur_price = $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']];
                        } else {
                            foreach( $currencies->currencies as $key => $value ) {
                                if( $specials_new_products_price[$value['id']] != '' ) {
                                    $def_cur_price                                                                = $specials_new_products_price[$value['id']] / $value['value'];
                                    $specials_new_products_price[$currencies->currencies[DEFAULT_CURRENCY]['id']] = $def_cur_price;
                                    break;
                                }
                            }
                        }
                        if( $def_cur_price != 0 ) {
                            foreach( $currencies->currencies as $key => $value ) {
                                if( $specials_new_products_price[$value['id']] == '' ) {
                                    $specials_new_products_price[$value['id']] = $def_cur_price * $value['value'];
                                }
                            }
                        }
                        foreach( $currencies->currencies as $key => $value ) {
                            tep_db_query( "INSERT INTO " . TABLE_SPECIALS_PRICES . " (specials_id, currencies_id, specials_new_products_price) values ('" . $specials_id . "', '" . $currencies->currencies[$key]['id'] . "', '" . tep_db_prepare_input( $specials_new_products_price[$currencies->currencies[$key]['id']] ) . "')" );
                            $data_query = tep_db_query( "select * from " . TABLE_GROUPS . " order by groups_id" );
                            while( $data = tep_db_fetch_array( $data_query ) ) {
                                $sql_data_array = array( 'specials_id'                 => $specials_id,
                                                         'specials_new_products_price' => tep_db_prepare_input( $_POST['specials_new_products_price_' . $data['groups_id']][$currencies->currencies[$key]['id']] ),
                                                         'groups_id'                   => $data['groups_id'],
                                                         'currencies_id'               => $currencies->currencies[$key]['id'] );
                                tep_db_perform( TABLE_SPECIALS_PRICES, $sql_data_array );
                            }

                        }
                    } else {
                        $data_query = tep_db_query( "select * from " . TABLE_GROUPS . " order by groups_id" );
                        while( $data = tep_db_fetch_array( $data_query ) ) {
                            $sql_data_array = array( 'specials_id'                 => $specials_id,
                                                     'specials_new_products_price' => tep_db_prepare_input( $_POST['specials_groups_prices_' . $data['groups_id']] ),
                                                     'groups_id'                   => $data['groups_id'],
                                                     'currencies_id'               => '0' );
                            tep_db_perform( TABLE_SPECIALS_PRICES, $sql_data_array );
                        }
                    }
                }

            }

            if( $error === TRUE ) {
                $messageType = 'warning';

                if( $message == '' ) $message = WARN_UNKNOWN_ERROR;
            }

            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>  
                    </div>    
                    <div class="noti-btn">
                    <div></div>
                    <div><button class="btn btn-primary" onclick="resetStatement();"><?php echo TEXT_BTN_OK;?></button></div>
                </div>
                </div>  
                <script>
                //$('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
            

            <table>
            <tr>
                <td class="main" align="right" colspan="2">
                    <p class="btn-toolbar">
                        <?php /* echo $delete_btn; */ ?>
                        <input class="btn btn-primary" type="button" onclick="return resetStatement()"
                               value="Back">
                    </p>
                </td>
            </tr>
        </table>
            <?php

           // $this->actionItemPreEdit();
        }

        function actionConfirmitemdelete()
        {
            global $languages_id, $language;

            \common\helpers\Translation::init('admin/specials');
            \common\helpers\Translation::init('admin/faqdesk');

            $this->layout = FALSE;

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $message   = $name = $title = '';
            $heading   = array();
            $contents  = array();
            $parent_id = 0;

            $specials_query = tep_db_query( "select p.products_id, s.specials_id, pd.products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = 0 and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'" );
            $specials       = tep_db_fetch_array( $specials_query );

            $sInfo = new \objectInfo( $specials );

            $heading[]  = array( 'text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>' );
						echo '<div class="or_box_head top_spec">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</div>';
						echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
						echo '<div class="col_desc"><strong>' . $sInfo->products_name  . '</strong></div>';


            echo tep_draw_form( 'item_delete', FILENAME_SPECIALS, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );

            $box = new \box;
           // echo $box->infoBox( $heading, $contents );
            ?>
            <div class="btn-toolbar btn-toolbar-order">
                <?php
                    echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
                    echo '<button class="btn btn-cancel" onclick="return resetStatement()">' . IMAGE_CANCEL . '</button>';

                    echo tep_draw_hidden_field( 'item_id', $item_id );
                ?>
            </div>
            </form>
        <?php
        }

        function actionItemdelete()
        {
            $this->layout = FALSE;

            $specials_id = (int) Yii::$app->request->post( 'item_id' );

            $messageType = 'success';
            $message     = TEXT_INFO_DELETED;

            tep_db_query( "delete from " . TABLE_SPECIALS . " where specials_id = '" . (int) $specials_id . "'" );

            if( USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True' ) {
                tep_db_query( "delete from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . tep_db_input( $specials_id ) . "'" );
            }

            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
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
            

            <p class="btn-toolbar">
                <?php
                    echo '<input type="button" class="btn btn-primary" value="' . IMAGE_BACK . '" onClick="return resetStatement()">';
                ?>
            </p>
        <?php
        }
        public function actionSpecialedit() {
        global $languages_id, $language;

            $this->selectedMenu = array( 'marketing', 'specials' );
        $this->view->headingTitle = HEADING_TITLE;
        $this->selectedMenu        = array( 'marketing', 'specials' );
        \common\helpers\Translation::init('admin/specials');
        
				$text_new_or_edit = ($_GET['action']=='new_special_ACD') ? TEXT_INFO_HEADING_NEW_SPECIAL : TEXT_INFO_HEADING_EDIT_SPECIAL;
				$this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials/index'), 'title' => HEADING_TITLE);
        
				//$this->layout = false;
				return $this->render('specialedit');
				
			}

    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        tep_db_query("update " . TABLE_SPECIALS . " set status = '" . ($status == 'true' ? 1 : 0) . "' where specials_id = '" . (int)$id . "'");
    }      

    }