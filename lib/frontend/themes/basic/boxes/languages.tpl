<div class="languages">
    <div class="current">
      {foreach $languages as $language}
        {if $language.id == $languages_id}
          {$language.image}
        {/if}
      {/foreach}
    </div>
    <div class="select">
      {foreach $languages as $language}
        {if $language.id != $languages_id}
          <a href="{$language.link}">{$language.image}</a>
        {/if}
      {/foreach}
    </div>
</div>