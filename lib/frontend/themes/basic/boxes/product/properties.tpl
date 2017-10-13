<div class="product-properties">
  {if $products_data.manufacturers_name && $settings.show_manufacturer != 'no'}
  <ul class="properties-table">
    <li class="propertiesName"><strong>{$smarty.const.TEXT_MANUFACTURER}</strong></li>
    <li class="propertiesValue">
      {if $products_data.manufacturers_link}
      <a href="{$products_data.manufacturers_link}" title="{$products_data.manufacturers_name|escape:'html'}"><span itemprop="brand">{$products_data.manufacturers_name}</span></a>
      {else}
      <span itemprop="brand">{$products_data.manufacturers_name}</span>
      {/if}
    </li>
  </ul>
  {/if}

  {if $settings.show_model != 'no'}
  <ul class="properties-table js_prop-block{if !$products_data.products_model} js-hide{/if}">
    <li class="propertiesName"><strong>{$smarty.const.TEXT_MODEL}</strong></li>
    <li class="propertiesValue js_prop-products_model" itemprop="model">{$products_data.products_model}</li>
  </ul>
  {/if}

  {if $products_data.products_ean && $settings.show_model != 'no'}
    <ul class="properties-table{if !$products_data.products_ean} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong>{$smarty.const.TEXT_EAN}</strong></li>
      <li class="propertiesValue js_prop-products_ean">{$products_data.products_ean}</li>
    </ul>
  {/if}
  {if $products_data.products_isbn && $settings.show_model != 'no'}
    <ul class="properties-table{if !$products_data.products_isbn} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong>{$smarty.const.TEXT_ISBN}</strong></li>
      <li class="propertiesValue js_prop-products_isbn">{$products_data.products_isbn}</li>
    </ul>
  {/if}
{if {$properties_tree_array|@count} > 0}

  <div itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
{foreach $properties_tree_array as $key => $property}
  <ul id="property-{$property['properties_id']}" class="{$property['properties_type']}" itemprop="value" itemscope itemtype="http://schema.org/PropertyValue">
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}"><strong><span itemprop="name">{$property['properties_name']}</span></strong></li>
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}">
    {if {$property['values']|@count} > 0}
      {foreach $property['values'] as $value_id => $value}
        <div class="sel_pr_values">
          <span id="value-{$value_id}" itemprop="value">{$value}</span>
        </div>
      {/foreach}
    {/if}
    </li>
  </ul>
{/foreach}
  </div>

{/if}
</div>
