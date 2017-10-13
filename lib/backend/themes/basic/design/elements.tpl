{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
<div class="page-elements">
  {include 'menu.tpl'}
  <div class="page-elements-top">
      <div class="tabbable tabbable-custom">
          {if !$landing}
          <div class="tp-all-pages-btn">
              <div class="tp-all-pages-btn-wrapp">
                  <span>{$smarty.const.TEXT_ALL_PAGES}</span>
              </div>
              <div class="tl-all-pages-block">
                    <ul class="js-catalog_url_set">
              <li class="page-link-home active" data-ref="home" data-href="{$editable_links.home}"><a data-toggle="tab"><span>{$smarty.const.TEXT_HOME}</span></a></li>
        {foreach $editable_links_home as $item}
                  <li class="page-link-home active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-product" data-ref="product" data-href="{$editable_links.product}" {if empty($editable_links.product)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT}</span></a></li>
        {foreach $editable_links_product as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-product" data-ref="attributes" data-href="{$editable_links.attributes}" {if empty($editable_links.attributes)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT_WITH_ATTRIBUTES}</span></a></li>
              <li class="page-link-product" data-ref="bundle" data-href="{$editable_links.bundle}" {if empty($editable_links.bundle)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT_BUNDLES}</span></a></li>
              <li class="page-link-catalog" data-ref="categories" data-href="{$editable_links.categories}" {if empty($editable_links.categories)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_LISTING_CATEGORIES}</span></a></li>
        {foreach $editable_links_categories as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-catalog" data-ref="products" data-href="{$editable_links.products}" {if empty($editable_links.products)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_LISTING_PRODUCTS}</span></a></li>
        {foreach $editable_links_products as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-info" data-ref="information" data-href="{$editable_links.information}" {if empty($editable_links.information)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_INFORMATION}</span></a></li>
        {foreach $editable_links_info as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
        <li class="page-link-cart" data-ref="cart" data-href="{$editable_links.cart}"><a data-toggle="tab"><span>{$smarty.const.TEXT_SHOPPING_CART}</span></a></li>
        <li class="page-link-success" data-ref="success" data-href="{$editable_links.success}"><a data-toggle="tab"><span>{$smarty.const.TEXT_CHECKOUT_SUCCESS}</span></a></li>
        <li class="page-link-contact" data-ref="contact" data-href="{$editable_links.contact}"><a data-toggle="tab"><span>{$smarty.const.TEXT_HEADER_CONTACT_US}</span></a></li>
        <li class="page-link-email" data-ref="email" data-href="{$editable_links.email}"><a data-toggle="tab"><span>Email</span></a></li>
        <li class="page-link-invoice" data-ref="invoice" data-href="{$editable_links.invoice}" {if empty($editable_links.invoice)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_INVOICE}</span></a></li>
        <li class="page-link-packingslip" data-ref="packingslip" data-href="{$editable_links.packingslip}" {if empty($editable_links.packingslip)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PACKINGSLIP}</span></a></li>
        <li class="page-link-gift" data-ref="gift" data-href="{$editable_links.gift}" {if empty($editable_links.gift)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_GIFT_CARD}</span></a></li>
        <li class="page-link-blog" data-ref="blog" data-href="{$editable_links.blog}"><a data-toggle="tab"><span>Blog</span></a></li>
        <li class="add-page"><a data-toggle="tab"><span>+</span></a></li>
          </ul>
        {include 'menu.tpl'}
              </div>
          </div>
          {/if}
          <ul class="nav nav-tabs nav-tabs-platform nav-tabs-scroll js-catalog_url_set">
               {if !$landing}
              <li class="page-link-home active" data-ref="home" data-href="{$editable_links.home}"><a data-toggle="tab"><span>{$smarty.const.TEXT_HOME}</span></a></li>
        {foreach $editable_links_home as $item}
                  <li class="page-link-home active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-product" data-ref="product" data-href="{$editable_links.product}" {if empty($editable_links.product)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT}</span></a></li>               
        {foreach $editable_links_product as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-product" data-ref="attributes" data-href="{$editable_links.attributes}" {if empty($editable_links.attributes)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT_WITH_ATTRIBUTES}</span></a></li>
              <li class="page-link-product" data-ref="bundle" data-href="{$editable_links.bundle}" {if empty($editable_links.bundle)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCT_BUNDLES}</span></a></li>
              <li class="page-link-catalog" data-ref="categories" data-href="{$editable_links.categories}" {if empty($editable_links.categories)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_LISTING_CATEGORIES}</span></a></li>
        {foreach $editable_links_categories as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-catalog" data-ref="products" data-href="{$editable_links.products}" {if empty($editable_links.products)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_LISTING_PRODUCTS}</span></a></li>
        {foreach $editable_links_products as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
              <li class="page-link-info" data-ref="information" data-href="{$editable_links.information}" {if empty($editable_links.information)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_INFORMATION}</span></a></li>
        {foreach $editable_links_info as $item}
                  <li class="page-link-product active" data-ref="{$item.page_name}" data-href="{$item.link}"><a data-toggle="tab"><span>{$item.page_title}</span></a></li>
        {/foreach}
        <li class="page-link-cart" data-ref="cart" data-href="{$editable_links.cart}"><a data-toggle="tab"><span>{$smarty.const.TEXT_SHOPPING_CART}</span></a></li>
        <li class="page-link-success" data-ref="success" data-href="{$editable_links.success}"><a data-toggle="tab"><span>{$smarty.const.TEXT_CHECKOUT_SUCCESS}</span></a></li>
        <li class="page-link-contact" data-ref="contact" data-href="{$editable_links.contact}"><a data-toggle="tab"><span>{$smarty.const.TEXT_HEADER_CONTACT_US}</span></a></li>
        <li class="page-link-email" data-ref="email" data-href="{$editable_links.email}"><a data-toggle="tab"><span>Email</span></a></li>
        <li class="page-link-invoice" data-ref="invoice" data-href="{$editable_links.invoice}" {if empty($editable_links.invoice)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_INVOICE}</span></a></li>
        <li class="page-link-packingslip" data-ref="packingslip" data-href="{$editable_links.packingslip}" {if empty($editable_links.packingslip)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_PACKINGSLIP}</span></a></li>
        <li class="page-link-gift" data-ref="gift" data-href="{$editable_links.gift}" {if empty($editable_links.gift)} style="display:none" {/if}><a data-toggle="tab"><span>{$smarty.const.TEXT_GIFT_CARD}</span></a></li>
        <li class="page-link-blog" data-ref="blog" data-href="{$editable_links.blog}"><a data-toggle="tab"><span>Blog</span></a></li>
        <li class="add-page"><a data-toggle="tab"><span>+</span></a></li>
        {else}      
              <li class="page-link-home active" data-ref="home" data-href="{$editable_links.home}"><a data-toggle="tab"><span>{$smarty.const.TEXT_HOME}</span></a></li>
        {/if}
          </ul>
      </div>
  </div>
    

  <script type="text/javascript">
    (function ($) {
      $(function(){
        var tab_links = $('.page-elements-top .nav-tabs');

        $('.edit', tab_links).on('click', function(){
          var name = $(this).data('name');
          $('<a href="{Yii::$app->urlManager->createUrl(['design/add-page-settings'])}"></a>').popUp({ data: {
            theme_name: '{$theme_name}',
            page_name: name
          }}).trigger('click');
          return false;
        });
      })
    })(jQuery)
  </script>

  <div class="info-view-wrap">
    <div class="info-view"></div>
  </div>

  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
      <span data-href="{$link_cancel}" class="btn btn-save-boxes">{$smarty.const.IMAGE_CANCEL}</span>
      <span data-href="{$link_copy}" class="btn btn-copy-boxes" style="display: none">Copy theme settings</span>
    </div>
    <div class="btn-right">
      <span class="btn btn-preview">{$smarty.const.ICON_PREVIEW}</span>
      <span class="btn btn-edit" style="display: none">{$smarty.const.IMAGE_EDIT}</span>
      <span data-href="{$link_save}" class="btn btn-confirm btn-save-boxes">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>

</div>

<script type="text/javascript" src="{$app->request->baseUrl}/plugins/html2canvas.js"></script>
<script type="text/javascript">
  var per_platform_links = {json_encode($per_platform_links)};
  (function($){    
	$('#platform_selector').on('change',function(){
      if ( !per_platform_links[$(this).val()] ) return;
      var new_links = per_platform_links[$(this).val()];
      var reset_active = false;
      $('.js-catalog_url_set [data-ref]').each( function(){
        var $a = $(this);
        var ref = $a.attr('data-ref');
        if ( typeof new_links[ref] !== 'undefined' ) {
          $a.attr('data-href', new_links[ref]);
          $a.data('href', new_links[ref]);
        }
        if ( $a.attr('data-href').length==0 && $a.is(':visible') ) {
          $a.hide();
          if ( $a.hasClass('active') ) reset_active = true;
        }
        if ( $a.attr('data-href').length>0 && $a.not(':visible') ) $a.show();
      } );
      if ( reset_active ) {
        $('.js-catalog_url_set li[data-href]').filter(':not([data-href=""])').first().trigger('click')
      }else {
        $('.js-catalog_url_set li.active').first().trigger('click');
      }
    });

    $(function(){
      $('.btn-save-boxes').on('click', function(){
        $.get($(this).data('href'), { theme_name: '{$theme_name}'}, function(d){
          alertMessage(d);
          setTimeout(function(){
            $(window).trigger('reload-frame');

            $('body').append('<iframe src="{$app->request->baseUrl}/../?theme_name={$theme_name}" width="100%" height="0" frameborder="no" id="home-page"></iframe>');

            var home_page = $('#home-page');
            home_page.on('load', function(){
                html2canvas(home_page.contents().find('body').get(0), {
                  background: '#ffffff',
                  onrendered: function(canvas) {
                    $.post('upload/screenshot', { theme_name: '{$theme_name}', image: canvas.toDataURL('image/png')});
                    home_page.remove()
                  }
                })
            });



          }, 500)
        })
      });

      $('.btn-copy-boxes').on('click', function(){
        var href = $(this).data('href');
        alertMessage('<div class="confirm"><p>{$smarty.const.TEXT_ENTER_THEME_NAME}</p><p><input type="text" class="form-control"/></p><p><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span> <span class="btn btn-primary btn-yes">{$smarty.const.IMAGE_SAVE}</span></p></div>');

        $('.confirm .btn-yes').on('click', function(){

          var n = $('.confirm input').val();
          $('.pop-up-content .confirm').html('<div class="preloader"></div>');
          $.post(href, {
            theme_name: '{$theme_name}',
            theme_new: n
          }, function(d){
            location.href = '{Yii::$app->urlManager->createUrl(['design/themes'])}'
          }, 'json')

        });
      });

      var url = '';

      var cooked_url = $.cookie('page-url') || '';
      var reset_preview_url = true;
      if ( cooked_url.length>0 ) {
        $('.js-catalog_url_set [data-href]').each(function(){
          if ($(this).attr('data-href')==cooked_url) reset_preview_url = false;
        });
      }
      var cookie_url_match = cooked_url.match(/theme_name=([a-z0-9\-_]+)/);
      if (
          reset_preview_url || !cookie_url_match || (cookie_url_match && cookie_url_match[1] != '{$theme_name}')
      ){
        url = $('.js-catalog_url_set .active').attr('data-href');

        $.cookie('page-url', url);
      } else {
        url = $.cookie('page-url');
      }

      $('.info-view').infoView({
        page_url: url,
        theme_name: '{$theme_name}',
        //clear_url: {$clear_url}
      });


      $(window).on('scroll', function(){
        if ($(window).scrollTop() > 70) {
          $('.page-elements-top')
                  .css('top', $(window).scrollTop() - 70)
                  .addClass('scrolled')
        } else {
          $('.page-elements-top')
                  .css('top', 0)
                  .removeClass('scrolled')
        }
      });

      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        var event = $(this).data('event');
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          if (event == 'addPage' ){
            location.href = location.href
          }
          $(window).trigger('reload-frame');

        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        var event = $(this).data('event');
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          if (event == 'addPage'){
            location.href = location.href
          }
          $(window).trigger('reload-frame');
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });
      $(window).on('reload-frame', function(){
        $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
          redo_buttons.html(data);
          $(redo_buttons).show();
        })
      })

    })
  })(jQuery);
  $(document).ready(function(){ 
		var all_page_btn = $('.tp-all-pages-btn').width() + 8;
		$('.scrtabs-tab-container').css('margin-right', all_page_btn);
        $('.tl-all-pages-block ul li').on('click', function () { 
        $('.nav-tabs-scroll li.active').removeClass('active');
        $('.nav-tabs-scroll li[data-href="' + $(this).attr('data-href') + '"]').addClass('active');
        $('.nav-tabs-scroll').scrollingTabs('scrollToActiveTab');
    });

    $('.nav-tabs-scroll li').on('click', function () { 
        $('.tl-all-pages-block ul li.active').removeClass('active');
        $('.tl-all-pages-block ul li[data-href="' + $(this).attr('data-href') + '"]').addClass('active');
    });
  });
</script>