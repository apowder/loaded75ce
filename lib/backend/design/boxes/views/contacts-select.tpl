{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CONTACTS}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TEXT_CONTACTS}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_TIME_FORMAT}</label>
            <select name="setting[0][view_item]" id="" class="form-control">
              <option value=""{if $settings[0].view_item == ''} selected{/if}></option>
              <option value="phone_number"{if $settings[0].view_item == 'phone_number'} selected{/if}>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</option>
              <option value="email"{if $settings[0].view_item == 'email'} selected{/if}>{$smarty.const.ENTRY_EMAIL_ADDRESS}</option>
              <option value="address"{if $settings[0].view_item == 'address'} selected{/if}>{$smarty.const.CATEGORY_ADDRESS}</option>
              <option value="company_no"{if $settings[0].view_item == 'company_no'} selected{/if}>{$smarty.const.ENTRY_BUSINESS_REG_NUMBER}</option>
              <option value="company_vat_id"{if $settings[0].view_item == 'company_vat_id'} selected{/if}>{$smarty.const.ENTRY_BUSINESS}</option>
              <option value="opening_hours"{if $settings[0].view_item == 'opening_hours'} selected{/if}>{$smarty.const.CATEGORY_OPEN_HOURS}</option>
            </select>
          </div>

          <div class="setting-row address-spacer" style="display: none">
            <label for="">{$smarty.const.TEXT_SPACER}</label>
            <input type="text" name="setting[0][address_spacer]" value="{$settings[0].address_spacer}" class="form-control" />
          </div>

          <div class="setting-row time-format" style="display: none">
            <label for="">{$smarty.const.TEXT_TIME_FORMAT}</label>
            <select name="setting[0][time_format]" id="" class="form-control">
              <option value=""{if $settings[0].time_format == ''} selected{/if}>12</option>
              <option value="24"{if $settings[0].time_format == '24'} selected{/if}>24</option>
            </select>
          </div>

          <script type="text/javascript">
            (function($){
              $(function(){
                $('select[name="setting[0][view_item]"]').on('change', function(){
                  if ($(this).val() == 'opening_hours'){
                    $('.time-format').show()
                  } else {
                    $('.time-format').hide()
                  }
                  if ($(this).val() == 'address'){
                    $('.address-spacer').show()
                  } else {
                    $('.address-spacer').hide()
                  }
                }).trigger('change')
              })
            })(jQuery)
          </script>



          {*include 'include/ajax.tpl'*}
        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>