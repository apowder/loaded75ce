{use class="frontend\design\Info"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
{use class="frontend\design\Info"}
<h1>{$smarty.const.HEADING_TITLE}</h1>
<div class="middle-form">
<form action="{$action}" method="post" id="accountEdit">
    <input type="hidden" name="action" value="{$process}">
{$account_array['message']}
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="company">{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</label>
    {if in_array(ACCOUNT_COMPANY, ['required', 'required_register'])}
    <input id="company" type="text" name="company" value="{$customers_company|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_COMPANY_ERROR}"/>
    {else}
    <input id="company" type="text" name="company" value="{$customers_company|escape:'html'}"/>
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="company_vat">{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT_ID"}</label>
    {if in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register'])}
    <input id="company_vat" type="text" name="company_vat" value="{$customers_company_vat|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_VAT_ID_ERROR}"/>
    {else}
    <input id="company_vat" type="text" name="company_vat" value="{$customers_company_vat|escape:'html'}"/>
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-full col-gender">    
    <span>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</span>
    <input type="radio" name="gender" value="m"{if $custom_gender == 'm'} checked{/if} id="male">
    <label for="male">{$smarty.const.T_MR}</label>    
    <input type="radio" name="gender" value="f"{if $custom_gender == 'f'} checked{/if} id="female">
    <label for="female">{$smarty.const.T_MRS}</label>
    <input type="radio" name="gender" value="s"{if $custom_gender == 's'} checked{/if} id="miss">
    <label for="miss">{$smarty.const.T_MISS}</label>
</div>
{/if}
{if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="firstname">{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])}
    <input type="text" name="firstname" id="firstname" value="{$firstname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}">
    {else}
    <input type="text" name="firstname" id="firstname" value="{$firstname|escape:'html'}">
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="lastname">{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
    <input type="text" name="lastname" id="lastname" value="{$lastname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}">
    {else}
    <input type="text" name="lastname" id="lastname" value="{$lastname|escape:'html'}">
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-left">
    <label for="phone">{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
    <input type="text" name="telephone" id="phone" value="{$telephone|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}">
    {else}
    <input type="text" name="telephone" id="phone" value="{$telephone|escape:'html'}">
    {/if}
</div>
{/if}
{if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-right">
    <label for="fax-number">{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
    <input type="text" name="landline" id="fax-number" value="{$landline|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}">
    {else}
    <input type="text" name="landline" id="fax-number" value="{$landline|escape:'html'}">
    {/if}
</div>   
{/if}
<div class="col-full">
    <label for="email">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
    <input type="email" name="email_address" id="email" value="{$email_address|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_EMAIL_ADDRESS_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_EMAIL_ADDRESS_ERROR}">
</div>
{if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])}
<div class="col-full dob-input">
    <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"}</label>
    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
    <input type="text" name="dob" id="dob" value="{$customers_dob|escape:'html'}" class="datepicker" data-pattern="{$re1}4{$re2}" data-required="{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}">
    {else}
    <input type="text" name="dob" id="dob" value="{$customers_dob|escape:'html'}" class="datepicker">
    {/if}
</div>
{/if}

<div class="required requiredM">{$smarty.const.FORM_REQUIRED_INFORMATION}</div>
<div class="center-buttons"><button type="submit" class="btn-2"><span class="button">{$smarty.const.IMAGE_BUTTON_UPDATE}</span></button></div>
</form>
</div>
 <div class="buttonBox buttonedit"><div class="button1"></div><div class="button2"><a class="btn" href="{$back_link}">{$smarty.const.IMAGE_BUTTON_BACK}</a></div></div>
 <script type="text/javascript">
   tl([
     '{Info::themeFile('/js/main.js')}'
   ], function(){
     $('#accountEdit input').validate();
   });


   tl(['{Info::themeFile('/js/bootstrap.min.js')}',
     '{Info::themeFile('/js/bootstrap-datepicker.js')}'
   ], function(){
     $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap.css')}">')
             .prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');

     $.fn.datepicker.dates.current={
       days:["{$smarty.const.TEXT_SUNDAY}","{$smarty.const.TEXT_MONDAY}","{$smarty.const.TEXT_TUESDAY}","{$smarty.const.TEXT_WEDNESDAY}","{$smarty.const.TEXT_THURSDAY}","{$smarty.const.TEXT_FRIDAY}","{$smarty.const.TEXT_SATURDAY}"],
       daysShort:["{$smarty.const.DATEPICKER_DAY_SUN}","{$smarty.const.DATEPICKER_DAY_MON}","{$smarty.const.DATEPICKER_DAY_TUE}","{$smarty.const.DATEPICKER_DAY_WED}","{$smarty.const.DATEPICKER_DAY_THU}","{$smarty.const.DATEPICKER_DAY_FRI}","{$smarty.const.DATEPICKER_DAY_SAT}"],
       daysMin:["{$smarty.const.DATEPICKER_DAY_SU}","{$smarty.const.DATEPICKER_DAY_MO}","{$smarty.const.DATEPICKER_DAY_TU}","{$smarty.const.DATEPICKER_DAY_WE}","{$smarty.const.DATEPICKER_DAY_TH}","{$smarty.const.DATEPICKER_DAY_FR}","{$smarty.const.DATEPICKER_DAY_SA}"],
       months:["{$smarty.const.DATEPICKER_MONTH_JANUARY}","{$smarty.const.DATEPICKER_MONTH_FEBRUARY}","{$smarty.const.DATEPICKER_MONTH_MARCH}","{$smarty.const.DATEPICKER_MONTH_APRIL}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUNE}","{$smarty.const.DATEPICKER_MONTH_JULY}","{$smarty.const.DATEPICKER_MONTH_AUGUST}","{$smarty.const.DATEPICKER_MONTH_SEPTEMBER}","{$smarty.const.DATEPICKER_MONTH_OCTOBER}","{$smarty.const.DATEPICKER_MONTH_NOVEMBER}","{$smarty.const.DATEPICKER_MONTH_DECEMBER}"],
       monthsShort:["{$smarty.const.DATEPICKER_MONTH_JAN}","{$smarty.const.DATEPICKER_MONTH_FEB}","{$smarty.const.DATEPICKER_MONTH_MAR}","{$smarty.const.DATEPICKER_MONTH_APR}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUN}","{$smarty.const.DATEPICKER_MONTH_JUL}","{$smarty.const.DATEPICKER_MONTH_AUG}","{$smarty.const.DATEPICKER_MONTH_SEP}","{$smarty.const.DATEPICKER_MONTH_OCT}","{$smarty.const.DATEPICKER_MONTH_NOV}","{$smarty.const.DATEPICKER_MONTH_DEC}"],
       today:"{$smarty.const.TEXT_TODAY}",
       clear:"{$smarty.const.TEXT_CLEAR}",
       weekStart:1
     };

     $('.datepicker').datepicker({
       startView: 3,
       format: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
       language: 'current'
     });
   });
</script>