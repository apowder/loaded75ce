<div class="gridBg">
    <div class="btn-bar btn-bar-top after">
        <div class="btn-left"><form action="{$app->urlManager->createUrl('orders/process-order')}" method="get" class="go-to-order" style="margin-left: 20px">{$smarty.const.TEXT_GO_TO_ORDER} <input type="text" class="form-control" name="orders_id"/> <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_GO}</button></form>
        {if $ref_id}
           {$smarty.const.TEXT_REORDER_FROM}<a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $ref_id])}">{$ref_id}</a>
        {/if}
        </div>
        <div class="btn-right"><a href="{$app->urlManager->createUrl(['orders/order-history', 'orders_id' => $orders_id, 'cid' => $customer_id])}" class="btn-link-create popup">{$smarty.const.TEXT_ORDER_LEGEND}</a><span class="print_but" onclick="printDiv()">Print</span>
        <a href="{$app->urlManager->createUrl(['orders/order-edit', 'orders_id' => $orders_id])}" class="btn btn-delete btn-edit">{$smarty.const.IMAGE_EDIT}</a>
        <a href="javascript:void(0)" onclick="return deleteOrder({$orders_id});" class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</a></div>
    </div>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<link href="{{$smarty.const.DIR_WS_ADMIN}}/css/fancybox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$smarty.const.DIR_WS_ADMIN}/js/jquery.fancybox.pack.js"></script>

<!--===Process Order ===-->
<div class="row w-or-prev-next">
    {if $app->controller->view->order_prev > 0}
    <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $app->controller->view->order_prev])}" class="btn-next-prev-or btn-prev-or" title="{$smarty.const.TEXT_GO_PREV_ORDER} (#{$app->controller->view->order_prev})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_PREV_ORDER}"></a>
    {/if}
    {if $app->controller->view->order_next > 0}
    <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $app->controller->view->order_next])}" class="btn-next-prev-or btn-next-or" title="{$smarty.const.TEXT_GO_NEXT_ORDER} (#{$app->controller->view->order_next})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_NEXT_ORDER}"></a>
    {/if}
    <div class="col-md-12" id="order_management_data">
        {$content}
    </div>
</div>
<!-- Process Order -->
<script type="text/javascript">
function addProduct(id){
    //$("#order_management").hide();
    $.get("{$app->urlManager->createUrl('orders/addproduct')}", $('form[name=search]').serialize()+'&orders_id='+id, function(data, status){
        if (status == "success") {
            $("#order_management_data").html(data);
            //$('#order_management_data .scroll_col').html(data);
            //$("#order_management").show();
            //switchOffCollapse('customers_list_collapse');
        } else {
            alert("Request error.");
            //$("#customer_management").hide();
        }
    },"html");
    return false;
}                                
                              
function addProductUpdate(id){
    //$("#order_management").hide();
    $.post("{$app->urlManager->createUrl('orders/addproductprocess')}", $('form[name=add_product]').serialize()+'&orders_id='+id, function(data, status){
        if (status == "success") {
            $.post("{$app->urlManager->createUrl('orders/order-edit')}", {
                'orders_id': id,
            }, function (data, status) {
                if (status == "success") {  
                    $("#order_management_data").html(data);
                    //$('#order_management_data .scroll_col').html(data);
                    //$("#order_management").show();
                    //switchOffCollapse('customers_list_collapse');
                }
            }, "html");
        } else {
            alert("Request error.");
            //$("#customer_management").hide();
        }
    },"html");
    return false;                              
}
function check_form() {
    //return false;
//ajax save
    //$("#order_management").hide();
    //var orders_id = $( "input[name='orders_id']" ).val();
    $.post("{$app->urlManager->createUrl('orders/ordersubmit')}", $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            //$('#order_management_data .scroll_col').html(data);
            $("#order_management_data").html(data);
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
function resetStatement() {
     window.history.back();
    return false;
}
function closePopup() {
    $('.popup-box').trigger('popup.close');
    $('.popup-box-wrap').remove();
    return false;
}
function saveTracking(){
    //$("#order_management").hide();
    $(window).scrollTop(0);
    var orders_id = $( "#trackingNumber input[name='orders_id']" ).val();
    $.post("{$app->urlManager->createUrl('orders/savetracking')}", $('form[name=savetrack]').serialize(), function(data, status){
        if (status == "success") {
          $('#trackingNumber').prepend(data);  

          $('.barcode').html('<a href="http://www.17track.net/en/track?nums='+$('input[name="tracking_number"]').val()+'" target="_blank"><img alt="'+$('input[name="tracking_number"]').val()+'" src="{$qr_img_url}&rand=' + Math.random() + '"></a>');

          $('.tracknum').html('<a href="http://www.17track.net/en/track?nums='+$('input[name="tracking_number"]').val()+'" target="_blank">'+$('input[name="tracking_number"]').val()+'</a>');
          setTimeout(function(){
            closePopup();
          },500)
        } else {
            alert("Request error.");
        }
    },"html");
    return false;                              
}
$(document).ready(function() { 
    $('a.btn-link-create.popup').popUp({
        box_class:'legend-info'
    });
    $('a.edit-tracking').popUp({
      box: "<div class='popup-box-wrap trackWrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_EDIT_TRACKING_NUMBER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });


    $('.fancybox').fancybox({
      nextEffect: 'fade',
      prevEffect: 'fade',
      padding: 10
    });

	$('body').on('click', '.fancybox-wrap', function(){
		$.fancybox.close();
	})

});
function printDiv() { 
 window.print();
 window.close();
}
</script>
<style>
@media print {
a[href]:after {
   content:"" !important;
}
#content, #container, #container > #content > .container{
	margin:0 !important;
}
#sidebar, header, .btn-bar, .top_header, .pra-sub-box .pra-sub-box-map:nth-child(2), .btn-next-prev-or, .btn-next-prev-or.btn-next-or, .footer{
	display:none !important;
}
.pr-add-det-box.pr-add-det-box02.pr-add-det-box03 .pra-sub-box-map{
	width:100%;
}
.pr-add-det-box.pr-add-det-box03 .pra-sub-box-map .barcode{
margin-top:-132px !important;
}
.box-or-prod-wrap{
padding:0 !important;
}
.filter-wrapp{
display:none;
}
}
</style>
        <!-- /Page Content -->
</div>