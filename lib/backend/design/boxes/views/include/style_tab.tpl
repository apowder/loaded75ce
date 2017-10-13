
<div class="tabbable tabbable-custom box-style-tab">
  <ul class="nav nav-tabs">
{if $responsive && !$settings.data_class}
  <li class="active"><a href="#view-{$id}" data-toggle="tab">{$smarty.const.TEXT_VIEW}</a></li>
    {if $styleHide.font !== 1}
      <li><a href="#font-{$id}" data-toggle="tab">{$smarty.const.TEXT_FONT}</a></li>
    {/if}
{else}
    {if $styleHide.font !== 1}
      <li class="active"><a href="#font-{$id}" data-toggle="tab">{$smarty.const.TEXT_FONT}</a></li>
    {/if}
{/if}
    {if $styleHide.background !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background !== 1} class="active"{/if}><a href="#background-{$id}" data-toggle="tab">{$smarty.const.TEXT_BACKGROUND}</a></li>
    {/if}
    {if $styleHide.padding !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding !== 1} class="active"{/if}><a href="#padding-{$id}" data-toggle="tab">{$smarty.const.TEXT_PADDING}</a></li>
    {/if}
    {if $styleHide.border !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border !== 1} class="active"{/if}><a href="#border-{$id}" data-toggle="tab">{$smarty.const.TEXT_BORDER}</a></li>
    {/if}
    {if $styleHide.size !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size !== 1} class="active"{/if}><a href="#size-{$id}" data-toggle="tab">{$smarty.const.TABLE_HEADING_FILE_SIZE}</a></li>
    {/if}
    {if $styleHide.display !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size === 1 && $styleHide.display !== 1} class="active"{/if}><a href="#display-{$id}" data-toggle="tab">Display</a></li>
    {/if}

  </ul>
  <div class="tab-content menu-list">
    {if $responsive && !$settings.data_class}
      <div class="tab-pane active" id="view-{$id}">
        <p><label><input type="checkbox" name="{$name}[display_none]"{if $value.display_none} checked{/if}/> {$smarty.const.TEXT_HIDE_BLOCK}</label></p>

        {if $responsive_settings}
          {foreach $responsive_settings as $item}
            {include $item}
          {/foreach}
        {/if}
        {if $block_view}
        {include 'include/schema.tpl'}
        {/if}

      </div>
    {/if}
    <div class="tab-pane{if ($responsive != 1 || $settings.data_class) && $styleHide.font !== 1} active{/if}" id="font-{$id}">

      {if $styleHide.font.content !== 1 && ($id == 'before' || $id == 'after')}
        <div class="setting-row">
          <label for="">Content</label>
          <input type="text" name="{$name}[content]" value="{$value.content}" class="form-control" />
        </div>
      {/if}

      {if $styleHide.font.font_family !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_FAMILY}</label>
        <select name="{$name}[font_family]" id="" class="form-control">
          <option value=""{if $value.font_family == ''} selected{/if}></option>
          <option value="Arial"{if $value.font_family == 'Arial'} selected{/if}>Arial</option>
          <option value="Verdana"{if $value.font_family == 'Verdana'} selected{/if}>Verdana</option>
          <option value="Tahoma"{if $value.font_family == 'Tahoma'} selected{/if}>Tahomaa</option>
          <option value="Times"{if $value.font_family == 'Times'} selected{/if}>Times</option>
          <option value="Times New Roman"{if $value.font_family == 'Times New Roman'} selected{/if}>Times New Roman</option>
          <option value="Georgia"{if $value.font_family == 'Georgia'} selected{/if}>Georgia</option>
          <option value="Trebuchet MS"{if $value.font_family == 'Trebuchet MS'} selected{/if}>Trebuchet MS</option>
          <option value="Sans"{if $value.font_family == 'Sans'} selected{/if}>Sans</option>
          <option value="Comic Sans MS"{if $value.font_family == 'Comic Sans MS'} selected{/if}>Comic Sans MS</option>
          <option value="Courier New"{if $value.font_family == 'Courier New'} selected{/if}>Courier New</option>
          <option value="Garamond"{if $value.font_family == 'Garamond'} selected{/if}>Garamond</option>
          <option value="Helvetica"{if $value.font_family == 'Helvetica'} selected{/if}>Helvetica</option>
          {foreach $settings.font_added as $item}
            <option value="{$item}"{if $value.font_family == $item} selected{/if}>{$item}</option>
          {/foreach}
        </select>
      </div>
      {/if}

      {if $styleHide.font.color !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_COLOR_}</label>
        <div class="colors-inp">
          <div id="cp3" class="input-group colorpicker-component">
            <input type="text" name="{$name}[color]" value="{$value.color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>
      {/if}

      {if $styleHide.font.font_size !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_SIZE}</label>
        <input type="number" name="{$name}[font_size]" value="{$value.font_size}" class="form-control" />
        <select name="{$name}[font_size_dimension]" id="" class="form-control sizing" onchange="changeDimension(this, '{$name}[font_size]')">
          <option value=""{if $value.font_size_dimension == ''} selected{/if}>px</option>
          <option value="em"{if $value.font_size_dimension == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.font_size_dimension == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.font_size_dimension == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.font_size_dimension == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.font_size_dimension == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.font_size_dimension == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.font_size_dimension == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.font_weight !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_WEIGHT}</label>
        <select name="{$name}[font_weight]" id="" class="form-control">
          <option value=""{if $value.font_weight == ''} selected{/if}></option>
          <option value="100"{if $value.font_weight == '100'} selected{/if}>100</option>
          <option value="200"{if $value.font_weight == '200'} selected{/if}>200</option>
          <option value="300"{if $value.font_weight == '300'} selected{/if}>300</option>
          <option value="400"{if $value.font_weight == '400' || $value.font_weight == 'normal'} selected{/if}>400 ({$smarty.const.TEXT_NORMAL})</option>
          <option value="500"{if $value.font_weight == '500'} selected{/if}>500</option>
          <option value="600"{if $value.font_weight == '600'} selected{/if}>600</option>
          <option value="700"{if $value.font_weight == '700' || $value.font_weight == 'bold'} selected{/if}>700 ({$smarty.const.TEXT_BOLD})</option>
          <option value="800"{if $value.font_weight == '800'} selected{/if}>800</option>
          <option value="900"{if $value.font_weight == '900'} selected{/if}>900</option>
          <option value="1000"{if $value.font_weight == '1000'} selected{/if}>1000</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.line_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_LINE_HEIGHT}</label>
        <input type="number" name="{$name}[line_height]" value="{$value.line_height}" class="form-control" />
        <select name="{$name}[line_height_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.line_height_measure == ''} selected{/if}>px</option>
          <option value="%"{if $value.line_height_measure == '%'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_align !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_ALIGN}</label>
        <select name="{$name}[text_align]" id="" class="form-control">
          <option value=""{if $value.text_align == ''} selected{/if}></option>
          <option value="left"{if $value.text_align == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value.text_align == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
          <option value="center"{if $value.text_align == 'center'} selected{/if}>{$smarty.const.TEXT_CENTER}</option>
          <option value="justify"{if $value.text_align == 'justify'} selected{/if}>{$smarty.const.TEXT_JUSTIFY}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_shadow !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_SHADOW}</label>
        <div class="" style="display: inline-block; width: 60%">
          <input type="number" name="{$name}[text_shadow_left]" value="{$value.text_shadow_left}" class="form-control" placeholder="position left" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[text_shadow_top]" value="{$value.text_shadow_top}" class="form-control" placeholder="position top" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[text_shadow_size]" value="{$value.text_shadow_size}" class="form-control" placeholder="radius" /><span class="px">px</span>
          <div class="colors-inp">
            <div id="cp3" class="input-group colorpicker-component">
              <input type="text" name="{$name}[text_shadow_color]" value="{$value.text_shadow_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
              <span class="input-group-addon"><i></i></span>
            </div>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.font.vertical_align !== 1}
      <div class="setting-row">
        <label for="">Vertical align</label>
        <select name="{$name}[vertical_align]" id="" class="form-control">
          <option value=""{if $value.vertical_align == ''} selected{/if}>baseline</option>
          <option value="bottom"{if $value.vertical_align == 'bottom'} selected{/if}>bottom</option>
          <option value="middle"{if $value.vertical_align == 'middle'} selected{/if}>middle</option>
          <option value="sub"{if $value.vertical_align == 'sub'} selected{/if}>sub</option>
          <option value="super"{if $value.vertical_align == 'super'} selected{/if}>super</option>
          <option value="text-bottom"{if $value.vertical_align == 'text-bottom'} selected{/if}>text-bottom</option>
          <option value="text-top"{if $value.vertical_align == 'text-top'} selected{/if}>text-top</option>
          <option value="top"{if $value.vertical_align == 'top'} selected{/if}>top</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_transform !== 1}
      <div class="setting-row">
        <label for="">Transform</label>
        <select name="{$name}[text_transform]" id="" class="form-control">
          <option value=""{if $value.text_transform == ''} selected{/if}></option>
          <option value="none"{if $value.text_transform == 'none'} selected{/if}>none</option>
          <option value="uppercase"{if $value.text_transform == 'uppercase'} selected{/if}>uppercase</option>
          <option value="lowercase"{if $value.text_transform == 'lowercase'} selected{/if}>lowercase</option>
          <option value="capitalize"{if $value.text_transform == 'capitalize'} selected{/if}>capitalize</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_decoration !== 1}
      <div class="setting-row">
        <label for="">Text decoration</label>
        <select name="{$name}[text_decoration]" id="" class="form-control">
          <option value=""{if $value.text_decoration == ''} selected{/if}></option>
          <option value="none"{if $value.text_decoration == 'none'} selected{/if}>none</option>
          <option value="underline"{if $value.text_decoration == 'underline'} selected{/if}>underline</option>
          <option value="line-through"{if $value.text_decoration == 'line-through'} selected{/if}>line through</option>
          <option value="overline"{if $value.text_decoration == 'overline'} selected{/if}>overline</option>
          <option value="inherit"{if $value.text_decoration == 'inherit'} selected{/if}>inherit</option>
        </select>
      </div>
      {/if}


    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background !== 1} active{/if}" id="background-{$id}">

      {if $styleHide.background.background_color !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_COLOR}</label>
        <div class="colors-inp">
          <div id="cp2" class="input-group colorpicker-component">
            <input type="text" name="{$name}[background_color]" value="{$value.background_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>
      {/if}

      {if $styleHide.background.background_image !== 1}
      <div class="setting-row setting-row-image">
        <label for="">{$smarty.const.TEXT_BACKGROUND_IMAGE}</label>

        {if isset($value.background_image)}
          <div class="image">
            <img src="../{\frontend\design\Info::themeImage($value.background_image)}" alt="">
            <div class="remove-img"></div>
          </div>
        {/if}

        <div class="image-upload">
          <div class="upload" data-name="{$name}[background_image]"></div>
          <script type="text/javascript">
            $('#{$id} .upload').uploads().on('upload', function(){
              var img = $('#{$id} .dz-image-preview img', this).attr('src');
              $('#{$id} .demo-box').css('background-image', 'url("'+img+'")')
            });

            $(function(){
              $('#{$id} .setting-row-image .image > img').each(function(){
                var img = $(this).attr('src');
                $('#{$id} .demo-box').css('background-image', 'url("'+img+'")');

                $('#{$id} input[name="{$name}[background_image]"]').val('{$value.background_image}');
              });

              $('#{$id} .setting-row-image .image .remove-img').on('click', function(){
                $('#{$id} input[name="{$name}[background_image]"]').val('');
                $('#{$id} .setting-row-image .image').remove()
              })

            });

          </script>
        </div>

      </div>
      {/if}

      {if $styleHide.background.background_position !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_POSITION}</label>
        <select name="{$name}[background_position]" id="" class="form-control">
          <option value=""{if $value.background_position == ''} selected{/if}></option>
          <option value="top left"{if $value.background_position == 'top left'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
          <option value="top center"{if $value.background_position == 'top center'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
          <option value="top right"{if $value.background_position == 'top right'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
          <option value="left"{if $value.background_position == 'left'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
          <option value="center"{if $value.background_position == 'center'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
          <option value="right"{if $value.background_position == 'right'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
          <option value="bottom left"{if $value.background_position == 'bottom left'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
          <option value="bottom center"{if $value.background_position == 'bottom center'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
          <option value="bottom right"{if $value.background_position == 'bottom right'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.background.background_repeat !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_REPEAT}</label>
        <select name="{$name}[background_repeat]" id="" class="form-control">
          <option value=""{if $value.background_repeat == ''} selected{/if}></option>
          <option value="no-repeat"{if $value.background_repeat == 'no-repeat'} selected{/if}>{$smarty.const.TEXT_NO_REPEAT}</option>
          <option value="repeat"{if $value.background_repeat == 'repeat'} selected{/if}>{$smarty.const.TEXT_REPEAT}</option>
          <option value="repeat-x"{if $value.background_repeat == 'repeat-x'} selected{/if}>{$smarty.const.TEXT_REPEAT_HORIZONTAL}</option>
          <option value="repeat-y"{if $value.background_repeat == 'repeat-y'} selected{/if}>{$smarty.const.TEXT_REPEAT_VERTICAL}</option>
          <option value="space"{if $value.background_repeat == 'space'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE}</option>
          <option value="top left"{if $value.background_repeat == 'top left'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE_RESIZE}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.background.background_size !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_SIZE}</label>
        <select name="{$name}[background_size]" id="" class="form-control">
          <option value=""{if $value.background_size == ''} selected{/if}>{$smarty.const.TEXT_NO_RESIZE}</option>
          <option value="cover"{if $value.background_size == 'cover'} selected{/if}>{$smarty.const.TEXT_FIELD_ALL_BLOCK}</option>
          <option value="contain"{if $value.background_size == 'contain'} selected{/if}>{$smarty.const.TEXT_WIDTH_HEIGHT_SIZE}</option>
        </select>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding !== 1} active{/if}" id="padding-{$id}">

      {if $styleHide.padding.padding_top !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_TOP}</label>
        <input type="number" name="{$name}[padding_top]" value="{$value.padding_top}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.padding_left !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_LEFT}</label>
        <input type="number" name="{$name}[padding_left]" value="{$value.padding_left}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.padding_right !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_RIGHT}</label>
        <input type="number" name="{$name}[padding_right]" value="{$value.padding_right}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.padding_bottom !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_BOTTOM}</label>
        <input type="number" name="{$name}[padding_bottom]" value="{$value.padding_bottom}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.margin_top !== 1}
      <div class="setting-row">
        <label for="">Margin Top</label>
        <input type="number" name="{$name}[margin_top]" value="{$value.margin_top}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.margin_left !== 1}
      <div class="setting-row">
        <label for="">Margin left</label>
        <input type="number" name="{$name}[margin_left]" value="{$value.margin_left}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.margin_right !== 1}
      <div class="setting-row">
        <label for="">Margin right</label>
        <input type="number" name="{$name}[margin_right]" value="{$value.margin_right}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

      {if $styleHide.padding.margin_bottom !== 1}
      <div class="setting-row">
        <label for="">Margin bottom</label>
        <input type="number" name="{$name}[margin_bottom]" value="{$value.margin_bottom}" class="form-control" /><span class="px">px</span>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border !== 1} active{/if}" id="border-{$id}">

      {if $styleHide.border.border_top !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_TOP}</label>
        <input type="number" name="{$name}[border_top_width]" value="{$value.border_top_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border_top_color]" value="{$value.border_top_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 0 0 0 1px">{$smarty.const.TEXT_CLICK_CHOOSE_COLOR}</span>
      </div>
      {/if}

      {if $styleHide.border.border_left !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_LEFT}</label>
        <input type="number" name="{$name}[border_left_width]" value="{$value.border_left_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border_left_color]" value="{$value.border_left_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_right !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_RIGHT}</label>
        <input type="number" name="{$name}[border_right_width]" value="{$value.border_right_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border_right_color]" value="{$value.border_right_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_bottom !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_BOTTOM}</label>
        <input type="number" name="{$name}[border_bottom_width]" value="{$value.border_bottom_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border_bottom_color]" value="{$value.border_bottom_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_radius !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BORDER_RADIUS}</label>
        <div class="" style="display: inline-block; width: 60%">
          <input type="number" name="{$name}[border_radius_1]" value="{$value.border_radius_1}" class="form-control" placeholder="top left" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[border_radius_2]" value="{$value.border_radius_2}" class="form-control" placeholder="top right" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[border_radius_4]" value="{$value.border_radius_4}" class="form-control" placeholder="bottom left" /><span class="px">px</span>
          <input type="number" name="{$name}[border_radius_3]" value="{$value.border_radius_3}" class="form-control" placeholder="bottom right" /><span class="px">px</span>
        </div>
      </div>
      {/if}

      {if $styleHide.border.box_shadow !== 1}
      <div class="setting-row">
        <label for="">Box shadow</label>
        <div class="" style="display: inline-block; width: 60%">
          <input type="number" name="{$name}[box_shadow_left]" value="{$value.box_shadow_left}" class="form-control" placeholder="position left" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[box_shadow_top]" value="{$value.box_shadow_top}" class="form-control" placeholder="position top" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="{$name}[box_shadow_blur]" value="{$value.box_shadow_blur}" class="form-control" placeholder="blur" style="margin-bottom: 5px"/><span class="px">px</span>
          <input type="number" name="{$name}[box_shadow_spread]" value="{$value.box_shadow_spread}" class="form-control" placeholder="spread" style="margin-bottom: 5px"/><span class="px">px</span>
          <div class="colors-inp" style="margin-right: 11px">
            <div id="cp3" class="input-group colorpicker-component">
              <input type="text" name="{$name}[box_shadow_color]" value="{$value.box_shadow_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
              <span class="input-group-addon"><i></i></span>
            </div>
          </div>
          <select name="{$name}[box_shadow_set]" id="" class="form-control" style="width: 100px;">
            <option value=""{if $value.box_shadow_set == ''} selected{/if}>outset</option>
            <option value="pr"{if $value.box_shadow_set == 'inset'} selected{/if}>inset</option>
          </select>
        </div>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size !== 1} active{/if}" id="size-{$id}">

      {if $styleHide.size.width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_WIDTH}</label>
        <input type="number" name="{$name}[width]" value="{$value.width}" class="form-control" />
        <select name="{$name}[width_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.width_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.width_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.min_width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MIN_WIDTH}</label>
        <input type="number" name="{$name}[min_width]" value="{$value.min_width}" class="form-control" />
        <select name="{$name}[min_width_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.min_width_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.min_width_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.max_width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXY_MAX_WIDTH}</label>
        <input type="number" name="{$name}[max_width]" value="{$value.max_width}" class="form-control" />
        <select name="{$name}[max_width_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.max_width_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.max_width_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_HEIGHT}</label>
        <input type="number" name="{$name}[height]" value="{$value.height}" class="form-control" />
        <select name="{$name}[height_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.height_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.height_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.min_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MIN_HEIGHT}</label>
        <input type="number" name="{$name}[min_height]" value="{$value.min_height}" class="form-control" />
        <select name="{$name}[min_height_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.min_height_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.min_height_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.max_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MAX_HEIGHT}</label>
        <input type="number" name="{$name}[max_height]" value="{$value.max_height}" class="form-control" />
        <select name="{$name}[max_height_measure]" id="" class="form-control" style="width: 50px;">
          <option value=""{if $value.max_height_measure == ''} selected{/if}>px</option>
          <option value="pr"{if $value.max_height_measure == 'pr'} selected{/if}>%</option>
        </select>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size === 1 && $styleHide.display !== 1} active{/if}" id="display-{$id}">

      {if $styleHide.display.float !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FLOAT}</label>
        <select name="{$name}[float]" id="" class="form-control">
          <option value=""{if $value.float == ''} selected{/if}></option>
          <option value="none"{if $value.float == 'none'} selected{/if}>{$smarty.const.OPTION_NONE}</option>
          <option value="left"{if $value.float == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value.float == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.clear !== 1}
      <div class="setting-row">
        <label for="">Clear</label>
        <select name="{$name}[clear]" id="" class="form-control">
          <option value=""{if $value.clear == ''} selected{/if}></option>
          <option value="none"{if $value.clear == 'none'} selected{/if}>{$smarty.const.OPTION_NONE}</option>
          <option value="left"{if $value.clear == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value.clear == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
          <option value="both"{if $value.clear == 'both'} selected{/if}>{$smarty.const.TEXT_BOTH}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.display !== 1}
      <div class="setting-row">
        <label for="">Display</label>
        <select name="{$name}[display]" id="" class="form-control">
          <option value=""{if $value.display == ''} selected{/if}></option>
          <option value="block"{if $value.display == 'block'} selected{/if}>Block</option>
          <option value="inline-block"{if $value.display == 'inline-block'} selected{/if}>Inline block</option>
          <option value="inline"{if $value.display == 'inline'} selected{/if}>Inline</option>
          <option value="table"{if $value.display == 'table'} selected{/if}>Table</option>
          <option value="table-cell"{if $value.display == 'table-cell'} selected{/if}>Table-cell</option>
          <option value="none"{if $value.display == 'none'} selected{/if}>none</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.position !== 1}
      <div class="setting-row">
        <label for="">Position</label>
        <select name="{$name}[position]" id="" class="form-control">
          <option value=""{if $value.position == ''} selected{/if}></option>
          <option value="relative"{if $value.position == 'relative'} selected{/if}>relative</option>
          <option value="absolute"{if $value.position == 'absolute'} selected{/if}>absolute</option>
          <option value="static"{if $value.position == 'static'} selected{/if}>static</option>
          <option value="fixed"{if $value.position == 'fixed'} selected{/if}>fixed</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.overflow !== 1}
      <div class="setting-row">
        <label for="">Overflow</label>
        <select name="{$name}[overflow]" id="" class="form-control">
          <option value=""{if $value.overflow == ''} selected{/if}></option>
          <option value="hidden"{if $value.overflow == 'hidden'} selected{/if}>hidden</option>
          <option value="auto"{if $value.overflow == 'auto'} selected{/if}>auto</option>
          <option value="scroll"{if $value.overflow == 'scroll'} selected{/if}>scroll</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.top !== 1}
        <div class="setting-row">
          <label for="">Top</label>
          <input type="number" name="{$name}[top]" value="{$value.top}" class="form-control" />
          <select name="{$name}[top_dimension]" id="" class="form-control" style="width: 50px;">
            <option value=""{if $value.top_dimension == ''} selected{/if}>px</option>
            <option value="em"{if $value.top_dimension == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.top_dimension == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.top_dimension == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.top_dimension == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.top_dimension == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.top_dimension == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.top_dimension == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.left !== 1}
        <div class="setting-row">
          <label for="">Left</label>
          <input type="number" name="{$name}[left]" value="{$value.left}" class="form-control" />
          <select name="{$name}[left_dimension]" id="" class="form-control" style="width: 50px;">
            <option value=""{if $value.left_dimension == ''} selected{/if}>px</option>
            <option value="em"{if $value.left_dimension == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.left_dimension == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.left_dimension == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.left_dimension == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.left_dimension == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.left_dimension == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.left_dimension == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.right !== 1}
        <div class="setting-row">
          <label for="">Right</label>
          <input type="number" name="{$name}[right]" value="{$value.right}" class="form-control" />
          <select name="{$name}[right_dimension]" id="" class="form-control" style="width: 50px;">
            <option value=""{if $value.right_dimension == ''} selected{/if}>px</option>
            <option value="em"{if $value.right_dimension == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.right_dimension == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.right_dimension == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.right_dimension == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.right_dimension == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.right_dimension == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.right_dimension == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.bottom !== 1}
        <div class="setting-row">
          <label for="">Bottom</label>
          <input type="number" name="{$name}[bottom]" value="{$value.bottom}" class="form-control" />
          <select name="{$name}[bottom_dimension]" id="" class="form-control" style="width: 50px;">
            <option value=""{if $value.bottom_dimension == ''} selected{/if}>px</option>
            <option value="em"{if $value.bottom_dimension == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.bottom_dimension == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.bottom_dimension == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.bottom_dimension == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.bottom_dimension == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.bottom_dimension == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.bottom_dimension == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

    </div>

  </div>
</div>


