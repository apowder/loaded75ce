<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

if(isset($mysql_error_dump) && !empty($mysql_error_dump[0])) {
?>
<div class="popup-box-wrap popup-box-wrap-mysql">
    <div class="around-pop-up"></div>
    <div class="popup-box">
        <div class="pop-up-close"></div>
        <div class="pop-up-content">
            <div class="popup-heading">Error</div>
            <div class="popup-content">
                <div id="id_mysql_error">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="heading">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="heading">You have an error in your SQL syntax</td>
                                        <td class="close"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="error">
                                <?php
                                for ($i = 0; $i <= count($mysql_error_dump); $i++) {
                                    echo $mysql_error_dump[$i];
                                    if ($i < (count($mysql_error_dump) - 1) && count($mysql_error_dump > 1)) {
                                        echo '<hr>';
                                    }
                                }
                                tep_session_unregister('mysql_error_dump');
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
    $('.pop-up-close').click(function(){
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        return false
      });
    });
    
</script>

<?php  
}

// close session (store variables)
  tep_session_close();

  if (STORE_PAGE_PARSE_TIME == 'true') {
    if (!is_object($logger)) $logger = new logger;
    echo $logger->timer_stop(DISPLAY_PAGE_PARSE_TIME);
  }
?>
