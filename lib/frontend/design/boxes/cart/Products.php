<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Products extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $cart, $currencies, $languages_id;

        $products = $cart->get_products();

        $allow_checkout = true;
        $oos_product_incart = false;
        $bound_quantity_ordered = false;
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// {{ Products Bundle Sets
            if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'inProducts')) {
                list($bundles, $bundles_info) = $ext::inProducts($products[$i]);
                if (count($bundles) > 0) {
                    $products[$i]['bundles'] = $bundles;
                }
                if (count($bundles_info) > 0) {
                    $products[$i]['bundles_info'] = $bundles_info;
                }
            }
// }}

            $products[$i]['hidden_fields'] = '';
            $products[$i]['hidden_fields'] .= tep_draw_hidden_field('products_id[]', $products[$i]['id']);
            $products[$i]['hidden_fields'] .= tep_draw_hidden_field('ga[]', $products[$i]['ga']);

            $products[$i]['final_price'] = $currencies->display_price($products[$i]['final_price'] * $products[$i]['quantity'], \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id']));
            $products[$i]['link'] = tep_href_link('catalog/product', 'products_id='. $products[$i]['id']);
            $products[$i]['image'] = Images::getImageUrl($products[$i]['id'], 'Small');
            if ( $products[$i]['ga'] ) {
                $products[$i]['remove_link'] = tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_giveaway&product_id=' . $products[$i]['id']);
            }else {
                $products[$i]['remove_link'] = tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&products_id=' . $products[$i]['id']);
            }

            $products[$i]['gift_wrap_price_formated'] = ($products[$i]['gift_wrap_price']<0?'-':'+') . $currencies->display_price(abs($products[$i]['gift_wrap_price']), \common\helpers\Tax::get_tax_rate(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS')?MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS:0));

//            $products[$i]['in_stock'] = false;
            $products[$i]['all_in_stock'] = 1;
            if (STOCK_CHECK == 'true'){
//                $products[$i]['in_stock'] = 0;
//                $products[$i]['in_stock'] = \common\helpers\Product::get_products_stock($products[$i]['id']);
//                if ($products[$i]['in_stock'] <= 0) $allow_checkout = false;
//                if (\common\helpers\Product::check_stock($products[$i]['id'], $products[$i]['quantity'])){
//                  $oos_product_incart = true;
//                }
                if ( isset($products[$i]['stock_info']) ) {
                  if ( $bound_quantity_ordered==false ) {
                    $bound_quantity_ordered = $products[$i]['stock_info']['order_instock_bound'];
                  }
                  if ( !$products[$i]['stock_info']['allow_out_of_stock_checkout'] ) {
                    $oos_product_incart = true;
                  }
                }
            }
            $products[$i]['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($products[$i]['id']);

            if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])){
                if ($products[$i]['virtual_gift_card'] && $products[$i]['attributes'][0] > 0) {
//      echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][0]', $products[$i]['attributes'][0]);
                    $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "' where length(vgcb.virtual_gift_card_code) = 0 and vgcb.virtual_gift_card_basket_id = '" . (int)$products[$i]['attributes'][0] . "' and p.products_id = vgcb.products_id and pd.affiliate_id = 0 and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and " . ($_SESSION['customer_id'] > 0 ? " vgcb.customers_id = '" . (int)$_SESSION['customer_id'] . "'" : " vgcb.session_id = '" . tep_session_id() . "'")));
                    $products[$i]['attr'][0]['products_id'] = $virtual_gift_card['products_id'];
                    $products[$i]['attr'][0]['products_options_name'] = TEXT_GIFT_CARD_DETAILS;
                    $products[$i]['attr'][0]['options_values_id'] = $products[$i]['attributes'][0];
                    $products[$i]['attr'][0]['products_options_values_name'] = "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_name'])) $products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_RECIPIENTS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_recipients_name'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_email'])) $products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_RECIPIENTS_EMAIL . ' ' . $virtual_gift_card['virtual_gift_card_recipients_email'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_message'])) $products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_MESSAGE . ' ' . $virtual_gift_card['virtual_gift_card_message'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_senders_name'])) $products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_SENDERS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_senders_name'] . "\n";
                } else
// }}

                    while (list($option, $value) = each($products[$i]['attributes'])) {
                        $products[$i]['hidden_fields'].=tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
                        
                        $option_arr = explode('-', $option);
                        $attributes = tep_db_query("select pa.products_id, pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . (int)($option_arr[1] > 0 ? $option_arr[1] : $products[$i]['id']) . "'
                                       and pa.options_id = '" . (int)$option_arr[0] . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . (int)$value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");
                        $attributes_values = tep_db_fetch_array($attributes);

// {{ Products Bundle Sets
                        $products[$i]['attr'][$option]['products_id'] = $attributes_values['products_id'];
// }}
                        $products[$i]['attr'][$option]['products_options_name'] = $attributes_values['products_options_name'];
                        $products[$i]['attr'][$option]['options_values_id'] = $value;
                        $products[$i]['attr'][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
                        $products[$i]['attr'][$option]['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes_values['products_attributes_id']);
                        $products[$i]['attr'][$option]['price_prefix'] = $attributes_values['price_prefix'];
                    }
            }
        }
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// {{ Products Bundle Sets
            $products[$i]['is_bundle'] = false;
            if (!isset($products[$i]['bundles_info']) || !is_array($products[$i]['bundles_info'])) continue;

            foreach( $products[$i]['bundles_info'] as $bpid=>$bundle_info ) {
                $products[$i]['bundles_info'][$bpid]['attr'] = array();

                if ( isset($products[$i]['attr']) && is_array($products[$i]['attr']) && count($products[$i]['attr'])>0) {
                    foreach ($products[$i]['attr'] as $__option_id=>$__option_value_data) {
                        if ( strpos($__option_id.'-', '-'.$bpid.'-')===false ) continue;
                        $products[$i]['bundles_info'][$bpid]['attr'][$__option_id] = $__option_value_data;
                        unset($products[$i]['attr'][$__option_id]);
                    }
                }
                $products[$i]['bundles_info'][$bpid]['with_attr'] = count($products[$i]['bundles_info'][$bpid]['attr'])>0; 
            }
            $products[$i]['is_bundle'] = true;
// }}
        }

        if ($cart->count_contents() > 0) {
            return IncludeTpl::widget(['file' => 'boxes/cart/products' . ($this->type ? '-' . $this->type : '') . '.tpl', 'params' => [
              'products' => $products,
              'allow_checkout' => !($oos_product_incart || $bound_quantity_ordered),
              'oos_product_incart' => $oos_product_incart,
              'bound_quantity_ordered' => $bound_quantity_ordered,
            ]]);
        } else {
            return '<div class="empty">' . CART_EMPTY . '</div>';
        }
    }
}