<form action="" id="add-page">
  <input type="hidden" name="theme_name" value="{$theme_name}"/>
  <input type="hidden" name="page_name" value="{$page_name}"/>
  <div class="popup-heading">{$page_name}</div>
  <div class="popup-content pop-mess-cont">

    {if $page_type == 'products'}

    <p><label><input type="checkbox" name="added_page_settings[no_filters]"{if $added_page_settings.no_filters} checked{/if}/> No filters</label></p>

    {/if}
    {if $page_type == 'product'}

    <p><label><input type="checkbox" name="added_page_settings[has_attributes]"{if $added_page_settings.has_attributes} checked{/if}/> Has attributes</label></p>

    <p><label><input type="checkbox" name="added_page_settings[is_bundle]"{if $added_page_settings.is_bundle} checked{/if}/> Is bundle</label></p>

    {/if}

  </div>
  <div class="noti-btn">
    <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
    <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
  </div>
</form>
<script type="text/javascript">
  (function($){
    $(function(){
      $('#add-page').on('submit', function(){
        var values = $(this).serializeArray();
        values = values.concat(
                $('input[type=checkbox]:not(:checked)', this).map(function() {
                  return { "name": this.name, "value": 0}
                }).get()
        );
        $.post('{$action}', values, function(){
          $('.popup-box-wrap:last').remove();
          $(window).trigger('reload-frame');
        }, 'json');

        return false
      })
    })
  })(jQuery)
</script>