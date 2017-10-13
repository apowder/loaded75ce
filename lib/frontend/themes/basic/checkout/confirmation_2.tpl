<div class="page-confirmation">
  <form action="{$form_action_url}" method="post" name="checkout_confirmation" id="frmCheckoutConfirm">
    {if $is_shipable_order}
  <div class="col-left">
    <div class="heading-4">{$smarty.const.SHIPPING_ADDRESS}{*<a href="{$shipping_address_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
    <div class="confirm-info">
      {$address_label_delivery}
    </div>
  </div>
    {/if}
  <div class="{if $is_shipable_order}col-right{else}col-left{/if}">
    <div class="heading-4">{$smarty.const.TEXT_BILLING_ADDRESS}{*<a href="{$billing_address_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
    <div class="confirm-info">
      {$address_label_billing}
    </div>
  </div>
    {if $is_shipable_order}
  <div class="col-left">
    <div class="heading-4">{$smarty.const.SHIPPING_METHOD}{*<a href="{$shipping_method_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
    <div class="confirm-info">
      {$order->info['shipping_method']}
    </div>
  </div>
    {/if}
  <div class="col-right">
    <div class="heading-4">{$smarty.const.PAYMENT_METHOD}{*<a href="{$payment_method_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
    <div class="confirm-info">
      <strong>{$order->info['payment_method']}</strong>
      {if $payment_confirmation}
        <br>
        {if $payment_confirmation.title}
          {$payment_confirmation.title}<br>
        {/if}
        {if isset($payment_confirmation.fields) && is_array($payment_confirmation.fields)}
          <table>
          {foreach $payment_confirmation.fields as $payment_confirmation_field}
            <tr>
            <td>{$payment_confirmation_field.title}</td><td>{$payment_confirmation_field.field}</td>
            </tr>
          {/foreach}
          </table>
        {/if}
      {/if}
      {*Credit card<br>
      <strong>Credit Card:</strong> Visa Electron<br>
      <strong>Owner:</strong>	     Vladislav Malyshev<br>
      <strong>Number:</strong>        1111XXXXXXXXXX4444<br>
      <strong>Expiry Date:</strong>  January, 2023*}
    </div>
  </div>


  <div class="buttons">
    <div class="right-buttons">
      <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
    </div>
  </div>

  <div class="heading-4">{$smarty.const.PRODUCT_S}{*<a href="{$cart_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>

  {use class="frontend\design\boxes\cart\Products"}
  {Products::widget(['type'=> 3])}



  <div class="price-box">
    {include file="totals.tpl"}
  </div>

  <div class="buttons" style="overflow: hidden">
    <div class="right-buttons">
      <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
    </div>
  </div>
  {$payment_process_button_hidden}
  </form>
</div>