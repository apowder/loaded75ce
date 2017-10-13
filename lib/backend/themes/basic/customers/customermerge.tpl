<div class="mergeCustomer">
<div class="info-message"><i class="icon-info-circle"></i> {$smarty.const.TEXT_THE_PHONE} <strong>'{$cInfo->customers_telephone}'</strong> {$smarty.const.TEXT_AND_EMAIL} <strong>'{$cInfo->customers_email_address}</strong>'  {$smarty.const.TEXT_MERGING_CUSTOMER}</div>
<div class="merge_title"><i class="icon-checked"></i> {$smarty.const.TEXT_CHOOSE_CUSTOMER}</div>
<form name="customer_merge" id="customer_merge" onSubmit="return doMerge();">
<input type="hidden" name="customers_id" id="parent_id" value="{$cInfo->customers_id}">
<div class="row">
    <div class="col-md-6">
        <div class="merge-title">
            <span class="customer_icon"><i class="icon-user"></i></span>
            <div class="customer_title">
                <div class="customer_t">{$smarty.const.TEXT_CUSTOMER} 1</div>
                <div class="customer_n">{$cInfo->customers_firstname} {$cInfo->customers_lastname}</div>
            </div>
        </div>
        <div class="widget box">
            <div class="widget-header">
                <h4>
                    <i class="icon-map-marker"></i>
                    {$smarty.const.TEXT_ADDRESS}
                </h4>
            </div>
            <div class="widget-content">
                <div class="defaut_add_title">{$smarty.const.TEXT_DEFAULT}</div>
                <div class="defaut_add_value">{$defaultAddress.text}</div>
                {foreach $addresses as $keyvar => $address}
                <div class="default_add_check">
                    <input type="checkbox" name="address_id[]" value="{$address.id}" checked>
                    <label>{$address.text}</label>
                </div>
                {/foreach}
                </div>
            </div>
        </div>
    <div class="col-md-6">
        <div class="merge-title">
            <span class="customer_icon"><i class="icon-user"></i></span>
            <div class="customer_title">
                <div class="customer_t">{$smarty.const.TEXT_CUSTOMER} 2</div>
                <div class="customer_in auto-wrapp" style="position: relative; width: 100%;">
                    <input type="text" name="customer" id="selectCustomer" class="form-control" placeholder="{$smarty.const.TEXT_CHOOSE_TYPE}">
                    <input type="hidden" name="sacrifice_id" value="0" id="child_id">
                    <button class="btn-primary btn">{$smarty.const.TEXT_CHOOSE}</button>
                </div>
            </div>
        </div>
        <div class="widget box">
            <div class="widget-header">
                <h4>
                    <i class="icon-map-marker"></i> {$smarty.const.TEXT_ADDRESS}
                </h4>
            </div>
                <div class="widget-content" id="management_data"></div>
        </div>
    </div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="resetStatement()" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_MERGE}</button></div>
</div>
</form>
</div>
<script type="text/javascript">
    function doMerge() {
        if ($("#parent_id").val() == $("#child_id").val()) {
            return false;
        }
        $.post("{$app->urlManager->createUrl('customers/do-customer-merge')}", $('#customer_merge').serialize(), function(data, status){
            if (status == "success") {
                $(window).scrollTop(0);
                $('.mergeCustomer').html(data);
            }
        },"html");
        return false;
    }
    function resetStatement() {
        window.history.back();
        return false;
    }  
    
    $(document).ready(function(){
    $(window).resize(function(){
        var height_box = 0;
        $('.mergeCustomer .widget-content').each(function(){
        if($(this).height() > height_box){
            height_box = $(this).height();
        }
        })
        $('.mergeCustomer .widget-content').css('min-height',height_box+20);
    })
    $(window).resize();
    
    $('#selectCustomer').autocomplete({
            source: "{$app->urlManager->createUrl('orders/customer')}",
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.auto-wrapp',
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
                $("#child_id").val(ui.item.id);
                if(ui.item.id != null){ 
                    $.post("{$app->urlManager->createUrl('customers/customer-merge-info')}", { 'customers_id' : {$cInfo->customers_id}, 'sacrifice_id' : ui.item.id }, function(data, status){
                        if (status == "success") {
                            $('#management_data').html(data);
                        }
                    },"html");
                }
            },
        }).focus(function () {
          $(this).autocomplete("search");
        });
        
    })
</script>