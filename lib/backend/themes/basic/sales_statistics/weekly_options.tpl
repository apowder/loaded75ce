{use class="\yii\helpers\Html"}
<div class="tl_filters_title">{$smarty.const.TEXT_RANGE}</div>
<div class="wl-td">
<label>{$smarty.const.TEXT_WEEK_COMMON}:</label>
{Html::input('text', 'week', $week, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
<div class="tl_filters_title">{$smarty.const.TEXT_CUSTOM}</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_FROM}</label>{Html::input('text', 'start_custom', $start_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_TO}</label>{Html::input('text', 'end_custom', $end_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
<script>
    var checkSelection = function(){
        //check custom    
        return true;
    }

    $(document).ready(function(){
    
        if ($('input[name=start_custom]').val().length == 0){
            $('input[name=start_custom]').css({ 'background': '#eeeeee' });
        }
        if ($('input[name=end_custom]').val().length == 0){
            $('input[name=end_custom]').css({ 'background': '#eeeeee' });
        }
        
        $('input[name=week]').focus(function(){
            $(this).css({ 'background': '#ffffff' });
            $('input[name=start_custom], input[name=end_custom]').css({ 'background': '#eeeeee' }).val('');
        });
        
        $('input[name=week]').datepicker({ 
            'minViewMode':0,
            'format':'dd/mm/yyyy',
            'weekStart':1,
            'multidate': true,
            'multidateSeparator':'-',
            'autoclose':true,
            'isChanged': false,
            }).on('changeDate', function(e){
                this.isChanged = true;
                $('input[name=start_custom]').val('');
                $('input[name=end_custom]').val('');
            }).on('hide', function(e){
                if (!isNaN(Date.parse(e.date)) && this.isChanged){
                    var date = new Date(e.date);
                    var startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()); // - date.getDay() + 1 from monday to saunday
                    var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 6);// - date.getDay() + 7 from monday to saunday
                    $('input[name=week]').datepicker('setDates', [ startDate, endDate ] );
                }
                this.isChanged = false;
            });
            
        $('input[name=start_custom]').datepicker({ 
            'minViewMode':0, 
            'format':'dd/mm/yyyy',
            'autoclose':true,
            'weekStart':1,
            'immediateUpdates': true,
             beforeShowMonth: function(date){
                var $end = $('input[name=end_custom]').val();
                if ($end.length > 0){
                    $_end = $end.split("/");
                    $gend = new Date([ $_end[2], $_end[1], $_end[0]]);
                    return date <= $gend;
                }
                return true;
            }
        }).on('show', function(e){
            var $end = $('input[name=end_custom]').val();
            var $send = new Date(e.date);
            if ($end.length > 0){
                $_end = $end.split("/");
                $gend = new Date([ $_end[2], $_end[1], $_end[0]]);
                if ($gend.getFullYear() == $send.getFullYear() || isNaN($send.getFullYear())){
                    $('input[name=start_custom]').datepicker('setEndDate', $gend);
                } else {
                    $('input[name=start_custom]').datepicker('setEndDate', '');
                }
            }
        }).focus(function(){
            $('input[name=week]').css({ 'background': '#eeeeee' }).val('');
            $(this).css({ 'background': '#ffffff' });
            $('input[name=end_custom]').css({ 'background': '#ffffff' });
        });
        
        $('input[name=end_custom]').datepicker({ 
            'minViewMode':0, 
            'format':'dd/mm/yyyy',
            'autoclose':true,
            'weekStart':1,
            'immediateUpdates': true,
            beforeShowMonth: function(date){
                var $start = $('input[name=start_custom]').val();
                if ($start.length > 0){
                    $_start = $start.split("/");
                    $gstart = new Date([ $_start[2], $_start[1], $_start[0]]);
                    return date >= $gstart;
                }
                return true;
            }
        }).on('show', function(e){
            var $start = $('input[name=start_custom]').val();
            var $sstart = new Date(e.date);
            if ($start.length > 0){
                $_start = $start.split("/");
                $gstart = new Date([ $_start[2], $_start[1], $_start[0]]);
                if ($gstart.getFullYear() == $sstart.getFullYear() || isNaN($sstart.getFullYear()) ){
                    $('input[name=end_custom]').datepicker('setStartDate', $gstart);
                } else {
                    $('input[name=end_custom]').datepicker('setStartDate', '');
                }                
            }
        }).focus(function(){
            $('input[name=week]').css({ 'background': '#eeeeee' }).val('');
            $('input[name=start_custom], input[name=end_custom]').css({ 'background': '#ffffff' });
        });

    })
    
</script>