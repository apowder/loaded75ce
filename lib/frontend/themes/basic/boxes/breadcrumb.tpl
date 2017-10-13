{if $breadcrumb}
<div class="catalog-breadcrumb">
  {if $settings[0].show_text}<div class="breadcrumbs-text">{$smarty.const.TEXT_BEFORE_BREADCRUMBS}</div>{/if}
  <ul itemscope itemtype="http://schema.org/BreadcrumbList">
  {foreach $breadcrumb as $item}
    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
      {if $item.link != ''}
      <a itemprop="item" href="{$item.link}">
        <span itemprop="name">{$item.name}</span>
      </a>
      {else}
        <span itemprop="item">
          <span itemprop="name">{$item.name}</span>
        </span>
      {/if}
      <meta itemprop="position" content="{$item@iteration}" />
    </li>
  {/foreach}
  </ul>
</div>
{/if}