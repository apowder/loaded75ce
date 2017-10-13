<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\contact;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\classes\ReCaptcha;

class ContactForm extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    $captcha = new ReCaptcha();
  
    $info = [];
    if ($_GET['action'] == 'send'){
      $_POST['email'] = preg_replace( "/\n/", " ", $_POST['email'] );
      $_POST['email'] = preg_replace( "/\r/", " ", $_POST['email'] );
      $_POST['email'] = str_replace("Content-Type:","",$_POST['email']);
      $_POST['name'] = preg_replace( "/\n/", " ", $_POST['name'] );
      $_POST['name'] = preg_replace( "/\r/", " ", $_POST['name'] );
      $_POST['name'] = str_replace("Content-Type:","",$_POST['name']);

      $name = tep_db_prepare_input($_POST['name']);
      $email_address = tep_db_prepare_input($_POST['email']);
      $enquiry = tep_db_prepare_input($_POST['enquiry']);

      if (!\common\helpers\Validations::validate_email($email_address)) {
        $error = true;
        $info[] =  ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
      }
      
      if (strtolower($this->settings[0]['show_captcha']) == 'on'){
          if (!$captcha->checkVerification($_POST['g-recaptcha-response'])){
            $error = true;
            $info[] = UNSUCCESSFULL_ROBOT_VERIFICATION;
          }
      }


      if (!$error) {
        $data = Info::platformData();
//        \common\helpers\Mail::send($data['owner'], $data['email_address'], EMAIL_SUBJECT, $enquiry, $name, $email_address);
        \common\helpers\Mail::send($data['owner'], $data['email_address'], EMAIL_SUBJECT, $enquiry, $name, $email_address, array(), 'Reply-To: "' . $name . '" <' . $email_address . '>');
        tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success'));
      }
    }
    
    
    
    return IncludeTpl::widget(['file' => 'boxes/contact/contact-form.tpl', 'params' => [
      'link' => tep_href_link('contact/index', 'action=send'),
      'info' => $info,
      'action' => $_GET['action'],
      'settings' => $this->settings,
      'captcha_enabled' => $captcha->isEnabled()
    ]]);
  }
}