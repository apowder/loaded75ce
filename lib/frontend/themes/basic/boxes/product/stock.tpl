{use class="Yii"}
{use class="frontend\design\Info"}
{if $stock_indicator}
<div class="stock js-stock">
  <span class="{$stock_indicator.text_stock_code}"><span class="{$stock_indicator.stock_code}-icon">&nbsp;</span>{$stock_indicator.stock_indicator_text}</span>
</div>
{/if}