        <div class="widget-header widget-header-prod">
            <h4>{$smarty.const.TEXT_PROD_DET}</h4>
			<a name="products"></a>
            <div class="toolbar no-padding">
                <div class="btn-group">
                    <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                </div>
            </div>
        </div>
		<div class="widget-content widget-content-prod_">
            <table class="table" border="0" width="100%" cellspacing="0" cellpadding="2">
                <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent left" colspan="3">{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                        <th class="dataTableHeadingContent" width="10%">{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>
                        {if $giftWrapExist}
                        <th class="dataTableHeadingContent" width="6%">{$smarty.const.TEXT_GIFT_WRAP}</th>
                        {/if}
                        <th class="dataTableHeadingContent" width="8%"  align="center">{$smarty.const.TABLE_HEADING_TAX}</th>
                        <th class="dataTableHeadingContent" width="8%" align="center">{$smarty.const.TABLE_HEADING_UNIT_PRICE}</th>
                        <th class="dataTableHeadingContent" width="8%" align="center">{$smarty.const.TABLE_HEADING_TOTAL_PRICE}</th>
                        <th class="dataTableHeadingContent" width="10%" align="center">{$smarty.const.TABLE_HEADING_TOTAL_PRICE_VAT}</th>
                        <th class="dataTableHeadingContent" width="100px"></th>
                    </tr>
                </thead>
			
			{for $i=0; $i<sizeof($products); $i++}
				<tr class="dataTableRow product_info">
					<td class="dataTableContent plus_td box_al_center" valign="top" align="center">
						{if !$products[$i]['ga'] && !$products[$i]['is_pack']}
						<span class="pr_minus {if $products[$i]['qty'] eq '1'}disable{/if}"></span>
						<input name="update_products[{$products[$i]['id']}][qty]" size='2' value="{$products[$i]['qty']}"  data-max="{$products[$i]['stock_info']['max_qty']+$products[$i]['reserved_qty']}"{if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'setLimit')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($products[$i]['stock_limits'])}{/if}{if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'setLimit')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($products[$i]['stock_limits'])}{/if} class='form-control qty'><span class='pr_plus'></span>
						{tep_draw_hidden_field('products_id',{(int)$products[$i]['id']})}
						{else}
							<div class="box_al_center">{$products[$i]['qty']}</div>
						{/if}
						{tep_draw_hidden_field('uprid',{$products[$i]['id']})}
					</td>
                                        <td class="dataTableContent box_al_cente" valign="top">
						{\common\classes\Images::getImage($products[$i]['id'])}
					</td>
					<td class="dataTableContent left" valign="top">
						<label>{$products[$i]['name']}</label>
                        {if (!$products[$i]['ga'] && \common\helpers\Acl::checkExtension('PackUnits', 'queryOrderProcessAdmin'))}
                                {\common\extensions\PackUnits\PackUnits::queryOrderProcessAdmin($products, $i)}
                        {/if}
						{if is_array($products[$i]['attributes']) && $products[$i]['attributes']|count > 0}
							{for $j=0; $j<sizeof($products[$i]['attributes']); $j++}
								<div class="prop-tab-det-inp"><small>&nbsp;
									<i> - {($products[$i]['attributes'][$j]['option'])} : {($products[$i]['attributes'][$j]['value'])}</i></small>
								</div>
								<input type="hidden" name="id[{$products[$i]['attributes'][$j]['option_id']}]" data-option="{$products[$i]['attributes'][$j]['option_id']}" value="{$products[$i]['attributes'][$j]['value_id']}">
							 {/for}					
						{/if}					
					</td>
					<td class="dataTableContent left" valign="top">
						<label>{$products[$i]['model']}</label>
					</td>
                    {if $giftWrapExist}
                    <td class="dataTableContent right" valign="top">
						{if $products[$i]['gift_wrap_allowed']}
							<div class="gift-wrap"><label>+{$currencies->display_price($products[$i]['gift_wrap_price'], $products[$i]['tax'])}{*true, $order->info['currency'], $order->info['currency_value']*}<br/><input type="checkbox" name="gift_wrap[{$products[$i]['id']}]" class="check_on_off gift_wrap" {if $products[$i]['gift_wrapped']} checked="checked"{/if}/></label></div>
						{/if}
					</td>
                    {/if}
					<td class="dataTableContent" align="center" valign="top">
					{if !$products[$i]['ga']}
							{assign var="tax_selected" value="{$cart->getOwerwrittenKey($products[$i]['id'], 'tax_selected')}"}
							{if $tax_selected neq ''}
								{$zone_id = $tax_selected}
							{else}
								{assign var="zone" value="{\common\helpers\Tax::get_zone_id($products[$i]['tax_class_id'])}"}
								{assign var="zone_id" value="{$products[$i]['tax_class_id']}_{$zone}"}
							{/if}
                        {if (!$products[$i]['is_pack'])}
                            {tep_draw_pull_down_menu("update_products[{$products[$i]['id']}][tax]", $tax_class_array,  $zone_id , "class='form-control tax' onchange='updateProduct(this)'")}
                        {else}
                            {$_z = \yii\helpers\ArrayHelper::map($tax_class_array, 'id', 'text')}
                            {$_z[$zone_id]}
                        {/if}
					{/if}
					</td>
					<td class="dataTableContent" align="right" valign="top">
						<label>{$currencies->format($products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value'])}</label>
					</td>
					<td class="dataTableContent" align="right" valign="top">
						<label>{$currencies->format($products[$i]['final_price'] * $products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value'])}</label>
					</td>
					<td class="dataTableContent" align="right" valign="top">
						<label>{$currencies->format(\common\helpers\Tax::add_tax_always($products[$i]['final_price'] * $products[$i]['qty'], $products[$i]['tax']) , $recalculate, $order->info['currency'])}</label>
					</td>
					<td class="dataTableContent adjust-bar" align="center" >
						{if $products[$i]['ga']}
							<div><a href="{\yii\helpers\Url::to(['orders/addproduct', 'orders_id'=>{$oID}, 'action'=>'show_giveaways'])}" class="popup" data-class="add-product"><i class="icon-pencil"></i></a></div>
						{else}
                            {if $oID}
							<div><a href="{\yii\helpers\Url::to(['orders/addproduct', 'action' => 'edit_product', 'products_id' => {$products[$i]['id']}, 'orders_id' => {$oID}])}" class="popup" data-class="edit-product"><i class="icon-pencil"></i></a></div>
                            {else}
                            <div><a href="{\yii\helpers\Url::to(['orders/addproduct', 'action' => 'edit_product', 'products_id' => {$products[$i]['id']}])}" class="popup" data-class="edit-product"><i class="icon-pencil"></i></a></div>
                            {/if}
						{/if}						
						<div class="del-pt" onclick="deleteOrderProduct(this);">
						{if $products[$i]['ga']}
							{tep_draw_hidden_field('delete_type', 'remove_giveaway')}
						{else}
							{tep_draw_hidden_field('delete_type', 'remove_product')}
						{/if}
						
						</div>
					</td>
				</tr>
			{/for}	
		</table>
        {if $oID }
        <a href="{\yii\helpers\Url::to(['orders/addproduct', 'orders_id'=>{$oID}])}" class="btn popup" data-class="add-product">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</a>
        {else}
        <a href="{\yii\helpers\Url::to(['orders/addproduct'])}" class="btn popup" data-class="add-product">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</a>
        {/if}		
		{if $giveaway['count']>0}
			<a href="{\yii\helpers\Url::to(['orders/addproduct', 'orders_id'=>{$oID}, 'action'=>'show_giveaways'])}" class="btn btn-cancel popup" data-class="add-product">{$smarty.const.TEXT_ADD_GIVEAWAY}</a>
		{/if}
		</div>
<script>
function switchStatement(id, status) {
	updateProduct(id);
}
	(function($){
	 $(".check_on_off").bootstrapSwitch(
        {
			onSwitchChange: function (element, arguments) {
					switchStatement(element.target, arguments);
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