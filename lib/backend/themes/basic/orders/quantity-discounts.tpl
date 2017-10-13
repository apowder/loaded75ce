<div class="w-line-row w-line-row-qtd">
    <div class="edp-line">
        <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
        <div class="quantity-discounts">
          <div class="quantity-discounts-content">
            {*<div class="item" data-id="0" data-min="1" data-max="">
              <span class="count"></span>
              <span class="price"></span>
            </div>*}
            {foreach $discounts as $key=>$discount}
            <div class="item" data-id="{$key+1}" data-min="" data-max="">
              <span class="count">{$discount.count}</span>
              <span class="price">{$discount.price}</span>
            </div>
            {/foreach}
          </div>
        </div>
    </div>
</div>