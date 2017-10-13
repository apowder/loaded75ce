{use class="yii\helpers\Html"}
<div class="popupCategory popupSup">

    <form id="add_supplier_form" name="add_supplier" onSubmit="return addSupplier();">
    {Html::hiddenInput('uprid', $uprid)}
    <table cellspacing="0" cellpadding="0" width="100%" class="sup_pop_tabl">
        <tr>
            <td class="label_name">{$smarty.const.TEXT_SELECT_SUPPLIER}</td>
            <td class="label_value label_value_inp">{Html::dropDownList('suppliers_id', '', $app->controller->view->suppliers, ['class'=>'form-control', 'id'=>'suppliers_id', 'onchange'=>'changeSupplier(this);'])}</td>
            <td></td>
        </tr>
        <tr>
            <td class="label_name">{$smarty.const.TEXT_SUPPLIERS_NAME}</td>
            <td class="label_value label_value_inp">{Html::textInput('suppliers_name', '', ['class'=>'form-control', 'required'=>true])}</td>
            <td></td>
        </tr>
        <tr>
            <td class="label_name">{$smarty.const.TEXT_SUPPLIERS_SURCHARGE_AMOUNT}</td>
            <td class="label_value label_value_inp">{Html::textInput('suppliers_surcharge_amount', '', ['class'=>'form-control', 'required'=>true])}</td>
            <td></td>
        </tr>
        <tr>
            <td class="label_name">{$smarty.const.TEXT_SUPPLIERS_MARGIN_PERCENTAGE}</td>
            <td class="label_value label_value_inp">{Html::textInput('suppliers_margin_percentage', '', ['class'=>'form-control', 'required'=>true])}</td>
            <td class="label_value">
                <span id="add_select_supplier"><button class="btn btn-primary">{$smarty.const.IMAGE_ADD_SELECT}</button></span>
                <span id="select_supplier" style="display:none;"><a href="javascript:void(0)" class="btn btn-primary" onclick="return selectSupplier()">{$smarty.const.IMAGE_SELECT}</a></span>
            </td>
        </tr>
    </table>
    {Html::hiddenInput('add', 1)}
    </form>

    
</div>
    <div class="noti-btn">
        <div>
            <a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return cancelStatement()">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div></div>
    </div>

<script type="text/javascript">
{$app->controller->view->suppliers_js}
function changeSupplier(theSelect) {
  if (theSelect.value > 0) {
    document.add_supplier.suppliers_name.value = theSelect.options[theSelect.selectedIndex].innerHTML;
    document.add_supplier.suppliers_name.readOnly = true;
    document.add_supplier.suppliers_surcharge_amount.value = arSurcharge[theSelect.value];
    document.add_supplier.suppliers_surcharge_amount.readOnly = true;
    document.add_supplier.suppliers_margin_percentage.value = arMargin[theSelect.value];
    document.add_supplier.suppliers_margin_percentage.readOnly = true;
    $('#add_select_supplier').hide();
    $('#select_supplier').show();
  } else {
    document.add_supplier.suppliers_name.value = '';
    document.add_supplier.suppliers_name.readOnly = false;
    document.add_supplier.suppliers_surcharge_amount.value = '';
    document.add_supplier.suppliers_surcharge_amount.readOnly = false;
    document.add_supplier.suppliers_margin_percentage.value = '';
    document.add_supplier.suppliers_margin_percentage.readOnly = false;
    $('#add_select_supplier').show();
    $('#select_supplier').hide();
  }
}

function selectSupplier() {
    var suppliers_id = $('#suppliers_id').val();
    if ( suppliers_id > 0 ) {
        if ($('#suppliers{str_replace(['{', '}'], ['-', '-'], $uprid)}-' + suppliers_id).length) {
            alert("This supplier is already selected.");
            return false;
        }
        $.post("{Yii::$app->urlManager->createUrl('categories/supplier-add')}", { 'suppliers_id' : suppliers_id, 'uprid' : '{$uprid}' }, function(data, status) {
            if (status == "success") {
                $('#suppliers-placeholder{str_replace(['{', '}'], ['-', '-'], $uprid)}').prepend(data);
                cancelStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function addSupplier() {
    $.post("{Yii::$app->urlManager->createUrl('categories/supplier-add')}", $('#add_supplier_form').serialize(), function(data, status) {
        if (status == "success") {
                $('#suppliers-placeholder{str_replace(['{', '}'], ['-', '-'], $uprid)}').prepend(data);
                cancelStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function cancelStatement() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
    return false;
}
</script>
