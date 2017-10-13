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

class AdminaccountController extends Sceleton
{
  
    public function __construct($id, $module=null){
      Translation::init('admin/admin_account');
      parent::__construct($id, $module);
    }
  
    public function actionIndex()
    {
        global $languages_id, $language;

        $this->selectedMenu = array('administrator', 'adminaccount');
        $this->navigation[] = array( 'link' => \Yii::$app->urlManager->createUrl( 'adminaccount/index' ), 'title' => HEADING_TITLE );
        $this->view->headingTitle = HEADING_TITLE;
        return $this->render('index');
    }

    function actionAdminaccountactions()
    {
        global $languages_id, $language;

        $this->layout = FALSE;

        $admin_id = (int) \Yii::$app->request->post( 'admin_id' );

        if( $admin_id == 0 ) {
            // Get my account
            $admin_id = tep_session_var( 'login_id' );
        }

        $myAccount = $this->getAdminObj( $admin_id );
				
        if( !is_array( $myAccount ) ) die( "Wrong admin id: $admin_id" );
						$languages = \common\helpers\Language::get_languages();
						for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {
							$languages[$i]['logo'] = $languages[$i]['image_svg'];
						}
        ?>
				<div class="change_avatar after">				
                    <div class="avatar_col"><div class="avatar<?php echo ((@GetImageSize(DIR_FS_CATALOG_IMAGES . $myAccount['avatar']) > 0) ? ' avatarImg': ' avatar_noimg');?>"><?php echo ((@GetImageSize(DIR_FS_CATALOG_IMAGES . $myAccount['avatar']) > 0) ? tep_image(DIR_WS_CATALOG_IMAGES . $myAccount['avatar'], $myAccount['admin_firstname'] . " " . $myAccount['admin_lastname']) : '<i class="icon-user"></i>' );?><a href="<?php echo \Yii::$app->urlManager->createUrl(['adminaccount/changeavatar']) ?>" class="avatar_edit popup"><i class="icon-pencil"></i></a><span class="avatar_delete popup" data-admin_id="<?php echo $myAccount['admin_id'];?>" onclick="return deleteImage();"><i class="icon-trash"></i></span></div>
				</div>
	<div class="account_wrapper_col">
		<div class="account_wrapper_row after">
			<div class="account_col">
				<div class="account1">
					<div class="ac_name"><?php echo TEXT_INFO_FULLNAME; ?></div>
					<div class="ac_value"><a href="<?php echo \Yii::$app->urlManager->createUrl(['adminaccount/nameform']) ?>" class="popup"><?php echo $myAccount['admin_firstname'] . " " . $myAccount['admin_lastname']; ?></a></div>
				</div>    
				<div class="account3">
					<div class="ac_name"><?php echo TEXT_INFO_PASSWORD; ?></div>
					<div class="ac_value"><?php echo '<a class="change_pass popup" href="' . \Yii::$app->urlManager->createUrl(['adminaccount/getpassword']) . '"><span>' . TEXT_INFO_PASSWORD_HIDDEN . '</span></a>'; ?></div>
				</div>
			</div>
			<div class="account_col">
				<div class="account2">
					<div class="ac_name"><?php echo TEXT_INFO_EMAIL; ?></div>
					<div class="ac_value"><a href="<?php echo \Yii::$app->urlManager->createUrl(['adminaccount/emailform']) ?>" class="popup"><?php echo $myAccount['admin_email_address']; ?></a></div>
				</div>
				<div class="account9">
					<div class="ac_name"><?php echo TEXT_INFO_USERNAME; ?></div>
					<div class="ac_value"><a href="<?php echo \Yii::$app->urlManager->createUrl(['adminaccount/usernameform']) ?>" class="popup"><?php echo ($myAccount['admin_username'] ? $myAccount['admin_username'] : TEXT_CHANGE_USERNAME); ?></a></div>
				</div>
			</div>
		</div>
		<div class="account_wrapper_row after">
			<div class="account_col">
				<div class="account4">
					<div class="ac_name"><?php echo TEXT_INFO_GROUP; ?></div>
					<div class="ac_value"><?php echo $myAccount['access_levels_name']; ?></div>
				</div>
				<div class="account5">
					<div class="ac_name"><?php echo TEXT_INFO_CREATED; ?></div>
					<div class="ac_value"><?php echo \common\helpers\Date::date_short( $myAccount['admin_created'] ); ?></div>
				</div>		
				<div class="account8">
					<div class="ac_name"><?php echo TEXT_INFO_MODIFIED; ?></div>
					<div class="ac_value"><?php echo \common\helpers\Date::date_short( $myAccount['admin_modified'] ); ?></div>
				</div>
			</div>
			<div class="account_col">		
				<div class="account6">
					<div class="ac_name"><?php echo TEXT_INFO_LOGNUM; ?></div>
					<div class="ac_value"><?php echo $myAccount['admin_lognum']; ?></div>
				</div>
				<div class="account7">
					<div class="ac_name"><?php echo TEXT_INFO_LOGDATE; ?></div>
					<div class="ac_value"><?php echo \common\helpers\Date::date_short( $myAccount['admin_logdate'] ); ?></div>
				</div>				
			</div>
		</div>
	</div>
</div>
        <!--<p class="btn-toolbar">
            <input class="btn btn-primary" type="button" onclick="return getChangeForm()" value="<?php echo TEXT_LABEL_CHANGE_ACCOUNT_DATA; ?>">
        </p>-->

    <?php
    }

    function getAdminObj( $admin_id )
    {
        $query = tep_db_query( "
              select distinct(a.admin_id), a.admin_groups_id, a.admin_firstname, a.admin_lastname,
              a.admin_email_address, a.admin_password, a.admin_created, a.admin_modified, a.admin_logdate,
              a.admin_lognum, a.individual_id,
              g.access_levels_name, a.avatar, a.admin_username
              from " . TABLE_ADMIN . " a LEFT JOIN " . TABLE_ACCESS_LEVELS . " g ON (a.access_levels_id = g.access_levels_id)
              where a.admin_id = '" . (int) $admin_id . "'" );

        $myAccount = tep_db_fetch_array( $query );

        if (!is_array($myAccount))  die("Wrong data.");

        return $myAccount;

    }
    function actionSaveaccount()
    {
        global $languages_id, $language;

        $this->layout = FALSE;
        $error        = FALSE;
        $message      = '';
        $messageType  = 'success';
        $html = "";

        $hiddenPassword = TEXT_INFO_PASSWORD_HIDDEN;
        $login_id  = (int) tep_session_var( 'login_id' );
        $myAccount = $this->getAdminObj( $login_id );
        if( is_array( $myAccount ) ) $mInfo = new \objectInfo( $myAccount );

        $admin_id = (int) \Yii::$app->request->post( 'admin_id' );

        if( $login_id !== $admin_id ) {
            $error   = TRUE;
            $message = TEXT_MESS_WRONG_DATA;
        }
				
            $popupname        = \Yii::$app->request->post( 'popupname' );
            $admin_email_address = '';
            $stored_email = array();
            if($popupname == 'name'){
                $admin_firstname        = \Yii::$app->request->post( 'admin_firstname' );
                $admin_lastname         = \Yii::$app->request->post( 'admin_lastname' );
            }elseif($popupname == 'email'){
                $admin_email_address    = \Yii::$app->request->post( 'admin_email_address' );
                $stored_email[]      = 'NONE';

                $check_email_query = tep_db_query( "select admin_email_address from " . TABLE_ADMIN . " where admin_id <> " . $admin_id . "" );
                while( $check_email = tep_db_fetch_array( $check_email_query ) ) {
                                $stored_email[] = $check_email['admin_email_address'];
                }		
                if( in_array( $admin_email_address, $stored_email )) {
                    $error   = TRUE;
                    $message = TEXT_MESS_EMAIL_EXISTS;
                }
            }elseif($popupname == 'password'){
                $password_confirmation = \Yii::$app->request->post( 'password_confirmation' );
                $admin_password         = \Yii::$app->request->post( 'admin_password' );
                $admin_password_confirm = \Yii::$app->request->post( 'admin_password_confirm' );

                $check_pass_query = tep_db_query( "select admin_password as confirm_password from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'" );
                $check_pass       = tep_db_fetch_array( $check_pass_query );

                if($admin_password != $admin_password_confirm ) {	
                        $message = TEXT_MESS_PASSWORD_WRONG;
                        $error   = TRUE;
                }								
            }elseif($popupname == 'group'){
                $admin_groups_id = \Yii::$app->request->post( 'admin_groups_id' );
            }elseif($popupname == 'avatar'){
                $avatar = new \upload($_POST['avatar']);
                $file_name = Uploads::move($_POST['avatar']);
                $avatar_img = $file_name ? $file_name : '';
            }elseif($popupname == 'admin_username'){
				$admin_username = \Yii::$app->request->post( 'admin_username' );
			} 

            if($error === FALSE ) {
                if($popupname == 'name'){
                        $sql_data_array['admin_firstname'] = tep_db_prepare_input( $admin_firstname );
                        $sql_data_array['admin_lastname'] = tep_db_prepare_input( $admin_lastname );
                }elseif($popupname == 'email'){
                        $sql_data_array['admin_email_address'] = tep_db_prepare_input( $admin_email_address );
                }elseif($popupname == 'password'){
                        $sql_data_array['admin_password'] = \common\helpers\Password::encrypt_password(tep_db_prepare_input($admin_password));
                }elseif($popupname == 'group'){
                        $sql_data_array['admin_groups_id'] = tep_db_prepare_input($admin_groups_id);
                }elseif($popupname == 'avatar'){
                        $sql_data_array['avatar'] = tep_db_prepare_input($avatar_img);
                }elseif($popupname == 'admin_username'){
                        $sql_data_array['admin_username'] = tep_db_prepare_input($admin_username);
                }					

                $sql_data_array['admin_modified'] = 'now()';


            tep_db_perform( TABLE_ADMIN, $sql_data_array, 'update', 'admin_id = \'' . $admin_id . '\'' );

            $data_query = tep_db_query( "select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'" );
            $data = tep_db_fetch_array( $data_query );
            
            //{{
            if ($popupname == 'password'){
              $email_params = array();
              $email_params['STORE_NAME'] = STORE_NAME;
              $email_params['NEW_PASSWORD'] = $admin_password;
              $email_params['CUSTOMER_FIRSTNAME'] = $data['admin_firstname'];
              $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN);
              $email_params['CUSTOMER_EMAIL'] = $data['admin_email_address'];
              list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin Password Forgotten', $email_params);
              \common\helpers\Mail::send($data['admin_firstname'] . ' ' . $data['admin_lastname'], $data['admin_email_address'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_params);              
            } else {
              \common\helpers\Mail::send($data['admin_firstname'] . ' ' . $data['admin_lastname'], $data['admin_email_address'],
                              ADMIN_EMAIL_SUBJECT,
                              sprintf(ADMIN_EMAIL_TEXT, $data['admin_firstname'], \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN), $data['admin_email_address'], $hiddenPassword, STORE_OWNER),
                              STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            } //}}

            //}}

            $message = TEXT_MESS_DATA_CHANGE_SUCCESS;
        }
        if( $error === TRUE ) {
            $messageType = 'warning';
        }
        if( $message != '' ) {
            ?>
            <div class="alert alert-<?= $messageType ?> fade in">
                <i data-dismiss="alert" class="icon-remove close"></i>
                <?= $message ?>
            </div>
            <?php //echo $html ?>
        <?php
        }
    }
    function actionNameform(){
        global $languages_id, $language;

        $this->layout = false;
        $this->view->usePopupMode = true;
        
      $login_id  = (int) tep_session_var( 'login_id' );  

        $myAccount = $this->getAdminObj( $login_id );

				$html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] ) . tep_draw_hidden_field( 'popupname', 'name' );
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_FIRSTNAME . '</td>
                    <td class="dataTableContent">'. tep_draw_input_field( 'admin_firstname', $myAccount['admin_firstname'], 'class="form-control"' ) .'</td>
                </tr>
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_LASTNAME . '</td><td class="dataTableContent">' . tep_draw_input_field( 'admin_lastname', $myAccount['admin_lastname'], 'class="form-control"') . '</td>
                </tr>
            </table>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';

        return $html;
    }
		function actionEmailform(){
        global $languages_id, $language;

        $this->layout = false;
        $this->view->usePopupMode = true;
        
      $login_id  = (int) tep_session_var( 'login_id' );  

        $myAccount = $this->getAdminObj( $login_id );

				$html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] ) . tep_draw_hidden_field( 'popupname', 'email' );
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_EMAIL . '</td>
                    <td class="dataTableContent">' . tep_draw_input_field( 'admin_email_address', $myAccount['admin_email_address'], 'class="form-control"' ) . '</td>
                </tr>
            </table>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';

        return $html;
    }
		function actionPasswordform(){
        global $languages_id, $language;

        $this->layout = false;
        $this->view->usePopupMode = true;
        
				$login_id  = (int) tep_session_var( 'login_id' );  

        $myAccount = $this->getAdminObj( $login_id );

				$html = tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] ) . tep_draw_hidden_field( 'popupname', 'password' );
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="dataTableContent">'.TEXT_INFO_PASSWORD_NEW.'</td>
                    <td class="dataTableContent">'.tep_draw_password_field( 'admin_password' ).'</td>
                </tr>
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_PASSWORD_CONFIRM . '</td>
                    <td class="dataTableContent">'.tep_draw_password_field( 'admin_password_confirm' ).'</td>
                </tr>
            </table>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form>';

        return $html;
    }
    
    function actionChangeavatar(){
        global $languages_id, $language;

        $this->layout = false;
        $this->view->usePopupMode = true;
				$login_id  = (int) tep_session_var( 'login_id' );  
        $myAccount = $this->getAdminObj( $login_id );
				$html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] ) . tep_draw_hidden_field( 'popupname', 'avatar' );
        $html .= '<div class="avatar_img"><div class="upload" data-name="avatar"></div></div>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';
				$html .='<script type="text/javascript">$(document).ready(function(){$(".upload").uploads1();})</script>';
        return $html;
    }
    
    function actionGetpassword()
    {
			global $languages_id, $language;
		
			$this->layout = false;
			$this->view->usePopupMode = true;
			$login_id  = (int) tep_session_var( 'login_id' );  
			$myAccount = $this->getAdminObj( $login_id );
			$html = '<div id="accountpopup">' . tep_draw_form('check_pass_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="check_pass_form" onSubmit="return checkPassword();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] );
			$html .= '<table cellspacing="0" cellpadding="0" width="100%">
							<tr>
									<td class="dataTableContent">'.TEXT_INFO_PASSWORD_CURRENT.'</td>
									<td class="dataTableContent">'.tep_draw_password_field( 'password_confirmation' ).'</td>
							</tr>
					</table>
					<div class="btn-bar">
							<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
							<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
					</div></form></div>';
			return $html;
		}
    function actionCheckpassword(){
            global $languages_id, $language;
			
			$this->layout = FALSE;
			//$this->view->usePopupMode = true;
		//	die('dffsdf');
			$login_id  = (int) tep_session_var( 'login_id' );  
      $myAccount = $this->getAdminObj( $login_id );
			$password_confirmation = \Yii::$app->request->post( 'password_confirmation' );
		//	die($password_confirmation);
			$check_pass_query = tep_db_query( "select admin_password as confirm_password from " . TABLE_ADMIN . " where admin_id = '" . $myAccount['admin_id']. "'" );
			$check_pass       = tep_db_fetch_array( $check_pass_query );

			 if( !\common\helpers\Password::validate_password( $password_confirmation, $check_pass['confirm_password'] ) ) {
			?>
				<div class="alert alert-warning fade in">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <?php echo TEXT_MESS_PASSWORD_WRONG; ?>
        </div>
			<?php
			}else{
				return $this->actionPasswordform();
			}
		}
		function actionDeleteimage(){
			global $languages_id, $language;
      
			$this->layout = FALSE;
			$this->view->usePopupMode = true;

			$login_id  = (int) tep_session_var( 'login_id' );  
      $myAccount = $this->getAdminObj( $login_id );

			$sql_data_array['avatar'] = '';
			tep_db_perform( TABLE_ADMIN, $sql_data_array, 'update', 'admin_id = \'' . $myAccount['admin_id'] . '\'' );	

			?><div class="popup-box-wrap delete_popup"><div class="around-pop-up"></div><div class="popup-box"><div class="popup-heading cat-head"><?php echo TEXT_EDITING_ACCOUNT;?></div><div class="pop-up-content">
								<div class="alert alert-success fade in">
										<i data-dismiss="alert" class="icon-remove close"></i>
										<?php echo TEXT_MESSTYPE_SUCCESS; ?>
								</div>
				</div></div></div>	
			<?php
			
		}
		function actionUsernameform(){
			global $languages_id, $language;
	
			$this->layout = false;
			$this->view->usePopupMode = true;
			
		$login_id  = (int) tep_session_var( 'login_id' );  
	
			$myAccount = $this->getAdminObj( $login_id );
	
					$html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"' ) . tep_draw_hidden_field( 'admin_id', $myAccount['admin_id'] ) . tep_draw_hidden_field( 'popupname', 'admin_username' );
			$html .= '<table cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="dataTableContent">' . TEXT_INFO_USERNAME . '</td>
						<td class="dataTableContent">'. tep_draw_input_field( 'admin_username', $myAccount['admin_username'], 'class="form-control"' ) .'</td>
					</tr>
				</table>
				<div class="btn-bar">
					<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
					<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
				</div></form></div>';
	
			return $html;
		}
}