{use class="Yii"}
<h3>{$smarty.const.REVIEWS} ({$number_of_rows})</h3>
<div class="product-reviews">



  <div class="reviews-list">
{foreach $reviews as $item}
  <div class="item" itemprop="review" itemscope itemtype="http://schema.org/Review">
    <div class="date"><meta itemprop="datePublished" content="{$item.date_schema}">{$item.date}</div>

    <div class="text" itemprop="description">{$item.reviews_text}</div>
    <div class="name"><span itemprop="author">{$item.customers_name}</span> <span class="rating-{$item.reviews_rating}"></span></div>
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

</div>

<script type="text/javascript">
  tl(function(){

    var product_reviews = $('.product-reviews');
    $.get('{$reviews_link}', function(d){
      product_reviews.html(d)
    });
    product_reviews.on('click', 'a', function(){
      $.get($(this).attr('href'), function(d){
        product_reviews.html(d)
      });
      return false
    })

  })
</script>