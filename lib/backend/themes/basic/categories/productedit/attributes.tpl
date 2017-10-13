
<div class="btn-box-inv-price after">
  <span class="full-attr-price dis_module" data-value="1">{$smarty.const.TEXT_FULL_PRICE}</span><span class="add-attr-price active" data-value="0">{$smarty.const.TEXT_ADDITIONAL_PRICE}</span>
</div>
<input type="hidden" name="products_price_full" id="full_add_price" value="0"/>
<div class="attr-box-wrap after">
  <div class="attr-box attr-box-1">
    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TAB_ATTRIBUTES}</h4>
        <div class="box-head-serch after">
          <input type="search" value="" id="search-by-attributes" placeholder="{$smarty.const.TAB_SEARCH_ATTR}" class="form-control" />
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content">
        <select class="attr-tree" size="25" name="attributes" ondblclick="addSelectedAttribute()" style="width: 100%; height: 100%; border: none;">
          {foreach $app->controller->view->attributes as $optgroup}
            <optgroup label="{$optgroup['label']}" id="{$optgroup['id']}">
              {foreach $optgroup['options'] as $option}
                <option value="{$option['value']}">{$option['name']}</option>
              {/foreach}
            </optgroup>
          {/foreach}
        </select>
      </div>
    </div>
  </div>
  <div class="attr-box attr-box-2">
    <span class="btn btn-primary" onclick="addSelectedAttribute()"></span>
  </div>
  <div class="attr-box attr-box-3">
    <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_ASSIGNED_ATTR}</h4>
        <div class="box-head-serch after">
          <input type="search" placeholder="{$smarty.const.TEXT_SEARCH_ASSIGNED_ATTR}" class="form-control" />
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content" id="selected_attributes_box">
        {foreach $app->controller->view->selectedAttributes as $option}
          <div class="widget box box-no-shadow js-option" data-option_id="{$option['products_options_id']}">
            <input type="hidden" name="products_option_values_sort_order[{$option['products_options_id']}]" value="{$option['ordered_value_ids']}">
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
                <!--<tr role="row">
                                                    <td class="sort-pointer"></td>
                                                    <td class="img-ast img-ast-img">
                                                        <img src="http://www.trueloaded.co.uk/new/images/w32_sm.jpg" />
                                                    </td>
                                                    <td class="name-ast">
                                                        Grey PVD coated stainless steel
                                                    </td>
                                                    <td class="ast-price ast-price-dis">
                                                        <select class="form-control">
                                                            <option>{$smarty.const.TEXT_EQUAL}</option>
                                                        </select><input type="text" class="form-control" placeholder="&pound;0.00" />
                                                    </td>
                                                    <td class="ast-price ast-weight">
                                                        <select class="form-control">
                                                            <option>+</option>
                                                            <option>-</option>
                                                        </select><input type="text" class="form-control" placeholder="0.00" />
                                                    </td>
                                                    <td class="remove-ast"></td>
                                                </tr>!-->
                </tbody>
              </table>
              <script type="text/javascript">
                $(document).ready(function() {
                  var $sortable_body = $( ".attr-option-{$option['products_options_id']} tbody" );
                  $sortable_body.sortable({
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
                  if ( $sortable_body.find('.js-option-value').length<=1 ) {
                    $sortable_body.sortable('disable');
                  }

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
            </div>
          </div>
        {/foreach}
      </div>
    </div>
  </div>
</div>
{if $app->controller->view->showInventory == true}
  <div class="widget box box-no-shadow inventory-box" style="margin-bottom: 0;">
    <div class="widget-header"><h4>{$smarty.const.BOX_CATALOG_INVENTORY}</h4></div>
    {if \common\helpers\Acl::checkExtension('Inventory', 'allowed')}
    <div class="widget-content widget-inv" id="product-inventory-box">
    {else}
    <div class="widget-content widget-inv dis_module" id="product-inventory-box">
    {/if}    
    </div>
  </div>
{/if}

<script type="text/javascript">
  function addSelectedAttribute() {
    $( 'select[name="attributes"] option:selected' ).each(function() {

      /*var existingFields = document.new_product.getElementsByTagName('select');
       var attributeExists = false;

       for (i=0; i<existingFields.length; i++) {
       if (existingFields[i].name == 'price_prefix[' + document.new_product.attributes.options[document.new_product.attributes.options.selectedIndex].parentNode.id + '][' + document.new_product.attributes.options[document.new_product.attributes.options.selectedIndex].value + ']') {
       attributeExists = true;
       break;
       }
       }*/

      var $opt_group = $(this).parent();
      var products_options = $opt_group.attr('id');
      var products_options_values = $(this).val();

      if ( $( 'input[name="products_attributes_id\['+products_options+'\]\['+products_options_values+'\]"]' ).length ) {
        //already exist
      } else {
        if ($( ".attr-option-"+products_options ).length ) {
          //group exist
          $.post("{Yii::$app->urlManager->createUrl('categories/product-new-attribute')}", { 'products_id': {$pInfo->products_id}, 'products_options_id' : products_options, 'products_options_values_id' : products_options_values }, function(data, status){
            if (status == "success") {
              var $target_tbody = $(".attr-option-"+products_options+" tbody");
              var insert_order_locate = ',';
              $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]"]').each(function(){
                var val_id_match = this.name.match(/products_attributes_id\[\d+\]\[(\d+)\]/);
                if ( val_id_match ) {
                  insert_order_locate = insert_order_locate + val_id_match[1] + ',';
                }
              });
              var after_val_id = '', before_val_id = '', id_pass = false;
              $opt_group.find('option').each(function(){
                if ( before_val_id ) return;
                if ( this.value==products_options_values ) {
                  id_pass = true;
                }else
                if ( insert_order_locate.indexOf(','+this.value+',')!=-1 ){
                  if ( id_pass ) {
                    before_val_id = this.value;
                  }else{
                    after_val_id = this.value;
                  }
                }
              });
              if ( after_val_id ) {
                $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+after_val_id+'\]"]').parents('tr[role="row"]').after(data);
              }else if( before_val_id ) {
                $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+before_val_id+'\]"]').parents('tr[role="row"]').before(data);
              }else {
                $target_tbody.append(data);
              }
              if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                $(".attr-option-"+products_options+" tbody").sortable('enable');
              }
              updateInventoryBox();
            } else {
              alert("Request error.");
            }
          },"html");
        } else {
          //group not exist product-new-option
          $.post("{Yii::$app->urlManager->createUrl('categories/product-new-option')}", { 'products_id': {$pInfo->products_id}, 'products_options_id' : products_options, 'products_options_values_id' : products_options_values }, function(data, status){
            if (status == "success") {
              var insert_order_locate = ',';
              var $added_options = $("#selected_attributes_box .js-option");
              $added_options.each(function(){
                insert_order_locate = insert_order_locate + $(this).attr('data-option_id')+',';
              });
              var after_opt_id = '', before_opt_id = '', id_pass = false;
              $opt_group.parent().find('optgroup').each(function(){
                if ( before_opt_id ) return;
                if ( this.id==products_options ) {
                  id_pass = true;
                }else
                if ( insert_order_locate.indexOf(','+this.id+',')!=-1 ){
                  if ( id_pass ) {
                    before_opt_id = this.id;
                  }else{
                    after_opt_id = this.id;
                  }
                }
              });
              if ( after_opt_id ) {
                $added_options.filter('[data-option_id="'+after_opt_id+'"]').after(data);
              }else if( before_opt_id ) {
                $added_options.filter('[data-option_id="'+before_opt_id+'"]').before(data);
              }else {
                $("#selected_attributes_box").append(data);
              }
              if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                $(".attr-option-"+products_options+" tbody").sortable('enable');
              }else{
                $(".attr-option-"+products_options+" tbody").sortable('disable');
              }
              updateInventoryBox();
            } else {
              alert("Request error.");
            }
          },"html");
        }
      }
      //console.log(products_options);
      //console.log(products_options_values);
      //console.log($( this ).text());
    });

    return false;
  }

  function deleteSelectedAttribute(obj) {
    var optionBox = $(obj).parent().parent();
    var option_value_id = $(obj).parents('.js-option-value').attr('data-option_value_id');;
    var option_id = $(obj).parents('.js-option').attr('data-option_id');;
    $(obj).parent().remove();
    var $sort_input = $('input[name="products_option_values_sort_order['+option_id+']"]');
    if ($sort_input.length>0) $sort_input.val($sort_input.val().replace(','+option_value_id+',',','));
    var findtr = $(optionBox).find('tr');
    if (findtr[0] == undefined) {
      $(optionBox).parent().parent().parent().remove();
    }
    if ( $(".attr-option-"+option_id +" tbody").find('.js-option-value').length==1 ) {
      $(".attr-option-"+option_id +" tbody").sortable('disable');
    }

    updateInventoryBox();
    return false;
  }

  function updateInventoryBox() {
    $('#save_product_form').trigger('attributes_changed');
    $.post("{Yii::$app->urlManager->createUrl('categories/product-inventory-box')}", $('#save_product_form').serialize(), function(data, status){
      if (status == "success") {
        $( "#product-inventory-box" ).html(data);
        $('#save_product_form').trigger('inventory_arrived');
      } else {
        alert("Request error.");
      }
    },"html");

    if ($("table[class^='attr-option-'],table[class*=' attr-option-']").length <= 1) {
      $('.one-attribute').show();
      $('.more-attributes').hide();
      $('.inventory-box').css({
        'height':0,'overflow':'hidden'
      });
    } else {
      $('.one-attribute').hide();
      $('.more-attributes').show();
      $('.inventory-box').css({
        'height':'','overflow':''
      });
    }
  }

  var color = '#ff0000';
  var athighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }
  var atunhighlight = function(obj){
    $(obj).html($(obj).text());
  }
  var atsearch = null;
  var atstarted = false;
  $(document).ready(function() {
    $('#search-by-attributes').on('focus keyup', function(e){
      $('select[name="attributes"]').find('option').parent().hide();
      if ($(this).val().length == 0){
        atstarted = false;
      }
      if (!atstarted && e.type == 'focus'){
        $('select[name="attributes"]').find('option').show();
        $('select[name="attributes"]').find('option').parent().show();
      }
      atstarted = true;
      var str = $(this).val();
      atsearch = new RegExp(str, 'i');
      $.each($('select[name="attributes"]').find('option'), function(i, e){
        atunhighlight(e);
        if (!atsearch.test($(e).text())){
          $(e).hide();
        } else {
          $(e).show();
          $(e).parent().show();
          athighlight(e, str);
        }
      });
    });

    {if $app->controller->view->showInventory == true}
    updateInventoryBox();
    {/if}
  });
</script>