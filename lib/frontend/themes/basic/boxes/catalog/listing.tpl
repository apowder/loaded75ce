{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
{if $fbl}
  {$list_type = Info::listType($settings[0])}
  {$list_type_file = 'boxes/listing-product/'|cat:$list_type|cat:'.tpl'}

  {foreach $products as $product}

    {IncludeTpl::widget(['file' => $list_type_file, 'params' => ['product' => $product, 'settings' => $settings, 'params' => $params, 'languages_id' => $languages_id]])}

  {/foreach}


{else}
  {IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['products' => $products, 'settings' => $settings, 'params' => $params, 'languages_id' => $languages_id]])}
{/if}
