<ul class="accountTop">
  <li>
    <a href="{tep_href_link(FILENAME_ACCOUNT, '', 'SSL')}" class="my_acc_link"><span class="no-text">{$smarty.const.TEXT_MY_ACCOUNT}</span></a>
    <ul class="account_dropdown after{if tep_session_is_registered('customer_id')} logged_ul{/if}">
      {if !tep_session_is_registered('customer_id')}
        <li>
          <h2>{$smarty.const.NEW_CUSTOMER}</h2>
          {$smarty.const.TEXT_BY_CREATING_AN_ACCOUNT}

          <div class="accTop"><a class="btn-1" href="{tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')}">{$smarty.const.CONTINUE}</a></div>
          <div class="accTopBottom display_none">{$smarty.const.TEXT_CONTACT_AND_ASK}</div>

        </li>
        <li>
          <h2>{$smarty.const.RETURNING_CUSTOMER}</h2>
          {$messages_login}

          <form action="{tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL')}" method="post">
            <input type="hidden" name="account_login" value="login">
            <div class="col-left">
              <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
              <input type="text" name="email_address" id="email_address" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
            </div>
            <div class="col-right">
              <label>{field_label const="PASSWORD" required_text=""}</label>
              <input type="password" name="password"/>
            </div>

            <div class="accButtons after">
              <a class="f_pass" href="{tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL')}">{$smarty.const.TEXT_PASSWORD_FORGOTTEN_S}</a>
              <button class="btn-1" type="submit">{$smarty.const.SIGN_IN}</button>
            </div>
          </form>
          <div class="accTopBottom display_none">{$smarty.const.TEXT_ALREADY_HAVE_ACCOUNT}</div>

        </li>
      {else}
        <li class="logged_in">
          <div class="acc_top_title">{$smarty.const.HEADING_ACCOUNT_TOP}</div>
          <ul class="acc_top_link">
            <li><a href="{tep_href_link(FILENAME_ACCOUNT, '', 'SSL')}">{$smarty.const.TEXT_MY_ACCOUNT}</a></li>
            <li><a href="{tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL')}">{$smarty.const.ENTRY_PASSWORD}</a></li>
            <li><a href="{tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL')}">{$smarty.const.TEXT_ADDRESS_BOOK}</a></li>
            <li><a href="{tep_href_link('account/history', '', 'SSL')}">{$smarty.const.HEADER_ORDER_OVERVIEW}</a></li>
            <li><a href="{tep_href_link(FILENAME_LOGOFF, '')}">{$smarty.const.TEXT_LOGOFF}</a></li>
          </ul>
        </li>
      {/if}
    </ul>
  </li>
</ul>
<script type="text/javascript">
  tl(function(){
    var account_dropdown = $('.account_dropdown');
    var key = true;
    var account_position = function(){
      if (key){
        key = false;
        setTimeout(function(){
          account_dropdown.show();
          key = true;
          account_dropdown.css({
            'top': $('.my_acc_link').height() + 9,
            'width': '{if !tep_session_is_registered('customer_id')}935{else}250{/if}',
            'right': 0
          });
          if (account_dropdown.width() > $(window).width()){
            var w = $(window).width() * 1 - 20;
            account_dropdown.css({
              width: w + 'px'
            })
          }
          if (account_dropdown.offset().left < 0){
            var r = account_dropdown.offset().left * 1 - 15;
            account_dropdown.css({
              right: r + 'px'
            })
          }
          account_dropdown.hide();
        }, 300)
      }
    };

    account_position();
    $(window).on('resize', account_position)
  })
</script>