<div class="wb-or-prod edit_product_popup edit_product_popup1">
<form name="cart_quantity" action="{\yii\helpers\Url::to(['orders/addproduct', 'orders_id' => $params['oID']])}" method="post" id="product-form" onSubmit="return checkproducts();">
    <input type="hidden" name="currentCart" value="{$currentCart}">
    <div class="widget box box-no-shadow" style="border: none;">
        <div class="popup-heading">{$smarty.const.T_EDIT_PROD}</div>
        <div class="widget-content">				
			<div >
               {$this->render('product_details', ['params' => $params, 'cart' => $cart, 'tax_class_array' => $tax_class_array])}
            </div>								
        </div>
        {tep_draw_hidden_field('action', 'add_product')}
        {tep_draw_hidden_field('orders_id', $params['oID'])}
		<div class="noti-btn three-btn">
		  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
          <div><input type="submit" class="btn btn-confirm btn-save" value="{$smarty.const.IMAGE_SAVE}"></div>
          <div class="btn-center"><span class="btn btn-default btn-reset" >{$smarty.const.TEXT_RESET}</span></div>		  
		</div>		

    </div>
</form>
</div>