				<!--=== Page Header ===-->
				<div class="page-header">
					<div class="page-title">
						<h3>{$this->view->headingTitle}</h3>
					</div>
				</div>
				<!-- /Page Header -->
                                
                                <!--=== Page Content ===-->

                                <!--===Brands List ===-->
				<div class="row">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> {$this->view->headingTitle} Listing</h4>
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="brand_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
							</div>
							<div class="widget-content" id="brand_management">
              <table class="" width="100%">
               <tr>
               <td width="60%">
                      <table class="table table-striped table-bordered table-hover table-responsive datatable table-checkable brands-table" checkable_list="1">
                        <thead>
                          <tr>
                                                                                    {foreach $this->view->brandsTable as $tableItem}
                                                                                        <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
                          </tr>
                        </thead> 
                        <tbody>
                                                                                    {foreach $this->view->brandsTableData as $tableItem}
                                                                                        <tr >
                                                                                          <td>
                                                                                          {$tableItem['data']}
                                                                                          </td>
                                                                                          <td>Edit</td>
                                                                                          </tr>
                                                                                    {/foreach}
                        </tbody>                  
                      </table>
                  
            
                </td>
                <td valign="top"> 
                  <div id="brandinfo" class=" " style="height:100%;float:right;text-align: center; margin: 82px 50px;">
                    <div>
                    </div>

                  </div>
                </td>
                </tr>
                <tr><td colspan="2">
                    <div style="width:100%">
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary" value="Insert" onClick="newBrand()">
                                                                </p>    
                    </div>     
                      </td></tr></table>
							</div>
						</div>
					</div>
				</div>
                                <!-- /Brands List -->
                                
                                {include file='../../../includes/javascript/ajax_load.js'}
                                
                                <script type="text/javascript">
                                $(document).ready(function(){

                                  $.each($('#brand_management td'), function(i, item){
                                    if ($(item).find('.cell_identify').val() == {$mID}){
                                      $(item).parent().addClass('selected');
                                    }
                                  })
                                  getListproducts();
                                  
                                  $('a.btn').load({
                                    'container':'#brandinfo'
                                  });

                                
                                })
                                function getListproducts(){
                                          var brand_id = $('input[type=hidden][name=mID]').val();
                                          $.get('brand_manager/listproducts','mID='+brand_id+'&filter='+$('select[name=filter]').val(), function(data){
                                            filTable(data, brand_id);
                                          }, 'json');
                                          $.get('brand_manager/brandactions','mID='+brand_id, function(data){
                                            $('#brandinfo').html(data);
                                            $('a.btn').load({
                                              'container':'#brandinfo'
                                              });
                                          }, 'html');
                                }
                                
                                    function updateBrand() {

$.post($('form[name=manufacturers]').attr('action'), $('form[name=manufacturers]').serialize(), function(data, status){
    if (status == "success") {
        $('#brandinfo').html(data);
        $('a.btn').load({
          'container':'#brandinfo'
        });        
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
                                    } 

                                    function newBrand(){
                                        var brand_id = $('input[type=hidden][name=mID]').val();
                                          $.get('brand_manager/brandactions','action=new&mID='+brand_id, function(data){
                                            $('#brandinfo').html(data);
                                            $('a.btn').load({
                                              'container':'#brandinfo'
                                              });
                                          }, 'html');
                                    }
                                
                                    function onClickEvent(obj, table) {
                                        //$('#catalog_management_data').html('');
                                        var brand_id = $(obj).find('input.cell_identify').val();
                                        if (brand_id > 0){
                                          $('input[type=hidden][name=mID]').val(brand_id);
                                          getListproducts();
                                        }
                                    }
                                    function onUnclickEvent(obj, table){
                                      return false;
                                    }
                                    
                                    function filTable(data, brand_id){
                                            $('#pcontent').html('');
                                            $('#pcontent').append('<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="1">');
                                            $('#pcontent table:last').dataTable({
                                              "data": data['data'],
                                                      "columns": [
                                                      { "title": "PRODUCT CODE" },
                                                      { "title": "NAME" },
                                                      { "title": "PRICE" },
                                                      { "title": "PRICE(gross)", "class": "center" },
                                                      { "title": "STK", "class": "center" },
                                                      { "title": "VIS", "class": "center" },
                                                      { "title": "ACTION", "class": "center" }
                                                  ]
                                            })
                                    }
                                    </script>
  
                                <!--===Actions ===-->
				<div class="row" id="product_management">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> <span id="product_management_title">Products Manager</span></h4>
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="brand_products_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
							</div>
							<div class="widget-content" id="product_management_data">
               <form name="products" action="brand_manager/update" method="post" onSubmit="return updateProducts();">
               <div style="float:right;">{$filter}</div><br><br>
               <input type="hidden" name="mID" value="{$mID}">
               <div id="pcontent">
<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="1">
									<thead>
										<tr>
                                                                                    {foreach $this->view->productsTable as $tableItem}
                                                                                        <th>{$tableItem['title']}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
                  <tbody>
                                                                                    {foreach $this->view->productsTableData as $tableItem}
                                                                                        <tr>
                                                                                          <td>
                                                                                          {$tableItem['model']}
                                                                                          </td>
                                                                                          <td>
                                                                                          {$tableItem['name']}
                                                                                          </td>  
                                                                                          <td>
                                                                                          {$tableItem['price']}
                                                                                          </td>
                                                                                          <td>
                                                                                          {$tableItem['price1']}
                                                                                          </td>
                                                                                          <td>
                                                                                          {$tableItem['qty']}
                                                                                          </td>
                                                                                          <td>
                                                                                          {$tableItem['status']}
                                                                                          </td>
                                                                                          <td>
                                                                                          {$tableItem['action']}
                                                                                          </td>              
                                                                                          </tr>
                                                                                    {/foreach}
                  
                  </tbody>    
                  </table>
                  </div>
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary" value="New Product" onClick="return editProduct(0)" style="float:right;">
                                                                    <input type="submit" class="btn btn-primary" value="Update" >
                                                                </p>
                    </form>
                                                                <script> 
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
                                                                
function editProduct(products_id) {
//$('#brand_products_collapse, #brand_list_collapse').click();
$.post("categories/productedit?bm=1&mID="+$('input[type=hidden][name=mID]').val(), { 'products_id' : products_id }, function(data, status){
    if (status == "success") {
        $('#catalog_management_data').html(data);
        $("#catalog_management").show();
        switchOffCollapse('brand_products_collapse');
        switchOffCollapse('brand_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
                                    }
                                    
                                    function deleteProduct(goto){
                                      if (confirm('Are you sure you want to permanently delete this product?')){
                                        window.location.href = goto;
                                      }
                                    }
                                    
                                    function resetStatement() {
                                        $("#catalog_management").hide();
                                        switchOnCollapse('brand_products_collapse');
                                        switchOnCollapse('brand_list_collapse');
                                        //var table = $('.table').DataTable();
                                        //table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }
                                    
                                    function updateProducts() {
switchOffCollapse('brand_products_collapse');
$.post("brand_manager/update", $('form[name=products]').serialize(), function(data, status){
    if (status == "success") {
         filTable(data, $('input[type=hidden][name=mID]').val());
         switchOnCollapse('brand_products_collapse');
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;
                                    }                                    
                                    
                                    function checkProductForm() {
$("#catalog_management").hide();
var products_id = $( "input[name='products_id']" ).val();
$.post("categories/productsubmit?bm=1", $('#products_edit').serialize(), function(data, status){
    if (status == "success") {
       getListproducts();
       resetStatement();
    } else {
        alert("Request error.");
    }
},"html");

                                        return false;
                                    }                                    

                                                                </script>
							</div>
						</div>
					</div>
                                </div>
   				<!--===products ===-->                             
				<div class="row" id="catalog_management" style="display: none;">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> <span id="catalog_management_title">Catalog Management</span></h4>
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="catalog_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
							</div>
							<div class="widget-content" id="catalog_management_data">
                                                            Action
							</div>
						</div>
					</div>
        </div>                                
				<!--===Actions ===-->
				<!-- /Page Content -->