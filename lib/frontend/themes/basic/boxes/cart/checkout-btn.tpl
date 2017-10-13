<a href="{$link}" class="btn-2 btn-to-checkout">{$smarty.const.PROCEED_TO_CHECKOUT}</a>
{if $paypal_link neq ''}
<br/><br/>
  <center>
  {$paypal_link}
  </center>
{/if}