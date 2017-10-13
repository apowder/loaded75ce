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
 * default controller to handle user requests.
 */
class Cache_controlController extends Sceleton  {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_CACHE_CONTROL'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'cache_control');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cache_control/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
      
    }
    
    public function actionFlush() {
        global $language;
        \common\helpers\Translation::init('admin/cache_control');
        
        $runtimePath = Yii::getAlias('@runtime');
        $messageType = 'warning';//success warning
        
        /**
         * Smarty
         */
        if (Yii::$app->request->post('smarty') == 1) {
            $smartyPath = $runtimePath . DIRECTORY_SEPARATOR . 'Smarty' . DIRECTORY_SEPARATOR . 'compile' . DIRECTORY_SEPARATOR . '*.*';
            array_map('unlink', glob($smartyPath));
            
        $message = TEXT_SMARTY_WARNING;
?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                        <?= $message?>
                    </div>  
                </div>     
                <div class="noti-btn noti-btn-ok">
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
        
<?php
        }
        
        /**
         * Debug
         */
        if (Yii::$app->request->post('debug') == 1) {
            $debugPath = $runtimePath . DIRECTORY_SEPARATOR . 'debug' . DIRECTORY_SEPARATOR . '*.*';
            array_map('unlink', glob($debugPath));
        $message = TEXT_DEBUG_WARNING;
?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                        <?= $message?>
                    </div>  
                </div>     
                <div class="noti-btn noti-btn-ok">
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
        
<?php
        }
        
        
        /**
         * Logs
         */
        if (Yii::$app->request->post('logs') == 1) {
            $logsPath = $runtimePath . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . '*.*';
            array_map('unlink', glob($logsPath));
        $message = TEXT_LOGS_WARNING;
?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                        <?= $message?>
                    </div>  
                </div>     
                <div class="noti-btn noti-btn-ok">
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
        
<?php
        }


      /**
       * Image cache
       */
      if (Yii::$app->request->post('image_cache') == 1) {
        \common\classes\Images::cacheFlush(true);

        $message = TEXT_IMAGE_CACHE_CLEANED;
        ?>
        <div class="popup-box-wrap pop-mess">
          <div class="around-pop-up"></div>
          <div class="popup-box">
            <div class="pop-up-close pop-up-close-alert"></div>
            <div class="pop-up-content">
              <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
              <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType?>">
                <?= $message?>
              </div>
            </div>
            <div class="noti-btn noti-btn-ok">
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

        <?php
      }

    }
    
}
