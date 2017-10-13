{use class="yii\helpers\Html"}
{if $app->controller->view->contentAlreadyLoaded == 0}
<div class="brandEditPage popupEditCat">
{/if}
<form id="save_brand_form" name="brand_edit" onSubmit="return saveManufacturer();">
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_2" data-toggle="tab">{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
            <li><a href="#tab_4" data-toggle="tab">{$smarty.const.TEXT_SEO}</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active topTabPane tabbable-custom" id="tab_2">
                    <div class="tab-pane active">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_NAME}</td>
                                <td class="label_value">{Html::input('text', 'manufacturers_name', $mInfo->manufacturers_name, ['class' => 'form-control'])}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_IMAGE}</td>
                                <td class="label_value">
                                    
                                    
            <div id="gallery-filedrop" class="gallery-filedrop-container">
                <div class="gallery-filedrop">
                    <span class="gallery-filedrop-message">{$smarty.const.TEXT_DRAG_DROP} <a href="#gallery-filedrop" class="gallery-filedrop-fallback-trigger btn" rel="nofollow">{$smarty.const.TEXT_CHOOSE_FILE}</a> {$smarty.const.TEXT_FROM_COMPUTER}</span>
                    <input size="30" id="gallery-filedrop-fallback" name="manufacturers_image" class="elgg-input-file hidden" type="file">

                    <div class="gallery-filedrop-queue">
                        <img id="manufacturer_logo" src="" style="display: none;">
                    </div>

                </div>
                <div class="hidden" id="image_wrapper">
                    <div class="gallery-template">
                        <div class="gallery-media-summary">
                            <div class="gallery-album-image-placeholder">
                                <img src="">
                                <span class="elgg-state-uploaded"></span>
                                <span class="elgg-state-failed"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                                    
                                    
                                    <!--<img id="manufacturer_logo" src="">!-->
                                    <div class="md_value"><span class="cat_upload_img">{$mInfo->manufacturers_image}</span></div>
                                    <div class="js-image-actions brand_remove {if !$mInfo->manufacturers_image}hide-default{/if}">
                                        <input type="checkbox" name="remove_image" id="brand_img_remove">
                                        <label for="brand_img_remove">{$smarty.const.TEXT_PRODUCTS_IMAGE_REMOVE_SHORT}</label>
                                        <input type="checkbox" name="delete_image" id="brand_img_delete">
                                        <label for="brand_img_delete">{$smarty.const.TEXT_PRODUCTS_IMAGE_DELETE_SHORT}</label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_4">
                {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul> 
                {/if}
                <div class="tab-content seoTab {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $mDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_URL}</td>
                                <td class="label_value">{$mItem['manufacturers_url']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_SEO_NAME}</td>
                                <td class="label_value">{$mItem['manufacturers_seo_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_META_DESCRIPTION}</td>
                                <td class="label_value">{$mItem['manufacturers_meta_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_META_KEYWORDS}</td>
                                <td class="label_value">{$mItem['manufacturers_meta_key']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_META_TITLE}</td>
                                <td class="label_value">{$mItem['manufacturers_meta_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</td>
                                <td class="label_value">{Html::input('text', 'manufacturers_old_seo_page_name', $mInfo->manufacturers_old_seo_page_name, ['class' => 'form-control seo-input-field'])}
                                <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
                                  {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                                  <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
                                  {/if}
                                </td>
                            </tr>
                            {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                                {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderBrand($manufacturers_id, $mItem['code'])}
                            {/if}
                        </table>
                         <script>
                      $(document).ready(function(){
                        $('body').on('click', "#tab_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#tab_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });
                        
                        $('input[name=manufacturers_old_seo_page_name]').change(function(){
                            $('input[name=manufacturers_old_seo_page_name]').val($(this).val());
                        })
                      })
                      </script>
                    </div>        
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
</div>
{tep_draw_hidden_field( 'manufacturers_id', $manufacturers_id )}
{tep_draw_hidden_field( 'manufacturers_image_loaded', '' )}
{if $app->controller->view->usePopupMode}
    <input type="hidden" name="popup" value="1" />
{/if}
</form>
{if $app->controller->view->contentAlreadyLoaded == 0}
</div>
{/if}
<script type="text/javascript">
{$imageScript}

{if $app->controller->view->contentAlreadyLoaded == 0}

function backStatement() {
    {if $app->controller->view->usePopupMode}
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
    {else}
        window.history.back();
    {/if}
    return false;
}

function saveManufacturer() {
    $.post("{Yii::$app->urlManager->createUrl('categories/brand-submit')}", $('#save_brand_form').serialize(), function(data, status){
        if (status == "success") {
            {if $app->controller->view->usePopupMode}
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove(); 
                $( ".brand_box" ).html(data);
                $('.edit_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                resetStatement();
            {else}      
                $('.brandEditPage').html(data);
            {/if}
            //$('#manufacturers_management_data').html(data);
            //$("#manufacturers_management").show();

            //$('.gallery-album-image-placeholder').html('');

            //$('.table').DataTable().search( '' ).draw(false);

            

        } else {
            alert("Request error.");
        }
    },"html");

    //$('input[name=manufacturers_image_loaded]').val();

    return false;
}
{/if}

var $filedrop = $('#gallery-filedrop');

function createImage (file, $container){
    var $preview = $('.gallery-template', $filedrop);
    $image = $('img', $preview);
    var reader = new FileReader();
    $image.width(300);
    reader.onload = function(e){
        $image.attr('src',e.target.result);
    };
    reader.readAsDataURL(file);
    $preview.appendTo($('.gallery-filedrop-queue', $container));
    $.data(file, $preview);
}

$(function () {

    $('.gallery-filedrop-fallback-trigger', $filedrop)
            .on('click', function(e) {
                e.preventDefault();
                $('#gallery-filedrop-fallback').trigger('click');
            })

    $filedrop.filedrop({
        fallback_id : 'gallery-filedrop-fallback',
        url: "{Yii::$app->urlManager->createUrl('categories/temporary-upload')}",
        paramname: 'filedrop_files',
        maxFiles: 1,
        maxfilesize : 20,
        allowedfiletypes: ['image/jpeg','image/png','image/gif'],
        allowedfileextensions: ['.jpg','.jpeg','.png','.gif'],
        error: function(err, file) {
            console.log(err);
        },
        uploadStarted: function(i, file, len){
            $("#manufacturer_logo").hide();
            createImage(file, $filedrop);
        },
        progressUpdated: function(i, file, progress) {
            $.data(file).find('.gallery-filedrop-progress').width(progress);
        },
        uploadFinished: function (i, file, response, time) {
            if (response.status >= 0) {
                createImage(file, $filedrop);
                $.data(file).find('.elgg-state-uploaded').show();
                $.data(file).find('.elgg-state-failed').hide();

                if(response.filename != ''){
                    $('input[name=manufacturers_image_loaded]').val(response.filename);
                    $('span.cat_upload_img').text(response.filename);
                    $('.js-image-actions').removeClass('hide-default');
                }


            } else {
                $.data(file).find('.elgg-state-uploaded').hide();
                $.data(file).find('.elgg-state-failed').show();
            }
        }
    });
});


$(document).ready(function(){
    $(".check_on_off").bootstrapSwitch(
      {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );
})
</script>