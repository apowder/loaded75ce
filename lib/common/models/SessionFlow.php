<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use yii\base\BootstrapInterface;
use yii\base\Application;
use app\components\InitFactory;
use frontend\design\Info;

class SessionFlow implements BootstrapInterface {
  
  public function bootstrap($app){
    global $session_started, $HTTP_SESSION_VARS, $request_type, $cookie_path, $cookie_domain;

  // set the session ID if it exists
   if (isset($_POST[tep_session_name()])) {
     tep_session_id($_POST[tep_session_name()]);
   } elseif ( ($request_type == 'SSL') && isset($_GET[tep_session_name()]) ) {
     tep_session_id($_GET[tep_session_name()]);
   }
   

// start the session
  $session_started = false;
  if (\frontend\design\Info::isTotallyAdmin()){
	tep_session_start();
	$session_started = true;
  } else {
	if (SESSION_FORCE_COOKIE_USE == 'True') {
		\common\helpers\System::setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $cookie_path, $cookie_domain);

		if (isset($_COOKIE['cookie_test'])) {
		  tep_session_start();
		  if (!tep_session_is_registered('referer_url')) { 
			$referer_url = $_SERVER['HTTP_REFERER']; 
			if ($referer_url) { 
			  tep_session_register('referer_url'); 
			} 
		  }
		  $session_started = true;
		}
	  } elseif (SESSION_BLOCK_SPIDERS == 'True') {
		$user_agent = strtolower(getenv('HTTP_USER_AGENT'));
		$spider_flag = false;

		if (tep_not_null($user_agent) && !\frontend\design\Info::isTotallyAdmin()) {
		  $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');

		  for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
			if (tep_not_null($spiders[$i])) {
			  if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
				$spider_flag = true;
				break;
			  }
			}
		  }
		}

		if ($spider_flag == false) {
		  tep_session_start();
		  if (!tep_session_is_registered('referer_url')) { 
			$referer_url = $_SERVER['HTTP_REFERER']; 
			if ($referer_url) { 
			  tep_session_register('referer_url'); 
			} 
		  }
		  $session_started = true;
		}
	  } else {
		tep_session_start();
		if (!tep_session_is_registered('referer_url')) { 
		  $referer_url = $_SERVER['HTTP_REFERER']; 
		  if ($referer_url) { 
			tep_session_register('referer_url'); 
		  } 
		}
		$session_started = true;
	  }	  
  }
  
  
  global $PHP_SELF;
  
  if (basename($PHP_SELF) == 'index.php' && !\frontend\design\Info::isTotallyAdmin()){

    $pl_currs = Info::platformCurrencies();
    if (!Info::isAdmin() && isset($_SESSION['currency']) && (is_array($pl_currs) && !in_array($_SESSION['currency'], $pl_currs) || (!$pl_currs and $_SESSION['currency'] != DEFAULT_CURRENCY))){
      tep_session_start(true);// be sure currency from payment is in platform currencies list
    }
    $pl_langs = Info::platformLanguages();
    $_code = \common\helpers\Language::get_language_code($_SESSION['languages_id']);
    if (!Info::isAdmin() && isset($_SESSION['languages_id']) && (is_array($pl_langs) && !in_array($_code['code'], $pl_langs) || (!$pl_langs and !in_array($_SESSION['languages_id'],  \common\helpers\Language::get_language_id(strtolower(DEFAULT_LANGUAGE)))))){
      tep_session_start(true);
    }

  }
  
  $HTTP_SESSION_VARS =& $_SESSION;  
  if (\frontend\design\Info::isTotallyAdmin()) {
	  \backend\components\AdminFactory::init();  
  } else {
	  InitFactory::init();  
  }
	  
  }
}