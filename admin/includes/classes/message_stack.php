<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  class messageStack extends tableBlock {
    var $size = 0;
    var $messageType = '';

    function __construct() {
      global $messageToStack;

      $this->errors = array();

      if (tep_session_is_registered('messageToStack')) {
        for ($i = 0, $n = sizeof($messageToStack); $i < $n; $i++) {
          $this->add($messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }
        tep_session_unregister('messageToStack');
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $type = 'danger';
        $this->errors[] = array('params' => $type, 'text' => tep_image(DIR_WS_ICONS . 'error.gif', ICON_ERROR) . '&nbsp;' . $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => $type, 'text' => tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message);
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => $type, 'text' => tep_image(DIR_WS_ICONS . 'success.gif', ICON_SUCCESS) . '&nbsp;' . $message);
      } else {
        $this->errors[] = array('params' => $type, 'text' => $message);
      }
      $this->messageType = $type;
      $this->size++;
    }

    function add_session($message, $type = 'error') {
      global $messageToStack;

      if (!tep_session_is_registered('messageToStack')) {
        tep_session_register('messageToStack');
        $messageToStack = array();
      }

      $messageToStack[] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output($simple = false) {
     if ($simple){
        $html = '';
        foreach($this->errors as $error){
          foreach($error as $key => $item){
            if ($key == 'text') $html .= $item . "<br>";
          }
        }
      return $html;
     } else {
         $html = '';
         foreach ($this->errors as $error) {
$html .= '<div class="popup-box-wrap pop-mess" style="top: 200px;">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                 <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading">'.TEXT_NOTIFIC.'</div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-' . $error['params'] . '">
                        ' . $error['text'] . '
                        </div>
                    </div>
                    <div class="noti-btn">
                            <div></div>
                            <div><span class="btn btn-primary">'.TEXT_BTN_OK.'</span></div>
                        </div>
            </div>
            <script>
            setTimeout(function(){
                $("body").scrollTop(0);
                $(".popup-box-wrap.pop-mess").insertAfter("#container");
                $(".pop-mess .pop-up-close-alert, .noti-btn .btn").click(function(){
                    $(this).parents(".pop-mess").remove();
                });}
                , 100);
            </script>
         </div>';
         }
         return $html;
      $this->table_data_parameters = 'class="messageBox"';
      return parent::__construct($this->errors);
      }
    }

      function  getErrors(){
          return $this->errors;
      }
  }
