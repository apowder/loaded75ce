{use class="Yii"}
{use class="frontend\design\Info"}
<div class="buttons" id="product-buttons">
  <span id="btn-cart"{if $product_has_attributes || !$stock_info.flags.add_to_cart } style="display:none;"{/if}>
    <button type="submit" class="btn-2 add-to-cart"{if $product_in_cart} style="display: none"{/if}>
      {$smarty.const.ADD_TO_CART}
    </button>
    <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class="btn-2 in-cart"{if !$product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_IN_YOUR_CART}</a>
  </span>
  <span class="btn-2" id="btn-cart-none"{if not $product_has_attributes || $stock_info.flags.add_to_cart} style="display:none;"{/if}>{$smarty.const.ADD_TO_CART}</span>
  <span class="btn" id="btn-notify"{if $product_has_attributes || !$stock_info.flags.notify_instock} style="display:none;"{/if}>{$smarty.const.NOTIFY_WHEN_STOCK}</span>
  <span class="btn" id="btn-rfq"{if $product_has_attributes || !$stock_info.flags.request_for_quote} style="display:none;"{/if}>{$smarty.const.BUTTON_REQUEST_FOR_QUOTE}</span>

{*
  <a href="{$compare_link}" id="btn-compare" class="btn btn-compare">{$smarty.const.ADD_TO_COMPARE}</a>
  <a href="{$wishlist_link}" id="btn-wishlist" class="btn btn-wishlist">{$smarty.const.ADD_TO_WISH_LIST}</a>
*}
  {if tep_session_is_registered('customer_id')}
  <span class="btn" id="add_to_whishlist">{$smarty.const.ADD_TO_WISH_LIST}</span>
  {/if}

</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    $('#btn-notify').on('click', function() {
      alertMessage('<div class="notify-form"><form action="" onsubmit="return ajax_notify_product();"><p><b>{$smarty.const.BACK_IN_STOCK}</b></p><p>{$smarty.const.TEXT_NAME}<br><input type="text" id="notify-name"></p><p>{$smarty.const.ENTRY_EMAIL_ADDRESS}<br><input type="text" id="notify-email"></p><p><button type="submit" class="btn">{$smarty.const.NOTIFY_ME}</button></p></form></div>');
    });
    $('#btn-rfq').on('click', function() {
      alertMessage('<div class="rfq-form"><form action="" onsubmit="return ajax_rfq_product();"><p><b>{$smarty.const.HEADING_REQUEST_FOR_QUOTE}</b></p>{if !$customer_is_logged}<p>{$smarty.const.TEXT_NAME}<br><input type="text" id="rfq-name"></p><p>{$smarty.const.ENTRY_EMAIL_ADDRESS}<br><input type="text" id="rfq-email"></p>{/if}<p>{$smarty.const.ENTRY_REQUEST_FOR_QUOTE_MESSAGE}<br><textarea id="rfq-message"></textarea></p><p><button type="submit" class="btn">{$smarty.const.BUTTON_REQUEST_FOR_QUOTE}</button></p></form></div>');
    });

    var product_form = $('#product-form');

    {assign var=after_add value=Info::themeSetting('after_add')}
    {if $after_add == 'popup'}
    product_form.popUp({
      box_class: 'cart-popup',
      opened: function(){
        $('.add-to-cart').hide();
        $('.in-cart').show()
      }
    });
    {elseif $after_add == 'animate'}

    {/if}


    $('#add_to_whishlist').on('click', function(){
      product_form.append('<input type="hidden" name="add_to_whishlist" value="1">');
      product_form.append('<input type="hidden" name="popup" value="1">');
      $.post(product_form.attr('action'), product_form.serializeArray(), function(d){
        alertMessage(d);		
				if($(d).filter("#error_wishlist_popup").length > 0){
				}else{
					$('.popup-box').addClass('cart-popup');
				}        
        $('.alert-message').removeClass('alert-message')
      });
      $('input[name="add_to_whishlist"]').remove()
    })
  });


  function ajax_notify_product() {
    if ($('#notify-name').val() < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
      alert('{sprintf($smarty.const.NAME_IS_TOO_SHORT, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}');
    } else { 
      var email = $("#notify-email").val();
      if (!isValidEmailAddress(email)) {
        alert('{$smarty.const.ENTER_VALID_EMAIL}');
      } else {
        var uprid = '&products_id=' + $('[name="products_id"]').val();
      if ($('input[name=inv_uprid]').length) {
        error = true;
        if ($('input[name=inv_uprid]:checked').length) {
          error = false;
          uprid += '&uprid=' + $('input[name=inv_uprid]:checked').val();
        }
      } else {
        var error = false;
        $('[name^="id\\["]').each(function(index) {
          uprid += '&id[' + this.name.match(/id\[([-\d]+)\]/)[1] + ']=' + $(this).val();
          if (!parseInt($(this).val())) {
            error = true;
          }
        });
      }
        if (error) {
          alert('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES}');
        } else {
          $.ajax({
            url: "{Yii::$app->urlManager->createUrl('catalog/product-notify')}",
            data: "name=" + $('#notify-name').val() + "&email=" + $('#notify-email').val() + uprid,
            success: function(msg) {
              $('.notify-form').replaceWith('<div class="notify-form">' + msg + '</div>');
            }
          });
        }
      }
    }
    return false;
  }
  function ajax_rfq_product() {
      var check_error = false;
      var post_data = $('form[name="cart_quantity"]').serializeArray();
      {if !$customer_is_logged}
      post_data.push( { name:'name', value:$('#rfq-name').val() } );
      post_data.push( { name:'email', value:$('#rfq-email').val() } );
      if ($('#rfq-name').val() < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
          check_error = true;
          alert('{sprintf($smarty.const.NAME_IS_TOO_SHORT, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)|escape:'javascript'}');
      }
      if (!isValidEmailAddress($("#rfq-email").val())) {
          check_error = true;
          alert('{$smarty.const.ENTER_VALID_EMAIL|escape:'javascript'}');
      }
      {/if}
      post_data.push( { name:'message', value:$('#rfq-message').val() } );
      if ($("#rfq-message").val().length==0) {
          check_error = true;
          alert('{$smarty.const.REQUEST_MESSAGE_IS_TOO_SHORT|escape:'javascript'}');
      }
      var error = false;
      $(post_data).each(function(idx, param) {
          if ( param.name.indexOf('id[')!==0 ) return;
          if (!parseInt(param.value)) {
              error = true;
          }
      });
      if (error) {
          check_error = true;
          alert('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}');
      }
      if ( !check_error ) {
          $.ajax({
              url: "{Yii::$app->urlManager->createUrl('catalog/product-request-for-quote')}",
              data: post_data,
              type: 'POST',
              success: function(msg) {
                  $('.rfq-form').replaceWith('<div class="rfq-form">' + msg + '</div>');
              }
          });
      }

    return false;
  }

{literal}
  function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.) {2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
  }
{/literal}
</script>