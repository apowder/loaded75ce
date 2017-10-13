{use class="yii\helpers\Html"}
{use class="backend\components\Currencies"}
{Currencies::widget()}
{\backend\assets\OrderAsset::register($this)|void}
<script>
var cc_number = new Array();
var cc_type = new Array();
var cc_owner = new Array();
var cc_expires = new Array();
var cc_cvn = new Array();
function select_cc(cc){
        var f = document.edit_order;
        f.update_info_cc_type.value = cc_type[cc];
        f.update_info_cc_owner.value = cc_owner[cc];
        f.update_info_cc_number.value = cc_number[cc];
        f.update_info_cc_expires.value = cc_expires[cc];
        f.update_info_cc_cvn.value = cc_cvn[cc];
      }
var selected_shipping = false;

function rowOverEffect_paym(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect_paym(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}

function rowOverEffect_ship(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect_ship(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}

</script>
<div class="gridBg">
    <div class="btn-bar btn-bar-top after">
        <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
        <div class="btn-right">
        {if is_array($admin_choice) && count($admin_choice)}
         <div class="dropdown btn-link-create" style="float:left">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {$smarty.const.TEXT_UNSAVED_CARTS}
                <i class="icon-caret-down small"></i>
            </a>
            <ul class="dropdown-menu">
                {foreach $admin_choice as $choice}
                <li>{$choice}</li>
                {/foreach}
            </ul>
        </div>
        {/if}
        {if $oID}
        <a href="{$app->urlManager->createUrl(['orders/order-history', 'orders_id' => $oID])}" class="btn-link-create popup" data-class="legend-info">{$smarty.const.TEXT_ORDER_LEGEND}</a><a href="javascript:void(0)" onclick="return deleteOrder({$oID});" class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</a>
        {/if}
        </div>        
    </div>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<div id="message">
{$message}
</div>
<input type="hidden" name="admin_message" value="{if strlen($admin_message) > 0}1{else}0{/if}">
<!--===Process Order ===-->
<div class="row">
    <div class="col-md-12 editing" id="order_management_data">
		{tep_draw_form('create_order', 'orders/order-edit', $form_params, 'post', 'id="edit_order" onSubmit="return updateOrderProcess();"')}
		{tep_draw_hidden_field('oID', {$oID})}
        {tep_draw_hidden_field('currentCart', "{$currentCart}")}
                <!-- Begin Phone/Email Block -->
				 <div class="widget box box-no-shadow">
					<div class="widget-header widget-header-contact">
						<h4>{$smarty.const.T_CONTACT}</h4>
                                                <div class="toolbar no-padding">
                                                    <div class="btn-group">
                                                            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                                                    </div>
                                                </div>
					</div>
					<div class="widget-content">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="cr-ord-cust">
                                                        <span>{$smarty.const.ENTRY_CUSTOMER}</span>
                                                        <div><a href="{Yii::$app->urlManager->createUrl(['customers/customeredit','customers_id' => $order->customer['customer_id']])}">{\common\helpers\Address::address_format($order->customer['format_id'], $order->customer, 1, '', '<br>')}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="w-line-row col-xs-6">
                                                    <div class="edp-line">
                                                        <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<span class="fieldRequired">*</span></label>
                                                        {tep_draw_input_field('update_customer_email_address', $order->customer['email_address'], ' size="35" class="form-control"')}
                                                    </div>   
                                                    <div class="edp-line">
                                                        <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>
                                                        {tep_draw_input_field('update_customer_telephone', $order->customer['telephone'], ' size="15" class="form-control"')}
                                                    </div> 
                                                    <div class="edp-line">
                                                        <label>{$smarty.const.ENTRY_LANDLINE}</label>
                                                        {tep_draw_input_field('update_customer_landline', $order->customer['landline'], ' size="15" class="form-control"')}
                                                    </div>
                                                </div>
                                            </div>
                                            
						<div class="w-line-row w-line-row-1 w-line-row-req">
							<span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
						</div>
					</div>
				 </div>
   
				<!-- End Phone/Email Block -->
                                <div class="widget box box-no-shadow widget-closed">
                                    <div class="widget-header widget-header-address">
                                        <h4>{$smarty.const.TEXT_ADDRESS_DETAILS}</h4>
                                        <div class="toolbar no-padding">
                                            <div class="btn-group">
                                                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="widget-content">
                                            <!-- Begin Addresses Block -->
                                            <div id="address_details">
                                                {$address_details}
                                            </div>

                                            <div id="update_billing_address_box" class="w-line-row w-line-row-1" style="display: none;">
                                                <input type="checkbox" name="update_billing_address" value="1"><label>Update in address book</label>
                                            </div>			  
                                            <input type="hidden" name="address_has_been_changed" value="0">
                                            <!-- End Addresses Block -->
                                        </div>
                                </div>
                                            
                                <div class="widget box box-no-shadow widget-closed">
                                    <div class="widget-header widget-header-address">
                                        <h4>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}/{$smarty.const.TEXT_SELECT_PAYMENT_METHOD}</h4>
                                        <div class="toolbar no-padding">
                                            <div class="btn-group">
                                                <span class="btn btn-xs widget-collapse widget-collapse-height"><i class="icon-angle-up"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="widget-content create-or-wrap after">
                                            <div class="widget box w-box-left wb-or-ship wb-or-ship1">
                                                <div class="widget-header widget-header-shipping-m"><h4>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}</h4></div>
                                                <div id="shiping_holder">
                                                    {$app->controller->renderAjax('shipping', ['quotes'=> $shipping_details['quotes'], 'quotes_radio_buttons' => $shipping_details['quotes_radio_buttons'], 'order' => $order])}
                                                </div>
                                            </div>
                                            <div class="widget box w-box-right wb-or-ship wb-or-pay wb-or-pay1">
                                                <div class="widget-header widget-header-payment-m"><h4>{$smarty.const.TEXT_SELECT_PAYMENT_METHOD}</h4></div>
                                                <div id="payment_holder">
                                                    {$app->controller->renderAjax('payment', ['selection'=> $selection, 'order'=>$order, 'gv_amount_current' => $gv_amount_current, 'payment'=>$payment, 'oID' => $oID, 'gv_redeem_code' => $gv_redeem_code, 'cot_gv_active' => $cot_gv_active, 'custom_gv_amount' => $custom_gv_amount])}
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                

<!--begin select shipping and payment Block-->

              {if $smarty.const.ACCOUNT_COMPANY == 'true'}
              <div class="widget box box-no-shadow">
                  <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_COMPANY}</h4></div>
                  <div class="widget-content">              
                      <div class="w-line-row w-line-row-2 w-line-row-2-big">
                          <div>
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_COMPANY}</label>
								  {tep_draw_input_field('customers_company', $order->customer['company'], 'maxlength="32" class="form-control"')}
                              </div> 
                          </div>       
                          <div>
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_BUSINESS}</label>
								{tep_draw_input_field('customers_company_vat', $order->customer['company_vat'], 'maxlength="32" class="form-control"')}
                            </div>                              
                        </div>
                      </div>                   
                  </div>
              </div>
			 {/if}

			
			<div class="widget box box-no-shadow">
				<div class="box-or-prod-wrap-">
					<div id="products_holder">
					{$products_details}
					</div>
					<div class="">
						<div id="totals_holder">
						{$order_total_details}
						</div>
					</div>					
				</div>

			</div>
			<div id="order_statuses">
			{$order_statuses}
			</div>
			
			<div class="btn-bar">
				<div class="btn-right">
					{*tep_draw_hidden_field('action', 'update', 'id="action_type"')*}
					{tep_draw_hidden_field('update_and_pay', '', 'id="action_update_and_pay"')}
					{tep_draw_hidden_field('update_and_pay_amount', '0', 'id="action_update_and_pay_amount"')}
					{*<input type="submit" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE}" >*}
					{*<input type="submit" onclick="return changeActionType();" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE_RETURN}" >*}
					{*<input type="button" class="btn btn-primary" value="{$smarty.const.IMAGE_UPDATE}" onclick="updateEditOrder()">*}
				</div>
				<div class="btn-left">
					<a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
					<a href="javascript:void(0)" id="cancel_button" style="display: none;" onclick="return resetStatement({$oID});" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
				</div>
				<a class="btn btn-primary update_pay" href="javascript:void(0)" onclick="return updatePay();">{$smarty.const.IMAGE_UPDATE_PAY}</a>
			</div>	
	</form>
    </div>
</div>
<!-- Process Order -->
<script type="text/javascript">

function updateProduct(obj) {
	var ids = [];
	if ($(obj).parents('.product_info').find('input[name*="id["]').size()>0){
		$.each($(obj).parents('.product_info').find('input[name*="id["]'), function(i, e){
			ids[$(e).data('option')] = $(e).val();
		})
	}
    $.post("{Yii::$app->urlManager->createUrl('orders/addproduct')}?orders_id={$oID}", {
		'action': 'add_product',
        'currentCart': $('input[name=currentCart]').val(),
		'uprid' :  encodeURIComponent($(obj).parents('.product_info').find('input[name=uprid]').val()),
		'products_id': $(obj).parents('.product_info').find('input[name=products_id]').val(),
		'qty': $(obj).parents('.product_info').find('.qty').val(),
		'tax' : $(obj).parents('.product_info').find('.tax').val(),
		//'id' : ids,
		'gift_wrap':$(obj).parents('.product_info').find('.gift_wrap').prop('checked')
	}, function(data, status){
        if (status == "success") {
			$('#shiping_holder').html(data.shipping_details);
			$('#products_holder').html(data.products_details);
			$('#totals_holder').html(data.order_total_details);
			$('#totals_holder .mask-money').setMaskMoney();
			$('#message').html(data.message);
			setPlugin();
			localStorage.orderChanged = true;
        } else {
            alert("Request error.");
        }
    },"json");
}

function updatePay() {		
	var ot_total = $('input[name="update_totals[ot_total]"]').data('total');
    var ot_paid = $('input[name="update_totals[ot_paid]"]').val();
    $.post("orders/updatepay", {
		'orders_id': "{$oID}",
		'ot_total': ot_total,
        'ot_paid': ot_paid,
		'currency_id' : currency_id
	}, function(data, status){
        if (status == "success") {
            var n = $(window).scrollTop();
            $('.pop-up-content:last').html(data);
            $(window).scrollTop(n);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function checkproducts(){
	var success = true;
	var attributes = $('form[name=cart_quantity]').find('select[name*=id]');
	var qty = $('form[name=cart_quantity]').find('#qty');
	if ($(attributes).size()> 0 ){
		$.each($(attributes), function(i, e){
			if ($(e).val() == 0) success = false;
		})
	}
    if (typeof product == object && product.multy_qty){
    //
    } else {
        if ($(qty).val() < 1 || $(qty).val().length == 0){
            $(qty).val('1');
        }
    }
	if (!success){
       bootbox.dialog({
        message: '<div class=""><label class="control-label">'+"{$smarty.const.ERROR_WARNING}"+'</label></div>',
        title: "{$smarty.const.ICON_ERROR}",
          buttons: {
            cancel: {
              label: "{$smarty.const.TEXT_BTN_OK}",
              className: "btn-cancel",
              callback: function() {
                }
            }
          }
      });

	} else {
		if (typeof unformatMaskMoney == 'function') {
			unformatMaskMoney();
		}	
	}
 return success;
}

function checkAdmin(){
    if ($('input[name=admin_message]').val() == 1){
        bootbox.dialog({
                    closeButton: false,
                    message: "{$admin_message}",
                    title: "{$smarty.const.ICON_WARNING}",
                    buttons: {
                            success: {
                                    label: "{$smarty.const.TEXT_BTN_YES}",
                                    className: "btn-delete",
                                    callback: function() {
                                        $.post("{$app->urlManager->createUrl('orders/reset-admin')}", {
                                            'basket_id': "{$cart->basketID}",
                                            'customer_id': "{$cart->customer_id}",
                                            'orders_id': "{$oID}",
                                        }, function(data, status){
                                            if (status == "success") {
                                                window.location.href= data.reload;
                                            } else {
                                                alert("Request error.");
                                            }
                                        },"json");
                                    }
                            },
                            main: {
                                    label: "{$smarty.const.TEXT_BTN_NO}",
                                    className: "btn-cancel",
                                    callback: function() {
                                        window.location.href = "{$app->urlManager->createUrl('orders/')}";
                                    }
                            }
                    }
            });       
    }
}

function deleteOrder() {
        bootbox.dialog({
                message: "{$smarty.const.TEXT_INFO_DELETE_INTRO}",
                title: "{$smarty.const.TEXT_INFO_HEADING_DELETE_ORDER}",
                buttons: {
                        success: {
                                label: "{$smarty.const.TEXT_BTN_YES}",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("{$app->urlManager->createUrl('orders/orderdelete')}", {
                                        'orders_id': "{$oID}",
                                    }, function(data, status){
                                        if (status == "success") {
                                            $("#order_management_data").html('');
                                            window.location.href= "{$app->urlManager->createUrl('orders/')}";
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "{$smarty.const.TEXT_BTN_NO}",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
    return false;
}
function changeActionType() {
    var subaction = document.createElement('input');
    subaction.name='subaction';
    subaction.type='hidden';
    subaction.value='return';
    document.create_order.appendChild(subaction);
    return true;
}
function closePopup() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
}
function billingAddressHasBeenChanged() {
    $('#update_billing_address_box').show();
    //orderHasBeenChanged();
}
function billingAddressNotChanged() {
    $('#update_billing_address_box').hide();
    $('input[name="update_billing_address"]').prop('checked', false);
    //orderHasBeenChanged();
}
function deliveryAddressHasBeenChanged() {
    $('#update_delivery_address_box').show();
    //orderHasBeenChanged();
}
function deliveryAddressNotChanged() {
    $('#update_delivery_address_box').hide();
    $('input[name="update_delivery_address"]').prop('checked', false);
    //orderHasBeenChanged();
}
function orderHasBeenChanged() {
	if (typeof unformatMaskMoney == 'function') {
		unformatMaskMoney();
	}
	$.post('orders/order-edit' + ($('input[name=oID]').val().length>0?'?orders_id='+$('input[name=oID]').val():''), 
		$('#edit_order').serialize(),
	function (data, status){
		$('#address_details').html(data.address_details);
		$('#shiping_holder').html(data.shipping_details);
		$('#payment_holder').html(data.payment_details);
		$('#products_holder').html(data.products_details);
		$('#totals_holder').html(data.order_total_details);
		$('#order_statuses').html(data.order_statuses);
		$('#totals_holder .mask-money').setMaskMoney();
		$('#message').html(data.message);
        setDataTables();
		localStorage.orderChanged = true;
		setPlugin();
	}, 'json');
}
function backStatement() {
{if $app->controller->view->newOrder}
    {if $app->controller->view->backOption == 'orders'}
        window.location.href="{$app->urlManager->createUrl('orders/')}";
    {/if}
    {if $app->controller->view->backOption == 'customers'}
        window.location.href="{$app->urlManager->createUrl('customers/')}";
    {/if}
{else}    
    window.history.back();
{/if}        
    return false;
}
function resetStatement(id) {
    $('#cancel_button').hide();
    $.post("{$app->urlManager->createUrl('orders/order-edit')}", {
        'orders_id': id,
    }, function (data, status) {
        if (status == "success") {  
            $("#order_management_data").html(data);
            $('.datatable').DataTable( {
                "scrollY":        "200px",
                "scrollCollapse": true,
                "paging":         false
            } );
        }
    }, "html");
    return false;
}

function addModule(code, visible){
	var params = {};
    params.currentcart = $('input[name=currentCart]').val();
	if (code.length < 1) return;
	params.update_totals = {};
	if (typeof unformatMaskMoney == 'function') {
		unformatMaskMoney();
	}	
	$.each($('input[name*=update_totals].use-recalculation'), function (i,e){
		if (!params.update_totals.hasOwnProperty($(e).data('control').substr(1))) params.update_totals[$(e).data('control').substr(1)] = {};
		params.update_totals[$(e).data('control').substr(1)].in = $('input[name="update_totals['+$(e).data('control').substr(1)+'][in]"]').val();
		params.update_totals[$(e).data('control').substr(1)].ex = $('input[name="update_totals['+$(e).data('control').substr(1)+'][ex]"]').val();
	});
	
	if (typeof code != 'undefined' && code.length > 0){
		params.action = 'new_module';		
		if (visible){
                    if (Array.isArray(code)){
                        $.each(code, function(i,e){
                          if (code == '$ot_custom'){
                        	params.update_totals_custom = {};
                                params.update_totals_custom['prefix'] = $('select[name="update_totals_custom[prefix]"]').val();
                                params.update_totals_custom['desc'] = $('input[name="update_totals_custom[desc]"]').val();
                          } else {
                              params.update_totals[e] = '&nbsp;'; 
                          }                          
                        });
                    } else {
                        params.update_totals[code] = '&nbsp;';
                    }                  
                }		
	}	
        
	$.post('orders/order-edit?orders_id={$oID}', 
		params
	, function(data, status){
		$('#totals_holder').html(data.order_total_details);
		$('#totals_holder .mask-money').setMaskMoney();
	}, 'json');
}

function removeModule(code){
	$.post('orders/order-edit?orders_id={$oID}', {
		'action':'remove_module',
		'module':code,
	}, function(data, status){
		$('#totals_holder').html(data.order_total_details);
		$('#totals_holder .mask-money').setMaskMoney();
	}, 'json');	
}

function setDataTables(){
$('.datatable').DataTable( {
					"scrollY":        "200px",
					"scrollCollapse": true,
					"paging":         false
				} );
}

function updateOrderProcess(){
	if (localStorage.orderChanged == 'false') {
		if ( typeof alertMessage == 'function' ) {
          alertMessage('<div class="widget box"><div class="widget-content">{$smarty.const.WARNING_ORDER_NOT_UPDATED|escape:'javascript'}</div><div class="noti-btn"><div><span class="btn btn-cancel">{$smarty.const.TEXT_OK}</span></div></div></div>');
        }else{
          alert('{$smarty.const.WARNING_ORDER_NOT_UPDATED|escape:'javascript'}');
        }
		return false;
	}
	if (typeof unformatMaskMoney == 'function') {
		unformatMaskMoney();
	}
	$('#edit_order').append('<input type="hidden" name="action" value="update">');
    $.post("{$app->urlManager->createUrl(['orders/order-edit'])}"+($('input[name=oID]').val().length>0?'?orders_id='+$('input[name=oID]').val():''), $('#edit_order').serialize(), function(data, status){
        if (status == "success") {
			localStorage.orderChanged = false;
			if (data.reload){
				window.location.href = data.reload;
			} else {
				$('#address_details').html(data.address_details);
				$('#shiping_holder').html(data.shipping_details);
				$('#payment_holder').html(data.payment_details);
				$('#products_holder').html(data.products_details);
				$('#totals_holder').html(data.order_total_details);
				$('#order_statuses').html(data.order_statuses);
				$('#totals_holder .mask-money').setMaskMoney();
				$('#message').html(data.message);
				setDataTables();
                $('textarea[name=comment]').val('');
                $('input[name=notify]').prop('checked', false);
                $('#edit_order input[name=action]').remove();
				setPlugin();			
			}			
        } else {
            alert("Request error.");
        }
    },"json");
    return false;
}
    
function addProduct(id){
    $.get("{$app->urlManager->createUrl('orders/addproduct')}", $('form[name=search]').serialize()+'&orders_id='+id, function(data, status){
        if (status == "success") {
            $("#order_management_data").html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}                                
                              
function check_form() {
    return false;
    $.post("{$app->urlManager->createUrl('orders/ordersubmit')}", $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            $("#order_management_data").html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteOrderProduct(obj) {
	$.post('orders/addproduct?orders_id={$oID}',{
		'action' : $(obj).parents('.product_info').find('input[name=delete_type]').val(),
        'currentCart': $('input[name=currentCart]').val(),
		'products_id' : encodeURIComponent($(obj).parents('.product_info').find('input[name=uprid]').val()),
	}, function(data, status){
		$('#products_holder').html(data.products_details);
		$('#shiping_holder').html(data.shipping_details);
		$('#payment_holder').html(data.payment_details);
		$('#totals_holder').html(data.order_total_details);
		$('#totals_holder .mask-money').setMaskMoney();
        localStorage.orderChanged = true;
		setPlugin();
	},'json');
}
var user_work = false;
var tout;
function activatePlusNinus(parent_class) {    
    $('body').on('click', parent_class+' .pr_plus', function(){
     var _this = this;
     val = $(this).prev('input').val();
     var input = $(this).prev('input');
     var step = parseInt(input.attr('data-step'));
     var max = parseInt(input.attr('data-max'));
     val = parseInt(val) + parseInt(step);
     if (val > max) val = max;
     input.val(val);
     $(input).trigger('change');
     if (typeof product == 'object'){
        if (typeof product.getQty == 'function')
            product.checkQuantity();
     }
     clearInterval(tout);
     if (val > 1) input.siblings('.pr_minus').removeClass('disable');
     if (!input.hasClass('new-product')){
        tout = setInterval(function(){
            updateProduct(_this);
            clearInterval(tout);
        },1000);
     }
		
   });

   $('body').on('click', parent_class+' .pr_minus', function(){
     if ($(this).hasClass('disable')) return;
     var _this = this;
     var input = $(this).next('input');
     var step = parseInt(input.attr('data-step'));
     var min = parseInt(input.attr('data-min'));
     val = $(this).next('input').val();
     if (val > min){
       val = parseInt(val) - parseInt(step);
       $(this).next('input').siblings('.more').removeClass('disableM');
     }
     if (val < min) val = min;
     clearInterval(tout);     
     input.val(val);
     $(input).trigger('change');
     if (typeof product == 'object'){
        if (typeof product.getQty == 'function')
            product.checkQuantity();
     }
     if (val < 2) $(parent_class +'.pr_minus').addClass('disable');
	 if (!input.hasClass('new-product') && !user_work){
            tout = setInterval(function(){
                updateProduct(_this);
                clearInterval(tout);
            },1000);
         }
   });
}

function setPlugin(){
    $('a.popup').popUp({
		box_class: $(this).data('class'),
        data:{ 'currentCart' : $('input[name=currentCart]').val() }
	});		    
}



$(document).ready(function() { 
	setPlugin();
    $(".update_pay").popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-update-pay'><div class='popup-heading up-head'>{$smarty.const.IMAGE_UPDATE_PAY}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });	
	
    activatePlusNinus('#products_holder');
	activatePlusNinus('.edit_product_popup');	
	activatePlusNinus('.product_adding');	
	localStorage.orderChanged = true;
    
    checkAdmin();
    
    var url = window.location.href.substr(0, window.location.href.length- window.location.hash.length);
    if (url.indexOf('currentCart') == -1){
        if (url.indexOf('?') != -1){    
            url = url + '&currentCart=' + $('input[name=currentCart]').val();
        } else {
            url = url + '?currentCart=' + $('input[name=currentCart]').val();
        }
        url = url + window.location.hash;
        window.history.replaceState({ }, '', url);
    }

    
	$('body').on('blur', '.product_info .qty', function(){
        val = $(this).val();
        var step = $(this).attr('data-step');
        var max = $(this).attr('data-max');
        var min = $(this).attr('data-min');
        if (val > max) val = max;
        if (val < min) val = min;
        $(this).val(val);
		updateProduct(this);
	});
    
      		
        $(window).resize(function () {
        setTimeout(function () {
            var height_1 = $('.wb-or-ship1').height();
            var height_2 = $('.wb-or-pay1').height();
            if(height_1 > height_2){
                $('.wb-or-pay1').css('min-height', height_1);
            }else{
                $('.wb-or-ship1').css('min-height', height_2);
            }
        }, 800);
        $('.widget-collapse-height').click(function(){ 
            setTimeout(function () {
            var height_1 = $('.wb-or-ship1').height();
            var height_2 = $('.wb-or-pay1').height();
            if(height_1 > height_2){
                $('.wb-or-pay1').css('min-height', height_1);
            }else{
                $('.wb-or-ship1').css('min-height', height_2);
            }
        }, 800);
        });
        })
        $(window).resize(); 
});

</script>
				<!-- /Page Content -->
</div>