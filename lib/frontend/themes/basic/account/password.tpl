{use class="frontend\design\Info"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}

<h1>{$smarty.const.HEADING_TITLE}</h1>

{$message_account_password}
<div class="middle-form">
<form action="{$account_password_action}" method="post" id="frmAccountPassword">
    <div class="col-full">
        <label for="pass-current">{field_label const="ENTRY_PASSWORD_CURRENT" required_text="*"}</label>
        <input type="password" name="password_current" id="pass-current" data-pattern="{$re1}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR}">
    </div>    
    <div class="col-full">
        <label for="pass-new">{field_label const="ENTRY_PASSWORD_NEW" required_text="*"}</label>
        <input type="password" name="password_new" id="pass-new" data-pattern="{$re1}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR}">
    </div>
    <div class="col-full">
        <label for="pass-confirm">{field_label const="ENTRY_PASSWORD_CONFIRMATION" required_text="*"}</label>
        <input type="password" name="password_confirmation" id="pass-confirm" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation="password_new">
    </div>
    <div class="required requiredM">
        {$smarty.const.FORM_REQUIRED_INFORMATION}
    </div>    
   <div class="center-buttons"><button class="btn-2" type="submit"><span class="button">{$smarty.const.IMAGE_BUTTON_UPDATE}</span></button></div>
</form>
  </div>
   <div class="buttonBox buttonedit">
       <a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a>
   </div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    $('#frmAccountPassword input').validate();
  });
</script>