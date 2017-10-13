{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow" style="margin-bottom: 10px;" id="suppliers-{$sInfo->suppliers_id}">
    {Html::hiddenInput('suppliers_id['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_id)}
    <div class="widget-header widget-header-grey after">
        <h4>{$sInfo->suppliers_name}</h4>
        <a href="javascript:void(0)" onclick="deleteSupplier({$sInfo->suppliers_id})"><div class="del-head-box">{$smarty.const.TEXT_DELETE_THIS_PRODUCT}</div></a>
    </div>
    <div class="widget-content after">
        <div class="tab-sup tab-sup01">
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_MODEL}</label>
                    {Html::textInput('suppliers_model['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_model, ['class'=>'form-control'])}
                </div>
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_QUANTITY}</label>
                    {Html::textInput('suppliers_quantity['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_quantity, ['class'=>'form-control'])}
                </div> 
            </div> 
            <div class="tab-sup-line after">
                <div>
                    <label style="min-height: 35px;">{$smarty.const.TEXT_SUPPLIERS_PRICE}</label>
                    {Html::textInput('suppliers_price['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_price, ['onKeyUp'=>'updateSupplierPrices('|cat:$sInfo->suppliers_id|cat:')', 'class'=>'form-control'])}
                </div> 
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIER_DISCOUNT}</label>
                    {Html::textInput('supplier_discount['|cat:$sInfo->suppliers_id|cat:']', $sInfo->supplier_discount, ['onKeyUp'=>'updateSupplierPrices('|cat:$sInfo->suppliers_id|cat:')', 'class'=>'form-control'])}
                </div>
            </div> 
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SURCHARGE}</label>
                    {Html::textInput('suppliers_surcharge_amount['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_surcharge_amount, ['onKeyUp'=>'updateSupplierPrices('|cat:$sInfo->suppliers_id|cat:')', 'class'=>'form-control'])}
                </div>
                <div>
                    <label>{$smarty.const.TEXT_MARGIN}</label>
                    {Html::textInput('suppliers_margin_percentage['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_margin_percentage, ['onKeyUp'=>'updateSupplierPrices('|cat:$sInfo->suppliers_id|cat:')', 'class'=>'form-control'])}
                </div>
            </div> 
        </div>
        <div class="tab-sup tab-sup02">
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_SUPPLIER_PRICE}</span><br>
                    <span id="supplier_price_{$sInfo->suppliers_id}"></span>
                </p>
            </div>
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_OUR_PRICE}</span><br>
                    <span id="supplier_cost_price_{$sInfo->suppliers_id}"></span>
                </p>
            </div>
        </div>
        <div class="tab-sup tab-sup03 calc_div_width_{$sInfo->suppliers_id}">
            <div class="tab-sup03-line01 after">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_CURRENT}</span>
                    <div>
                        <i>{$smarty.const.TEXT_NET}</i>
                        <span id="our_net_price_{$sInfo->suppliers_id}"></span>
                    </div>
                    <div>
                        <i>{$smarty.const.TEXT_GROSS}</i>
                        <span id="our_gross_price_{$sInfo->suppliers_id}"></span>
                    </div>
                </div>
            </div>
            <div class="tab-sup03-line02">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_CURRENT_PROFIT}</span>
                    <span id="our_profit_{$sInfo->suppliers_id}" class="our-prof-grey"></span>
                </div>
            </div>
        </div>
        <div class="tab-sup tab-sup03 tab-sup003" id="calc_div_{$sInfo->suppliers_id}">
            <div class="tab-sup03-line01 after tab-sup03-line001">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_CALCULATED}</span>
                    <div>
                        <i>{$smarty.const.TEXT_NET}</i>
                        <span id="calc_net_price_{$sInfo->suppliers_id}"></span>
                    </div>
                    <div>
                        <i>{$smarty.const.TEXT_GROSS}</i>
                        <span id="calc_gross_price_{$sInfo->suppliers_id}"></span>
                    </div>
                </div>
            </div>
            <div class="tab-sup03-line02 tab-sup03-line001">
                <div class="ht-col">
                    <span class="slab">{$smarty.const.TEXT_OUR_PROFIT}</span>
                    <span id="calc_profit_{$sInfo->suppliers_id}" class="our-prof-grey"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
  updateSupplierPrices({$sInfo->suppliers_id});
});
</script>