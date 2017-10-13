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
 * GV mail controller to handle user requests.
 */
class Gv_mailController extends Sceleton {

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_MAIL'];
    
    function __construct($id, $module = null) {
        if (false === \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'allowed')) {
            $this->redirect(array('/'));
        }
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language;

        \common\helpers\Translation::init('admin/coupon_admin');
		
        $this->selectedMenu = array('marketing', 'gv_admin', 'gv_mail');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('gv_mail/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $cid = Yii::$app->request->get('cid', 0);
		

		ob_start();
		echo tep_draw_form('mail',  FILENAME_GV_MAIL.'/preview'); 
    
    if (strpos(Yii::$app->request->referrer, 'customers')){
      echo tep_draw_hidden_field('referrer', Yii::$app->request->getReferrer());
      ?>
      <script>
           $('form[name=mail]').submit(function(){
              $.post($(this).attr('action'),
                $('form[name=mail]').serialize(),
                function(data){
                $('.pop-up-content').html(data);
              });             
              return false;
           })          
      </script>
      <?php
    }
    ?>
            <table border="0" cellpadding="0" cellspacing="2" width="100%">
              <tr>
                  <td width="8%"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    $customers = array();
    $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
    $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
    $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
    $mail_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " order by customers_lastname");
    while($customers_values = tep_db_fetch_array($mail_query)) {
      $customers[] = array('id' => $customers_values['customers_email_address'],
                           'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
    }
    
    if ($cid){
      $cc_query = tep_db_query("select coupon_id, coupon_code, coupon_amount, coupon_currency, coupon_type,  coupon_expire_date, (now() < coupon_expire_date or year(coupon_expire_date) in (0, 1970) ) as not_expired from " . TABLE_COUPONS . " where coupon_id = '" . (int) $cid . "'");
        $cc_list = tep_db_fetch_array($cc_query);
        $cInfo = new \objectInfo($cc_list);
        //echo '<pre>';print_r($cInfo);

        $currencies = new \common\classes\currencies();
        
        $currency = $cInfo->coupon_currency;
        if ($cInfo->coupon_type == 'P') {
          $amount = number_format($cInfo->coupon_amount, 2).'%';
        } else {
          $amount = $currencies->format($cInfo->coupon_amount, false, $currency);
        }      
        ?>
             <tr>
                <td class="main"><label><?php echo COUPON_CODE; ?></label></td>
                <td><?php echo tep_draw_hidden_field('coupon_id', $cInfo->coupon_id). $cInfo->coupon_code . '&nbsp;(' . $amount. ') ' . (!$cInfo->not_expired ? '&nbsp;<span style="color:#ff0000">' . TEXT_EXPIRED . '</span>' : '');?></td>
              </tr>
        <?php       
    }
?>
              <tr>
                <td class="main"><label><?php echo TEXT_CUSTOMER; ?></label></td>
                <td><?php echo tep_draw_pull_down_menu('customers_email_address', $customers, $_GET['customer'], 'class="form-control"');?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
               <tr>
                   <td class="main"><label><?php echo TEXT_TO; ?></label><br><br></td>
                <td><?php echo tep_draw_input_field('email_to', '', 'class="form-control"'); ?><?php echo '&nbsp;&nbsp;' . TEXT_SINGLE_EMAIL; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
             <tr>
                <td class="main"><label><?php echo TEXT_FROM; ?></label></td>
                <td><?php echo tep_draw_input_field('from', EMAIL_FROM, 'class="form-control"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><label><?php echo TEXT_SUBJECT; ?></label></td>
                <td><?php echo tep_draw_input_field('subject', '', 'class="form-control"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <?php
              
              if (!($cid && $cInfo->not_expired )){
                ?>
              <tr>
                <td valign="top" class="main"><label><?php echo TEXT_AMOUNT; ?></label></td>
                <td><?php echo tep_draw_input_field('amount','', 'class="form-control"'); ?></td>
              </tr>                
                <?php
              }
              ?>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
$class = '';
if (EMAIL_USE_HTML == 'true') {
	$class = 'ckeditor';
}
?>              
              <tr>
                <td valign="top" class="main" nowrap><label><?php echo TEXT_MESSAGE; ?></label></td>
	<td width="100%"><?php echo tep_draw_textarea_field('message', 'soft', '60', '15', '', "id='editor' class='{$class} form-control' style='width: 100%;'"); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                 <td colspan="2" align="right">
				  <input type="submit" class="btn btn-primary" value="<?=IMAGE_SEND_EMAIL?>" >
                </td>
              </tr>
            </table>
          </form>
<?php
		$buf = ob_get_contents();
		ob_clean();
		
	  if(Yii::$app->session->hasFlash('success')){
		Yii::$app->controller->view->errorMessage = Yii::$app->session->getFlash('success');
		Yii::$app->controller->view->errorMessageType = 'success';
	  }	elseif (Yii::$app->session->hasFlash('error')) {
		Yii::$app->controller->view->errorMessage = Yii::$app->session->getFlash('error');
		Yii::$app->controller->view->errorMessageType = 'error';
	  }
	   Yii::$app->session->removeAllFlashes();		
	   
	   if (Yii::$app->request->get('mail_sent_to')){
      Yii::$app->controller->view->errorMessage = sprintf(NOTICE_EMAIL_SENT_TO, urldecode(Yii::$app->request->get('mail_sent_to')));
      Yii::$app->controller->view->errorMessageType = 'notice';
	   }
  		
      if (Yii::$app->request->isAjax){
        if (Yii::$app->request->get('mail_sent_to')){
          return $this->renderAjax('index', ['content' => '']);
        } else {
          return $this->renderAjax('index', ['content' => $buf]);
        }
        
      } else {
        return $this->render('index', ['content' => $buf]);
      }
        
    }

	public function actionPreview(){

		\common\helpers\Translation::init('admin/gv_mail');	  
	
        $this->selectedMenu = array('marketing', 'gv_admin', 'gv_mail');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('gv_mail/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
	
	  if ( ($_POST['customers_email_address'] || $_POST['email_to']) ) {

		
		  
		if ( ($_GET['action'] == 'preview') && (!$_POST['amount'] && !isset($_POST['coupon_id'])) ) {
			Yii::$app->controller->view->errorMessage = ERROR_NO_AMOUNT_SELECTED;
			Yii::$app->controller->view->errorMessageType = 'error';
		}
		  
		switch ($_POST['customers_email_address']) {
		  case '***':
			$mail_sent_to = TEXT_ALL_CUSTOMERS;
			break;
		  case '**D':
			$mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
			break;
		  default:
			$mail_sent_to = $_POST['customers_email_address'];
			if ($_POST['email_to']) {
			  $mail_sent_to = $_POST['email_to'];
			}
			break;
		}
			ob_start();
			echo tep_draw_form('mail', FILENAME_GV_MAIL .'/sendemailtouser');
      
    if (strpos(Yii::$app->request->referrer, 'customers')){
      echo tep_draw_hidden_field('referrer', Yii::$app->request->getReferrer());
      ?>
      <script>
           $('form[name=mail]').submit(function(){
              $.post($(this).attr('action'),
                $('form[name=mail]').serialize(),
                function(data){
                $('.pop-up-content').html(data);
              });             
              return false;
           })          
      </script>
      <?php
    }      
      ?>
				<table border="0" width="100%" cellpadding="0" cellspacing="2">
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b><br><?php echo $mail_sent_to; ?></td>
				  </tr>
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td class="smallText"><b><?php echo TEXT_FROM; ?></b><br><?php echo htmlspecialchars(tep_db_prepare_input($_POST['from'])); ?></td>
				  </tr>
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br><?php echo htmlspecialchars(tep_db_prepare_input($_POST['subject'])); ?></td>
				  </tr>
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td class="smallText"><b><?php echo TEXT_AMOUNT; ?></b><br><?php echo nl2br(htmlspecialchars(tep_db_prepare_input($_POST['amount']))); ?></td>
				  </tr>
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td class="smallText"><b>  <?php if (EMAIL_USE_HTML == 'true') { echo (tep_db_prepare_input($_POST['message'])); } else { echo htmlspecialchars(tep_db_prepare_input($_POST['message'])); } ?></td>
				  </tr>
				  <tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
				  <tr>
					<td>
	<?php
	/* Re-Post all POST'ed variables */
		reset($_POST);
		while (list($key, $value) = each($_POST)) {
		  if (!is_array($_POST[$key])) {
			echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
		  }
		}
	?>
					<table border="0" width="100%" cellpadding="0" cellspacing="2">
					  <tr>
						
						 <tr>
						<td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_GV_MAIL) . '" class="btn btn-cancel">' . IMAGE_CANCEL . '</a> <input type="submit" class="btn btn-primary" value="'.IMAGE_SEND_EMAIL.'">'; ?></td>
						</tr>
						<td class="smallText">
					<?php if (EMAIL_USE_HTML == 'false'){echo tep_image_submit('button_back.gif', IMAGE_BACK, 'name="back"');
					} ?><?php if (EMAIL_USE_HTML == 'false') {echo(TEXT_EMAIL_BUTTON_HTML);
					 } else { echo(TEXT_EMAIL_BUTTON_TEXT); } ?>
						</td>
					  </tr>
					</table></td>
				 </tr>
				</table>
			  </form>
	<?php
		$buf = ob_get_contents();
		ob_clean();
  		
      if (Yii::$app->request->isAjax){
        return $this->renderAjax('index', ['content' => $buf]);
      } else {
        return $this->render('index', ['content' => $buf]);
      }
        
	  } else {
		  Yii::$app->session->setFlash('error', 'Please define customer or group');
		  return $this->redirect('index');
	  }
	
	}

	
	public function actionSendemailtouser(){

	  if ( ($_POST['customers_email_address'] || $_POST['email_to']) && !$_POST['back_x'] ) {
		
    $coupon_id  = Yii::$app->request->post('coupon_id', 0);
    
		\common\helpers\Translation::init('admin/gv_mail');
		
		switch ($_POST['customers_email_address']) {
		  case '***':
			$mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address, customers_id, platform_id from " . TABLE_CUSTOMERS);
			$mail_sent_to = TEXT_ALL_CUSTOMERS;
			break;
		  case '**D':
			$mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address, customers_id, platform_id from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");
			$mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
			break;
		  default:
			$customers_email_address = tep_db_prepare_input($_POST['customers_email_address']);

			$mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address, customers_id, platform_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "'");
			$mail_sent_to = $_POST['customers_email_address'];
			if ($_POST['email_to']) {
			  $mail_sent_to = $_POST['email_to'];
			}
			break;
		}

		$from = tep_db_prepare_input($_POST['from']);
		$subject = tep_db_prepare_input($_POST['subject']);
    $platforms = [];
		while ($mail = tep_db_fetch_array($mail_query)) {
      
      if (!isset($platforms[$mail['platform_id']])){
        $platform_query = tep_db_fetch_array(tep_db_query("select default_currency from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$mail['platform_id'] . "'"));
        $platforms[$mail['platform_id']] = $platform_query['default_currency'];
        if (!tep_not_null($platforms[$mail['platform_id']])) $platforms[$mail['platform_id']] = DEFAULT_CURRENCY;
      }
      
      $data = generate_customer_gvcc($coupon_id, $mail['customers_email_address'], $_POST['amount'], $platforms[$mail['platform_id']], $mail['customers_id']);

      $email_params = array();
      $email_params['STORE_NAME'] = STORE_NAME;
      $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link('', '', 'NONSSL'/*, $store['store_url']*/));

      $email_params['COUPON_CODE'] = $data['id1'];
      $email_params['COUPON_NAME'] = $subject;
      $email_params['COUPON_DESCRIPTION'] = $_POST['message'];
      $email_params['COUPON_AMOUNT'] = $data['amount'];

      list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Send coupon', $email_params, -1, $mail['platform_id']);
		  
		  \common\helpers\Mail::send($mail['customers_firstname'] . ' ' . $mail['customers_lastname'], $mail['customers_email_address'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

		}
		if (isset($_POST['email_to']) && !empty($_POST['email_to'])) {
      $data = generate_customer_gvcc($coupon_id, $_POST['email_to'], $_POST['amount'], DEFAULT_CURRENCY);
		  
      $email_params = array();
      $email_params['STORE_NAME'] = STORE_NAME;
      $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link('', '', 'NONSSL'/*, $store['store_url']*/));

      $email_params['COUPON_CODE'] = $data['id1'];
      $email_params['COUPON_NAME'] = $subject;
      $email_params['COUPON_DESCRIPTION'] = $_POST['message'];
      $email_params['COUPON_AMOUNT'] = $data['amount'];

      list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Send coupon', $email_params, -1, $mail['platform_id']);
     
     \common\helpers\Mail::send('', $_POST['email_to'], $email_subject, $email_text, '', $from);

		}
     
     if (Yii::$app->request->isAjax){
        if(Yii::$app->session->hasFlash('success')){
          $content = Yii::$app->session->getFlash('success');
        }	elseif (Yii::$app->session->hasFlash('error')) {
          $content = Yii::$app->controller->view->errorMessage = Yii::$app->session->getFlash('error');
        }
         Yii::$app->session->removeAllFlashes();		
         
         if ($mail_sent_to){
          $content = sprintf(NOTICE_EMAIL_SENT_TO, urldecode($mail_sent_to));
         }
       
       return $this->renderAjax('index', ['content' => $content]);
     } else {
       return $this->redirect(Yii::$app->urlManager->createUrl([FILENAME_GV_MAIL, 'mail_sent_to'=> urlencode($mail_sent_to), 'cid' => $coupon_id]));
     }
		
		
	  }

	
	}

}
