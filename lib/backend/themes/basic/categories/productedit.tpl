{use class="yii\helpers\Html"}
{use class="\common\classes\Images"}
{use class="backend\components\Currencies"}
{use class="\common\classes\platform"}

{Currencies::widget()}

<form action="{Yii::$app->urlManager->createUrl('categories/product-submit')}" method="post" enctype="multipart/form-data" id="save_product_form" name="product_edit" onSubmit="return saveProduct();">
<button type="submit" style="display:none"></button>
{tep_draw_hidden_field( 'products_id', $pInfo->products_id )}
{tep_draw_hidden_field( 'categories_id', $categories_id )}
<div class="w-prod-page after w-or-prev-next">
    {if $app->controller->view->product_prev > 0}
    <a href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $app->controller->view->product_prev])}" class="btn-next-prev-or btn-prev-or" title="{$app->controller->view->product_prev_name}"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title=""></a>
    {/if}
    {if $app->controller->view->product_next > 0}
    <a href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $app->controller->view->product_next])}" class="btn-next-prev-or btn-next-or" title="{$app->controller->view->product_next_name}"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title=""></a>
    {/if}
    <div class="tabbable tabbable-custom">
    <div class="tp-all-pages-btn">
        <div class="tp-all-pages-btn-wrapp">
            <span>{$smarty.const.TEXT_ALL_PAGES}</span>
        </div>
        <div class="tl-all-pages-block">
            {if count(platform::getProductsAssignList())>1 }
              <ul class="">
              <li><a href="#tab_platform" data-toggle="tab"><span>{$smarty.const.TEXT_PLATFORM_TAB}</span></a></li>
              {else}
              <ul class="">
          {/if}
            {*<li><a href="#tab_1_1" data-toggle="tab"><span>{$smarty.const.ITEXT_PAGE_VIEW}</span></a></li>*}
{if $app->controller->view->showStatistic == true}
            <li><a href="#tab_1_2" data-toggle="tab"><span>{$smarty.const.TEXT_STATIC}</span></a></li>
{/if}
            <li><a href="#tab_1_3" data-toggle="tab"><span>{$smarty.const.TEXT_PRICE_COST_W}</span></a></li>
            <li class="active"><a href="#tab_1_4" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
            <li><a href="#tab_1_5" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
            <li class="attributes-tab"><a href="#tab_1_6" data-toggle="tab"><span>{$smarty.const.TEXT_ATTR_INVENTORY}</span></a></li>
            <li><a href="#tab_1_7" data-toggle="tab"><span>{$smarty.const.TAB_IMAGES}</span></a></li>
            <li><a href="#tab_1_14" data-toggle="tab"><span>{$smarty.const.TEXT_VIDEO}</span></a></li>
            <li><a href="#tab_1_8" data-toggle="tab"><span>{$smarty.const.TEXT_SIZE_PACKAGING}</span></a></li>
            <li><a href="#tab_1_9" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
            <li><a href="#tab_1_10" data-toggle="tab"><span>{$smarty.const.TEXT_MARKETING}</span></a></li>
            <li><a href="#tab_1_11" data-toggle="tab" title="{$smarty.const.TAB_PROPERTIES}"><span>{$smarty.const.TAB_PROPERTIES}</span></a></li>
            <li><a href="#tab_1_12" data-toggle="tab"><span>{$smarty.const.TAB_BUNDLES}</span></a></li>
            <li><a href="#tab_1_13" data-toggle="tab"><span>{$smarty.const.TAB_DOCUMENTS}</span></a></li>
        </ul>
        </div>
    </div>
          {if count(platform::getProductsAssignList())>1 }
              <ul class="nav nav-tabs nav-tabs-platform nav-tabs-scroll">
              <li><a href="#tab_platform" data-toggle="tab"><span>{$smarty.const.TEXT_PLATFORM_TAB}</span></a></li>
              {else}
              <ul class="nav nav-tabs nav-tabs-scroll">
          {/if}
                {*<li class="active"><a href="#tab_1_1" data-toggle="tab"><span>{$smarty.const.ITEXT_PAGE_VIEW}</span></a></li>*}
{if $app->controller->view->showStatistic == true}
            <li><a href="#tab_1_2" data-toggle="tab"><span>{$smarty.const.TEXT_STATIC}</span></a></li>
{/if}
            <li><a href="#tab_1_3" data-toggle="tab"><span>{$smarty.const.TEXT_PRICE_COST_W}</span></a></li>
            <li class="active"><a href="#tab_1_4" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
            <li><a href="#tab_1_5" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
            <li class="attributes-tab"><a href="#tab_1_6" data-toggle="tab"><span>{$smarty.const.TEXT_ATTR_INVENTORY}</span></a></li>
            <li><a href="#tab_1_7" data-toggle="tab"><span>{$smarty.const.TAB_IMAGES}</span></a></li>
            <li><a href="#tab_1_14" data-toggle="tab"><span>{$smarty.const.TEXT_VIDEO}</span></a></li>
            <li><a href="#tab_1_8" data-toggle="tab"><span>{$smarty.const.TEXT_SIZE_PACKAGING}</span></a></li>
            <li><a href="#tab_1_9" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
            <li><a href="#tab_1_10" data-toggle="tab"><span>{$smarty.const.TEXT_MARKETING}</span></a></li>
            <li><a href="#tab_1_11" data-toggle="tab" title="{$smarty.const.TAB_PROPERTIES}"><span>{$smarty.const.TAB_PROPERTIES}</span></a></li>
            <li><a href="#tab_1_12" data-toggle="tab"><span>{$smarty.const.TAB_BUNDLES}</span></a></li>
            <li><a href="#tab_1_13" data-toggle="tab"><span>{$smarty.const.TAB_DOCUMENTS}</span></a></li>
        </ul>
        <div class="tab-content">
          {if count(platform::getProductsAssignList())>1 }
            <div class="tab-pane topTabPane tabbable-custom" id="tab_platform">
              {include 'productedit/platform.tpl'}
            </div>
          {/if}
            {*<div class="tab-pane active" id="tab_1_1">
                <div id="product-view-edit" style="background: #fff"></div>
                <script type="text/javascript">
                  (function($){ $(function(){
                    $('#product-view-edit').editProduct({
                      page_url: '{Yii::getAlias('@web')}/../catalog/product?products_id={$pInfo->products_id}&is_admin=1'
                    })
                  })})(jQuery)
                </script>
            </div>*}
{if $app->controller->view->showStatistic == true}
            <div class="tab-pane" id="tab_1_2">
              {include 'productedit/statistic.tpl'}
            </div>
{/if}
            <div class="tab-pane" id="tab_1_3">
              {include 'productedit/price.tpl'}
            </div>

            <div class="tab-pane active" id="tab_1_4">
              {include 'productedit/name.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_5">
              {include 'productedit/details.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_6">
                {if \common\helpers\Acl::checkExtension('Inventory', 'productBlock') && PRODUCTS_INVENTORY == 'True'}
                {\common\extensions\Inventory\Inventory::productBlock($pInfo)}
              {else}   
              {include 'productedit/attributes.tpl'}
              {/if}
            </div>

            <div class="tab-pane" id="tab_1_7">
              {include 'productedit/images.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_14">
              {include 'productedit/video.tpl'}
            </div>

            <div class="tab-pane tab-size-pack" id="tab_1_8">
              {include 'productedit/size.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_9">
              {include 'productedit/seo.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_10">
              {include 'productedit/marketing.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_11">
              {include 'productedit/properties.tpl'}
            </div>

            <div class="tab-pane" id="tab_1_12">
              {if \common\helpers\Acl::checkExtension('ProductBundles', 'productBlock')}
                {\common\extensions\ProductBundles\ProductBundles::productBlock($pInfo)}
              {else}   
                {include 'productedit/bundles.tpl'}
              {/if}
            </div>

            <div class="tab-pane" id="tab_1_13">
              {if \common\helpers\Acl::checkExtension('ProductDocuments', 'productBlock')}
                {\common\extensions\ProductDocuments\ProductDocuments::productBlock($pInfo)}
              {else}  
                {include 'productedit/documents.tpl'}
              {/if}
            </div>

        </div>
    </div>
    <div class="btn-bar btn-bar-edp-page after" style="padding: 0;">
        <div class="btn-left">
            <a href="javascript:void(0)" onclick="return backStatement()" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button><a style="opacity: 0.3; cursor: default;" class="btn btn-primary" title="Will be available in the next version.">{$smarty.const.TEXT_PREVIEW_LIGHTBOX}</a>

          {if $app->controller->view->preview_link|@count > 1}
            <a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
          {else}
            <a href="{$app->controller->view->preview_link[0].link}" target="_blank" class="btn btn-primary">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
          {/if}
        </div>
    </div>
    <div class="btn-bar-text">{$smarty.const.TEXT_AFTER_SAFE_ONLY}</div>
</div>
</form>

{if $app->controller->view->preview_link|@count > 1}
<div id="choose-frontend" style="display: none">
  <div class="popup-heading">Choose frontend</div>
  <div class="popup-content frontend-links">
    {foreach $app->controller->view->preview_link as $link}
      <p><a href="{$link.link}" target="_blank">{$link.name}</a></p>
    {/foreach}
  </div>
  <div class="noti-btn">
    <div><button class="btn btn-cancel">Cancel</button></div>
  </div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.popup-box-wrap .frontend-links a').on('click', function(){
          $('.popup-box-wrap').remove()
        })
      })
    })(jQuery)
  </script>
</div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.btn-choose-frontend').popUp();
      })
    })(jQuery)
  </script>
{/if}

<script>

function backStatement() {
    window.history.back();
    return false;
}

function resetStatement() {
    return false;
}

function saveProduct() {
    if (typeof unformatMaskMoney == 'function') {
        unformatMaskMoney();
    }
    //return true;
    if (typeof(CKEDITOR) == 'object'){
      for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
      }
    }
    $.post("{Yii::$app->urlManager->createUrl('categories/product-submit')}", $('#save_product_form').serialize(), function(data, status){
        if (status == "success") {
            $('#save_product_form').html(data);

        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}


//===== Images START =====//
//===== Images END =====//
(function($) {
        var jcarousel = $('.jcarousel').jcarousel();

        $('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '+=1'
            });

        var setup = function(data) {
            var html = '<ul>';

            $.each(data.items, function() {
                html += '<li><img src="' + this.src + '" alt="' + this.title + '"></li>';
            });

            html += '</ul>';

            // Append items
            jcarousel
                .html(html);

            // Reload carousel
            jcarousel
                .jcarousel('reload');
        };

        $( "#images-listing" ).sortable({
            handle: ".handle",
            axis: 'x',
            update: function( event, ui ) {
                var data = $(this).sortable('serialize', { attribute: "prefix" });
                $("#images_sort_order").val(data);
            },
        }).disableSelection();

})(jQuery);

    $(document).ready(function(){ 
        $(".check_bot_switch_on_off").bootstrapSwitch(
            {
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );
        $(".is-virtual-btn .is_virt_on_off").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.is-virtual').toggle();
                    $('.is-virtual-upload').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $(".check_give_wrap").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s2').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );
        $(".check_shipping_surcharge").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s8').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );
        $(".check_feat_prod").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s3').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $(".check_subscription").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s9').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $('.heigh_col').on('click', function(){
            setTimeout(function(){
              $('.mn-tab .cbox-right .widget').css('height', $('.mn-tab .cbox-left').height() - 5);
          }, 100);
        });
        $('.heigh_col2').on('click', function(){
            setTimeout(function(){
              $('.edp-our-price-box .widget-not-full .widget-content').css('min-height', $('.edp-pc-box .cbox-right').height() - $('.widget-full').height() - 2);
          }, 100);
        });
        $(window).resize(function() {
          setTimeout(function(){
              //$('.mn-tab .cbox-right .widget').css('height', $('.mn-tab .cbox-left').height() - 5);
              $('.edp-our-price-box .widget-not-full .widget-content').css('min-height', $('.edp-pc-box .cbox-right').height() - $('.widget-full').height() - 2);
          }, 500);
        });
        $(window).resize();

        $('.rem-quan-line').click(function() {
            $(this).parent().remove();
            updateInventoryBox();
        });
{if $app->controller->view->useMarketPrices == true}
    {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
        $('.btn-add-more-{$currId}-0').click(function() {
            $('.wrap-quant-discount-{$currId}-0').append('<div class="quant-discount-line after div_qty_discount_prod"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>{Html::textInput('discount_qty_'|cat:$currId|cat:'_0[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_NET}</label>{Html::textInput('discount_price_'|cat:$currId|cat:'_0[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_GROSS}</label>{Html::textInput('discount_price_gross_'|cat:$currId|cat:'_0[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}</div><span class="rem-quan-line"></span></div>');
            $('.rem-quan-line').unbind('click').click(function() {
                $(this).parent().remove();
                updateInventoryBox();
            });
        });
        {if {$app->controller->view->groups|@count} > 0}
            {foreach $app->controller->view->groups as $groups_id => $group}
                $('.btn-add-more-{$currId}-{$groups_id}').click(function() {
                    $('.wrap-quant-discount-{$currId}-{$groups_id}').append('<div class="quant-discount-line after div_qty_discount_prod"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>{Html::textInput('discount_qty_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_NET}</label>{Html::textInput('discount_price_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_GROSS}</label>{Html::textInput('discount_price_gross_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}</div><span class="rem-quan-line"></span></div>');
                    $('.rem-quan-line').unbind('click').click(function() {
                        $(this).parent().remove();
                        updateInventoryBox();
                    });
                });
            {/foreach}
        {/if}


    {/foreach}


{else}
        $('.btn-add-more').click(function() {
            $('.wrap-quant-discount').append('<div class="quant-discount-line after div_qty_discount_prod"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>{Html::textInput('discount_qty[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_NET}</label>{Html::textInput('discount_price[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_GROSS}</label>{Html::textInput('discount_price_gross[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}</div><span class="rem-quan-line"></span></div>');
            $('.rem-quan-line').unbind('click').click(function() {
                $(this).parent().remove();
                updateInventoryBox();
            });
        });
{if {$app->controller->view->groups|@count} > 0}
    {foreach $app->controller->view->groups as $groups_id => $group}
        $('.btn-add-more-{$groups_id}').click(function() {
            $('.wrap-quant-discount-{$groups_id}').append('<div class="quant-discount-line after div_qty_discount_prod"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>{Html::textInput('discount_qty_'|cat:$groups_id|cat:'[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_NET}</label>{Html::textInput('discount_price_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}</div><div><label>{$smarty.const.TEXT_GROSS}</label>{Html::textInput('discount_price_gross_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}</div><span class="rem-quan-line"></span></div>');
            $('.rem-quan-line').unbind('click').click(function() {
                $(this).parent().remove();
                updateInventoryBox();
            });
        });
    {/foreach}
{/if}
{/if}


        $('.click-main').click(function(){
            $('[href="#tab_1_5"]').click();
        });
        $('.click-price').click(function(){
            $('[href="#tab_1_3"]').click();
        });
        $('.click-images').click(function(){
            $('[href="#tab_1_7"]').click();
        });
        $('.widget-content-stat img').click(function(){
            $('[href="#tab_1_7"]').click();
        });
        $('.edp-qty-t b').click(function(){
            $('[href="#tab_1_6"]').click();
        });
        $('.pr_plus').click(function(){
            val = $(this).next('input').attr('value');
            //if (val < 9){
              val++;
            //}
            /*if (val == 9){
                $(this).addClass('disableM');
            }*/
            var input = $(this).next('input');
            input.attr('value', val);
            if (val > 1) input.siblings('.pr_minus').removeClass('disable');
        });
         $('.pr_minus').click(function(){
            //productButtonCell = $('#qty').parents('.qty-buttons');
            val = $(this).prev('input').attr('value');
            if (val > 1){
              val--;
              $(this).prev('input').siblings('.more').removeClass('disableM');
            }
            var input = $(this).prev('input');
            input.attr('value', val);
            if (val < 2) $('.pr_minus').addClass('disable');
        });

        $('.upload').uploads();

        $('.jcarousel li').click( function() {
            $('.jcarousel li').removeClass('active');
            $(this).addClass('active');
            $(".image-box.active").removeClass('active').addClass('inactive');
            var prefix = $(this).attr('prefix');
            $("#"+prefix).removeClass('inactive').addClass('active');
        });

        $('.type-altr-file-name').click(function(){
            $(this).parent('span').parent('div').parent('label').parent('div.our-pr-line').children('.type-altr-file-name-input').toggle();
        });

        /*$('.w-img-check-all > span').click(function(){
            $('.w-img-check-all > span').removeClass('active');
            $(this).toggleClass('active');
        });

        $('.w-img-list ul li').click(function(){
            $('.w-img-list ul li').removeClass('active');
            $(this).toggleClass('active');
        });

        $('.check_all').click(function(){
            $('.w-img-list ul li .uniform').click().change();
        });

        $('.uncheck_all').click(function(){
            $('.w-img-list ul li .uniform').click().change();
        });*/

        //===== Date Pickers  =====//
	$( ".datepicker" ).datepicker({
		changeMonth: true,
                changeYear: true,
		showOtherMonths:true,
		autoSize: false,
		dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
        });
    });

    $(function() {
        var all_page_btn = $('.tp-all-pages-btn').width() + 8;
		$('.scrtabs-tab-container').css('margin-right', all_page_btn);
		$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
            localStorage.setItem('lastTab', $(this).attr('href'));
        });
        var lastTab = localStorage.getItem('lastTab');
        if (lastTab) {
            $('a[href=' + lastTab + ']').tab('show');
        } else {
            $('a[data-toggle="tab"]:first').tab('show');
        }
        var activate_categories = {$json_platform_activate_categories};
      $('.check_on_off').bootstrapSwitch( {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            onSwitchChange: function (ob, st) {
                var switched_to_state = false;
                if($(this).is(':checked')){
                    switched_to_state = true;
                }
                $(window).trigger('platform_changed', [ob, st]);
                
                if (switched_to_state && this.name.indexOf('platform')==0 ) {
                    var platform_id = this.value;
                    var askActivateCategories = '';
                    if ( activate_categories[platform_id] ) {
                        for( var cat_id in activate_categories[platform_id]){
                            if ( !activate_categories[platform_id].hasOwnProperty(cat_id) ) continue;
                            askActivateCategories += '<br><label><input name="_assign_select[]" class="js-activate_parent_categories_select" '+(activate_categories[platform_id][cat_id]['selected']?' checked="checked" disabled="disabled" readonly="readonly"':'')+' type="checkbox" value="'+cat_id+'"> '+activate_categories[platform_id][cat_id]['label']+'</label>';
                        }
                    }

                        var $state_input = $('.js-platform_parent_categories').filter('input[name="activate_parent_categories['+platform_id+']"]');
                        if ( switched_to_state && $state_input.val()=='' && (askActivateCategories.length>0) ) {
                            $('body').append(
                                '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                                '<div class="around-pop-up"></div>' +
                                '<div class="popup-box"><div class="pop-up-close"></div>' +
                                '<div class="pop-up-content">' +
                                '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_PRODUCT_CATEGORIES} '+askActivateCategories+'</div>' +
                                '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                                '</div>' +
                                '</div>' +
                                '</div>');
                            $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));
                            if ( $('.js-activate_parent_categories_select').filter(':checked').length==0 ) {
                                $('.js-activate_parent_categories_select').trigger('click');
                            }

                            var $popup = $('.js-state-confirm-popup');
                            $popup.find('.pop-up-close').on('click', function(){
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-cancel').on('click', function(){
                                $state_input.val('');
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-success').on('click', function(){
                                var selected_values = [];
                                $('.js-activate_parent_categories_select:checked').each(function(){
                                    selected_values.push(this.value);
                                });
                                $state_input.val(selected_values.join(','));
                                $('.popup-box-wrap:last').remove();
                            });
                        }

                }
              },
            handleWidth: '20px',
            labelWidth: '24px'
      } );

      $('#save_product_form input[type="search"]').on('keydown',function(event){
          if (event.keyCode=='13'){
              event.preventDefault();
          }
      });
			$('.metric_system span').off().click(function(){
				$('.metric_system span').removeClass('selected');
				$(this).addClass('selected');
				$('.dimmens').hide();
				$('.'+$(this).data('class')).show();
				return false;
			})
			$('input[name="pack_unit"]').keyup(function(){
                            if ($(this).val() > 0) {
                                $('input[name="packaging"]').removeAttr('disabled');
                            } else {
                                $('input[name="packaging"]').val(0);
                                $('input[name="packaging"]').attr('disabled','disabled');
                            }
                            
				if($('input[name="packaging"]').val()){
                                  $('input[name="units_to_pack"]').val($(this).val()*$('input[name="packaging"]').val());
				}else{
				  $('input[name="units_to_pack"]').val($(this).val());
				}				
			})
                        if ( $('input[name="pack_unit"]').val() > 0 ) {
                            $('input[name="packaging"]').removeAttr('disabled');
                        } else {
                            $('input[name="packaging"]').val(0);
                            $('input[name="packaging"]').attr('disabled','disabled');
                        }
			$('input[name="packaging"]').keyup(function(){
					if($('input[name="packaging"]').val()){
						$('input[name="units_to_pack"]').val($(this).val()*$('input[name="pack_unit"]').val());		
					}else{
						$('input[name="units_to_pack"]').val($('input[name="pack_unit"]').val());
					}
			})
    });
</script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>