<script>
  function reloadPdetails(pid){
	$.get('orders/get-platform-details', {
		'platform_id':pid,
	}, function (data, status){
		if (status='success'){
			$('.currency_languages').html(data);
		}
	}, 'html');
  }

  // -->
</script>

	{tep_draw_form('create_order', 'orders/createorderprocess', '')}
	{tep_draw_hidden_field('customers_id', $cInfo->customers_id)}
  <div class="box-show-customer">
       {$smarty.const.ENTRY_CUSTOMER}<b> {$cInfo->customers_firstname} {$cInfo->customers_lastname} {$cInfo->customers_email_address}
	   {tep_draw_hidden_field('customers_lastname', $cInfo->customers_lastname)}
	   {tep_draw_hidden_field('customers_firstname', $cInfo->customers_firstname)}
	   {tep_draw_hidden_field('customers_email_address', $cInfo->customers_email_address)}</b>
  </div>
  
		{$app->controller->renderAjax('address_details', ['error'=> $error, 'errors'=>$errors, 'saID' => $saID, 'aID' => $aID, 'addresses' => $addresses, 'cInfo' => $cInfo, 'entry'=> $entry, 'js_arrs'=>$js_arrs, 'entry_state' => $entry_state, 'csa'=>$csa])}
		
			  {if $smarty.const.ACCOUNT_COMPANY == 'true' || $smarty.const.ACCOUNT_COMPANY_VAT_ID == 'true'}
				<div class="widget box box-no-shadow">
					  <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_COMPANY}</h4></div>
					  <div class="widget-content">              
						<div class="w-line-row w-line-row-2 w-line-row-2-big">	
							{if $smarty.const.ACCOUNT_COMPANY == 'true'}
								<div>
								  <div class="wl-td">
									  <label>{$smarty.const.ENTRY_COMPANY}</label>
									  {tep_draw_input_field('customers_company', $cInfo->customers_company, 'maxlength="32" class="form-control"')}
								  </div> 
							  </div> 
							{/if}
							{if $smarty.const.ACCOUNT_COMPANY_VAT_ID == 'true'}
								<div>
									<div class="wl-td">
										<label>{$smarty.const.ENTRY_BUSINESS}</label>
										{tep_draw_input_field('customers_company_vat', $cInfo->customers_company_vat, 'maxlength="32" class="form-control"')}
									</div>                              
								</div>	
							{/if}
						</div>                   
					</div>
				</div>						  
			  {/if}    
			{if $entry->platforms}
			<div style="clear: both"></div>			  
			<div class="widget box box-no-shadow">
					  <div class="widget-header widget-header-company"><h4>{$smarty.const.TEXT_MAIN_DETAILS}</h4></div>
					  <div class="widget-content">              
						<div class="w-line-row w-line-row-2 w-line-row-2-big">	
							  <div style="width:30%">
								  <div class="wl-td">
									  <label>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}:<span class="fieldRequired">*</span></label>
									  {tep_draw_pull_down_menu('platform_id', $entry->platforms, $entry->default_platform, 'maxlength="32" class="form-control" onchange="reloadPdetails(this.value)"')}
								  </div> 
							  </div>
								<div class="currency_languages">
								{$app->controller->renderAjax('currency_language', ['entry'=>$entry])}
								</div>
						</div>                   
					</div>
			</div>
			{/if}
	{tep_draw_hidden_field('convert', $app->controller->view)}
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main">
				<button class="btn btn-back" onclick="return resetStatement()">{$smarty.const.IMAGE_BACK}</button>
			</td>
            <td class="main" align="right">
				<input type="submit" value="{$smarty.const.IMAGE_BUTTON_CONFIRM}" class="btn btn-confirm" onclick="return createOrderProcess();">
			</td>
          </tr>
        </table></td>
      </tr>
    </table>
</form>