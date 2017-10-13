{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
<div class="multi-page-checkout">
  <div class="checkout-login-page checkout-step active">
    <div class="login-box">
      <div class="login-box-heading">{$smarty.const.CONTINUE_AS_GUEST}</div>
      <div class="middle-form">

        {$message_checkout_as_guest}

        <form action="{$action}" method="post">
          <input type="hidden" name="checkout_login" value="as_guest">
          <label for="email_address-1">
            {field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}
          </label>
          <input type="text" name="email_address" id="email_address-1" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
          <div class="center-buttons">
            <button type="submit" class="btn-2">{$smarty.const.CONTINUE}</button>
          </div>

          <div class="info">{$smarty.const.CAN_CREATE}</div>

        </form>

      </div>
    </div>


    <div class="login-box">
      <div class="login-box-heading">{$smarty.const.RETURNING_CUSTOMER}</div>
      <div class="middle-form">

        {$message_checkout_login}

        <form action="{$action}" method="post">
          <input type="hidden" name="checkout_login" value="login">

          <div class="col-left">
            <label for="email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
            <input type="text" name="email_address" id="email_address"/>
          </div>
          <div class="col-right">
            <label for="password1">{field_label const="PASSWORD" required_text=""}</label>
            <input type="password" name="password" id="password1"/>
          </div>
          <p style="clear:both;margin:0;padding:15px 0 0;">
            <a href="{tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL')}">{$smarty.const.TEXT_PASSWORD_FORGOTTEN_S}</a>
          </p>
          <div class="center-buttons">
            <button class="btn-2" type="submit">{$smarty.const.SIGN_IN}</button>
          </div>
          <div class="info">{$smarty.const.CART_MERGED} (<a href="#cart-merged" class="pop-up-link">{$smarty.const.MORE_INFO}</a>)</div>
          <div id="cart-merged" style="display: none;">
            <div class="pop-up-info">
              <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_1}</div>
              <p>{$smarty.const.SUB_HEADING_TEXT_1}</p>
              <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_2}</div>
              <p>{$smarty.const.SUB_HEADING_TEXT_2}</p>
              <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_3}</div>
              <p>{$smarty.const.SUB_HEADING_TEXT_3}</p>
            </div>
            <div class="center-buttons">
              <span class="btn btn-cancel">{$smarty.const.CONTINUE}</span>
            </div>
          </div>
        </form>

      </div>
    </div>


    <div class="login-box">
      <div class="login-box-heading">{$smarty.const.REGISTER}</div>
      <div class="middle-form">

        {$message_checkout_create_account}

        <div class="info">{$smarty.const.BENEFITS_FROM_CREATING}</div>

        {assign var=re1 value='.{'}
        {assign var=re2 value='}'}

        <form action="{$action}" method="post">
          <input type="hidden" name="checkout_login" value="create_account">
          {if in_array(ACCOUNT_COMPANY, ['required_register', 'visible_register'])}
              <div class="col-left">
                <label for="company">{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</label>
                {if ACCOUNT_COMPANY == 'required_register'}
                    <input type="text" name="company" id="company" value="{$company|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_COMPANY_ERROR}"/>
                {else}
                    <input type="text" name="company" id="company" value="{$company|escape:'html'}"/>
                {/if}
              </div>
          {/if}
          {if in_array(ACCOUNT_COMPANY_VAT_ID, ['required_register', 'visible_register'])}
              <div class="col-right">
                <label for="company_vat">{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT_ID"}</label>
                {if ACCOUNT_COMPANY_VAT_ID == 'required_register'}
                    <input type="text" name="company_vat" id="company_vat" value="{$company_vat|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_VAT_ID_ERROR}"/>
                {else}
                    <input type="text" name="company_vat" id="company_vat" value="{$company_vat|escape:'html'}"/>
                {/if}
              </div>
          {/if}        
          {if in_array(ACCOUNT_GENDER, ['required_register', 'visible_register'])}
              <div class="col-full col-gender">    
                <span>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</span>
                <input type="radio" name="gender" value="m"{if $custom_gender == 'm'} checked{/if} id="male">
                <label for="male">{$smarty.const.T_MR}</label>    
                <input type="radio" name="gender" value="f"{if $custom_gender == 'f'} checked{/if} id="female">
                <label for="female">{$smarty.const.T_MRS}</label>
                <input type="radio" name="gender" value="s"{if $custom_gender == 's'} checked{/if} id="miss">
                <label for="miss">{$smarty.const.T_MISS}</label>
              </div>
          {/if}       
          {if in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])}
              <div class="col-left">
                <label for="firstname">{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
                {if ACCOUNT_FIRSTNAME == 'required_register'}
                    <input type="text" name="firstname" id="firstname" value="{$customers_first_name|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"/>
                {else}
                    <input type="text" name="firstname" id="firstname" value="{$customers_first_name|escape:'html'}"/>
                {/if}
              </div>
          {/if}
          {if in_array(ACCOUNT_LASTNAME, ['required_register', 'visible_register'])}
              <div class="col-right">
                <label for="lastname">{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
                {if ACCOUNT_LASTNAME == 'required_register'}
                    <input type="text" name="lastname" id="lastname" value="{$customers_last_name|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"/>
                {else}
                    <input type="text" name="lastname" id="lastname" value="{$customers_last_name|escape:'html'}"/>
                {/if}
              </div>
          {/if}
          {if in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])}
              <div class="col-left">
                <label for="telephone">{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                {if ACCOUNT_TELEPHONE == 'required_register'}
                    <input type="text" name="telephone" id="telephone" value="{$telephone|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}"/>
                {else}
                    <input type="text" name="telephone" id="telephone" value="{$telephone|escape:'html'}"/>
                {/if}
              </div>
          {/if}
          {if in_array(ACCOUNT_LANDLINE, ['required_register', 'visible_register'])}
              <div class="col-right">
                <label for="landline">{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
                {if ACCOUNT_LANDLINE == 'required_register'}
                    <input type="text" name="landline" id="landline" value="{$landline|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}"/>
                {else}
                    <input type="text" name="landline" id="landline" value="{$landline|escape:'html'}"/>
                {/if}
              </div>
          {/if}
          <div class="col-full">
            <label for="email_address-2">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
            <input type="email" name="email_address" id="email_address-2" value="{$customers_email_address|escape:'html'}" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
          </div>
          <div class="password-row">
            <div class="col-left">
              <label for="password" class="password-info">
                <div class="info-popup"><div>{sprintf($smarty.const.TEXT_HELP_PASSWORD, $smarty.const.STORE_NAME)}</div></div>
                    {field_label const="PASSWORD" required_text="*"}
              </label>
              <input type="password" name="password" id="password" data-pattern="{$re1}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_PASSWORD_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}"/>
            </div>
            <div class="col-right">
              <label for="confirmation">{field_label const="PASSWORD_CONFIRMATION" required_text="*"}</label>
              <input type="password" name="confirmation" id="confirmation" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation="password"/>
            </div>
          </div>
          {if in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])}
              <div class="col-left" style="position: relative">
                <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                <div style="position: relative">
                  <input type="text" name="dob" id="dob" value="{$customers_dob|escape:'html'}"{if ACCOUNT_DOB == 'required_register'} data-required="{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"{/if} />
                </div>
              </div>
          {/if}
          <div class="col-right">
            <label for="">{$smarty.const.RECEIVE_REGULAR_OFFERS}</label>
            <input type="checkbox" name="newsletter" value="1" id="newsletter" class="check-on-off" {if $customers_newsletter} checked="checked"{/if}/>
          </div>
          {if $showAddress}
              {if in_array(ACCOUNT_POSTCODE, ['required_register', 'visible_register'])}
                  <div class="col-left">
                    <label for="postcode">{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</label>
                    {if ACCOUNT_POSTCODE == 'required_register'}
                        <input type="text" name="postcode" id="postcode" value="{$postcode|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, $smarty.const.ENTRY_POSTCODE_MIN_LENGTH)}"/>
                    {else}
                        <input type="text" name="postcode" id="postcode" value="{$postcode|escape:'html'}" />
                    {/if}
                  </div>
              {/if}
              {if in_array(ACCOUNT_STREET_ADDRESS, ['required_register', 'visible_register'])}
                  <div class="col-right">
                    <label for="street_address">{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</label>
                    {if ACCOUNT_STREET_ADDRESS == 'required_register'}
                        <input type="text" name="street_address" id="street_address" value="{$street_address|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, $smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH)}"/>
                    {else}
                        <input type="text" name="street_address" id="street_address" value="{$street_address|escape:'html'}"/>
                    {/if}
                  </div>
              {/if}
              {if in_array(ACCOUNT_SUBURB, ['required_register', 'visible_register'])}
                  <div class="col-left">
                    <label for="suburb">{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</label>
                    {if ACCOUNT_SUBURB == 'required_register'}
                        <input type="text" name="suburb" id="suburb" value="{$suburb|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_SUBURB_ERROR}"/>
                    {else}
                        <input type="text" name="suburb" id="suburb" value="{$suburb|escape:'html'}" />
                    {/if}
                  </div>
              {/if}
              {if in_array(ACCOUNT_CITY, ['required_register', 'visible_register'])}
                  <div class="col-right">
                    <label for="city">{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</label>
                    {if ACCOUNT_CITY == 'required_register'}
                        <input type="text" name="city" id="city" value="{$city|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_CITY_ERROR, $smarty.const.ENTRY_CITY_MIN_LENGTH)}"/>
                    {else}
                        <input type="text" name="city" id="city" value="{$city|escape:'html'}"/>
                    {/if}
                  </div>
              {/if}
              {if in_array(ACCOUNT_STATE, ['required_register', 'visible_register'])}
                  <div class="col-left">
                    <label for="state">{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                    {if ACCOUNT_STATE == 'required_register'}
                        <input type="text" name="state" id="state" value="{$state|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_STATE_ERROR, $smarty.const.ENTRY_STATE_MIN_LENGTH)}"/>
                    {else}
                        <input type="text" name="state" id="state" value="{$state|escape:'html'}" />
                    {/if}
                  </div>
              {/if}
              {if in_array(ACCOUNT_COUNTRY, ['required_register', 'visible_register'])}
                  <div class="col-right">
                    <label for="country">{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</label>
                    {Html::dropDownList('country', $country, \common\helpers\Country::new_get_countries(), ['id' => "country", 'required' => (ACCOUNT_COUNTRY == 'required_register')])}
                  </div>
              {/if}
          {/if}
          <div class="center-buttons">
            <button class="btn-2" type="submit">{$smarty.const.CREATE}</button>
          </div>

        </form>
      </div>
    </div>

  </div>

    <div class="checkout-step">
        <div class="checkout-heading"><span class="count">1</span> Delivery details</div>
    </div>
    <div class="checkout-step">
        <div class="checkout-heading"><span class="count">2</span> Payment details</div>
    </div>
    <div class="checkout-step">
        <div class="checkout-heading"><span class="count">3</span> Confirmation</div>
    </div>

</div>


<script type="text/javascript" src="{Info::themeFile('/js/password-strength.js')}"></script>

{frontend\design\DatePickerJs::widget(['selector' => '#dob', 'params' => ['startView' => 3]])}

<script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/password-strength.js')}',
        '{Info::themeFile('/js/jquery.tabs.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function () {

        $('#password').passStrength({
            shortPassText: "{$smarty.const.TEXT_TOO_SHORT|strip}",
            badPassText: "{$smarty.const.TEXT_WEAK|strip}",
            goodPassText: "{$smarty.const.TEXT_GOOD|strip}",
            strongPassText: "{$smarty.const.TEXT_STRONG|strip}",
            samePasswordText: "{$smarty.const.TEXT_USERNAME_PASSWORD_IDENTICAL|strip}",
            userid: "#firstname"
        });

        $('#confirmation, #password').on('keyup', function () {
            var _this = $('#confirmation');
            if ($(_this).val() != $('#password').val() && $(_this).val() != '') {
                $(_this).prev(".pass-strength").remove();
                $(_this).before('<span class="pass-strength pass-no-match"><span>{$smarty.const.TEXT_NO_MATCH|strip}</span></span>');
            } else if ($(_this).val() == '') {
                $(_this).prev(".pass-strength").remove();
            } else {
                $(_this).prev(".pass-strength").remove();
                $(_this).before('<span class="pass-strength pass-match"><span>{$smarty.const.TEXT_MATCH|strip}</span></span>');
            }
        });

        $('.checkout-login-page').tlTabs({
            tabContainer: '.login-box',
            tabHeadingContainer: '.login-box-heading'
        });
  {if $active_tab=='create_account'}
        $('.checkout-login-page .tab-navigation li:nth-child(3)').find('span').trigger('click')
  {/if}
  {if $active_tab=='login'}
        $('.checkout-login-page .tab-navigation li:nth-child(2)').find('span').trigger('click')
  {/if}

        $('.pop-up-link').popUp();

        $('.middle-form input').validate();


        $(".check-on-off").bootstrapSwitch({
            offText: 'NO',
            onText: 'YES',
            onSwitchChange: function () {
                $(this).closest('form').trigger('cart-change')
            }
        });

        var input_email_address = $('input[name="email_address"]');
  
    });


</script>