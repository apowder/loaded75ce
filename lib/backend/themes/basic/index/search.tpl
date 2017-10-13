{foreach $list as $item}
    <a href="javascript:void(0)" onclick="return searchSuggestSelected({$item.id}, '{$item.value}');" class="item">
        <span class="suggest_table">
            <span class="td_image">{if isset($item.image)}<img src="{$item.image}" alt="">{/if}</span>
            <span class="td_name">{$item.title}</span>
            <span class="td_price">{$item.price}</span>
        </span>
    </a>
{/foreach}