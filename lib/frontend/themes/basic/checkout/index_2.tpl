{use class="Yii"}
{use class="frontend\design\boxes\checkout\ShippingList"}
{use class="frontend\design\Info"}

      {assign var=re1 value='.{'}
      {assign var=re2 value='}'}
{if $payment_error && $payment_error.title }
  <p><strong>{$payment_error.title}</strong><br>{$payment_error.error}</p>
{/if}
{if $message != ''}
  <p>{$message}</p>
{/if}

  <div class="multi-page-checkout">

<form id="frmCheckout" name="one_page_checkout" action="{$checkout_process_link}" method="post">
    <div class="checkout-step active" id="shipping-step">
      <div class="checkout-heading">
        <span class="edit">Edit</span>
        <span class="count">1</span>
        Delivery details
      </div>
        <div class="checkout-content">
            
  {if $is_shipping}      
        <div class="col-left">
          {if $is_shipping}
              {include 'index/shipping-address.tpl'}
          {/if}
        </div>
        <div class="col-right">
          <div class="shipping-method" id="shipping_method">
            {include file="shipping.tpl"}
          </div>
        </div>
  {/if}
  
<div class="col-left">
      {include file="index/contact-info.tpl"}
</div>

            <div class="col-right">
              <div class="contact-info form-inputs">
                <div class="heading-4">{$smarty.const.COMMENTS_ABOUT_ORDER}</div>
                <div class="col-full">
                  <label>
                    <span>{field_label const="TYPE_ADDITIONAL_INFO" required_text=""}</span>
                    <textarea name="comments" id="" cols="30" rows="5">{$comments|escape:'html'}</textarea>
                  </label>
                </div>
              </div>
            </div>
                  
                  
                  
            <div class="buttons">
              <div class="right-buttons">
                <span class="continue-text">{$smarty.const.CONTINUE_CHECKOUT_PROCEDURE}</span>
                <span class="btn-2 btn-next">{$smarty.const.CONTINUE}</span>
              </div>
            </div>
        </div>
    </div>
    <div class="checkout-step" id="payment-step">
      <div class="checkout-heading">
        <span class="edit">Edit</span>
        <span class="count">2</span>
        Payment details
      </div>
      <div class="checkout-content" style="display: none">

        <div class="col-left">
          {include file="index/billing-address.tpl"}
        </div>
        <div class="col-right">
          {include file="index/payment-method.tpl"}
        </div>
        
        
        <div class="buttons">
          <div class="right-buttons">
            <span class="continue-text">{$smarty.const.CONTINUE_CHECKOUT_PROCEDURE}</span>
            <span class="btn-2 btn-next">{$smarty.const.CONTINUE}</span>
          </div>
        </div>
  
      </div>
    </div>
</form>
    <div class="checkout-step" id="confirmation-step">
      <div class="checkout-heading"><span class="count">3</span> Confirmation</div>
      <div class="checkout-content" style="display: none">

      </div>
    </div>
  
  
  </div>
  
  
            <div class="checkout-products">
              {use class="frontend\design\boxes\cart\Products"}
              <h1>{$smarty.const.TEXT_ITEM_IN_YOUR_CART}</h1>
              {Products::widget(['type'=> 2])}
              <div class="price-box" id="order_totals">
                {include file="totals.tpl"}
              </div>
            </div>











{$payment_javascript_validation}

  <script type="text/javascript">
    function checkCountryVatState() {
        var selected = $('select[name="country"]').val();
        if (selected == {$smarty.const.STORE_COUNTRY}) {
            $('.company_vat_box').hide();
        } else {
            $('.company_vat_box').show();
        }
    }
        
    var submitter = 0;

    tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        
        var timeSlide = 500;
        var shippingStep = $('#shipping-step');
        var paymentStep = $('#payment-step');
        var confirmationStep = $('#confirmation-step');        
        var confirmationStepContent = $('.checkout-content', confirmationStep);
        $('.btn-next', shippingStep).on('click', function(){
            $('input, select', shippingStep).trigger('check');
            if ($('.required-error', shippingStep).length == 0) {
                shippingStep.removeClass('active').addClass('past');
                $('.checkout-content', shippingStep).slideUp(timeSlide);
                paymentStep.addClass('active');
                $('.checkout-content', paymentStep).slideDown(timeSlide);
                $("html, body").stop().animate({ scrollTop: shippingStep.offset().top}, timeSlide);
            }
        });
        $('.checkout-heading .edit', shippingStep).on('click', function(){
            shippingStep.addClass('active').removeClass('past');
            paymentStep.removeClass('past');
            $('.checkout-content', shippingStep).slideDown(400);
            paymentStep.removeClass('active');
            confirmationStep.removeClass('active');
            $('.checkout-content', paymentStep).slideUp(timeSlide);
            confirmationStepContent.slideUp(timeSlide);
            $('.checkout-products').slideDown(timeSlide);
        });

        var confirmationResponse = function(d, a, xhr){
            var ct = xhr.getResponseHeader("content-type") || "";
            if (ct.indexOf('html') > -1) {
                confirmationStepContent.stop();
                confirmationStepContent.css('height', confirmationStepContent.height());
                confirmationStepContent.html(d);
                confirmationStepContent
                        .animate({ height: $('> div', confirmationStepContent).height()+40}, timeSlide)
                        .removeAttr('style');
                $('.checkout-content', paymentStep).slideUp(timeSlide);
            }
            if (ct.indexOf('json') > -1) {
                d = $.parseJSON(d);
                if (d.message.length > 0 || d.payment_error.length > 0) {
                    setTimeout(function(){
                        if (d.message.length > 0) {
                            alertMessage(d.message);
                        }
                        if (d.payment_error.length > 0) {
                            var paymentError = '<div class="messageBox"><strong>' + 
                                    d.payment_error.title + 
                                    '</strong><br>' + 
                                    d.payment_error.error + '</div>';
                            alertMessage(paymentError);
                        }
                    }, timeSlide);
                    if (
                        d.error_name == 'shipping_gender' ||
                        d.error_name == 'ship_firstname' ||
                        d.error_name == 'ship_postcode' ||
                        d.error_name == 'ship_street_address_line1' ||
                        d.error_name == 'ship_street_address_line2' ||
                        d.error_name == 'ship_state' ||
                        d.error_name == 'ship_country' ||
                        d.error_name == 'email_address'
                    ) {
                        $('.checkout-heading .edit', shippingStep).trigger('click');
                    } else {
                        $('.checkout-heading .edit', paymentStep).trigger('click');
                    }
                }
            }
        };

        $('.btn-next', paymentStep).on('click', function(){
            $('input, select', paymentStep).trigger('check');
            $('#payment-step input, #payment-step select', paymentStep).trigger('check');
            if ($('.required-error', paymentStep).length == 0) {
                confirmationStepContent.html('<div class="preloader"></div>');
                paymentStep.removeClass('active').addClass('past');
                confirmationStep.addClass('active');
                confirmationStepContent.slideDown(timeSlide);
                $('.checkout-products').slideUp(timeSlide);
                $("html, body").stop().animate({ scrollTop: shippingStep.offset().top}, timeSlide);
                var form = paymentStep.closest('form');                
                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serializeArray(),
                    success: confirmationResponse
                });
            }
        });
        $('.checkout-heading .edit', paymentStep).on('click', function(){
            paymentStep.addClass('active').removeClass('past');
            $('.checkout-content', paymentStep).slideDown(400);
            confirmationStep.removeClass('active');
            confirmationStepContent.slideUp(timeSlide);
            $('.checkout-products').slideDown(timeSlide);
        });
        
        $('input', shippingStep).validate({ onlyCheck: true});
        $('input', paymentStep).validate({ onlyCheck: true});

      var addresses = {$addresses_json};

      $(function(){
        var shipping_address = $('#shipping-address');
        var shipping_addresses = $('#shipping-addresses');
        var billing_address = $('#billing-address');
        var billing_addresses = $('#billing-addresses');


        var ship_as_bill = false;
        if ($('input[name="ship_as_bill"]:checked').val()){
          ship_as_bill = true;
        }else{
          $('.hide-billing-address').hide();
        }

        $('.item-radio input').radioHolder({ holder: '.item-radio'});

        $('.addresses input').radioHolder({ holder: '.address-item'});

        $('.genders-title input').radioHolder();
        $('.shipping-method input').radioHolder();


        $('#as-shipping').bootstrapSwitch({
          offText: '{$smarty.const.TEXT_NO}',
          onText: '{$smarty.const.TEXT_YES}',
          onSwitchChange: function (a, key) {
            if(key){
              $('.hide-billing-address').show();
              ship_as_bill = true;
            } else {
              $('.hide-billing-address').hide();
              ship_as_bill = false;
            }
            $('input:checked', shipping_addresses).each(same_addresses);
            $('input, select', shipping_address).each(same_address)
          }
        });

        var change_address = function(event){
          var address = addresses[$(this).val()];
          $('input, select', event.data.type_address).each(function(){
            var name = $(this).attr('name').replace('ship_', '').replace('shipping_', '');
            if ($(this).attr('type') == 'radio'){
              if (address == undefined) {
                $(this).prop('checked', false)
              }else{
                if ($(this).val() == address[name]){
                  $(this).prop('checked', true)
                } else {
                  $(this).prop('checked', false)
                }
              }
            } else {
              if (address != undefined) {
                $(this).val(address[name])
              } else if (name == 'country') {
                $(this).val('{$smarty.const.STORE_COUNTRY}')
              } else {
                $(this).val('')
              }
            }
            $(this).trigger('change')
          })
        };

        var same_addresses = function(){
          if (ship_as_bill){
            var val = $(this).val();
            $('select', billing_addresses).val(val);
            $('input', billing_addresses).each(function(){

              if ($(this).attr('value') == val){
                $(this).prop('checked', true).trigger('change')
              } else {
                $(this).prop('checked', false)
              }
            });
          }
        };

        var same_address = function(){
          if (ship_as_bill) {
            var val = $(this).val();
            var name = $(this).attr('name').replace('ship_', '').replace('shipping_', '');
            if ( this.type && this.type == 'radio' ) {
		if ($(this).prop('checked')) {
              	    $('input[name="' + name + '"]', billing_address).filter('[value="'+val+'"]').trigger('click');
		}
            }else {
              $('input[name="' + name + '"], select[name="' + name + '"]', billing_address).val(val);
            }
          }
        };

        $('input', shipping_addresses).on('change', { type_address: shipping_address}, change_address);

        $('input', billing_addresses).on('change', { type_address: billing_address}, change_address);

        $('input', shipping_addresses).on('change', same_addresses);

        $('input[type="radio"]', shipping_address).on('click', same_address);
        $('input, select', shipping_address).filter(function() {
          return !this.name.match(/(postcode|state|country)$/);
        }).on('change keyup', same_address);

        var delay = (function(){
          var timer = 0;
          return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
          };
        })();

        var $frmCheckout = $('#frmCheckout');
        $frmCheckout.append('<input type="hidden" name="xwidth" value="'+screen.width+'">').append('<input type="hidden" name="xheight" value="'+screen.height+'">');

        //$frmCheckout.find('input, select').validate();

        if ( typeof window.check_form == 'function' ) {
          $frmCheckout.on('submit',function(){
              return false;
            //return window.check_form();
          });
        }
        
        checkCountryVatState();
        
        $(document).on('change keyup',function(event) {
          if ( event.target.name && event.target.name.match(/(postcode|state|country|company_vat)$/) ) {
            if ( event.target.name.indexOf('ship')===0 ) {
              same_address.apply(event.target);
            }
            $frmCheckout.trigger('checkout_data_changed');
          }
        });
//        $('input, select',$frmCheckout).filter(function() {
//          return this.name.match(/(postcode|state|country)$/);
//        }).on('change',function () {
//          $frmCheckout.trigger('checkout_data_changed');
//        });
        $('#shipping_method').on('click',function(e){
          if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='shipping' ) {
            $frmCheckout.trigger('checkout_data_changed');
          }
        });
				$('#payment_method').on('click',function(e){
          if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='payment' ) {
            $frmCheckout.trigger('checkout_data_changed');
          }
        });
        $frmCheckout.on('checkout_data_changed', function(event, extra_post) {
          var $xhr,
                  $post_data = $frmCheckout.serializeArray();
          if ( extra_post && $.isArray(extra_post) ) {
            for(var _i=0; _i<extra_post.length; _i++){
              $post_data.push(extra_post[_i]);
            }
          }
          delay(function(){
            if($xhr && $xhr.readyState != 4) {
              $xhr.abort();
            }
            $xhr = $.ajax({
              url:'{$ajax_server_url}',
              data: $post_data,
              method:'post',
              dataType:'json',
              success: function(data) {
                if ( data.replace ) {
                  if ( data.replace.shipping_method ) {
                    $('#shipping_method').html(data.replace.shipping_method);
                    $('#shipping_method input').radioHolder();
                  }
                  if ( data.replace.order_totals ) {
                    $('#order_totals').html(data.replace.order_totals);
                  }
                  if ( data.replace.company_vat_status ) {
                    $('#customer_company_vat_status').html(data.replace.company_vat_status);
                  }
                }
                if (data.payment_allowed){
                  var $payments = $('#payment_method').find('.payment_item');
                  $payments.each(function(){
                    var $payment_item = $(this);
                    var get_payment_class = this.className.match(/payment_class_([^\s]+)/);
                    if ( get_payment_class ){
                      if ($.inArray(get_payment_class[1],data.payment_allowed)===-1){
                        if ($payment_item.is(':visible')){
                          $payment_item.hide();
                        }
                      }else{
                        if ($payment_item.not(':visible')){
                          $payment_item.show();
                        }
                      }
                    }
                  });
                }
/*
                if ( data.zones ) {
                  if (data.zones.state){
                    var $current_state = $('input[name="state"], select[name="state"]',$frmCheckout);
                    var $new_state = $(data.zones.state);
                    if ($current_state.length>0 && $new_state.length>0 && $new_state[0].tagName!=$current_state[0].tagName){
                      $current_state.replaceWith($new_state);
                      //$new_state.validate();
                    }

                  }
                  if (data.zones.ship_state){
                    var $current_ship_state = $('input[name="ship_state"], select[name="ship_state"]',$frmCheckout);
                    var $new_ship_state = $(data.zones.ship_state);
                    if ($current_ship_state.length>0 && $new_ship_state.length>0 && $new_ship_state[0].tagName!=$current_ship_state[0].tagName ){
                      $current_ship_state.replaceWith($new_ship_state);
                      //$new_ship_state.validate();
                    }
                  }
                }
*/
                var credit_modules_message = '';
                if ( data.credit_modules && data.credit_modules.message ) {
                  credit_modules_message = data.credit_modules.message;
                }
                $('#credit_modules_message').html(credit_modules_message);
                
                checkCountryVatState();
              }
            });
          }, 300 );
        });
        $frmCheckout.trigger('checkout_data_changed');

        $('.js_cot_gv_dep').on('switch_update',function(event,state){
          if (state){
            $(this).removeClass('semi_disabled');
            $('.js_cot_gv_dep').find('input, button').removeAttr('disabled').removeAttr('readonly');
          }else{
            $(this).addClass('semi_disabled');
            $('.js_cot_gv_dep').find('input, button').attr({
              disabled:'disabled',
              readonly:'readonly'
            });
          }
        });

        $('.credit-on-off').bootstrapSwitch({
          offText: '{$smarty.const.TEXT_NO}',
          onText: '{$smarty.const.TEXT_YES}',
          onSwitchChange: function (a, key) {
            $frmCheckout.trigger('checkout_data_changed', [[{
              name:'coupon_apply',value:'y'
            }]]);
            $('.js_cot_gv_dep').trigger('switch_update',[key]);
          }
        });
        if (!$('.credit-on-off').is(':checked')){
          $('.js_cot_gv_dep').trigger('switch_update',[false]);
        }
        $('.js_discount_apply').on('click',function() {
          $frmCheckout.trigger('checkout_data_changed', [[{
            name:'coupon_apply',value:'y'
          }]]);
          return false;
        });
      });

    })

  tl(['{Info::themeFile('/js/jquery-ui.min.js')}'], function(){
    $('input[name="state"]').autocomplete({
      source: function(request, response) {
        $.getJSON("{Yii::$app->urlManager->createUrl('account/address-state')}", { term : request.term, country: $('select[name="country"]').val() }, response);
      },
      minLength: 0,
      autoFocus: true,
      delay: 0,
      open: function (e, ui) {
        if ($(this).val().length > 0) {
          var acData = $(this).data('ui-autocomplete');
          acData.menu.element.find('a').each(function () {
            var me = $(this);
            var keywords = acData.term.split(' ').join('|');
            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
          });
        }
      },
      select: function( event, ui ) {
        setTimeout(function(){
          $('input[name="state"]').trigger('change');
        }, 200)
      }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    $('input[name="ship_state"]').autocomplete({
			appendTo: $('input[name="ship_state"]').parent().parent(),
      source: function(request, response) {
        $.getJSON("{Yii::$app->urlManager->createUrl('account/address-state')}", { term : request.term, country: $('select[name="ship_country"]').val() }, response);
      },
      minLength: 0,
      autoFocus: true,
      delay: 0,
      open: function (e, ui) {
        if ($(this).val().length > 0) {
          var acData = $(this).data('ui-autocomplete');
          acData.menu.element.find('a').each(function () {
            var me = $(this);
            var keywords = acData.term.split(' ').join('|');
            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
          });
        }
      },
      select: function( event, ui ) {
        setTimeout(function(){
          $('input[name="ship_state"]').trigger('change');
        }, 200)
      }
    }).focus(function () {
      $(this).autocomplete("search");
    });
  })

  </script>