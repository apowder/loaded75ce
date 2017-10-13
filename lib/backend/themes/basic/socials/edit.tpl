<h4>
    {$title} ({$social->module})
</h4>
<div>
			  {if {$messages|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}

    <form name="social_form" method="post" action="socials/save">
    <input type="hidden" name="platform_id" value="{$platform_id}">
    <input type="hidden" name="socials_id" value="{$socials_id}">
    <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs top_tabs_ul main_tabs">
                {if $social->hasAuth}
                <li class="active"><a href="#tab_main" data-toggle="tab"><span>Autorization</span></a></li>
                {/if}
                {assign var=i value=0}
                {foreach $social->addon_settings as $block => $values}
                <li class="{if !$social->hasAuth && !$i}active{/if}"><a href="#tab_{$block}" data-toggle="tab"><span>{ucfirst($block)}</span></a></li>
                {$i++|void}
                {/foreach}
              </ul>
              <div class="tab-content">
              {if $social->hasAuth}
                <div class="tab-pane active topTabPane tabbable-custom" id="tab_main">
                      <div class="tabbable tabbable-custom">   
                          <div class="tab-inserted">
                                  <div class="template_cell">
                                    <div class="main_title">Client ID</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[auth][client_id]", $social->client_id, ['class' => 'form-control'])}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">Client Secret</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[auth][client_secret]", $social->client_secret, ['class' => 'form-control'])}</div>
                                  </div>
                                  <center><a class="btn btn-primary popup" href="{$test_url}">{$smarty.const.IMAGE_TEST}</a></center>
                                  <a class="message"></a>
                          </div>
                      </div>
                </div>
                {/if}
                {assign var=i value=0}
                {foreach $social->addon_settings as $block => $values}
                 <div class="tab-pane topTabPane tabbable-custom {if !$social->hasAuth && !$i}active{/if}" id="tab_{$block}">
                      <div class="tabbable tabbable-custom">
                          <div class="tab-inserted">                            
                                {foreach $values as $key => $info}
                                <div class="template_cell">
                                    <div class="main_title">{$info['description']}</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[`$block`][`$key`]", $info['value'], ['class' => 'form-control'])}</div>
                                </div>
                            {/foreach}
                          </div>
                      </div>
                </div>
                {$i++|void}
                {/foreach}
              </div>
    {if $socials_id}
        <input type="hidden" name="module" value="{$social->module}">        
    {/if}    
    <div class="btn-bar">
        <div class="btn-left"><a href="{\yii\helpers\Url::to(['socials/', 'platform_id' => $platform_id])}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
    </form>
</div>
<script>
           (function($){
                 $("a.popup").popUp();   
            })(jQuery)
            </script>
            