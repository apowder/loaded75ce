$(document).ready(function() { 

$('.unstored_carts .del-pt').click(function(){
        var that = this;
        bootbox.dialog({
            message: $(that).parent('.unstored_carts').find('label').html()+".<br> "+ $tranlations.TEXT_CONFIRM_DELETE,
            title: $tranlations.ICON_WARNING,
            buttons: {
                success: {
                        label: $tranlations.TEXT_BTN_YES,
                        className: "btn-delete",
                        callback: function() {                           
                            $.post("orders/deletecart", {
                                'deleteCart': $(that).attr('data-id'),
                            }, function(data, status){
                                if (status == 'success'){
                                    if (data.hasOwnProperty('reload')){
                                        window.location.reload();
                                    } else if(data.hasOwnProperty('goto')){
                                        window.location.href = data.goto;
                                    }                                    
                                }            
                            }, 'json');
                        }
                },
                main: {
                        label: $tranlations.TEXT_BTN_NO,
                        className: "btn-cancel",
                        callback: function() {
                           
                        }
                }
            }
        }); 
        
    });
    
});