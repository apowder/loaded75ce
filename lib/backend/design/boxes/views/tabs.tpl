{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_TABS}
  </div>
  <div class="popup-content">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

          <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TEXT_TABS}</a></li>
          <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
          <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="type">

          <div class="tabbable tabbable-custom">
            <div class="nav nav-tabs">

              {foreach $languages as $language}
                <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$item.id}_{$language.id}" data-toggle="tab">{$language.logo} {$language.name}</a></div>
              {/foreach}

            </div>
            <div class="tab-content">

              {foreach $languages as $language}
                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">

                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 1</label>
                    <input type="text" name="setting[{$language.id}][tab_1]" value="{$settings[$language.id].tab_1}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 2</label>
                    <input type="text" name="setting[{$language.id}][tab_2]" value="{$settings[$language.id].tab_2}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 3</label>
                    <input type="text" name="setting[{$language.id}][tab_3]" value="{$settings[$language.id].tab_3}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 4</label>
                    <input type="text" name="setting[{$language.id}][tab_4]" value="{$settings[$language.id].tab_4}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 5</label>
                    <input type="text" name="setting[{$language.id}][tab_5]" value="{$settings[$language.id].tab_5}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 6</label>
                    <input type="text" name="setting[{$language.id}][tab_6]" value="{$settings[$language.id].tab_6}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 7</label>
                    <input type="text" name="setting[{$language.id}][tab_7]" value="{$settings[$language.id].tab_7}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 8</label>
                    <input type="text" name="setting[{$language.id}][tab_8]" value="{$settings[$language.id].tab_8}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 9</label>
                    <input type="text" name="setting[{$language.id}][tab_9]" value="{$settings[$language.id].tab_9}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 10</label>
                    <input type="text" name="setting[{$language.id}][tab_10]" value="{$settings[$language.id].tab_10}" class="form-control form-control-width"/>
                  </div>

                </div>
              {/foreach}

            </div>
          </div>


        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">Save</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>
<script type="text/javascript">
  $(function(){

    $('.block-type input:checked').parent().addClass('active');
    $('.block-type').on('click', function(){
      $('.block-type .active').removeClass('active');
      $('input:checked', this).parent().addClass('active')
    })
  });

</script>