{use class="frontend\design\Info"}
{if ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart || $settings[0].list_demo) && !GROUPS_DISABLE_CHECKOUT}
  {$can_buy = true}
{else}
  {$can_buy = false}
{/if}
{if ((!$product.product_has_attributes && tep_session_is_registered('customer_id')) || $settings[0].list_demo) && !GROUPS_DISABLE_CHECKOUT}
  {$can_save = true}
{else}
  {$can_save = false}
{/if}
<div class="item">
  {if !$settings[0].show_image}
    <div class="image">
      <a href="{$product.link}"><img src="{$product.image}" alt="{str_replace('"', '″', $product.products_name)}" title="{str_replace('"', '″', $product.products_name)}"></a>

      {if !isset($product.price)}
        <span class="sale"></span>
      {/if}
    </div>
  {/if}
  {if !$settings[0].show_stock}
    <div class="stock">
      <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
    </div>
  {/if}

  <div class="name">
  {if !$settings[0].show_name}
    <div class="title"><a href="{$product.link}">{$product.products_name}</a></div>
  {/if}
  {if !$settings[0].show_description}
    <div class="description">{if $product.products_description_short}{$product.products_description_short|truncate:90:"...":false}{else}{$product.products_description|truncate:90:"...":true}{/if}</div>
  {/if}
  {if !$settings[0].show_model && $product.products_model}
    <div class="products-model"><strong>{$smarty.const.TEXT_MODEL}<span class="colon">:</span></strong> <span>{$product.products_model}</span></div>
  {/if}
  {if !$settings[0].show_properties}
    {if {$product.properties|@count} > 0}
      <div class="properties">
        {foreach $product.properties as $key => $property}
          {if {$property['values']|@count} > 0}
            {if $property['properties_type'] == 'flag' && $property['properties_image']}
              <div class="property-image">
                {if $property['values'][1] == 'Yes'}
                  <span class="hover-box">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                  <span class="hover-box-content">
                    <strong>{$property['properties_name']}</strong>
                    {\common\helpers\Properties::get_properties_description($property['properties_id'], $languages_id)}
                  </span>
                </span>
                {else}
                  <span class="disable">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                </span>
                {/if}
              </div>
            {else}
              <div class="property">
                <strong>{$property['properties_name']}<span class="colon">:</span></strong>
                {foreach $property['values'] as $value_id => $value}{if $value@index > 0}, {/if}<span>{$value}</span>{/foreach}
              </div>
            {/if}
          {/if}
        {/foreach}
      </div>
    {/if}

  {/if}
  </div>
  <div class="add-height">
  {if !$settings[0].show_rating_counts}
    <div class="rating-count">
    ({Info::getProductsRating($product.id, 'count')})
    </div>
  {/if}
  {if !$settings[0].show_rating}
  <div class="rating">
    <span class="rating-{Info::getProductsRating($product.id)}"></span>
  </div>
  {/if}
  {if !$settings[0].show_price}
    <div class="price">
      {if isset($product.price)}
        <span class="current">{$product.price}</span>
      {else}
        <span class="old">{$product.price_old}</span>
        <span class="specials">{$product.price_special}</span>
      {/if}
    </div>
  {/if}

  {if !$settings[0].show_qty_input && $can_buy && Info::pageBlock() != 'product'}
    {if $product.product_has_attributes}
    <form action="{$product.link}" method="post">
    {else}
    <form action="{$product.action_buy}" method="post" class="form-buy">
      <div class="qty-input">
        {if $can_buy}
          <label>{output_label const="QTY"}</label>
          <input type="text" name="qty" value="1" class="qty-inp"{if $product.stock_indicator.quantity_max>0} data-max="{$product.stock_indicator.quantity_max}"{/if}{if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'setLimit')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($product.order_quantity_data)}{/if}{if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'setLimit')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($product.order_quantity_data)}{/if} />
          <input type="hidden" name="products_id" value="{$product.id}"/>
        {/if}
      </div>
    {/if}
  {/if}
  {if !$settings[0].show_buy_button}
    <div class="buy-button">
      {if $can_buy}
        {if !$settings[0].show_qty_input && Info::pageBlock() != 'product'}
          <button type="submit" class="btn-1 btn-buy add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></button>
        {else}
          {if $product.product_has_attributes}
            <a href="{$product.link}" class="btn-1 btn-cart add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
          {else}
            <a href="{$product.link_buy}" class="btn-1 btn-buy add-to-cart" rel="nofollow" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
          {/if}
        {/if}
        <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class=" btn-1 btn-cart in-cart" rel="nofollow" title="{$smarty.const.TEXT_IN_YOUR_CART}"{if !$product.product_in_cart} style="display: none"{/if}></a>
      {/if}
    </div>
  {/if}
  {if !$settings[0].show_qty_input && $can_buy && Info::pageBlock() != 'product'}
    </form>
  {/if}
  </div>
  {if (!$settings[0].show_wishlist_button) || !$settings[0].show_view_button}
    <div class="buttons">
  {/if}
  {if !$settings[0].show_wishlist_button}
    <div class="button-wishlist">
      {if $can_save && Info::pageBlock() != 'product'}
      <form action="{$product.action_buy}" method="post" class="form-whishlist">
        <input type="hidden" name="products_id" value="{$product.id}"/>
        <input type="hidden" name="add_to_whishlist" value="1"/>
        <button type="submit">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
      </form>
      {/if}
    </div>
  {/if}
  {if !$settings[0].show_view_button}
    <div class="button-view">
      <a href="{$product.link}" class="view-button">{$smarty.const.VIEW}</a>
    </div>
  {/if}
  {if (!$settings[0].show_wishlist_button && (!$product.product_has_attributes || $settings[0].list_demo)) || !$settings[0].show_view_button}
    </div>
  {/if}
  {if !$settings[0].show_compare}
    <div class="compare-box-item">
      <label>
        <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
        <div class="cb_check"><input type="checkbox" name="compare[]" value="{$product.id}" class="checkbox"><span>&nbsp;</span></div>
      </label>
    </div>
  {/if}
</div>