<div style="clear:both;"></div>
{*if $shippingMethod = $klarnaCheckout->getShippingMethod()}
<p align="right"><strong>{$shippingMethod['title']} {$currencies->format($shippingMethod['cost'])}</strong></p>
{/if*}
{if (!isset($_SESSION['klarna_error']))}
    {$klarnaOrder['gui']['snippet']}
{else}
    <div class="klarna-error">
        {$_SESSION['klarna_error']}
    </div>
{/if}