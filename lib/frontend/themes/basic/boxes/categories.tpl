{use class="frontend\design\Info"}
<div class="categories">
  {foreach $categories as $category}<div class="item">
      <a href="{$category.link}">
    {if $category.img != 'no'}
      <span class="image" style="background-image: url('{$category.img}')"></span>
    {else}
      <span class="image" style="background-image: url('{Info::themeFile("/img/no-categories-image.gif")}')"></span>
    {/if}
        <span class="name">{$category.categories_name}</span>
      </a>
    </div>{/foreach}
</div>
