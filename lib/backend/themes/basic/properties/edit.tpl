{use class="yii\helpers\Html"}
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
{if not {$app->controller->view->usePopupMode}}
<div class=""><a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
{/if}
<div class="properties">
  <form action="{Yii::$app->urlManager->createUrl('properties/save')}" method="post" enctype="multipart/form-data" id="property_edit" name="property_edit" {if {$app->controller->view->usePopupMode}} onsubmit="return saveProperty()" {/if}>
  {Html::hiddenInput('properties_id', $pInfo->properties_id)}
  <div class="prop_wrapper">
  <div class="properties_top">
  {if {$app->controller->view->usePopupMode}}
    <div class="properties_filter popup_pr_filter">
      <strong>{$smarty.const.TEXT_CATEGORY}</strong>
      {tep_draw_pull_down_menu('parent_id', \common\helpers\Properties::get_properties_tree(), $pInfo->parent_id, 'class="form-control"')}
    </div>
  {else}
    {Html::hiddenInput('parent_id', $pInfo->parent_id)}
  {/if}
    <div class="properties_filter">
      <ul class="pf_ul after">
        <li>
          <label>{$smarty.const.TEXT_TYPE}</label>
          {Html::dropDownList('properties_type', $pInfo->properties_type, $app->controller->view->properties_types, ['onchange'=>'changePropertyType()',  'class'=>'form-control', 'required'=>true])}
        </li>
        <li class="property_option">
          <label>{$smarty.const.TEXT_OPTION}</label>
          {Html::dropDownList('multi_choice', $pInfo->multi_choice, $app->controller->view->multi_choices, ['class'=>'form-control'])}
        </li>
        <li class="property_format" style="display:none;">
          <label>{$smarty.const.TEXT_FORMAT}</label>
          {Html::dropDownList('multi_line', $pInfo->multi_line, $app->controller->view->multi_lines, ['onchange'=>'changePropertyType()', 'class'=>'form-control'])}
          {Html::dropDownList('decimals', $pInfo->decimals, $app->controller->view->decimals, ['class'=>'form-control'])}
        </li>
      </ul>
      <div class="pf_bottom after">
        <div class="pf_bottom_td">{$smarty.const.DISPLAY_MODE}</div>
          <div class="pf_bottom_td">
            <label>{$smarty.const.TEXT_PRODUCT_INFO}</label>
            <input type="checkbox" name="display_product" value="1" class="check_on_off" {if {$pInfo->display_product > 0}} checked="checked" {/if} />
          </div>
          <div class="pf_bottom_td">
            <label>{$smarty.const.TEXT_LISTING}</label>
            <input type="checkbox" name="display_listing" value="1" class="check_on_off" {if {$pInfo->display_listing > 0}} checked="checked" {/if} />
          </div>
          <div class="pf_bottom_td">
            <label>{$smarty.const.TEXT_FILTER}</label>
            <input type="checkbox" name="display_filter" value="1" class="check_on_off" {if {$pInfo->display_filter > 0}} checked="checked" {/if} />
          </div>
          <div class="pf_bottom_td">
            <label>{$smarty.const.TEXT_SEARCH}</label>
            <input type="checkbox" name="display_search" value="1" class="check_on_off" {if {$pInfo->display_search > 0}} checked="checked" {/if} />
          </div>
        <div class="pf_bottom_td">
          <label>{$smarty.const.TEXT_COMPARE}</label>
          <input type="checkbox" name="display_compare" value="1" class="check_on_off" {if {$pInfo->display_compare > 0}} checked="checked" {/if} />
        </div>
      </div>
    </div>
  </div>
    <div class="properties_bottom tabbable-custom">
      {if count($languages) > 1}
      <ul class="nav nav-tabs under_tabs_ul">
        {foreach $languages as $lang}
          <li{if $lang['code'] == $default_language} class="active"{/if}><a href="#tab_{$lang['code']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>
        {/foreach}
      </ul>
      {/if}
      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
        {foreach $languages as $lang}
          <div class="tab-pane{if $lang['code'] == $default_language} active{/if}" id="tab_{$lang['code']}">
            <div class="property_content_width">
              <table cellspacing="0" cellpadding="0">
                {if $lang['code'] == $default_language}
              <tr>
                <td class="pf_label">{$smarty.const.TEXT_SAME_OF_ALL}</td>
                <td><input type="checkbox" name="same_all_languages" value="1" class="check_on_off same_all"></td>
              </tr>
                {/if}
              <tr class="properties_descr">
                <td class="pf_label">{$smarty.const.TEXT_NAME}</td>
                {if $lang['code'] == $default_language}
                <td>{Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id, $lang['id']), ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id, $lang['id']), 'required'=>true])}</td>
                {else}
                <td>{Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id, $lang['id']), ['class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id, $lang['id'])])}</td>
                {/if}
              </tr>
              <tr class="properties_descr">
                <td class="pf_label">{$smarty.const.TEXT_ICON}</td>
                <td>
                  <div class="gallery-filedrop-container">
                    <div class="gallery-filedrop">
                      <span class="gallery-filedrop-message"><span>{$smarty.const.TEXT_DRAG_DROP}</span><a href="#gallery-filedrop" class="gallery-filedrop-fallback-trigger btn" rel="nofollow">{$smarty.const.TEXT_CHOOSE_FILE}</a><span>{$smarty.const.TEXT_FROM_COMPUTER}</span></span>
                      <input size="30" id="gallery-filedrop-fallback-{$lang['id']}" name="properties_image[{$lang['id']}]" class="elgg-input-file hidden" type="file">
                      <input type="hidden" name="properties_image_loaded[{$lang['id']}]" class="elgg-input-hidden">

                      <div class="gallery-filedrop-queue">
                        <img style="max-height:200px" src="{$smarty.const.DIR_WS_CATALOG_IMAGES}{\common\helpers\Properties::get_properties_image($pInfo->properties_id, $lang['id'])}" class="properties_image" {if strlen(\common\helpers\Properties::get_properties_image($pInfo->properties_id, $lang['id'])) == 0} style="display:none" {/if} />
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
                </td>
              </tr>
              <tr class="use_additional properties_descr">
                <td class="pf_label">{$smarty.const.TEXT_ADDITIONAL_INFO}</td>
                <td><input type="checkbox" name="additional_info" value="1" class="check_on_off check_desc" {if {$app->controller->view->additional_info > 0}} checked="checked" {/if}></td>
              </tr>
              <tr class="use_desc properties_descr" {if {$app->controller->view->additional_info > 0}} style="display:table-row;" {else} style="display:none;" {/if}>
                <td class="pf_label">{$smarty.const.TEXT_PROPERTIES_DESCRIPTION}</td>
                <td><textarea name="properties_description[{$lang['id']}]" class="form-control">{\common\helpers\Properties::get_properties_description($pInfo->properties_id, $lang['id'])}</textarea></td>
              </tr>
              <tr class="use_add_info properties_descr">
                <td class="pf_label">{$smarty.const.TEXT_UNITS}</td>
                <td>
                  <div class="f_td_group unit_group_{$lang['id']}">{Html::textInput('properties_units_title['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_units_title($pInfo->properties_id, $lang['id']), ['id'=>'select-unit-'|cat:$lang['id'], 'class'=>'form-control', 'placeholder'=>TEXT_UNIT_DESCRIPTION, 'autocomplete'=>'off'])}</div>
<script type="text/javascript">
$(document).ready(function() {
  $('#select-unit-{$lang['id']}').autocomplete({
      source: "{Yii::$app->urlManager->createUrl('properties/units')}",
      minLength: 0,
      autoFocus: true,
      delay: 0,
      appendTo: '.unit_group_{$lang['id']}',
      open: function (e, ui) {
        if ($(this).val().length > 0) {
          var acData = $(this).data('ui-autocomplete');
          acData.menu.element.find('a').each(function () {
            var me = $(this);
            var keywords = acData.term.split(' ').join('|');
            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
          });
        }
      }
  }).focus(function () {
    $(this).autocomplete("search");
  });
});
</script>
                </td>
              </tr>
              <tr class="properties_descr">
                <td class="pf_label">{$smarty.const.TEXT_SEO_PAGE_NAME}</td>
                <td>{Html::textInput('properties_seo_page_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_seo_page_name($pInfo->properties_id, $lang['id']), ['class'=>'form-control'])}</td>
              </tr>
              <tr class="use_pos_values">
                <td colspan="2">
                  <div class="possible_values">
                    <div class="possible_title after">
                      <div class="ps_1">{$smarty.const.TEXT_POSSIBLE_VALUES}</div>
                      <div class="ps_1"><span class="ps_info"><i class="icon-info-circle"></i></span>{$smarty.const.TEXT_ALTERNATIVE_CONST}</div>
                    </div>
                    <div class="ps_desc">
                      {if {$app->controller->view->properties_values[$lang['id']]|@count} > 0}
                        {foreach $app->controller->view->properties_values_sorted_ids as $val_id => $val_id}
                          {include file="prop_value.tpl" val_id=$val_id lang_id=$lang['id'] value=$app->controller->view->properties_values[$lang['id']][$val_id] is_default_lang=($lang['code']==$default_language) pInfo=$pInfo} 
                        {/foreach}
                      {/if}
                      <div align="right" class="ps_button_{$lang['id']}"><a class="btn btn-add">{$smarty.const.TEXT_ADD_MORE}</a></div>
                    </div>
                    <div class="ps_desc_template_{$lang['id']}" style="display:none;">{include file="prop_value.tpl" val_id='__val_id__' lang_id=$lang['id'] value=array() is_default_lang=($lang['code']==$default_language) pInfo=$pInfo}</div>
                  </div>
                </td>
              </tr>
            </table>
            </div>
          </div>
        {/foreach}
      </div>
      </div>
    </div>
    <div class="buttons after">
    {if not {$app->controller->view->usePopupMode}}
      <a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
      <a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
    {/if}
      <button class="btn btn-save">{$smarty.const.IMAGE_SAVE}</button>
    </div>
  </form>
</div>
<script type="text/javascript">
<!--
function disableClick() {
  $('.nav-tabs a').click(function() {
    return false
  });
}
function switchChange(var1, var2) {
  if ((var1.target.className == 'check_on_off check_desc') && var2 == true) {
    $('.use_desc').show();
    $('.check_desc').each(function() {
      if(!$(this).is(':checked')) {
        $(this).click();
      }
    })
  } else if ((var1.target.className == 'check_on_off check_desc') && var2 == false) {
    $('.use_desc').hide();
    $('.check_desc').each(function() {
      if($(this).is(':checked')) {
        $(this).click();
      }
    })
  } else if ((var1.target.className == 'check_on_off same_all') && var2 == true) {
    disableClick();
  } else if ((var1.target.className == 'check_on_off same_all') && var2 == false) {
    $('.nav-tabs a').off('click');
  }
}

$(".check_on_off").bootstrapSwitch(
  {
    onSwitchChange: function (element, arguments) {
      switchChange(element, arguments);
      return true;
    },
    onText: "{$smarty.const.SW_ON}",
    offText: "{$smarty.const.SW_OFF}",
    handleWidth: '38px',
    labelWidth: '24px'
  }
)

function delPropValue(val_id) {
  $('.prop_value_' + val_id).remove();
}

$.fn.uploads2 = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  var body = $('body');
  var html = $('html');

  return this.each(function() {
    var _this = $(this);
    if (_this.data('value')) {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file dz-clickable dz-started"><div class="dz-details dz-processing dz-success dz-image-preview"><div class="dz-filename"><span data-dz-name="">' + _this.data('value') + '</span></div><div class="upload-remove"></div></div></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
      $('.upload-remove', _this).click(function(){
        $('.upload-file', _this).html('');
        _this.removeAttr('data-value');
        $('input[name="' + _this.data('name').replace('upload_docs', 'values') + '"]').val('');
      })
    } else {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
    }

    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
        })
      },
      previewTemplate: '<div class="dz-details"><div class="dz-filename"><span data-dz-name=""></span></div><div class="upload-remove"></div></div>',
      dataType: 'json',
      drop: function(){
        $('.upload-file', _this).html('');
      }
    });

  })
};

var new_val_counter = 0;
$('.btn-add').click(function() {
  new_val_counter++;
  {foreach $languages as $lang}
  $('.ps_button_{$lang['id']}').before($('.ps_desc_template_{$lang['id']}').html().replace(/__val_id__/g, 'new' + new_val_counter));
  {/foreach}
  $('.upload_doc', $('.prop_value_new' + new_val_counter)).uploads2();
  return false;
})

function changePropertyType() {
  if ($('select[name=properties_type]').val() == 'number' || $('select[name=properties_type]').val() == 'interval') {
    $('.property_option').show();
    $('.property_format').show();
    $('select[name=multi_line]').hide();
    $('select[name=decimals]').show();
    $('.use_pos_values').show();
    $('.upload_doc').hide();
  } else if ($('select[name=properties_type]').val() == 'text') {
    $('.property_option').show();
    $('.property_format').show();
    $('select[name=multi_line]').show();
    $('select[name=decimals]').hide();
    $('.use_pos_values').show();
    $('.upload_doc').hide();
  } else if ($('select[name=properties_type]').val() == 'file') {
    $('.property_option').hide();
    $('.property_format').hide();
    $('.use_pos_values').show();
    $('.upload_doc').show();
  } else {
    $('.property_option').hide();
    $('.property_format').hide();
    $('.use_pos_values').hide();
    $('.upload_doc').hide();
  }
  if ($('select[name=properties_type]').val() == 'interval') {
    $('.show-interval').show();
    $('.div-interval').removeClass('ps_desc_2').addClass('ps_desc_1');
  } else {
    $('.show-interval').hide();
    $('.div-interval').removeClass('ps_desc_1').addClass('ps_desc_2');
  }
  if ($('select[name=properties_type]').val() == 'text' && $('select[name=multi_line]').val() > 0) {
    $('.can-be-textarea').each(function () {
      textbox =   $(document.createElement('textarea')).
                    attr('name', $(this).attr('name')).
                    attr('class', $(this).attr('class')).
                    attr('onchange', $(this).attr('onchange')).
                    attr('placeholder', $(this).attr('placeholder')).
                    html($(this).val() ? $(this).val() : $(this).html());
      $(this).replaceWith(textbox);
    });
  } else {
    $('.can-be-textarea').each(function () {
      inputbox =  $(document.createElement('input')).attr('type', 'text').
                    attr('name', $(this).attr('name')).
                    attr('class', $(this).attr('class')).
                    attr('onchange', $(this).attr('onchange')).
                    attr('placeholder', $(this).attr('placeholder')).
                    val($(this).val() ? $(this).val() : $(this).html());
      $(this).replaceWith(inputbox);
    });
    if ($('select[name=properties_type]').val() == 'file') {
      $('.div-interval.ps_desc_2 .can-be-textarea').hide();
    }
  }
}
changePropertyType();

function changeDefaultLang(theInput, default_lang) {
  $('input[name^="' + theInput.name.replace('[' + default_lang + ']', '[') + '"]').each(function(index) {
    $(this).attr('placeholder', theInput.value);
  });
}

$('.gallery-filedrop-container').each(function() {

  var $filedrop = $(this);

  function createImage (file, $container) {
    var $preview = $('.gallery-template', $filedrop);
    $image = $('img', $preview);
    var reader = new FileReader();
    $image.height(200);
    reader.onload = function(e) {
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
        $('#' + $('.elgg-input-file', $filedrop).attr('id')).trigger('click');
      })

    $filedrop.filedrop({
      fallback_id : $('.elgg-input-file', $filedrop).attr('id'),
      url: "{Yii::$app->urlManager->createUrl('upload/index')}",
      paramname: 'file',
      maxFiles: 1,
      maxfilesize : 20,
      allowedfiletypes: ['image/jpeg','image/png','image/gif'],
      allowedfileextensions: ['.jpg','.jpeg','.png','.gif'],
      error: function(err, file) {
        console.log(err);
      },
      uploadStarted: function(i, file, len) {
        $('.properties_image', $filedrop).hide();
        createImage(file, $filedrop);
      },
      progressUpdated: function(i, file, progress) {
        $.data(file).find('.gallery-filedrop-progress').width(progress);
      },
      uploadFinished: function (i, file, response, time) {
        $('.elgg-input-hidden', $filedrop).val(file.name);
      }
    });
  });

});

$('.upload_doc').uploads2();

{if {$app->controller->view->usePopupMode && $pInfo->properties_id > 0}}
$('.properties_top').hide();
$('.properties_descr').hide();
{/if}
//-->
</script>
