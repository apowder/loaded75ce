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

class FeaturedController extends Sceleton {
    
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_FEATURED'];

        public function actionIndex()
        {
            global $languages_id, $language;

            $this->selectedMenu        = array( 'marketing', 'featured' );
            $this->navigation[]        = array( 'link' => Yii::$app->urlManager->createUrl( 'featured/index' ), 'title' => HEADING_TITLE );
            $this->view->headingTitle  = HEADING_TITLE;
            $this->topButtons[] = '<a href="#" class="create_item" onclick="return editItem(0)">'.IMAGE_INSERT.'</a>';
            $this->view->featuredTable = array(
                array(
                    'title'         => TABLE_HEADING_PRODUCTS,
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
                $search_condition = "where p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = s.products_id and pd.products_name like '%" . $keywords . "%' ";

            } else {
                $search_condition = " where p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = s.products_id  ";
            }

            if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
                switch( $_GET['order'][0]['column'] ) {
                    case 0:
                        $orderBy = "pd.products_name " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
                        break;
                    default:
                        $orderBy = "pd.products_name";
                        break;
                }
            } else {
                $orderBy = "pd.products_name";
            }


            $featured_query_raw = "select p.products_id, pd.products_name, s.featured_id, s.featured_date_added, s.featured_last_modified, s.expires_date, s.date_status_change, s.status, s.affiliate_id from " . TABLE_PRODUCTS . " p, " . TABLE_FEATURED . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd $search_condition  and pd.affiliate_id = 0 " . ( tep_session_is_registered( 'login_affiliate' ) ? " and s.affiliate_id = '" . $login_id . "'" : '' ) . " order by $orderBy";

            $current_page_number = ( $start / $length ) + 1;
            $_split              = new \splitPageResults( $current_page_number, $length, $featured_query_raw, $query_numrows, 'p.products_id' );
            $featured_query      = tep_db_query( $featured_query_raw );

            while( $featured = tep_db_fetch_array( $featured_query ) ) {
                
                $products_query = tep_db_query( "select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $featured['products_id'] . "'" );
                $products       = tep_db_fetch_array( $products_query );
                $sInfo_array    = array_merge( $featured, $products );
                $sInfo          = new \objectInfo( $sInfo_array );

                $image = \common\classes\Images::getImage($featured['products_id']);
                /*if( (int) $sInfo->status > 0 ) {
                    $status = '<span class="label label-success">Active</span>';
                } else {
                    $status = '<span class="label label-danger">Inactive</span>';
                }*/
                $status = '<input type="checkbox" value="'. $sInfo->featured_id . '" name="status" class="check_on_off" ' . ((int) $sInfo->status > 0 ? 'checked="checked"' : '') . '>';
                $responseList[] = array(
                    '<div class="">' .
                    '<div class="prod_name click_double" data-click-double="">'.
                    (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                    '<span class="prodNameC">' . $sInfo->products_name . '<input class="cell_identify" type="hidden" value="' . $sInfo->featured_id . '"></span>'. 
                    '</div>'.
                    '</div>',
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

        function actionItempreedit( $item_id = NULL )
        {
            $this->layout = FALSE;

            global $languages_id, $language, $login_id;

            \common\helpers\Translation::init('admin/featured');

            if( $item_id === NULL )
                $item_id = (int) Yii::$app->request->post( 'item_id' );

            $product_query = tep_db_query( "select p.*, pd.*, s.* from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_FEATURED . " s where p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = s.products_id and s.featured_id = '" . $item_id . "' and pd.affiliate_id = 0 " . ( tep_session_is_registered( 'login_affiliate' ) ? " and s.affiliate_id = '" . $login_id . "'" : '' ) );
            $product       = tep_db_fetch_array( $product_query );

            $sInfo = new \objectInfo( $product );

            ?>
            <div class="row_or_img row_img_top"><?php echo \common\helpers\Image::info_image( $sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT ); ?></div>
            <div class="or_box_head or_box_head_no_margin"><?php echo $sInfo->products_name; ?></div>
            <div class="row_or_wrapp">
                <div class="row_or">
                    <div><?php echo TEXT_INFO_DATE_ADDED;?></div>
                    <div><?php echo \common\helpers\Date::date_format( $sInfo->featured_date_added, DATE_FORMAT_SHORT );?></div>
                </div>
                <div class="row_or">
                    <div><?php echo TEXT_INFO_LAST_MODIFIED;?></div>
                    <div><?php echo \common\helpers\Date::date_format( $sInfo->featured_last_modified, DATE_FORMAT_SHORT );?></div>
                </div>
                <div class="row_or">
                    <div><?php echo TEXT_INFO_EXPIRES_DATE;?></div>
                    <div><?php echo \common\helpers\Date::date_format( $sInfo->expires_date, DATE_FORMAT_SHORT );?></div>
                </div>
                <div class="row_or">
                    <div><?php echo TEXT_INFO_STATUS_CHANGE;?></div>
                    <div><?php echo \common\helpers\Date::date_format( $sInfo->date_status_change, DATE_FORMAT_SHORT );?></div>
                </div>
            </div>
            <div class="btn-toolbar btn-toolbar-order">
                <button class="btn btn-edit btn-no-margin" onclick="return editItem( <?php echo $item_id; ?>)"><?=IMAGE_EDIT?></button><button class="btn btn-delete" onclick="return deleteItemConfirm( <?php echo $item_id; ?>)"><?=IMAGE_DELETE?></button>
            </div>
        <?php
        }

        function actionItemedit()
        {
            $this->layout = FALSE;

            global $languages_id, $language, $login_id;

            \common\helpers\Translation::init('admin/featured');

            $item_id = (int) Yii::$app->request->post( 'item_id' );


            //$_params = Yii::$app->getParams();

            $header     = '';
            $script     = '';
            $delete_btn = '';
            $form_html  = '';

            $fields = array();


            if( $item_id === 0 ) {
                // Insert
                $header = IMAGE_INSERT;

                $featured_array = array();
                $featured_query = tep_db_query( "select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_FEATURED . " s where s.products_id = p.products_id " . ( tep_session_is_registered( 'login_affiliate' ) ? " and (s.affiliate_id = '" . $login_id . "' or s.affiliate_id = 0)" : '' ) );
                while( $featured = tep_db_fetch_array( $featured_query ) ) {
                    $featured_array[] = $featured['products_id'];
                }

                $html = \common\helpers\Product::draw_products_pull_down( 'products_id', 'style="font-size:10px"', $featured_array );

                $fields[] = array( 'type' => 'field', 'title' => TEXT_FEATURED_PRODUCT, 'value' => $html );


                $fields[] = array( 'name' => 'expires_date', 'title' => TEXT_FEATURED_EXPIRES_DATE, 'class' => 'datepicker', 'value' => '' );

            } else {
                // Update
                $header = IMAGE_EDIT;

                $product_query = tep_db_query( "select p.*, pd.*, s.* from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_FEATURED . " s where p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = s.products_id and s.featured_id = '" . $item_id . "' and pd.affiliate_id = 0 " . ( tep_session_is_registered( 'login_affiliate' ) ? " and s.affiliate_id = '" . $login_id . "'" : '' ) );
                $product       = tep_db_fetch_array( $product_query );

                $sInfo = new \objectInfo( $product );

                $fields[] = array( 'type' => 'field', 'title' => TEXT_FEATURED_PRODUCT, 'value' => $sInfo->products_name );

                $status_checked_disabled = FALSE;
                $status_checked_active   = FALSE;

                if( (int) $sInfo->status > 0 ) {
                    $status_checked_active = TRUE;
                } else {
                    $status_checked_disabled = TRUE;
                }

                $status_html = '';
                /*$status_html .= "Active " . tep_draw_radio_field( 'status', 1, $status_checked_active );
                $status_html .= '<br>';
                $status_html .= "Inactive " . tep_draw_radio_field( 'status', '0', $status_checked_disabled );*/
                $status_html .= '<input type="checkbox" value="1" name="status" class="check_on_off" ' . ($status_checked_active ? 'checked="checked' : '') . '">';

                $fields[] = array( 'type' => 'field', 'title' => TABLE_HEADING_STATUS . ':', 'value' => $status_html );


                if( $sInfo->expires_date == '0000-00-00 00:00:00' ) {
                    $expires_date = '';
                } else {
                    $expires_date = explode( "-", $sInfo->expires_date );
                    @$Y = $expires_date[0];
                    @$M = $expires_date[1];
                    @$d = $expires_date[2];
                    @$D = explode( " ", $d );
                    $expires_date = $M . "/" . $D[0] . "/" . $Y;
                }

                if( $expires_date == "//" ) $expires_date = '';

                $fields[] = array( 'name' => 'expires_date', 'title' => TEXT_FEATURED_EXPIRES_DATE, 'class' => 'datepicker', 'value' => \common\helpers\Date::date_short($sInfo->expires_date) );


            }
            $script = '
                        <script type="text/javascript">
                          /*$("input[name=expires_date]").datepicker();*/
                         $( ".datepicker" ).datepicker({
                            changeMonth: true,
                            changeYear: true,
                            showOtherMonths:true,
                            autoSize: false,
                            minDate: "1",
                            dateFormat: "'. DATE_FORMAT_DATEPICKER. '",
                        });
                        </script>
                        ';

            echo tep_draw_form(
                    'save_item_form',
                    'reviews/index',
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
                                        echo '<div class="main_row_el after">
                                            <div class="mt_left">' . $field_title . '</div>
                                            <div class="mt_value">' . $field_value . '</div>
                                        </div>';
                                    } elseif( $field_type == 'textarea' ) {

                                        $field_html = tep_draw_textarea_field( $field_name, 'soft', $field_cols, $field_rows, $field_value );
                                        echo '<div class="main_row">
                                            <div class="main_title">' . $field_title . '</div>
                                            <div class="main_value">' .  $field_pre_html . $field_html . $field_required . $field_post_html . '</div>
                                        </div>';
                                    } else {
                                        echo '<div class="main_row">
                                            <div class="main_title">' . $field_title . '</div>
                                            <div class="main_value">' .  $field_pre_html . '<input type="' . $field_type . '" name="' . $field_name . '" value="' . $field_value .'" ' . $field_maxlength . $field_size .' class="' . $field_class . ' form-control">' . $field_post_html . $field_required  . '</div>
                                        </div>';
                                    }
                                }
                            ?>
            <div class="btn-toolbar btn-toolbar-order">
                <button class="btn btn-no-margin"><?=IMAGE_SAVE?></button><?php echo $delete_btn; ?><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="<?=IMAGE_CANCEL?>"> 
            </div>     

            <?php echo $form_html; ?>
            </form>
            <?php echo $script; ?>
        <?php
        }

        function actionSubmit()
        {
            global $languages_id, $language, $login_id;

            $currencies = new \common\classes\currencies();

            \common\helpers\Translation::init('admin/featured');

            $item_id      = (int) Yii::$app->request->post( 'item_id' );
            $products_id  = tep_db_prepare_input( Yii::$app->request->post( 'products_id', FALSE ) );
            $status       = tep_db_prepare_input( Yii::$app->request->post( 'status', 0 ) );
            $expires_date = Yii::$app->request->post( 'expires_date' );
            
            $expires_date = date("Y-m-d", strtotime($expires_date));

            $this->layout  = FALSE;
            $error         = FALSE;
            $action_update = FALSE;
            $message       = '';
            $script        = '';
            $delete_btn    = '';

            $messageType = 'success';

            if( $error === FALSE ) {
                if( $item_id > 0 ) {
                    // Update
                    $action_update = TRUE;

                    $featured_id = $item_id;

                    tep_db_query( "update " . TABLE_FEATURED . " set status = '$status' , featured_last_modified = now(), expires_date = '" . $expires_date . "' where featured_id = '" . $featured_id . "'" );


                    $message = "Item updated";
                } else {
                    // Insert
                    $message = "Item inserted";

                    tep_db_query( "insert into " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, affiliate_id) values ('" . $products_id . "', now(), '" . $expires_date . "', '1', " . ( tep_session_is_registered( 'login_affiliate' ) ? $login_id : '0' ) . ")" );


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

            if( $action_update )
                $this->actionItemPreEdit( $item_id );
        }

        function actionConfirmitemdelete()
        {
            global $languages_id, $language;

            \common\helpers\Translation::init('admin/featured');
            \common\helpers\Translation::init('admin/faqdesk');

            $this->layout = FALSE;

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $message   = $name = $title = '';
            $heading   = array();
            $contents  = array();
            $parent_id = 0;

            $product_query = tep_db_query( "select p.*, pd.*, s.* from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_FEATURED . " s where p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = s.products_id and s.featured_id = '" . $item_id . "' and pd.affiliate_id = 0 " . ( tep_session_is_registered( 'login_affiliate' ) ? " and s.affiliate_id = '" . $login_id . "'" : '' ) );
            $product       = tep_db_fetch_array( $product_query );

            $sInfo = new \objectInfo( $product );

            $heading[]  = array( 'text' => '<b>' . TEXT_INFO_HEADING_DELETE_FEATURED . '</b>' );
            $contents[] = array( 'text' => TEXT_INFO_DELETE_INTRO . '<br>' );
            $contents[] = array( 'text' => '<br><b>' . $sInfo->products_name . '</b>' );

            echo tep_draw_form( 'item_delete', FILENAME_featured, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_FEATURED . '</div>';
            echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
            echo '<div class="col_desc"><strong>' . $sInfo->products_name . '</strong></div>';
            //$box = new \box;
            //echo $box->infoBox( $heading, $contents );
            ?>
            <div class="btn-toolbar btn-toolbar-order">
                <?php
                    echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
                    echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
                    echo tep_draw_hidden_field( 'item_id', $item_id );
                ?>
            </div>
            </form>
        <?php
        }

        function actionItemdelete()
        {
            $this->layout = FALSE;

            $featured_id = (int) Yii::$app->request->post( 'item_id' );

            $messageType = 'success';
            $message     = TEXT_INFO_DELETED;

            tep_db_query( "delete from " . TABLE_FEATURED . " where featured_id = '" . tep_db_input( $featured_id ) . "'" );

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

        function tep_set_featured_status( $featured_id, $status )
        {
            if( $status == '1' ) {
                return tep_db_query( "update " . TABLE_FEATURED . " set status = '1', expires_date = NULL, date_status_change = NULL where featured_id = '" . (int)$featured_id . "'" );
            } elseif( $status == '0' ) {
                return tep_db_query( "update " . TABLE_FEATURED . " set status = '0', date_status_change = now() where featured_id = '" . (int)$featured_id . "'" );
            } else {
                return -1;
            }
        }
        
        public function actionSwitchStatus()
        {
            $id = Yii::$app->request->post('id');
            $status = Yii::$app->request->post('status');
            $this->tep_set_featured_status($id, ($status == 'true' ? 1 : 0));
        }      
        
    }