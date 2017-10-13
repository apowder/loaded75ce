{use class="Yii"}

{if $page_review}
  <h1>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h1>
{/if}

{if $message_review !=''}
  {$message_review}
{/if}
{if $reviews == ''}
  {if $link_write}
  <p style="float: right"><a href="{$link_write}" class="btn">{$smarty.const.WRITE_REVIEW}</a></p>
  {/if}
  <p>{$smarty.const.NO_PRODUCT_REVIEW}</p>
{else}
  {if $link_write}
  <p style="float: right"><a href="{$link_write}" class="btn">{$smarty.const.WRITE_REVIEW}</a></p>
  {/if}
  {if $rating}
  <p class="middle-rating" style="font-size: 20px; font-weight: bold">{$smarty.const.RATING} <span class="rating-{$rating}"></span> ({$count})</p>
  {/if}

<div class="reviews-list" style="background: #eee; padding: 20px">
  {foreach $reviews as $item}
    <div class="item">
      <div class="date">{$item.date}</div>

      <div class="review">{$item.reviews_text}</div>
      <div class="name">{$item.customers_name} <span class="rating-{$item.reviews_rating}"></span></div>
    </div>
  {/foreach}
</div>

<div class="pagination">

  <div class="left-area">
    {$counts}
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