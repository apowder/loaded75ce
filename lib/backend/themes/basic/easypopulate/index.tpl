{use class="yii\helpers\Html"}
<div id="command_target_holder" style="display: none;"></div>

{if $message_stack_output}
    {$message_stack_output}
{/if}

<div class="tabbable tabbable-custom tabbable-ep">
    <ul class="nav nav-tabs">
        {foreach $directories as $directory}
            <li class="{if !$dataSourcesActive && $directory['id']==$selectedRootDirectoryId} active {/if}"><a class="js_link_folder_select" href="{$directory['link']}" data-directory_id="{$directory['id']}"{if !$dataSourcesActive && $directory['id']==$selectedRootDirectoryId} onclick="return false;"{/if}><span>{$directory['text']}</span></a></li>
        {/foreach}
        {if $datasourceSettings}
            <li class="{if $dataSourcesActive} active {/if}"><a href="{$dataSourcesHref}"><span>{$smarty.const.TEXT_DATA_SOURCES}</span></a></li>
        {/if}
    </ul>
    <div class="tab-content tab-content1">
        
        <div class="tab-pane topTabPane tabbable-custom {if !$dataSourcesActive}active{/if}">
            {if $show_export_page}
                {include "tab_export.tpl"}
            {/if}
            {if $show_import_page}
                {include "tab_import.tpl"}
            {/if}

                    
            {if $currentDirectory->cron_enabled || $currentDirectory->directory_type=='import' }
                {include "directory_listing.tpl"}
            {/if}
        </div>
        {if $datasourceSettings}
        <div id="#datasource" class="tab-pane topTabPane tabbable-custom {if $dataSourcesActive}active{/if}">
            <form action="" method="post">

            </form>
        </div>
        {/if}
    </div>
</div>

        
<br style="clear:both">
{if $show_data_management}
<div class="row">
    <div class="col-md-12">
    {$smarty.const.TEXT_DIRECTORY_IMPORT} {$app->controller->view->importFolder}<br/><br/>
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="easypopulate_management_title">{$smarty.const.TEXT_DATA_MANAGEMENT}</span>
                </h4>
            </div>
            <div class="widget-content fields_style">
                <div class="row">
                    <div class="col-md-6 text-right">
                        <label>{$smarty.const.TEXT_DELETE_TEST_DATA}</label>
                    </div>
                    <div class="col-md-6">
                        <form name="form_control" ENCTYPE="multipart/form-data" ACTION="{Yii::$app->urlManager->createUrl(['easypopulate/empty'])}" method="POST">
                            {Html::checkbox('products',false,['value'=>1,'class' => 'uniform'])}{$smarty.const.TEXT_PRODUCTS_AND_CATEGORIES_ONLY}<br>
                            {Html::checkbox('customers',false,['value'=>1,'class' => 'uniform'])}{$smarty.const.TEXT_CUSTOMERS_ONLY}<br>
                            {Html::checkbox('orders',false,['value'=>1,'class' => 'uniform'])}{$smarty.const.TEXT_ORDERS_ONLY}<br><br>
                            <button type="submit" class="btn btn-primary" >{$smarty.const.IMAGE_DELETE}</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}


<!-- cut content -->
<div id="popupUnknownFileUploaded" class="js-ep_popup" style="display: none;">
    <div class='popup-box-wrap'>
        <div class='around-pop-up'></div>
        <div class="popup-box popup-box-ep">
            <div class="widget box widget-box-ep">
            <div class="widget-header">
                <h4><i class="icon-upload"></i><span id="easypopulate_download_files_title">{$smarty.const.TITLE_SELECT_IMPORT_FILE_TYPE}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-close"><i class="icon-close"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style">
                {$smarty.const.NOTICE_UNKNOWN_IMPORT_FILE_TYPE}
                <p>
                  {foreach from=$upload_options key=_value item=_label}
                    {if is_array($_label)}
                      {foreach from=$_label key=__value item=__label}
                        {if !empty($__value)}
                           <div><label><input type="radio" name="update_type" value="{$__value}"> {$__label}</label></div>
                        {/if}
                      {/foreach}
                    {else}
                        {if !empty($_value)}
                          <div><label><input type="radio" name="update_type" value="{$_value}"> {$_label}</label></div>
                        {/if}
                    {/if}
                  {/foreach}
                </p>
                <input type="hidden" name="id" value="">
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
                    <div class="btn-right"><button class="btn btn-primary js-confirm-file-type">{$smarty.const.IMAGE_CONFIRM}</button></div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<div id="popupSelectExportFields" class="js-ep_popup" style="display: none;">
    <div class='popup-box-wrap'>
        <div class='around-pop-up'></div>
        <div class="popup-box popup-box-ep">
            <div class="widget box widget-box-ep">
            <div class="widget-header">
                <h4><i class="icon-download"></i><span id="easypopulate_download_files_title">{$smarty.const.TITLE_SELECT_EXPORT_FIELDS}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-close"><i class="icon-close"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style">
                <div class="scroll-table-workaround">
                <table class="js-export_columns table -table-striped -table-hover -table-responsive -table-ordering -no-footer">
                    <thead>
                    <tr>
                        <th width="30">{Html::checkbox('select_all')}</th>
                        <th>{$smarty.const.TEXT_FILE_FIELD_TITLE}</th>
                    </tr>
                    </thead>
                </table>
                </div>
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
                    <div class="btn-right">
                        <button class="btn btn-primary js-confirm-fields">{$smarty.const.IMAGE_CONFIRM}</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<div id="popupSelectImportFields" class="js-ep_popup" style="display: none;">
    <div class='popup-box-wrap'>
        <div class='around-pop-up'></div>
        <div class="popup-box popup-box-ep">
            <div class="widget box widget-box-ep">

            <div class="widget-header">
                <h4><i class="icon-upload"></i><span id="easypopulate_download_files_title">{$smarty.const.TITLE_SELECT_IMPORT_FIELDS}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-close"><i class="icon-close"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style">
                <div class="scroll-table-workaround">
                <table class="js-import_columns table -table-striped -table-hover -table-responsive -table-ordering no-footer">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TEXT_NAME_IMPORT_FILE_FIELD}</th>
                        <th>{$smarty.const.TEXT_NAME_IMPORT_DB_FIELD}</th>
                    </tr>
                    </thead>
                </table>
                </div>
                <input type="hidden" name="id" value="">
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
                    <div class="btn-right"><button class="js-mapping-confirmed btn btn-primary">{$smarty.const.IMAGE_CONFIRM}</button></div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<div id="popupImportStatus" class="js-ep_popup" style="display: none;">
    <div class='popup-box-wrap'>
    <div class='around-pop-up'></div>
    <div class="popup-box popup-box-ep">
        <div class="widget box widget-box-ep">
            <div class="widget-header">
                <h4><i class="icon-upload"></i><span id="easypopulate_download_files_title">{$smarty.const.TITLE_IMPORT_PROCESS}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-close"><i class="icon-close"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style import_result_popup">
                <div>
                    <div class="js-import-progress progress_bar"></div>
                    <div style="height:1.5em" class="js-import-time">

                    </div>
                </div>
                <div class="js-import-messages import_log">

                </div>
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.TEXT_OK}</a></div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<style>
#easypopulate_download_files_data select{
    margin: 10px 0;
}
#ptTimeSelectCntr{ z-index: 2001; background-color: #fff; }
.ep-file-list.tab-cust th:nth-child(5), .ep-file-list.tab-cust td:nth-child(5) { text-align: left !important }
.ep-file-list.tab-cust th:nth-child(6), .ep-file-list.tab-cust td:nth-child(6) { text-align: left !important }
.ep-file-list.tab-cust th:nth-child(3), .ep-file-list.tab-cust td:nth-child(3) { text-align: right !important }
  .export_filter_row{ display: none;}
  .form-control.form-control-small{ width: auto !important; min-width: 120px !important }
  .form-control.form-control-small.on-time{ width: 90px !important }
  .import_log { overflow-y: auto; height: 400px }
    .popup-box-ep { width:650px };
  .widget-box-ep { text-align: left;border: none;margin: 0; }
.popup-box-wrap .widget-box-ep { text-align: left;border: none;margin: 0; }
.popup-box-wrap .table { width:100% }
.scroll-table-workaround{ height: 400px; overflow-y: scroll }


.progress_bar{ height:26px; border: 1px solid #305c88; display: none; }
.ui-progressbar-value { background-color: #305c88; height: 24px; }
.in_progress .progress_bar, .import_result_popup .progress_bar{ display: block; }
</style>
<script type="text/javascript">
    var bootboxDefaults = {
        onEscape: true,
        animate: false
    };

    function ep_command(command, params){
        if ( $('#frmCommand').length==0 ) {
            $('#command_target_holder').html('<iframe id="command_target" name="command_target" style="width:0;height:0;border:0px solid #fff;"></iframe><form name="frmCommand" id="frmCommand" enctype="multipart/form-data" action="javascript:void(0);" method="POST" target="command_target" style="display: none"></form>');
        }

        if ( $.isNumeric( params )){
            params = { by_id:  params };
        }
        var str_params = '<input type="hidden" name="directory_id" value="{$current_directory_id}">';
        for( var key in params ) {
            if ( !params.hasOwnProperty(key) ) continue;
            str_params += '<input type="hidden" name="'+key+'" value="'+params[key]+'">';
        }
        console.log(command);
        $('#frmCommand').html(str_params);
        if ( command=='configure' ) {
            $('#frmCommand').attr('action', 'easypopulate/import-configure');
            $('#frmCommand').trigger('submit');
        }else if ( command=='configure_datasource_settings' ){
            var datasource_setting_endpoint = 'easypopulate/configure-datasource-settings';
            $.ajax({
                url: datasource_setting_endpoint,
                cache:false,
                type:'GET',
                data:params,
                success:function(data){
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }, {
                        buttons: {
                            confirm_and_save: {
                                label: 'Ok',
                                className: 'btn btn-primary',
                                callback: function(){
                                    var formParams = $('#frmDatasourceConfig').serializeArray();
                                    console.log(formParams);
                                    $.ajax({
                                        url: datasource_setting_endpoint,
                                        cache: false,
                                        type: 'POST',
                                        data: formParams,
                                        success: function () {
                                            bootbox.hideAll();
                                            uploader('reload_file_list');
                                        }
                                    });

                                    return false;
                                }
                            }
                        }
                    }));
                    /*$('.js-frequency-select').on('change',function(){
                        var $tr = $(this).parents('.js_row');
                        if ($(this).val()=='0'){
                            $('.js-defined-time',$tr).removeAttr('disabled');
                        }else{
                            $('.js-defined-time',$tr).attr('disabled','disabled');
                        }
                    });
                    $('.js-frequency-select').trigger('change');*/
                }
            });
        }else if ( command=='configure_dir' ) {
            var type = arguments[2] || 'import';
            var endpoint = 'configure-auto-'+type+'-directory';
            $.ajax({
                url:'easypopulate/'+endpoint,
                cache:false,
                type:'GET',
                data:params,
                success:function(data){
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }, {
                        buttons: {
                            confirm_and_save: {
                                label: 'Ok',
                                className: 'btn btn-primary',
                                callback: function(){
                                    var formParams = $('#frmDirectoryConfig').serializeArray();
                                    var maskInputs = formParams.filter(function(elem){ return elem.name.indexOf('directory_config[')===0 && elem.name.indexOf('[filename_pattern]')!==-1; });
                                    var hasErrors = false;
                                    for(var i=0; i<maskInputs.length;i++) {
                                        if (!maskInputs[i].value) {
                                            var $input = $('#frmDirectoryConfig').find('[name="'+maskInputs[i].name+'"]')
                                            if ( $input.get(0).type.toLowerCase()=='hidden' ) continue;
                                            $input.parent().addClass('has-error');
                                            hasErrors = true;
                                        }
                                    }
                                    if ( !hasErrors ) {
                                        formParams.push({ name:'by_id', value:params.by_id });
                                        $.ajax({
                                            url: 'easypopulate/'+endpoint,
                                            cache: false,
                                            type: 'POST',
                                            data: formParams,
                                            success: function () {
                                                bootbox.hideAll();
                                                uploader('reload_file_list');
                                            }
                                        });
                                    }
                                    return false;
                                }
                            }
                        }
                    }));
                    $('.js-frequency-select').on('change',function(){
                        var $tr = $(this).parents('.js_row');
                        if ($(this).val()=='0'){
                            $('.js-defined-time',$tr).removeAttr('disabled');
                        }else{
                            $('.js-defined-time',$tr).attr('disabled','disabled');
                        }
                    });
                    $('.js-frequency-select').trigger('change');
                }
            });
        }else if( command=='configure_export_columns' ) {
            $('#popupSelectExportFields').show();
            $('#popupSelectExportFields').attr('data-job_id',params['by_id']);

            $.ajax({
                url:'{$get_fields_action}',
                type:'POST',
                data: params,
                success:function(data){
                    var $table_target = $('#popupSelectExportFields .js-export_columns');
                    
                    var $table = $table_target.DataTable();
                    $table.clear();
                    var row_data = [];
                    var all_checked = true;
                    for( var i=0; i<data.length;i++ ) {
                        row_data.push([
                            '<input type="checkbox" class="uniform" name="field" value="'+data[i].db_key+'" id="chkExp'+data[i].db_key+'" '+(data[i].selected?' checked="checked" ':'')+'>',
                            '<label for="chkExp'+data[i].db_key+'">'+data[i].title+'</label>'
                        ]);
                        if ( !data[i].selected ) {
                            all_checked = false;
                        }
                    }
                    $table_target.css('width','100%');
                    $table.rows.add(row_data).draw();
                    $table_target.trigger('checkboxes:init');
                    /*$table.rows.add($.map(data,function(row){
                        return [[
                            row.db_key,
                            row.title,
                        ]];
                    })).draw();*/
                }
            });
        }else if( command=='import_zip' ) {
            $('#frmCommand').attr('action','easypopulate/import');
            $('#frmCommand').trigger('submit');
        }else if( command=='import' ) {
            $('#frmCommand').attr('action','easypopulate/import');
            $('#frmCommand').trigger('submit');
        }else if( command=='run_frequency' ) {
            $.ajax({
                url:'easypopulate/job-frequency',
                type: 'GET',
                cache: false,
                data: params,
                success:function(data) {
                    if ( !data ) return;
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }, {
                        buttons: {
                            confirm:{
                                callback: function (result) {
                                    params['run_frequency'] = $('#txtRunFrequency').val();
                                    params['run_time'] = $('#txtRunTime').val();
                                    $.ajax({
                                        url:'easypopulate/job-frequency',
                                        type: 'POST',
                                        cache: false,
                                        data: params,
                                        success:function(data) {
                                            bootbox.hideAll();
                                            uploader('reload_file_list');
                                        }
                                    });
                                }
                            }
                        }
                    }));
                    $('#txtRunFrequency').on('change',function(){
                        if ($(this).val()=='0'){
                            $('#txtRunTime').removeAttr('disabled');
                        }else{
                            $('#txtRunTime').attr('disabled','disabled');
                        }
                    });
                    $('#txtRunFrequency').trigger('change');
                }
            });
        }else if( command=='export' ) {
            $('#frmCommand').attr('action','{$download_form_action}');
            $('#frmCommand').trigger('submit');
        }

        return false;
    }
    function ep_file_configure(params) {
        if ( $.isNumeric( params )){
            params = { by_id:  params };
        }
        var str_params = '';
        for( var key in params ) {
            if ( !params.hasOwnProperty(key) ) continue;
            str_params += '<input type="hidden" name="'+key+'" value="'+params[key]+'">';
        }
        $('#frmCommand').html(str_params);
        $('#frmCommand').attr('action','easypopulate/import-configure');
        $('#frmCommand').trigger('submit');
        return false;
    }

    function uploader(cmd){
      if( cmd=='start_import' ) {
        $('#popupImportStatus').show();
        $('.js-import-progress').progressbar('value',0);
        $('.js-import-messages').html('');
      }else
      if (cmd=='message') {
         $('.js-import-messages').append('<div>'+(arguments[1]||'')+'</div>');
      }else
      if (cmd=='progress') {
          $('.js-import-progress').progressbar('value',parseInt(arguments[1],10));
          if ( arguments.length>2 && arguments[2] ) {
              $('.js-import-time').html(arguments[2]);
          }else{
              $('.js-import-time').html('');
          }
      }else
      if ( cmd=='reload_file_list' ) {
          $('#tblFiles').trigger('reload',[true]);
      }else
      if ( cmd=='need_choose_file_type' ){
        //alert('Select file type and upload file again');
          $('#popupUnknownFileUploaded input[name="id"]').val(arguments[1].id);
          $('#popupUnknownFileUploaded').show();
      }else if( cmd=='wrong_file_type' ){
          $('#popupImportStatus').show();
          $('.js-import-messages').html('{$smarty.const.WRONG_FILE_TYPE}');
      }else if( cmd=='need_choose_import_map' ){
          $('#popupSelectImportFields input[name="id"]').val(arguments[1].id);
          $('#popupSelectImportFields').show();
          $('#easypopulate_upload_files_data .js-mappings').html('');

          var fileFields = arguments[1].file_columns;
          var providerColumns = arguments[1].provider_columns;
          var preSelected = arguments[1].remap_columns || { };

          var dbOptions = '';
          for( var dbCol in providerColumns ) {
              if ( !providerColumns.hasOwnProperty(dbCol) ) continue;
              var providerColumn = providerColumns[dbCol];
              dbOptions += '<option value="'+dbCol+'" data-fileColumn="'+providerColumn.replace(/"/g,'&quot;')+'">'+providerColumn+'</option>';
          }

          var $table_target = $('#popupSelectImportFields .js-import_columns');
          var $table = $table_target.DataTable();
          $table.clear();
          var row_data = [];
          var all_checked = true;
          for( var i=0; i<fileFields.length;i++ ) {
              row_data.push([
                 fileFields[i],
                 '<select name="map['+fileFields[i].replace(/"/g,'&quot;').replace(/\[/g,'%5B').replace(/\]/g,'%5D')+']">'+dbOptions+'</select>'
              ]);
          }
          $table.rows.add(row_data).draw();

          $table_target.find('[name^="map\["]').each(function() {
              var getFname = this.name.match(/^map\[(.*)\]$/);
              if ( !getFname || !preSelected[getFname[1]] ) return;
              $(this).val(preSelected[getFname[1]]);
          });
      }
    }
    function afterSend(html){
        $('#easypopulate_management').show()
        switchOnCollapse("easypopulate_management_collapse")
        document.getElementById("easypopulate_management_data").innerHTML = html
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

    $(document).on('click','.js-action-link',function(event){
        var $_target = $(event.currentTarget);
        var action = $_target.attr('data-action') || '';
        if ( action && (action==='configure_dir' || action==='configure_datasource_settings') ) {
            ep_command(action, $_target.attr('data-directory_id'), $_target.attr('data-type') );
        }
        return event.stopPropagation();
    });

    function onClickEvent(obj, table) {
        return;
    }

    function onUnclickEvent(obj, table) {
        var $dir_source = $(obj).find('[data-directory_id]');
        if ( $dir_source.length==0 ) return;
        var directory_id = $dir_source.attr('data-directory_id');
        $('#tblFiles').attr('data-directory_id', directory_id);
        $('#tblFiles').trigger('reload');
        $('.js-currentDirectoryId').val(directory_id);
        return;
    }

    function resetStatement() {
        $("#easypopulate_management").hide();

        switchOnCollapse('easypopulate_list_box_collapse');
        switchOffCollapse('easypopulate_management_collapse');

        $('easypopulate_management_data').html('');
        $('#easypopulate_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }

    function selectCategory(theSelect) {
        $('a[href*="category_id"]').each(function() {
            url = $(this).attr('href');
            url = url.replace(/category_id=\d+/g, 'category_id=' + theSelect.value);
            $(this).attr('href', url);
        });
    }
    function refreshFilterContent(){
        $.ajax({
            url:'{$refresh_filter_action}',
            type: 'GET',
            cache: false,
            //data:$form.serializeArray(),
            success:function(data) {
                if ( !data ) return;
                for( var key in data ) {
                    if ( !data.hasOwnProperty(key) ) continue;
                    $('.'+key).html(data[key]);
                }
            }
        });
    }
    
    function showJobMessages(job_id){
        $.ajax({
            url:'{$get_job_messages_popup_action}',
            type: 'GET',
            cache: false,
            data:[ { name:'id', value: job_id }  ],
            success:function(data) {
                if ( !data ) return;
                if ( data.dialog ) {
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }));
                }else{
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, {
                        title : 'Job messages',
                        message : '<div id="blkLogMessages"></div>',
                        buttons : {
                            cancel : {
                                label : '{$smarty.const.TEXT_OK|escape:"javascript"}',
                                className: 'btn-primary'
                            }
                        }
                    }));
                    $('#blkLogMessages').html(data);
                }
            }
        });
        return false;
    }

    $(document).ready(function(){
        var table = $('#tblFiles').DataTable( {
            //"serverSide": true,
            "ajax": {
                "url": '{$job_list_url}',
                "data":function(data, settings){
                    data.directory_id = $('#tblFiles').attr('data-directory_id');
                }
            },
            "ordering": false
        } );

        //var table = $(this).dataTable(options);
        $('#tblFiles').find('tbody').on( 'click', 'td', function () {
            if ($(this).find('.job-actions').length>0) return;
            var $tr = $(this).parent();
            if ( $tr.hasClass('selected') ) {
                $tr.removeClass('selected');
                onUnclickEvent($tr, table);
            } else {
                table.$('tr.selected').removeClass('selected');
                $tr.addClass('selected');
                onClickEvent($tr, table);
            }
        } );

        $('.js-ep_popup').each(function(){
            var $_self = $(this);
            $_self.find('.widget-close, .btn-close, .btn-cancel').on('click',function(){
                $_self.hide();
            });
        });
        
        $('#popupUnknownFileUploaded .js-confirm-file-type').on('click',function() {
            var $selected = $('#popupUnknownFileUploaded [name="update_type"]').filter(':checked');
            if ( $selected.length>0 ) {
                $.post('easypopulate/choose-provider',{
                    file_type: $selected.attr('value'),
                    id: $('#popupUnknownFileUploaded input[name="id"]').val()
                },function(){
                    uploader('reload_file_list');
                });
                $('#easypopulate_upload_files_data [name="split"]').val();
            }
            $('#popupUnknownFileUploaded input[name="id"]').val('');
            $('#popupUnknownFileUploaded').hide();
        });

        $('#popupSelectImportFields .js-mapping-confirmed').on('click',function(){
            var params = [];
            params.push({ 'name':'id', 'value': $('#popupSelectImportFields input[name="id"]').val() });

            $('#popupSelectImportFields').find('[name^="map\["]').each(function () {
                params.push({ 'name':this.name, 'value': $(this).val() });
            });

            $('#popupSelectImportFields input[name="id"]').val('');

            $.post('easypopulate/confirm-mapping',params, function() {
                $('#tblFiles').trigger('reload');
            });

            $('#popupSelectImportFields').hide();
        });


        var dtDef = $.fn.dataTable.defaults;
        $.extend(true, $.fn.dataTable.defaults, {
            searching: false,
            ordering:  false,
            sDom:'',
            scrollY: '200px',
            scrollCollapse: false,
            paging: false,
            fnDrawCallback:function(){ }
        } );

      
      var $exportTable = $('#popupSelectExportFields .js-export_columns');
// { checkboxes handle
      $exportTable.on('checkboxes:init',function(){
          $('input[name="remember_choice"]').each(function(){
              this.checked = false;
          });
          var $fields_collection = $(this).find('input[name="field"]');
          var $main_switch = $(this).find('input[name="select_all"]');
          $fields_collection.on('click',function() {
              if ( this.checked ) {
                  $main_switch.get(0).checked = $fields_collection.not(':checked').length==0;
              }else{
                  $main_switch.get(0).checked = false;
              }
          });
          $main_switch.get(0).checked = $fields_collection.length==$fields_collection.filter(':checked').length;
      });
        $exportTable.find('input[name="select_all"]').on('click', function() {
            var main_state = this.checked;
            $exportTable.find('input[name="field"]').each(function() {
                this.checked = main_state;
            });
        });
// } checkboxes handle
      $exportTable.DataTable({
              searching: false,
              ordering:  false,
              sDom:'',
              scrollY: '200px',
              scrollCollapse: false,
              paging: false,
              fnDrawCallback:function(){ }
      });
      $('.js-confirm-fields').on('click',function() {
          var $popupBox = $('#popupSelectExportFields');
          var selected = [];
          $popupBox.find('input[name="field"]').filter(':checked').each( function(){
              selected.push(this.value);
          } );
          var persist_char = '';
          if ( $popupBox.find('input[name="remember_choice"]:checked').length>0 ){
              persist_char = '!';
          }

          $('#frmDownload input[name="selected_fields"]').val(persist_char+selected.join(','));
          var job_id = $popupBox.attr('data-job_id');
          $popupBox.removeAttr('data-job_id');
          $popupBox.hide();
          $('#frmDownload').trigger('confirm_selected_columns',[job_id, persist_char+selected.join(',')]);
          {if $currentDirectory->cron_enabled}
          if ( !job_id ) $('#frmDownload').trigger('submit',[true]);
          {else}
          $('#frmDownload').trigger('submit',[true]);
          {/if}
      });

      /*var table = $('.table').DataTable({
          "scrollY": "200px",
          "scrollCollapse": true,
          "paging": false
      });
      table.draw(false);*/
      $.fn.dataTable.defaults = dtDef;

      $('#frmDownload').on('confirm_selected_columns',function(event, job_id, selected_columns){
          if ( !job_id ) return;
          $.ajax({
            url:'easypopulate/export-columns',
            type: 'POST',
            data:[ { name:'by_id', value: job_id }, { name:'selected_fields', value: selected_columns } ],
            success:function(data) {
            }
        });
      });
  });
</script>

<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.fileupload-process.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.fileupload-validate.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.fileupload-ui.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jQuery-File-Upload/js/jquery.fileupload-jquery-ui.js"></script>
<script type="text/javascript">
    var messageOutput, messageProcess;
    var js_messages = {$js_messages};
    $(document).ready(function() {
        $('input[name="data_file"]').each(function () {
            var $uplCtrl = $(this);
            $(this).fileupload({
                url: '{$upload_form_action_ajax}',
                maxChunkSize: {$upload_max_part_size},
                //autoUpload: false,
                replaceFileInput: false,
                acceptFileTypes: new RegExp('\.(' + $uplCtrl.attr('data-accept') + ')$', 'i')
            }).on('fileuploadprocessalways', function (e, data) {
                var currentFile = data.files[data.index];
                if (data.files.error && currentFile.error) {
                    $('.progress_state').html(currentFile.error);
                }
            }).on('fileuploadprogressall', function (e, data) {
                $('.import_progress').addClass('in_progress');
                $('.js-upload-progress').progressbar('value', parseInt(data.loaded / data.total * 100, 10));
            }).on('fileuploadchange', function (e, data) {
                $('.js-upload-progress').progressbar('value', 0);
                $('.progress_state').html(js_messages['file_changed']);
            }).on('fileuploadadded', function (e, data) {
                $('.js-btn_upload').unbind('click').bind('click', function (e) {

                    $('.progress_state').html(js_messages['file_upload']);
                    e.preventDefault();
                    data.submit().error(function (jqXHR, textStatus, errorThrown) {
                        //console.log(arguments);
                    }).success(function (result, textStatus, jqXHR) {
                        $('.import_progress').removeClass('in_progress');
                        $('.progress_state').html(js_messages['file_uploaded']);
                        setTimeout(function(){
                            $('.progress_state').html('');
                        },5000);
                        messageOutput = function (messageType, messageText) {
                            $('.progress_state').html(messageText);
                        };
                        messageProcess = function (info) {
                            //console.log(info);
                            var messageText = 'Updated ' + info['rows_affected'] + '. Processed ' + info['file_lines_processed'] + '.';
                            $('.progress_state').html(messageText);
                            $('.js-upload-progress').progressbar('value', parseInt(info['progress'], 10));
                        };
                        $('#tblFiles').trigger('reload',[true]);
                        $('#frmUpload').trigger('init_import_start',[result.data_file[0].name]);
                        /*
                        $('#frmFileProcess').remove();
                        var options_array = $('#frmImportOptions').serializeArray();
                        var options_hidden_array = [];
                        for (var i = 0; i < options_array.length; i++) {
                            options_hidden_array.push('<input type="hidden" name="' + options_array[i].name + '" value="' + options_array[i].value + '">');
                        }
                        ;
                        $('body')
                            .append('<form id="frmFileProcess" method="post" action="{$upload_form_action_ajax}" target="file_process">' +
                                '<input type="hidden" name="uploaded_file" value="' + result.data_file[0].name + '">' +
                                '<input type="hidden" name="action" value="file_process">' +
                                (options_hidden_array.join()) +
                                '</form>');
                        $('#frmFileProcess').submit();
                        */
                    });
                    return false;
                });
            });
        });

        $('.progress_bar').progressbar();

        $('#frmUpload').bind('submit', function () {
            return false;
        });
        $('#frmUpload').on('init_import_start',function(event, filename) {
            ep_command('configure', { by_file_name: filename });
        });

        $('#tblFiles').on('reload',function(event, resetPage) {
            if (typeof resetPage === 'undefined') resetPage = false;
            $('#tblFiles').DataTable().ajax.reload(null,resetPage);
        });
        
        // --- js-create-directory
        $('.js-create-directory').on('click',function(){
            var _link = $(this);
            $.ajax({
                url: _link.attr('href'),
                type: 'GET',
                success:function(data){
                    if ( !data ) return;
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }));
                }
            });
            return false;
        });
        $('.js-create-datasource').on('click',function(){
            var _link = $(this);
            $.ajax({
                url: _link.attr('href'),
                type: 'GET',
                success:function(data){
                    if ( !data ) return;
                    bootbox.dialog($.extend(true, { }, bootboxDefaults, data.dialog || { }, {
                        buttons: {
                            confirm:{
                                callback: function (result) {
                                    var params = [];
                                    $('#blockNewDatasource').find('input, select, textarea').each(function(){
                                        params.push({ name:this.name, value:$(this).val() });
                                    });
                                    $.ajax({
                                        url:_link.attr('href'),
                                        type: 'POST',
                                        cache: false,
                                        data: params,
                                        success:function(data) {
                                            bootbox.hideAll();
                                            uploader('reload_file_list');
                                        }
                                    });
                                }
                            }
                        }
                    }));
                }
            });
            return false;
        });

        
        $(document).on('focus','.on-time',function(event){
            var $self = $(event.target);
            if ( !$self.attr('clock-on') ) {
                $self.ptTimeSelect({ zIndex: '2001' });
                $self.attr('clock-on','on');
            }
        }).on('click','.js-directory-config-add-more',function(event) {
            var $table = $('#tblDirectorySetting');
            var skel = $('tfoot',$table).html();
            var rowCount = parseInt($table.attr('data-row_count'),10);
            var newRowHtml = skel.replace(/_new_/g, '['+rowCount+']').replace(/_cnt_/g, rowCount);
            $('tbody',$table).append(newRowHtml);
            rowCount++;
            $table.attr('data-row_count',rowCount);
        }).on('click','.js-directory-config-remove',function(event){
            var $table = $('#tblDirectorySetting');
            var $row = $(event.target).parents('tr');
            $row.remove();
            //$table.find('tbody .'+$row.attr('class')).remove();
        }).on('click focus','.js_ac-ondemand',function(event){
            var $self = $(event.target);
            $self.parent().removeClass('has-error');
            if ( !$self.attr('ac-bind') ) {
                $self.attr('ac-bind','1');
                var source = $self.attr('data-ac-source').split(':');
                $self.autocomplete({
                    source: source,
                    minLength: 0,
                    autoFocus: true,
                    delay: 100
                }).focus(function () {
                    $(this).autocomplete("search");
                });
            }
        });
    });
</script>















