{use class="\yii\helpers\Html"}
{\backend\assets\BDPAsset::register($this)|void}
<label>{$smarty.const.TEXT_RANGE}:</label>
<br>
<label>{$smarty.const.TITLE_MONTH}/{$smarty.const.TITLE_YEAR}</label>
{Html::input('text', 'day', $day, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
<br>
<label>{$smarty.const.TEXT_CUSTOM}:</label>
<br/>
{$smarty.const.TEXT_FROM}:{Html::input('text', 'start_custom', $start_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
{$smarty.const.TEXT_TO}{Html::input('text', 'end_custom', $end_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
<script>
    var checkSelection = function(){
        //check custom    
        return true;
    }
    
    $(document).ready(function(){
    
        if ($('input[name=start_custom]').val().length == 0){
            $('input[name=start_custom]').css({ 'background': '#eeeeee' });
        } else {
            $('input[name=day]').css({ 'background': '#eeeeee' });
        }
        if ($('input[name=end_custom]').val().length == 0){
            $('input[name=end_custom]').css({ 'background': '#eeeeee' });
        }
        
        $('input[name=day]').datepicker({ 
            'minViewMode':0,
            'format':'dd/mm/yyyy',
            autoclose:true,
            }).on('changeDate', function(){
                $('input[name=start_custom]').val('');
                $('input[name=end_custom]').val('');
            }).focus(function(){                
                $(this).css({ 'background': '#ffffff' });
                $('input[name=start_custom]').css({ 'background': '#eeeeee' });
                $('input[name=end_custom]').css({ 'background': '#eeeeee' });
            });
            
        $('input[name=start_custom]').datepicker({ 
            'minViewMode':0, 
            'format':'dd/mm/yyyy',
            'autoclose':true,
            'immediateUpdates': true,
            beforeShowMonth: function(date){
                var $end = $('input[name=end_custom]').val();
                if ($end.length > 0){
                    $_end = $end.split("/");
                    $end = $_end[1]+'/'+$_end[0]+'/' + $_end[2];
                    $gend = new Date($end);
                    return date <= $gend;
                }
                return true;
            }
        }).on('show', function(e){
            var $end = $('input[name=end_custom]').val();
            var $send = new Date(e.date);
            if ($end.length > 0){
                $_end = $end.split("/");
                $end = $_end[1]+'/'+$_end[0]+'/' + $_end[2];
                $gend = new Date($end);
                if ($gend.getFullYear() == $send.getFullYear() || isNaN($send.getFullYear())){
                    $('input[name=start_custom]').datepicker('setEndDate', $gend);
                } else {
                    $('input[name=start_custom]').datepicker('setEndDate', '');
                }
            }
        }).focus(function(){
            $('input[name=day]').css({ 'background': '#eeeeee' });
            $(this).css({ 'background': '#ffffff' });
            $('input[name=end_custom]').css({ 'background': '#ffffff' });
        }).change(function(){
            $('input[name=day]').val('');
        });
        
        $('input[name=end_custom]').datepicker({ 
            'minViewMode':0,
            'format':'dd/mm/yyyy',
            'autoclose':true,
            'immediateUpdates': true,
            beforeShowMonth: function(date){
                var $start = $('input[name=start_custom]').val();
                if ($start.length > 0){
                    $_start = $start.split("/");
                    $start = $_start[1]+'/'+$_start[0]+'/' + $_start[2];
                    $gstart = new Date($start);
                    return date >= $gstart;
                }
                return true;
            }
        }).on('show', function(e){
            var $start = $('input[name=start_custom]').val();
            var $sstart = new Date(e.date);
            if ($start.length > 0){
                $_start = $start.split("/");
                $start = $_start[1]+'/'+$_start[0]+'/' + $_start[2];
                $gstart = new Date($start);
                if ($gstart.getFullYear() == $sstart.getFullYear() || isNaN($sstart.getFullYear()) ){
                    $('input[name=end_custom]').datepicker('setStartDate', new Date($start));
                } else {
                    $('input[name=end_custom]').datepicker('setStartDate', '');
                }                
            }
        }).focus(function(){
            $('input[name=day]').css({ 'background': '#eeeeee' });            
            $('input[name=start_custom]').css({ 'background': '#ffffff' });
            $(this).css({ 'background': '#ffffff' });
        });

    })
    
</script>