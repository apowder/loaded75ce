{use class="frontend\design\Info"}
<div class="" style="padding: 30px 0; width: 400px; clear: both">
  <div class="cart-box"{Info::dataClass('.cart-box')}>
    <a{Info::dataClass('.cart-box > a')}>
        <span style="overflow: visible">
          <strong{Info::dataClass('.cart-box > a > span > strong')}>{$smarty.const.TEXT_HEADING_SHOPPING_CART}</strong>
        </span>
    </a>
    <div class="cart-content"{Info::dataClass('.cart-content')}>
      <a class="item"{Info::dataClass('.cart-content a.item')}>
        <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>

        <span class="name"{Info::dataClass('.cart-content .name')}><span class="qty">1</span>Product 1</span>
        <span class="price"{Info::dataClass('.cart-content .price')}>£46.98</span>
      </a>
      <a class="item"{Info::dataClass('.cart-content a.item')}>
        <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>
        <span class="name"{Info::dataClass('.cart-content .name')}><span class="qty">1</span>Product 2</span>
        <span class="price"{Info::dataClass('.cart-content .price')}>£58.21</span>
      </a>
      <div class="cart-total"{Info::dataClass('.cart-content .cart-total')}>{$smarty.const.SUB_TOTAL} £105.19</div>
      <div class="buttons"{Info::dataClass('.cart-content .buttons')}>
        <div class="left-buttons"><a class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
        <div class="right-buttons"><a class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('.products-listing').inRow(['.image', '.name', '.price'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
  })
</script>