{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->


<div id="email_management_edit">
  <form id="save_email_form" name="new_email" onSubmit="return saveEmail();">
    <div class="">
      <h4>{$email_templates_key}</h4>

      {if $isMultiPlatforms}
      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          {foreach $platforms as $platform}
            <li class="{if $platform['id']==$default_platform_id}active {/if}">
              <a href="#platform_{$platform['id']}" data-toggle="tab">{$platform['text']}</a>
            </li>
          {/foreach}
        </ul>
        <div class="tab-content">
          {/if}
          {foreach $platforms as $platform}
          <div class="tab-pane{if $platform['id']==$default_platform_id} active {/if} topTabPane tabbable-custom" id="platform_{$platform['id']}">




            <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_{$platform['id']}_2" data-toggle="tab">{$smarty.const.TEXT_EMAIL_TEMPLATE_HTML}</a></li>
                <li><a href="#tab_{$platform['id']}_3" data-toggle="tab">{$smarty.const.TEXT_EMAIL_TEMPLATE_TEXT}</a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active topTabPane tabbable-custom" id="tab_{$platform['id']}_2">
                    {if count($languages) > 1}
                  <ul class="nav nav-tabs under_tabs_ul">
                    {foreach $languages as $lKey => $lItem}
                      <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$platform['id']}_html_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                  </ul>
                  {/if}
                  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $cDescriptionHtml[$platform['id']] as $mKey => $mItem}
                      <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$platform['id']}_html_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_SUBJECT}</td>
                            <td class="label_value">{$mItem['email_templates_subject']}</td>
                          </tr>
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_TEMPLATES_KEYS}</td>
                            <td class="label_value"><a href="{$app->urlManager->createUrl('email/templates-keys')}?id_ckeditor={$mItem['c_link']}" class="btn popupLinks">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                          </tr>
                          <tr>
                            <td valign="top" class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_BODY}</td>
                            <td class="label_value">{$mItem['email_templates_body']}</td>
                          </tr>
                        </table>
                      </div>
                    {/foreach}
                  </div>

                </div>
                <div class="tab-pane topTabPane tabbable-custom" id="tab_{$platform['id']}_3">

                  <ul class="nav nav-tabs under_tabs_ul">
                    {foreach $languages as $lKey => $lItem}
                      <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$platform['id']}_text_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                  </ul>
                  <div class="tab-content">
                    {foreach $cDescriptionText[$platform['id']] as $mKey => $mItem}
                      <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$platform['id']}_text_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_SUBJECT}</td>
                            <td class="label_value">{$mItem['email_templates_subject']}</td>
                          </tr>
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_TEMPLATES_KEYS}</td>
                            <td class="label_value"><a href="{$app->urlManager->createUrl('email/templates-keys')}?id_ckeditor={$mItem['c_link']}" class="btn popupLinks">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                          </tr>
                          <tr>
                            <td valign="top" class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_BODY}</td>
                            <td class="label_value">{$mItem['email_templates_body']}</td>
                          </tr>
                        </table>
                      </div>
                    {/foreach}
                  </div>

                </div>
              </div>
            </div>




          </div>
          {/foreach}
          {if $isMultiPlatforms}
        </div>
      </div>
      {/if}

      <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_BACK}</a></div>
        <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
      </div>
    </div>
    {tep_draw_hidden_field( 'email_templates_id', $email_templates_id )}
    {tep_draw_hidden_field( 'platform_id', $selected_platform_id )}
  </form>
</div>
<script type="text/javascript">
  function insertAtCaret(areaId, text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var caretPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0, caretPos);
    var back = (txtarea.value).substring(txtarea.selectionEnd, txtarea.value.length);
    txtarea.value = front + text + back;
    caretPos = caretPos + text.length;
    txtarea.selectionStart = caretPos;
    txtarea.selectionEnd = caretPos;
    txtarea.focus();
    txtarea.scrollTop = scrollPos;
  }
  function saveEmail() {
    ckeplugin();
    $.post("{$app->urlManager->createUrl('email/templates-save')}", $('#save_email_form').serialize(), function (data, status) {
      if (status == "success") {
        $('#email_management_edit').html(data);
        CKEDITOR.replaceAll('ckeditor');
      } else {
        alert("Request error.");
      }
    }, "html");

    return false;
  }
  function backStatement() {
    window.history.back();
    return false;
  }
  $(window).load(function(){
    $('.popupLinks').popUp({		
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_TEMPLATES_KEYS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"		
    });
  })
</script>
