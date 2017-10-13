<form action="{Yii::$app->urlManager->createUrl(['catalog/gift-card', 'action' => 'add_gift_card'])}" method="post" enctype="multipart/form-data" name="product_edit">
<div class="gift-card-form form-inputs columns">
  <div class="col-2">
    <label>
      <span>{field_label const="SELECT_AMOUNT" required_text=""}</span>
      <select name="gift_card_price" id="gift-amount">
          {foreach $giftAmount as $value => $gift}
            <option value="{$value}">{$gift}</option>
        {/foreach}
      </select>
    </label>
  </div>
  <div class="col-1" style="clear:both;">
    <label>
      <span>{field_label const="PERSONAL_MESSAGE" required_text=""}</span>
      <textarea cols="30" rows="10" name="virtual_gift_card_message" id="gift-message"></textarea>
    </label>
    <div class="limitation">{sprintf($smarty.const.CHARACTERS_REMAINING, '160')}</div>
  </div>
  <div class="col-1">
    <label>
      <span>{field_label const="RECIPIENTS_NAME" required_text=""}</span>
      <input type="text" name="virtual_gift_card_recipients_name" />
    </label>
  </div>
  <div></div>
  <div class="col-2">
    <label>
      <span>{field_label const="RECIPIENTS_EMAIL" required_text="*"}</span>
      <input type="text" name="virtual_gift_card_recipients_email" />
    </label>
  </div>
  <div class="col-2">
    <label>
      <span>{field_label const="TEXT_CONFIRM_EMAIL" required_text="*"}</span>
      <input type="text" name="virtual_gift_card_confirm_email" />
    </label>
  </div>
  <div class="col-1">
    <label>
      <span>{field_label const="TEXT_YOUR_NAME" required_text=""}</span>
      <input type="text" name="virtual_gift_card_senders_name" />
    </label>
  </div>
  <div class="button"><button class="btn-2 add-to-cart">{$smarty.const.ADD_TO_CART}</button></div>
</div>
</form>
<script type="text/javascript">
  tl(function(){
    $('.amount-view').html($('#gift-amount option:selected').text());
    $('#gift-amount').on('change', function(){
      $('.amount-view').html($('#gift-amount option:selected').text());
    })

    $('.message-view').html($('#gift-amount option:selected').text());
    $('#gift-message').on('keyup', function(){
      $('.message-view').html($(this).val());
    })

  })
</script>