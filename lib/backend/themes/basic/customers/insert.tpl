{use class="yii\helpers\Html"}
<!--=== Page Content ===-->
<div id="customer_management_data">
</div>
<!--===Add Customer ===-->
<form name="customer_edit" id="customers_edit" onSubmit="return check_form();">
{Html::input('hidden', 'customers_id', 0)}
<div class="box-wrap">
    <div class="status-wrapp after">
        <div class="status-left">
            <span>{$smarty.const.ENTRY_ACTIVE}</span>
            <input type="checkbox" value="1" name="customers_status" class="check_bot_switch_on_off" checked />
        </div>
{if $app->controller->view->showGroup}
        <div class="status-right">
            <label>{$smarty.const.ENTRY_GROUP}</label>{Html::dropDownList('groups_id', '', $app->controller->view->groupStatusArray, ['class' => 'form-control'])}
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
                                            {Html::radio('customers_gender', true, ['class' => 'radio-inline', 'value' => 'm'])}
                                            {$smarty.const.T_MR}
                                    </label>
                                    <label class="radio-inline">
                                            {Html::radio('customers_gender', false, ['class' => 'radio-inline', 'value' => 'f'])}
                                            {$smarty.const.T_MRS}
                                    </label>
                                    <label class="radio-inline">
                                            {Html::radio('customers_gender', false, ['class' => 'radio-inline', 'value' => 's'])}
                                            {$smarty.const.T_MISS}
                                    </label>
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>{Html::input('text', 'customers_firstname', '', ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>{Html::input('text', 'customers_lastname', '', ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
{/if}
{if $app->controller->view->showDOB}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_DATE_OF_BIRTH}</label>{Html::input('text', 'customers_dob', '', ['class' => 'datepicker form-control'])}
                        </div>
                    </div>
{/if}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TABLE_HEADING_PLATFORM}</label>{Html::dropDownList('platform_id', '', $platforms, ['class' => 'form-control'])}
                        </div>
                    </div>

                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_ERP_CUSTOMER_ID}</label>{Html::input('text', 'erp_customer_id', '', ['class' => 'form-control', 'required' => false])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_ERP_CUSTOMER_CODE}</label>{Html::input('text', 'erp_customer_code', '', ['class' => 'form-control', 'required' => false])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-contact"><h4>{$smarty.const.CATEGORY_CONTACT}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<span class="fieldRequired">*</span></label>{Html::input('text', 'customers_email_address', $cInfo->customers_email_address, ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
{if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}                                
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>{Html::input('text', 'customers_telephone', '', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>{Html::input('text', 'customers_landline', '', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register']) || in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_COMPANY}</h4></div>
                        <div class="widget-content">
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</label>{Html::input('text', 'customers_company', '', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_COMPANY_VAT_ID, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT_ID"}</label>{Html::input('text', 'customers_company_vat', '', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
                        </div>
                    </div>
{/if}
        </div>
        <div class="cbox-right">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_ADDRESS}</h4></div>
                <div class="widget-content">
{if in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</label>{Html::input('text', 'entry_postcode[]', '', ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
{/if}
{if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</label>{Html::input('text', 'entry_street_address[]', '', ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
{/if}
{if in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</label>{Html::input('text', 'entry_suburb[]', '', ['class' => 'form-control'])}
                        </div>
                    </div>
{/if}
{if in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</label>{Html::input('text', 'entry_city[]', '', ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
{/if}
{if $app->controller->view->showState}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                            <div class="f_td_state">
                                {Html::input('text', 'entry_state[]', '', ['class' => 'form-control', 'id' => "selectState"])}
                            </div>
                        </div>
                    </div>
{/if}
{if in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</label>{Html::dropDownList('entry_country_id[]', STORE_COUNTRY, \common\helpers\Country::new_get_countries(), ['class' => 'form-control', 'id' => "selectCountry", 'required' => true])}
                        </div>
                    </div>
{/if}
                    <!--<div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <input type="checkbox" /> <b>Make default address</b>
                        </div>
                    </div>!-->
                </div>
                <div class="w-line-row w-line-row-1 w-line-row-req w-line-row-abs">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
            </div>
            {Html::input('hidden', 'address_book_id[]', '0')} 
        </div>        
    </div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_INSERT}</button></div>
</div>
</form>
<!-- /Add Customer -->           
<!-- /Page Content -->
<script>
{if $app->controller->view->showState}
$('#selectState').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{$app->urlManager->createUrl('customers/states')}",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry").val()
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
{/if}
function backStatement() {
{if $app->controller->view->redirect == 'customeredit'}
    window.history.back();
{else}    
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
{/if}        
    return false;
}
function check_form() {
    $.post("{$app->urlManager->createUrl(['customers/customersubmit', 'redirect' => $app->controller->view->redirect])}", $('#customers_edit').serialize(), function(data, status){
        if (status == "success") {
            $('#customer_management_data').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
$(document).ready(function(){ 
    $(".check_bot_switch_on_off").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(window).resize(function(){
	$('.cbox-right .box-no-shadow').css('min-height', $('.cbox-left').height() - 20);
    });
    $(window).resize();
    $(this).find(".cbox-left input[type='radio']").uniform();
    
{if $app->controller->view->showDOB}
    $( ".datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
    });
{/if}    
});
</script>