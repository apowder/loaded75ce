{use class="frontend\design\Info"}
{use class="frontend\design\boxes\ReCaptchaWidget"}
{if $info|count > 0}
  {foreach $info as $_info}
  <div class="info">{$_info}</div>
  {/foreach}
{/if}

{if $action == 'success'}

  <div style="text-align: center; font-size: 20px; margin: 20px 0 100px">{$smarty.const.TEXT_MESSAGE_IS_SENT}</div>

{else}
<form action="{$link}" method="post" id="contact-form">
<div class="contact-info form-inputs">
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_NAME" required_text="*"}</span>
      <input type="text" name="name" value="" data-required="{$smarty.const.NAME_REQUIRED}">
    </label>
  </div>
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_EMAIL" required_text="*"}</span>
      <input type="email" name="email" value="" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email">
    </label>
  </div>
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_ENQUIRY" required_text="*"}</span>
      <textarea name="enquiry" cols="30" rows="10" data-required="{$smarty.const.ENQUIRY_REQUIRED}"></textarea>
    </label>
  </div>
  {if $captcha_enabled && $settings[0]['show_captcha'] eq 'on'}
    {ReCaptchaWidget::widget()}
  {/if}
  <div class="buttons">
    <div class="right-buttons"><button type="submit" class="btn">{$smarty.const.CONTINUE}</button></div>
  </div>
</div>
</form>

{/if}


<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('#contact-form input, #contact-form textarea').validate();
  });
</script>