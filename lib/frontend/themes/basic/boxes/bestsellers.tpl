{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
<div class="bestsellers">
<h3>{$smarty.const.BEST_SELLERS}</h3>

  {if !$settings[0].view_as}
    <div class="bestsellers-list">
    {foreach $products as $product}
      <div class="item"><span>{counter}</span><a href="{$product.link}">{$product.products_name}</a></div>
    {/foreach}
    </div>
  {else}

    <div class="products-box columns-{$settings[0].col_in_row}{if $settings[0].view_as == 'carousel'} products-carousel carousel-1{/if}">
      {IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['only_column'=>true, 'products' => $products, 'settings' => $settings, 'languages_id' => $languages_id]])}
    </div>
    <div class="view-all"><a href="{tep_href_link('catalog/products_new')}" class="btn">{$smarty.const.VIEW_ALL_NEW_PRODUCTS}</a></div>

    {if $settings[0].view_as == 'carousel'}
      <script type="text/javascript">
        tl('{Info::themeFile('/js/slick.min.js')}', function(){

          var carousel = $('.carousel-7');
          var tabs = carousel.parents('.tabs');
          tabs.find('> .block').show();
          var show = {$settings[0].col_in_row};
          var width = carousel.width();
          if (!show) show = 4;
          if (width < 800 && show > 3) show = 3;
          if (width < 600 && show > 2) show = 2;
          if (width < 400 && show > 1) show = 1;
          $('.carousel-7 > div').slick({
            slidesToShow: show,
            slidesToScroll: show,
            infinite: false
          });
          setTimeout(function(){ tabs.trigger('tabHide') }, 100)

        })
      </script>
    {/if}
  {/if}
</div>
