
<div class="tabbable tabbable-custom">
  {if count($languages) > 1}
    <ul class="nav nav-tabs">
      {foreach $languages as $lKey => $lItem}
        <li{if $lKey == 0} class="active"{/if}><a href="#tab_2_{$lItem['id']}" class="flag-span" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
      {/foreach}
    </ul>
  {/if}
  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $pDescription  as $dKey => $dItem}
      <div class="tab-pane{if $dKey == 0} active{/if}" id="tab_2_{$dItem['id']}">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_NAME}</label>
          {$dItem['products_name']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION}</label>
          {$dItem['products_description']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION_SHORT}</label>
          {$dItem['products_description_short']}
        </div>
      </div>
    {/foreach}
  </div>
</div>