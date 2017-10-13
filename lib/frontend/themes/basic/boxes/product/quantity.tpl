{use class="Yii"}
{use class="frontend\design\Info"}
{if \common\helpers\Acl::checkExtension('PackUnits', 'checkQuantityFrontend')}
{if $product.pack_unit > 0 || $product.packaging > 0}
<div class="qty_packs"{if !$show_quantity_input} style="display: none"{/if}>
<div class="qty-input">
<div class="qty_t">{$smarty.const.UNIT_QTY}:</div>
  <div class="input">
  {if $product.pack_unit > 0 || $product.packaging > 0}
    {$order_quantity_data.order_quantity_minimal = 0}
  {/if}
        <span class="price_1">{$single_price['unit']}</span>
        <input type="text" name="qty_[0]" value="{if $qty != ''}0{else}1{/if}" class="qty-inp check-spec-max"  data-type="unit" {if $stock != false} data-max="{$stock}"{/if} data-min="{$order_quantity_data.order_quantity_minimal}"  {if $order_quantity_data.order_quantity_step>1} data-step="{$order_quantity_data.order_quantity_step}"{/if}>
        <input type="hidden" name="qty[0]" value="0" class="depended" data-type="unit" data-stepmareed="{$order_quantity_data.order_quantity_step}"/>
				<span class="qty_price"></span>			
  </div>
  </div>

{if $product.pack_unit > 0}
<div class="qty-input">
	<div class="qty_t">{$smarty.const.PACK_QTY}:<span>({$product.pack_unit} items)</span></div>
  <div class="input inps">
        <span class="price_1">{$single_price['pack']}</span>
        <input type="text" name="qty_[1]" value="0" class="qty-inp check-spec-max" data-type="pack_unit" data-min="0"  {if $stock != false} data-max="{floor($stock/$product.pack_unit)}"{/if} >
        <input type="hidden" name="qty[1]" value="0" class="depended"  {if $product.pack_unit>0} data-step="1"{/if} data-stepmareed="{$product.pack_unit}" />
				<span class="qty_price"></span>
  </div>
  </div>
{/if}
{if $product.packaging > 0}
<div class="qty-input">
	<div class="qty_t">{$smarty.const.CARTON_QTY}:<span>({$product.packaging * $product.pack_unit} items)</span></div>
  <div class="input inps">
        <span class="price_1">{$single_price['package']}</span>
        <input type="text" name="qty_[2]" value="0" class="qty-inp"  data-type="packaging" data-min="0" {if $stock != false} data-max="{floor($stock/$product.packaging)}"{/if} >
        <input type="hidden"  name="qty[2]" value="0" class="depended" data-min="0" {if $product.packaging>0} data-step="1"{/if}  data-stepmareed="{$product.packaging * $product.pack_unit}"/>
				<span class="qty_price"></span>
  </div>
	</div>
{/if}
<div class="total-qty after">
	<div class="qty_t">{$smarty.const.TEXT_TOTAL}:</div>
  <div class="input inps">
			<span >{$smarty.const.QTY}:</span>
      <span class="price_2" id="total_qty"></span>
      <span class="qty_price" id="total_sum"></span>
  </div>
</div>
</div>
{else}
<div class="qty-input"{if !$show_quantity_input} style="display: none"{/if}>
  <label for="qty">{output_label const="QTY"}</label>
  <div class="input">
    <input type="text" id="qty" name="qty" value="{if $qty != ''}{$qty}{else}1{/if}" class="qty-inp"{if $quantity_max>0} data-max="{$quantity_max}"{/if}{if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'setLimit')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($order_quantity_data)}{/if}{if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'setLimit')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($order_quantity_data)}{/if} />
  </div>
</div>
{/if}
<script type="text/javascript"> 
    tl('{Info::themeFile('/js/main.js')}', function(){
      
        $('input.qty-inp').quantity();
      
        $('input.qty-inp').on('check_quantity keyup', function(){
          var obj = $(this);
          $.post('catalog/get-price',{
            'pid':'{$product.products_id}',
            'qty': $(this).val(),
            'type': $(this).data('type'),
            'qty_[0]': $('input[name="qty_[0]"]').val(),
            'qty_[1]': $('input[name="qty_[1]"]').val(),
            'qty_[2]': $('input[name="qty_[2]"]').val(),
            
          }, function(data, status){
            if (status == 'success'){
                var _p = obj.val() * obj.parent().next().data('stepmareed');
                if (_p > 0 && $(this).data('type') != 'unit') {
                  _p = '<span class="pr_attr">' + data.type + '</span>';
                } else {
                  _p = '';
                }
                obj.parent().next().val(obj.val()* obj.parent().next().data('stepmareed'));
                obj.parent().next().next().html( data.price);    
							
                if(obj.parent().find('.pr_attr').length > 0){
                        obj.parent().find('.pr_attr').html(_p); 
                }else{
                        obj.parent().append(_p);
                }
              obj.parent().prev().html( data.single_price);
              $('#total_sum').text(data.total_price);
              $('#total_qty').text(data.total_quantity);
            }
          }, 'json');
        })
      
    })
</script>
{else}
<div class="qty-input">
  <label for="qty">{output_label const="QTY"}</label>
  <div class="input">
    <input type="text" id="qty" name="qty" value="{if $qty != ''}{$qty}{else}1{/if}" class="qty-inp"{if $quantity_max>0} data-max="{$quantity_max}"{/if}{if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'setLimit')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($order_quantity_data)}{/if}{if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'setLimit')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($order_quantity_data)}{/if} />
  </div>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('input.qty-inp').quantity();
  })
</script>
{/if}