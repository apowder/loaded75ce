<!--=== Page Header ===-->
<!--<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>-->
<!-- /Page Header -->
{\backend\assets\OrderAsset::register($this)|void}
<!--=== Page Content ===-->
<div class="widget box box-wrapp-blue filter-wrapp widget-closed widget-fixed">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER} <form action="{$app->urlManager->createUrl('orders/process-order')}" method="get" class="go-to-order filterFormHead"><label>{$smarty.const.TEXT_GO_TO_ORDER}</label> <input type="text" class="form-control" name="orders_id"/> <button type="submit" class="btn">{$smarty.const.TEXT_GO}</button></form><form id="filterFormHead" name="filterFormHead" class="filterFormHead" onsubmit="return applyFilter();"><label>{$smarty.const.TEXT_SEARCH_BY}</label><select class="form-control" name="by">
                        {foreach $app->controller->view->filters->by as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select><input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" /><button type="submit" class="btn">{$smarty.const.TEXT_GO}</button></form>
        {if count($app->controller->view->filters->admin_choice)}
            <div class="dropdown btn-link-create" style="float:right">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {$smarty.const.TEXT_UNSAVED_CARTS}
                <i class="icon-caret-down small"></i>
            </a>
            <ul class="dropdown-menu">
                {foreach $app->controller->view->filters->admin_choice as $choice}
                <li>{$choice}</li>
                {/foreach}
            </ul>
        </div>
        {/if}
        </h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        
            <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                <div class="wrap_filters after {if $isMultiPlatform}wrap_filters_4{/if}">
                    <div class="item_filter item_filter_1 choose_platform">
                        {if $isMultiPlatform}
                            <div class="tl_filters_title">{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</div>
                            <div class="f_td f_td_radio ftd_block tl_fron_height">
                                <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
                                        {foreach $platforms as $platform}
                                    <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} checked="checked"{/if}> {$platform['text']}</label></div>
                                        {/foreach}
                            </div>
                        {/if}
                    </div>
                    <div class="item_filter item_filter_2">
                        <div class="tl_filters_title">{$smarty.const.TABLE_HEADING_STATUS}/{$smarty.const.TEXT_STOCK}</div>
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_STATUS}</label>
                            <select name="status" class="form-control">
                                {foreach $app->controller->view->filters->status as $Item}
                                    <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_ORDER_PLACED}</div>
                        <div class="wl-td w-tdc">
                             <label class="radio_label"><input type="radio" name="date" value="presel" id="presel" {if $app->controller->view->filters->presel}checked{/if} /> {$smarty.const.TEXT_PRE_SELECTED}</label>
                             <select name="interval" class="form-control" {if $app->controller->view->filters->exact}disabled{/if}>
                                    {foreach $app->controller->view->filters->interval as $Item}
                                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                    {/foreach}
                                </select>
                        </div>
                        <div class="wl-td wl-td-from w-tdc">
                            <label class="radio_label"><input type="radio" name="date" value="exact" id="exact" {if $app->controller->view->filters->exact}checked{/if} /> {$smarty.const.TEXT_EXACT_DATES}</label><table width="100%" cellpadding="0" cellspacing="0"><tr><td><span>{$smarty.const.TEXT_FROM}</span><input id="from_date" type="text" value="{$app->controller->view->filters->from}" autocomplete="off" name="from" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td></tr><tr><td><span class="sp_marg">{$smarty.const.TEXT_TO}</span><input id="to_date" type="text" value="{$app->controller->view->filters->to}" autocomplete="off" name="to" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td></tr></table>
                        </div>                                
                    </div>
                    <div class="item_filter item_filter_3">
                        <div class="tl_filters_title">{$smarty.const.TEXT_BY_DELIVERY}</div>
                        <div class="wl-td f_td_country">
                            <label>{$smarty.const.ENTRY_COUNTRY}:</label>
                            <input name="delivery_country" value="{$app->controller->view->filters->delivery_country}" id="selectCountry" type="text" class="form-control" placeholder="{$smarty.const.TEXT_TYPE_COUNTRY}" />
                        </div>
                        {if $app->controller->view->showState == true}
                        <div class="wl-td f_td_state">
                            <label>{$smarty.const.ENTRY_STATE}:</label>
                           <input name="delivery_state" value="{$app->controller->view->filters->delivery_state}" id="selectState" type="text" class="form-control" placeholder="{$smarty.const.TEXT_TYPE_COUNTY}" {if $app->controller->view->filters->delivery_country == ''}disabled{/if} />
                        </div>
                        {/if}
                    </div>
                    <div class="item_filter item_filter_4">
                        {*
                        <div class="tl_filters_title">{$smarty.const.TEXT_AMOUNT_FILTER}</div>
                        <div class="wl-td wl-td-from" style="padding-left: 0;">
                            <table width="100%" cellpadding="0" cellspacing="0"><tr><td><span>{$smarty.const.TEXT_FROM}</span><input id="" type="text" value="" autocomplete="off" name="from" class="datepicker form-control form-control-small" /></td><td><span class="sp_marg">{$smarty.const.TEXT_TO}</span><input id="" type="text" value="" autocomplete="off" name="to" class="datepicker form-control form-control-small" /></td></tr></table>
                        </div> *}
                        <div class="tl_filters_title {*tl_filters_title_border*}">{$smarty.const.TEXT_PAYMENT_SHIPPING}</div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_PAYMENT_METHOD}</label>
                            <select name="payments" class="form-control">
                                {foreach $app->controller->view->filters->payments as $Item}
                                    <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}:</label>
                            <select name="shipping" class="form-control">
                                {foreach $app->controller->view->filters->shipping as $Item}
                                    <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filters_btn">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
        <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
        <input type="hidden" name="fs" value="{$app->controller->view->filters->fs}" />
                </div>
            </form>
        
    </div>
</div>

<!--===Orders List ===-->
<div class="order-wrap">    
<div class="row order-box-list order-sc-text">
    <div class="col-md-12">
        <div class="widget-content">
            <div class="btn-wr after btn-wr-top btn-wr-top1 disable-btn">
                <div>
                    <a href="javascript:void(0)" onclick="invoiceSelectedOrders();" class="btn btn-no-margin">{$smarty.const.TEXT_BATCH_INVOICE}</a><a href="javascript:void(0)" onclick="packingslipSelectedOrders()" class="btn">{$smarty.const.TEXT_BATCH_PACKING_SLIP}</a><a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a><a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                </div>
                <div>
                </div>
            </div>   
            <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tabl-res double-grid table-orders" data_ajax="orders/orderlist" checkable_list="">
                <thead>
                    <tr>
                        {foreach $app->controller->view->ordersTable as $tableItem}
                            <th{if $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                    </tr>
                </thead>

            </table>
            <div class="btn-wr after disable-btn">
                <div>
                    <a href="javascript:void(0)" onclick="invoiceSelectedOrders();" class="btn btn-no-margin">{$smarty.const.TEXT_BATCH_INVOICE}</a><a href="javascript:void(0)" onclick="packingslipSelectedOrders()" class="btn">{$smarty.const.TEXT_BATCH_PACKING_SLIP}</a><a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a><a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                </div>
                <div>
                </div>
            </div>                
        </div>
    </div>
</div>
<!-- /Orders List -->
        
        
                                
<script type="text/javascript">
function getTableSelectedIds() {
    var selected_messages_ids = [];
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_ids[selected_messages_count] = aaa;
            selected_messages_count++;
        }
    });
    return selected_messages_ids;
}
function getTableSelectedCount() {
    //var selected_messages_ids = [];
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            //selected_messages_ids[selected_messages_count] = aaa;
            selected_messages_count++;
        }
    });
    return selected_messages_count;
}
function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}
function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}
function cancelStatement() {
    var orders_id = $('.table tbody tr.selected').find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('orders/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
}
function setFilterState() {
    orig = $('#filterForm, #filterFormHead').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}
function resetStatement() {
    setFilterState();
    if ($('#customers_edit').is('form')){
      createOrder();
      $(window).scrollTop(0);
      return;
    }
    $("#order_management").hide();
    switchOnCollapse('orders_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    //$('.table tbody tr:eq(0)').click();
    $(window).scrollTop(0);
    return false;
}
function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    //$("#order_management").hide();
    var orders_id = $(obj).find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('orders/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
}
function onUnclickEvent(obj, table) {
    //$("#order_management").hide();
}
function check_form() {
//ajax save
    $("#order_management").hide();
    //var orders_id = $( "input[name='orders_id']" ).val();
    $.post("{$app->urlManager->createUrl('orders/ordersubmit')}", $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
    /*        
            switchOnCollapse('orders_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            setTimeout('$(".cell_identify[value=\''+orders_id+'\']").click();', 500);
            //$(".cell_identify[value='"+orders_id+"']").click();
    */        
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    //$('#order_management_data').html('');
    return false;
}
function deleteOrder() {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('orders/orderdelete')}", $('#orders_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function confirmDeleteOrder(orders_id) {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('orders/confirmorderdelete')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}
function reassignOrder(orders_id) {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('orders/order-reassign')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}
function confirmedReassignOrder() {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('orders/confirmed-order-reassign')}", $('#orders_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
                                
function createOrder(){
    $.post("{$app->urlManager->createUrl('orders/createorder')}", $('form[name=create_order]').serialize(), function(data, status){
        if (status == "success") {
            switchOffCollapse('orders_list_collapse');
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }  
    },"html");
    return false;                                  
}
                                
function createOrderProcess(){
    $.post("{$app->urlManager->createUrl('orders/createorderprocess')}", $('form[name=create_order]').serialize(), function(data, status){
        if (status == "success") {
            switchOffCollapse('orders_list_collapse');
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}   
function editCustomer(customers_id) {
    $("#order_management").hide();
    switchOffCollapse('orders_list_collapse');
    $.post("{$app->urlManager->createUrl('customers/customeredit')}", { 'customers_id' : customers_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}
function loadCustomer(form){
    $.get("{$app->urlManager->createUrl('orders/createorder')}", $(form).serialize(), function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}

function addProduct(id){
    $("#order_management").hide();
    $(window).scrollTop(0);
    $.get("{$app->urlManager->createUrl('orders/addproduct')}", $('form[name=search]').serialize()+'&orders_id='+id, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('customers_list_collapse');
        } else {
            alert("Request error.");
            //$("#customer_management").hide();
        }
    },"html");
    return false;
}                                
                              
function addProductUpdate(id){
    $("#order_management").hide();
    $(window).scrollTop(0);
    $.post("{$app->urlManager->createUrl('orders/addproductprocess')}", $('form[name=add_product]').serialize()+'&orders_id='+id, function(data, status){
        if (status == "success") {
            $.post("orders/order-edit", {
                'orders_id': id,
            }, function (data, status) {
                if (status == "success") {        
                    $('#order_management_data .scroll_col').html(data);
                    $("#order_management").show();
                    switchOffCollapse('customers_list_collapse');
                }
            }, "html");
        } else {
            alert("Request error.");
            //$("#customer_management").hide();
        }
    },"html");
    return false;                              
}
$(document).ready(function() {

    $(window).resize(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800);        
    })
    $(window).resize();
    
    
    $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
    $('.w-tdc.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.w-tdc').removeClass('act_row');
            $(this).parents('.w-tdc').addClass('act_row');
            $('.w-tdc input[type="text"]').prop('disabled', true);
            $('.w-tdc select').prop('disabled', true);
            $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
            $('.w-tdc.act_row select').prop('disabled', false);
        }
    });    

    $('body').on('click', 'th.checkbox-column .uniform', function() { 
        if($(this).is(':checked')){
			$('tr.checkbox-column .uniform').prop('checked', true);
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
    
    $('select.select2-offscreen').change(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800); 
    });

    var $platforms = $('.js_platform_checkboxes');
    var check_platform_checkboxes = function(){
        var checked_all = true;
        $platforms.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $platforms.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_platform_checkboxes();
    $platforms.on('click',function(){
        var self = this;
        if (this.value=='') {
            $platforms.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $platforms.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $platforms.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
});

function resetFilter() {
    $('select[name="by"]').val('');
    $('input[name="search"]').val('');
    $("#presel").prop("checked", true);
    $("#exact").prop("checked", false);
    $('.js_platform_checkboxes').prop("checked", false);
    $('select[name="interval"]').val('');
    $('input[name="from"]').val('');
    $('input[name="to"]').val('');
    $('select[name="status"]').val('');
    $('input[name="delivery_country"]').val('');
    $('input[name="delivery_state"]').val('');
    $("#row_id").val(0);
    $('label.active_options, span.active_options').removeClass('active_options');
    resetStatement();
    return false;  
}
    
function applyFilter() {
    resetStatement();
    return false;    
}
    
function invoiceSelectedOrders() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.target = "_blank";    
        form.method = "POST";
        form.action = 'orders/ordersbatch?pdf=invoice&action=selected';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");              
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }
    
    return false;
}

function packingslipSelectedOrders() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.target = "_blank";    
        form.method = "POST";
        form.action = 'orders/ordersbatch?action=selected';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");              
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }
    
    return false;
}

function exportSelectedOrders() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.target = "_blank";
        form.method = "POST";
        form.action = 'orders/ordersexport';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }
    
    return false;
}

function deleteSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                message: "Restock product quantity?",
                title: "Delete selected Orders",
                buttons: {
                        success: {
                                label: "Yes",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("orders/ordersdelete", { 'selected_ids' : selected_ids, 'restock' : '1' }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        danger: {
                                label: "No",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("orders/ordersdelete", { 'selected_ids' : selected_ids, 'restock' : '0' }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "Cancel",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
    }
    return false;
}

$(document).ready(function(){
	//===== Date Pickers  =====//
	$( ".datepicker" ).datepicker({
		changeMonth: true,
                changeYear: true,
		showOtherMonths:true,
		autoSize: false,
		dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
                onSelect: function (e) { 
                    if ($(this).val().length > 0) { 
                      $(this).siblings('span').addClass('active_options');
                    }else{ 
                      $(this).siblings('span').removeClass('active_options');
                    }
                  }
        });
        /*$( "select[name='interval']" ).focus(function() {
            $("#presel").prop("checked", true);
            $("#exact").prop("checked", false);
        });
        $( "#from_date, #to_date" ).focus(function() {
            $("#presel").prop("checked", false);
            $("#exact").prop("checked", true);
        });*/
        
        $('#selectCountry').autocomplete({
            source: "orders/countries",
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.f_td_country',
            open: function (e, ui) {
              if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                  var me = $(this);
                  var keywords = acData.term.split(' ').join('|');
                  me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
                $('input[name="delivery_state"]').prop('disabled', true);
                if(ui.item.value != null){ 
                    $('input[name="delivery_state"]').prop('disabled', false);
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
        });
        
        $('#selectState').autocomplete({
            // source: "orders/state?country=" + $('#selectCountry').val(),
            source: function(request, response) {
                $.ajax({
                    url: "orders/state",
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
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                $(this).siblings('label').addClass('active_options');
            }else{ 
                $(this).siblings('label').removeClass('active_options');
            }
        });  
});

</script>
<!--===Actions ===-->
    <div class="row right_column" id="order_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="order_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
<!--===Actions ===-->

<!-- /Page Content -->