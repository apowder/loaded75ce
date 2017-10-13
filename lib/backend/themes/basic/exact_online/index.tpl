<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->

{if $message_stack_output}
  {$message_stack_output}
{/if}

<div class="row">
  <div class="col-md-12">
    <div class="widget box">
      <div class="widget-header">
        <h4><i class="icon-reorder"></i><span id="easypopulate_management_title">{$smarty.const.HEADING_TITLE}</span></h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span id="easypopulate_management_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
      </div>
      <div class="widget-content fields_style">
        <div class="scroll-table-workaround">

{tep_draw_form('exact_online', 'exact_online/update')}
  <div class="main" style="width:900px">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_BASE_URL}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_BASE_URL', EXACT_BASE_URL, 'readonly')}</td>
      </tr>
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_CLIENT_ID}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_CLIENT_ID', EXACT_CLIENT_ID, 'readonly')}</td>
      </tr>
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_CLIENT_SECRET}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_CLIENT_SECRET', EXACT_CLIENT_SECRET, 'readonly')}</td>
      </tr>
<!-- {*
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_RETURN_URL}</td>
        <td class="smallText">{str_replace(SID, '', tep_href_link('exact_online/oauth'))}</td>
      </tr>
*} -->
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_CURRENT_DIVISION}</td>
        <td class="smallText">{tep_draw_pull_down_menu('EXACT_CURRENT_DIVISION', $divisions_array, EXACT_CURRENT_DIVISION)}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_DESCRIPTION_FIELD}</td>
        <td class="smallText">{tep_draw_pull_down_menu('EXACT_DESCRIPTION_FIELD', $description_fields_array, EXACT_DESCRIPTION_FIELD)}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_ORDERNUMBER_SHIFT}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_ORDERNUMBER_SHIFT', EXACT_ORDERNUMBER_SHIFT)}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_0_VAT_CODE}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_0_VAT_CODE', EXACT_0_VAT_CODE)}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_ORDER_STATUSES_SYNCED}</td>
        <td class="smallText">
        {if {$order_statuses_array|@count} > 0}
          {foreach $order_statuses_array as $key => $status}
          <label><input type="checkbox" name="EXACT_ORDER_STATUSES_SYNCED[]" value="{$status['id']}" {if {in_array($status['id'], explode(',', EXACT_ORDER_STATUSES_SYNCED))}} checked{/if}> {$status['text']}</label><br>
          {/foreach}
        {/if}
      </tr>
    {if {$platforms_list_array|@count} > 0}
      {foreach $platforms_list_array as $platform_id => $platform_title}
      <tr>
        <td colspan="2"><h4>{$platform_title}</h4><table border="0">
          <tr>
            <td width="45%" valign="top"><table border="0">
            {if {$installed_payment_modules[$platform_id]|@count} > 0}
              <tr>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_PAYMENT_MODULE}</b></td>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_MAP_CODE}</b></td>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_MAP_DESCRIPTION}</b></td>
              </tr>
              <tr>
                <td class="smallText">{$smarty.const.TEXT_EXACT_DEFAULT}</td>
                <td class="smallText">{tep_draw_input_field('payment_map['|cat:$platform_id|cat:'][default][code]', $payment_map[$platform_id]['default']['code'], 'size="5"')}</td>
                <td class="smallText">{tep_draw_input_field('payment_map['|cat:$platform_id|cat:'][default][description]', $payment_map[$platform_id]['default']['description'])}</td>
              </tr>
              {foreach $installed_payment_modules[$platform_id] as $key => $title}
              <tr>
                <td class="smallText">{$title}</td>
                <td class="smallText">{tep_draw_input_field('payment_map['|cat:$platform_id|cat:']['|cat:$key|cat:'][code]', $payment_map[$platform_id][$key]['code'], 'size="5"')}</td>
                <td class="smallText">{tep_draw_input_field('payment_map['|cat:$platform_id|cat:']['|cat:$key|cat:'][description]', $payment_map[$platform_id][$key]['description'])}</td>
              </tr>
              {/foreach}
            {/if}
            </table></td>
            <td width="55%" valign="top"><table border="0">
            {if {$installed_shipping_modules[$platform_id]|@count} > 0}
              <tr>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_SHIPPING_MODULE}</b></td>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_MAP_CODE}</b></td>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_MAP_DESCRIPTION}</b></td>
                <td class="smallText"><b>{$smarty.const.TEXT_EXACT_MAP_PRODUCT}</b></td>
              </tr>
              <tr>
                <td class="smallText">{$smarty.const.TEXT_EXACT_DEFAULT}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:'][default][code]', $shipping_map[$platform_id]['default']['code'], 'size="5"')}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:'][default][description]', $shipping_map[$platform_id]['default']['description'])}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:'][default][product]', $shipping_map[$platform_id]['default']['product'], 'size="7"')}</td>
              </tr>
              {foreach $installed_shipping_modules[$platform_id] as $key => $title}
              <tr>
                <td class="smallText">{$title}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:']['|cat:$key|cat:'][code]', $shipping_map[$platform_id][$key]['code'], 'size="5"')}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:']['|cat:$key|cat:'][description]', $shipping_map[$platform_id][$key]['description'])}</td>
                <td class="smallText">{tep_draw_input_field('shipping_map['|cat:$platform_id|cat:']['|cat:$key|cat:'][product]', $shipping_map[$platform_id][$key]['product'], 'size="7"')}</td>
              </tr>
              {/foreach}
            {/if}
            </table></td>
          </tr>
        </table></td>
      </tr>
      {/foreach}
    {/if}
      <tr>
        <td colspan="2">{tep_draw_separator('pixel_trans.gif', '1', '10')}</td>
      </tr>
      <tr>
        <td width="25%" class="smallText"><b>{$smarty.const.TEXT_CONNECTOR_STATUS}</b></td>
        <td class="smallText"><label {if (EXACT_CONNECTOR_STATUS == 'True')}style="font-weight:bold;color:#0C0"{/if}>{tep_draw_radio_field('EXACT_CONNECTOR_STATUS', 'True', EXACT_CONNECTOR_STATUS == 'True', '', 'id="s1"')}&nbsp;{$smarty.const.TEXT_EXACT_ON}</label>&nbsp;&nbsp;<label {if (EXACT_CONNECTOR_STATUS == 'False')}style="font-weight:bold;color:#C00"{/if}>{tep_draw_radio_field('EXACT_CONNECTOR_STATUS', 'False', EXACT_CONNECTOR_STATUS == 'False', '', 'id="s0"')}&nbsp;{$smarty.const.TEXT_EXACT_OFF}</label></td>
      </tr>
      
  {if count($exact_cron_array) > 0}
    {foreach $exact_cron_array as $exact_cron}
      <tr>
        <td class="smallText">{if (defined($exact_cron['exact_crons_name']))} {constant($exact_cron['exact_crons_name'])} {else} {sprintf($smarty.const.TEXT_RUN_CRONS_NAME, $exact_cron['exact_crons_name'])} {/if}</td>
        <td class="smallText">{tep_draw_pull_down_menu('schedule_every_minutes['|cat:$exact_cron['exact_crons_id']|cat:']', $intervals_array, $exact_cron['schedule_every_minutes'], 'style="width:150px"')}
          &nbsp;-&nbsp;<a href="{tep_href_link('exact_online/run', 'feed='|cat:$exact_cron['exact_crons_function'])}">{$smarty.const.TEXT_RUN_IMMEDIATELY}</a></td>
      </tr>
      <tr>
        <td class="smallText">&nbsp;</td>
        <td class="smallText">{$smarty.const.TEXT_SCHEDULE_LAST_STARTED} {if ($exact_cron['schedule_last_started'] == 0)} {$smarty.const.TEXT_TIME_NEVER} {else} {\common\helpers\Date::datetime_short($exact_cron['schedule_last_started'])} {/if}</td>
      </tr>
    {/foreach}
  {/if}
{*
      <tr>
        <td colspan="2"><a href="{tep_href_link('exact_online/products')}">{$smarty.const.TEXT_RUN_PRODUCTS}</a></td>
      </tr>
      <tr>
        <td colspan="2"><a href="{tep_href_link('exact_online/stock')}">{$smarty.const.TEXT_RUN_STOCK}</a></td>
      </tr>
      <tr>
        <td colspan="2"><a href="{tep_href_link('exact_online/orders')}">{$smarty.const.TEXT_RUN_ORDERS}</a></td>
      </tr>
*}
      <tr>
        <td colspan="2">{tep_draw_separator('pixel_trans.gif', '1', '10')}</td>
      </tr>
    </table>
    <div class="btn-bar edit-btn-bar">
      <div class="btn-left"><a href="{tep_href_link('exact_online/oauth')}" class="btn btn-primary">{$smarty.const.TEXT_EXACT_PROCESS_AUTHORIZATION}</a></div>
      <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_UPDATE}</button></div>
    </div>
  </div>
</form>

        </div>
      </div>
    </div>
  </div>
</div>
