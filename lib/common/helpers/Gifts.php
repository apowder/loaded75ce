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

use common\classes\Images;

class Gifts {

    public static function allow_gift_wrap($products_id) {
        if (!defined('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS') || MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS != 'true')
            return false;

        $check_gift_wrap_r = tep_db_query(
                "SELECT COUNT(*) AS c " .
                "FROM " . TABLE_GIFT_WRAP_PRODUCTS . " " .
                "WHERE products_id='" . (int) $products_id . "' "
        );
        if (tep_db_num_rows($check_gift_wrap_r) > 0) {
            $check_gift_wrap = tep_db_fetch_array($check_gift_wrap_r);
            return $check_gift_wrap['c'] > 0;
        }
        return false;
    }

    public static function get_gift_wrap_price($products_id) {
        if (!defined('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS') || MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS != 'true')
            return false;

        $gift_wrap_price = false;
        $check_gift_wrap_price_r = tep_db_query(
                "SELECT gift_wrap_price " .
                "FROM " . TABLE_GIFT_WRAP_PRODUCTS . " " .
                "WHERE products_id='" . (int) $products_id . "' " .
                "LIMIT 1"
        );
        if (tep_db_num_rows($check_gift_wrap_price_r) > 0) {
            $_gift_wrap_price = tep_db_fetch_array($check_gift_wrap_price_r);
            $gift_wrap_price = $_gift_wrap_price['gift_wrap_price'];
        }
        return $gift_wrap_price;
    }

    public static function virtual_gift_card_process($virtual_gift_card_id, $from_email = STORE_OWNER_EMAIL_ADDRESS) {
        global $customer_id, $languages_id, $currencies, $currency;
        $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.affiliate_id = '" . (int) $HTTP_SESSION_VARS['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and vgcb.virtual_gift_card_basket_id = '" . (int) $virtual_gift_card_id . "' and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and vgcb.customers_id = '" . (int) $customer_id . "'"));
        if (!$virtual_gift_card)
            return;

        // Generate virtual gift card coupon
        do {
            $virtual_gift_card_code = strtoupper(\common\helpers\Password::create_random_value(10));
            $check = tep_db_fetch_array(tep_db_query("select count(*) as coupon_exists from " . TABLE_COUPONS . " where coupon_code = '" . tep_db_input($virtual_gift_card_code) . "'"));
        } while ($check['coupon_exists']);

        $sql_data_array = array('coupon_code' => $virtual_gift_card_code,
            'coupon_amount' => $virtual_gift_card['products_price'],
            'coupon_currency' => $virtual_gift_card['currency_code'],
            'coupon_type' => 'F',
            'uses_per_coupon' => 1,
            'uses_per_user' => 1,
            'coupon_minimum_order' => 0,
            'restrict_to_products' => '',
            'restrict_to_categories' => '',
            'coupon_start_date' => 'now()',
            'coupon_expire_date' => date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 3)),
            'date_created' => 'now()',
            'date_modified' => 'now()');
        $query = tep_db_perform(TABLE_COUPONS, $sql_data_array);
        $insert_id = tep_db_insert_id();

        $sql_data_array = array('coupon_name' => $currencies->display_gift_card_price($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']) . ' - ' . $virtual_gift_card['products_name']);
        $sql_data_array['coupon_id'] = $insert_id;
        $sql_data_array['language_id'] = $languages_id;
        tep_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_array);

        // Instantiate a new mail object
        $message = new \common\classes\email(array('X-Mailer: osCommerce Mailer'));

        $contents = implode('', file(tep_href_link('email-template/virtual-gift-card-template', '', 'NONSSL', false)));
        $contents = str_replace(array("\r\n", "\n", "\r"), '', $contents);

        $search = array(
            "'##PRICE##'i",
            "'##PERSONAL_MESSAGE##'i",
            "'##SENDERS_NAME##'i",
            "'##CARD_CODE##'i");
        $replace = array(
            $currencies->display_gift_card_price($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']),
            nl2br($virtual_gift_card['virtual_gift_card_message']),
            $virtual_gift_card['virtual_gift_card_senders_name'],
            $virtual_gift_card_code);
        foreach ($replace as $key => $val) {
            $replace[$key] = str_replace('$', '/$/', $val);
        }
        $email_text = str_replace('/$/', '$', preg_replace($search, $replace, $contents));

        $message->add_html($email_text);

        // Send message
        $message->build_message();
        $message->send($virtual_gift_card['virtual_gift_card_recipients_name'], $virtual_gift_card['virtual_gift_card_recipients_email'], $virtual_gift_card['virtual_gift_card_senders_name'], $from_email, $virtual_gift_card['products_name']);

        tep_db_query("update " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " set virtual_gift_card_code = '" . tep_db_input($virtual_gift_card_code) . "' where length(virtual_gift_card_code) = 0 and virtual_gift_card_basket_id = '" . (int) $virtual_gift_card_id . "'");

        return $virtual_gift_card_code;
    }

    public static function getGiveAwaysSQL($products_id = 0, $only_active=false, $sorted = true, $only_buy_get = false){
        global $cart, $languages_id, $platform_id, $customer_groups_id, $currency_id;

        if ($only_buy_get) {
          $total = 0;
        } else {
          $total = $cart->show_total();
        }

        $products2c_join = '';
        if ( $platform_id ) {
            $products2c_join .=
              " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . (int)$platform_id . "' ".
              " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
              " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . (int)$platform_id . "' ";
        } else if ( \common\classes\platform::activeId() ) {
            $products2c_join .=
              " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
              " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
              " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }
        $giveaway_query =
            "select distinct p.products_id, p.products_image, p.products_status, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, if(gap.shopping_cart_price <= '" . number_format($total,4,'.','') . "', 1, 0) as active, gap.shopping_cart_price as price, gap.products_qty as qty, gap.buy_qty as buy_qty, use_in_qty_discount, gap_id as gaw_id ".
            "from " . TABLE_GIVE_AWAY_PRODUCTS . " gap, " . TABLE_PRODUCTS . " p {$products2c_join} ".
            " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '0', " .
            TABLE_PRODUCTS_DESCRIPTION . " pd ".

            "where gap.products_id = p.products_id and (gap.buy_qty > 0 or gap.shopping_cart_price > 0) " .
            " and ( (gap.begin_date<=now() or gap.begin_date='0000-00-00') and (gap.end_date>=now() or gap.end_date='0000-00-00')) ".
            ((USE_MARKET_PRICES == 'True')?" and (shopping_cart_price<=0 or gap.currencies_id='" . (int)$currency_id . "' )":"") .
            " and gap.groups_id='" . (int)$customer_groups_id . "'" .
            " and p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and pd.affiliate_id = '0' ".
            ($products_id? " and p.products_id = '" . (int)$products_id . "'" : "") .
            ($only_active?" and gap.shopping_cart_price <= '" . number_format($total,4,'.','') . "'":"") .
            ($sorted?"order by price, active desc, gap.buy_qty, products_name":"")
        ;

        return ['cart_total' => $total, 'giveaway_query' =>$giveaway_query ];
    }

    public static function getGiveAwaysQuery($products_id = 0){
    // backward compatibility
        $response = self::getGiveAwaysSQL($products_id);
        return ['cart_total' => $response['cart_total'], 'giveaway_query' => tep_db_query($response['giveaway_query']) ];
    }

    public static function getGiveAways($products_id = 0){
    //returns array of GAW products for design module at shopping cart page.
        global $cart, $languages_id, $currencies;

        $response = self::getGiveAwaysQuery($products_id);
        $giveaway_query = $response['giveaway_query'];
        $total = $response['cart_total'];
        if (tep_db_num_rows($giveaway_query) > 0) {
            $row = 0;
            $cartProducts = $cart->get_products();
            while ($d = tep_db_fetch_array($giveaway_query))
            {
              $price_b = '';
              if ($d['buy_qty'] > 0) {
                  $inCartQty = $cart->getQty($d['products_id']);
                  $price_b = sprintf(TEXT_QTY_BEFORE, $d['buy_qty'], $d['qty']);
                  if ($d['buy_qty'] > $inCartQty) {
                      $collect = $d['buy_qty'] - $inCartQty;
                      $giveaway_note = sprintf(TEXT_SPEND_MORE_ITEMS, $collect);
                      $d['active'] = 0;
                  } else {
                   /* if (self::get_max_quantity($d['products_id'])['qty'] > $d['qty']) {
                    //don't show active GAW if more same free product available
                      continue;
                    }*/
                    $giveaway_note = TEXT_ADD_GIVEAWAY;
                  }
              } elseif ($d['active'] == 1) {
                $giveaway_note = TEXT_ADD_GIVEAWAY;
                $price_b = sprintf(TEXT_PRICE_BEFORE, $d['qty'], $currencies->format($d['price']));
              } else {
                $collect = $d['price'] - $total;
                if ($collect < 0) {
                  $collect = 0;
                }
                $giveaway_note = sprintf(TEXT_SPEND_MORE, $currencies->format($collect));
                $price_b = sprintf(TEXT_PRICE_BEFORE, $d['qty'], $currencies->format($d['price']));
              }

              if ( \common\helpers\Attributes::has_product_attributes($d['products_id']) ){
                $tmp = array();
                $tmp = \common\helpers\Attributes::getDetails( $d['products_id'], $tmp);
                $attributes_data = $tmp['attributes_array'];
              } else {
                $attributes_data = array();
              }

              $products[] = array(
                'ga_idx' => $d['gaw_id'], //$row,
                'products_link' => tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $d['products_id']),
                'products_id' => $d['products_id'],
                'image' => Images::getImageUrl($d['products_id'], 'Small'),
                'products_status' => $d['products_status'],
                'products_name' => $d['products_name'],
                'price_b' => $price_b,
                'giveaway_note' => $giveaway_note,
                'ga_form_action' => tep_href_link(FILENAME_SHOPPING_CART, 'product_id=' . $d['products_id'] . '&action=' . ($cart->in_giveaway($d['products_id'], $d['qty']) ? 'remove_giveaway' : 'add_giveaway')),
                'single_checkbox' => $cart->in_giveaway($d['products_id'], $d['qty'], $d['gaw_id']),
                'attributes' =>  $attributes_data,
                'active' => $d['active'],
              );

              $row++;
            }
          }

          return $products;
    }


    public static function get_max_quantity($prid, $gaw_id=false){
        global $cart;
        if ((int)$prid==0) return false;

        if ($gaw_id) {
          $response = \common\helpers\Gifts::getGiveAwaysSQL($prid, true, false); // product, only active , no default sort order
          $giveaway_query = tep_db_query($response['giveaway_query'] . " and gap_id='" . (int)$gaw_id . "'"); // 2do - check better approach
          $total = $response['cart_total'];
        } else {
          $response = self::getGiveAwaysQuery($prid);
          $giveaway_query = $response['giveaway_query'];
          $total = $response['cart_total'];
        }

        if (tep_db_num_rows($giveaway_query) > 0) {

          $inCartQty = $cart->getQty($prid);
          while ($d = tep_db_fetch_array($giveaway_query)) {
            if ($d['buy_qty'] > 0) {
              if ($d['buy_qty'] <= $inCartQty) {
                $ret = array('qty' => $d['qty'], 'gaw_id' => $d['gaw_id']);
              } else {
                break;
              }
            } else {
              $ret = array('qty' => $d['qty'], 'gaw_id' => $d['gaw_id']);
              break;
            }
          }
        }
        return $ret;
    }
    
    /// not required while 1 giveaway only
    //also returns matched GAW_id
    public static function allowedGAW($prid, $get_qty){
        global $cart;

        $response = self::getGiveAwaysQuery($prid);
        $giveaway_query = $response['giveaway_query'];
        $total = $response['cart_total'];

        if (tep_db_num_rows($giveaway_query) > 0) {

          $inCartQty = $cart->getQty($prid);//for buy & get option
          while ($d = tep_db_fetch_array($giveaway_query)) {

            if ($d['buy_qty'] > 0) {
              if ($d['buy_qty'] <= $inCartQty && $d['qty'] == $get_qty) {
                return $d['gaw_id'] ;
              }
            } else {
              if ($d['price'] <= $total && $d['qty'] == $get_qty) {
                return $d['gaw_id'];
              }
            }
          }
        }
        return false;
    }

    public static function in_qty_discount($gaw_id) {
      static $cache;
      if (!isset($cache[$gaw_id])) {

        $response = self::getGiveAwaysSQL(0, true, false, true);
        $giveaway_query = tep_db_query($response['giveaway_query']);
        while ($d = tep_db_fetch_array($giveaway_query)) {
          $cache[$d['gaw_id']] = $d['use_in_qty_discount'];
        }
      }
      return $cache[$gaw_id];
    }


}


