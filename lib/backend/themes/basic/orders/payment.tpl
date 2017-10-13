 <div class="widget-content">
    <div class="payment-method">
	{if is_array($selection) && $selection|count > 0}
      {foreach $selection as $i}
        <div class="item payment_item payment_class_{$i.id}"  {if $i.hide_row} style="display: none"{/if}>
        {if isset($i.methods)}
            {foreach $i.methods as $m}
                <div class="item-radio">
                    <label>
                      <input type="radio" name="payment" value="{$m.id}"{if $i.hide_input} style="display: none"{/if}{if $m.id == $payment} checked{/if}/>
                      <span>{$m.module}</span>
                    </label>
                </div>
            {/foreach}
        {else}
          <div class="item-radio">
            <label>
              <input type="radio" name="payment" value="{$i.id}"{if $i.hide_input} style="display: none"{/if}{if $i.id == $payment} checked{/if}/>
              <span>{$i.module}</span>
            </label>
          </div>
        {/if}
          {foreach $i.fields as $j}
            <div class="sub-item">
              <label>
                <span>{$j.title}</span>
                {$j.field}
              </label>
            </div>
          {/foreach}
        </div>
      {/foreach}
	{/if}
    </div>
</div>
    <div class="widget-content widget-content-top-border" style="display: none;">
      <table cellpadding=0 cellspacing=0 border=0>
          <tr>
            <td class="main pay-td">{$smarty.const.ENTRY_CREDIT_CARD_TYPE}</td>
            <td class="main pay-td2"><input name='update_info_cc_type' size='10' class="form-control" value='{$order->info['cc_type']}'></td>
          </tr>
          <tr>
            <td class="main pay-td">{$smarty.const.ENTRY_CREDIT_CARD_OWNER}</td>
            <td class="main pay-td2"><input name='update_info_cc_owner' class="form-control" size='20' value='{$order->info['cc_owner']}'></td>
          </tr>
          <tr>
            <td class="main pay-td">{$smarty.const.ENTRY_CREDIT_CARD_NUMBER}</td>
            <td class="main pay-td2"><input name='update_info_cc_number' class="form-control" size='20' value='{$order->info['cc_number']}'></td>
          </tr>
          <tr>
            <td class="main pay-td">{$smarty.const.ENTRY_CREDIT_CARD_CVN}</td>
            <td class="main pay-td2"><input name='update_info_cc_cvn' size='4' class="form-control" value='{$order->info['cc_cvn']}'></td>
          </tr>
          <tr>
            <td class="main pay-td">{$smarty.const.ENTRY_CREDIT_CARD_EXPIRES}</td>
            <td class="main pay-td2"><input name='update_info_cc_expires' size='4' class="form-control" value='{$order->info['cc_expires']}'></td>
          </tr>
        </table>
    <!-- End Credit Card Info Block -->
    </div>
	
    <div class="widget-content widget-content-top-border">
        <table cellpadding=0 cellspacing=0 border=0>
            {if (\common\helpers\Acl::checkExtension('CouponsAndVauchers', 'orderCouponVoucher'))}
                {\common\extensions\CouponsAndVauchers\CouponsAndVauchers::orderCouponVoucher($gv_redeem_code)}
            {else}
                <tr class="dis_module">
                <td class="label_name">{$smarty.const.TEXT_COUPON_CODE}</td>
                <td class="label_value">
                    <a name="coupon"></a>
                    <input name="gv_redeem_code[coupon]" class="form-control" value='' disabled="">
                </td>
                <td class="label_value">
                    <button type="button" class="btn btn-small discount_apply" disabled="">{$smarty.const.TEXT_APPLY}</button>
                </td>
            </tr>
            <tr>
                <td class="main pay-td" colspan="3">&nbsp;</td>
            </tr>
            <tr class="dis_module">
                <td class="label_name" valign="top">{$smarty.const.TEXT_GIFT_VOUCHER}</td>
                <td class="label_value">

                    <input name="gv_redeem_code[gv]" class="form-control" value='' disabled="">

                </td> 
                <td class="label_value" valign="top">
                    <button type="button" class="btn btn-small certificate_apply" disabled="">{$smarty.const.TEXT_APPLY}</button>
                </td>		
            </tr>			
            {/if}
            <tr>
                <td class="main pay-td" colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="label_name" valign="top">{$smarty.const.TEXT_CREDIT_AMOUNT_ASK_USE}<br/>
				<input type="checkbox" name="cot_gv" class="gv_check_on_off" {if $cot_gv_active}checked{/if}>
			  </td>
              <td class="label_value">
              <input type="text" name='cot_gv_amount' class="form-control" value='{$custom_gv_amount}' {if !$cot_gv_active}disabled{/if}>
              <div>
			  {sprintf($smarty.const.TEXT_NOW_CUTOMER_HAVE, $gv_amount_current)}
              </div>
              </td> 
				<td class="label_value" valign="top">
				<button type="button" class="btn btn-small gv_apply">{$smarty.const.TEXT_APPLY}</button>
			  </td>			  
            </tr>			
          </table>
    </div>	
<script>
$(document).ready(function(){
	$('input[name=payment]').change(function(){
		$.post('orders/set-payment?orders_id={$oID}', {
			'payment' : $(this).val(),
            'currentCart': $('input[name=currentCart]').val(),
		}, function(data, status){
			if (status == 'success'){
				$('#payment_holder').html(data.payment_details);
				$('#totals_holder').html(data.order_total_details);
				$('#order_statuses').html(data.order_statuses);
				$('#totals_holder .mask-money').setMaskMoney();
				$('#message').html(data.message);
                setDataTables();                
				localStorage.orderChanged = true;				
			}
		}, 'json');
	});
	
	$('.discount_apply').click(function(){
		$.post('orders/order-edit?orders_id={$oID}',{
			'action':'apply_coupon',
            'currentCart': $('input[name=currentCart]').val(),
			'gv_redeem_code[coupon]' : $('input[name="gv_redeem_code[coupon]"]').val(),
		}, function(data, status){
			$('#shiping_holder').html(data.shipping_details);
			$('#payment_holder').html(data.payment_details);
			$('#products_holder').html(data.products_details);
			$('#totals_holder').html(data.order_total_details);
			$('#order_statuses').html(data.order_statuses);
			$('#totals_holder .mask-money').setMaskMoney();
            setDataTables();
			location.hash = "#coupon";
			$('#message').html(data.message);			
			localStorage.orderChanged = true;
            setPlugin();
		},'json');
	});
    
	$('.certificate_apply').click(function(){
		$.post('orders/order-edit?orders_id={$oID}',{
			'action':'apply_coupon',
            'currentCart': $('input[name=currentCart]').val(),
			'gv_redeem_code[gv]' : $('input[name="gv_redeem_code[gv]"]').val(),
		}, function(data, status){
			$('#shiping_holder').html(data.shipping_details);
			$('#payment_holder').html(data.payment_details);
			$('#products_holder').html(data.products_details);
			$('#totals_holder').html(data.order_total_details);
			$('#order_statuses').html(data.order_statuses);
			$('#totals_holder .mask-money').setMaskMoney();
            setDataTables();
			location.hash = "#coupon";
			$('#message').html(data.message);			
			localStorage.orderChanged = true;
            setPlugin();
		},'json');
	});    
	
	$('.gv_apply').click(function(){
		var status = '';
		if ($('input[name=cot_gv]').prop('checked')){
			status = 'on';
		}
		applyCredit(status);
	})
	

});

	function applyCredit(prop){
		$.post('orders/order-edit?orders_id={$oID}',{
				'action':'update_gv_amount',
                'currentCart': $('input[name=currentCart]').val(),
				'cot_gv_amount' : $('input[name=cot_gv_amount]').val(),
				'cot_gv' : prop,
			}, function(data, status){
				$('#shiping_holder').html(data.shipping_details);
				//$('#payment_holder').html(data.payment_details);
				//$('#products_holder').html(data.products_details);
				$('#totals_holder').html(data.order_total_details);
				//$('#order_statuses').html(data.order_statuses);
				$('#totals_holder .mask-money').setMaskMoney();
				$('#message').html(data.message);	
				localStorage.orderChanged = true;
			},'json');	
	}

	(function($){
	 $(".gv_check_on_off").bootstrapSwitch(
        {
			onSwitchChange: function (element, arguments) {
					var status = '';
					if (arguments){
						$('input[name=cot_gv_amount]').prop('disabled', false);
						status = 'on';
					} else {
						$('input[name=cot_gv_amount]').prop('disabled', true);
					}
					applyCredit(status);
					return true;  
		},
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
        }
      );
	})(jQuery)
</script>
	