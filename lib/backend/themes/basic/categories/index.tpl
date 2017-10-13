<!--=== Page Header ===-->
<div class="page-header">
        <div class="page-title">
                <h3>{$app->controller->view->headingTitle}</h3>
        </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
        <div class="widget box box-wrapp-blue filter-wrapp">
          <div class="widget-header filter-title">
            <h4>{$smarty.const.TEXT_FILTER}</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
              </div>
            </div>
          </div>
          <div class="widget-content">
              <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                {if $isMultiPlatforms}
								<div class="filt_left">
                      <label>{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</label>

                      <div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div></div>
                      {foreach $platforms as $platform}
                        <div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} data-checked="true" checked="checked"{else} data-checked="false" {/if}> {$platform['text']}</label></div></div>
                      {/foreach}
								</div>
                {/if}

            <div class="filter_categories {if $isMultiPlatforms}filter_categories_1{/if}">
              <div class="filter_block after">
                <div class="filter_left">
                  <div class="filter_row row_with_label">
                    <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                    <select class="form-control" name="by">
                        {foreach $app->controller->view->filters->by as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                  </div>
                  <div class="filter_row row_with_label">
                    <label>{$smarty.const.TEXT_BRAND}</label>
                    <div class="f_td f_td_group brands">
                        <input type="text" value="{$app->controller->view->filters->barnd}" name="barnd" id="selectBrand" class="form-control" placeholder="{$smarty.const.TEXT_CHOOSE_BRAND}">
                    </div>
                  </div>
                  <div class="filter_row stock_row row_with_label">
                    <label>{$smarty.const.TEXT_STOCK}</label>
                    <select name="stock" class="form-control">
                        {foreach $app->controller->view->filters->stock as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                  </div>
                  <div class="filter_attr">
                    <input type="checkbox" class="uniform" name="prod_attr" value="1" {if $app->controller->view->filters->prod_attr == 1}checked{/if}>
                    <label>{$smarty.const.TEXT_PRODUCTS_ATTR}</label>
                  </div>
                </div>
                <div class="filter_right">
                  <div class="filter_row filter_disable">
                    <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" />
                  </div>
                  <div class="filter_row supllier_filter">
                    <label>{$smarty.const.TEXT_SUPPLIER}</label>
                    <div class="f_td f_td_group suppliers">
                        <input type="text" value="{$app->controller->view->filters->supplier}" name="supplier" id="selectSupplier" class="form-control" placeholder="{$smarty.const.TEXT_CHOOSE_SUPPLIER}">
                    </div>
                  </div>
                  <div class="price_row after">
                    <div class="price_title">{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}</div>
                    <div class="price_desc">
                      <span>{$smarty.const.TEXT_FROM}</span>
                      <input type="text" name="price_from" value="{$app->controller->view->filters->price_from}" class="form-control">
                      <span>{$smarty.const.TEXT_TO}</span>
                      <input type="text" name="price_to" value="{$app->controller->view->filters->price_to}" class="form-control">
                    </div>
                  </div>
                  <div class="weight_row">
                    <label class="weight_title">{$smarty.const.TEXT_WEIGHT}:</label>
                    <div class="weight_desc">
                      <div class="weight_field_text">
                        <span>{$smarty.const.TEXT_FROM}</span>
                        <input type="text" name="weight_from" value="{$app->controller->view->filters->weight_from}" class="form-control">
                        <span>{$smarty.const.TEXT_TO}</span>
                        <input type="text" name="weight_to" value="{$app->controller->view->filters->weight_to}" class="form-control">
                      </div>
                      <div class="weight_field">
                        <input type="radio" name="weight_value" value="kg" {if $app->controller->view->filters->weight_kg}checked{/if}>
                        <span>Kg</span>
                        <input type="radio" name="weight_value" value="lbs" {if $app->controller->view->filters->weight_lbs}checked{/if}>
                        <span>Lbs</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="filter_check_pad">
              <div class="filter_checkboxes">
                <div>
                  <input type="checkbox" class="uniform" name="low_stock" value="1" {if $app->controller->view->filters->low_stock == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_LOW_STOCK}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="featured" value="1" {if $app->controller->view->filters->featured == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_FEATURED}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="gift" value="1" {if $app->controller->view->filters->gift == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_GIFT_WRAP}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="virtual" value="1" {if $app->controller->view->filters->virtual == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_VIRTUAL_PRODUCT}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="all_bundles" value="1" {if $app->controller->view->filters->all_bundles == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_ALL_BUNDLES}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="sale" value="1" {if $app->controller->view->filters->sale == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_SALE}</span>
                </div>
              </div>
              </div>
              <div class="filters_buttons">
                <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                <button class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
              </div>
            </div>
                  <input type="hidden" name="listing_type" id="listing_type" value="{$app->controller->view->filters->listing_type}" />
                  <input type="hidden" name="category_id" id="global_id" value="{$app->controller->view->filters->category_id}" />
                  <input type="hidden" name="brand_id" id="brand_id" value="{$app->controller->view->filters->brand_id}" />
                  <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
            </form>
          </div>
        </div>
        <div class="category_list">
          <div class="cat_left_column">
            <!-- Tabs-->
            <div class="tabbable tabbable-custom scroll_col">
                    <ul class="nav nav-tabs">
                        <li{if $app->controller->view->filters->listing_type == 'category'} class="active"{/if}><a href="#tab_1_1" onclick="changeListingCategory()" data-toggle="tab"><i class="icon-folder-open"></i><span>{$smarty.const.TEXT_CATEGORIES}</span></a></li>
                            <li{if $app->controller->view->filters->listing_type == 'brand'} class="active"{/if}><a href="#tab_1_2" onclick="changeListingBrand()" data-toggle="tab"><i class="icon-tag"></i><span>{$smarty.const.TEXT_BRANDS}</span></a></li>
                    </ul>
                    <div class="tab-content">
                            <div class="tab-pane{if $app->controller->view->filters->listing_type == 'category'} active{/if}" id="tab_1_1">
                              <div class="top_cat">
      <div class="cat_search_by">
        <div class="input-group input-group-order"><span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span><input type="search" id="categorysearch" class="form-control" placeholder="{$smarty.const.ENTRY_SEARCH_CATEGORIES}"></div>
      </div>
     <div class="sorting_collapse after">
        <div class="switch_collapse">
          <a href="#" class="expand_all switch_active">{$smarty.const.ENTRY_EXPAND_ALL}</a>
          <a href="#" class="collapse_all">{$smarty.const.ENTRY_COLLAPSE_ALL}</a>
        </div>
       {*<div class="sw_sort_by_cat">
         <select name="sort_by_cat" class="form-control">
           <option value="sort by">Sort by</option>
           <option value="sort by">Sort by</option>
           <option value="sort by">Sort by</option>
         </select>
       </div>*}
     </div>
   </div>

{function name=renderCategoriesTree level=0}

<ol class="categories_ul dd-list">
{foreach $items as $item name=foo}
<li class="dd-item dd3-item" data-id="{$item.id}">
  <div class="tl-wrap-li-left-cat">
    <div class="dd-handle handle">
        <i class="icon-hand-paper-o"></i>
    </div>
    <div class="dd3-content{if $item.id == $app->controller->view->filters->category_id} selected{/if}">
        <span class="cat_li"><span id="{$item.id}" class="cat_text" onClick="changeCategory(this)">{$item.text}</span>
            <a href="{Yii::$app->urlManager->createUrl(['categories/categoryedit', 'categories_id' => $item.id])}" class="edit_cat"><i class="icon-pencil"></i></a>
            <a class="delete_cat" href="{Yii::$app->urlManager->createUrl(['categories/confirmcategorydelete', 'popup' => 1,'categories_id' => $item.id])}"><i class="icon-trash"></i></a>
            {if count($item.child) > 0}<span class="collapse_span"></span>{/if}
        </span>
    </div>
  </div>
{if count($item.child) > 0}
{call name=renderCategoriesTree items=$item.child level=$level+1}
{/if}
</li>
{/foreach}
</ol>


{/function}

                              <div class="dd cat_main_box">
                                  <div class="dd3-content"><span class="cat_li"><span id="0" class="" onClick="changeCategory(this)">{$smarty.const.TEXT_TOP}</span></span></div>
{call renderCategoriesTree items=$app->controller->view->categoriesTree}
                              </div>
                              <div class="cat_buttons after">
                                <a class="btn btn-add-category btn-primary js_create_new_category" href="{Yii::$app->urlManager->createUrl('categories/categoryedit')}">{$smarty.const.TEXT_CREATE_NEW_CATEGORY}</a>
                                <a class="btn btn-add-product btn-primary js_create_new_product"  href="{Yii::$app->urlManager->createUrl('categories/productedit')}">{$smarty.const.TEXT_CREATE_NEW_PRODUCT}</a>
                              </div>
                            </div>
                            <div class="tab-pane{if $app->controller->view->filters->listing_type == 'brand'} active{/if}" id="tab_1_2">
                              <div class="top_brands after">
                                <div class="brand_search_by">
                                  <div class="input-group input-group-order"><span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span><input type="search" id="brandsearch" class="form-control" placeholder="{$smarty.const.TEXT_SEARCH_BRANDS}"></div>
                                </div>
                                <!--<div class="sort_by_brands">
                                  <select class="form-control">
                                    <option value="Sort by">Sort by</option>
                                    <option value="Sort by">Sort by</option>
                                    <option value="Sort by">Sort by</option>
                                  </select>
                                </div>!-->
                              </div>
                              <div class="brand_box">
                                <ul>
                                    <li class="li_block"><span class="brand_li"><span id="0" onclick="changeBrand(this)">{$smarty.const.TEXT_ALL}</span></span></li>
                                    <li class="li_block"><span class="brand_li"><span id="-1" onclick="changeBrand(this)">{$smarty.const.TEXT_ALL_WITHOUT_BRAND}</span></span></li>
                                     {foreach $app->controller->view->brandsList as $brandItem}
                                         <li id="brands-{$brandItem.id}" class="li_block{if $brandItem.id == $app->controller->view->filters->brand_id} selected{/if}"><span class="handle"><i class="icon-hand-paper-o"></i></span><span class="brand_li"><span class="brand_text" id="{$brandItem.id}" onClick="changeBrand(this)">{$brandItem.text}</span><a href="{Yii::$app->urlManager->createUrl(['categories/brandedit', 'manufacturers_id' => $brandItem.id])}" class="edit_brand"><i class="icon-pencil"></i></a>
                                                 <a class="delete_brand" href="{Yii::$app->urlManager->createUrl(['categories/confirm-manufacturer-delete', 'manufacturers_id' => $brandItem.id])}"><i class="icon-trash"></i></a></span></li>
                                    {/foreach}
                                </ul>
                              </div>
                              <div class="cat_brand_buttons">
                                <a href="{Yii::$app->urlManager->createUrl('categories/brandedit')}" class="btn btn-primary"><i class="icon-tag"></i>{$smarty.const.TEXT_CREATE_NEW_BRANDS}</a>
                              </div>
                            </div>
                    </div>
            </div>
          </div>
            <!--END TABS-->
          <div class="cat_center">
                                                                                
    <div class="order-wrap">                            <!--===Customers List ===-->
				<div class="row order-box-list">
					<div class="col-md-12">
							<div class="widget-content">
                <div id="list_bread_crumb"></div>
								<table class="table table-striped table-bordered table-hover table-responsive table-selectable datatable tab-status sortable-grid catelogue-grid" data_ajax="categories/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->catalogTable as $tableItem}
                                                                                        <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
									
								</table>
<div class="count_category">
  <span>{$smarty.const.TEXT_CATEGORIES} <strong id="categories_counter">0</strong></span>
  <span>{$smarty.const.TEXT_PRODUCTS} <strong id="products_counter">0</strong></span>
</div>
							</div>
					</div>
				</div>
				
                                <!-- /Customers List -->
                                             
<script type="text/javascript">
function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
    CKEDITOR.replaceAll('ckeditor');
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
    window.history.replaceState({ }, '', url);
}

function resetStatement() {
    setFilterState();
    //$("#catalog_management").hide();
    //switchOnCollapse('catalog_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    //$(window).scrollTop(0);
    return false;
}

var files = {};
var form;

function editCategory(category_id) {
    $("#catalog_management").hide();
    $.post("categories/categoryedit?categories_id="+category_id, {}, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            switchOffCollapse('catalog_list_collapse');
             files = {};
             addFileListeners($(':file'), files);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
                           
function checkCategoryForm() {
    $("#catalog_management").hide();
    cke_preload();
    var category_id = $( "input[name='categories_id']" ).val();
    form = collectData('new_category', files);
    var xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
            xhr.onreadystatechange = function() {
              if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                  switchOnCollapse('catalog_list_collapse');
                  var table = $('.table').DataTable();
                  table.draw(false);
                  setTimeout('$(".cell_identify[value=\''+category_id+'\']").click();', 500);
                } else {
                  alert("Request error.");
                }
              } 
            };  
    xhr.open("POST", 'categories/categorysubmit', true);
    xhr.send(form);
    return false;
}
                                    
function checkProductForm() {
    $("#catalog_management").hide();
    cke_preload();
    var products_id = $( "input[name='products_id']" ).val();
    form = collectData('products_edit', files);
    var xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
            xhr.onreadystatechange = function() {
              if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                  switchOnCollapse('catalog_list_collapse');
                  var table = $('.table').DataTable();
                  table.draw(false);
                  $('#messageStack').html(xhr.responseText);
                  setTimeout('$(".cell_identify[value=\''+products_id+'\']").click();', 500);
                } else {
                  alert("Request error.");
                }
              } 
            };  
    xhr.open("POST", 'categories/productsubmit', true);
    xhr.send(form);
    return false;
}
                                    

function editProduct(products_id) {
    $("#catalog_management").hide();
    $.post("categories/productedit", { 'products_id' : products_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            //switchOffCollapse('catalog_list_collapse');
            files = {};
            addFileListeners($(':file'), files);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteProduct() {
    $("#catalog_management").hide();
    $.post("categories/productdelete", $('#products_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmDeleteProduct(products_id) {
$("#catalog_management").hide();
$.post("categories/confirmproductdelete", { 'products_id' : products_id }, function(data, status){
    if (status == "success") {
        $('#catalog_management_data .scroll_col').html(data);
        $("#catalog_management").show();
        //switchOffCollapse('catalog_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
    return false;
}

function confirmMoveProduct(products_id) {
    var categories_id = $('#global_id').val();
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-move')}", { 'products_id' : products_id, 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function moveProduct() {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/product-move')}", $('#products_move').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmCopyProduct(products_id) {
    var categories_id = $('#global_id').val();
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-copy')}", { 'products_id' : products_id, 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function copyProduct() {
    $.post("{Yii::$app->urlManager->createUrl('categories/product-copy')}", $('#products_copy').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmCopyProductAttr(products_id) {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-attr-copy')}", { 'products_id' : products_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function copyProductAttr() {
    $.post("{Yii::$app->urlManager->createUrl('categories/product-attr-copy')}", $('#products_attr_copy').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function switchStatement(type ,id, status) {
    $.post("categories/switch-status", { 'type' : type, 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}
function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.name, element.target.value, arguments);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
  offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $("#catalog_management").hide();
    $('#catalog_management_data .scroll_col').html('');
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    if (type_code == 'category') {
        $("#catalog_management_title").text('Category Management');
        $.post("categories/categoryactions", { 'categories_id' : event_id }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (type_code == 'product') {
        $("#catalog_management_title").text('Product Management');
        $.post("categories/productactions", { 'products_id' : event_id }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (type_code == 'parent') {
        event_id = $('#global_id').val();
        $("#catalog_management_title").text('Category Management');
        $.post("categories/categoryactions", { 'categories_id' : event_id }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    }
}
                                    
function onUnclickEvent(obj, table) {
    $("#catalog_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    /*$(table).dataTable({
        destroy: true,
        "ajax": "categories/list/parent/"+event_id
    });*/
    if (type_code == 'category' || type_code == 'parent') {
        $('#global_id').val(event_id);
        changeCategory($('span#'+event_id))
        //$(table).DataTable().draw(false);
    }

}

function resetFilter() {
    $('select[name="by"]').val('');
    $('input[name="search"]').val('');
    $('input[name="barnd"]').val('');
    $('input[name="supplier"]').val('');
    $('select[name="stock"]').val('');
    //$('.js_platform_checkboxes').prop("checked", false);
    $('input[name="price_from"]').val('');
    $('input[name="price_to"]').val('');
    $('input[name="weight_from"]').val('');
    $('input[name="weight_to"]').val('');
    $('input[name="prod_attr"]').prop("checked", false);
    $('input[name="low_stock"]').prop("checked", false);
    $('input[name="featured"]').prop("checked", false);
    $('input[name="gift"]').prop("checked", false);
    $('input[name="virtual"]').prop("checked", false);
    $('input[name="all_bundles"]').prop("checked", false);
    $('input[name="sale"]').prop("checked", false);
    $("#row_id").val(0);
    resetStatement();
    $("div.dd3-content.selected").removeClass('selected');
    $(".categories_ul li.dd-item[data-id='"+$('#global_id').val()+"'] div.dd3-content").addClass('selected');
    $("div.brand_box li.selected").removeClass('selected');
    $("div.brand_box li[id='brands-"+$('#brand_id').val()+"']").addClass('selected');
    return false;  
}

function closePopup() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
}
function applyFilter() {
    $("div.dd3-content.selected").removeClass('selected');
    $("div.brand_box li.selected").removeClass('selected');
    var $platforms = $('.js_platform_checkboxes');
    if ( $platforms.length>0 ) {
      var http_method = false;
      $platforms.filter('[data-checked]').each(function(){
        if ( this.checked != ($(this).attr('data-checked')=='true') ) {
          http_method = true;
        }
      });
      if ( http_method ) return true;
    }
    resetStatement();
    return false;    
}                                    
function changeCategory(obj) {
    var event_id = $(obj).attr('id');
    $('#global_id').val(event_id);
    $("div.dd3-content.selected").removeClass('selected');
    $(obj).parent('span').parent('div').addClass('selected');
    
    var table = $('.table').DataTable();
    table.page( 'first' );// .draw( 'page' );
    
    resetFilter();
    //resetStatement();
    return false;
}
function changeListingCategory() {
    $("#listing_type").val('category');
    resetFilter();
}
function changeBrand(obj) {
    var event_id = $(obj).attr('id');
    $('#brand_id').val(event_id);
    $("li.li_block.selected").removeClass('selected');
    $(obj).parent('span').parent('li').addClass('selected');
    resetStatement();
    return false;
}
function changeListingBrand() {
    $("#listing_type").val('brand');
    resetFilter();
}

function confirmDeleteCategory(categories_id) {
    $("#catalog_management").hide();
    $.post("categories/confirmcategorydelete", { 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            //switchOffCollapse('catalog_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteCategory() {
$("#catalog_management").hide();
$.post("{Yii::$app->urlManager->createUrl('categories/categorydelete')}", $('#categories_edit').serialize(), function(data, status){
    if (status == "success") {
        closePopup();
        $( ".cat_main_box" ).html(data);
        $('.edit_cat').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
        $('.delete_cat').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
        $('.collapse_span').click(function(){
            $(this).toggleClass('c_up');
            $(this).parent().parent().next().slideToggle();
        });
        resetStatement();
    } else {
        alert("Request error.");
    }
},"html");

    return false;
}

function deleteManufacturer() {
    $.post("{Yii::$app->urlManager->createUrl('categories/manufacturer-delete')}", $('#manufacturer_delete').serialize(), function(data, status){
            if (status == "success") {
                closePopup();
                $( ".brand_box" ).html(data);
                $('.edit_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    return false;
}
function confirmMoveCategory(categories_id) {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-category-move')}", { 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function moveCategory() {
$("#catalog_management").hide();
$.post("{Yii::$app->urlManager->createUrl('categories/category-move')}", $('#categories_move').serialize(), function(data, status){
    if (status == "success") {
        $( ".cat_main_box" ).html(data);
        $('.edit_cat').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
        $('.delete_cat').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
        $('.collapse_span').click(function(){
            $(this).toggleClass('c_up');
            $(this).parent().parent().next().slideToggle();
        });
        resetStatement();
    } else {
        alert("Request error.");
    }
},"html");

    return false;
}


$(document).ready(function() {
    $('#selectBrand').autocomplete({
        source: "{Yii::$app->urlManager->createUrl('categories/brands')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group.brands',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
    $('#selectSupplier').autocomplete({
        source: "{Yii::$app->urlManager->createUrl('categories/suppliers')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group.suppliers',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });    

    $( ".cat_main_box" ).nestable();
    $( ".cat_main_box" ).on('change', function() {
        var data = window.JSON.stringify($(this).nestable('serialize'));
        $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}", { 'categories' : data }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    });
    /*$( ".categories_ul" ).sortable({
      handle: ".handle"
    });*/

    $( ".datatable tbody" ).sortable({
        stop: function( event, ui ) {
            var elem = document.elementFromPoint(event.clientX, event.clientY);
            var obj = $(elem).parents("li.dd-item");
            if (obj[0] != undefined) {
//                console.log(ui.item[0]);//orig
//                console.log( obj[0] );//target
                
//                var title = $(obj[0]).children('div.dd3-content').children('span.cat_li').children('span.cat_text').text();
//                var categories_id = $(obj[0]).attr('data-id');
                //console.log( categories_id );
                
                var cell_identify = $(ui.item[0]).find('.cell_identify').val();
//                console.log( cell_identify[0] );
                var cell_type = $(ui.item[0]).find('.cell_type').val();
//                console.log( cell_type[0] );
                
                //if (cell_type[0] != undefined) {
                    //var type_code = $(cell_type).val();
                    if (cell_type == 'product') {
                        var title = $(obj[0]).children('div.dd3-content').children('span.cat_li').children('span.cat_text').text();
                        var categories_id = $(obj[0]).attr('data-id');
                        bootbox.dialog({
                            message: '<div class=""><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div></div>',

                            title: "{$smarty.const.TEXT_MOVE_OR_COPY_PRODUCT_TO} " + title,
                            buttons: {
                                    success: {
                                            label: "{$smarty.const.TEXT_YES}",
                                            className: "btn btn-primary",
                                            callback: function() {
                                                var copy_to = $('input[name="copy_to"]:checked').val();
                                                var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                                var current_category_id = $('#global_id').val();
                                                $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'products_id' : cell_identify, 'categories_id' : categories_id, 'copy_to' : copy_to, 'copy_attributes' : copy_attributes, 'current_category_id' : current_category_id }, function(data, status){
                                                    if (status == "success") {
                                                        resetStatement();
                                                    } else {
                                                        alert("Request error.");
                                                    }
                                                },"html");
                                            }
                                    },
                                    cancel: {
                                            label: "Cancel",
                                            className: "btn-cancel",
                                            callback: function() {
                                                    //console.log("Primary button");
                                            }
                                    }
                            }
                        });
                    } else if (cell_type == 'category') {
                        var parent_id = $(obj[0]).attr('data-id');
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'categories_id' : cell_identify, 'parent_id' : parent_id }, function(data, status){
                            if (status == "success") {
                                $( ".cat_main_box" ).html(data);
                                $('.edit_cat').popUp({
                                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                                });
                                $('.delete_cat').popUp({
                                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                                });
                                $('.collapse_span').click(function(){
                                    $(this).toggleClass('c_up');
                                    $(this).parent().parent().next().slideToggle();
                                });
                                resetStatement();
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                //}
            
                
                
            } else {
                var obj = $(elem).parents("li.li_block");
                if (obj[0] != undefined) {
                    var cell_identify = $(ui.item[0]).find('.cell_identify').val();
                    var cell_type = $(ui.item[0]).find('.cell_type').val();
                    if (cell_type == 'product') {
                        var brand_id = $(obj[0]).children('span.brand_li').children('span.brand_text').attr('id');
                        //console.log(brand_id);
                        //console.log(cell_identify);
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : 'brand', 'products_id' : cell_identify, 'brand_id' : brand_id }, function(data, status){
                            if (status == "success") {
                                resetStatement();
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                } else {
                    var obj = $(elem).parents("span.cat_li");
                    if (obj[0] != undefined) {
                        
                                        
                var cell_identify = $(ui.item[0]).find('.cell_identify').val();
                var cell_type = $(ui.item[0]).find('.cell_type').val();
                
                    if (cell_type == 'product') {
                        var title = $(obj[0]).children('span').text();
                        var categories_id = $(obj[0]).children('span').attr('id');
                        bootbox.dialog({
                            message: '<div class=""><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div></div>',

                            title: "{$smarty.const.TEXT_MOVE_OR_COPY_PRODUCT_TO} " + title,
                            buttons: {
                                    success: {
                                            label: "{$smarty.const.TEXT_YES}",
                                            className: "btn btn-primary",
                                            callback: function() {
                                                var copy_to = $('input[name="copy_to"]:checked').val();
                                                var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                                var current_category_id = $('#global_id').val();
                                                $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'products_id' : cell_identify, 'categories_id' : categories_id, 'copy_to' : copy_to, 'copy_attributes' : copy_attributes, 'current_category_id' : current_category_id }, function(data, status){
                                                    if (status == "success") {
                                                        resetStatement();
                                                    } else {
                                                        alert("Request error.");
                                                    }
                                                },"html");
                                            }
                                    },
                                    cancel: {
                                            label: "Cancel",
                                            className: "btn-cancel",
                                            callback: function() {
                                                    //console.log("Primary button");
                                            }
                                    }
                            }
                        });
                    } else if (cell_type == 'category') {
                        var parent_id = $(obj[0]).children('span').attr('id');
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'categories_id' : cell_identify, 'parent_id' : parent_id }, function(data, status){
                            if (status == "success") {
                                $( ".cat_main_box" ).html(data);
                                $('.edit_cat').popUp({
                                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                                });
                                $('.delete_cat').popUp({
                                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                                });
                                $('.collapse_span').click(function(){
                                    $(this).toggleClass('c_up');
                                    $(this).parent().parent().next().slideToggle();
                                });
                                resetStatement();
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                //}
                        
                        
                    }
                }
            }
            return true;
          },
        update: function( event, ui ) {
            var disabled = $(ui.item[0]).find('div.handle_cat_list').hasClass('state-disabled');
            if (disabled == true) {
                bootbox.alert("Sorting disabled for search mode!");
                return false;
            }
            var listing_type = $('#listing_type').val();
            $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}?listing_type=" + listing_type + "&category_id=" + $('#global_id').val() + "&brand_id=" + $('#brand_id').val(), $(this).sortable('serialize'), function(data, status){
                if (status == "success") {
                    if (listing_type == 'category') {
                        $( ".cat_main_box" ).html(data);
                        $('.edit_cat').popUp({
                            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                        });
                        $('.delete_cat').popUp({
                            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                        });
                        $('.collapse_span').click(function(){
                            $(this).toggleClass('c_up');
                            $(this).parent().parent().next().slideToggle();
                        });
                    }
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
      handle: ".handle"
    }).disableSelection();
    
    $( ".brand_box ul" ).sortable({
        axis: 'y',
        update: function( event, ui ) {
            //console.log(event);
            //console.log(ui.item);
            //var data = $(this).sortable('serialize');
            //console.log(data);
            $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}", $(this).sortable('serialize'), function(data, status){
                if (status == "success") {
                    //resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        handle: ".handle"
    }).disableSelection();
    
    $('.edit_cat').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.delete_cat').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.edit_brand').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>Editing brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.delete_brand').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });



    var color = '#ff0000';

    var chighlight = function(obj, reg){
            if (reg.length == 0) return;
            $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
            return;
    }

    var cunhighlight = function(obj){
            $(obj).html($(obj).text());
    }

    var csearch = null;
    var cstarted = false;
    $('#categorysearch').on('focus keyup', function(e){

            if ($(this).val().length == 0){
                    //restart
                    cstarted = false;
            }

            if (!cstarted && e.type == 'focus'){
                $('.expand_all').click();
                    // $('.cat_main_box').find('ol').addClass('open').children('ul').show();
                    // $('#nav').find('.arrow').removeClass('icon-plus').addClass('icon-minus');
            }

            cstarted = true;
            var str = $(this).val();
            csearch = new RegExp(str, 'i');


            $.each($('.cat_main_box').find('span.cat_text'), function(i, e){
                    cunhighlight(e);
                    if (!csearch.test($(e).text())){
                            $(e).parent().parent().parent().hide();
                    } else {
                            $(e).parents('ol li').show();
                            //$(e).next().show();
                            chighlight(e, str);
                    }
            });

            /*$.each($('.cat_main_box').find('span.cat_text').parent(), function(i, e){
                    if ($(e).is(':visible')){
                            $(e).find('ol, li').show();
                    }
            });	*/


            /*$.each($('.cat_main_box').find('span.cat_text'), function(i, e){
                    if ($(e).next().find('li:visible').size() == 0){
                            $(e).parent().hide();
                    } else {
                            $(e).parent().show();
                    }

            });	*/


    });
    
    var bsearch = null;
    var bstarted = false;
    $('#brandsearch').on('focus keyup', function(e){
        if ($(this).val().length == 0){
                bstarted = false;
        }
        if (!bstarted && e.type == 'focus'){
            // $('.cat_main_box').find('ol').addClass('open').children('ul').show();
            // $('#nav').find('.arrow').removeClass('icon-plus').addClass('icon-minus');
        }
        
        bstarted = true;
        var str = $(this).val();
        bsearch = new RegExp(str, 'i');

        $.each($('.brand_box').find('span.brand_text'), function(i, e){
                cunhighlight(e);
                if (!bsearch.test($(e).text())){
                    //console.log($(e));
                        $(e).parent().parent().hide();
                } else {
                        $(e).parents('ul li').show();
                        //$(e).next().show();
                        chighlight(e, str);
                }
        });
            

    });

   $('.js_create_new_product, .js_create_new_category').on('click',function() {
     if ( $("#listing_type").val()=='category' ) {
       var href = $(this).attr('href');
       var check_url = href.match(/(\?|&)category_id=\d+/);
       if ( check_url ) {
         href = href.replace(check_url[0],check_url[1]+'category_id='+$('#global_id').val());
       }else{
         href += ((href.indexOf('?')===-1)?'?':'&')+'category_id='+$('#global_id').val();
       }
       $(this).attr('href',href);
     }
   });

  var $platforms = $('.js_platform_checkboxes');
  var check_platform_checkboxes = function(){
    var checked_all = true;
    $platforms.not('[value=""]').each(function () {
      if (!this.checked) checked_all = false;
    });
    $platforms.filter('[value=""]').each(function() {
      this.checked = checked_all
    });
  };
  check_platform_checkboxes();
  $platforms.on('click',function(){
    var self = this;
    if (this.value=='') {
      $platforms.each(function(){
        this.checked = self.checked;
      });
    }else{
      var checked_all = this.checked;
      if ( checked_all ) {
        $platforms.not('[value=""]').each(function () {
          if (!this.checked) checked_all = false;
        });
      }
      $platforms.filter('[value=""]').each(function() {
        this.checked = checked_all
      });
    }
  });

});
</script>                                               

                                <!--===Actions ===-->
        <script language="JavaScript" src="{$app->request->baseUrl}/includes/javascript/ajax_load.js"></script>
        <script language="JavaScript" src="{$app->request->baseUrl}/includes/javascript/utils.js"></script>
        <div class="row right_column" id="catalog_management" style="display: none;">
            <div class="widget box">
                <div class="widget-content" id="catalog_management_data">
                    <div class="scroll_col"></div>
                </div>
            </div>
        </div>
</div>
</div>
</div>
				<!--===Actions ===-->
				<!-- /Page Content -->