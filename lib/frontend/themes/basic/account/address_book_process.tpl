{use class="frontend\design\Info"}
<h1>{$title}</h1>
<div class="middle-form">
{if !$get_delete}<form action="{$action}" method="post" id="addressProcess">{/if}
	{$message}
{if $get_delete}
    <div class="info">{$smarty.const.DELETE_ADDRESS_DESCRIPTION}</div>
  <div class="deleteAddress">
      {$address_label}
  </div>
	<div class="center-buttons"><a class="btn-2" href="{$link_address_delete}">{$smarty.const.IMAGE_BUTTON_DELETE}</a></div>
	{else}
{if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
 <div class="col-full col-gender">
     <span>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</span>
     <input type="radio" name="gender" value="m" {if $entry_gender == 'm'} checked{/if}><label for="male">{$smarty.const.T_MR}</label>
     <input type="radio" name="gender" value="f" {if $entry_gender == 'f'} checked{/if}><label for="female">{$smarty.const.T_MRS}</label>
     <input type="radio" name="gender" value="s" {if $entry_gender == 's'} checked{/if}><label for="miss">{$smarty.const.T_MISS}</label>
 </div>
 {/if}  
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
{if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="firstname">{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])}
    <input type="text" name="firstname" id="firstname" value="{$entry_firstname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}">
    {else}
    <input type="text" name="firstname" id="firstname" value="{$entry_firstname|escape:'html'}">
    {/if}
</div>
{/if}  
{if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="lastname">{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
    <input type="text" name="lastname" id="lastname" value="{$entry_lastname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}">
    {else}
    <input type="text" name="lastname" id="lastname" value="{$entry_lastname|escape:'html'}">
    {/if}
</div>
{/if}  
{if in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="post-code">{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</label>
    {if in_array(ACCOUNT_POSTCODE, ['required', 'required_register'])}
    <input type="text" name="postcode" value="{$entry_postcode|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, $smarty.const.ENTRY_POSTCODE_MIN_LENGTH)}"/>
    {else}
    <input type="text" name="postcode" value="{$entry_postcode|escape:'html'}"/>
    {/if}
</div>
{/if}  
{if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="street">{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</label>
    {if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register'])}
    <input type="text" name="street_address_line1" value="{$street_address_line1|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, $smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH)}"/>
    {else}
    <input type="text" name="street_address_line1" value="{$street_address_line1|escape:'html'}"/>
    {/if}
</div>
{/if}  
{if in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="suburb">{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</label>
    {if in_array(ACCOUNT_SUBURB, ['required', 'required_register'])}
    <input type="text" name="street_address_line2" value="{$entry_suburb|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_SUBURB_ERROR}"/>
    {else}
    <input type="text" name="street_address_line2" value="{$entry_suburb|escape:'html'}"/>
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="city">{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</label>
    {if in_array(ACCOUNT_CITY, ['required', 'required_register'])}
    <input type="text" id="city" name="city" value="{$city|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_CITY_ERROR, $smarty.const.ENTRY_CITY_MIN_LENGTH)}"/>
    {else}
    <input type="text" id="city" name="city" value="{$city|escape:'html'}"/>
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
      <label for="state">{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
{if $process == true}
{if $entry_state_has_zones}
	<select name="zone_id">
    {foreach $zones_array as $zone}
      <option value="{$zone.id}"{if $state == $zone.id} selected{/if}>{$zone.text}</option>
    {/foreach}
  </select>
{else}
<input type="text" name="state" value="">
{/if}
{else}
<input type="text" name="state" value="{$get_zone_name|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_STATE_ERROR, $smarty.const.ENTRY_STATE_MIN_LENGTH)}"/>
{/if}
</div>
{/if}
{if in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="country">{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</label>
    {$country}
</div>
{/if}
{if $set_primary == 1}
<div class="col-full col-gender">
    <input type="checkbox" value="on"  name="primary" id="primary" class="checkbox"><label for="primary">{$smarty.const.SET_AS_PRIMARY}</label>
</div>
{/if}
<div class="required requiredM">{$smarty.const.FORM_REQUIRED_INFORMATION}</div>
<div class="center-buttons">{$links.update}</div>
{/if}
{if !$get_delete}</form>{/if}
 </div>
 <div class="buttonBox buttonedit"><div class="button2"><a class="btn" href="{$links.back_url}">{$links.back_text}</a></div></div>
<script type="text/javascript">
  tl(['{Info::themeFile('/js/main.js')}', '{Info::themeFile('/js/jquery-ui.min.js')}'], function(){
    $('#addressProcess input').validate();
//    console.log(222);
    $('input[name="state"]').autocomplete({
			appendTo: $('input[name="state"]').parent(),
      source: function(request, response) {
        $.getJSON("{Yii::$app->urlManager->createUrl('account/address-state')}", { term : request.term, country: $('select[name="country"]').val() }, response);
      },
      minLength: 0,
      autoFocus: true,
      delay: 0,
      open: function (e, ui) {
        if ($(this).val().length > 0) {
          var acData = $(this).data('ui-autocomplete');
          acData.menu.element.find('a').each(function () {
            var me = $(this);
            var keywords = acData.term.split(' ').join('|');
            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
          });
        }
      },
      select: function( event, ui ) {
        setTimeout(function(){
          $('input[name="state"]').trigger('change');
        }, 200)
      }
    }).focus(function () {
      $(this).autocomplete("search");
    });
  });
</script>