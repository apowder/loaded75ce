<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\web\Session;

class AdminCarts extends Admin {

    protected $info;
    private $carts = [];
    private $currentCart = null;
    private $lastCart;

    public function __construct() {
        parent::__construct();
    }

    public function loadCustomersBaskets() {
        $this->carts = [];
        $carts_query = tep_db_query("select * from " . TABLE_ADMIN_SHOPPING_CARTS . " where admin_id = '" . (int) $this->info['admin_id'] . "'");
        if (tep_db_num_rows($carts_query)) {
            while ($row = tep_db_fetch_array($carts_query)) {
                $this->carts[$row['customers_id'] . '-' . $row['basket_id']] = [
                    'customers_id' => $row['customers_id'],
                    'basket_id' => $row['basket_id'],
                    'order_id' => $row['order_id'],
                    'cart_details' => unserialize(base64_decode($row['customer_basket'])),
                    'status' => $row['status'],
                ];
            }
        }
        return $this;
    }

    public function checkCartOwnerClear($cart) {
        $check_query = tep_db_query("select status from " . TABLE_ADMIN_SHOPPING_CARTS . " where admin_id <> '" . (int) $this->info['admin_id'] . "' and customers_id ='" . (int) $cart->customer_id . "' and basket_id = '" . (int) $cart->basketID . "'");
        if (tep_db_num_rows($check_query)) {
            $check = tep_db_fetch_array($check_query);
            if ($check['status']) { // cart busy
                return false;
            }
        }
        return true;
    }

    public function updateCustomersBasket($cart) {
        if (!isset($this->carts[$cart->customer_id . '-' . $cart->basketID])) {
            if (!$this->checkCartOwnerClear($cart))
                return false;
            $this->saveCustomerBasket($cart);

            $this->setCurrentCartID($cart->customer_id . '-' . $cart->basketID);
        } else {
            $this->setCurrentCartID($cart->customer_id . '-' . $cart->basketID);
            $this->loadCurrentCart();
        }
    }

    public function saveCustomerBasket($cart) {
        $this->carts[$cart->customer_id . '-' . $cart->basketID] = [
            'customers_id' => $cart->customer_id,
            'basket_id' => $cart->basketID,
            'order_id' => $cart->order_id,
            'cart_details' => [
                'cart' => $cart,
                'payment' => @$_SESSION['payment'],
                'shipping' => @$_SESSION['shipping'],
                'select_shipping' => @$_SESSION['select_shipping'],
                'adress_details' => @$_SESSION['adress_details'],
                'sendto' => @$_SESSION['sendto'],
                'billto' => @$_SESSION['billto'],
                'cart_address_id' => @$_SESSION['cart_address_id'],
                'cot_gv' => @$_SESSION['cot_gv'],
                'cc_id' => @$_SESSION['cc_id'],
            ],
            'status' => 1,
        ];

        $check_query = tep_db_query("select * from " . TABLE_ADMIN_SHOPPING_CARTS . " where admin_id = '" . (int) $this->info['admin_id'] . "' and customers_id ='" . (int) $cart->customer_id . "' and basket_id = '" . (int) $cart->basketID . "'");
        $sql_data = [
            'admin_id' => (int) $this->info['admin_id'],
            'basket_id' => (int) $cart->basketID,
            'customers_id' => (int) $cart->customer_id,
            'order_id' => (int) $cart->order_id,
            'status' => 1,
            'customer_basket' => base64_encode(serialize($this->carts[$cart->customer_id . '-' . $cart->basketID]['cart_details'])),
        ];
        if (tep_db_num_rows($check_query)) {
            tep_db_perform(TABLE_ADMIN_SHOPPING_CARTS, $sql_data, 'update', "admin_id = '" . (int) $this->info['admin_id'] . "' and customers_id ='" . (int) $cart->customer_id . "' and basket_id = '" . (int) $cart->basketID . "'");
        } else {
            tep_db_perform(TABLE_ADMIN_SHOPPING_CARTS, $sql_data);
        }
    }

    public function setCurrentCartID($cartID, $is_virtual = false) {
        $this->currentCart = $cartID;
        if ($is_virtual) {
            $this->setLastVirtualID($cartID);
        }
    }

    public function setLastVirtualID($cartID) {
        $session = new Session;
        $session->set('lastVirtual', $cartID);
    }

    public function getLastVirtualID($set_main = false) {
        $session = new Session;
        if ($set_main) {
            $this->currentCart = $session->get('lastVirtual');
        }
        return $session->get('lastVirtual');
    }

    public function getCurrentCartID() {
        return $this->currentCart;
    }

    public function getVirtualCartIDs() {
        $ids = [];
        if (is_array($this->carts)) {
            foreach ($this->carts as $_id => $_cart) {
                if (!$_cart['order_id'] || $_cart['order_id'] <= 0) {
                    $ids[] = $_id;
                }
            }
            return (count($ids) ? $ids : false);
        }
        return false;
    }

    public function loadCurrentCart() {
        global $cart, $payment, $shipping, $select_shipping, $adress_details, $sendto, $billto, $cot_gv, $cc_id;

        if (!is_null($this->currentCart)) {
            if (is_array($this->carts[$this->currentCart]['cart_details'])) {
                foreach ($this->carts[$this->currentCart]['cart_details'] as $item => $value) {
                    if (!tep_session_is_registered($item))
                        tep_session_register($item);
                    unset($GLOBALS[$item]);
                    $_SESSION[$item] = $value;
                    $GLOBALS[$item] = &$_SESSION[$item];
                }
            }
        }
    }

    public function getAdminByCart($cart) {
        $name = '';
        $admin = tep_db_fetch_array(tep_db_query("select admin_id from " . TABLE_ADMIN_SHOPPING_CARTS . " where customers_id ='" . (int) $cart->customer_id . "' and basket_id = '" . (int) $cart->basketID . "'"));
        if ($admin) {
            $_admin = new Admin($admin['admin_id']);
            $name = $_admin->getInfo('admin_firstname') . ' ' . $_admin->getInfo('admin_lastname');
        }
        return $name;
    }

    public function relocateCart($basket_id, $customer_id) {
        if ($basket_id && $customer_id) {
            tep_db_query("update " . TABLE_ADMIN_SHOPPING_CARTS . " set admin_id = '" . (int) $this->info['admin_id'] . "' where customers_id ='" . (int) $customer_id . "' and basket_id = '" . (int) $basket_id . "'");
        }
    }

    public function deleteCartByOrder($orders_id) {
        tep_db_query("delete from " . TABLE_ADMIN_SHOPPING_CARTS . " where order_id = '" . (int) $orders_id . "'");
    }

    public function deleteCartByBC($customer_id, $basket_id) {
        tep_db_query("delete from " . TABLE_ADMIN_SHOPPING_CARTS . " where basket_id = '" . (int) $basket_id . "' and customers_id = '" . (int) $customer_id . "'");
        $this->loadCustomersBaskets();
        return true;
    }

}
