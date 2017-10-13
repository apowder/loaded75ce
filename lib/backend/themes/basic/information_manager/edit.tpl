{use class="yii\helpers\Html"}
<div class="top-btn after">
    <div><a href="javascript:void(0);" onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
    {if $information_id>0}
      <div><a href="javascript:void(0);" class="btn btn-delete" onclick="return confirmDeleteInfoPage({$information_id})">{$smarty.const.IMAGE_DELETE}</a></div>
    {/if}
</div>
<!-- TABS-->
<form id="infoPage_form" name="infoPage_edit" onSubmit="return saveInfoPage();">
    {if $isMultiPlatform}<div class="tab-radius">{/if}
<input type="hidden" name="information_id" value="{$information_id}">
<div class="tabbable tabbable-custom tabbable-ep">
    <ul class="nav nav-tabs nav-tabs-big {if $isMultiPlatform}tab-radius-ul{/if}">
        {if $isMultiPlatforms}<li><a href="#platforms" data-toggle="tab"><span>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</span></a></li>{/if}
        <li class="active"><a href="#main" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
        <li><a href="#seo" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
    </ul>
    <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
        {if $isMultiPlatforms}
        <div class="tab-pane topTabPane tabbable-custom" id="platforms">
            <div class="filter_pad {if $isMultiPlatform}tab_edt_page_pl{/if}" style="padding: 0;">
                <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_PAGE_ASSIGN}</th>
                        {if $some_need_login}<th>{$smarty.const.SHOW_FOR_NON_LOGGED}</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $platforms as $platform}
                        <tr>
                            <td>{$platform['text']}</td>
                            <td><input type="checkbox" name="visible[{$platform['id']}]" value="1" class="check_on_off" {if $pages_data[$platform['id']]['visible']} checked{/if}></td>
                          {if $some_need_login}
                            <td>
                              {if $platform.need_login}<input type="checkbox" name="no_logged[{$platform['id']}]" value="1" class="check_on_off" {if $pages_data[$platform['id']]['no_logged']} checked{/if}>{/if}
                            </td>
                          {/if}
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        {else}
            {foreach $platforms as $platform}
                <input type="hidden" name="visible[{$platform['id']}]" value="{$pages_data[$platform['id']]['visible']}">
            {/foreach}
        {/if}
        <input type="hidden" name="visible_per_platform" value="1">

        <div class="tab-pane topTabPane tabbable-custom active" id="main">

            {if $isMultiPlatforms}
                <ul class="nav nav-tabs {if $isMultiPlatform}tab-radius-ul tab-radius-ul-white{/if}">
                    {foreach $platforms as $pKey => $platform}
                        <li{if $platform['id'] == $first_platform_id} class="active"{/if}><a href="#tab_{$platform['id']}" data-toggle="tab"><span>{$platform['logo']}<span>{$platform['text']}</span></span></a></li>
                    {/foreach}
                </ul>
            {/if}
            {if $isMultiPlatforms}<div class="tab-content">{/if}

            {foreach $pages_data as $page_data}
                {if $isMultiPlatforms}
                 <div class="tab-pane topTabPane tabbable-custom{if $page_data['platform_id'] == $first_platform_id} active{/if}" id="tab_{$page_data['platform_id']}">
                {/if}
                {if count($languages) > 1}
            <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$page_data['platform_id']}_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
            </ul>
            {/if}
            <div class="tab-content {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $page_data.lang as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$page_data['platform_id']}_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TITLE_PAGE_TITLE}</td>
                                <td class="label_value">{$mItem['c_page_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NAME_IN_MENU}</td>
                                <td class="label_value">{$mItem['c_info_title']}</td>
                            </tr>
														<tr>
                                <td class="label_name">{$smarty.const.TEXT_DESCRIPTION_LINKS}</td>
                                <td class="label_value">{$mItem['c_links']}<div class="info_desc_links">{$smarty.const.TEXT_INFO_DESC_LINKS}</div></td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.DESCRIPTION_INFORMATION}</td>
                                <td class="label_value">{$mItem['c_description']}</td>
                            </tr>
                        </table>
                    </div>
                {/foreach}
            </div>
                {if $isMultiPlatforms}
                         </div>
                {/if}
            {/foreach}
                {if $isMultiPlatforms}</div>{/if}
            </div>

        <div class="tab-pane topTabPane tabbable-custom" id="seo">
            {if $isMultiPlatforms}
                <ul class="nav nav-tabs {if $isMultiPlatform}tab-radius-ul tab-radius-ul-white{/if}">
                    {foreach $platforms as $pKey => $platform}
                        <li{if $platform['id'] == $first_platform_id} class="active"{/if}><a href="#seo_tab_{$platform['id']}" data-toggle="tab"><span>{$platform['logo']}<span>{$platform['text']}</span></span></a></li>
                    {/foreach}
                </ul>
            {/if}

            {if $isMultiPlatforms}<div class="tab-content">{/if}

            {foreach $pages_data as $page_data}
                {if $isMultiPlatforms}
                   <div class="tab-pane topTabPane tabbable-custom{if $page_data['platform_id'] == $first_platform_id} active{/if}" id="seo_tab_{$page_data['platform_id']}">
                {/if}
                {if count($languages) > 1}
            <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#seo_tab_{$page_data['platform_id']}_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
            </ul>
            {/if}
            <div class="tab-content seoTab {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $page_data.lang as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="seo_tab_{$page_data['platform_id']}_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TITLE_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['c_seo_page_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.META_TAG_TITLE}</td>
                                <td class="label_value">{$mItem['c_meta_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.INFO_DESCRIPTION_META_TAG}</td>
                                <td class="label_value">{$mItem['c_meta_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.INFO_KEYWORDS}</td>
                                <td class="label_value">{$mItem['c_meta_key']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['c_old_seo_page_name']}
                                <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
                                {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                                <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
                                {/if}
                                </td>
							</tr>
                            {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                               {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderInfo($information_id, $mItem['languages_id'], $mItem['platform_id'])}
                            {/if}
                        </table>
                         <script>
                      $(document).ready(function(){
                        $('body').on('click', "#seo_tab_{$page_data['platform_id']}_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#seo_tab_{$page_data['platform_id']}_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });
                        
                      })
                      </script>
                    </div>
                {/foreach}
            </div>
                {if $isMultiPlatforms}
                    </div>
                {/if}
            {/foreach}
                {if $isMultiPlatforms}</div>{/if}
            </div>
    </div>
</div>
{if $isMultiPlatform}</div>{/if}
<div class="btn-bar" style="padding: 0;">
    <div class="btn-left">
        <a href="javascript:void(0);" onclick="return backStatement();" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
    </div>
    <div class="btn-right">
        {if $information_id > 0}
           <span class="btn btn btn-delete" style="margin-right: 15px;" onClick="return confirmDeleteInfoPage({$information_id})">{$smarty.const.IMAGE_DELETE}</span>
        {/if}        
        <button class="btn btn-confirm" type="submit">{$smarty.const.IMAGE_SAVE}</button>
    </div>
</div>            
</form>
<!--END TABS-->
<script type="text/javascript">
    function backStatement() {
        {if $app->controller->view->usePopupMode}
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        {else}
        window.history.back();
        {/if}
        return false;
    }

    function confirmDeleteInfoPage(info_id) {

    }
function saveInfoPage(){
    ckeplugin();
    $.post("{Yii::$app->urlManager->createUrl('information_manager/page-save')}", $('#infoPage_form').serialize(), function(data, status){
        if (status == "success") {
            if ( data.indexOf('<textarea')!==-1 ) {
                $('.content-container').html(data);
                CKEDITOR.replaceAll('ckeditor');
            }else {
                $('.content-container').prepend(data);
            }
        } else {
            alert("Request error.");
        }
    },"html");

    //$('input[name=categories_image_loaded]').val();

    return false;
}
    function confirmDeleteInfoPage(id) {
        bootbox.dialog({
            message: "{$smarty.const.JS_DELETE_PAGE_TEXT}",
            title: "{$smarty.const.JS_DELETE_PAGE_HEAD}",
            buttons: {
                success: {
                    label: "{$smarty.const.TEXT_BTN_YES}",
                    className: "btn-delete",
                    callback: function () {
                        $.post("information_manager/delete", { 'info_id': id }, function (data, status) {
                            if (status == "success") {
                                window.history.back();
                                /*
                                var table = $('.table').DataTable();
                                table.draw(false);
                                resetStatement();*/
                            } else {
                                alert("Request error.");
                            }
                        }, "html");
                    }
                },
                main: {
                    label: "{$smarty.const.TEXT_BTN_NO}",
                    className: "btn-cancel",
                    callback: function () {
                        //console.log("Primary button");
                    }
                }
            }
        });
        return;
    }
$(document).ready(function() {
    $('.check_on_off').bootstrapSwitch( {
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    } );

    $(window).resize(function(){
	//var lab_url = $('.edp-line .input-group-addon').width() + 14;
        //$('.input-width-url').css({ 'padding-left' : lab_url + 10, 'margin-left' : '-' + lab_url });
    });
    $(window).resize();
});
</script>
<script>
$(window).load(function(){
    $('.popupLinks').popUp({		
			box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"		
		});
		$('.popupLinks').on('click', function(){
			$('.popup-heading').text($(this).text());
		})
})
</script>



