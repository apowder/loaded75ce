<div class="widget box">
    <div class="widget-content">
        <table id="tblFiles" class="ep-file-list table table-striped table-selectable table-checkable table-hover table-responsive table-bordered -datatable tab-cust tabl-res double-grid"
            checkable_list="" data_ajax="{$job_list_url}" data-directory_id="{$currentDirectory->directory_id}">
            <thead>
            <tr>
                <th>{$smarty.const.ICON_FILE}</th>
                <th>{$smarty.const.HEADING_TYPE}</th>
                <th>{$smarty.const.TABLE_HEADING_FILE_SIZE}</th>
                <th>{$smarty.const.TEXT_INFO_DATE_ADDED1}</th>
                {if $currentDirectory->cron_enabled and ($currentDirectory->directory_type=='import' or $currentDirectory->directory_type=='datasource')}
                <th>State</th>
                {/if}
                <th>{$smarty.const.TABLE_HEADING_ACTION}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<style>
.ep-file-list a.remove-ast{ text-decoration: none; }
a.job-button{ text-decoration: none; margin: 0 4px; font-size: 1.1em; }
a.job-button:hover{ text-decoration: none; }
.job-button .icon-trash{ color: #ff0000 }
.job-button .icon-cog, .job-button .icon-reorder{ color: #008be8 }
.job-button .icon-play{ color: #006400 }
</style>
<script type="text/javascript">

    function ep_file_remove(fileId) {
        $.ajax({
            url:'easypopulate/remove-ep-file',
            type: 'POST',
            cache: false,
            data: {
                id: fileId
            },
            success:function(data) {
                if ( data.status=='error' ) {

                }else{
                    $('#tblFiles').trigger('reload');
                }
            }
        });

        return false;
    }

    function ep_directory_remove(directoryId) {
        bootbox.confirm('Remove datasource?',function(process){
            if ( !process ) return;

            $.ajax({
                url:'easypopulate/remove-directory',
                type: 'POST',
                cache: false,
                data: {
                    id: directoryId
                },
                success:function(data) {
                    if ( data.status=='error' ) {

                    }else{
                        $('#tblFiles').trigger('reload');
                    }
                }
            });

        });

        return false;
    }

</script>