{use class="yii\helpers\Html"}
{use class="\common\classes\platform"}

{if $app->controller->view->contentAlreadyLoaded == 0}
<div class="catEditPage popupEditCat">
{/if}
<form id="save_category_form" name="category_edit" onSubmit="return saveCategory();">
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            {if count(platform::getCategoriesAssignList())>1 }
            <li><a href="#tab_platform" data-toggle="tab">{$smarty.const.TEXT_PLATFORM_TAB}</a></li>
            {/if}
            <li class="active"><a href="#tab_2" data-toggle="tab">{$smarty.const.TEXT_NAME_DESCRIPTION}</a></li>
            <li><a href="#tab_3" data-toggle="tab">{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
            <li><a href="#tab_4" data-toggle="tab">{$smarty.const.TEXT_SEO}</a></li>
{if {$categories_id > 0}}
            <li><a href="#tab_5" data-toggle="tab">{$smarty.const.TEXT_FILTERS}</a></li>
{/if}
        </ul>
        <div class="tab-content">
            {if count(platform::getCategoriesAssignList())>1 }
            <div class="tab-pane topTabPane tabbable-custom" id="tab_platform">
                <div class="filter_pad">
                    <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                        <thead>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_CATEGORY_ASSIGN}</th>
                        </thead>
                        <tbody>
                        {foreach platform::getCategoriesAssignList() as $platform}
                            <tr>
                                <td>{$platform['text']}</td>
                                <td>
                                    {Html::checkbox('platform[]', isset($app->controller->view->platform_assigned[$platform['id']]), ['value' => $platform['id'],'class'=>'check_on_off'])}
                                    {Html::hiddenInput('category_product_assign['|cat:$platform['id']|cat:']', '', ['class'=>'js-apply_status_to_sub_categories'])}
                                </td>
                           </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            {/if}
            <div class="tab-pane active topTabPane tabbable-custom" id="tab_2">
               {if count($languages) > 1}
               <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $cDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_EDIT_CATEGORIES_NAME}</td>
                                <td class="label_value">{$mItem['categories_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_EDIT_CATEGORIES_DESCRIPTION}</td>
                                <td class="label_value">{$mItem['categories_description']}</td>
                            </tr>
                        </table>
                    </div>        
                    {/foreach} 
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_3">
                <div class="">

                    <div class="md_row after">
                        <label for="status">{$smarty.const.TEXT_CATEGORIES_STATUS}</label>
                        <div class="md_value"><input type="checkbox" value="1" name="categories_status" class="check_on_off"{if $cInfo->categories_status == 1} checked="checked"{/if}></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="widget box">
                                <div class="widget-header">
                                    <h4>Gallery image</h4>
                                </div>
                                <div class="widget-content">
                                    <div class="about-image">
                                        <div class="about-image-scheme-1">
                                            <div></div><div></div><div></div><div></div><div></div><div></div>
                                        </div>
                                        <div class="about-image-text">
                                            This image will be used on category listing page
                                            <ul>
                                                <li>Make sure your image is appropriately sized. It should be not too big and not too small.</li>
                                                <li>Formats:  jpg, png, gif.</li>
                                                <li>Color mode: RGB</li>
                                            </ul>
                                        </div>
                                    </div>

                                    {\backend\design\Image::widget([
                                        'name' => 'categories_image',
                                        'value' => {$image},
                                        'upload' => 'categories_image_loaded',
                                        'delete' => 'delete_image'
                                    ])}
                                </div>
                                <div class="divider"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="widget box">
                                <div class="widget-header">
                                    <h4>Hero image</h4>
                                </div>
                                <div class="widget-content">
                                    <div class="about-image">
                                        <div class="about-image-scheme-2">
                                            <div></div><div></div><div></div><div></div>
                                        </div>
                                        <div class="about-image-text">
                                            This image will be used on product listing page
                                            <ul>
                                                <li>Make sure your image is appropriately sized. It should be not too small.</li>
                                                <li>Formats:  jpg, png, gif.</li>
                                                <li>Color mode: RGB</li>
                                            </ul>
                                        </div>
                                    </div>
                                    {\backend\design\Image::widget([
                                        'name' => 'categories_image_2',
                                        'value' => {$image_2},
                                        'upload' => 'categories_image_loaded_2',
                                        'delete' => 'delete_image_2'
                                    ])}
                                </div>
                                <div class="divider"></div>
                            </div>
                        </div>
                    </div>






                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_4">
                {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#seo_tab_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content seoTab {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $cDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="seo_tab_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['categories_seo_page_name']}</td>
                            </tr>			
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_PAGE_TITLE}</td>
                                <td class="label_value">{$mItem['categories_head_title_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_HEADER_DESCRIPTION}</td>
                                <td class="label_value">{$mItem['categories_head_desc_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_KEYWORDS}</td>
                                <td class="label_value">{$mItem['categories_head_keywords_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_GOOGLE_PRODUCT_CATEGORY}</td>
                                <td class="label_value">{$mItem['google_product_category']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_OLD_SEO_PAGE_NAME}</td>
                                <td class="label_value"><input class="form-control seo-input-field" type="input" name="categories_old_seo_page_name" value="{$cInfo->categories_old_seo_page_name}">
                                <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
                                  {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                                  <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
                                  {/if}
                                </td>
                            </tr>
                            {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                                {assign var="language_code" value=$mItem['code']}
                                {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderCategory($categories_id, $language_code)}
                            {/if}
                        </table>                        
                      <script>
                      $(document).ready(function(){
                        $('body').on('click', "#seo_tab_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#seo_tab_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });
                        
                        $('input[name=categories_old_seo_page_name]').change(function(){
                            $('input[name=categories_old_seo_page_name]').val($(this).val());
                        })
                      })
                      </script>
                    </div>
                    {/foreach}
                    <div>
                        <table cellspacing="0" cellpadding="0" width="100%">
                           
                        </table>
                    </div>
                </div>
            </div>
            {if {$categories_id > 0}}
                {if \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'categoryBlock')}
                    {\common\extensions\ProductPropertiesFilters\ProductPropertiesFilters::categoryBlock($categories_id)}
                {else}   
                    <div class="tab-pane topTabPane tabbable-custom dis_module" id="tab_5">
                        <div class="filter_pad">
                            <table class="table table-striped table-bordered table-hover table-responsive datatable-dashboard table-ordering no-footer filter_table" data-ajax="{Yii::$app->urlManager->createUrl(['categories/filter-tab-list', 'cID' => $categories_id])}" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="filter_th_name">{$smarty.const.TEXT_FILTER_NAME}</th>
                                        <th class="filter_th_count">{$smarty.const.TEXT_COUNT_VALUES}</th>
                                        <th class="filter_th_use">{$smarty.const.TEXT_USE_FILTER}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <script type="text/javascript">
                    $('.datatable-dashboard').DataTable({
                        fnDrawCallback: function () {
                            $(".check_on_off").bootstrapSwitch();
                        }
                    });
                    </script>
                {/if}
            {/if}
        </div>
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
</div>
{tep_draw_hidden_field( 'categories_id', $categories_id )}
{tep_draw_hidden_field( 'parent_category_id', $cInfo->parent_id )}
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

function saveCategory() {
  if (typeof(CKEDITOR) == 'object'){
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
    }
  }
    console.log($('#save_category_form').serialize());
    $.post("{Yii::$app->urlManager->createUrl('categories/category-submit')}", $('#save_category_form').serialize(), function(data, status){
        if (status == "success") {
            {if $app->controller->view->usePopupMode}
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove(); 
                $( ".cat_main_box" ).html(data);
                $('.edit_cat').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_cat').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.collapse_span').click(function(){
                    $(this).toggleClass('c_up');
                    $(this).parent().parent().next().slideToggle();
                });
                resetStatement();
            {else}      
                $('.catEditPage').append(data);
            {/if}
            //$('#manufacturers_management_data').html(data);
            //$("#manufacturers_management").show();

            //$('.gallery-album-image-placeholder').html('');

            //$('.table').DataTable().search( '' ).draw(false);

            

        } else {
            alert("Request error.");
        }
    },"html");

    //$('input[name=categories_image_loaded]').val();

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


$(document).ready(function(){
    var switch_assign_stat = {$js_platform_switch_notice};
    $(".check_on_off").bootstrapSwitch( {
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        onSwitchChange: function () {
          var switched_to_state = false;
          if($(this).is(':checked')){
            switched_to_state = true;
            $(this).parents('tr').find('.handle_cat_list, .count_block').removeClass('dis_module');
          }else{
            $(this).parents('tr').find('.handle_cat_list, .count_block').addClass('dis_module');
          }
          if (this.name.indexOf('platform')==0 ) {
            var platform_id = this.value;
            if ( switch_assign_stat[platform_id] && switch_assign_stat[platform_id]['original_state']!=switched_to_state ) {
                var $state_input = $('input[name="category_product_assign['+platform_id+']"]');
                if ( switched_to_state && $state_input.val()!='yes' && (parseInt(switch_assign_stat[platform_id]['categories'][switched_to_state?0:1])>0 || parseInt(switch_assign_stat[platform_id]['products'][switched_to_state?0:1])>0) ) {
                    $('body').append(
                        '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                        '<div class="around-pop-up"></div>' +
                        '<div class="popup-box"><div class="pop-up-close"></div>' +
                        '<div class="pop-up-content">' +
                        '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_CATEGORIES_AND_PRODUCTS_TO_PLATFORM}</div>' +
                        '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>');
                    $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));

                    var $popup = $('.js-state-confirm-popup');
                    $popup.find('.pop-up-close').on('click', function(){
                        $('.popup-box-wrap:last').remove();
                    });
                    $popup.find('.btn-cancel').on('click', function(){
                        $state_input.val('');
                        $('.popup-box-wrap:last').remove();
                    });
                    $popup.find('.btn-success').on('click', function(){
                        $state_input.val('yes');
                        $('.popup-box-wrap:last').remove();
                    });
                }
            }
          }
        },
        handleWidth: '20px',
        labelWidth: '24px'
    } );


})
</script>