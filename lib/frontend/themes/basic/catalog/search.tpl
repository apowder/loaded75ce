{$value=''}

{foreach $list as $item}

  {if $item.type != $type}
    <strong>{$item.type}</strong>
    {$type = $item.type}
  {/if}
    <a href="{$item.link}" class="item">
      <span class="image">{if isset($item.image)}<img src="{$item.image}" alt="">{/if}</span>
      <span class="name">{$item.title}</span>
    </a>

{/foreach}