<form action="" id="add-theme">
<div class="popup-heading">{$smarty.const.TEXT_ADD_THEME}</div>
<div class="popup-content pop-mess-cont">

  <div class="setting-row" style="display: none">
    <label for="">Theme name</label>
    <input type="text" name="theme_name" value="" class="form-control theme_name" style="width: 243px" placeholder="only lowercase letters and numbers" />

  </div>

  <div class="setting-row">
    <label for="">{$smarty.const.TEXT_THEME_TITLE}</label>
    <input type="text" name="title" value="" class="form-control" style="width: 243px" required/>
  </div>

  <div class="setting-row">
    <label for="">{$smarty.const.COPY_FROM_THEME}</label>
    <select name="parent_theme" id="" class="form-control">
      <option value=""></option>
      {foreach $themes as $theme}
      <option value="{$theme.theme_name}">{$theme.title}</option>
      {/foreach}
    </select>
  </div>

  <div class="setting-row">
    <label for=""> Landing page</label>
    <input type="checkbox" name="landing" class="uniform" style="width: auto; position: relative; top: 3px"/>
  </div>

</div>
<div class="noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
</div>
</form>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/html2canvas.js"></script>
<script type="text/javascript">
  (function($){
    $(function(){
      $('#add-theme').on('submit', function(){
        var theme_name = $('.theme_name').val()
        $.get('{$action}', $('#add-theme').serializeArray(), function(d){
          $('.pop-mess-cont .error').remove();
          if (d.code == 1){
            $('.pop-mess-cont').prepend('<div class="error">'+d.text+'</div>')
          }
          if (d.code == 2){
            $('.pop-mess-cont').prepend('<div class="info">'+d.text+'</div>')

            $('body').append('<iframe src="{$app->request->baseUrl}/../?theme_name='+theme_name+'" width="100%" height="0" frameborder="no" id="home-page"></iframe>');
            var home_page = $('#home-page');
            home_page.on('load', function(){
              setTimeout(function(){
                home_page.contents().find('body').append('<div>&nbsp;</div>');
                html2canvas(home_page.contents().find('body').get(0), {
                  background: '#ffffff',
                  onrendered: function(canvas) {
                    $.post('upload/screenshot', { theme_name: theme_name, image: canvas.toDataURL('image/png')}, function(){
                      location.reload();
                    });
                    home_page.remove()
                  }
                })
              }, 2000)
            });
          }
        }, 'json');

        return false
      })
    })
  })(jQuery)
</script>