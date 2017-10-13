<h1>{$smarty.const.HEADING_TITLE}</h1>

{$messages_password_forgotten}

<p>{$smarty.const.TEXT_MAIN}</p>

<form name="password_forgotten" action="{$account_password_forgotten_action}" method="post" id="frmPasswordForgotten">

  <table class="tableForm">
    <tr>
      <td width="20%"><label for="email">{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label></td>
      <td><input type="email" name="email_address" value="{$email_address|escape:'html'}" id="email"></td>
    </tr>
  </table>

  <div class="buttons">
    <div class="right-buttons"><button class="btn-1" type="submit">{$smarty.const.IMAGE_BUTTON_CONTINUE}</button></div>
    <div class="left-buttons"><a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
  </div>

</form>

