<h3>{$smarty.const.BOX_HEADING_MANUFACTURERS}</h3>
<div class="brands-listing after">
  {foreach $brands as $brand}
    {if $brand.img != 'no'}
      <div class="item"><a href="{Yii::$app->urlManager->createUrl(['catalog', 'manufacturers_id'=>$brand.manufacturers_id])}"><img src="{$brand.img}" alt="{$brand.manufacturers_name}"></a></div>
    {else}
      {*<div class="item">{$brand.manufacturers_name}</div>*}
    {/if}
  {/foreach}
</div>