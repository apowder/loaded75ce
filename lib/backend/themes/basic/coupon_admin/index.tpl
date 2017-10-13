<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->row_id}" />
    {if $app->controller->view->coupon_code neq ''}
    <input type="hidden" name="coupon_code" value="{$app->controller->view->coupon_code}" />
    {/if}
</form>
<!--===reviews list===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="reviews_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-coupon_admin"
                       checkable_list="0,1,2" data_ajax="coupon_admin/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->catalogTable as $tableItem}
                            <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--===/reviews list===-->

<script type="text/javascript">
    function applyFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;    
    }
    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }

    function preEditItem( item_id ) {
        $.post("coupon_admin/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();
               // switchOnCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("coupon_admin/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();
                //switchOnCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("coupon_admin/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#reviews_management_data .scroll_col').html("");
                //switchOffCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    /*function switchOffCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-down')) {
            $("#" + id).click();
        }
    }

    function switchOnCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-up')) {
            $("#" + id).click();
        }
    }*/

    function resetStatement() {
        $("#reviews_management").hide();

        //switchOnCollapse('reviews_list_box_collapse');
        //switchOffCollapse('reviews_management_collapse');

        $('reviews_management_data').html('');
        $('#reviews_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }

    function onClickEvent(obj, table) {

        var dtable = $(table).DataTable();
        var id = dtable.row('.selected').index();
        $("#row_id").val(id);
        setFilterState();
        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

</script>

<!--===  reviews management ===-->
<div class="row right_column" id="reviews_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="reviews_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
</div>
<!--=== reviews management ===-->