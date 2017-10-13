{foreach $attributes as $option}
<div class="widget box box-no-shadow js-option" data-option_id="{$option['products_options_id']}">
    <input type="hidden" name="products_option_values_sort_order[{$option['products_options_id']}]" value="">
    <div class="widget-header">
        <h4>{$option['products_options_name']}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">
        <table class="table assig-attr-sub-table attr-option-{$option['products_options_id']}">
            <thead>
                <tr role="row">
                    <th></th>
                    <th>{$smarty.const.TEXT_IMG}</th>
                    <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                    <th>{$smarty.const.TEXT_PRICE}</th>
                    <th>{$smarty.const.TEXT_WEIGHT}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {foreach $option['values'] as $value}
                <tr role="row" class="js-option-value" data-option_value_id="{$value['products_options_values_id']}">
                    <td class="sort-pointer"></td>
                    {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock')}
                      {\common\extensions\AttributesImages\AttributesImages::productBlock($option, $value)}
                    {else} 
                        <td class="img-ast dis_module">
                            <div id="AdminSettns" class="int-upload">
                                <select class="divselktr divselktr-{$option['products_options_id']}" disabled />
                            </div>
                        </td>
                    {/if}
                    <td class="name-ast-short">
                      {$value['products_options_values_name']}
                      <input type="hidden" name="products_attributes_id[{$option['products_options_id']}][{$value['products_options_values_id']}]" value="{$value['products_attributes_id']}" />
                    </td>
                    <td class="ast-price ast-price-w ast-price-dis">
                          <select name="price_prefix[{$option['products_options_id']}][{$value['products_options_values_id']}]" class="form-control">
                              <option value="+"{if $value['price_prefix'] == '+'} selected{/if}>+</option>
                              <option value="-"{if $value['price_prefix'] == '-'} selected{/if}>-</option>
                          </select>
                          <div>
                              <div class="prtt"><div class="prtt2">
                                      <span>
                                          {if {$app->controller->view->groups|@count} > 0}<b>Main</b>{/if}<input type="text" name="products_attributes_price[{$option['products_options_id']}][{$value['products_options_values_id']}][0]" value="{$value['prices'][0]}" class="form-control" placeholder="&pound;0.00" />
                                      </span>

                                      {if {$app->controller->view->groups|@count} > 0}
                                          {foreach $app->controller->view->groups as $groups_id => $group}
                                              <span><b>{$group['groups_name']}</b><input type="text" name="products_attributes_price[{$option['products_options_id']}][{$value['products_options_values_id']}][{$groups_id}]" value="{$value['prices'][$groups_id]}" class="form-control" placeholder="&pound;0.00" /></span>
                                              {/foreach}
                                          {/if}
                                  </div></div>
                          </div>
                      </td>
                      <td class="ast-price ast-weight">
                          <select name="products_attributes_weight_prefix[{$option['products_options_id']}][{$value['products_options_values_id']}]" class="form-control">
                              <option value="+"{if $value['products_attributes_weight_prefix'] == '+'} selected{/if}>+</option>
                              <option value="-"{if $value['products_attributes_weight_prefix'] == '-'} selected{/if}>-</option>
                          </select><input name="products_attributes_weight[{$option['products_options_id']}][{$value['products_options_values_id']}]" value="{$value['products_attributes_weight']}" type="text" class="form-control" placeholder="0.00" />
                      </td>
                <td class="remove-ast" onclick="deleteSelectedAttribute(this)"></td>
            </tr>
            {/foreach}
        </tbody>
    </table>
<script type="text/javascript">
$(document).ready(function() {
   $( ".attr-option-{$option['products_options_id']} tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
     update: function( event, ui ) {
       var order_ids = [''];
       $(this).find('.js-option-value').each(function() {
         order_ids.push($(this).attr('data-option_value_id'));
       });
       order_ids.push('');
       $('.js-option[data-option_id="{$option['products_options_id']}"]').find('input[name="products_option_values_sort_order[{$option['products_options_id']}]"]').val(order_ids.join(','));
     }
   });
    
    $('.divselktr-{$option['products_options_id']}').multiselect({
        multiple: true,
        height: '205px',
        header: 'See the images in the rows below:',
        noneSelectedText: 'Select',
        selectedText: function(numChecked, numTotal, checkedItems){
          return numChecked + ' of ' + numTotal;
        },
        selectedList: false,
        show: ['blind', 200],
        hide: ['fade', 200],
        position: {
            my: 'left top',
            at: 'left bottom'
        }
    });
    
    $('.widget .toolbar .widget-collapse').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                    // Open Widget
                    $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                    widget_content.slideDown(200, function() {
                            widget.removeClass('widget-closed');
                    });
                    widget_chart.slideDown(200);
                    divider.slideDown(200);
            } else {
                    // Close Widget
                    $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                    widget_content.slideUp(200, function() {
                            widget.addClass('widget-closed');
                    });
                    widget_chart.slideUp(200);
                    divider.slideUp(200);
            }
    });
    
});
</script>
    </div>
</div>
{/foreach}