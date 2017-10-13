
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<form action="{Yii::$app->urlManager->createUrl('virtual-gift-card/submit')}" method="post" enctype="multipart/form-data" name="product_edit" id="save_product_form" onSubmit="return saveProduct();">
<div class="row">
    <div class="col-md-12">
        <div class="tabbable tabbable-custom tabbable-ep">
            {if count($languages) > 1}
            <ul class="nav nav-tabs">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$lItem['id']}" class="flag-span" data-toggle="tab">{$lItem['image']}<span>{$lItem['name']}</span></a></li>
                            {/foreach}
            </ul>
            {/if}
            <div class="tab-content tab-content-virtual {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $languages  as $lKey => $lItem}
                    <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$lItem['id']}" style="padding-bottom: 10px;">
                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                            <tr>
                                <td class="bigText" width="400">{$smarty.const.TEXT_PRODUCTS_NAME}</td>
                                <td class="bigText">{tep_draw_input_field('products_name['|cat:$lItem['id']|cat:']', \common\helpers\Product::get_products_name($products_id, $lItem['id']), 'class="form-control"')}</td>
                            </tr>
                        </table>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="cache_control_title">{$smarty.const.TEXT_PRICES}</span>
                </h4>
            </div>
            <div class="widget-content fields_style">
                <div class="tabbable tabbable-custom tabbable-ep">
                    <ul class="nav nav-tabs">
                        {foreach $currenciesTabs as $currId => $curr}
                        <li{if $defaultCurrenciy == $currId} class="active"{/if}><a href="#markettab_{$currId}" data-toggle="tab"><span>{$curr['title']}</span></a></li>
                        {/foreach}
                    </ul>
                    <div class="tab-content tab-content1 tab-content-vgc-pr">
                        {foreach $currenciesTabs as $currId => $curr}
                            <div class="tab-pane{if $defaultCurrenciy == $currId} active{/if}" id="markettab_{$currId}">
                                {$curr['content']}
                            </div>
                        {/foreach}
                    </div>
                </div>       
            </div>
        </div>
    </div>
</div>
<div class="btn-bar btn-bar-edp-page after" style="padding: 0;">
    <div class="btn-left">
        <a href="javascript:void(0)" onclick="return backStatement()" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a>
    </div>
    <div class="btn-right">
        <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
    </div>
</div>
</form>
<script>
function backStatement() {
    window.history.back();
    return false;
}
function add_row_price(obj_id, new_obj_html) {
  var div = document.createElement('div');
  div.innerHTML = new_obj_html;
  document.getElementById(obj_id).appendChild(div);
}
function delete_row_price($obj) {
  $obj.parentNode.parentNode.removeChild($obj.parentNode);
}
function saveProduct() {
    $.post("{Yii::$app->urlManager->createUrl('virtual-gift-card/submit')}", $('#save_product_form').serialize(), function(data, status){
        if (status == "success") {
            $('#save_product_form').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
</script>