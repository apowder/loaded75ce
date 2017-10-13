{use class="yii\helpers\Html"}
<div class="ps_desc_wrapper after prop_value_{$val_id}">
  <div class="ps_desc_1 div-interval">
    <label class="show-interval">{$smarty.const.TEXT_FROM}</label>
  {if {$pInfo->properties_type == 'text' && $pInfo->multi_line > 0}}
    {if $is_default_lang > 0}
      {Html::textarea('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
    {else}
      {Html::textarea('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
    {/if}
  {else}
    {if $is_default_lang > 0}
      {Html::textInput('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
    {else}
      {Html::textInput('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
    {/if}
  {/if}
    <label class="show-interval">{$smarty.const.TEXT_TO}</label>
    {if $is_default_lang > 0}
      {Html::textInput('values_upto['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_number_upto'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control show-interval', 'placeholder'=>$value['values_number_upto']])}
    {else}
      {Html::textInput('values_upto['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_number_upto'], ['class'=>'form-control show-interval', 'placeholder'=>$value['values_number_upto']])}
    {/if}
    <div class="upload_doc" data-name="upload_docs[{$val_id}][{$lang_id}]" {if {$pInfo->properties_type == 'file'}}data-value="{$value['values']}"{/if}></div>
  </div>
  <div class="ps_desc_2">
  {if {$pInfo->properties_type == 'text' && $pInfo->multi_line > 0}}
    {if $is_default_lang > 0}
      {Html::textarea('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
    {else}
      {Html::textarea('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
    {/if}
  {else}
    {if $is_default_lang > 0}
      {Html::textInput('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
    {else}
      {Html::textInput('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
    {/if}
  {/if}
    <a href="javascript:delPropValue('{$val_id}')" class="ps_del"><i class="icon-trash"></i></a>
  </div>
</div>