<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
</form>
<!--===giveaway list===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="giveaway_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable"
                       checkable_list="0,1" data_ajax="giveaway/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->giveawayTable as $tableItem}
                            <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--===/giveaway list===-->

<script type="text/javascript">
    function resetFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;  
    }

    function applyFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;    
    }

    function preEditItem( item_id ) {
        $.post("giveaway/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#giveaway_management_data').html(data);
                $("#giveaway_management").show();
                switchOnCollapse('giveaway_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        //$("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function editItem(item_id) {

        $.post("giveaway/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#giveaway_management_data').html(data);
                $("#giveaway_management").show();
                switchOnCollapse('giveaway_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("giveaway/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#giveaway_management_data').html(data);
                $("#giveaway_management").show();

                $('.table').DataTable().search('').draw(false);

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("giveaway/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#giveaway_management_data').html(data);
                $("#giveaway_management").show();
                switchOnCollapse('giveaway_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("giveaway/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#giveaway_management_data').html("");
                switchOffCollapse('giveaway_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    
    function switchOffCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-down')) {
            $("#" + id).click();
        }
    }

    function switchOnCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-up')) {
            $("#" + id).click();
        }
    }

    function resetStatement() {
        $("#giveaway_management").hide();

        switchOnCollapse('giveaway_list_box_collapse');
        switchOffCollapse('giveaway_management_collapse');

        $('giveaway_management_data').html('');
        $('#giveaway_management').hide();

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
<!--===  giveaway management ===-->
<div class="row right_column" id="giveaway_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="giveaway_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== giveaway management ===-->
</div>