<div class="search">
  <form action="{$link}" method="get">
    <input type="text" name="keywords" placeholder="{$smarty.const.ENTER_YOUR_KEYWORDS}" value="{$keywords}" />
{if $smarty.const.SEARCH_IN_DESCRIPTION == 'True'}
    <input type="hidden" name="search_in_description" value="1" />
{/if}
    <button type="submit"></button>
    {$extra_form_fields}
  </form>
</div>
<script type="text/javascript">
  tl(function(){

    var input_s = $('.search input');
    input_s.attr({
      autocomplete:"off"
    });

    input_s.keyup(function(e){
      jQuery.get('catalog/search-suggest', {
        keywords: $(this).val()
      }, function(data){
        $('.suggest').remove();
        $('.search').append('<div class="suggest">'+data+'</div>')
      })
    });
    input_s.blur(function(){
      setTimeout(function(){
        $('.suggest').hide()
      }, 200)
    });
    input_s.focus(function(){
      $('.suggest').show()
    })

  })
</script>