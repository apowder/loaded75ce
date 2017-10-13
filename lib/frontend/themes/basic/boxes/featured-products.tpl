{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
<h3>{$smarty.const.FEATURED_PRODUCTS}</h3>
<div class="products-box columns-{$settings[0].col_in_row}{if $settings[0].view_as == 'carousel'} products-carousel carousel-3{/if}">
{IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['only_column'=>true, 'products' => $products, 'settings' => $settings, 'languages_id' => $languages_id]])}
</div>
<div class="view-all"><a href="{tep_href_link('catalog/featured_products')}" class="btn">{$smarty.const.VIEW_ALL_FEATURED_PRODUCTS}</a></div>

{if $settings[0].view_as == 'carousel'}
  <script type="text/javascript">
    tl('{Info::themeFile('/js/slick.min.js')}', function(){

      var carousel = $('.carousel-3');
      var tabs = carousel.parents('.tabs');
      tabs.find('> .block').show();
      var show = {$settings[0].col_in_row};
      var width = carousel.width();
      if (!show) show = 4;
      if (width < 800 && show > 3) show = 3;
      if (width < 600 && show > 2) show = 2;
      if (width < 400 && show > 1) show = 1;
      $('.carousel-3 > div').slick({
        slidesToShow: show,
        slidesToScroll: show,
        infinite: false
      });
      setTimeout(function(){ tabs.trigger('tabHide') }, 100)
    });
  </script>
{/if}