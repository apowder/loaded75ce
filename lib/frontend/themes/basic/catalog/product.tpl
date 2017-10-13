{use class="frontend\design\Block"}
<form name="cart_quantity" action="{$action}" method="post" id="product-form">
  <input type="hidden" name="products_id" value="{$products_prid}"/>
  <div class="product" itemscope itemtype="http://schema.org/Product">
    {Block::widget(['name' => $page_name, 'params' => ['type' => 'product', 'params' => ['message' => $message]]])}
  </div>
</form>