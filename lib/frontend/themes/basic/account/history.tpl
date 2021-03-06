{use class="frontend\design\Info"}
<div class="account_history">
<h1>{$smarty.const.HEADING_TITLE}</h1>
{if $orders_total > 0}
{if $number_of_rows > 0}
<div class="pagination">
  <div class="left-area">
    {$history_count}
  </div>
<div class="right-area">
    {if isset($links.prev_page.link)}
      <a href="{$links.prev_page.link}" class="prev"></a>
    {else}
      <span class="prev"></span>
    {/if}
    {if isset($links.prev_pages.link)}
      <a href="{$links.prev_pages.link}" title="{$links.prev_pages.title}">...</a>
    {/if}

    {foreach $links.page_number as $page}
      {if isset($page.link)}
        <a href="{$page.link}">{$page.title}</a>
      {else}
        <span class="active">{$page.title}</span>
      {/if}
    {/foreach}

    {if isset($links.next_pages.link)}
      <a href="{$links.next_page.link}" title="{$links.next_page.title}">...</a>
    {/if}
    {if isset($links.next_page.link)}
      <a href="{$links.next_page.link}" class="next"></a>
    {else}
      <span class="next"></span>
    {/if}
  </div>
 </div>
{/if}
<div class="main">
      <table class="order-info">
			<tr>
				<th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
				<th>{$smarty.const.TEXT_ORDER_DATE}</th>
				<th>{$smarty.const.TEXT_ORDER_SHIPPED_TO}</th>
				<th>{$smarty.const.TEXT_ORDER_PRODUCTS}</th>
				<th>{$smarty.const.TEXT_ORDER_TOTAL}</th>
				<th>{$smarty.const.TEXT_ORDER_STATUS}</th>
				<th></th>
			</tr>
{foreach $history_array as $hisarray}
      <tr>
				<td>{$hisarray.orders_id}</td>
				<td>{$hisarray.date}</td>
				<td>{\common\helpers\Output::output_string_protected($hisarray.name)}</td>
				<td>{$hisarray.count}</td>
				<td>{strip_tags($hisarray.order_total)}</td>
				<td>{$hisarray.orders_status_name}</td>
				<td>
          {if $hisarray.reorder_link}
            <a class="view_link" {if $hisarray.reorder_confirm}data-js-confirm="{$hisarray.reorder_confirm|escape:'html'}"{/if} href="{$hisarray.reorder_link}">{$smarty.const.SMALL_IMAGE_BUTTON_REORDER}</a>
          {/if}
          <a class="history_link" href="{$hisarray.link}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
        </td>
      </tr>      
{/foreach}
</table>
    </div>
  <script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}', function(){

      if ( typeof alertMessage !== 'function' ) return;
      $('a[data-js-confirm]').on('click', function () {
        alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>');
        return false;
      });

    })
  </script>
{else}
<div class="noItems">{$smarty.const.TEXT_NO_PURCHASES}</div>
{/if}
<div class="buttonBox"><div class="button2">{$account_back}</div></div>
</div>