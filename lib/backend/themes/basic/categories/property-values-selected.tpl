{$properties_hiddens}
{if {$properties_tree_array|@count} > 0}
<div class="product_right_properties">
{foreach $properties_tree_array as $key => $property}
  <ul id="property-{$property['properties_id']}" class="{$property['properties_type']}">
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" valign="top"><span class="pr_count">{$property['throughoutID']}</span> {$property['properties_name']}</li>
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" valign="top">
    {if {$property['values']|@count} > 0}
      {foreach $property['values'] as $value_id => $value}
        <div class="sel_pr_values">
          <span id="value-{$value_id}">{$value}</span>
          <a href="javascript:delPropertyValue({$property['properties_id']}, {$value_id})" class=""><i class="icon-trash"></i></a>
        </div>
      {/foreach}
    {/if}
    </li>
  </ul>
{/foreach}
</div>
{/if}

