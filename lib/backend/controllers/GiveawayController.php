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

class GiveawayController extends Sceleton {
    
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_GIVE_AWAY'];
    
        public function actionIndex()
        {
            global $languages_id, $language;

            $this->selectedMenu        = array( 'marketing', 'giveaway' );
            $this->navigation[]        = array( 'link' => Yii::$app->urlManager->createUrl( 'categories/index' ), 'title' => HEADING_TITLE );
            $this->topButtons[] = '<a href="#" class="create_item" onClick="return editItem(0)">'.IMAGE_INSERT.'</a>';
            $this->view->headingTitle  = HEADING_TITLE;
            $this->view->giveawayTable = array(
                array(
                    'title'         => TABLE_HEADING_PRODUCTS,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_PRODUCTS_PRICE,
                    'not_important' => 0
                ),
            );

            $this->view->filters = new \stdClass();
            $this->view->filters->row = (int)$_GET['row'];

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
                $search_condition = " where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' and p.products_id = gap.products_id and pd.products_name like '%" . $keywords . "%' ";

            } else {
                $search_condition = " where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' and p.products_id = gap.products_id  ";
            }

            if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
                switch( $_GET['order'][0]['column'] ) {
                    case 0:
                        $orderBy = "pd.products_name " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
                        break;
                    case 1:
                        $orderBy = "p.products_price " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
                        break;
                    default:
                        $orderBy = "pd.products_name";
                        break;
                }
            } else {
                $orderBy = "pd.products_name";
            }

            $gap_query_raw = "select p.products_id, pd.products_name, gap.gap_id, gap.products_qty, gap.shopping_cart_price from " . TABLE_PRODUCTS . " p, " . TABLE_GIVE_AWAY_PRODUCTS . " gap, " . TABLE_PRODUCTS_DESCRIPTION . " pd  $search_condition  order by $orderBy";

            $current_page_number = ( $start / $length ) + 1;
            $_split              = new \splitPageResults( $current_page_number, $length, $gap_query_raw, $query_numrows, 'p.products_id' );
            $gap_query           = tep_db_query( $gap_query_raw );

            while( $gap = tep_db_fetch_array( $gap_query ) ) {

                $products_query = tep_db_query( "select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $gap['products_id'] . "'" );
                $products       = tep_db_fetch_array( $products_query );
                $gapInfo_array  = array_merge( $gap, $products );
                $gapInfo        = new \objectInfo( $gapInfo_array );

                $responseList[] = array(
                    $gapInfo->products_name . '<input class="cell_identify" type="hidden" value="' . $gapInfo->gap_id . '">',
                    $currencies->format( $gapInfo->shopping_cart_price ),
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

            \common\helpers\Translation::init('admin/giveaway');

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $form_action   = 'update';
            $product_query = tep_db_query( "select p.products_id, pd.products_name, p.products_price, gap.gap_id, gap.shopping_cart_price, gap.products_qty from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_GIVE_AWAY_PRODUCTS . " gap where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' and p.products_id = gap.products_id and gap.gap_id = '" . (int) $item_id . "'" );
            $product       = tep_db_fetch_array( $product_query );
            $gapInfo       = new \objectInfo( $product );

            $products_query = tep_db_query( "select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $gapInfo->products_id . "'" );
            $products       = tep_db_fetch_array( $products_query );
            ?>
						<div class="or_box_head"><?php echo TEXT_GIVE_MANAGEMENT;?></div>
            <div class="col_desc"> <?php echo '<b>' . $gapInfo->products_name . '</b>'; ?></div>

            <div class="col_desc box_al_center"> <?php echo \common\helpers\Image::info_image( $products['products_image'], $gapInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT ); ?></div>
						<div class="btn-toolbar btn-toolbar-order">
							<button class="btn btn-edit btn-no-margin" onclick="return editItem( <?php echo $item_id; ?>)"><?php echo IMAGE_EDIT;?></button><button class="btn btn-delete" onclick="return deleteItemConfirm( <?php echo $item_id; ?>)"><?php echo IMAGE_DELETE;?></button>							
						</div>
        <?php
        }

        function actionItemedit()
        {
            $this->layout = FALSE;

            global $languages_id, $language;

            \common\helpers\Translation::init('admin/giveaway');

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $currencies = new \common\classes\currencies();

            //$_params = Yii::$app->request->getBodyParams();

            //if( !isset( $_params->currencies ) ) Yii::$app->request->setBodyParams( array( 'currencies' => $currencies ) );

            $header     = '';
            $script     = '';
            $delete_btn = '';
            $form_html  = '';

            $fields = array();

            $languages = \common\helpers\Language::get_languages();

            if( $item_id === 0 ) {
                // Insert
                $header = IMAGE_INSERT;


                $gapInfo   = new \objectInfo( array() );
                /*$gap_array = array();
                $gap_query = tep_db_query( "select distinct(p.products_id), pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' order by pd.products_name" );
                $gap_array = array();
                while( $gap = tep_db_fetch_array( $gap_query ) ) {
                    $gap_array[] = array( 'id' => $gap['products_id'], 'text' => $gap['products_name'] );
                }*/

                //$gap_product_html = \common\helpers\Product::draw_products_pull_down( 'products_id', 'style="font-size:10px"', $gap_array );
                
                $gap_product_html = '<div class="search"><input type="text" value="" placeholder="Enter your keywords" name="keywords" autocomplete="off"></div>';
                $gap_product_html .= tep_draw_hidden_field( 'products_id', 0 );

                $fields[] = array( 'type' => 'field', 'title' => TEXT_GIVE_AWAY_PRODUCT, 'value' => $gap_product_html );


                //$fields[] = array( 'name' => 'item_id', 'type' => 'hidden', 'value' => '' );


                $fields[] = array( 'name' => 'group_price', 'title' => TEXT_GROUP_GIVE_AWAY_PRICE, 'value' => '' );

            } else {
                // Update
                $header = IMAGE_EDIT;

                /*$gap_array = array();
                $gap_query = tep_db_query( "select distinct(p.products_id), pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' order by pd.products_name" );
                $gap_array = array();
                while( $gap = tep_db_fetch_array( $gap_query ) ) {
                    $gap_array[] = array( 'id' => $gap['products_id'], 'text' => $gap['products_name'] );
                }*/


                $product_query = tep_db_query( "select p.products_id, pd.products_name, p.products_price, gap.gap_id, gap.shopping_cart_price, gap.products_qty, gap.qty_for_free from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_GIVE_AWAY_PRODUCTS . " gap where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' and p.products_id = gap.products_id and gap.gap_id = '" . (int) $item_id . "'" );
                $product       = tep_db_fetch_array( $product_query );
                $gapInfo       = new \objectInfo( $product );

                if( isset( $gapInfo->products_name ) ) {
                    $html = $gapInfo->products_name . ' <small>(' . $currencies->format( $gapInfo->products_price ) . ')</small>';
                } else {
                    //$html = tep_draw_pull_down_menu( 'products_id', $gap_array );
                    $html = '<div class="search"><input type="text" value="" placeholder="Enter your keywords" name="keywords" autocomplete="off"></div>';
                    $html .= tep_draw_hidden_field( 'products_id', 0 );
                    
                    
                }


                $fields[] = array( 'title' => TEXT_GIVE_AWAY_PRODUCT, 'type' => 'field', 'value' => $html );

                $fields[] = array( 'title' => TEXT_GROUP_GIVE_AWAY_PRICE, 'name' => 'group_price', 'value' => $gapInfo->shopping_cart_price );

                $fields[] = array( 'title' => TEXT_BUY_QUANTITY, 'name' => 'qty_for_free', 'value' => $gapInfo->qty_for_free );

            }
            $script = '
                        <script type="text/javascript">

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
                                        echo '<div class="main_row">';
                                        echo '<div class="main_title">' . $field_title . '</div>';
                                        echo '<div class="main_value">' . $field_value . '</div>';
                                        echo '</div>';
                                    } elseif( $field_type == 'textarea' ) {

                                        $field_html = tep_draw_textarea_field( $field_name, 'soft', $field_cols, $field_rows, $field_value );

                                        echo '<div class="main_row">';
                                        echo '<div class="main_title">' . $field_title . '</div>';
                                        echo '<div class="main_value">' . $field_pre_html . $field_html .  $field_required . $field_post_html . '</div>';
                                        echo ' </div>';
                                    } else {
                                        echo '<div class="main_row">';
                                        echo '<div class="main_title">' . $field_title . '</div>';
                                        echo '<div class="main_value">' . $field_pre_html . '<input type="' . $field_type . '" name="'.$field_name . '" value="' . $field_value . '" ' . $field_maxlength . ' ' . $field_size . ' class="'.$field_class.'"></div>';
                                        echo ' </div>';
                                    }
                                }
                            ?>
            <div class="btn-toolbar btn-toolbar-order">
                <button class="btn btn-no-margin"><?php echo IMAGE_SAVE;?></button><?php echo $delete_btn; ?><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="<?php echo IMAGE_CANCEL;?>">
            </div>

            <?php echo $form_html; ?>
            </form>
<script type="text/javascript">
    function searchSuggestSelected(id, value) {
        $('input[name="keywords"]').val(value);
        $('input[name="products_id"]').val(id);
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
    })
  })(jQuery)
</script>
            <?php echo $script; ?>
        <?php
        }

        function actionSubmit()
        {
            global $languages_id, $language;

            $currencies = new \common\classes\currencies();

            \common\helpers\Translation::init('admin/giveaway');

            $item_id     = (int) Yii::$app->request->post( 'item_id' );
            $products_id = tep_db_prepare_input( Yii::$app->request->post( 'products_id' ) );
            $group_price = tep_db_prepare_input( Yii::$app->request->post( 'group_price' ) );
            $qty_for_free = (int)Yii::$app->request->post('qty_for_free');
            
            $this->layout = FALSE;
            $error        = FALSE;
            $message      = '';
            $script       = '';
            $delete_btn   = '';

            $messageType = 'success';

            if( $error === FALSE ) {
                if( $item_id > 0 ) {
                    // Update
                    $specials_id = $item_id;


                    $gap_id       = $item_id;
                    $products_qty = 1; // tep_db_prepare_input($HTTP_POST_VARS['products_qty']);

                    tep_db_query( "update " . TABLE_GIVE_AWAY_PRODUCTS . " set products_qty = '" . tep_db_input($products_qty) . "', shopping_cart_price = '" . tep_db_input($group_price) . "', qty_for_free = '" . tep_db_input($qty_for_free) . "' where gap_id = '" . (int) $gap_id . "'" );

                    $message = "Item updated";
                } else {
                    // Insert
                    $message = "Item inserted";


                    $products_qty = 1; // tep_db_prepare_input($HTTP_POST_VARS['products_qty']);

                    if( $group_price >= 0 ) {
                        // clear prev data, in case if input combination already exists
                        tep_db_query( "delete from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id = '" . tep_db_input($products_id) . "'" );
                        tep_db_query( "insert into " . TABLE_GIVE_AWAY_PRODUCTS . " (products_id, shopping_cart_price, products_qty, qty_for_free) values('" . tep_db_input($products_id) . "', '" . tep_db_input($group_price) . "', '" . tep_db_input($products_qty) . "', '" . tep_db_input($qty_for_free) . "')" );
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
                                   value="<?php echo IMAGE_BACK;?>">
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

            \common\helpers\Translation::init('admin/giveaway');
            \common\helpers\Translation::init('admin/faqdesk');

            $this->layout = FALSE;

            $item_id = (int) Yii::$app->request->post( 'item_id' );

            $message  = $name = $title = '';
            $heading  = array();
            $contents = array();

            $product_query = tep_db_query( "select p.products_id, pd.products_name, p.products_price, gap.gap_id, gap.shopping_cart_price, gap.products_qty from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_GIVE_AWAY_PRODUCTS . " gap where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.affiliate_id = '0' and p.products_id = gap.products_id and gap.gap_id = '" . (int) $item_id . "'" );
            $product       = tep_db_fetch_array( $product_query );
            $gapInfo       = new \objectInfo( $product );

            $heading[]  = array( 'text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>' );
            $contents[] = array( 'text' => TEXT_INFO_DELETE_INTRO . '<br>' );
            $contents[] = array( 'text' => '<br><b>' . $gapInfo->products_name . '</b>' );

            echo tep_draw_form( 'item_delete', FILENAME_SPECIALS, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</div>';
            echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
            echo '<div class="col_desc">'. $gapInfo->products_name . '</div>';
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

            $gap_id = (int) Yii::$app->request->post( 'item_id' );

            $messageType = 'success';
            $message     = TEXT_INFO_DELETED;

            tep_db_query( "delete from " . TABLE_GIVE_AWAY_PRODUCTS . " where gap_id = '" . (int) $gap_id . "'" );

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
    }