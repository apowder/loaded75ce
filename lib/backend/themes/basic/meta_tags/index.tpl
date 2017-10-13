
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

           <!--=== Page Content ===-->


							<div class="widget-content metatags_div">
                <form name="meta_tags" action="{$update_form_action}" method="post" {if $isMultiPlatform}class="wtabplform"{/if}>
              <!-- TABS-->
              {if $isMultiPlatform}<div class="tab-radius">{/if}
                <div class="tabbable tabbable-custom tabbable-ep">
                  <ul class="nav nav-tabs">
                    {foreach $tabs_data as $tab_data}
                      <li {if $tab_data.active}class="active"{/if}><a href="#{$tab_data.id}" data-toggle="tab"><span>{$tab_data.tab_title}</span></a></li>
                    {/foreach}
                  </ul>
                  <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
                    {foreach $tabs_data as $tab_data}
                    <div id="{$tab_data.id}" class="tab-pane {if $tab_data.active} active{/if}">

                      {if $isMultiPlatform}
                      <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs tab-light-gray {if $isMultiPlatform && false}tab-radius-ul tab-radius-ul-white{/if}">
                          {foreach $platforms as $platform}
                          <li {if $first_platform_id==$platform['id']} class="active"{/if}><a href="#{$tab_data.id}_{$platform['id']}" data-toggle="tab"><span>{$platform['text']}</span></a></li>
                          {/foreach}
                        </ul>
                      {/if}

                        <div {if $isMultiPlatform}class="tab-content"{/if}>
                          {foreach $platforms as $platform}
                          <div id="{$tab_data.id}_{$platform['id']}" class="tab-pane {if $first_platform_id==$platform['id']}active{/if}">
                            <div class="tabbable tabbable-custom">
                                {if count($languages) > 1}
                              <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                              {foreach $languages as $lang_idx=>$lang}<li{if $lang_idx == 0} class="active"{/if}><a href="#{$tab_data.id}_{$platform['id']}_{$lang['id']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>{/foreach}
                              </ul>
                              {/if}
                              <div class="tab-content {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                                {foreach $languages as $lang_idx=>$lang}
                                  <div class="tab-pane{if $lang_idx == 0} active{/if}" id="{$tab_data.id}_{$platform['id']}_{$lang['id']}">

                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                      {foreach $tab_data.input_controls[$lang['id']|cat:'_'|cat:$platform['id']] as $cp_inp}
                                        <tr>
                                          <td class="bigText">{$cp_inp.label}</td>
                                          <td class="bigText">{$cp_inp.control}</td>
                                        </tr>
                                      {/foreach}
                                    </table>


                                  </div>
                                {/foreach}
                              </div>
                            </div>
                          </div>
                          {/foreach}
                        </div>

                      {if $isMultiPlatform}
                      </div>
                      {/if}
                    </div>
                    {/foreach}
                  </div>

                </div>
                                                      {if $isMultiPlatform}  </div>{/if}
              <!--END TABS-->
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary" value="{$smarty.const.IMAGE_UPDATE}" onClick="return updateMetaTags()">
                                                                </p>
                                                                  <script type="text/javascript">
function updateMetaTags() {
  $.post("meta_tags/update", $('form[name=meta_tags]').serialize(), function(data, status){
    if (status == "success") {
      $('.widget-content').slideUp();
      $('.widget-content').slideDown();
    } else {
        alert("Request error.");
    }
},"html");
  return false;
}
                                    
                                                                  </script>
                </form>
							</div>