{use class="yii\helpers\Html"}
{use class="common\helpers\Acl"}
<!--=== Page Content ===-->
<div id="customer_management_data">
<!--===Customer Edit ===-->
<form name="customer_edit" id="customers_edit" onSubmit="return check_form();">
{Html::input('hidden', 'customers_id', $cInfo->customers_id)}
{Html::input('hidden', 'individual_id', $cInfo->admin_id)}
<div class="row">
    <div class="col-md-12">
        <div class="widget-content fields_style">
            <div class="cedit-top after">
                <div class="cedit-block cedit-block-1">
                    <div class="status-left" style="float: none;">
                        <span>{$smarty.const.ENTRY_ACTIVE}</span>
                        <input type="checkbox" value="1" name="customers_status" class="check_bot_switch_on_off" {if $cInfo->customers_status == 1}checked{/if} />
                    </div>
                </div>
                <div class="cedit-block cedit-block-2">
                    <div class="cr-ord-cust">
                        <span>{$smarty.const.TEXT_DATE_OF_LAST_ORDER}</span>
                        <div>{$cInfo->last_purchased}</div>
                        {$cInfo->last_purchased_days}
                    </div>
                </div>
                <div class="cedit-block cedit-block-3">
                    <div class="cr-ord-cust">
                        <span>{$smarty.const.TEXT_ORDER_COUNT}</span>
                        <div>{$cInfo->total_orders}</div>
                    </div>
                </div>
                <div class="cedit-block cedit-block-4">
                    <div class="cr-ord-cust">
                        <span>{$smarty.const.TEXT_TOTAL_ORDERED}</span>
                        <div>{$cInfo->total_sum}</div>
                    </div>
                </div>
{if $app->controller->view->showGroup}
                <div class="cedit-block cedit-block-5">
                    <div class="cr-ord-cust-link">
                        <a href="{Yii::$app->urlManager->createUrl(['groups/itemedit', 'popup' => 1])}" class="popup"></a>
                        <span>{$smarty.const.ENTRY_GROUP}</span>
                        <b>{$app->controller->view->groupStatusArray[$cInfo->groups_id]}</b>&nbsp;
                        {*Html::dropDownList('groups_id', $cInfo->groups_id, $app->controller->view->groupStatusArray, ['class' => 'form-control'])*}
                    </div>
                    <div class="cr-ord-plat-link">
                        <a href="javascript:void(0)" class=""></a>
                        <span>{$smarty.const.TABLE_HEADING_PLATFORM}:</span>
                      <b>{$platforms[$cInfo->platform_id]}</b>
                    </div>
                </div>
{/if}
            </div>
            <div class="create-or-wrap after create-cus-wrap">
                <div class="cbox-left">
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_PERSONAL}</h4></div>
                        <div class="widget-content">
{if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td after">    
                                        <label>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</label>
                                    <label class="radio-inline">
                                            {Html::radio('customers_gender', $cInfo->customers_gender == 'm', ['class' => 'radio-inline', 'value' => 'm'])}
                                            {$smarty.const.T_MR}
                                    </label>
                                    <label class="radio-inline">
                                            {Html::radio('customers_gender', $cInfo->customers_gender == 'f', ['class' => 'radio-inline', 'value' => 'f'])}
                                            {$smarty.const.T_MRS}
                                    </label>
                                    <label class="radio-inline">
                                            {Html::radio('customers_gender', $cInfo->customers_gender == 's', ['class' => 'radio-inline', 'value' => 's'])}
                                            {$smarty.const.T_MISS}
                                    </label>
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>{Html::input('text', 'customers_firstname', $cInfo->customers_firstname, ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>{Html::input('text', 'customers_lastname', $cInfo->customers_lastname, ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
{/if}
{if $app->controller->view->showDOB}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"}</label>{Html::input('text', 'customers_dob', \common\helpers\Date::date_short($cInfo->customers_dob), ['class' => 'datepicker form-control'])}
                                </div>
                            </div>
{/if}
                          <div style="position: relative">
                            <div class="cr-ord-plat-link-2">
                              <a href="javascript:void(0)" class=""></a>
                            </div>
                          </div>
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="TABLE_HEADING_PLATFORM" required_text=""}</label>{Html::dropDownList('platform_id', $cInfo->platform_id, $platforms, ['class' => 'form-control'])}
                                </div>
                            </div>

{if $app->controller->view->showGroup}

  <div style="position: relative">
                <div class="cr-ord-cust-link-2">
                    <a href="{Yii::$app->urlManager->createUrl(['groups/itemedit', 'popup' => 1])}" class="popup"></a>
                </div>
  </div>
                        
                <div class="w-line-row w-line-row-1">
                    <div class="wl-td">
                        <label>{$smarty.const.ENTRY_GROUP}</label>
                        {Html::dropDownList('groups_id', $cInfo->groups_id, $app->controller->view->groupStatusArray, ['class' => 'form-control'])}
                    </div>
                </div>
                        
{/if}
                <div class="w-line-row w-line-row-1">
                    <div class="wl-td">
                        <label>{field_label const="TEXT_GUEST" required_text=""}</label>
                        {Html::dropDownList('opc_temp_account', $cInfo->opc_temp_account, $app->controller->view->guestStatusArray, ['class' => 'form-control'])}
                    </div>
                </div>
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_ERP_CUSTOMER_ID" required_text=""}</label>{Html::input('text', 'erp_customer_id', $cInfo->erp_customer_id, ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_ERP_CUSTOMER_CODE" required_text=""}</label>{Html::input('text', 'erp_customer_code', $cInfo->erp_customer_code, ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
                            {if $TrustpilotClass = Acl::checkExtension('Trustpilot', 'viewCustomerEdit')}
                                {$TrustpilotClass::viewCustomerEdit($cInfo)}
                            {/if}
                        </div>
                    </div>
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-contact"><h4>{$smarty.const.CATEGORY_CONTACT}</h4></div>
                        <div class="widget-content">
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>{Html::input('text', 'customers_email_address', $cInfo->customers_email_address, ['class' => 'form-control', 'required' => true])}
                                </div>
                            </div>
{if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}                                
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>{Html::input('text', 'customers_telephone', $cInfo->customers_telephone, ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>{Html::input('text', 'customers_landline', $cInfo->customers_landline, ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
                        </div>
                    </div>                               
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']) || in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_COMPANY}</h4></div>
                        <div class="widget-content">
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</label>{Html::input('text', 'customers_company', $cInfo->customers_company, ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT_ID"}</label>{Html::input('text', 'customers_company_vat', $cInfo->customers_company_vat, ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
                        </div>
                    </div>
{/if}
                <div class="widget box box-no-shadow">
                    <div class="widget-header widget-header-credit"><h4>{$smarty.const.CREDIT_AMOUNT}</h4><a href="{Yii::$app->urlManager->createUrl(['customers/credithistory', 'customers_id' => $cInfo->customers_id])}" class="credit_amount_history">{$smarty.const.CREDIT_AMOUNT_EDITING}</a></div>
                        <div class="widget-content">
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{$smarty.const.TEXT_CREDIT}</label>
                                    <div class="credit_wr">
                                        <div class="credit_left">{$cInfo->credit_amount}</div>
                                        <div class="credit_right">
                                            <select name="credit_prefix" class="form-control"><option value="+">+</option><option value="-">-</option></select>
                                            <input name="credit_amount" type="text" class="form-control" placeholder="{$cInfo->credit_amount_mask}"><span class="btn btn-apply" onclick="return check_form();">{$smarty.const.TEXT_APPLY}</span>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                           <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{$smarty.const.TEXT_COMMENT}</label><textarea name="comments" class="form-control textareaform"></textarea>
                                </div>
                            </div>
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <div class="notify_check">
                                        <input name="notify" type="checkbox" class="uniform" checked="checked">
                                        <span>{$smarty.const.TEXT_NOTIFY}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cbox-right">
                    <div class="widget-no-btn box-no-btn box-no-shadow box-no-close">
                        <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_ADDRESS}</h4></div>
                        <div class="widget-content-no-slider">
                            {foreach $addresses as $keyvar => $address}
                            <div class="widget box box-no-shadow">
                                <div class="widget-header">
                                    <div class="btn-address">
                                        <div class="btn-default-add">{$smarty.const.ENTRY_DEFAULT}</div>
                                        {Html::radio('customers_default_address_id', $address.is_default, ['class' => 'check_bot_switch', 'value' => $address.id])}
                                    </div>
                                    <h4>{$address.text}</h4>
                                    <div class="toolbar no-padding">
                                        <div class="btn-group btn-group-no-bg">
                                            {if $address.id > 0}<a href="javascript:void(0)" onclick="deleteAddress(this)" class="btn-del-add-cus"></a>{/if}<span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content">
                                    <div class="w-line-row w-line-row-2">
{if in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div>
                                           <div class="wl-td">
                                               <label>{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</label>{if $address.id > 0}{Html::input('text', 'entry_postcode[]', $address.entry_postcode, ['class' => 'form-control'])}{else}{Html::input('text', 'entry_postcode[]', $address.entry_postcode, ['class' => 'form-control'])}{/if}
                                           </div>
                                       </div>
{/if}
{if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])}
                                       <div>
                                           <div class="wl-td">
                                               <label>{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</label>{if $address.id > 0}{Html::input('text', 'entry_street_address[]', $address.entry_street_address, ['class' => 'form-control'])}{else}{Html::input('text', 'entry_street_address[]', $address.entry_street_address, ['class' => 'form-control'])}{/if}
                                           </div>
                                       </div>
{/if}
                                    </div>
                                    <div class="w-line-row w-line-row-2">
{if in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div>
                                            <div class="wl-td">
                                               <label>{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</label>{Html::input('text', 'entry_suburb[]', $address.entry_suburb, ['class' => 'form-control'])}
                                           </div>
                                        </div>
{/if}
{if in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])}
                                       <div>   
                                           <div class="wl-td">
                                               <label>{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</label>{if $address.id > 0}{Html::input('text', 'entry_city[]', $address.entry_city, ['class' => 'form-control'])}{else}{Html::input('text', 'entry_city[]', $address.entry_city, ['class' => 'form-control'])}{/if}
                                           </div>
                                       </div>
{/if}
                                    </div>
                                    <div class="w-line-row w-line-row-2">
{if $app->controller->view->showState}
                                        <div>
                                           <div class="wl-td">
                                               <label>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                                               <div class="f_td2 f_td_state">
                                                {Html::input('text', 'entry_state[]', $address.entry_state, ['class' => 'form-control', 'id' => "selectState$keyvar"])}
                                               </div>
                                           </div>
                                       </div>
{/if}
{if in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])}
                                       <div>
                                           <div class="wl-td">
                                               <label>{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</label>{Html::dropDownList('entry_country_id[]', $address.entry_country_id, \common\helpers\Country::new_get_countries(), ['class' => 'form-control', 'id' => "selectCountry$keyvar", 'required' => true])}
                                           </div>
                                       </div>
{/if}
                                    </div>
                                </div>
                                {Html::input('hidden', 'address_book_id[]', $address.id)}  
                            </div>
{if $app->controller->view->showState}
<script type="text/javascript">
$('#selectState{$keyvar}').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{$app->urlManager->createUrl('customers/states')}",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry{$keyvar}").val()
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_state',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        },
        select: function(event, ui) {
            $('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="city"]').prop('disabled', false);
            }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
</script>
{/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
                        <div class="w-line-row w-line-row-1 w-line-row-req">
                                <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                        </div>
            <div class="btn-bar" style="padding: 0;">
                <div class="btn-left"><a href="javascript:void(0)" onclick="resetStatement()" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
                <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_CONFIRM}</button></div>
            </div>
                        <div class="btn-wr-center">
                            <a class="btn btn-orders" href="{$app->urlManager->createUrl(['orders/', 'by' => 'cID', 'search' => $cInfo->customers_id])}">{$smarty.const.TEXT_ORDERS}</a>
                            <a class="btn btn-email" href="mailto:{$cInfo->customers_email_address}">{$smarty.const.TEXT_EMAIL}</a>
                            <a class="btn btn-merge" href="{$app->urlManager->createUrl(['customers/customermerge', 'customers_id' => $cInfo->customers_id])}">{$smarty.const.TEXT_MERGE_CUSTOMER}</a>
                            <a class="btn btn-send-coupon popup" href="{$app->urlManager->createUrl(['customers/send-coupon', 'customers_id' => $cInfo->customers_id])}">{$smarty.const.TEXT_SEND_COUPON}</a>
                            <a class="btn btn-new-order btn-primary" href="{$app->urlManager->createUrl(['orders/create', 'Customer' => $cInfo->customers_id, 'back' => 'customers'])}">{$smarty.const.TEXT_CREATE_NEW_ORDER}</a>
                        </div>
        </div>
    </div>
</div>
</form>
<!-- Customer Edit -->
</div>
<script type="text/javascript">
function saveItem() {
        $.post("groups/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('select[name="groups_id"]').html(data);
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove();
            } else {
                alert("Request error.");
            }
        }, "html");
    return false;
}
function cancelStatement() {
    
    return false;
}
function deleteAddress(obj) {
    $(obj).parent().parent().parent().parent().remove();
        return false;
}
function resetStatement() {
    window.history.back();
    return false;
}
function check_form() {
    //var customers_id = $( "input[name='customers_id']" ).val();
    $.post("{$app->urlManager->createUrl('customers/customersubmit')}", $('#customers_edit').serialize(), function(data, status){
        if (status == "success") {
            $(window).scrollTop(0);
            $('#customer_management_data').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
$(document).ready(function(){ 
    $("a.popup").popUp(); 
    $('.credit_amount_history').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCredithistory'><div class='popup-heading credit-head'>{$smarty.const.ENTRY_CREDIT_HISTORY}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $(".check_bot_switch").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(".check_bot_switch_on_off").bootstrapSwitch(
        {
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(this).find(".cbox-left input[type='radio']").uniform();
});
{if $app->controller->view->showDOB}
$( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
        dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
});
{/if}
</script>
<!-- /Page Content -->
