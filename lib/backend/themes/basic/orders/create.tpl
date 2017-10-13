<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<div id="message">
{$message}
</div>
<!--===Process Order ===-->
<div class="row">
    <div class="col-md-12" id="order_management_data">
        <div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header filter-title filter-title-search">
        <h4>Search</h4>
    </div>
    <div class="widget-content">
        <div class="filter-box-create">
            <span>{$smarty.const.TEXT_TYPE_CUSTOMER}:</span><span class="auto-wrapp"><input type="text" name="Customer" id="selectCustomer" class="form-control" /></span> <button type="button" class="btn btn-primary" onclick="customerChoose();">Choose</button><br><br><span>Or:</span><a href="{$app->urlManager->createUrl(['customers/insert', 'redirect'=>'neworder'])}" class="btn btn-primary btn-add-customer popup" data-class="pop-up-insert-cust">Add new customer</a>
            <input type="hidden" name="customer_id" value="0" id="topic_id">
        </div>
    </div>
</div>        
        <div id="contentBox" class="bc-line">
            {$content}
        </div>
    </div>
</div>
<!-- Process Order -->

<script type="text/javascript">
function resetStatement() {
    window.history.back();
    return false;
}
function createOrderProcess(){
    $.post("{$app->urlManager->createUrl(['orders/createorderprocess', 'back' => $app->controller->view->backOption, 'convert' => $app->controller->view->convert])}", $('form[name=create_order]').serialize(), function(data, status){
        if (status == "success") {
            $('#order_management_data #contentBox').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
var urlParams = location.search.substring(1).split('&'),
params = {};
urlParams.forEach(function(el){
    var tmpArr = el.split('=');
    params[tmpArr[0]] = tmpArr[1];
});
var customers_id_url = params['customers_id'];
if(customers_id_url > 0){
    customerChoose();
}

function customerChoose() {
    if(customers_id_url > 0){
        var customer_id = customers_id_url;
    }else{
         var customer_id = $("#topic_id").val();
    }
    
    if (customer_id > 0) {
        $.ajax({
            url: '{$app->urlManager->createUrl('orders/create')}',
            dataType: "html",
            data: {
                Customer : customer_id,
            },
            success: function(data) {
                $("#contentBox").html(data);
                //response(data);
                $('.bc-line').css('display', 'block');
            }
        });
    }
    return false;
}
$(document).ready(function(){
{if $app->controller->view->autoSubmit}
//$('.bc-line').css('display', 'none');
//createOrderProcess();
{/if}
$('#selectCustomer').autocomplete({
            source: "{$app->urlManager->createUrl('orders/customer')}",
            minLength: 2,
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
                 $("#topic_id").val(ui.item.id);
                /*$('.bc-line').css('display', 'none');
                if(ui.item.value != null){ 
                    $('.bc-line').css('display', 'block');
                }*/
            },
            //html: true,
        }).focus(function () {
          $(this).autocomplete("search");
        });
        
        $("a.popup").popUp({
             box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading pup-head'>Add new customer</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
         });   
});

</script>
<!-- /Page Content -->