<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  class order {
    var $info, $totals, $products, $customer, $delivery, $content_type;

    function __construct($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
      global $language;
    // changed by Art. Add cc_cvn selection, orders_type
      $order_query = tep_db_query("select o.*, a.individual_id as orders_admin, a.individual_id as customers_admin from " . TABLE_ORDERS . " o left join " . TABLE_ADMIN . " a on a.admin_id=o.admin_id where orders_id = '" . (int)$order_id . "'");
      $order = tep_db_fetch_array($order_query);

      $shipping_method_query = tep_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' and class = 'ot_shipping'");
      $shipping_method = tep_db_fetch_array($shipping_method_query);
      
      \common\helpers\Translation::init('ordertotal');
      
      $totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
      while ($totals = tep_db_fetch_array($totals_query)) {
        if (!is_object($totals['class'])){
          $value = $totals['class'].'.php';
          if (!class_exists ($totals['class']) && is_file(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' .$value)) {
            /*
            if (file_exists(DIR_FS_CATALOG . DIR_WS_LANGUAGES . '/modules/order_total/' . $value)){
              require_once(DIR_FS_CATALOG . DIR_WS_LANGUAGES . '/modules/order_total/' . $value);
            }*/ 
              require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $value);
          }
          if (class_exists ($totals['class'])){
            $class = new $totals['class'];
          } else {
            $class = new stdClass;
            $class->title = $totals['title'];
          }            
        }        
        $this->totals[] = array('title' => $class->title,
                                'text' => $totals['text'],
                                'value' => $totals['value'],
                                'class' => $totals['class'],
                                'sort_order' => $totals['sort_order']);
      }

      $this->info = array('currency' => $order['currency'],
                          'currency_value' => $order['currency_value'],
                          'platform_id' => $order['platform_id'],
                          'payment_method' => $order['payment_method'],
                          'cc_type' => $order['cc_type'],
                          'cc_owner' => $order['cc_owner'],
                          'cc_number' => $order['cc_number'],
                          'cc_expires' => $order['cc_expires'],
                          'gv' => $order['gv'],
                          // added by Art. Start
                          'cc_cvn' => $order['cc_cvn'],
                          'orders_type' => $order['orders_type'],
                          // added by Art. Stop
                          'tax_groups' => array(),
                          'order_admin' => (tep_not_null($order['orders_admin'])?$order['orders_admin']:TEXT_INTERNET),
                          'date_purchased' => $order['date_purchased'],
                          'orders_status' => $order['orders_status'],
                          'payment_class' => $order['payment_class'],
                          'shipping_class' => $order['shipping_class'],
                          'shipping_method' => ((substr($shipping_method['title'], -1) == ':') ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title'])),
                          'language_id' => $order['language_id'],
                          'shipping_cost' => 0,
                          'subtotal' => 0,
                          'tax' => 0,
                          'tax_groups' => array(),
                          'tracking_number' => $order['tracking_number'],
                          'transaction_id' => $order['transaction_id'],
                          'approval_code' => $order['approval_code'],
                          'last_modified' => $order['last_modified'],
                          'edit_orders_recalculate_totals' => $order['edit_orders_recalculate_totals'],
                          'shipping_weight' => $order['shipping_weight'],
                          );

      $country = \common\helpers\Country::get_country_info_by_name($order['customers_country'], $order['language_id']);
      $this->customer = array('id' => $order['customers_id'],
                              'customer_id' => $order['customers_id'],
                              'name' => $order['customers_name'],
                              'firstname' => $order['customers_firstname'],
                              'lastname' => $order['customers_lastname'],
                              'admin' => (tep_not_null($order['customers_admin'])?$order['customers_admin']:TEXT_INTERNET),
                              'company' => $order['customers_company'],
                              'company_vat' => $order['customers_company_vat'],
                              'company_vat_status' => $order['customers_company_vat_status'],
                              'street_address' => $order['customers_street_address'],
                              'suburb' => $order['customers_suburb'],
                              'city' => $order['customers_city'],
                              'postcode' => $order['customers_postcode'],
                              'state' => $order['customers_state'],
                              'country' => $country,
                              'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $order['customers_state']),
                              'country_id' => $country['id'],
                              'format_id' => $order['customers_address_format_id'],
                              'telephone' => $order['customers_telephone'],
                              'landline' => $order['customers_landline'],
                              'email_address' => $order['customers_email_address']);

      $country = \common\helpers\Country::get_country_info_by_name($order['delivery_country'], $order['language_id']);
      $this->delivery = array('name' => $order['delivery_name'],
                              'gender' => $order['delivery_gender'],
                              'firstname' => $order['delivery_firstname'],
                              'lastname' => $order['delivery_lastname'],      
                              'company' => $order['customers_company'],
                              'street_address' => $order['delivery_street_address'],
                              'suburb' => $order['delivery_suburb'],
                              'city' => $order['delivery_city'],
                              'postcode' => $order['delivery_postcode'],
                              'state' => $order['delivery_state'],
                              'country' => $country,
                              'address_book_id' => $order['delivery_address_book_id'],
                              'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $order['delivery_state']),
                              'country_id' => $country['id'],
                              'format_id' => $order['delivery_address_format_id']);

      $country = \common\helpers\Country::get_country_info_by_name($order['billing_country'], $order['language_id']);
      $this->billing = array('name' => $order['billing_name'],
                             'gender' => $order['billing_gender'],
                             'firstname' => $order['billing_firstname'],
                             'lastname' => $order['billing_lastname'],      
                             'company' => $order['customers_company'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $country,
                             'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $order['billing_state']),
                             'country_id' => $country['id'],
                             'address_book_id' => $order['billing_address_book_id'],
                             'format_id' => $order['billing_address_format_id']);

      $index = 0;
      $subtotal = 0;
      $tax = 0;
      $tax_groups = array();

      $orders_products_query = tep_db_query("select orders_products_id, ".
        "is_virtual, ".
        "gv_state, ".
        "gift_wrap_price, gift_wrapped, ".
        "products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],																	
                                        'name' => $orders_products['products_name'],
                                        'model' => $orders_products['products_model'],
                                        'tax' => $orders_products['products_tax'],
                                        'price' => $orders_products['products_price'],
                                        'final_price' => $orders_products['final_price'],
                                        'is_virtual' => (int)$orders_products['is_virtual'],
                                        'gv_state' => $orders_products['gv_state'],
                                        'gift_wrap_price' => $orders_products['gift_wrap_price'],
                                        'gift_wrapped' => !!$orders_products['gift_wrapped'],
                                        'id' =>  $orders_products['products_id'],
                                        'orders_products_id' => $orders_products['orders_products_id']
                                        );
			  
				if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'queryOrderAdmin')) {
                                    $this->products[$index] = array_merge($ext::queryOrderAdmin($order_id, $index), $this->products[$index]);
				}	
        $subtotal += $orders_products['final_price'] * $orders_products['products_quantity'];
        $tax += $orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100;
        $selected_tax="";
        $query_tax_class_id = "select tax_class_id, sum(tax_rate) as rate from " . TABLE_TAX_RATES . " group by tax_class_id, tax_zone_id order by tax_priority";
        $result_tax_class_id = tep_db_query($query_tax_class_id);
        if(tep_db_num_rows($result_tax_class_id)>0)
        {
          while($array_tax_class_id = tep_db_fetch_array($result_tax_class_id))
          {
            if($array_tax_class_id['rate']==$orders_products['products_tax'])
            {
              $tax_class_id = $array_tax_class_id['tax_class_id'];
              $selected_tax = \common\helpers\Tax::get_tax_description($tax_class_id, $this->delivery['country_id'], $this->delivery['zone_id']);
              break;
            }
          }
        }
        if (!isset($tax_groups[$selected_tax])) {
          $tax_groups[$selected_tax] = $orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100;
        } else {
          $tax_groups[$selected_tax] += $orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100;
        }

        $subindex = 0;
        $attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$order_id . "' and orders_products_id = '" . (int)$orders_products['orders_products_id'] . "'");
        if (tep_db_num_rows($attributes_query)) {
          while ($attributes = tep_db_fetch_array($attributes_query)) {
            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options'],
                                                                     'value' => $attributes['products_options_values'],
                                                                     'prefix' => $attributes['price_prefix'],
                                                                     'price' => $attributes['options_values_price']);

            $subindex++;
          }
        }
        $index++;
      }
      // {{ content type
      $this->content_type = 'physical';
      $count_virtual = 0;
      $count_physical = 0;
      foreach( $this->products as $__product ) {
        if ( $__product['is_virtual']!=0 ) {
          $count_virtual++;
        }else{
          $count_physical++;
        }
      }
      if ( $count_physical>0 && $count_virtual==0 ) {
        $this->content_type = 'physical';
      }elseif($count_physical>0 && $count_virtual>0) {
        $this->content_type = 'mixed';
      }elseif($count_physical==0 && $count_virtual>0) {
        $this->content_type = 'virtual';
      }else{
        $this->content_type = 'physical';
      }
      // }} content type

      /*
      $res = tep_db_query("select count(*) as total from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD  . " where orders_id='" . (int)$order_id . "'");
      $d = tep_db_fetch_array($res);
      if ($d['total']==0){
        $this->content_type = 'physical';
      } else {
        if ($d['total']==count($this->contents)){
          $this->content_type = 'virtual';
        } else {
          $this->content_type = 'mixed';
        }
      }
      */
      
      /*      'shipping_cost' => 0, */
      
      if (DISPLAY_PRICE_WITH_TAX == 'true')
      {
        $this->info['subtotal'] = round($subtotal+$tax,2);
      }
      else
      {
        $this->info['subtotal'] = round($subtotal,2);
      }
      $this->info['total'] = round($subtotal+$tax,2);
      $this->info['tax'] = round($tax,2);
      $this->info['tax_groups'] = $tax_groups;
    }
  }
