<div class="widget-content widget-content-top-border shipping-method">
{if is_array($quotes) && $quotes|count > 0}
  {foreach $quotes as $shipping_quote_item}
    <div class="item">
      <div class="title">{$shipping_quote_item.module}</div>
      {if $shipping_quote_item.error}
        <div class="error">{$shipping_quote_item.error}</div>
      {else}
        {foreach $shipping_quote_item.methods as $shipping_quote_item_method}
          <label class="row">
            {if $quotes_radio_buttons>0}
              <div class="input"><input value="{$shipping_quote_item_method.code}" {if $shipping_quote_item_method.selected}checked="checked"{/if} type="radio" name="shipping"/></div>
            {else}
              <input value="{$shipping_quote_item_method.code}" type="hidden" name="shipping"/>
            {/if}
            <div class="cost">{$shipping_quote_item_method.cost_f}</div>
            <div class="sub-title">{$shipping_quote_item_method.title}</div>
          </label>
        {/foreach}
      {/if}
    </div>
  {/foreach}
{/if}
{if \common\helpers\Acl::checkExtension('DelayedDespatch', 'viewAdminEditOrder')}
    {\common\extensions\DelayedDespatch\DelayedDespatch::viewAdminEditOrder($order)}
{/if}
</div>
<script>
$(document).ready(function(){
	$('input[name=shipping]').change(function(){
		$.post('orders/order-edit?orders_id={$order->order_id}', {
			'shipping' : $(this).val(),
            'currentCart': $('input[name=currentCart]').val(),
		}, function(data, status){
			if (status == 'success'){
				$('#totals_holder').html(data.order_total_details);
				$('#totals_holder .mask-money').setMaskMoney();
				localStorage.orderChanged = true;
			}
		}, 'json');
	})
})
</script>
