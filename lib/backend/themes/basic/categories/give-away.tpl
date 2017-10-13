{* function draw_gaw_buy_get_row --- params data, groups_id, id_prefix, cnt *}
{function draw_gaw_buy_get_row }{strip}
    <div class="{$id_prefix}-buy-get gaw-buy-get t-row after">
      <div class="gaw-buy-get-col t-col" >
        <label>{$smarty.const.TEXT_QTY_BEFORE_GIVE_AWAY}:
          <input type="text" name="buy_qty[{$groups_id}][{$cnt}]" value="{$data['buy_qty']}" class="form-control form-control-small" style="width:40px; vertical-align: middle">
        </label>
      </div>
      <div class="gaw-buy-get-col t-col" >
        <label>{$smarty.const.TEXT_GIVE_AWAY_FREE_QTY}:
          <input type="text" name="products_qty_gb[{$groups_id}][{$cnt}]" value="{$data['products_qty']}" class="form-control form-control-small" style="width:40px; vertical-align: middle">
        </label>
      </div>
      <div class="gaw-buy-get-col t-col" >
        <label>
          <input type="checkbox" value="1" name="use_in_qty_discount[{$groups_id}][{$cnt}]" id="use_in_qty_discount[{$groups_id}][{$cnt}]" class="check_give_away1" {if {$data['use_in_qty_discount'] > 0}} checked="checked" {/if} />{$smarty.const.FIELDSET_GIVE_AWAY_BUY_GET_USE_IN_PRICE}</label>
      </div>
      <div class="gaw-buy-get-col t-col" >
        <span class="rem-gaw-line rem-line-button"></span>
      </div>
    </div>{/strip}{/function}

{function draw_gaw_buy_get id_prefix='tab'}
{* global data groups_id*}
  <div class="{$id_prefix}-buy-get-wrap-{$groups_id}">
    {if $data[$groups_id]['by_total'] != 1}
      {assign var="i" value=0}
      {foreach $data[$groups_id] as $d}
        {if is_array($d)}
          {call draw_gaw_buy_get_row id_prefix=$id_prefix groups_id=$groups_id data=$d cnt=$i++}
        {/if}
      {foreachelse}
        {call draw_gaw_buy_get_row id_prefix=$id_prefix groups_id=$groups_id data=false cnt=$i++}
      {/foreach}
    {/if}
  </div>
  <div class="{$id_prefix}-buy-get-more" >
    <span class="btn btn-add-more-gaw-buy-get" data-groups_id="{$groups_id}">{$smarty.const.TEXT_ADD_MORE}</span>
  </div>
{/function}

{function group_tabs id_prefix='tab' }
{* params groups *}
  <ul class="nav nav-tabs">
  {foreach $groups as $groups_id => $group}
    <li class="{if $groups_id==0}active{/if}"><a href="#{$id_prefix}_{$groups_id}" data-toggle="tab"><span>{$group['groups_name']}</span></a></li>
  {/foreach}
  </ul>
{/function}

{function group_tabs_data id_prefix='tab' }
{* params groups, global : app..=>gaw, currenciesTabs, useMarketPrices *}
  <div class="tab-content">
  {foreach $groups as $groups_id => $group}
      <div class="tab-pane {if $groups_id==0}active{/if}" id="{$id_prefix}_{$groups_id}">
          <div class="tab-content">
              <label>{$smarty.const.TEXT_GIVE_AWAY_ORDER}</label>
              <input type="checkbox" name="give_away[{$groups_id}]" value="1" class="check_give_away" id="gaw_group_switcher_{$groups_id}" {if count($app->controller->view->gaw[$groups_id])>0} checked{/if} />
              <div class="gaw-group" id="gaw_group_{$groups_id}" {if not count($app->controller->view->gaw[$groups_id])>0}style="display:none"{/if} >
                <label for="begin_date[{$groups_id}]" >{$smarty.const.TEXT_START_DATE}:</label><input type="text" class="datepicker form-control form-control-small" name="begin_date[{$groups_id}]" id="begin_date[{$groups_id}]" value="{$app->controller->view->gaw[{$groups_id}]['begin_date']}">
                <label for="end_date[{$groups_id}]" >{$smarty.const.TEXT_END_DATE}:</label><input type="text" class="datepicker form-control form-control-small" name="end_date[{$groups_id}]" id="end_date[{$groups_id}]" value="{$app->controller->view->gaw[{$groups_id}]['end_date']}">
                <div class="btn-box-inv-price gaw-switch after" >
                    <span class="btn-ga btn-gaw-total {if $app->controller->view->gaw[{$groups_id}]['by_total'] == 1}active{/if}" id="btn-gaw-total-{$groups_id}">{$smarty.const.FIELDSET_GIVE_AWAY_TOTAL}</span>
                    <span class="btn-ga btn-gaw-buy-get {if $app->controller->view->gaw[{$groups_id}]['by_total'] != 1}active{/if}" id="btn-gaw-buy-get-{$groups_id}">{$smarty.const.FIELDSET_GIVE_AWAY_BUY_GET}</span>
                </div>
                <div class="box-ga box-gaw-total" id="box-gaw-total-{$groups_id}" style="{if $app->controller->view->gaw[{$groups_id}]['by_total'] != 1}display:none{/if}">
                    <div class="after">
          {if $app->controller->view->useMarketPrices == true}
                        <ul class="nav nav-tabs">
                            {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
                            <li{if $app->controller->view->defaultCurrenciy == $currId} class="active"{/if}><a href="#gaw_{$groups_id}_{$currId}" data-toggle="tab"><span>{$currTitle}</span></a></li>
                            {/foreach}
                        </ul>
                        <div class="tab-content">
                    {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
                            <div class="tab-pane{if $app->controller->view->defaultCurrenciy == $currId} active{/if}" id="gaw_{$groups_id}_{$currId}">
                                <span class="wchb">
                                <label for="shopping_cart_price[{$groups_id}][{$currId }]">{$smarty.const.TEXT_ORDER_AMOUNT}:</label><input type="text" name="shopping_cart_price[{$groups_id}][{$currId }]" id="shopping_cart_price[{$groups_id}][{$currId }]" value="{$app->controller->view->gaw[{$groups_id}][$currId]['shopping_cart_price']}" class="form-control form-control-small mask-money"><i><div>{$smarty.const.TEXT_GROUP_GIVE_AWAY_PRICE}</div></i>
                                <label for="products_qty[{$groups_id}][{$currId }]" >{$smarty.const.TEXT_GIVE_AWAY_FREE_QTY}:</label><input type="text" name="products_qty[{$groups_id}][{$currId }]" id="products_qty[{$groups_id}][{$currId }]" value="{$app->controller->view->gaw[{$groups_id}][$currId ]['products_qty']}" class="form-control form-control-small" style="width:40px; vertical-align: middle">
                                </span>
                            </div><!-- class="tab-pane{if $app->controller->view->defaultCurrenciy == $currId} active{/if}" id="gaw_{$currId}"-->
                    {/foreach}
                        </div>
          {else}
                        <span class="wchb"><label for="shopping_cart_price[{$groups_id}]">{$smarty.const.TEXT_ORDER_AMOUNT}:</label><input type="text" name="shopping_cart_price[{$groups_id}]" id="shopping_cart_price[{$groups_id}]" value="{$app->controller->view->gaw[{$groups_id}][0]['shopping_cart_price']}" class="form-control form-control-small mask-money"><i><div>{$smarty.const.TEXT_GROUP_GIVE_AWAY_PRICE}</div></i> <label for="products_qty[{$groups_id}]">{$smarty.const.TEXT_GIVE_AWAY_FREE_QTY}:</label><input type="text" name="products_qty[{$groups_id}]" id="products_qty[{$groups_id}]" value="{$app->controller->view->gaw[{$groups_id}][0]['products_qty']}" class="form-control form-control-small" style="width:40px; vertical-align: middle"></span>
          {/if}
                    </div>
                </div>
                <div class="box-ga box-gaw-buy-get" id="box-gaw-buy-get-{$groups_id}" style="{if $app->controller->view->gaw[{$groups_id}]['by_total'] == 1}display:none{/if}">
                    <div class="after">
                      {call draw_gaw_buy_get id_prefix='gaw' data=$app->controller->view->gaw groups_id=$groups_id}
                    </div>
                </div>
              </div>
          </div>
      </div><!-- class="tab-pane" id="{$id_prefix}_{$groups_id}"-->
  {/foreach}
  </div>
{/function}



<div class="widget-content">
    <div class="tabbable tabbable-custom tab-content">
    {if {$app->controller->view->groups_m|@count} > 1}
      {$groups=$app->controller->view->groups_m}
      {call group_tabs id_prefix='ga' groups=$groups}
    {/if}
    {call group_tabs_data id_prefix='ga' groups=$groups}
    </div>
</div>
<script>
  $(document).ready(function(){
      function clickGAWButton() { // shows/hides appropriate divs
        $('.gaw-switch .btn-ga').each(function() {
          var div_selector = '#' + this.id.replace('btn-', 'box-');
          if ($(this).hasClass('active') ) {
            $(div_selector).css('display', 'block');
          } else {
            $(div_selector).css('display', 'none');
          }
        });
      }
      clickGAWButton();

      $('.gaw-switch .btn-ga').click(function() {
        //$(this).toggleClass('active');
        $(this).addClass('active').siblings().removeClass('active');
        clickGAWButton();
      });

      $('.btn-add-more-gaw-buy-get').click(function() {
        {call draw_gaw_buy_get_row id_prefix="gaw" assign="buy_get_js_row" groups_id=0 data=false}
        var gaw_buy_get_row = '{$buy_get_js_row|escape:quotes}';
        $('.gaw-buy-get-wrap-' + $(this).attr('data-groups_id')).append(
          gaw_buy_get_row.replace(/\[0\]\[\]/g, '[' + $(this).attr('data-groups_id') + '][' + $('.gaw-buy-get-wrap-' + $(this).attr('data-groups_id') + ' .t-row').length + ']')
        );
        $('.rem-gaw-line').unbind('click').click(function() {
          ($(this).parent()).parent().remove();
        });
      });
      $('.rem-gaw-line').click(function() {
//        if (($(this).parent()).parent().length>1) {
          ($(this).parent()).parent().remove();
/*        } else {
          ($(this).parent()).parent().find()
        }*/
      });

      $(".check_give_away").bootstrapSwitch(
      {
          onSwitchChange: function (element, arguments) {
              var target_id = element.target.id.replace('_switcher_', '_');
              $('#'+target_id).toggle();
              return true;
          },
          onText: "{$smarty.const.SW_ON}",
          offText: "{$smarty.const.SW_OFF}",
          handleWidth: '20px',
          labelWidth: '24px'
      });

  });
</script>