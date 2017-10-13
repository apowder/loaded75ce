{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
{$list_type = Info::listType($settings[0])}
{$list_type_file = 'boxes/listing-product/'|cat:$list_type|cat:'.tpl'}

<div class="products-listing
{if !$only_column && Info::get_gl() == 'list'} listing-list{/if}
{if $settings[0].col_in_row} cols-{$settings[0].col_in_row}{/if}
{if $settings[0].products_align} align-{$settings[0].products_align}{/if}
 list-{$list_type}">

  {$page_block = Info::pageBlock()}

  {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products')}
    <form action="{tep_href_link(FILENAME_SHOPPING_CART, 'action=add_all')}" method="post">
      <script type="text/javascript">

        function update_attributes_list(item) {

          var request = {
            products_id: $('input[name="products_id[]"]', item).val()
          };
          var id = '?';
          $('select', item).each(function(i){
            if (i != 0) id += '&';
            id += $(this).attr('name').replace('id[' + request.products_id + ']', 'id') + '=' + $(this).val();
          });
          request = $.extend({
            list_b2b: 1
          }, request);
          request = $.extend({
            qty: 1
          }, request);

          $.get('{Yii::$app->urlManager->createUrl('catalog/product-attributes')}' + id, request, function(data) {
            $('.old', item).html(data.product_price);
            $('.current', item).html(data.product_price);
            $('.specials', item).html(data.special_price);
            $('.attributes', item).html(data.product_attributes);
            if (data.product_valid > 0) {
              {if $smarty.const.STOCK_CHECK != 'false'}
                if ( data.stock_indicator ) {
                    if (typeof data.stock_indicator.quantity_max !== 'undefined') {
                        var $qty = $('input[name="qty[]"]', item);
                        $qty.attr('data-max', data.stock_indicator.quantity_max).trigger('changeSettings');
                    }
                    if (data.stock_indicator.can_add_to_cart){
                        $('.qty-input', item).removeClass('hidden');
                    }else{
                        $('.qty-input', item).addClass('hidden');
                        $('input[name="qty[]"]', item).val('0');
                    }
                }
              {else}
                if (data.product_qty > 0) {
                    $('.qty-input', item).removeClass('hidden');
                } else {
                  {if $smarty.const.STOCK_CHECK != 'false'}
                    $('.qty-input', item).addClass('hidden');
                    $('input[name="qty[]"]', item).val('0');
                  {/if}
                }
              {/if}
            } else {
              {if $smarty.const.STOCK_CHECK != 'false'}
              $('.qty-input', item).addClass('hidden');
              $('input[name="qty[]"]', item).val('0');
              {/if}
            }
            if ( typeof data.stock_indicator !== 'undefined' ) {
                $('.js-stock', item).html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');
            }
            $('select', item).each(function(){
              $(this).attr('name', $(this).attr('name').replace('id', 'id[' + request.products_id + ']'))
            });
            $('.attributes select', item).on('change', function(){
              update_attributes_list(item)
            })
          },'json');
        }
      </script>
  {/if}

{foreach $products as $product}{trim(IncludeTpl::widget(['file' => $list_type_file, 'params' => ['product' => $product, 'settings' => $settings, 'languages_id' => $languages_id]]))}{/foreach}

  {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products') && !GROUPS_DISABLE_CHECKOUT}
      <button type="submit" class="btn-2" id="add_all">{$smarty.const.ADD_TO_CART}</button>
    </form>
  {/if}
</div>

{if !$settings[0].list_demo}
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    setTimeout(function(){
      $('.products-listing').inRow(['.image', '.name', '.price', '.description-2', '.buttons', '.qty-input', '.buy-button', '.add-height'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
    }, 500);


    {if $settings[0].fbl}

    var page = 2;
    var key = true;
    var count = 0;
    var container = { };
    $(window).on('scroll', function(){
      var products_listing = $('.products-listing');
      if (products_listing.offset().top + products_listing.height() - $(window).scrollTop() < $(window).height()){

        {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products')}
        count = $('.products-listing > form > div').length;
        container = $('.products-listing > form');
        {else}
        count = $('.products-listing > div').length;
        container = $('.products-listing');
        {/if}
        if (key && count < {$params.number_of_rows}){
          key = false;
          $.get('{$params.url}', { fbl: 1, page: page }, function(d){
            page++;
            key = true;
            container.append(d);
            $('.products-listing').inRow(['.image', '.name', '.price', '.description-2', '.buttons', '.qty-input', '.buy-button', '.add-height'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
          })
        }
      }
    });

    {/if}

    $('.form-whishlist').popUp({
      box_class: 'cart-popup'
    });
    $('input.qty-inp').quantity();

    {assign var=after_add value=Info::themeSetting('after_add')}
    {if $after_add == 'popup'}
      $('.btn-buy, .form-buy').popUp({
        box_class: 'cart-popup',
        opened: function(obj){
          obj.closest('.item').find('.add-to-cart').hide();
          obj.closest('.item').find('.in-cart').show()
        }
      });
    {elseif $after_add == 'animate'}

    {/if}
  });

</script>
{/if}