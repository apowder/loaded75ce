				{use class="frontend\design\Block"}
				{use class="frontend\design\Info"}
				{use class="yii\helpers\Url"}
				{use class="common\helpers\Output"}                
<div class="wb-or-prod product_adding">
<form name="cart_quantity" action="{if $params['oID'] > 0}{Url::to(['orders/addproduct', 'orders_id' => $params['oID']])}{else}{Url::to(['orders/addproduct'])}{/if}" method="post" id="product-form" onSubmit="return checkproducts();">
        <input type="hidden" name="currentCart" value="{$currentCart}">
        <div class="popup-heading">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</div>
        <div class="widget-content after bundl-box">
            {if !$params['searchsuggest']}
            <div class="attr-box attr-box-1 oreder-edit-box-1">
                <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                    <div class="widget-header">
                        <h4>{$smarty.const.TEXT_PRODUCTS}</h4>
                        <div class="box-head-serch after search_product">
                            <input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}">
                            <button onclick="return false"></button>
                        </div>
                    </div>
                    <div class="widget-content">
                <ul name="tree" size="20" style="width: 100%;overflow-y: scroll; height: 500px;list-style: none;">
                {foreach $params['category_tree_array'] as $key => $value}
                    {if $value['category'] eq 1}
                        {assign var="parent" value="cat_{$value['id']}"}
                        <li id="{$value['id']}" value="cat_{$value['id']}" class="category_item" disabled level="{$value['level']}">{$value['text']}</li>
                        {$first = false}
                    {else}
                        <li id="{$value['id']}" value="prod_{$value['id']}" parent="cat_{$value['parent_id']}" class="product_item">{$value['text']}</li>
                    {/if}
                {/foreach}
                </ul>
                </div>
                </div>
            </div>
            <div class="attr-box attr-box-3 oreder-edit-box-2">
                <div class="product_holder">
                    <div class="widget box box-no-shadow">
                        <div class="widget-content after">
                            {$smarty.const.TEXT_PRODUCT_NOT_SELECTED}
                        </div>
                    </div>
                </div>
            </div>
            {else}
            <div class="attr-box attr-box-3">
                <table width="100%">
                  <tr>
                    <td>
                     <table border='0' cellpadding=2 cellspacing=0 width="100%">
                      <tr>
                        <td class="label_name">
                          {$smarty.const.HEADING_TITLE_SEARCH_PRODUCTS}
                        </td>
                        <td class="label_value" colspan=2>
                            <div class="f_td_group auto-wrapp"  style="width:100%;">
                                <div class="search_product"><input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}"></div>
                            </div>
                        </td>
                      </tr>
                     </table>
                    </td>
                  </tr>
                  <tr>
                    <td>				
                        <div class="product_holder" style="display:none;">				
                    </div>
                    </td>
                  </tr>
                </table> 	
             </div>
            {/if}
        </div>		
        {tep_draw_hidden_field('action', 'add_product')}
        {tep_draw_hidden_field('orders_id', $params['oID'])}
		<div class="noti-btn three-btn">
		  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
          <div><input type="submit" class="btn btn-confirm btn-save" style="display:none;" value="{$smarty.const.IMAGE_ADD}"></div>
          <div class="btn-center"><span class="btn btn-default btn-reset" style="display:none;">{$smarty.const.TEXT_RESET}</span></div>		  
		</div>	
	</form>
</div>
<script>

var selected_product;
var selected_product_name;
var tree;
(function($){

    {if !$params['searchsuggest']}
        tree = document.querySelector('ul[name=tree]');
        tree.options = [];
        tree.copy = [];
        $.each(tree.children, function(i, e){
            tree.options.push(e);
            tree.copy.push(e.innerHTML);
        });
    {/if}
        function loadProduct(id){
            $.get("{$app->urlManager->createUrl('orders/addproduct')}", {
                            'products_id':id,
                            'orders_id':"{$params['oID']}"
                        }, function (data, status){
                            $('.product_holder').html(data).show();                            
            }, 'html');        
            
        }    
        
        function seachText(text){
                        $.each(tree.options, function(i, e){
                            if ($(e).hasClass('product_item')){ //e.className=
                                if (tree.copy[i].toLowerCase().indexOf(text.toLowerCase()) == -1){
                                    if (selected_product == e.value) $('.append-product').hide();
                                    tree.options[i].hidden = true;
                                } else {
                                    tree.options[i].hidden = false;
                                    var string = tree.copy[i];
                                    var pos = string.search(new RegExp(text, "i"));
                                    tree.options[i].innerHTML = string.substr(0, pos) + '<span style="background-color:#ebef16">' + string.substr(parseInt(pos),text.length) + '</span>' + string.substr(parseInt(pos)+parseInt(text.length));
                                }
                            }
                        });        
        }        
        
        $('.product_item').click(function(){
            $('.product_item.selected').removeClass('selected');
            $(this).addClass('selected');
            selected_product = $(this).attr('value');
            $('.append-product').show();        
        });
        
        $('li.product_item').click(function(){
            var $v = selected_product.split("_");
            loadProduct($v[1]);
        });
        
        $('.search_product').click(function(e){
            if ((e.target.offsetWidth - e.offsetX) < e.target.offsetHeight){
                $('#search_text', this).val('');
                $('#search_text', this).trigger('keyup');
            }
        })
        
        $('#search_text').focus();
		$('#search_text').autocomplete({
			create: function(){
				$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>"+(item.hasOwnProperty('image') && item.image.length>0?"<img src='" + item.image + "' align='left' width='25px' height='25px'>":'')+"<span>" + item.label + "</span>&nbsp;&nbsp;<span class='price'>"+item.price+"</span></a>")
						.appendTo( ul );
					};
			},
			source: function(request, response){
				if (request.term.length > 2){
                    {if $params['searchsuggest']}
                        $.get("{Yii::$app->urlManager->createUrl('orders/addproduct')}", {
                            'search':request.term,
                            'orders_id':"{$params['oID']}",
                        }, function(data){
                            response($.map(data, function(item, i) {
                                return {
                                        values: item.text,
                                        label: item.text,
                                        id: parseInt(item.id),
                                        image:item.image,
                                        price:item.price,
                                    };
                                }));
                        },'json');
                    {else}                   
                        seachText(request.term);
                    {/if}
					
				} else {
                    {if !$params['searchsuggest']}
                    $.each(tree.options, function(i, e){
                            if (e.className == 'product_item'){
                                tree.options[i].hidden = false;
                            }
                    });
                    {/if}
                }
			},
            minLength: 2,
            autoFocus: true,
            delay: 0,
            appendTo: '.auto-wrapp',
            select: function(event, ui) {
				//$("#search_text").val(ui.item.label);
				if (ui.item.id > 0){
					$('.product_name').html(ui.item.label)
                    loadProduct(ui.item.id);					
				}                 
			},
        }).focus(function () {
			$('#search_text').autocomplete("search");  
        });
        
        {if !$params['searchsuggest']}
        $('input[name=search]').keyup(function(){        
            if (!$(this).val().length){
                $.each(tree.options, function(i, e){
                            if (e.className == 'product_item'){
                                tree.options[i].hidden = false;
                                if (tree.options[i].innerHTML != tree.copy[i])
                                    tree.options[i].innerHTML = tree.copy[i];
                            }
                    });   
            }
        })
        {/if}
		
})(jQuery);
</script>