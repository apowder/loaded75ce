<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
  <div class="popup-box-wrap pop-mess alert" style="display:none;">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-{$messageType}">
                        <span id="message_plce"></span>
                    </div>  
                </div>   
                 <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
                </div>
            </div>  
            <script>
            $('body').scrollTop(0);
            $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                $(this).parents('.pop-mess').remove();
            });
        </script>
        </div>
<!-- /Page Header -->
<div class="order-wrap">
  <!--=== Page Content ===-->
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content">     
        {if {$messages|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in {$message['messageType']}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>               
          {/foreach}
        {/if}
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-suppliers" checkable_list="0,1,2" data_ajax="{$app->urlManager->createUrl('suppliers/list')}">
          <thead>
            <tr>
              {foreach $app->controller->view->SupplierTable as $tableItem}
                <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                {/foreach}
            </tr>
          </thead>
        </table>            

      </div>

    </div>
  </div>

<script type="text/javascript">
  var global = '{$sID}';

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

  function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#suppliers_management").hide();
    switchOnCollapse('suppliers_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
  }

  var first = true;
  function onClickEvent(obj, table) {
    $("#suppliers_management").hide();
    $('#suppliers_management_data .scroll_col').html('');
    var suppliers_id = $(obj).find('input.cell_identify').val();
    if (global > 0) suppliers_id = global;

    $.post("{$app->urlManager->createUrl('suppliers/statusactions')}", { 'suppliers_id' : suppliers_id }, function(data, status) {
            if (status == "success") {
                $('#suppliers_management_data .scroll_col').html(data);
                $("#suppliers_management").show();
            } else {
                alert("Request error.");
            }
        },"html");

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + suppliers_id + ']').parents('tr').addClass('selected');
    global = '';
    url = window.location.href;
    if (url.indexOf('sID=') > 0) {
      url = url.replace(/sID=\d+/g, 'sID=' + suppliers_id);
    } else {
      url += '?sID=' + suppliers_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
  }

  function onUnclickEvent(obj, table) {
    $("#suppliers_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
  }

  function supplierEdit(id) {
    deleteScroll();
    $("#suppliers_management").hide();
    $.get("{$app->urlManager->createUrl('suppliers/edit')}", { 'suppliers_id' : id }, function (data, status) {
      if (status == "success") {
        $('#suppliers_management_data .scroll_col').html(data);
        $("#suppliers_management").show();
        switchOffCollapse('suppliers_list_collapse');
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function supplierSave(id) {
    $.post("{$app->urlManager->createUrl('suppliers/save')}?suppliers_id=" + id, $('form[name=supplier]').serialize(), function (data, status) {
      if (status == "success") {
        //$('#suppliers_management_data').html(data);
        //$("#suppliers_management").show();
        $('.alert #message_plce').html('');
        $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
        resetStatement(id);
        switchOffCollapse('suppliers_list_collapse');
      } else {
        alert("Request error.");
      }
    }, "json");
    return false;
  }

  function supplierDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('suppliers/confirmdelete')}", { 'suppliers_id': id }, function (data, status) {
      if (status == "success") {
        $('#suppliers_management_data .scroll_col').html(data);
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function supplierDelete() {
      $.post("{$app->urlManager->createUrl('suppliers/delete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
          //$('.alert #message_plce').html('');
          //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
          if (data == 'reset') {
            resetStatement();
          } else {
            $('#suppliers_management_data .scroll_col').html(data);
            $("#suppliers_management").show();
          }
          switchOnCollapse('suppliers_list_collapse');
        } else {
          alert("Request error.");
        }
      }, "html");
    return false;
  }
</script>
<!--===Actions ===-->
<div class="row right_column" id="suppliers_management">
  <div class="widget box">
    <div class="widget-content fields_style" id="suppliers_management_data">
      <div class="scroll_col"></div>
    </div>
  </div>
</div>
<!--===Actions ===-->
<!-- /Page Content -->
</div>
