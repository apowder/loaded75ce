
<div class="status-left" style="float: none; margin-bottom: 12px;">
  <span>{$smarty.const.TEXT_STATUS}</span>
  <input type="checkbox" value="1" name="products_status" class="check_bot_switch_on_off"{if $pInfo->products_status == 1} checked="checked"{/if} />
</div>
<div class="create-or-wrap after mn-tab">
  <div class="cbox-left">
    <div class="widget box box-no-shadow">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_LABEL_BRAND}</h4>
      </div>
      <div class="widget-content">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_MANUFACTURERS_NAME}</label>
          <div class="f_td_group f_td_group-pr">
            <input id="selectBrand" name="barnd" type="text" class="form-control form-control-small" value="{$pInfo->manufacturer_name}" autocomplete="off">
            {tep_draw_hidden_field( 'manufacturers_id', $pInfo->manufacturers_id )}
            <a href="{Yii::$app->urlManager->createUrl('categories/brandedit')}" class="btn btn-add-brand edit_brand" title="{$smarty.const.TEXT_ADD_NEW_BRAND}">{$smarty.const.TEXT_ADD_NEW_BRAND}</a>
          </div>

          <script type="text/javascript">
            $(document).ready(function() {
              $('#selectBrand').autocomplete({
                source: "{Yii::$app->urlManager->createUrl('categories/brands')}",
                minLength: 0,
                autoFocus: true,
                delay: 0,
                appendTo: '.f_td_group',
                open: function (e, ui) {
                  if ($(this).val().length > 0) {
                    var acData = $(this).data('ui-autocomplete');
                    acData.menu.element.find('a').each(function () {
                      var me = $(this);
                      var keywords = acData.term.split(' ').join('|');
                      me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                    });
                  }
                }
              }).focus(function () {
                $(this).autocomplete("search");
              });

              $('.edit_brand').popUp({
                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_NEW_BRAND}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
              });

              $('.edit_docs').popUp({
                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.CHOOSE_FILE}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
              });

            });
          </script>
        </div>
      </div>
    </div>
    <div class="widget box box-no-shadow">
      <div class="widget-header">
        <a href="{Yii::$app->urlManager->createUrl(['categories/stock-history', 'prid' => $pInfo->products_id])}" class="right-link">{$smarty.const.TEXT_STOCK_HISTORY}</a>
        <h4>{$smarty.const.TEXT_STOCK}</h4>
      </div>
      <div class="widget-content">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_STOCK_QUANTITY_INFO}:</label>
          {tep_draw_input_field('products_quantity', $pInfo->products_quantity, 'class="form-control form-control-small-qty" readonly disabled')}
          <span class="edp-qty-update">
                                    {tep_draw_pull_down_menu('products_quantity_update_prefix', [['id' => '+', 'text' => '+'], ['id' => '-', 'text' => '-']], '', 'class="form-control form-control-small-qty"')}
            {tep_draw_input_field('products_quantity_update', '', 'class="form-control form-control-small-qty"')}
            {if $pInfo->products_id > 0}
              <span class="btn btn-primary" onclick="products_quantity_update('{$pInfo->products_id}')">{$smarty.const.IMAGE_APPLY}</span>
              <script type="text/javascript">
function products_quantity_update(uprid) {
  var params = [];
  params.push({ name: 'uprid', value: uprid });
  params.push({ name: 'products_quantity_update', value: $('[name="products_quantity_update"]').val() });
  params.push({ name: 'products_quantity_update_prefix', value: $('[name="products_quantity_update_prefix"]').val() });
  $.post("{Yii::$app->urlManager->createUrl('categories/product-quantity-update')}", $.param(params), function(data, status){
    if (status == "success") {
      if (data.products_quantity != undefined) {
        $('[name="products_quantity_update"]').val('');
        $('[name="products_quantity"]').val(data.products_quantity);
      }
      if (data.allocated_quantity != undefined) {
        $('[name="allocated_quantity"]').val(data.allocated_quantity);
      }
      if (data.warehouse_quantity != undefined) {
        $('[name="warehouse_quantity"]').val(data.warehouse_quantity);
      }
    } else {
      alert("Request error.");
    }
  },"json");
}
$('.right-link').popUp({ 'box_class':'popupCredithistory' });
</script>
            {/if}
                                    </span>
          <span class="edp-qty-t" style="display:none;">{$smarty.const.TEXT_APPLICABLE}</b></span>
        </div>
        <div class="t-row">
          <div class="t-col-2">
            <div class="edp-line">
              <label>{$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}:</label>
              {tep_draw_input_field('allocated_quantity', $pInfo->allocated_quantity, 'class="form-control form-control-small-qty" readonly disabled')}
            </div>
          </div>
          <div class="t-col-2">
            <div class="edp-line">
              <label>{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}:</label>
              {tep_draw_input_field('warehouse_quantity', $pInfo->warehouse_quantity, 'class="form-control form-control-small-qty" readonly disabled')}
            </div>
          </div>
        </div>
        <div class="t-row">
          <div class="t-col-2">
            <div class="edp-line">
              <label>{$smarty.const.TEXT_STOCK_INDICATION}:</label>
              {tep_draw_pull_down_menu('stock_indication_id', \common\classes\StockIndication::get_variants(), $pInfo->stock_indication_id, 'class="form-control form-control-small"')}
            </div>
          </div>
          <div class="t-col-2">
            <div class="edp-line">
              <label>{$smarty.const.TEXT_STOCK_DELIVERY_TERMS}:</label>
              {tep_draw_pull_down_menu('stock_delivery_terms_id', \common\classes\StockIndication::get_delivery_terms(), $pInfo->stock_delivery_terms_id, 'class="form-control form-control-small"')}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="widget box box-no-shadow" style="margin-bottom: 5px;">
      <div class="widget-header">
        <h4>{$smarty.const.IMAGE_DETAILS}</h4>
      </div>
      <div class="widget-content">
        <div class="t-row">
              {if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'productBlock')}
                {\common\extensions\MinimumOrderQty\MinimumOrderQty::productBlock($pInfo)}
              {else}   
                <div class="t-col-2 dis_module">
                  <div class="edp-line">
                    <label>{$smarty.const.TEXT_PRODUCTS_ORDER_QUANTITY_MINIMAL}:</label>
                    <input class="form-control form-control-small-qty" type="text" disabled>
                  </div>
                </div>
              {/if}

              {if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'productBlock')}
                {\common\extensions\OrderQuantityStep\OrderQuantityStep::productBlock($pInfo)}
              {else}   
                <div class="t-col-2 dis_module">
                  <div class="edp-line">
                    <label>{$smarty.const.TEXT_PRODUCTS_ORDER_QUANTITY_STEP}:</label>
                    <input class="form-control form-control-small-qty" type="text" disabled>
                  </div>
                </div>
              {/if}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_DATE_AVAILABLE}</label>
          {tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="datepicker form-control form-control-small"' )}
        </div>
        <div class="edp-line edp-line-heig1">
          <label>{$smarty.const.TEXT_GIVE_WRAP}</label>
          <input type="checkbox" name="gift_wrap" value="1" class="check_give_wrap"{if $app->controller->view->gift_wrap == 1} checked{/if} /><input type="text" name="gift_wrap_price" value="{$app->controller->view->gift_wrap_price}" class="form-control form-control-small gift-wr-input edp-ex-s2 mask-money"{if $app->controller->view->gift_wrap == 0} style="display: none;"{/if}>
        </div>
        <div class="edp-line edp-line-heig">
          <label>{$smarty.const.TEXT_FEATURED_PRODUCT}</label>
          <input type="checkbox" name="featured" value="1" class="check_feat_prod"{if $app->controller->view->featured == 1} checked{/if} />
                                    <span class="edp-ex edp-ex-sp edp-ex-s3"{if $app->controller->view->featured == 0} style="display: none;"{/if}><label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
                                    <input type="text" name="featured_expires_date" value="{$app->controller->view->featured_expires_date}" class="datepicker form-control form-control-small"></span>
        </div>
        <div class="edp-line edp-line-heig1">
          <label>{$smarty.const.TEXT_SHIPPING_SURCHARGE}</label>
          <input type="checkbox" name="shipping_surcharge" value="1" class="check_shipping_surcharge"{if $pInfo->shipping_surcharge_price > 0} checked{/if} /><input type="text" name="shipping_surcharge_price" value="{$pInfo->shipping_surcharge_price}" class="form-control form-control-small gift-wr-input edp-ex-s8 mask-money"{if $pInfo->shipping_surcharge_price == 0} style="display: none;"{/if}>
        </div>
        <div class="edp-line edp-line-heig">
          <label>{$smarty.const.TEXT_SUBSCRIPTION}:</label>
          <input type="checkbox" name="subscription" value="1" class="check_subscription"{if $pInfo->subscription == 1} checked{/if} />
                                    <span class="edp-ex edp-ex-sp edp-ex-s9"{if $pInfo->subscription == 0} style="display: none;"{/if}><label>{$smarty.const.TEXT_SUBSCRIPTION_CODE}:</label>
                                    <input type="text" name="subscription_code" value="{$pInfo->subscription_code}" class="form-control form-control-small"></span>
        </div>
      </div>
    </div>
  </div>
  <div class="cbox-right">
    <div class="widget box box-no-shadow" style="background: #fff;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_PRODUCT_IDENTIFIERS}</h4>
      </div>
      <div class="widget-content widget-content-center">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_MODEL_SKU}</label>
          {tep_draw_input_field('products_model', $pInfo->products_model, 'class="form-control form-control-small"')}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_EAN}</label>
          {tep_draw_input_field('products_ean', $pInfo->products_ean, 'class="form-control form-control-small"')}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_ASIN}</label>
          {tep_draw_input_field('products_asin', $pInfo->products_asin, 'class="form-control form-control-small"')}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_ISBN}</label>
          {tep_draw_input_field('products_isbn', $pInfo->products_isbn, 'class="form-control form-control-small"')}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_UPC}</label>
          {tep_draw_input_field('products_upc', $pInfo->products_upc, 'class="form-control form-control-small"')}
        </div>
      </div>
    </div>


          <div class="widget box box-no-shadow product-frontend-box"{if !$app->controller->view->templates.show_block} style="display: none"{/if}>
            <div class="widget-header">
              <h4>{$smarty.const.CHOOSE_PRODUCT_TEMPLATE}</h4>
            </div>
            <div class="widget-content widget-content-center">
              {foreach $app->controller->view->templates.list as $frontend}
                  <div class="product-frontend frontend-{$frontend.id}{if !$frontend.active} disable{/if}">
                    <h4>{$frontend.text} <span>({$smarty.const.TEXT_THEME_NAME}: {$frontend.theme_title})</span></h4>
                    <div>
                      <label>
Default
<input type="radio" name="product_template[{$frontend.id}]" value=""
       class="check_give_wrap"{if !$frontend.template} checked{/if}>
                      </label>
                        {foreach $frontend.templates as $name}
                        <label>
                          {$name}
<input type="radio" name="product_template[{$frontend.id}]" value="{$name}"
       class="check_give_wrap"{if $frontend.template == $name} checked{/if}>
                        </label>
                        {/foreach}
                    </div>
                  </div>
              {/foreach}
            </div>
          </div>
        
  </div>
</div>
<script type="text/javascript">
    (function($){
        $(function(){
            $(window).on('platform_changed', function(e, ob, st){
                if (ob.currentTarget.name == 'platform[]') {
                    if (st == true) {
                        $('.frontend-' + ob.currentTarget.value).removeClass('disable');
                    } else {
                        $('.frontend-' + ob.currentTarget.value).addClass('disable');
                    }
                    if ($('.product-frontend:not(.disable) label:nth-child(2)').length > 0) {
                        $('.product-frontend-box').show();
                    } else {
                        $('.product-frontend-box').hide();
                    }
                }
            });
        });
    })(jQuery);
</script>