
<div class="brands-listing">
  {foreach $brands as $brand}


    {if $brand.img != 'no'}
      <div class="item"><a data-href="{$brand.link}"><img src="{$brand.img}" alt="{$brand.manufacturers_name}"></a></div>
    {else}
      <div class="item"><a data-href="{$brand.link}">{$brand.manufacturers_name}</a></div>
    {/if}
  {/foreach}
</div>