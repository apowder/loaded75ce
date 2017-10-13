{if $js_mask_type == 'maskMoney'}
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.maskMoney.js"></script>
{/if}
{if $js_mask_type == 'accounting'}
<script type="text/javascript" src="{$app->request->baseUrl}/js/accounting.min.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.maskedinput.js"></script>
{/if}
<script type="text/javascript">
        var currency_id = {$currencies_id['currencies_id']};
        var curr_hex = [];
        {$response}
	{if $js_mask_type == 'maskMoney'}		
		$.fn.setMaskMoney = function(){
			return this.each(function() {
				var _this = $(this);
				_this.attr('placeholder', curr_hex[currency_id].symbol_left + '0' + curr_hex[currency_id].decimal_point + '00' + curr_hex[currency_id].symbol_right);
				_this.maskMoney({
					prefix: curr_hex[currency_id].symbol_left,
					suffix: curr_hex[currency_id].symbol_right,
					decimal: curr_hex[currency_id].decimal_point,
					thousands: curr_hex[currency_id].thousands_point,
					precision: curr_hex[currency_id].decimal_places
				});
				_this.each(function () { 
					if (_this.val()!=''){
						_this.trigger('mask');
					}
				});
			});
		}
	{/if}	
	{if $js_mask_type == 'accounting'}
		$.fn.setMaskMoney = function(){
			return this.each(function() {
				var _this = $(this);
				_this.on('blur', function() {
					var result = accounting.formatMoney(
							$(this).val(),
							curr_hex[currency_id].symbol_left,
							curr_hex[currency_id].decimal_places,
							curr_hex[currency_id].thousands_point,
							curr_hex[currency_id].decimal_point
						);

					if (result == curr_hex[currency_id].symbol_left + '0.00') {
						result = '';
					}
					$(this).val(result);
				}).blur();

				_this.on('focus', function(){
					var result = $(this).val();
					result = accounting.unformat(result);
					if (result == 0) {
						result = '';
					}
					$(this).val(result);
				});

				_this.on('keydown', function(e){
					if (e.keyCode == 13) {
						var focusable = $('input,a,select,button,textarea').filter(':visible');
						$(this).blur()
						focusable.eq(focusable.index(this)+1).focus();
						return false;
					}
				});				
			});
		}				
	{/if}
    $(document).ready(function () { 
        var mask_money = $('.mask-money');
		mask_money.setMaskMoney();
    });
function unformatMaskMoney()
{
{if $js_mask_type == 'accounting'}
    $('.mask-money').each(function () { 
        var result = $(this).val();
        result = accounting.unformat(result);
        if (result == 0) {
            result = '';
        }
        $(this).val(result);
    });
{/if}
}
</script>
