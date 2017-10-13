<div class="specialTable">
<form name="save_item_form" action="" method="post" id="save_item_form" onsubmit="return saveItem();">
    <input type="hidden" name="item_id" value="36">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr><td class="label_name">{$smarty.const.TEXT_SPECIALS_PRODUCT}</td><td class="label_value_in">AEG Single Oven 60cm B21004B <small>(£600.00)</small>       </td>        </tr>
				<tr><td class="label_name">{$smarty.const.TABLE_HEADING_STATUS}</td><td class="label_value_in"> <input type="checkbox" value="2" name="status" class="check_on_off" checked="checked"></td>        </tr> <tr><td class="label_name">{$smarty.const.TEXT_SPECIALS_SPECIAL_PRICE}</td><td class="label_value_in"><input type="text" class="form-control" name="specials_price" value="600.0000"></td></tr>
				<tr><td class="label_name">{$smarty.const.TEXT_SPECIALS_EXPIRES_DATE}</td><td class="label_value_in"><input type="text" name="expires_date" value="" class="datepicker hasDatepicker form-control" id="dp1456934260684"></td></tr>
				<tr><td class="label_name"></td><td class="notest">{$smarty.const.TEXT_SPECIALS_PRICE_TIP}</td></tr>
    </table>
    <div class="btn-bar">
        <div class="btn-left"><a class="btn btn-cancel" href="{Yii::$app->urlManager->createUrl('specials')}">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><input class="btn btn-primary" type="submit" value="{$smarty.const.IMAGE_SAVE}"></div>
    </div>

    <input type="hidden" name="products_price" value="600.0000">            </form>
</div>
<script>
$(document).ready(function(){
$(".check_on_off").bootstrapSwitch(
        {
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
})

</script>