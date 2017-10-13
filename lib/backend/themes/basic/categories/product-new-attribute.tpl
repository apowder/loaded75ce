{foreach $options as $option}
<tr role="row" class="js-option-value" data-option_value_id="{$option['products_options_values_id']}">
    <td class="sort-pointer"></td>
      {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock')}
	{\common\extensions\AttributesImages\AttributesImages::productBlock($option, $option)}
      {else} 
          <td class="img-ast dis_module">
              <div id="AdminSettns" class="int-upload">
                  <select class="divselktr divselktr-{$option['products_options_id']}" disabled />
              </div>
          </td>
      {/if}
    <td class="name-ast-short">
        {$option['products_options_values_name']}
        <input type="hidden" name="products_attributes_id[{$products_options_id}][{$option['products_options_values_id']}]" value="{$option['products_attributes_id']}" />
    </td>
    <td class="ast-price ast-price-w ast-price-dis">
        <select name="price_prefix[{$option['products_options_id']}][{$option['products_options_values_id']}]" class="form-control">
            <option value="+"{if $option['price_prefix'] == '+'} selected{/if}>+</option>
            <option value="-"{if $option['price_prefix'] == '-'} selected{/if}>-</option>
        </select>
        <div>
            <div class="prtt"><div class="prtt2">
                    <span>
                        {if {$app->controller->view->groups|@count} > 0}<b>Main</b>{/if}<input type="text" name="products_attributes_price[{$option['products_options_id']}][{$option['products_options_values_id']}][0]" value="{$option['prices'][0]}" class="form-control" placeholder="&pound;0.00" />
                    </span>

                    {if {$app->controller->view->groups|@count} > 0}
                        {foreach $app->controller->view->groups as $groups_id => $group}
                            <span><b>{$group['groups_name']}</b><input type="text" name="products_attributes_price[{$option['products_options_id']}][{$option['products_options_values_id']}][{$groups_id}]" value="{$option['prices'][$groups_id]}" class="form-control" placeholder="&pound;0.00" /></span>
                            {/foreach}
                        {/if}
                </div></div>
        </div>
    </td>
    <td class="ast-price ast-weight">
        <select name="products_attributes_weight_prefix[{$option['products_options_id']}][{$option['products_options_values_id']}]" class="form-control">
            <option value="+"{if $option['products_attributes_weight_prefix'] == '+'} selected{/if}>+</option>
            <option value="-"{if $option['products_attributes_weight_prefix'] == '-'} selected{/if}>-</option>
        </select><input name="products_attributes_weight[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="{$option['products_attributes_weight']}" type="text" class="form-control" placeholder="0.00" />
    </td>
    
    
    <td class="remove-ast" onclick="deleteSelectedAttribute(this)"></td>
</tr>
{/foreach}
<script type="text/javascript">
$(document).ready(function() {
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
});
</script>
