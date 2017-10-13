<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class Order {

  public static function is_stock_updated($order_id){
    $get_stock_status = tep_db_fetch_array(tep_db_query(
      "SELECT stock_updated FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$order_id."'"
    ));
    return !!( $get_stock_status['stock_updated'] );
  }

  public static function restock($order_id){
    if (!self::is_stock_updated($order_id)) return;
            $order_query = tep_db_query("select if(length(uprid),uprid, products_id) as uprid, products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $order_id . "'");
            while ($order = tep_db_fetch_array($order_query)) {
                global $login_id;
                \common\helpers\Product::log_stock_history_before_update($order['uprid'], $order['products_quantity'], '+',
                                                                         ['comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $order_id]);
                tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                \common\helpers\Product::update_stock($order['uprid'], $order['products_quantity'], 0);
            }
  }

    public static function remove_order($order_id, $restock = false) {
        if ($restock == 'on') {
            self::restock($order_id);
        }

        tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "'");
    }

    public static function get_order_status_name($order_status_id, $language_id = '') {
        global $languages_id;

        if ($order_status_id < 1)
            return TEXT_DEFAULT;

        if (!is_numeric($language_id))
            $language_id = $languages_id;

        $status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int) $order_status_id . "' and language_id = '" . (int) $language_id . "'");
        $status = tep_db_fetch_array($status_query);

        return $status['orders_status_name'];
    }

    public static function get_status($default = '') {
        global $languages_id;

        $status_array = array();
        if (!empty($default)) {
            $status_array[] = array(
                'id' => '',
                'text' => $default);
        }
        $status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' order by orders_status_name");
        while ($status = tep_db_fetch_array($status_query)) {
            $status_array[] = array(
                'id' => $status['orders_status_id'],
                'text' => $status['orders_status_name']);
        }
        return $status_array;
    }

    public static function getStatusesGrouped()
    {
        $status = [];
        $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' order by orders_status_groups_id");
        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
            $status[] = [
                'text' => $orders_status_groups['orders_status_groups_name'],
                'id' => 'group_' . $orders_status_groups['orders_status_groups_id'],
            ];
            $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and orders_status_groups_id='" . $orders_status_groups['orders_status_groups_id'] . "' order by orders_status_name");
            while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                $status[] = [
                    'text' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $orders_status['orders_status_name'],
                    'id' => 'status_' . $orders_status['orders_status_id'],
                ];
            }
        }
        return $status;
    }

    public static function extractStatuses($statuses_string)
    {
        $statuses = array();
        foreach (explode(',',$statuses_string) as $check_status){
            $check_status = trim($check_status);
            if ( strpos($check_status,'group_')===0 ) {
                $orders_status_query = tep_db_query("select distinct orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id='" . intval( str_replace('group_','', $check_status) ) . "' ");
                while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                    $statuses[(int)$orders_status['orders_status_id']] = (int)$orders_status['orders_status_id'];
                }
            }elseif( strpos($check_status,'status_')===0 ){
                $status_id = intval( str_replace('status_','', $check_status) );
                $statuses[ (int)$status_id ] = (int)$status_id;
            }elseif( (int)$check_status!=0 ){
                $statuses[ (int)$check_status ] = (int)$check_status;
            }
        }

        return array_values($statuses);
    }

    public static function orders_status_groups_name($orders_status_groups_id, $language_id = '') {
        global $languages_id;

        if (!$language_id)
            $language_id = $languages_id;
        $orders_status_groups_query = tep_db_query("select orders_status_groups_name from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int) $language_id . "'");
        $orders_status_groups = tep_db_fetch_array($orders_status_groups_query);

        return $orders_status_groups['orders_status_groups_name'];
    }

    public static function get_status_name($id_status) {
        global $languages_id;
        if (strlen(trim($id_status)) == 0) {
            return TEXT_NO_STATUS;
        } else {
            $status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' and orders_status_id IN (" . $id_status . ") order by orders_status_name");
            while ($status = tep_db_fetch_array($status_query)) {
                $status_name[] = $status['orders_status_name'];
            }
            return implode(', ', $status_name);
        }
    }

}
