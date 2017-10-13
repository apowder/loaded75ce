<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\widgets;


class WarningWidget extends \yii\bootstrap\Widget
{
    public function init()
    {
        parent::init();

    }
	
	public function run(){
   global $messageStack;
   
    if (WARN_INSTALL_EXISTENCE == 'true') {
      if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/install')) {
        $messageStack->add('header', sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, dirname($_SERVER['SCRIPT_FILENAME'])), 'warning');
      }
    }

  // check if the configure.php file is writeable
    if (WARN_CONFIG_WRITEABLE == 'true') {
      if ( (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) && (is_writeable(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) ) {
        //$messageStack->add('header', sprintf(WARNING_CONFIG_FILE_WRITEABLE, dirname($_SERVER['SCRIPT_FILENAME'])), 'warning');
      }
    }

  // check if the session folder is writeable
    if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
      if (STORE_SESSIONS == '') {
        if (!is_dir(tep_session_save_path())) {
          $messageStack->add('header', sprintf(WARNING_SESSION_DIRECTORY_NON_EXISTENT, tep_session_save_path()), 'warning');
        } elseif (!is_writeable(tep_session_save_path())) {
          $messageStack->add('header', sprintf(WARNING_SESSION_DIRECTORY_NOT_WRITEABLE, tep_session_save_path()), 'warning');
        }
      }
    }


  // give the visitors a message that the website will be down at ... time
    if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'false') ) {
         $messageStack->add('header', TEXT_BEFORE_DOWN_FOR_MAINTENANCE . PERIOD_BEFORE_DOWN_FOR_MAINTENANCE, 'warning');
    }


  // this will let the admin know that the website is DOWN FOR MAINTENANCE to the public
    if ( (DOWN_FOR_MAINTENANCE == 'true') && (EXCLUDE_ADMIN_IP_FOR_MAINTENANCE == getenv('REMOTE_ADDR')) ) {
         $messageStack->add('header', TEXT_ADMIN_DOWN_FOR_MAINTENANCE, 'warning');
    }

  // check session.auto_start is disabled
    if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
      if (ini_get('session.auto_start') == '1') {
        $messageStack->add('header', WARNING_SESSION_AUTO_START, 'warning');
      }
    }

    if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
      if (!is_dir(DIR_FS_DOWNLOAD)) {
        $messageStack->add('header', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
      }
    }

    if ($messageStack->size('header') > 0) {
      echo $messageStack->output('header');
    }

    if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
  ?>
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr class="headerError">
      <td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?></td>
    </tr>
  </table>
  <?php
    }

    if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
  ?>
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr class="headerInfo">
      <td class="headerInfo"><?php echo htmlspecialchars($_GET['info_message']); ?></td>
    </tr>
  </table>
  <?php
    }   

	}
}
