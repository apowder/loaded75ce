{use class="\common\models\Google"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
           <!--=== Page Content ===-->
		   <style type="text/css">.dataTables_wrapper.no-footer .dataTables_footer{ display:none; } .dataTables_filter { display:none; }</style>
		<div class="widget-content ">
			<div class="" id="modules_list">
              <!-- TABS-->
                      <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
                          {foreach $platforms as $platform}
                          <li {if $first_platform_id==$platform['id']} class="active"{/if}><a href="#platform{$platform['id']}" data-toggle="tab"><span>{$platform['text']}</span></a></li>
                          {/foreach}
                            <li>
                                <a href="#reserved" data-toggle="tab"><span>{$smarty.const.GOOGLE_RESERVED_KEYS}</span></a>
                            </li>
                        </ul>
                          <div class="tab-content" id="google_list_data">
                              {foreach $platforms as $platform}
                                  <div id="platform{$platform['id']}" class="tab-pane {if $first_platform_id==$platform['id']}active{/if}">
										<table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable double-grid" checkable_list="" data-b-paginate="false" data-paging="false" data-info="false" displayLength = "-1" data_ajax="google_analytics/list?platform_id={$platform['id']}">
											<thead>
											<tr>
												{foreach $app->controller->view->tabList[$platform['id']] as $tableItem}
													<th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
												{/foreach}
											</tr>
											</thead>
										</table>
                                  </div>
                              {/foreach}
                              <div class="tab-pane " id="reserved">
								<form action="{\yii\helpers\Url::to('google_analytics/submit')}" method="post">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font table-send-coup">
										{foreach $reserved as $item}
                                            <tr>
                                                <td align="left"><label>{$item['module']}</label></td>
                                                <td><input type="text" class="form-control" name="settings[{$item['google_settings_id']}]" value="{$item['info']}"/></td>
												<td align="right"><label>{$item['module_name']}</label></td>
                                            </tr>
										{/foreach}
									</table>
									<input type="submit" class="btn btn-primary" value="{$smarty.const.IMAGE_UPDATE}">
								</form>
                              </div>

                          </div>

                 </div>

				</div>
              </div>{*widget*}
              <!--END TABS-->


<script type="text/javascript">

function onClickEvent(obj, table) {
}
function onUnclickEvent(obj, table) {
}
function resetStatement(platform_id) {
    $("#modules_management").hide();

    var table = $('#platform'+platform_id+' .table').DataTable();
    table.draw(false);
    return false;
}



function changeModule(module, platform_id, action){
	$confirm = false;
	if (action != 'remove'){
		$confirm = true;
	} else {
		if (confirm('{$smarty.const.TEXT_DELETE_SELECTED}?')){
		$confirm = true;
		}
	}
	if ($confirm){
		$.post('google_analytics/change',
			{
				'module' :module,
				'platform_id': platform_id,
				'action' : action
			},
			function(data, status){
               if (status == "success") {
                    resetStatement(platform_id);
                } else {
                    alert("Request error.");
                }
			},
			'html'
		);	
	}

}


function BootstrapIt(module, platform_id){
	$('input[data-module='+module+'][data-platform_id='+platform_id+'].check_on_off').bootstrapSwitch(
		{
		onSwitchChange: function (element, arguments) {
		  $.post('google_analytics/change',
				{
				'module' :element.target.dataset.module,
				'platform_id': element.target.dataset.platform_id,
				'action' : 'status',
				'status': arguments,
				}, 
				function (data, status){
					if (status == "success") {
						resetStatement(element.target.dataset.platform_id);
					} else {
						alert("Request error.");
					}				
				},
				'html'
		  );
		  return true;
		},
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
		handleWidth: '38px',
		labelWidth: '24px'
	  }	
	);
}

</script>