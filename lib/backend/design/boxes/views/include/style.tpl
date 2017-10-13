{use class="\backend\design\Style"}
{$styleHide = Style::hide($settings.data_class)}
{$styleShow = Style::show($settings.data_class)}
<div class="tabbable tabbable-custom box-style-tab">
  <ul class="nav nav-tabs">

    <li class="active"><a href="#main_view" data-toggle="tab">{$smarty.const.BOX_HEADING_MAIN_STYLES}</a></li>
    {if $styleHide.hover !== 1}
    <li><a href="#hover" data-toggle="tab">hover</a></li>
    {/if}
    {if $styleShow.active == 1}
      <li><a href="#active" data-toggle="tab">active</a></li>
    {/if}
    {if $styleHide.responsive !== 1}
    {foreach $settings.media_query as $item}
      <li><a href="#m{$item.id}" data-toggle="tab">{$item.setting_value}</a></li>
    {/foreach}
    {/if}
    {if $styleHide.before !== 1}
      <li><a href="#before" data-toggle="tab">:before</a></li>
    {/if}
    {if $styleHide.after !== 1}
      <li><a href="#after" data-toggle="tab">:after</a></li>
    {/if}

  </ul>
  <div class="tab-content menu-list">
    <div class="tab-pane active" id="main_view">

      {*<div class="demo-box">AaBbCc 1 2 3 4 5</div>*}
      {$id = 'main_view'}
      {$name = 'setting[0]'}
      {$value = $settings[0]}
      {include 'include/style_tab.tpl'}

    </div>
    <div class="tab-pane" id="hover">

      {$id = 'hover'}
      {$name = 'visibility[0][1]'}
      {$value = $visibility[0][1]}
      {include 'include/style_tab.tpl'}

    </div>
    {if $styleShow.active == 1}
      <div class="tab-pane" id="active">

        {$id = 'active'}
        {$name = 'visibility[0][2]'}
        {$value = $visibility[0][2]}
        {include 'include/style_tab.tpl'}

      </div>
    {/if}
    {$responsive = 1}
    {foreach $settings.media_query as $item}
    <div class="tab-pane" id="m{$item.id}">

      {$id = 'm'|cat:$item.id}
      {$name = 'visibility[0]['|cat:$item.id|cat:']'}
      {$value = $visibility[0][$item.id]}
      {include 'include/style_tab.tpl'}

    </div>
    {/foreach}
    <div class="tab-pane" id="before">

      {$id = 'before'}
      {$name = 'visibility[0][3]'}
      {$value = $visibility[0][3]}
      {include 'include/style_tab.tpl'}

    </div>
    <div class="tab-pane" id="after">

      {$id = 'after'}
      {$name = 'visibility[0][4]'}
      {$value = $visibility[0][4]}
      {include 'include/style_tab.tpl'}

    </div>
  </div>
</div>



{if !$settings.data_class}
<div class="setting-row menu-list">
  <label for="" style="padding-left: 10px">{$smarty.const.TEXT_CLASS}</label>
  <input type="text" name="setting[0][style_class]" value="{$settings[0].style_class}" class="form-control" style="width: 200px" />
</div>
{/if}
{if $settings.theme_name}<input type="hidden" name="theme_name" value="{$settings.theme_name}"/>{/if}



<script type="text/javascript">
  $(function() {

    var changeStyle = function(){
      $('#box-save').trigger('change')

    };

    $('.box-style-tab input, .box-style-tab select').each(changeStyle);//.on('change', changeStyle);


    $('.colorpicker-component').colorpicker({ sliders: {
      saturation: { maxLeft: 200, maxTop: 200 },
      hue: { maxTop: 200 },
      alpha: { maxTop: 200 }
    }}).on('changeColor', changeStyle);

  });
  function changeDimension(select, input){
    if ($(select).val() == 'px' || $(select).val() == 'pr') {
      $('input[name="' + input + '"]').attr('type', 'number')
    } else {
      $('input[name="' + input + '"]').attr('type', 'text')
    }
  }
</script>