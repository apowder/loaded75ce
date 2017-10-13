<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
// Get last order id for checkout_success
    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by orders_id desc limit 1");
    $orders = tep_db_fetch_array($orders_query);
    $last_order = $orders['orders_id'];
  } else {
    $last_order = $HTTP_GET_VARS['order_id'];
  }
  $downloads_query = tep_db_query("select o.orders_status, date_format(o.last_modified, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.customers_id = '" . (int)$customer_id . "' and o.orders_status IN (" . DOWNLOADS_CONTROLLER_ORDERS_STATUS . ") and o.orders_id = '" . (int)$last_order . "' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != ''");

  if (tep_db_num_rows($downloads_query) > 0) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main">
<?php
  $info_box_contents = array();
  $info_box_contents[] = array('text' => HEADING_DOWNLOAD);

  new contentBoxHeading($info_box_contents, false, false);
  $info_box_contents = array();
  while ($downloads = tep_db_fetch_array($downloads_query)) {
    
// MySQL 3.22 does not have INTERVAL
    list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
    $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
    $download_expiry = date('Y-m-d H:i:s', $download_timestamp);
      if ( ($downloads['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) && ( ($downloads['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
        $text = '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads['orders_products_download_id']) . '">' . $downloads['products_name'] . '<br>' . tep_template_image_button('button_download.' . BUTTON_IMAGE_TYPE, '', 'class=transpng') . '</a>';
      } else {
        $text = $downloads['products_name'];
      }

    $info_box_contents[] = array(array('text' => $text),
                               array('text' => TABLE_HEADING_DOWNLOAD_DATE . '<br>' . \common\helpers\Date::date_long($download_expiry)),
                               array('text' => $downloads['download_count'] . TABLE_HEADING_DOWNLOAD_COUNT));
  }
  if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)){
    $info_box_contents[] = array(array('params' => 'colspan="3"', 'text' => sprintf(FOOTER_DOWNLOAD, '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . TEXT_MY_ACCOUNT . '</a>')));
  }
  new contentBox($info_box_contents);
?>
        </td>
      </tr>
<?php
// BOF: WebMakers.com Added: Downloads Controller
// If there is a download in the order and they cannot get it, tell customer about download rules
  $downloads_check_query = tep_db_query("select o.orders_id, opd.orders_products_download_id from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.orders_id = opd.orders_id and o.orders_id = '" . (int)$last_order . "' and opd.orders_products_filename != ''");

if (tep_db_num_rows($downloads_check_query) > 0 and tep_db_num_rows($downloads_query) < 1) {
// if (tep_db_num_rows($downloads_query) < 1) {
  echo '<tr><td>';
  $info_box_contents = array();
  $info_box_contents[] = array(
                               array('params' => 'align="center" class="main" width=100%', 'text' => DOWNLOADS_CONTROLLER_ON_HOLD_MSG)
                               );
  new contentBox($info_box_contents);
  echo '</td></tr>';
}
  }
