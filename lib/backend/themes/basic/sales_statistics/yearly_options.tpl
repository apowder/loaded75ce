{use class="\yii\helpers\Html"}
<div class="tl_filters_title">{$smarty.const.TEXT_CUSTOM}</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_FROM}</label>{Html::dropDownList('start_custom', $start_custom, $years, ['class' =>'form-control', 'prompt' => TEXT_SELECT])}
</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_TO}</label>{Html::dropDownList('end_custom', $end_custom, $years, ['class' =>'form-control', 'prompt' => TEXT_SELECT])}
</div>
<script>
 var checkSelection = function(){
        //check custom    
        return true;
 }
 
 $(document).ready(function(){
    if ($('select[name=start_custom]').val() == ''){
        $('select[name=start_custom]').css({ 'background': '#eeeeee' });
    }
    if ($('select[name=end_custom]').val() == ''){
        $('select[name=end_custom]').css({ 'background': '#eeeeee' });
    }
    
    $('select[name=start_custom], select[name=end_custom]').focus(function(){
       $('select[name=start_custom], select[name=end_custom]').css({ 'background': '#ffffff' });
    })
 });
 
</script>
