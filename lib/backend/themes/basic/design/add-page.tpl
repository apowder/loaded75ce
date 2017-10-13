<form action="" id="add-page">
  <input type="hidden" name="theme_name" value="{$theme_name}"/>
  <div class="popup-heading">{$smarty.const.TEXT_ADD_PAGE}</div>
  <div class="popup-content pop-mess-cont">

    <div class="setting-row">
      <label for="">{$smarty.const.TEXT_PAGE_NAME}</label>
      <input type="text" name="page_name" value="" class="form-control" style="width: 243px" required="">
    </div>

    <div class="setting-row">
      <label for="">{$smarty.const.TEXT_PAGE_TYPE}</label>
      <select name="page_type" id="" class="form-control" required="">
        <option value="home">{$smarty.const.TEXT_HOME}</option>
        <option value="product">{$smarty.const.TEXT_PRODUCT}</option>
        <option value="products">{$smarty.const.TEXT_LISTING_PRODUCTS}</option>
        <option value="categories">{$smarty.const.TEXT_LISTING_CATEGORIES}</option>
        <option value="info">{$smarty.const.TEXT_INFORMATION}</option>
        <option value="custom">{$smarty.const.ENTRY_CUSTOM_LINK}</option>
      </select>
    </div>

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
        $.get('{$action}', $('#add-page').serializeArray(), function(d){
          $('.pop-mess-cont .error').remove();
          if (d.code == 1){
            $('.pop-mess-cont').prepend('<div class="error">'+d.text+'</div>')
          }
          if (d.code == 2){
            $('.pop-mess-cont').prepend('<div class="info">'+d.text+'</div>');
            setTimeout(function(){
              location.reload();
            }, 1000)
          }
        }, 'json');

        return false
      })
    })
  })(jQuery)
</script>