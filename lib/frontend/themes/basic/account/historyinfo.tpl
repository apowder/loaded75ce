{use class="frontend\design\Info"}
<div class="account_history_info {if $pay_link}account_history_info_due{/if}">
    {if $pay_link}
            <div class="not_fully_paid_td">{$smarty.const.TEXT_NOT_FULLY_PAID}</div>
        {/if}
		<div class="buttonBox topButtons">
      
      <div class="button1">
        {if $pay_link}
            <a class="btn-2" href="{$pay_link}">{$smarty.const.PAY}</a>
        {/if}
        {if $reorder_link}
            <a class="btn btn2" href="{$reorder_link}"{if $reorder_confirm} data-js-confirm="{$reorder_confirm|escape:'html'}"{/if}>{$smarty.const.IMAGE_BUTTON_REORDER}</a>
        {/if}
          <a class="btn-2" href="{$print_order_link}" target="_blank">{$smarty.const.TEXT_INVOICE}</a>
      </div>
    </div>
    <div class="account_history_info_due_wr"> 
    <h1>{$smarty.const.HEADING_ORDER_NUMBER_NEW}:#{$order_title} ({$order_info_status}) <span>{$order_date}</span></h1>
    <div class="history_info">
        <div class="historyInfoColumn">
            <h2 class="title-name">{$smarty.const.HEADING_NAME}</h2>
						<div class="contentColumn">{$order->customer['name']}</div>
						<h2 class="title-phone">{$smarty.const.ENTRY_TELEPHONE_NUMBER}</h2>
						<div class="contentColumn">{$order->customer['telephone']}</div>
						<h2 class="title-email">{$smarty.const.ENTRY_EMAIL_ADDRESS}</h2>
						<div class="contentColumn">{$order->customer['email_address']}</div>
        </div>    
        {if $order_delivery_address != ''}
            <div class="historyInfoColumn">
                <h2 class="title-delivery-address">{$smarty.const.HEADING_DELIVERY_ADDRESS}</h2>
                <div class="contentColumn">
                    <div>{$order_delivery_address}</div>            
                </div>
                {if $order_shipping_method !=''}
                    <h2 class="title-ship-method">{$smarty.const.HEADING_SHIPPING_METHOD}</h2>
                    <div class="contentColumn">{$order_shipping_method}</div>
                    {if $ext = \common\helpers\Acl::checkExtension('DelayedDespatch', 'showDeliveryDate')}
                    <div class="contentColumn">{$ext::showDeliveryDate($order->info['delivery_date'])}</div>
                    {/if}
                {/if}
            </div>
        {/if} 
        <div class="historyInfoColumn">
            <h2 class="title-billing-address">{$smarty.const.TEXT_BILLING_ADDRESS}</h2>
            <div class="contentColumn">
                <div>{$order_billing}</div>                
            </div>
            <h2 class="title-payment">{$smarty.const.HEADING_PAYMENT_METHOD}</b></h2>
            <div class="contentColumn">{$payment_method}</div>
        </div> 
    </div>
    <div class="productsDiv">
			<h2 class="product_details">{$smarty.const.HEADING_PRODUCT_DETAILS}</h2>
				<div class="cart-listing">			
            {if $tax_groups > 1}
								<div class="headings">
									<div class="image">{$smarty.const.HEADING_PRODUCTS}</div>
									<div class="name"></div>
									<div class="qty">{$smarty.const.HEADING_TAX}</div>
									<div class="price">{$smarty.const.HEADING_TOTAL}</div>
								</div>
            {else}
								<div class="headings">
									<div class="image">{$smarty.const.HEADING_PRODUCTS}</div>
									<div class="name"></div>
									<div class="qty"></div>
									<div class="price">{$smarty.const.HEADING_PRICE}</div>
								</div>
            {/if}
            {foreach $order_product as $order_product_array}
						<div class="item">
							<div class="image">{if $order_product_array.product_info_link}
                <a href="{$order_product_array.product_info_link}" title="{$order_product_array.order_product_name|escape:'html'}"><img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}"></a>
                  {else}
                <img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}">
                  {/if}
            </div>

							<div class="name">
								{$order_product_array.order_product_qty} x
                {if $order_product_array.product_info_link}
                  <a href="{$order_product_array.product_info_link}" title="{$order_product_array.order_product_name|escape:'html'}">{$order_product_array.order_product_name}</a>
                {else}
                  {$order_product_array.order_product_name}
                {/if}

								{if count($order_product_array['attr_array'])>0}
										<div class="history_attr">
										{foreach $order_product_array['attr_array'] as $info_attr}
												{if $info_attr.order_pr_option}
														<div><strong>{$info_attr.order_pr_option}:</strong><span>{$info_attr.order_pr_value}</span></div>
												{/if}
										{/foreach}
										</div>
								{/if}
                                                                {if $order_info_status == 'Delivered'}
								<div><a class="view_link popup" href="{tep_href_link('reviews/write', 'products_id='|cat:$order_product_array.id, 'SSL')}">{$smarty.const.IMAGE_BUTTON_WRITE_REVIEW}</a></div>
                                                                {/if}
							</div>
							<div class="right-area">
								<div class="qty">
								{if $tax_groups > 1}
                    {$order_product_array.order_products_tax}    
                {/if}
								</div>
								<div class="price">{$order_product_array.final_price}</div>
							</div>
						</div>
            {/foreach}
					</div>
    </div>
    <div class="historyTotal">
        <table class="tableForm">
            {foreach $order_info_ar as $order_info_arr}
                <tr class="'{$order_info_arr['class']} {if $order_info_arr['show_line']} totals-line{/if}">
                    <td align="right">{$order_info_arr.title}</td>
                    <td align="right">{$order_info_arr.text}</td>
                </tr>
            {/foreach}
        </table>
    </div>
    <div class="order_history_table">
        <h2 class="o_history_info">{$smarty.const.HEADING_ORDER_HISTORY}</h2>
        <div class="contentBoxContents">
            <table class="tableForm">
								<tr>
									<th width="10%">{$smarty.const.HEADING_DATE_ADDED}</th>
									<th width="20%">{$smarty.const.HEADING_STATUS}</th>
									<th>{$smarty.const.HEADING_COMMENTS}</th>
								</tr>
                {foreach $order_statusses as $statusses}
                    <tr>
                        <td>{$statusses.date}</td>
                        <td>{$statusses.status_name}</td>
                        <td>{$statusses.comments_new}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
    <div class="buttonBox">
        <div class="button2"><a class="btn" href="{$back_link}">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
      <div class="button1"><a class="btn-1" href="{$print_order_link}" target="_blank">{$smarty.const.TEXT_INVOICE}</a></div>
    </div>
            </div>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $(".account_history_info_due .account_history_info_due_wr .btn, .account_history_info_due .account_history_info_due_wr a").click(function(event) { 
        event.preventDefault();
    });
    if ( typeof alertMessage !== 'function' ) return;
    $('a[data-js-confirm]').on('click', function () {
      alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>');
      return false;
    });
		$('.popup').popUp({
        box: '<div class="popup-box-wrap popup-write"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>'
    });
  })
</script>
