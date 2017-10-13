{foreach $addresses as $keyvar => $address}
<div class="default_add_check">
    <input type="checkbox" name="sacrifice_address_id[]" value="{$address.id}">
    <label>{$address.text}</label>
</div>
{/foreach}