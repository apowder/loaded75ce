{use class="\yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    {if $app->controller->view->filters->cid > 0}
    <input type="hidden" name="cID" id="cID" value="{$app->controller->view->filters->cid}" />
    {/if}
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
    <input type="hidden" name="filter" id="filter" value="{$app->controller->view->filters->filter}" />
</form>
<div class="order-wrap">
<input type="hidden" id="row_id">
           <!--=== Page Content ===-->
				<div class="row order-box-list">
					<div class="col-md-12">
            <div class="widget-content">
              <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>   	
			  {if {$messages|@count} > 0}
			   {foreach $messages as $message}
              <div class="alert fade in {$message['messageType']}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message['message']}</span>
              </div>			   
			   {/foreach}
			  {/if}
              <div class="btn-wr after btn-wr-top">
                    <div>
                        {Html::dropDownList('filters', $app->controller->view->filters->filter, $app->controller->view->filters->filters, ['class' => 'form-control', 'prompt' => PULL_DOWN_DEFAULT, 'onchange' => 'applyFilter(this.value)'])}
                    </div>
                    <div>
                    </div>
                </div> 
                    <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable" checkable_list="0,2,3,4,5" data_ajax="customers_loyalty/list">
                            <thead>
                                    <tr>
                                        {foreach $app->controller->view->loyaltyTable as $tableItem}
                                            <th{if $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                        {/foreach}
                                    </tr>
                            </thead>

                    </table>            

                </form>
            </div>
				
					</div>
				</div>
<script type="text/javascript">

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

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement(id) {
    $("#loyalty_management").hide();
    //switchOnCollapse('countries_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    return false;
}

function applyFilter(value) {
    $("#row_id").val(0);
    $("#filter").val(value);
    resetStatement();
    return false;    
}

function onClickEvent(obj, table) {
    $("#loyalty_management").hide();
    $('#loyalty_management_data .scroll_col').html('');
    $('#row_id').val(table.find(obj).index());
    setFilterState();
    var loyalty_id = $(obj).find('input.cell_identify').val();

    $.post("{Yii::$app->urlManager->createUrl('customers_loyalty/view')}", { 'loyalty_id' : loyalty_id }, function(data, status){
            if (status == "success") {
                $('#loyalty_management_data .scroll_col').html(data);
                $("#loyalty_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
}

function onUnclickEvent(obj, table) {
    var event_id = $(obj).find('input.cell_identify').val();
}

function newLoyalty(id){
$("#loyalty_management").hide();
$.get("customers_loyalty/edit", { 'cid' : id , 'action' : 'new', 'filter' : $("#filter").val() }, function(data, status){
    if (status == "success") {
        $('#loyalty_management_data .scroll_col').html(data);
        $("#loyalty_management").show();
        //switchOffCollapse('countries_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function loyaltySave(){
$.post("{Yii::$app->urlManager->createUrl('customers_loyalty/save')}", $('form[name=loyalty]').serialize(), function(data, status){
    if (status == "success") {
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
		resetStatement();
        setTimeout('$(".cell_identify[value=\''+id+'\']").click();', 500);		
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}



					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="loyalty_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="loyalty_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>