{use class="Yii"}
{use class="frontend\design\Info"}
<div id="product-bundle">
<h2 class="bundle_title">{$smarty.const.TEXT_BUNDLE_PRODUCTS_NEW}</h2>
	<div class="bundle_row after">
  {foreach $products as $product name=bundles}
	 {if $smarty.foreach.bundles.index % 2 == 0 && $smarty.foreach.bundles.index != 0}
	</div><div class="bundle_row after">
	{/if}
    <div class="bundle_item">
      <div class="bundle_image"><a href="{$product.product_link}"><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}"></a></div>      
      <div class="right-area-bundle">
				<div class="bundle_name">
        <a href="{$product.product_link}">{$product.products_name}</a>
        <div class="bundle_attributes after">
          {foreach $product.attributes_array as $item}
            <div>
              <select name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$product.products_name|escape:'html'} - {$item.title}" onchange="update_bundle_attributes(this.form);">
                <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                {foreach $item.options as $option}
                  <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                {/foreach}
              </select>
            </div>
          {/foreach}
        </div>
        <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
      </div>
        <div class="bundle_qty">
          {$product.num_product} {$smarty.const.TEXT_ITEMS}
        </div>
        <div class="bundle_price">
          {if $product.price}
            <span class="current">{$product.price}</span>
          {else}
            <span class="old">{$product.price_old}</span>
            <span class="specials">{$product.price_special}</span>
          {/if}
        </div>
      </div>
    </div>
  {/foreach}

</div>
{if !Yii::$app->request->get('list_b2b')}
<script type="text/javascript">
{if not $isAjax}
  tl(function() {
    update_bundle_attributes(document.forms['cart_quantity']);
  });
{/if}
  function update_bundle_attributes(theForm) {
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-bundle')}", $(theForm).serialize(), function(data, status) {
      if (status == "success") {
//        $('#product-price-old').html(data.product_price);
//        $('#product-price-current').html(data.product_price);
//        $('#product-price-special').html(data.special_price);
        $('#product-bundle').replaceWith(data.product_bundle);
        if (data.product_valid > 0) {
            if (data.product_in_cart) {
                $('.add-to-cart').hide();
                $('.in-cart').show()
            } else {
                $('.add-to-cart').show();
                $('.in-cart').hide()
            }
            if ( data.stock_indicator ) {
              var stock_data = data.stock_indicator;
              if ( stock_data.add_to_cart ) {
                  $('#btn-cart').show();
                  $('.qty-input').show();
                  //$('.add-to-cart').show();
                  if (data.product_in_cart) {
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                  }
                  $('#btn-cart-none:visible').hide();
              } else {
                  $('#btn-cart').hide();
                  $('.qty-input').hide();
                  //$('.add-to-cart').hide();
                  if (data.product_in_cart) {
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                  }
                  $('#btn-cart-none:hidden').show();
              }
              if ( stock_data.request_for_quote ) {
                  $('#btn-rfq').show();
                  $('#btn-cart-none:visible').hide();
              } else {
                  $('#btn-rfq').hide();
              }
              if ( stock_data.notify_instock ) {
                  $('#btn-notify').show();
              } else {
                  $('#btn-notify').hide();
              }
              if ( stock_data.quantity_max > 0 ) {
                  var qty = $('.qty-inp');
                  $.each(qty, function(i, e) {
                      $(e).attr('data-max', stock_data.quantity_max).trigger('changeSettings');
                      if ($(e).val() > stock_data.quantity_max) {
                          $(e).val(stock_data.quantity_max);
                      }
                  });
              }
          } else {
              $('#btn-cart').hide();
              $('#btn-cart-none').show();
              $('#btn-notify').hide();
              $('.qty-input').hide();
          }
        } else {
            $('.qty-input').hide();
            $('#btn-cart').hide();
            $('#btn-cart').hide();
            $('#btn-cart-none').show();
            $('#btn-notify').hide();
        }
        if ( typeof data.stock_indicator != 'undefined' ) {
            $('.js-stock').html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');
        }
      }
    },'json');
  }
</script>
{/if}
</div>
