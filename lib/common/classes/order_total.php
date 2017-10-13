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

class order_total {

    var $modules;
    var $readonly = ['ot_tax', 'ot_total', 'ot_subtotal', 'ot_due', 'ot_paid', 'ot_subtax'];

// class constructor
    function __construct($reconfig = false) {
        global $language;

        if (defined('MODULE_ORDER_TOTAL_INSTALLED') && tep_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
            $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

            reset($this->modules);

            \common\helpers\Translation::init('ordertotal');

            while (list(, $value) = each($this->modules)) {
                if (\frontend\design\Info::isTotallyAdmin()) {
                    if (!is_file(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $value))
                        continue;
                } else {
                    if (!is_file(DIR_WS_MODULES . 'order_total/' . $value))
                        continue;
                }

                if (is_file(DIR_WS_LANGUAGES . $language . '/modules/order_total/' . $value)) {
                    include_once(DIR_WS_LANGUAGES . $language . '/modules/order_total/' . $value);
                }
                if (\frontend\design\Info::isTotallyAdmin()) {
                    define('ONE_PAGE_CHECKOUT', 'True');
                    define('ONE_PAGE_SHOW_TOTALS', 'true');
                    include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $value);
                } else {
                    include_once(DIR_WS_MODULES . 'order_total/' . $value);
                }

                $class = substr($value, 0, strrpos($value, '.'));
                $GLOBALS[$class] = new $class;
                if (method_exists($GLOBALS[$class], 'config')) {
                    $config_array = array_merge(array(
                        'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
                        'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
                            ), is_array($reconfig) ? $reconfig : array());
                    $GLOBALS[$class]->config($config_array);
                }
            }
        }
    }

    function processInAdmin($update_totals = array()) {
        global $cart, $currencies, $order, $currency;
        $order_total_array = array();
        if (is_array($this->modules)) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($cart->existHiddenModule($class))
                    continue;
                if ($GLOBALS[$class]->enabled) {
                    $replacing_value = -1;
                    $visible = false;
                    if ((array_key_exists($class, $update_totals) && tep_not_null($update_totals[$class])) && (!in_array($class, $this->readonly) || $class == 'ot_tax' || $class == 'ot_paid')) {
                        if ($update_totals[$class]['value'] != '&nbsp;') {
                            if (is_array($update_totals[$class])) {
                                $replacing_value = [
                                    'in' => (float)$update_totals[$class]['value']['in'] * $currencies->get_market_price_rate($currency, DEFAULT_CURRENCY),
                                    'ex' => (float)$update_totals[$class]['value']['ex'] * $currencies->get_market_price_rate($currency, DEFAULT_CURRENCY),
                                ];
                            }
                        } else {
                            $replacing_value = 0;
                            $visible = true;
                        }
                    }

                    $GLOBALS[$class]->process($replacing_value, $visible);
                    $i = 0;

                    if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text']) || (array_key_exists($class, $update_totals) && tep_not_null($update_totals[$class]))) {
                        $_text = $GLOBALS[$class]->output[$i]['text'];
                        $_value = $GLOBALS[$class]->output[$i]['value'];
                        $_value_exc_vat = $GLOBALS[$class]->output[$i]['value_exc_vat'];
                        $_value_inc_tax = $GLOBALS[$class]->output[$i]['value_inc_tax'];
                        $_text_exc_tax = $GLOBALS[$class]->output[$i]['text_exc_tax'];
                        $_text_inc_tax = $GLOBALS[$class]->output[$i]['text_inc_tax'];
                        $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                            'title' => $GLOBALS[$class]->output[$i]['title'],
                            'text' => $_text,
                            'value' => $_value,
                            'sort_order' => $GLOBALS[$class]->sort_order,
                            'text_exc_tax' => $_text_exc_tax,
                            'text_inc_tax' => $_text_inc_tax,
                            'adjusted' => $GLOBALS[$class]->output[$i]['adjusted'],
// {{
                            'tax_class_id' => $GLOBALS[$class]->output[$i]['tax_class_id'],
                            'value_exc_vat' => $_value_exc_vat,
                            'value_inc_tax' => $_value_inc_tax,
                            'difference' => @$GLOBALS[$class]->output[$i]['difference'],
// }}
                        );
                    }
                }
            }//echo '<pre>';print_r($order_total_array);die;
        }

        return $order_total_array;
    }

    function process() {
        global $cart, $currencies, $order;
        $order_total_array = array();
        if (is_array($this->modules)) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($GLOBALS[$class]->enabled) {
                    $GLOBALS[$class]->process();

                    for ($i = 0, $n = sizeof($GLOBALS[$class]->output); $i < $n; $i++) {
                        if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                            $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                'title' => $GLOBALS[$class]->output[$i]['title'],
                                'text' => $GLOBALS[$class]->output[$i]['text'],
                                'value' => $GLOBALS[$class]->output[$i]['value'],
                                'sort_order' => $GLOBALS[$class]->sort_order,
                                'text_exc_tax' => $GLOBALS[$class]->output[$i]['text_exc_tax'],
                                'text_inc_tax' => $GLOBALS[$class]->output[$i]['text_inc_tax'],
// {{
                                'tax_class_id' => $GLOBALS[$class]->output[$i]['tax_class_id'],
                                'value_exc_vat' => $GLOBALS[$class]->output[$i]['value_exc_vat'],
                                'value_inc_tax' => $GLOBALS[$class]->output[$i]['value_inc_tax'],
// }}
                            );
                        }
                    }
                }
            }
        }

        return $order_total_array;
    }

    function output() {
        $output_string = '';
        if (is_array($this->modules)) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($GLOBALS[$class]->enabled) {
                    $size = sizeof($GLOBALS[$class]->output);
                    for ($i = 0; $i < $size; $i++) {
                        $output_string .= '              <div class="row">' . "\n" .
                                '                <strong>' . $GLOBALS[$class]->output[$i]['title'] . '</strong>&nbsp;' . "\n" .
                                '                <span>' . $GLOBALS[$class]->output[$i]['text'] . '</span>' . "\n" .
                                '              </div>';
                    }
                }
            }
        }

        return $output_string;
    }

// ICW ORDER TOTAL CREDIT CLASS/GV SYSTEM - START ADDITION
//
// This function is called in checkout payment after display of payment methods. It actually calls
// two credit class functions.
//
// use_credit_amount() is normally a checkbox used to decide whether the credit amount should be applied to reduce
// the order total. Whether this is a Gift Voucher, or discount coupon or reward points etc.
//
// The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
// for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
// E.g. a setting in the admin section.
//
    function credit_selection() {
        $selection_string = '';
        $close_string = '';
        $credit_class_string = '';
        if (MODULE_ORDER_TOTAL_INSTALLED) {

            $header_string = '<div class="main contentBoxContents"><h2>' . TABLE_HEADING_CREDIT . '</h2>';

            $close_string = '</div>';
            reset($this->modules);
            $output_string = '';
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class) {
                    $use_credit_string = $GLOBALS[$class]->use_credit_amount();
                    if ($selection_string == '')
                        $selection_string = $GLOBALS[$class]->credit_selection();
                    if (($use_credit_string != '' ) || ($selection_string != '')) {
                        if ($GLOBALS[$class]->header) {
                            $output_string = '<div><strong>' . $GLOBALS[$class]->header . '</strong></div>' . "\n";
                        }

                        if ($use_credit_string) {
                            $output_string .= '<table class="tableForm">' . $use_credit_string . '</table>';
                        }

                        $output_string .= '<table class="tableForm">' . $selection_string . '</table>';
                    }
                }
            }
            if ($output_string != '') {
                $output_string = $header_string . $output_string;
                $output_string .= $close_string;
            }
        }
        return $output_string;
    }

// update_credit_account is called in checkout process on a per product basis. It's purpose
// is to decide whether each product in the cart should add something to a credit account.
// e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
// to the Gift Voucher account.
// Another use would be to check if the product would give reward points and add these to the points/reward account.
//
    function update_credit_account($i) {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if (($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class)) {
                    $GLOBALS[$class]->update_credit_account($i);
                }
            }
        }
    }

// This function is called in checkout confirmation.
// It's main use is for credit classes that use the credit_selection() method. This is usually for
// entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
// If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
// with an error
//
    function collect_posts($limit_class = '') {
        global $HTTP_POST_VARS, $HTTP_SESSION_VARS;
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if (($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class)) {
                    $post_var = 'c' . $GLOBALS[$class]->code;
                    if ($HTTP_POST_VARS[$post_var]) {
                        $GLOBALS[$post_var] = $HTTP_POST_VARS[$post_var];
                        if (!tep_session_is_registered($post_var))
                            tep_session_register($post_var);
                        //$post_var = $HTTP_POST_VARS[$post_var];
                    }
                    if (!empty($limit_class) && $limit_class != $class)
                        continue;
                    $GLOBALS[$class]->collect_posts();
                }
            }
        }
    }

// pre_confirmation_check is called on checkout confirmation. It's function is to decide whether the
// credits available are greater than the order total. If they are then a variable (credit_covers) is set to
// true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
// total, we don't want to go to paypal etc.
//
    function pre_confirmation_check() {
        global $payment, $order, $credit_covers;
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            $total_deductions = 0;
            reset($this->modules);
            $order_total = $order->info['total'];
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                $order_total = $this->get_order_total_main($class, $order_total);
                if (($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class)) {
                    $total_deductions = $total_deductions + $GLOBALS[$class]->pre_confirmation_check($order_total);
                    $order_total = $order_total - $GLOBALS[$class]->pre_confirmation_check($order_total);
                }
            }
            if ($order->info['total'] - $total_deductions <= 0) {
                if (!tep_session_is_registered('credit_covers'))
                    tep_session_register('credit_covers');
                $credit_covers = true;
            }
            else {   // belts and suspenders to get rid of credit_covers variable if it gets set once and they put something else in the cart
                if (tep_session_is_registered('credit_covers'))
                    tep_session_unregister('credit_covers');
            }
        }
    }

// this function is called in checkout process. it tests whether a decision was made at checkout payment to use
// the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
// is reduced the order total amount.
//
    function apply_credit() {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if (($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class)) {
                    $GLOBALS[$class]->apply_credit();
                }
            }
        }
    }

// Called in checkout process to clear session variables created by each credit class module.
//
    function clear_posts() {
        global $HTTP_POST_VARS, $HTTP_SESSION_VARS;
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            reset($this->modules);
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if (($GLOBALS[$class]->enabled && $GLOBALS[$class]->credit_class)) {
                    $post_var = 'c' . $GLOBALS[$class]->code;
                    if (tep_session_is_registered($post_var))
                        tep_session_unregister($post_var);
                }
            }
        }
    }

// Called at various times. This function calulates the total value of the order that the
// credit will be appled aginst. This varies depending on whether the credit class applies
// to shipping & tax
//
    function get_order_total_main($class, $order_total) {
        global $credit, $order;
//      if ($GLOBALS[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
//      if ($GLOBALS[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
        return $order_total;
    }

// ICW ORDER TOTAL CREDIT CLASS/GV SYSTEM - END ADDITION

    function get_all_totals_list() {
        global $HTTP_POST_VARS, $HTTP_SESSION_VARS, $currencies, $order, $cart, $update_totals_custom;
        //echo'<pre>';print_r($order->info['currency']);
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            reset($this->modules);
            $output_string = '<table class="p-or-t-tab">';
            $output_string .= '<tr class="row"><td></td><td align="right"><strong>' . TEXT_EXC_VAT . '</strong></td><td align="right"><strong>' . TEXT_INC_VAT . '</strong></td></tr>';

            $unused_modules = [];
            $js = "\n";
            while (list(, $value) = each($this->modules)) {
                $class = substr($value, 0, strrpos($value, '.'));
                if (($GLOBALS[$class]->enabled)) {
                    //if ($class == 'ot_subtax')  continue;
                    $js .= 'var $' . $GLOBALS[$class]->code . ' = {prefix: "' . $GLOBALS[$class]->output[$i]['prefix'] . '", sort_order:"' . $GLOBALS[$class]->sort_order . '"};' . "\n";
                    if (is_array($GLOBALS[$class]->output) && count($GLOBALS[$class]->output)) {
                        for ($i = 0; $i < count($GLOBALS[$class]->output); $i++) {
                            $i = 0;
                            if ($GLOBALS[$class]->code != 'ot_subtotal') {//echo '<pre>';print_r($GLOBALS[$class]);
                                $total_value_ex = $GLOBALS[$class]->output[$i]['value_exc_vat'];
                                $total_value_in = $GLOBALS[$class]->output[$i]['value_inc_tax'];
                                if (($_t = $cart->getTotalKey($GLOBALS[$class]->code)) !== false && (!in_array($class, $this->readonly) || $class == 'ot_tax'||$class=='ot_paid')) {
                                    if (is_array($_t)) {
                                        $total_value_ex = (float)$_t['ex'];
                                        $total_value_in = (float)$_t['in'];
                                    } else {
                                        $total_value_ex = (float)$_t;
                                        $total_value_in = (float)$_t;
                                    }
                                }

                                if (!in_array($class, $this->readonly)) {
                                    $js .= '$' . $GLOBALS[$class]->code . '.diff = "' . $total_value_in / ($total_value_ex != 0 ? $total_value_ex : 1) . '";' . "\n";
                                }

                                if (in_array($GLOBALS[$class]->code, $this->readonly)) {
                                    $output_string .= '<tr class="row ' . ($class=='ot_total'?'total-row':'') . ' ">' . "\n" .
                                            '   <td><strong>' .
                                            (isset($GLOBALS[$class]->output[$i]['title']) ? $GLOBALS[$class]->output[$i]['title'] : $GLOBALS[$class]->title) .
                                            '</strong></td>' . "\n" .
                                            '   <td colspan="' . ($GLOBALS[$class]->code == 'ot_subtax'?"1":"2") . '" align="right"><b>' .
                                            ($GLOBALS[$class]->code == 'ot_subtax'? $currencies->format($GLOBALS[$class]->output[$i]['value_exc_vat'], true, $order->info['currency'], $order->info['currency_value']) .'</b></td><td>': 
                                            tep_draw_hidden_field("update_totals[" . $GLOBALS[$class]->code . "]", $total_value_in * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $order->info['currency']) /*. $currencies->format_clear($GLOBALS[$class]->output[$i]['value_inc_tax'], true, $order->info['currency'], $order->info['currency_value'])*/, 'class="form-control" ' . ($GLOBALS[$class]->code == 'ot_total' ? 'data-total="' . $currencies->format_clear($order->info['total_inc_tax']/*GLOBALS[$class]->output[$i]['value']*/, true, $order->info['currency'], $order->info['currency_value']) . '" data-currency="' . $order->info['currency'] . '" ' : ''))) .
                                            ($GLOBALS[$class]->code == 'ot_subtax'? '': 
                                            $currencies->format($GLOBALS[$class]->output[$i]['value_inc_tax'], true, $order->info['currency'], $order->info['currency_value'])) .
                                            '       </b></td>' . "\n" .
                                            '   <td>' . ($GLOBALS[$class]->code == 'ot_tax' && abs($GLOBALS[$class]->output[$i]['difference']) != 0 ? '<a href="javascript:void(0)" class="adjust_tax" data-prefix="' . (is_numeric($GLOBALS[$class]->output[$i]['difference']) && $GLOBALS[$class]->output[$i]['difference'] >= 0 ? "+" : "-") . '"><div>' . sprintf(TEXT_ADJUST_TAX, ($GLOBALS[$class]->output[$i]['difference']>0?"+":"-") . abs($GLOBALS[$class]->output[$i]['difference'])) . '<div class="adjust_explanation">' . TEXT_ADJUST_EXPLANATION . '</div></div></a>' : '') . '</td>' .
                                            '</tr>';
                                } else {
                                    $output_string .= '<tr class="row">' . "\n" .
                                            '   <td><strong>' .
                                            ($GLOBALS[$class]->code == 'ot_custom' ? $GLOBALS[$class]->title : (isset($GLOBALS[$class]->output[$i]['title']) ? $GLOBALS[$class]->output[$i]['title'] : $GLOBALS[$class]->title)) .
                                            ($GLOBALS[$class]->code == 'ot_custom' ? '<input type="input" name="update_totals_custom[desc]" value="' . $update_totals_custom['desc'] . '" class="form-control" style="width:50%;float:right;">' : '') .
                                            '</strong></td>' . "\n" .
                                            '   <td align="right" ' . ($GLOBALS[$class]->code == 'ot_custom' ? 'colspan=2' : '') . '>' .
                                            ($GLOBALS[$class]->code == 'ot_custom' ? tep_draw_pull_down_menu('update_totals_custom[prefix]', array(array('id' => 'plus', 'text' => '+'), array('id' => 'minus', 'text' => '-')), $update_totals_custom['prefix'], 'style="width:30px; float: left;" class="form-control"') : '') .
                                            tep_draw_hidden_field("update_totals[" . $GLOBALS[$class]->code . "][ex]", $total_value_ex * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $order->info['currency'])/*$currencies->format_clear($total_value_ex, true, $order->info['currency'], $order->info['currency_value'])*/, 'class="form-control mask-money use-recalculation" data-control="$' . $GLOBALS[$class]->code . '" data-marker="ex"' .
                                                    ($GLOBALS[$class]->code == 'ot_custom' ? 'style="width:78%;float:right;"' : '')) .
                                            '      <b>' . $GLOBALS[$class]->output[$i]['text_exc_tax'] . '</b>' .
                                            '   </td>' . "\n" .
                                            ($GLOBALS[$class]->code != 'ot_custom' ?
                                            '   <td align="right">' .
                                            tep_draw_hidden_field("update_totals[" . $GLOBALS[$class]->code . "][in]", $total_value_in * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $order->info['currency'])/*$currencies->format_clear($total_value_in, true, $order->info['currency'], $order->info['currency_value'])*/, 'class="form-control mask-money use-recalculation" data-control="$' . $GLOBALS[$class]->code . '" data-marker="in"') .
                                            '      <b>' . $GLOBALS[$class]->output[$i]['text_inc_tax'] . '</b>' .
                                            '   </td>' . "\n" : '') .
                                            '   <td class="totals-adjust"><div class="totals edit-pt"><i class="icon-pencil"></i></div> ' . ($GLOBALS[$class]->code != 'ot_shipping'? '<div class="totals del-pt" onclick="removeModule(\'' . $GLOBALS[$class]->code . '\')"></div>': '') . '</td>              </tr>';
                                }
                            } else {
                                $output_string .= '<tr class="row">' . "\n" .
                                        '   <td><strong>' . $GLOBALS[$class]->output[$i]['title'] . '</strong></td>' . "\n" .
                                        '   <td align="right"><b>' . $GLOBALS[$class]->output[$i]['text_exc_tax'] . '</b></td>' . "\n" .
                                        '   <td align="right"><b>' . $GLOBALS[$class]->output[$i]['text_inc_tax'] . '</b></td><td></td>' . "\n" .
                                        '</tr>';
                            }
                        }
                    } else {
                        $unused_modules[$GLOBALS[$class]->code] = $GLOBALS[$class]->title;
                    }
                }
            }//die;
            //
		$output_string .= '
		<tr class="row"><td>' . (isset($GLOBALS['ot_paid'])? '<button class="btn btn-default update-paid-amount">'.TEXT_UPDATE_PAID_AMOUNT. '</button>' : ""). '</td>'
                        . '<td align="right"><button class="btn btn-default totals_reset">' . TEXT_RESET_RECALCULATION . '</button></td>'
                        . '<td align="right"><button class="btn btn-default add-more">' . HEADER_ADD_TOTAL_ELEMENT . '</button></td></tr>
		<!--<tr class="row"><td></td><td colspan="2"><input type="checkbox" name="reset_totals" class="totals_off_on">&nbsp;' . TEXT_RESET . '</td></tr>-->
		</table>' .
                    "<script>" . $js . "
		
		var list_modules='" . str_replace(array("\n", "\r"), array("\\n", "\\r"), \yii\helpers\Html::checkboxList('new_module[]', '', $unused_modules, ['class' => "form-control new-modules"])) . "'; 

		</script>";

            return $output_string;
        }
    }
    
}
