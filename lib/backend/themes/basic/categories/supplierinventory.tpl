{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow" style="margin-bottom: 10px;" id="suppliers{str_replace(['{', '}'], ['-', '-'], $uprid)}-{$sInfo->suppliers_id}">
    {Html::hiddenInput('suppliers_id_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_id)}
    <div class="widget-header widget-header-grey after">
        <h4>{$sInfo->suppliers_name}</h4>
        <a href="javascript:void(0)" onclick="deleteSupplierInv({$sInfo->suppliers_id}, '{str_replace(['{', '}'], ['-', '-'], $uprid)}')"><div class="del-head-box">{$smarty.const.TEXT_DELETE_THIS_PRODUCT}</div></a>
    </div>
    <div class="widget-content after">
        <div class="tab-sup tab-sup01">
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_MODEL}</label>
                    {Html::textInput('suppliers_model_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_model, ['class'=>'form-control'])}
                </div>
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_QUANTITY}</label>
                    {Html::textInput('suppliers_quantity_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_quantity, ['class'=>'form-control'])}
                </div> 
            </div> 
            <div class="tab-sup-line after">
                <div>
                    <label style="min-height: 35px;">{$smarty.const.TEXT_SUPPLIERS_PRICE}</label>
                    {Html::textInput('suppliers_price_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_price, ['onKeyUp'=>'updateSupplierPricesInv('|cat:$sInfo->suppliers_id|cat:', "'|cat:$uprid|cat:'")', 'class'=>'form-control'])}
                </div> 
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIER_DISCOUNT}</label>
                    {Html::textInput('supplier_discount_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->supplier_discount, ['onKeyUp'=>'updateSupplierPricesInv('|cat:$sInfo->suppliers_id|cat:', "'|cat:$uprid|cat:'")', 'class'=>'form-control'])}
                </div>
            </div> 
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SURCHARGE}</label>
                    {Html::textInput('suppliers_surcharge_amount_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_surcharge_amount, ['onKeyUp'=>'updateSupplierPricesInv('|cat:$sInfo->suppliers_id|cat:', "'|cat:$uprid|cat:'")', 'class'=>'form-control'])}
                </div>
                <div>
                    <label>{$smarty.const.TEXT_MARGIN}</label>
                    {Html::textInput('suppliers_margin_percentage_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_margin_percentage, ['onKeyUp'=>'updateSupplierPricesInv('|cat:$sInfo->suppliers_id|cat:', "'|cat:$uprid|cat:'")', 'class'=>'form-control'])}
                </div>
            </div> 
        </div>
        <div class="tab-sup tab-sup02">
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_SUPPLIER_PRICE}</span><br>
                    <span id="supplier_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                </p>
            </div>
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_OUR_PRICE}</span><br>
                    <span id="supplier_cost_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                </p>
            </div>
        </div>
        <div class="tab-sup tab-sup03 calc_div_width_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}">
            <div class="tab-sup03-line01 after">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_CURRENT}</span>
                    <div>
                        <i>{$smarty.const.TEXT_NET}</i>
                        <span id="our_net_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                    </div>
                    <div>
                        <i>{$smarty.const.TEXT_GROSS}</i>
                        <span id="our_gross_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                    </div>
                </div>
            </div>
            <div class="tab-sup03-line02">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_CURRENT_PROFIT}</span>
                    <span id="our_profit_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}" class="our-prof-grey"></span>
                </div>
            </div>
        </div>
        <div class="tab-sup tab-sup03 tab-sup003" id="calc_div_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}">
            <div class="tab-sup03-line01 after tab-sup03-line001">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_CALCULATED}</span>
                    <div>
                        <i>{$smarty.const.TEXT_NET}</i>
                        <span id="calc_net_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                    </div>
                    <div>
                        <i>{$smarty.const.TEXT_GROSS}</i>
                        <span id="calc_gross_price_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}"></span>
                    </div>
                </div>
            </div>
            <div class="tab-sup03-line02 tab-sup03-line001">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_PROFIT}</span>
                    <span id="calc_profit_{str_replace(['{', '}'], ['-', '-'], $uprid)}_{$sInfo->suppliers_id}" class="our-prof-grey"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
  updateSupplierPricesInv({$sInfo->suppliers_id}, '{$uprid}');
});
</script>
