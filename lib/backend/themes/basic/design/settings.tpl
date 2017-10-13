{include 'menu.tpl'}

<form action="{$action}" method="post" class="form-settings">
<div style="max-width: 800px">

  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_BACKGROUND_COLOR}</label>
    <div class="colors-inp">
      <div id="cp2" class="input-group colorpicker-component">
        <input type="text" name="setting[background_color]" value="{$setting.background_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
        <span class="input-group-addon"><i></i></span>
      </div>
    </div>
    <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
  </div>
  <script type="text/javascript">
    $(function(){
      $('.colorpicker-component').colorpicker({ sliders: {
        saturation: { maxLeft: 200, maxTop: 200 },
        hue: { maxTop: 200 },
        alpha: { maxTop: 200 }
      }})
    })
  </script>
  <div class="setting-row setting-row-image">
    <label for="">{$smarty.const.TEXT_BACKGROUND_IMAGE}</label>

    {if isset($setting.background_image)}
      <div class="image">
        <img src="{$app->request->baseUrl}/../images/{$setting.background_image}" alt="">
        <div class="remove-img"></div>
      </div>
    {/if}

    <div class="image-upload">
      <div class="upload" data-name="setting[background_image]"></div>
      <script type="text/javascript">
        $('.upload').uploads().on('upload', function(){
          var img = $('.dz-image-preview img', this).attr('src');
          $('.demo-box').css('background-image', 'url("'+img+'")')
        });

        $(function(){
          $('.setting-row-image .image > img').each(function(){
            var img = $(this).attr('src');
            $('.demo-box').css('background-image', 'url("'+img+'")');

            $('input[name="setting[background_image]"]').val('{$setting.background_image}');
          });

          $('.setting-row-image .image .remove-img').on('click', function(){
            $('input[name="setting[background_image]"]').val('');
            $('.setting-row-image .image').remove()
          })

        });

      </script>
    </div>

  </div>
  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_BACKGROUND_POSITION}</label>
    <select name="setting[background_position]" id="" class="form-control">
      <option value=""{if $setting.background_position == ''} selected{/if}></option>
      <option value="top left"{if $setting.background_position == 'top left'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
      <option value="top center"{if $setting.background_position == 'top center'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
      <option value="top right"{if $setting.background_position == 'top right'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
      <option value="left"{if $setting.background_position == 'left'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
      <option value="center"{if $setting.background_position == 'center'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
      <option value="right"{if $setting.background_position == 'right'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
      <option value="bottom left"{if $setting.background_position == 'bottom left'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
      <option value="bottom center"{if $setting.background_position == 'bottom center'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
      <option value="bottom right"{if $setting.background_position == 'bottom right'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
    </select>
  </div>
  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_BACKGROUND_REPEAT}</label>
    <select name="setting[background_repeat]" id="" class="form-control">
      <option value=""{if $setting.background_repeat == ''} selected{/if}></option>
      <option value="no-repeat"{if $setting.background_repeat == 'no-repeat'} selected{/if}>{$smarty.const.TEXT_NO_REPEAT}</option>
      <option value="repeat"{if $setting.background_repeat == 'repeat'} selected{/if}>{$smarty.const.TEXT_REPEAT}</option>
      <option value="repeat-x"{if $setting.background_repeat == 'repeat-x'} selected{/if}>{$smarty.const.TEXT_REPEAT_HORIZONTAL}</option>
      <option value="repeat-y"{if $setting.background_repeat == 'repeat-y'} selected{/if}>{$smarty.const.TEXT_REPEAT_VERTICAL}</option>
      <option value="space"{if $setting.background_repeat == 'space'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE}</option>
      <option value="top left"{if $setting.background_repeat == 'top left'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE_RESIZE}</option>
    </select>
  </div>
  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_BACKGROUND_SIZE}</label>
    <select name="setting[background_size]" id="" class="form-control">
      <option value=""{if $setting.background_size == ''} selected{/if}>{$smarty.const.TEXT_NO_RESIZE}</option>
      <option value="cover"{if $setting.background_size == 'cover'} selected{/if}>{$smarty.const.TEXT_FIELD_ALL_BLOCK}</option>
      <option value="contain"{if $setting.background_size == 'contain'} selected{/if}>{$smarty.const.TEXT_WIDTH_HEIGHT_SIZE}</option>
    </select>
  </div>

<div class="" style="border-bottom: 1px solid #eee; margin-bottom: 20px"></div>

  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_AFTER_ADDED}</label>
    <select name="settings[after_add]" id="" class="form-control">
      <option value=""{if $settings.after_add == ''} selected{/if}>{$smarty.const.TEXT_GO_TO_CART}</option>
      {*<option value="reload"{if $settings.after_add == 'reload'} selected{/if}>{$smarty.const.TEXT_RELOAD_PAGE}</option>
      <option value="animate"{if $settings.after_add == 'animate'} selected{/if}>{$smarty.const.TEXT_ANIMATE_FLY_PRODUCT}</option>*}
      <option value="popup"{if $settings.after_add == 'popup'} selected{/if}>{$smarty.const.TEXT_OPEN_CART_POPUP}</option>
    </select>
  </div>

  <div class="setting-row">
    <label for="">{$smarty.const.SHOW_PRODUCTS_FROM_SUBCATEGORIES}</label>
    <select name="settings[show_products_from_subcategories]" id="" class="form-control">
      <option value=""{if $settings.show_products_from_subcategories == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
      <option value="1"{if $settings.show_products_from_subcategories == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
    </select>
  </div>

  <div class="setting-row">
    <label for="">{$smarty.const.SHOW_EMPTY_CATEGORIES}</label>
    <select name="settings[show_empty_categories]" id="" class="form-control">
      <option value=""{if $settings.show_empty_categories == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
      <option value="1"{if $settings.show_empty_categories == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
    </select>
  </div>

  <div class="setting-row">
    <label for="">Checkout</label>
    <select name="settings[checkout_view]" id="" class="form-control">
      <option value=""{if $settings.checkout_view == ''} selected{/if}>One page</option>
      <option value="1"{if $settings.checkout_view == '1'} selected{/if}>Multy pages</option>
    </select>
  </div>


  <h4>{$smarty.const.SIZES_RESPONSIVE_DESIGN}</h4>
  <div class="extend-hidden" data-name="media_query" style="display: none">

    <div class="extend-row">
      <div class="extend-row-remove"></div>
      <div class="setting-row setting-row-left">
        <label for="">min-width</label>
        <input type="number" name="min" value="" class="min form-control" /><span class="px">px</span>
      </div>
      <div class="setting-row setting-row-right">
        <label for="">max-width</label>
        <input type="number" name="max" value="" class="max form-control" /><span class="px">px</span>
      </div>
      <input type="hidden" name="" class="main-input"/>
    </div>

  </div>
  <div class="extend" data-name="media_query"></div>
  <div style="margin-bottom: 30px"><span class="btn btn-extend-add" data-name="media_query">{$smarty.const.TEXT_ADD_SIZE}</span></div>

  <script type="text/javascript">
    (jQuery)(function($){
      $(function(){
        $('.extend[data-name="media_query"]').on('change_extend', function(){
          $('.extend-row', this).each(function(){
            var main = $('.main-input', this).val();
            var arr = main.split('w');
            $('.min', this).val(arr[0]);
            $('.max', this).val(arr[1]);
          });
          $('.extend-row input', this).on('change' ,function(){
            var row = $(this).closest('.extend-row');
            $('.main-input', row).val($('.min', row).val() + 'w' + $('.max', row).val())
          })
        });
      })
    })
  </script>

  <div class="" style="border-bottom: 1px solid #eee; margin-bottom: 20px"></div>

  <h4>Fonts <span style="font-size: 12px; color: #999; font-style: italic">(Use CSS code)</span></h4>

  <div class="extend-hidden" data-name="font_added" style="display: none">

    <div class="extend-row">
      <div class="extend-row-remove"></div>
      <div class="setting-row">
        <textarea name="" id="" cols="90" rows="5" class="main-input" style="width: 650px"></textarea>
      </div>
    </div>

  </div>
  <div class="extend" data-name="font_added"></div>
  <div><span class="btn btn-extend-add" data-name="font_added">Add font</span></div>


  <div class="" style="border-bottom: 1px solid #eee; margin: 20px 0"></div>


  <div class="setting-row">
    <label for="">Favicon <img src="{$favicon}" alt=""></label>
    <div class="upload-icon" style="display: inline-block">
      <div class="upload-file-wrap">
        <div class="upload-file-template">{$smarty.const.FILEUPLOAD_TITLE}<br>{$smarty.const.FILEUPLOAD_OR}<br><span class="btn">{$smarty.const.FILEUPLOAD_BUTTON}</span></div>
        <div class="upload-file"></div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    $(function(){
      var _this = $('.upload-icon');
      $('.upload-file', _this).dropzone({
        url: 'design/favicon?theme_name={$theme_name}',
        maxFiles: 1,
        uploadMultiple: false,
        sending:  function(e, data) {
          $('.upload-remove', _this).on('click', function(){
            $('.dz-details', _this).remove();
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="upload-remove"></div></div>',
        drop: function(){
          $('.upload-file', _this).html('');
        },
        success: function(e, data) {
          _this.trigger('upload')
        }
      })
    });

  </script>



  <div class="setting-row">
    <label for="">Css</label>
    <select name="settings[include_css]" id="" class="form-control">
      <option value=""{if $settings.include_css == ''} selected{/if}>Old</option>
      <option value="1"{if $settings.include_css == '1'} selected{/if}>New</option>
    </select>
  </div>


</div>


  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
    </div>
    <div class="btn-right">
      <button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
    </div>
  </div>



</form>


<script type="text/javascript">
  (jQuery)(function($){
    $(function(){
      var extend = $('.extend');
      extend.each(function(){
        var _this = $(this);
        var name = _this.data('name');
        var hidden = $('.extend-hidden[data-name="' + name + '"]');
        var btn = $('.btn-extend-add[data-name="' + name + '"]');

        var fill_settings = function(d){
          _this.html('');
          $.each(d, function(i, e){
            var row = $('.extend-row', hidden).clone();
            $('.main-input', row)
                    .attr('name', 'extend[' + e.setting_name + '][' + e.id + ']')
                    .val(e.setting_value);
            $('.extend-row-remove:last', row).on('click', function(){
              $.get('design/extend', { remove: e.id, setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');
            });
            row.appendTo(_this)
          });
          _this.trigger('change_extend');

          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data)
          });
        };

        $.get('design/extend', { setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');

        btn.off('click').on('click', function(){
          $.get('design/extend', { add: 1, setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');
        })
      });



      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          location.href = location.href
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          location.href = location.href
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });

    })
  })
</script>