jQuery(document).ready(function () {
	window.credit_active_tab = 1;
	window.credit_calculator_wp_url = jQuery('input[name="credit_calculator_wp_url"]').val();
	
	jQuery( "#tabs" ).tabs({
	activate: function( event, ui ) {
		// Получаем номер активной вкладки
		var n_tab_str = jQuery(ui.newTab[0]).attr('aria-controls');
		temp = n_tab_str.split(/[-]/);
		var n_tab = temp[1];

		window.credit_active_tab = n_tab;
		
		jQuery('div.credit_calculator_result').hide();
		jQuery('#credit_calculator_result_'+n_tab).show();
	}
	});
	
	// Очистить вкладку
	jQuery('button.credit_calculator_clear').live('click', function() {
		jQuery('#tabs-'+window.credit_active_tab+' input').val('');
		jQuery('#credit_calculator_result_'+window.credit_active_tab).empty();
		
	});
	
	// Результат работы калькулятора
	jQuery('button.credit_calculator_submit').live('click', function() {
		var credit_calculator_valuta = jQuery('select[name="credit_calculator_valuta"]:visible').val();
		var credit_calculator_sum_credit = jQuery('input[name="credit_calculator_sum_credit"]:visible').val();
		var credit_calculator_term_credit = jQuery('input[name="credit_calculator_term_credit"]:visible').val();
		var credit_calculator_term_credit_unit = jQuery('select[name="credit_calculator_term_credit_unit"]:visible').val();
		var credit_calculator_percent_rate = jQuery('input[name="credit_calculator_percent_rate"]:visible').val();
		var credit_calculator_percent_rate_unit = jQuery('select[name="credit_calculator_percent_rate_unit"]:visible').val();
		var credit_calculator_initial_fee = jQuery('input[name="credit_calculator_initial_fee"]:visible').val();
		var credit_calculator_initial_fee_unit = jQuery('select[name="credit_calculator_initial_fee_unit"]:visible').val();
		var credit_calculator_one_time_fee = jQuery('input[name="credit_calculator_one_time_fee"]:visible').val();
		var credit_calculator_one_time_fee_unit = jQuery('select[name="credit_calculator_one_time_fee_unit"]:visible').val();
		var credit_calculator_monthly_commission = jQuery('input[name="credit_calculator_monthly_commission"]:visible').val();
		var credit_calculator_monthly_commission_unit = jQuery('select[name="credit_calculator_monthly_commission_unit"]:visible').val();
		var credit_calculator_method_amortization = jQuery('select[name="credit_calculator_method_amortization"]:visible').val();
		
		if ((credit_calculator_sum_credit == '') || (credit_calculator_term_credit == '') || (credit_calculator_percent_rate == '')) {
			jQuery('#credit_calculator_result').empty();
			alert('Ошибка! Не заполнены все обязательные поля!');
			return;
		}
		
		jQuery.ajax({
			url: window.credit_calculator_wp_url+'/wp-admin/admin-ajax.php?action=credit_calculator_result',
			type: 'POST',
			data: {
				number_tab: window.credit_active_tab,
				credit_calculator_valuta: credit_calculator_valuta,
				credit_calculator_sum_credit: credit_calculator_sum_credit,
				credit_calculator_term_credit: credit_calculator_term_credit,
				credit_calculator_term_credit_unit: credit_calculator_term_credit_unit,
				credit_calculator_percent_rate: credit_calculator_percent_rate,
				credit_calculator_percent_rate_unit: credit_calculator_percent_rate_unit,
				credit_calculator_initial_fee: credit_calculator_initial_fee,
				credit_calculator_initial_fee_unit: credit_calculator_initial_fee_unit,
				credit_calculator_one_time_fee: credit_calculator_one_time_fee,
				credit_calculator_one_time_fee_unit: credit_calculator_one_time_fee_unit,
				credit_calculator_monthly_commission: credit_calculator_monthly_commission,
				credit_calculator_monthly_commission_unit: credit_calculator_monthly_commission_unit,
				credit_calculator_method_amortization: credit_calculator_method_amortization
			},
			dataType: 'html',
			success: function(html) {
				jQuery('#credit_calculator_result_divs').show();
				jQuery('div.credit_calculator_result').hide();
				jQuery('#credit_calculator_result_'+window.credit_active_tab).empty().html(html).show();
			}
		});	

		return false;		
	});
	
	jQuery('button.credit_calculator_btn_print').live('click', function() {
		var request_params = jQuery('#credit_calculator_print_url').val();
		var params = "menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes, width=600,height=500";
		window.open(window.credit_calculator_wp_url+"/wp-admin/admin-ajax.php?action=credit_calculator_result_print"+request_params, "Распечатать результаты", params);
	});
	
	jQuery('button.credit_calculator_btn_pdf').live('click', function() {
		var request_params = jQuery('#credit_calculator_print_url').val();
		window.document.location.href = window.credit_calculator_wp_url+"/wp-admin/admin-ajax.php?action=credit_calculator_result_pdf"+request_params;
	});
});