
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
  <input type="hidden" id="row_id" value="" />
</div>
<!-- /Page Header -->
  <!--=== Page Content ===-->
{if $isMultiPlatforms}
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
        <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
              <li class="platform-tab {if $platform['id']==$first_platform_id} active {/if}" data-platform_id="{$platform['id']}"><a onclick="loadModules('seo_redirects/list?platform_id={$platform['id']}')" data-toggle="tab"><span>{$platform['text']}</span></a></li>
            {/foreach}
        </ul>
    </div>
{/if}  
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>   	        
            <div class="widget-content" id="reviews_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable dataTable"
                       checkable_list="0,1" data_ajax="seo_redirects/list?platform_id={$first_platform_id}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->RedirectsTable as $tableItem}
                            <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<script type="text/javascript">
    function resetStatement() {
        var table = $('.table').DataTable();
        table.draw(false);
        return false;
    }
    
    function loadModules(url){
        var table = $('.table').DataTable();
         
        table.ajax.url( url ).load();
    }
    
    function redirectSave(){
        $.post("seo_redirects/submit", 
            $('form[name=redirect]').serialize(),
        function (data, status) {
            if (status == "success") {
                $('.alert #message_plce').html(data.message);
                $('.alert').addClass(data.messageType).show();
                resetStatement();
            } else {
                alert("Request error.");
            }
        }, "json");
        return false;
    }
    
    function preEditItem( item_id ) {
        $.post("seo_redirects/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#_management_data .scroll_col').html(data);
                $("#_management").show();
               // switchOnCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }
    
    function onClickEvent(obj, table) {

        var dtable = $(table).DataTable();
        var id = dtable.row('.selected').index();
        $("#row_id").val(id);
        
        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }
    
    function edit(id){
       $.get("seo_redirects/edit", {
            'item_id': id,
            {if $isMultiPlatforms}
            'platform_id': $('.platform-tab.active').attr('data-platform_id'),
            {else}
            'platform_id': {$default_platform_id},
            {/if}
        }, function (data, status) {
            if (status == "success") {
                $('#_management_data .scroll_col').html(data);
                $("#_management").show();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

function deleteRedirect(id){

            bootbox.dialog({
                message: "{$smarty.const.TEXT_REDIRECT_REMOVE_CONFIRM}",
                title: "{$smarty.const.TEXT_REDIRECT_DLEETE}",
                buttons: {
                    success: {
                        label: "{$smarty.const.TEXT_BTN_YES}",
                        className: "btn-delete",
                        callback: function(){
                            $.post("seo_redirects/delete",
                                {
                                'item_id' : id , 
                                },
                                function(data, status){
                                    if (status == "success"){                
                                        resetStatement();
                                    }
                                },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.TEXT_BTN_NO}",
                        className: "btn-cancel",
                        callback: function () {
                            //console.log("Primary button");
                        }
                    }
                }
            });

    return false;
}    
    
</script>
<div class="row right_column" id="_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
</div>
<!--=== reviews management ===-->