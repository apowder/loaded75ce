<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<div class="order-wrap">
<!--===specials list===-->
<div class="row order-box-list">
<input type="hidden" id="row_id">
    <div class="col-md-12">
            <div class="widget-content" id="specials_list_data">
						<div class="top-wr" style="display: none;"><input type="text" class="form-control datepicker" name="asd" id=""></div>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid table-specials" checkable_list="" data_ajax="specials/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->specialsTable as $tableItem}
                            <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>

    </div>
</div>
<!--===/specials list===-->

<script type="text/javascript">

    function preEditItem( item_id ) {
        $.post("specials/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        //$("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function editItem(item_id) {

        $.post("specials/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("specials/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();

                //$('.table').DataTable().search('').draw(false);

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("specials/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("specials/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#specials_management_data .scroll_col').html("");
                switchOffCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
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
        $("#specials_management").hide();

        switchOnCollapse('specials_list_box_collapse');
        switchOffCollapse('specials_management_collapse');

        $('specials_management_data').html('');
        $('#specials_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        //$(window).scrollTop(0);

        return false;
    }

    function switchStatement(id, status) {
      $.post("specials/switch-status", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      },"html");
    }    
    
    function onClickEvent(obj, table) {
        $('#row_id').val(table.find(obj).index());
        var event_id = $(obj).find('input.cell_identify').val();

      $(".check_on_off").bootstrapSwitch(
                  {
          onSwitchChange: function (element, arguments) {
            switchStatement(element.target.value, arguments);
            return true;
          },                   
					onText: "{$smarty.const.SW_ON}",
					offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                  }
        );        
        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

</script>

<!--===  specials management ===-->
<div class="row right_column" id="specials_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="specials_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
</div>
<!--=== specials management ===-->