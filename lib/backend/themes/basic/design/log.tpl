
{include 'menu.tpl'}
<div class="drop-list log-list">
{function name=menuTree}
  {foreach $tree as $item}
    {if $item.branch_id == $parent}


      <li>
        <div class="item-handle">
          <div class="item-close closed" style="display: block;"></div>
          <div class="restore" data-id="{$item.steps_id}">{$smarty.const.IMAGE_RESTORE}</div>
          <div class="date">{$item.date_added}</div>

          <span class="no-link"><span title="{$admins[$item.admin_id].admin_email_address}">{$admins[$item.admin_id].admin_firstname} {$admins[$item.admin_id].admin_lastname}</span> {$item.text}</span>


        </div>


        <ul>
          {call menuTree parent=$item.steps_id}
        </ul>
      </li>


    {/if}
  {/foreach}
{/function}

<ul>
{call menuTree parent=0}
</ul>
</div>
<script type="text/javascript">
  (function($){
    $(function(){

      $('.log-list li').each(function(){
        var _this = $(this);
        if ($('> ul > li', _this).length > 0){
          _this.addClass('has-sub');
        }
        $('> div > .item-close', _this).on('click', function(){
          if ($(this).hasClass('closed')){
            $(this).removeClass('closed');
            $('> ul', _this).slideDown(200)
          } else {
            $(this).addClass('closed')
            $('> ul', _this).slideUp(200)
          }
        })
      });
      $('.log-list li ul').hide();

      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          location.reload();
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          location.reload();
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });


      $('.restore').on('click', function(){
        $.get('design/step-restore', { 'id': $(this).data('id')}, function(data){
          if (data != '') {
            $('body').append(data);
          } else {
            location.reload();
          }
        })
      })
    })
  })(jQuery)
</script>