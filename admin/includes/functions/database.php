<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (USE_PCONNECT == 'true') {
      $server = 'p:' . $server;
    }

    $$link = mysqli_connect($server, $username, $password);

    $query="set names 'utf8'";
    $result=tep_db_query($query);

    if ($$link) 
    {
      $db_selected = mysqli_select_db($$link,$database);
    }
    if(!$db_selected)
    {
      tep_db_close();
      $$link=false;
    }

    return $$link;
  }

  function tep_db_close($link = 'db_link') {
    global $$link;

    return mysqli_close($$link);
  }

  function tep_db_error($query, $errno, $error) {
    global $mysql_errors, $mysql_error_dump;
    $degug_info = debug_backtrace();

    // {{ log to file
    $log_error = 'URI: ' . (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'--') . "\n";
    $log_error .= 'Error: ['.$errno.'] ' . $error . "\n";
    $log_error .= 'Query: ' . $query . "\n";
    $debug_size = sizeof($degug_info)-1;
    for ($i=$debug_size; $i>0; $i--) {
        $log_error .= 'Line ['.($debug_size-$i).']: ' . str_replace((defined('DIR_FS_CATALOG')?DIR_FS_CATALOG:''), '', $degug_info[$i]['file']).':'.$degug_info[$i]['line']."\n";
    }
    // }} log to file

    $file_name = '';
    $error_in_line = '';
    if(is_array( $degug_info['1'])) {
      $file_name = str_replace(DIR_FS_CATALOG, '', $degug_info['1']['file']) . '<br>';
      $file_name = 'Filename: ' . $file_name;
      $error_in_line = 'Line: ' . $degug_info['1']['line'] . '<br><br>';
    }
    if(isset($mysql_error_dump)) {
      $mysql_error_dump[] = '<b>' . $errno . ' - ' . $error . '</b><br><br>' . $query . '<br><br>' . $file_name . $error_in_line;
    }
    else {
      $mysql_errors[] = '<b>' . $errno . ' - ' . $error . '</b><br><br>' . $query . '<br><br>' . $file_name . $error_in_line;
    }
    if ( class_exists('\Yii',false) ) {
        \Yii::error($log_error,'sql_error');
    }
  }

  function tep_db_query($query, $link = 'db_link') {
    global $$link, $logger;

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
      $start = microtime(true);
      if (!is_object($logger)) $logger = new logger;
      $logger->write($query, 'QUERY');
    }

    $result = mysqli_query($$link,$query) or tep_db_error($query, mysqli_errno($$link), mysqli_error($$link));

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
      if (mysqli_error($$link)) $logger->write(mysqli_error($$link), 'ERROR');
       $time_end =  microtime(true);
       $parse_time = $time_end - $start;
       $logger->write('Query execution: ' . $parse_time . ' ms ', 'QUERY');
    }

    return $result;
  }

  function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return tep_db_query($query, $link);
  }

  function tep_db_fetch_array($db_query) {
    return @mysqli_fetch_array($db_query, MYSQLI_ASSOC);
  }

  function tep_db_result($result, $row, $field = '') {
    if ( $field === '' ) {
      $field = 0;
    }

    tep_db_data_seek($result, $row);
    $data = tep_db_fetch_array($result);

    return $data[$field];
  }
  
  function tep_db_field_exists($table,$field) {

    $describe_query = tep_db_query("describe $table");
    while($d_row = tep_db_fetch_array($describe_query))
    {
      if ($d_row["Field"] == "$field")
      return true;
    }

    return false;
  }

  function tep_db_num_rows($db_query) {
    return @mysqli_num_rows($db_query);
  }

  function tep_db_data_seek($db_query, $row_number) {
    return mysqli_data_seek($db_query, $row_number);
  }

  function tep_db_insert_id($link = 'db_link') {
    global $$link;

    return mysqli_insert_id($$link);
  }


  function tep_db_free_result($db_query) {
    return mysqli_free_result($db_query);
  }

  function tep_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
  }

  function tep_db_output($string) {
    return htmlspecialchars($string);
  }

  function tep_db_input($string, $link = 'db_link') {
    global $$link;
    
    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string($$link,$string);
    } elseif (function_exists('mysqli_escape_string')) {
      return mysqli_escape_string($$link,$string);
    }
   
    return addslashes($string);
  }

  function tep_db_prepare_input($string, $trim = true) {
    if (is_string($string)) {
      if ($trim){
        return trim($string);
      }else{
        return $string;
      }
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_db_prepare_input($value, $trim);
      }
      return $string;
    } else {
      return $string;
    }
  }
  
  function tep_db_affected_rows($link = 'db_link') {
    global $$link;

    return mysqli_affected_rows($$link);
  }

  function tep_db_get_server_info($link = 'db_link') {
    global $$link;

    return mysqli_get_server_info($$link);
  }


  function tep_db_input_mc($string){
    if (get_magic_quotes_gpc()){
      return $string;
    }else{
      return addslashes($string);
    }
  }
  
  ///////////////////////////////////////////////
  ///////////////////////////////////////////////
  ///////////////////////////////////////////////
  if ( !function_exists('mysqli_connect') ) {
    define('MYSQLI_ASSOC', MYSQL_ASSOC);

  function mysqli_connect($server, $username, $password, $database) {
      if ( substr($server, 0, 2) == 'p:' ) {
        $link = mysql_pconnect(substr($server, 2), $username, $password);
      } else {
        $link = mysql_connect($server, $username, $password);
      }

      if ( $link ) {
        mysql_select_db($database, $link);
      }

      return $link;
    }

    function mysqli_connect_errno($link = null) {
      if ( is_null($link) ) {
        return mysql_errno();
      }

      return mysql_errno($link);
    }

    function mysqli_connect_error($link = null) {
      if ( is_null($link) ) {
        return mysql_error();
      }

      return mysql_error($link);
    }

    function mysqli_set_charset($link, $charset) {
      if ( function_exists('mysql_set_charset') ) {
        return mysql_set_charset($charset, $link);
      }
    }

    function mysqli_close($link) {
      return mysql_close($link);
    }

    function mysqli_query($link, $query) {
      return mysql_query($query, $link);
    }

    function mysqli_errno($link = null) {
      if ( is_null($link) ) {
        return mysql_errno();
      }

      return mysql_errno($link);
    }

    function mysqli_error($link = null) {
      if ( is_null($link) ) {
        return mysql_error();
      }

      return mysql_error($link);
    }

    function mysqli_fetch_array($query, $type) {
      return mysql_fetch_array($query, $type);
    }

    function mysqli_num_rows($query) {
      return mysql_num_rows($query);
    }

    function mysqli_data_seek($query, $offset) {
      return mysql_data_seek($query, $offset);
    }

    function mysqli_insert_id($link) {
      return mysql_insert_id($link);
    }

    function mysqli_free_result($query) {
      return mysql_free_result($query);
    }

    function mysqli_fetch_field($query) {
      return mysql_fetch_field($query);
    }

    function mysqli_real_escape_string($link, $string) {
      if ( function_exists('mysql_real_escape_string') ) {
        return mysql_real_escape_string($string, $link);
      } elseif ( function_exists('mysql_escape_string') ) {
        return mysql_escape_string($string);
      }

      return addslashes($string);
    }

    function mysqli_affected_rows($link) {
      return mysql_affected_rows($link);
    }

    function mysqli_get_server_info($link) {
      return mysql_get_server_info($link);
    }
  }
