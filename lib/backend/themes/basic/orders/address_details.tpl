<script>
{$js_arrs}
  var aID = 0
  var s_aID = 0;
  
  (function($){
	aID = $('input[name=aID]:checked').val();
	s_aID = $('input[name=saID]:checked').val();
  })(jQuery);
  
  function setAddressParam(address_id, prefix){
	$.post('orders/set-address', {
		'address_id' : address_id,
		'prefix': prefix,
		'csa': $('input[name=csa]').prop('checked'),
        'currentCart': $('input[name=currentCart]').val(),
	}, function(data, success){
		if (prefix == 's_'){
          if (typeof orderHasBeenChanged == 'function')
			orderHasBeenChanged();
		}
	}, 'json');
  }
  
  function select_address(address_id, prefix){
	eval(prefix+'aID = "' +address_id +'"');
	if (entry_country_id[address_id])
		select_country(entry_country_id[address_id], prefix, address_id);
    var f = document.create_order;
    for (i=0; i<fields.length; i++){
	  if (typeof f.elements[prefix+fields[i]] != 'undefined'){
		  if (f.elements[prefix+fields[i]].type=='text') {
			f.elements[prefix+fields[i]].value = eval(fields[i]+'[' + address_id +']');
		  }
		  if (f.elements[prefix+fields[i]].type=='select-one'){
			setselected(f.elements[prefix+fields[i]], eval(fields[i]+'[' + address_id +']'));
		  }
		  //alert(f.elements[prefix+fields[i]].name + " " + prefix+fields[i] + " " + f.elements[prefix+fields[i]] + " " + f.elements[prefix+fields[i]].type);
		  if (fields[i]=='entry_gender'){
			setchecked(f.elements[prefix+fields[i]], eval(fields[i]+'[' + address_id +']'));
		  }
	  }
    }		
    //orderHasBeenChanged();	
  }
  function setselected(item, val){
    for(j=0; j<item.length; j++){
      if (item.options[j].value==val) {
        item.selectedIndex = j;
        return;
      }
    }
  };
  function setchecked(item, val){
    for(j=0; j<item.length; j++){
      if (item[j].value==val) {
        item[j].checked = true;
        return;
      }
    }
  };
  function copy_address(prefix){
    // fields is global array
    var f = document.create_order;
    $('input[name=aID][value='+ $('input[name=saID]:checked').val() +']').prop('checked', true);
    for (i=0; i<fields.length; i++){
        if (typeof f.elements[prefix+fields[i]] != 'undefined'){
          if (f.elements[fields[i]].type=='text') {
            f.elements[fields[i]].value = f.elements[prefix+fields[i]].value ;
          }
          if (f.elements[prefix+fields[i]].type != f.elements[fields[i]].type){
            var clone = f.elements[prefix+fields[i]].cloneNode();
            clone.innerHTML = f.elements[prefix+fields[i]].innerHTML;
            clone.name = fields[i];
            console.log(clone, clone.type);
            f.elements[fields[i]].replaceWith(clone); 

          }
          if (f.elements[fields[i]].type=='select-one'){
            f.elements[fields[i]].selectedIndex = f.elements[prefix+fields[i]].selectedIndex;
          }
          if (fields[i]=='entry_gender'){
            copychecked(f.elements[fields[i]], f.elements[prefix+fields[i]]);
          }
         }
    }    
    //billingAddressNotChanged();
    //orderHasBeenChanged();	
  };
  function copychecked(item_to, item_from){
    for(j=0; j<item_from.length; j++){
      if (item_from[j].checked) {
        item_to[j].checked = true;
        return;
      }
    }
  };
  
  function select_country(country_id, prefix, address_id){
    {if $smarty.const.ACCOUNT_STATE|in_array:['required', 'required_register', 'visible', 'visible_register']}
	$.post('orders/get-states', {
		'country_id':country_id,
		'def_country_id':entry_country_id[eval(prefix+'aID')],
		'value':entry_state[eval(prefix+'aID')],
		'prefix':prefix
	}, function(data, stataus){
		if (stataus == 'success'){
			$('input[name='+prefix+'entry_state], select[name='+prefix+'entry_state]').replaceWith(data);
			if (address_id > 0)	{
				setAddressParam(address_id, prefix);
			} else {
				orderHasBeenChanged();
			}
		}
	}, 'html');
	{/if}	
  }
</script>
<div class="create-or-wrap after">
              <div class="widget box w-box-left">
                  <div class="widget-header widget-header-shipping"><h4>{$smarty.const.ENTRY_SHIPPING_ADDRESS}</h4></div>
                  <div class="widget-content">
					{*tep_draw_hidden_field('customers_id', $cInfo->customer_id)*}
					{tep_draw_hidden_field('platform_id', $cInfo->platform_id)}
                      <div class="address-wrapp after">
                                {foreach $addresses as $addresses_line}
                                    <div>
					<label>
						{tep_draw_radio_field("saID", $addresses_line['id'], {$saID eq $addresses_line['id']}, '', 'onchange="select_address(this.value, \'s_\');"')}
						{$addresses_line['text']}
					</label>
                                    </div>
                                {/foreach}
                                <div>
								 {if $customer_loaded}
									<label>
										{tep_draw_radio_field("saID", '0', false, '', 'onchange="select_address(this.value, \'s_\');"')}
										{$smarty.const.TEXT_ADD_NEW_SHIP_ADD}									
									</label>
								 {/if}
								</div>
                       </div> 
					   {if (in_array($smarty.const.ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register']))}
                                  <div class="w-line-row w-line-row-1">
                                      <div class="wl-td wl-td-r">
                                          <label>{$smarty.const.TEXT_TITLE_}:</label>
										  {tep_draw_radio_field('s_entry_gender', 'm', false, $cInfo->entry_gender, 'class="state-control"')}
										  &nbsp;&nbsp;{$smarty.const.T_MR}
										  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										  {tep_draw_radio_field('s_entry_gender', 'f', false, $cInfo->entry_gender, 'class="state-control"')}
										  &nbsp;&nbsp;{$smarty.const.T_MRS}
										  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										  {tep_draw_radio_field('s_entry_gender', 's', false, $cInfo->entry_gender, 'class="state-control"')}
										  &nbsp;&nbsp;{$smarty.const.T_MISS}
                                      </div>                          
                                  </div>									  
					   {/if}
                  </div>                  
                  <div class="widget-content widget-content-top-border">
                      <div class="w-line-row w-line-row-2">
                          <div>
                              <div class="wl-td">
								{if (in_array($smarty.const.ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register']))}
                                    <label>{$smarty.const.ENTRY_FIRST_NAME}{if in_array($smarty.const.ACCOUNT_FIRSTNAME, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									  {if $error && $errors->s_entry_firstname_error}
										{tep_draw_input_field('s_entry_firstname', $cInfo->s_entry_firstname, 'maxlength="32" class="form-control state-control"')} {sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR,$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}
									  {else}
									    {tep_draw_input_field('s_entry_firstname', $cInfo->s_entry_firstname, 'maxlength="32" class="form-control state-control"')}
									  {/if}
								{/if}
                              </div>
                          </div>
                          <div>
                              <div class="wl-td">
								{if (in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register']))}
                                    <label>{$smarty.const.ENTRY_LAST_NAME}{if in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									{if $error && $errors->s_entry_lastname_error}
										{tep_draw_input_field('s_entry_lastname', $cInfo->s_entry_lastname, 'maxlength="32" class="form-control state-control"')}  {sprintf($smarty.const.ENTRY_LAST_NAME_ERROR,$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}
									{else}
										{tep_draw_input_field('s_entry_lastname', $cInfo->s_entry_lastname, 'maxlength="32" class="form-control state-control"')}
									{/if}
								{/if}
                              </div>
                          </div>
                      </div>				  
                      <div class="w-line-row w-line-row-2">
                          <div>
							{if (in_array($smarty.const.ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_POST_CODE}{if in_array($smarty.const.ACCOUNT_POSTCODE, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->s_entry_post_code_error}
									{tep_draw_input_field('s_entry_postcode', $cInfo->s_entry_postcode, 'maxlength="8" class="form-control state-control"')} {sprintf($smarty.const.ENTRY_POST_CODE_ERROR,$smarty.const.ENTRY_POSTCODE_MIN_LENGTH)}
								  {else}
								    {tep_draw_input_field('s_entry_postcode', $cInfo->s_entry_postcode, 'maxlength="8" class="form-control state-control"', true)}
								  {/if}
                              </div>                              
							{/if}							
                          </div>
                          <div>
							{if (in_array($smarty.const.ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_STREET_ADDRESS}{if in_array($smarty.const.ACCOUNT_STREET_ADDRESS, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->s_entry_street_address_error}
									{tep_draw_input_field('s_entry_street_address', $cInfo->s_entry_street_address, 'maxlength="64" class="form-control state-control"')} {sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, $smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH)}
								  {else}
									{tep_draw_input_field('s_entry_street_address', $cInfo->s_entry_street_address, 'maxlength="64" class="form-control state-control"', true)}
								  {/if}
                              </div>                              
							{/if}
                          </div>
                      </div>
                      <div class="w-line-row w-line-row-2">
                          <div>
							{if (in_array($smarty.const.ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register']))}
								<div class="wl-td">
								<label>{$smarty.const.ENTRY_SUBURB}{if in_array($smarty.const.ACCOUNT_SUBURB, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								{if $error && $errors->s_entry_suburb_error}
									{tep_draw_input_field('s_entry_suburb', $cInfo->s_entry_suburb, 'maxlength="32" class="form-control state-control"')} {$smarty.const.ENTRY_SUBURB_ERROR}
								{else}
									{tep_draw_input_field('s_entry_suburb', $cInfo->s_entry_suburb, 'maxlength="32" class="form-control state-control"', true)}
								{/if}
								</div>
		 				  {else}
								<div class="wl-td">
									<label></label>
								</div>
							{/if}
                          </div>
                          <div>
							{if (in_array($smarty.const.ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.T_TOWN_CITY}:{if in_array($smarty.const.ACCOUNT_CITY, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->s_entry_city_error}
									{tep_draw_input_field('s_entry_city', $cInfo->s_entry_city, 'maxlength="32" class="form-control state-control"')} {sprintf($smarty.const.ENTRY_CITY_ERROR, $smarty.const.ENTRY_CITY_MIN_LENGTH)}
								  {else}
									{tep_draw_input_field('s_entry_city', $cInfo->s_entry_city, 'maxlength="32" class="form-control state-control"', true)}
								  {/if}
                              </div>
							{/if}
                          </div>
                      </div>
                      <div class="w-line-row w-line-row-2">
                          <div>
							{if (in_array($smarty.const.ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register']))}
							  <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_STATE}:{if in_array($smarty.const.ACCOUNT_STATE, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->s_entry_state_error}
										{if $entry->s_entry_state_has_zones}
											{tep_draw_pull_down_menu('s_entry_state', $entry->s_zones_array, '', ' class="form-control"')} {sprintf($smarty.const.ENTRY_STATE_ERROR, $smarty.const.ENTRY_STATE_MIN_LENGTH)}
										{else}
											{tep_draw_input_field('s_entry_state', $entry->s_entry_state, ' class="form-control state-control"')} {sprintf($smarty.const.ENTRY_STATE_ERROR, $smarty.const.ENTRY_STATE_MIN_LENGTH)}
										{/if}
								  {else}
									{if $entry->s_entry_state_has_zones}
										{tep_draw_pull_down_menu('s_entry_state', $entry->s_zones_array, $entry->s_entry_state, ' class="form-control" onchange="orderHasBeenChanged()"')}
									{else}
										{tep_draw_input_field('s_entry_state', $entry->s_entry_state, ' class="form-control state-control"')}
									{/if}
									
								  {/if}
							  </div>
						  {else}
								<div class="wl-td">
									<label></label>
								</div>
							{/if}
                          </div>
                          <div>
							{if (in_array($smarty.const.ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_COUNTRY}{if in_array($smarty.const.ACCOUNT_COUNTRY, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {tep_draw_pull_down_menu('s_entry_country_id', $entry->countries, $cInfo->s_entry_country_id, ' class="form-control" onchange="select_country(this.value, \'s_\');"')}
								  {if $error}
									{if $errors->s_entry_country_error}
										{$smarty.const.ENTRY_COUNTRY_ERROR}
									{/if}
								  {/if}
                              </div>
							{/if}
                          </div>
                      </div>
                      <div class="w-line-row w-line-row-1">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.T_REQUIRED}</span>
                      </div> 
                  </div>
                </div>
                <div class="widget box w-box-right">
                  <div class="widget-header widget-header-billing">
					<h4>{$smarty.const.TEXT_BILLING_ADDRESS}
						<span class="same-address">{tep_draw_checkbox_field('csa', '', {$csa}, false, 'class="uniform" onclick="copy_address(\'s_\');"')}
						{$smarty.const.TEXT_COPY_SHIPPING_ADDRESS}</span>
					</h4>
				  </div>
                  <script>
                        $(document).ready(function() {
						
							$('.state-control').on('focusout keyout', function(){
								orderHasBeenChanged();
							})
							
							{if $saID eq $aID && !$error }
								//$('input[name="csa"]').trigger('click');
							{/if}
                            $('input[name="csa"]').click(function(){ 
                                if($('input[name="csa"]').prop('checked')){ 
                                    $('.w-box-right .widget-content.billing-content input, .w-box-right .widget-content.billing-content select').prop('disabled', true);
                                }else{
                                    $('.w-box-right .widget-content.billing-content input, .w-box-right .widget-content.billing-content select').prop('disabled', false);
                                }
                            });    
                            if($('input[name="csa"]').prop('checked')){ 
                                $('.w-box-right .widget-content.billing-content input, .w-box-right .widget-content.billing-content select').prop('disabled', true);
                            }else{
                                $('.w-box-right .widget-content.billing-content input, .w-box-right .widget-content.billing-content select').prop('disabled', false);
                            }
							
							/*$('.save-new-address').click(function(){
								$.post('orders/createorderprocess',
								$('form[name=create_order]').serialize()+'&action=only_address'
								, function(data, status){
									if (data.error){
										$('#address_details').html(data.data);
									} else {
										orderHasBeenChanged();
									}									
								}, 'json');
								return false;
							})*/
                        });
                  </script>
                  <div class="widget-content billing-content">
                          <div class="address-wrapp after">
							{foreach $addresses as $addresses_line}
								<div>
									<label>
										{tep_draw_radio_field("aID", $addresses_line['id'], {$aID eq $addresses_line['id']}, '', 'onchange="select_address(this.value, \'\');"')} {$addresses_line['text']}
									</label>
								</div>
							{/foreach}
							<div>
							{if $customer_loaded}
								<label>{tep_draw_radio_field("aID", '0', false, '', 'onchange="select_address(this.value, \'\');"')} {$smarty.const.TEXT_ADD_NEW_BILL_ADD}</label>
							{/if}
							</div>
                          </div>  
							{if (in_array($smarty.const.ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register']))}
								<div class="w-line-row w-line-row-1">
                                      <div class="wl-td wl-td-r">
                                          <label>{$smarty.const.TEXT_TITLE_}:</label>
										  {tep_draw_radio_field('entry_gender', 'm', false, $cInfo->entry_gender)}  {$smarty.const.T_MR}
										  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										  {tep_draw_radio_field('entry_gender', 'f', false, $cInfo->entry_gender)}  {$smarty.const.T_MRS}
										  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										  {tep_draw_radio_field('entry_gender', 's', false, $cInfo->entry_gender)}   {$smarty.const.T_MISS}
                                      </div>                          
                                </div> 							  
							{/if}                      
                  </div>                  
                  <div class="widget-content billing-content widget-content-top-border">
                      <div class="w-line-row w-line-row-2">
                          <div>
							{if (in_array($smarty.const.ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                    <label>{$smarty.const.ENTRY_FIRST_NAME}{if in_array($smarty.const.ACCOUNT_FIRSTNAME, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									  {if $error && $errors->entry_firstname_error}
										{tep_draw_input_field('entry_firstname', $cInfo->entry_firstname, 'maxlength="32" class="form-control"' ) } {sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR,$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}
									  {else}
									    {tep_draw_input_field('entry_firstname', $cInfo->entry_firstname, 'maxlength="32" class="form-control"')}
									  {/if}
							
                              </div>
							{/if}
                          </div>
                          <div>
							{if (in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
								
                                    <label>{$smarty.const.ENTRY_LAST_NAME}{if in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									{if $error && $errors->entry_lastname_error}
										{tep_draw_input_field('entry_lastname', $cInfo->entry_lastname, 'maxlength="32" class="form-control"', in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register']))}  {sprintf($smarty.const.ENTRY_LAST_NAME_ERROR,$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}
									{else}
										{tep_draw_input_field('entry_lastname', $cInfo->entry_lastname, 'maxlength="32" class="form-control"', in_array($smarty.const.ACCOUNT_LASTNAME, ['required', 'required_register']))}
									{/if}
                              </div>
							{/if}
                          </div>
                      </div>				  
                      <div class="w-line-row w-line-row-2">
                          <div>
							{if (in_array($smarty.const.ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_POST_CODE}{if in_array($smarty.const.ACCOUNT_POSTCODE, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->entry_post_code_error}
									{tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8" class="form-control"')} {sprintf($smarty.const.ENTRY_POST_CODE_ERROR,$smarty.const.ENTRY_POSTCODE_MIN_LENGTH)}
								  {else}
									{tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8" class="form-control"')}
								  {/if}
							
                              </div>
							{/if}
                          </div>
                          <div>
							{if (in_array($smarty.const.ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_STREET_ADDRESS}{if in_array($smarty.const.ACCOUNT_STREET_ADDRESS, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->entry_street_address_error}
									{tep_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64" class="form-control"')}  {sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, $smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH)}
								  {else}
									{tep_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64" class="form-control"', true)}
								  {/if}
								
                              </div>
							{/if}
                          </div>
                      </div>
                      <div class="w-line-row w-line-row-2">
                          <div>
						  {if (in_array($smarty.const.ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.ENTRY_SUBURB}{if in_array($smarty.const.ACCOUNT_SUBURB, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->entry_suburb_error}
									{tep_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32" class="form-control"')}  {$smarty.const.ENTRY_SUBURB_ERROR}
								  {else}
									{tep_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32" class="form-control"', true)}
								  {/if}
							  </div>
						  {else}
								<div class="wl-td">
									<label></label>
								</div>							  
						  {/if}
                          </div>
						  <div>
							{if (in_array($smarty.const.ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register']))}
                              <div class="wl-td">
                                  <label>{$smarty.const.T_TOWN_CITY}:{if in_array($smarty.const.ACCOUNT_CITY, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
								  {if $error && $errors->entry_city_error}
									{tep_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32" class="form-control"')}  {sprintf($smarty.const.ENTRY_CITY_ERROR, $smarty.const.ENTRY_CITY_MIN_LENGTH)}
								  {else}
									{tep_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32" class="form-control"', true)}
								  {/if}
                              </div>
							{/if}
                          </div>						  
                        </div>
						  <div class="w-line-row w-line-row-2">
							  <div>
							  {if (in_array($smarty.const.ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register']))}
								<div class="wl-td">
									<label>{$smarty.const.ENTRY_STATE}:{if in_array($smarty.const.ACCOUNT_STATE, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									{if $error && $errors->entry_state_error}
										{if $entry->entry_state_has_zones}
											{tep_draw_pull_down_menu('entry_state', $entry->zones_array, '', ' class="form-control"')}  {$smarty.const.ENTRY_STATE_ERROR}
										{else}
											{tep_draw_input_field('entry_state', $entry->entry_state, ' class="form-control"')}  {$smarty.const.ENTRY_STATE_ERROR}
										{/if}
									{else}
										{if $entry->entry_state_has_zones}
											{tep_draw_pull_down_menu('entry_state', $entry->zones_array, $entry->entry_state, ' class="form-control"')} 
										{else}
											{tep_draw_input_field('entry_state', $entry->entry_state, ' class="form-control"')}
										{/if}
									{/if}
								</div>
							  {else}
								<div class="wl-td">
									<label></label>
								</div>
							  {/if}
							  </div>
							  <div>
								 {if (in_array($smarty.const.ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register']))}
								  <div class="wl-td">
									  <label>{$smarty.const.ENTRY_COUNTRY}{if in_array($smarty.const.ACCOUNT_COUNTRY, ['required', 'required_register'])}<span class="fieldRequired">*</span></label>{/if}</label>
									  {tep_draw_pull_down_menu('entry_country_id', $entry->countries, $cInfo->entry_country_id, ' class="form-control" onchange="select_country(this.value, \'\');"')}
									  {if $error}
										{if $errors->entry_country_error}
											{$smarty.const.ENTRY_COUNTRY_ERROR}
										{/if}
									  {/if}
								  </div>
								{/if}
							  </div>
						  </div>						
						  <div class="w-line-row w-line-row-1">
							  <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.T_REQUIRED}</span>
						  </div> 						  
                      </div>
                  </div>
        </div>     
        <div style="clear: both"></div>