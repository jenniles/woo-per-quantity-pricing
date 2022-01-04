(function( $ ) {
	'use strict';

	$(document).ready(function() {

		if($('#_per_quantity_pqp').prop("checked") == true) {
			$('.iwe_pqp_table_single').show();
		}


		$(document).on('change','#_per_quantity_pqp',function() {
			if($(this).prop("checked") == true) {			
				$('.iwe_pqp_table_single').show();
			}
			else {
				$('.iwe_pqp_table_single').hide();
			}
		});
		$(document).on('change','.iwe_pqp_variation_enable',function() {
			if($(this).prop("checked") == true) {			
				$(this).parent().siblings('.iwe_pqp_table_variable').show();
			}
			else {
				$(this).parent().siblings('.iwe_pqp_table_variable').hide();
			}
		});
		$(document).on('click','.pqp_action_remove', function() {
			$(this).closest('tr').remove();
		});
		$('.pqp_action_add').click(function() {
			var empty = false;
			$('.pqp_minimum_qty').each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			$('.pqp_maximum_qty').each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			$('.pqp_price_qty').each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			if(!empty) {

				var index = $('.pqp_tbody_rows').find('tr:last-child td .pqp_action_remove').data('index');
				var html = '<tr><td><input type="number" name="pqp_minimum_qty[]" class="pqp_minimum_qty"></td><td><input type="number" name="pqp_maximum_qty[]" class="pqp_maximum_qty"></td><td><select name="pqp_pricing_type[]"><option value="pqp_sell_price">Selling Price</option><option value="pqp_fixed_disc">Fixed Discount</option><option value="pqp_perc_disc">Discount Percentage</option></select></td><td><input type="text" name="pqp_price_qty[]" class="pqp_price_qty wc_input_price"></td><td><input type="button" class="pqp_action_remove button" data-index="'+(index+1)+'" value="Remove"></td></tr>';
				$('.pqp_tbody_rows').append(html);
			}
		});
		$(document).on('click', '.pqp_action_add', function() {
			var loop = $(this).data('loop');
			var empty = false;
			
			$('.pqp_minimum_qty_'+loop).each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			$('.pqp_maximum_qty_'+loop).each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			$('.pqp_price_qty_'+loop).each(function(){
				if(!$(this).val()){
					$(this).css("border-color", "red");
					empty = true;
				}
				else {
					$(this).css("border-color", "");
				}
			});
			if(!empty) {
				
				var index = $('.pqp_tbody_rows_'+loop).find('tr:last-child td .pqp_action_remove').data('index');
				var html = '<tr><td><input type="number" name="pqp_minimum_qty['+loop+'][]" class="pqp_minimum_qty_'+loop+'"></td><td><input type="number" name="pqp_maximum_qty['+loop+'][]" class="pqp_maximum_qty_'+loop+'"></td><td><select name="pqp_pricing_type['+loop+'][]"><option value="pqp_sell_price">Selling Price</option><option value="pqp_fixed_disc">Fixed Discount</option><option value="pqp_perc_disc">Discount Percentage</option></select></td><td><input type="text" name="pqp_price_qty['+loop+'][]" class="pqp_price_qty_'+loop+' wc_input_price"></td><td><input type="button" class="pqp_action_remove button" data-index="'+(index+1)+'" value="Remove"></td></tr>';
				$('.pqp_tbody_rows_'+loop).append(html);
			}
		});
	});
})( jQuery );