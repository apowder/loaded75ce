{use class="frontend\design\Info"}
<div class="account_page">
<div class="buttons">
	<div class="button1"><a class="btn" href="{$account_links['account_logoff']}">{$smarty.const.TEXT_LOGOFF}</a></div>
</div>
    <h1>{$smarty.const.HEADING_TITLE}</h1>		
    {$account_links['message']}    
		<ul class="topAccount after">
			<li>
				<div class="dateLast">
					<span class="date_title">{$smarty.const.DATE_LAST_ORDERED}</span>
					<span class="date_value">{$topAcc.last_purchased} <br><strong>{if $topAcc.last_purchased_days}({$topAcc.last_purchased_days}){else}{/if}</strong></span>					
				</div>
			</li>
			<li>
				<div class="order_count">
					<span class="date_title">{$smarty.const.ORDER_COUNT}</span>
					<span class="date_value">{$topAcc.total_orders}</span>		
				</div>
			</li>
			<li>
				<div class="total_count">
					<span class="date_title">{$smarty.const.TOTAL_ORDERED}</span>
					<span class="date_value">{$topAcc.total_sum}</span>		
				</div>
			</li>
            <li>
				<div class="credit_amount_ac">
					<span class="date_title">{$smarty.const.CREDIT_AMOUNT}</span>
					<span class="date_value">{$topAcc.credit_amount}</span>
                    {if $topAcc.count_credit_amount|string_format:"%d" > 0}
                    <div><a href="{tep_href_link('account/credit-amount','','SSL')}" class="view_history">{$smarty.const.TEXT_VIEW_HISTORY}</a></div>    
                    {/if}
				</div>
			</li>
            <li>
				<div class="points_earnt">
					<span class="date_title">{$smarty.const.TEXT_POINTS_EARNT}</span>
					<span class="date_value">0</span>
                    
				</div>
			</li>
		</ul>
		<ul class="account_info after">
			<li>
				<h4 class="icon-user"><strong>{$smarty.const.TEXT_MY_ACCOUNT}</strong><a class="edit" href="{$account_links['acount_edit']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
				<div class="account_block">
					<div class="acount_row">
						<div class="ai_title">{output_label const="TEXT_NAME"}</div>
						<div class="ai_value">{$customers.customers_firstname} {$customers.customers_lastname}</div>
					</div>
					<div class="acount_row">
						<div class="ai_title">{output_label const="ENTRY_DATE_OF_BIRTH"}</div>
						<div class="ai_value">{\common\helpers\Date::date_long($customers['customers_dob'])}</div>
					</div>
					<div class="acount_row">
						<div class="ai_title">{output_label const="ENTRY_EMAIL_ADDRESS"}</div>
						<div class="ai_value">{$customers.customers_email_address}</div>
					</div>
					<div class="acount_row">
						<div class="ai_title">{output_label const="ENTRY_TELEPHONE_NUMBER"}</div>
						<div class="ai_value">{$customers.customers_telephone}</div>
					</div>
				</div>
			</li>
			<li>
				<h4 class="icon-mail">{$smarty.const.EMAIL_NOTIFICATIONS_TITLE}</h4>
				<div class="info">
					<span class="info_span">{$smarty.const.TEXT_KEEP_UPTODATE_VIA_EMAIL}</span>
					<input type="checkbox" name="newsletter_general" value="{$customers.customers_id}" class="check-on-off"{if $customers.customers_newsletter == 1} checked{/if}>
				</div>
				<h4 class="icon-password">{$smarty.const.TEXT_MY_PASSWORD} <a class="edit" href="{$account_links['account_password']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
				<div class="account_block">
					<strong>{output_label const="TEXT_CURRENT_PASSWORD"} *****</strong>
				</div>
			</li>
			<li class="addr_book">
				<h4 class="icon-address">{$smarty.const.TEXT_ADDRESS_BOOK}<a class="edit" href="{$account_links['address_book_edit']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
				<div class="account_block">
					<div class="acount_row">
						<div class="ai_title">{$smarty.const.TEXT_PRIMARY}</div>
						<div class="ai_value">{$priamry_address}</div>
					</div>
				</div>
				<div class="address_book_center"><a class="btn" href="{$account_links['address_book']}">{$smarty.const.TEXT_VIEW_ALL_ADDRESSES}</a></div>
			</li>
		</ul>
        {if $ext = \common\helpers\Acl::checkExtension('CustomerLoyalty', 'allowed')}
            {$ext::renderAccountLoyality()}
        {/if}
	{if ENABLE_TRADE_FORM == 'True'}
	<div class="trade-form-area">{$smarty.const.WOULD_LIKE_TRADE} <a href="{tep_href_link('account/trade-form')}" class="btn-1">{$smarty.const.TRADE_FORM}</a></div>
	{/if}
    <h4 class="order-table-title">{$smarty.const.OVERVIEW_TITLE}</h4>
    <div class="contentBoxContents mobile_scroll">   
        <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
						<tr>
							<th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
							<th>{$smarty.const.TEXT_ORDER_DATE}</th>
							<th>{$smarty.const.TEXT_ORDER_SHIPPED_TO}</th>
							<th>{$smarty.const.TEXT_ORDER_PRODUCTS}</th>
							<th>{$smarty.const.TEXT_ORDER_TOTAL}</th>
							<th>{$smarty.const.TEXT_ORDER_STATUS}</th>
                                                        <th></th>
							<th></th>
						</tr>
            {foreach $account_orders as $ac_orders}
                <tr class="moduleRow {if $ac_orders.pay_link}moduleRowDue{/if}">
		    <td>
                        {$ac_orders.orders_id}
                        {if $ac_orders.pay_link}
                            <div class="not_fully_paid_td">{$smarty.const.TEXT_NOT_FULLY_PAID}</div>
                        {/if}
                    </td>
                    <td>{$ac_orders.date}</td>                    
                    <td>{$ac_orders.name}</td>
                    <td>{$ac_orders.products}</td>	
										<td>{$ac_orders.order_total}</td>
                    <td>{$ac_orders.orders_status_name}</td>    
                    <td>
                        {if $ac_orders.pay_link}
                            <a class="btn-1" href="{$ac_orders.pay_link}">{$smarty.const.PAY}</a>
                        {/if}
                    </td>
                    <td class="td-alignright">
                        {if $ac_orders.pay_link == ''}
                            {if $ac_orders.reorder_link}
                                <a class="view_link" {if $ac_orders.reorder_confirm}data-js-confirm="{$ac_orders.reorder_confirm|escape:'html'}"{/if} href="{$ac_orders.reorder_link}">{$smarty.const.SMALL_IMAGE_BUTTON_REORDER}</a>
                            {/if}
                        {/if}
                        <a class="view_link" href="{$ac_orders.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
		<div class="address_book_center bottom_adc"><a class="btn" href="{tep_href_link('account/history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_ORDERS}</a></div>
        {if count($subscriptions) > 0}
        <h4 class="order-table-title">{$smarty.const.BOX_HEADING_SUBSCRIPTIONS}</h4>
        <div class="mobile_scroll">
            <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
                <tr>
                    <th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
                    <th>{$smarty.const.TEXT_ORDER_DATE}</th>
                    <th>{$smarty.const.TEXT_ORDER_STATUS}</th>
                    <th></th>
                </tr>
                {foreach $subscriptions as $ac_subscriptions}
                    <tr class="moduleRow">
                        <td>{$ac_subscriptions.orders_id}</td>
                        <td>{$ac_subscriptions.date}</td>                    
                        <td>{$ac_subscriptions.orders_status_name}</td>                    
                        <td class="td-alignright">
                            <a class="view_link" href="{$ac_subscriptions.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
            <div class="address_book_center bottom_adc"><a class="btn" href="{tep_href_link('account/subscription-history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_SUBSCRIPTIONS}</a></div>
        {/if}
        {if count($products_wishlist) > 0}
        <h4 class="order-table-title order_wishlist">{$smarty.const.BOX_HEADING_CUSTOMER_WISHLIST}</h4>
        <div class="mobile_scroll">
        <table cellspacing="0" cellpadding="0" width="100%" class="account_wishlist">
            <tr>
                <th class="wish_remove_title">{$smarty.const.TEXT_REMOVE_CART}</th>
                <th class="wish_products_title">{$smarty.const.PRODUCTS}</th>
				<th></th>
				<th class="acc_wish_price">{$smarty.const.TEXT_PRICE}</th>
				<th></th>
            </tr>
            {foreach $products_wishlist as $pr_wishlist}
                <tr>
                    <td class="acc_wish_remove"><a class="remove-btn" href="{$pr_wishlist.remove_link}"></a></td>
                    <td class="acc_wish_img">
                        {if $pr_wishlist.status}
                            <a href="{$pr_wishlist.link}"><img src="{$pr_wishlist.image}" alt="{$pr_wishlist.name|escape:'html'}" title="{$pr_wishlist.name|escape:'html'}"></a>
                          {else}
                            <img src="{$pr_wishlist.image}" alt="{$pr_wishlist.name|escape:'html'}" title="{$pr_wishlist.name|escape:'html'}">
                          {/if}
                    </td>
                    <td class="acc_wish_name">
                        {if $pr_wishlist.status}
                        <a href="{$pr_wishlist.link}">{$pr_wishlist.name}</a>
                      {else}
                        {$pr_wishlist.name}
                      {/if}
                      <div class="attributes">
                        {foreach $pr_wishlist.attr as $attr}
                          <div class="">
                            <strong>{$attr.products_options_name}:</strong>
                            <span>{$attr.products_options_values_name}</span>
                          </div>
                        {/foreach}
                      </div>
											{if $pr_wishlist.is_bundle}
												{foreach $pr_wishlist.bundles_info as $bundle_product }
													<div class="bundle_product">
														{$bundle_product.x_name}
														{if $bundle_product.with_attr}
															<div class="attributes">
																{foreach $bundle_product.attr as $attr}
																	<div class="">
																		<strong>{$attr.products_options_name}:</strong>
																		<span>{$attr.products_options_values_name}</span>
																	</div>
																{/foreach}
															</div>
														{/if}
													</div>
												{/foreach}
											{/if}
                    </td>
                    <td class="acc_wish_price">
                        {$pr_wishlist.final_price_formatted}
                    </td>
                    <td class="td-alignright">
                        {if $pr_wishlist.status}
                        {if $pr_wishlist.oos}
                          {$smarty.const.TEXT_PRODUCT_OUT_STOCK}
                        {else}
                            <a class="view_link" href="{$pr_wishlist.move_in_cart}">{$smarty.const.BOX_WISHLIST_MOVE_TO_CART}</a>
                        {/if}
                      {else}
                        {$smarty.const.TEXT_PRODUCT_DISABLED}
                      {/if}
                      <a class="view_link popup" href="{tep_href_link('reviews/write', 'products_id='|cat:$pr_wishlist.id)}">{$smarty.const.IMAGE_BUTTON_WRITE_REVIEW}</a>
                    </td>
                </tr>
            {/foreach}
        </table>
        </div>
            <div class="address_book_center bottom_adc"><a class="btn" href="{tep_href_link('account/wishlist', '', 'SSL')}">{$smarty.const.BOX_INFORMATION_ALLPRODS}</a></div>
        {/if}
	  {if count($account_reviews)>0}
		<h4 class="order-table-title order_review_title">{$smarty.const.OVERVIEW_MY_REVIEW_TITLE}</h4>
		<div class="contentBoxContents">
			{if count($account_reviews)>0}
                <div class="mobile_scroll">
			<table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
				<tr>
					<th>{$smarty.const.TEXT_REVIEW_COLUMN_PRODUCT_NAME}</th>
					<th>{$smarty.const.TEXT_REVIEW_COLUMN_RATED}</th>
					<th>{$smarty.const.TEXT_REVIEW_COLUMN_DATE_ADDED}</th>
					<th>{$smarty.const.TEXT_REVIEW_COLUMN_STATUS}</th>
					<th></th>
				</tr>
				{foreach $account_reviews as $_review}
					<tr class="moduleRow">
						<td>
							{if $_review.products_link}
							<a href="{$_review.products_link}">{$_review.products_name}</a>
							{else}
							{$_review.products_name}
							{/if}
						</td>
						<td><span class="rating-{$_review.reviews_rating}"></span></td>
						<td>{$_review.date_added_str}</td>
						<td>{$_review.status_name}</td>
						<td class="td-alignright">
                            {if $_review.status_name == 'Approved'}
							<a class="view_link" href="{$_review.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
                            {/if}
						</td>
					</tr>
				{/foreach}
			</table>
                </div>
				{if $account_reviews_more_link}
                    <div class="bottom_adc badc_bottom"><a href="{$account_reviews_more_link}" class="btn">{$smarty.const.LINK_ACCOUNT_REVIEW_MORE}</a></div>
				{/if}
			{else}
				{$smarty.const.OVERVIEW_MY_REVIEW_NONE}
			{/if}
		</div>
	  {/if}
    {if $account_links['show_gv_block']}
    <div class="gift_account">
        <h4>{$smarty.const.BOX_HEADING_GIFT_VOUCHER}</h4>
        <div class="contentBoxContents ">
            <div><label>{$smarty.const.VOUCHER_BALANCE}</label> <b>{$account_links['gv_balance_formatted']}</b></div>
            {if $account_links['show_gv_send_block']}
            <p>{$account_links['gv_send_intro']}</p>
            <div><a href="{$account_links['gv_send_link']}">{$smarty.const.BOX_SEND_TO_FRIEND}</a></div>
            {/if}
        </div>
    </div>
    {/if}
    <div class="gift_account">{$account_links['coupon']}</div>

</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){

    if ( typeof alertMessage !== 'function' ) return;
    $('a[data-js-confirm]').on('click', function () {
      alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>');
      return false;
    });
    $('.popup').popUp({
        box: '<div class="popup-box-wrap popup-write"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>'
    });
    $('.view_history').popUp({
        box: '<div class="popup-box-wrap popup-credit-amount"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>'
    });
  });

function switchStatement(id, newsletter_general) {
    $.post("account/switch-newsletter", { 'id': id, 'newsletter_general' : newsletter_general}, function(data, status){
        if (status == "success") {
           $('main').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
}

tl('{Info::themeFile('/js/bootstrap-switch.js')}', function(){
  $(".check-on-off").bootstrapSwitch({
    onSwitchChange: function (element, arguments) {
      switchStatement(element.target.value, arguments);

      return true;
    },
    offText: '{TEXT_NO}',
    onText: '{TEXT_YES}'
  });
})
</script>