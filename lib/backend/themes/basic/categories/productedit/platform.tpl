{use class="\common\classes\platform"}
{use class="yii\helpers\Html"}
<div class="filter_pad">
  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
    <thead>
    <tr>
      <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
      <th>{$smarty.const.TABLE_HEAD_PLATFORM_PRODUCT_ASSIGN}</th>
    </tr>
    </thead>
    <tbody>
    {foreach platform::getProductsAssignList() as $platform}
      <tr>
        <td>{$platform['text']}</td>
        <td>
          {Html::checkbox('platform[]', isset($app->controller->view->platform_assigned[$platform['id']]), ['value' => $platform['id'],'class'=>'check_on_off'])}
          {Html::hiddenInput('activate_parent_categories['|cat:$platform['id']|cat:']','',['class'=>'js-platform_parent_categories'])}
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>