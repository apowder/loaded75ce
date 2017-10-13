<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

  class message_stack {

    function __construct() {
      global $messageToStack;
      $this->messages = array();
      if (tep_session_is_registered('messageToStack')) {
        for ($i=0, $n=sizeof($messageToStack); $i<$n; $i++) {
          $this->add($messageToStack[$i]['class'], $messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }
        tep_session_unregister('messageToStack');
      }
    }

// class methods
    function add($class, $message, $type = 'error') {
      if ($type == 'error') {
        $this->messages[] = array('.type'=>$type, '.message'=>$message, 'params' => 'class="info"', 'class' => $class, 'text' => /*tep_image(DIR_WS_ICONS . 'error.png', ICON_ERROR) . '&nbsp;' .*/ $message);
      } elseif ($type == 'warning') {
        $this->messages[] = array('.type'=>$type, '.message'=>$message, 'params' => 'class="info"', 'class' => $class, 'text' => /*tep_image(DIR_WS_ICONS . 'error.png', ICON_WARNING) . '&nbsp;' .*/ $message);
      } elseif ($type == 'success') {
        $this->messages[] = array('.type'=>$type, '.message'=>$message, 'params' => 'class="info"', 'class' => $class, 'text' => /*tep_image(DIR_WS_ICONS . 'success.gif', ICON_SUCCESS) . '&nbsp;' .*/ $message);
      } else {
        $this->messages[] = array('.type'=>'error', '.message'=>$message, 'params' => 'class="info"', 'class' => $class, 'text' => $message);
      }
      $this->save_to_base($class, $message, $type);
    }

    function add_session($class, $message, $type = 'error') {
      global $messageToStack;

      if (!tep_session_is_registered('messageToStack')) {
        tep_session_register('messageToStack');
        $messageToStack = array();
      }

      $messageToStack[] = array('class' => $class, 'text' => $message, 'type' => $type);
    }

    public function convert_to_session($only_class='',$replace_to_class='')
    {
      foreach($this->messages as $idx=>$_message) {
        if ( empty($only_class) || $only_class==$_message['class'] ) {
          $this->add_session((empty($replace_to_class)?$_message['class']:$replace_to_class), $_message['.message'], $_message['.type']);
          $this->remove_current($_message['class'], $_message['.message']);
          unset($this->messages[$idx]);
        }
      }
      $this->messages = array_values($this->messages);
    }
    
    public function remove_current($class, $message){
      global $cart, $customer_id;
      if (is_object($cart) && $cart->basketID && $customer_id){
        tep_db_query("delete from " . TABLE_CUSTOMERS_ERRORS . " where customers_id = '" . (int)$customer_id . "' and basket_id = '" . (int)$cart->basketID . "' and error_entity='" . tep_db_input($class) . "' and error_message = '" . tep_db_input($message) . "'");
      }
    }

    function reset() {
      $this->messages = array();
    }

    function output($class) {
      $this->table_data_parameters = 'class="messageBox"';

      $output = array();
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }
      $str_output = '';
      if ( count($output)>0 ) {
        $str_output .= '<div '.$this->table_data_parameters.'>';
        foreach( $output as $output_message ) {
          $str_output .= "<div {$output_message['params']}>{$output_message['text']}</div>";
          //$output_message['class'];
        }
        $str_output .= '</div>';
      }
      return $str_output;
//Lango Added for template mod: BOF
      //return $this->tableBoxMessagestack($output);
//Lango Added for template mod: EOF
    }

    function size($class) {
      $count = 0;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $count++;
        }
      }

      return $count;
    }
    
    function save_to_base($class, $message, $type, $title = ''){
      global $cart, $customer_id;
      if (is_object($cart) && $cart->basketID && $customer_id && $class != 'header' && ($type == 'error' || $type == 'warning')){
        $sql_array = array(
          'customers_id' => (int)$customer_id,
          'basket_id' => (int)$cart->basketID,
          'error_entity' => tep_db_prepare_input($class),
          'error_title' => tep_db_prepare_input($title),
          'error_message' => tep_db_prepare_input($message),
          'error_date' => 'now()'
        );
        tep_db_perform(TABLE_CUSTOMERS_ERRORS, $sql_array);
      }
    }
  }
