{use class="frontend\design\Info"}
{if (((!$product.product_has_attributes || !$settings[0].show_attributes_b2b) && ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart)) || $settings[0].list_demo) && !GROUPS_DISABLE_CHECKOUT}
  {$can_buy = true}
{else}
  {$can_buy = false}
{/if}

{if ((!$product.product_has_attributes && tep_session_is_registered('customer_id')) || $settings[0].list_demo) && !GROUPS_DISABLE_CHECKOUT}
  {$can_save = true}
{else}
  {$can_save = false}
{/if}

<div class="item" id="item-{$product.id}">
  {if !$settings[0].show_image_b2b}
    <div class="image">
      <a href="{$product.link}"><img src="{$product.image}" alt="{str_replace('"', '″', $product.products_name)}" title="{str_replace('"', '″', $product.products_name)}"></a>

      {if !isset($product.price)}
        <span class="sale"></span>
      {/if}
    </div>
  {/if}

  <div class="right-area">

    {if !$settings[0].show_rating_counts_b2b}
      <div class="rating-count">
        ({Info::getProductsRating($product.id, 'count')})
      </div>
    {/if}
    {if !$settings[0].show_rating_b2b}
      <div class="rating">
        <span class="rating-{Info::getProductsRating($product.id)}"></span>
      </div>
    {/if}
    {if !$settings[0].show_price_b2b}
      <div class="price">
        {if isset($product.price)}
          <span class="current">{$product.price}</span>
        {else}
          <span class="old">{$product.price_old}</span>
          <span class="specials">{$product.price_special}</span>
        {/if}
      </div>
    {/if}

    {if !$settings[0].show_qty_input_b2b && $can_buy && Info::pageBlock() != 'product'}
      <div class="qty-input">
        <label>{output_label const="QTY"}</label>
        <input type="text" name="qty[]" value="0" data-zero-init="1" class="qty-inp"{if $product.stock_indicator.quantity_max>0} data-max="{$product.stock_indicator.quantity_max}"{/if} {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_minimal>0} data-min="{$product.order_quantity_data.order_quantity_minimal}" {else}data-min="0" {/if} {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_step>1} data-step="{$product.order_quantity_data.order_quantity_step}"{/if}/>
        <input type="hidden" name="products_id[]" value="{$product.id}"/>
      </div>
    {/if}

    {if (!$settings[0].show_wishlist_button_b2b && $can_save) || !$settings[0].show_view_button_b2b}
    <div class="buttons">
      {/if}
      {*if !$settings[0].show_wishlist_button_b2b && $can_save && Info::pageBlock() != 'product'}
        <div class="button-wishlist">
          <form action="{$product.action_buy}" method="post" class="form-whishlist">
            <input type="hidden" name="products_id" value="{$product.id}"/>
            <input type="hidden" name="add_to_whishlist" value="1"/>
            <button type="submit">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
          </form>
        </div>
      {/if*}
      {if !$settings[0].show_view_button_b2b}
        <div class="button-view">
          <a href="{$product.link}" class="view-button">{$smarty.const.VIEW}</a>
        </div>
      {/if}
      {if (!$settings[0].show_wishlist_button_b2b && $can_save) || !$settings[0].show_view_button_b2b}
    </div>
    {/if}
    {if !$settings[0].show_compare_b2b}
      <div class="compare-box-item">
        <label>
          <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
          <div class="cb_check"><input type="checkbox" name="compare[]" value="{$product.id}" class="checkbox"><span>&nbsp;</span></div>
        </label>
      </div>
    {/if}
  </div>


  <div>
    {if !$settings[0].show_name_b2b}
      <div class="title"><a href="{$product.link}">{$product.products_name}</a></div>
    {/if}
    {if !$settings[0].show_description_b2b}
      <div class="description">{if $product.products_description_short}{$product.products_description_short|truncate:150:"...":false}{else}{$product.products_description|truncate:150:"...":true}{/if}</div>
    {/if}
    {if !$settings[0].show_model_b2b && $product.products_model}
      <div class="products-model"><strong>{$smarty.const.TEXT_MODEL}<span class="colon">:</span></strong> <span>{$product.products_model}</span></div>
    {/if}
    {if !$settings[0].show_properties_b2b}
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
                  <strong>{$property['properties_name']}</strong>
                  {foreach $property['values'] as $value_id => $value}{if $value@index > 0}, {/if}<span>{$value}</span>{/foreach}
                </div>
              {/if}
            {/if}
          {/foreach}
        </div>
      {/if}
    {/if}

    {if !$settings[0].show_attributes_b2b && $can_buy && $product.product_has_attributes}
    <div class="attributes"></div>
    <script type="text/javascript">
      tl(function(){
        update_attributes_list($('#item-{$product.id}'));
      })
    </script>
    {/if}
  </div>


  {if !$settings[0].show_stock_b2b}
    <div class="stock js-stock">
      <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
    </div>
  {/if}

</div>