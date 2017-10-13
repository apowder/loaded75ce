{use class="\yii\helpers\Html"}{use class="\yii\helpers\ArrayHelper"}
<form name="new_setting" id="customer_coupon" action="{$app->urlManager->createUrl('google_analytics/new')}" method="post">
<div class="popup-heading popup-heading-coup">{$title}</div>
<div class="popup-content">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font table-send-coup">
        <tr>
            <td align="right"><label>{$smarty.const.TABLE_HEADING_PLATFORM}</label></td>
            <td>{Html::dropDownList('platform_id', $first_platform_id, ArrayHelper::map($platforms, 'id', 'text'), ['class' => 'form-control', 'prompt' =>{$smarty.const.GOOGLE_RESERVED_KEYS}])}</td>
            <td width="40%"></td>
        </tr>    
        <tr>
            <td align="right"><label>{$smarty.const.GOOGLE_SETTING_NAME}</label></td>
            <td><input type="text" class="form-control" name="setting_type"/></td>
            <td></td>
        </tr>
        <tr>
            <td align="right"><label>{$smarty.const.GOOGLE_SETTING_PRIORITY}</label></td>
            <td><input type="text" class="form-control" name="setting_priority"/></td>
            <td></td>
        </tr>
        <tr>
            <td align="right"><label>{$smarty.const.GOOGLE_SETTING_CODE}</label></td>
            <td colspan="2"><textarea class="form-control" name="setting_code"></textarea></td>
        </tr>
    </table>

</div>
<div class="noti-btn">
    <div class="btn-left"><a href="{$app->urlManager->createUrl('google_analytics/index')}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><input type="submit" class="btn btn-primary" value="{$smarty.const.IMAGE_SAVE}"></div>
</div>
</form>
