<div class="reviews">
<h3>{$smarty.const.REVIEWS}</h3>

  <div class="reviews-list">
  {foreach $items as $item}
    <div class="item">
      <div class="review">{$item.reviews_text|truncate:60:"..."}</div>
      <div class="name">{$item.customers_name} <span class="rating-{$item.reviews_rating}"></span></div>
    </div>
  {/foreach}
</div>
</div>
