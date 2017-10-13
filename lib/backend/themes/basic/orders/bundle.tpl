<h2 class="bundle_title">{$smarty.const.TEXT_PRODUCTS_BUNDLE}</h2>
<div class="bundle-listing">
	<div class="bundle_row after">
  {foreach $products['bundle_products'] as $product name=bundles}
	 {if $smarty.foreach.bundles.index % 2 == 0 && $smarty.foreach.bundles.index != 0}
	</div><div class="bundle_row after">
	{/if}
    <div class="bundle_item">
      <div class="bundle_image" style="min-height:40px;"><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}" width="50px" align="left">
          <div class="bundle_name" style="padding:5px 5px 5px 55px;">        
          {$product.products_name}
          </div>
      </div>
      <div class="right-area-bundle">        
        <div class="bundle_attributes w-line-row-2 w-line-row-22 after">
          {foreach $product.attributes_array as $item}
            <div>
            <div class="edp-line">
                <label>{$item.title}:</label>
                <div>
                  <select name="{$item.name}" data-required="{$smarty.const.PULL_DOWN_DEFAULT} {$product.products_name|escape:'html'} - {$item.title}" onchange="update_bundle_attributes(this.form);" class="form-control">
                    <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
                    {foreach $item.options as $option}
                      <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                    {/foreach}
                  </select>
                </div>
              </div>
            </div>
          {/foreach}
        </div>
        {*
        <div class="bundle_qty">
          {$product.num_product} {$smarty.const.TEXT_ITEMS}
        </div>
        <div class="bundle_price">
          {if $product.price}
            <span class="current">{$product.price}</span>
          {else}
            <span class="old">{$product.price_old}</span>
            <span class="specials">{$product.price_special}</span>
          {/if}
        </div>*}
      </div>
    </div>
  {/foreach}

</div>
</div>
<script type="text/javascript">
  function update_bundle_attributes(theForm) {
    if ( typeof update_attributes === 'function' ) return update_attributes(theForm);
  }
</script>
