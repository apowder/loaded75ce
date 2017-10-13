<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

// close session (store variables)

if(isset($mysql_error_dump) && !empty($mysql_error_dump[0])) {
?>
<div id="id_mysql_error">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="heading">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="heading">You have an error in your SQL syntax</td>
          <td class="close"><a onclick="javascript: document.getElementById('id_mysql_error').style.display = 'none';" href="javascript: void(0);"><img src="templates/Original/images/close.gif" border="0" /></a></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="error">
<?php
  for($i = 0; $i <= count($mysql_error_dump); $i++) {
    echo $mysql_error_dump[$i];
    if($i < (count($mysql_error_dump) - 1) && count($mysql_error_dump > 1)) {
      echo '<hr>';
    }
  }
  tep_session_unregister('mysql_error_dump');
?>
    </td>
  </tr>
</table>
</div>
<?php
}
if (!tep_session_is_registered('customer_id') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache'))
{
  global $page_cache;
  global $REMOTE_ADDR;
  $page_cache->end_page_cache();
}
else
  tep_session_close();

  if (STORE_PAGE_PARSE_TIME == 'true') {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    if(STORE_PAGE_PARSE_IP == '*') {
      error_log('Trans total: ' . $trans_count . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
    elseif(ip2long(STORE_PAGE_PARSE_IP) !== false && STORE_PAGE_PARSE_IP == \common\helpers\System::get_ip_address()) {
      error_log('Trans total: ' . $trans_count . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
    else {
      error_log(STORE_PAGE_PARSE_IP . TEXT_INVALID_IP . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
    
    

    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
      echo '<span class="smallText">Parse Time: ' . $parse_time . 's</span>';
    }
  }
