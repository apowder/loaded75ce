{use class="frontend\design\Info"}
{$message_review}

<div id="stars-default"><input type="hidden" name="rating" id="rating" value="{$review_rate}"/></div>

<div class="">
  <textarea name="review" cols="30" rows="5" style="width: 100%" id="review">{$review_text|escape:'html'}</textarea>
  <div style="padding-bottom: 10px">{$smarty.const.TEXT_NO_HTML}</div>
</div>

<div class="buttons">
  <div class="left-buttons"><span class="btn btn-cancel">{$smarty.const.CANCEL}</span></div>
  <div class="right-buttons"><span class="btn btn-submit">{$smarty.const.SEND_REVIEW}</span></div>
</div>

<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    $("#stars-default").rating();

    $('.btn-cancel').on('click', function(){
      $.get('{$link_cancel}', function(d){
        $('.product-reviews').html(d)
      })
    });

    $('.btn-submit').on('click', function(){
      $.post('{$link_write}', {
        action: 'process',
        rating: $('#rating').val(),
        review: $('#review').val(),
        products_id: '{$products_id}'
      }, function(d){
        $('.product-reviews').html(d);
        $('.pop-up-content').html(d);		
      })
    })
  });
</script>