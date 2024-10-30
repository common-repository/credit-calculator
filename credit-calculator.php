<?php
/*
Plugin Name: credit-calculator
Plugin URI: http://sunidea.ru/wordpress/credit-calculator-plugin/
Description: Плагин для расчета стоимости кредита и процентов / Plugin to calculate the cost of credit and percent
Version: 1.1
Author: Sunidea.ru
Author URI: http://sunidea.ru/
License: GPL2
*/

/*************************************************************************************************************/

define('CREDIT_CALCULATOR_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
define('CREDIT_CALCULATOR_PLUGIN_PATH', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)));

// Подключаем конфигурацию вкладок
include_once(CREDIT_CALCULATOR_PLUGIN_PATH.'/config_tabs.php');

// Подключаем языковой файл
$lang_file = 'ru.php';
include_once(CREDIT_CALCULATOR_PLUGIN_PATH.'/langs/'.$lang_file);

// Добавляем настройки плагина (Add the plugin settings)
add_option('credit_calculator_valuta', array(0 => "RUB", 1 => "USD", 2 => "EUR"));

// Добавляем пункт меню в админку (Adding a menu item to the admin panel)
add_action('admin_menu', 'credit_calculator_plugin_menu');

// Ajax действия по получению результата (Ajax actions to get results)
add_action('wp_ajax_credit_calculator_result', 'credit_calculator_result');  
add_action('wp_ajax_nopriv_credit_calculator_result', 'credit_calculator_result'); 

add_action('wp_ajax_credit_calculator_result_print', 'credit_calculator_result_print');  
add_action('wp_ajax_nopriv_credit_calculator_result_print', 'credit_calculator_result_print'); 

add_action('wp_ajax_credit_calculator_result_pdf', 'credit_calculator_result_pdf');  
add_action('wp_ajax_nopriv_credit_calculator_result_pdf', 'credit_calculator_result_pdf'); 

// Добавляем пункт меню в настройки (Adding a menu item in the admin page with settings)
function credit_calculator_plugin_menu() {
	add_options_page(cc_t('options_page_admin'), 'Credit calculator', 'manage_options', 'credit-calculator', 'credit_calculator_PageOptions');
}

// Страница настроек в админке (Page settings in the admin panel)
function credit_calculator_PageOptions() {
	if(isset($_POST['submit'])) {
		$credit_calculator_valuta = $_POST['credit_calculator_valuta'];
		$credit_calculator_valuta_arr = explode(',', $credit_calculator_valuta);

		update_option('credit_calculator_valuta', $credit_calculator_valuta_arr);

		echo '<div class="updated"><p><strong>'.cc_t('options_saved').'</strong></p></div>';
	}

	$credit_calculator_valuta = get_option('credit_calculator_valuta');
	$credit_calculator_valuta_str = implode(',', $credit_calculator_valuta);
	
	echo '
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>'.cc_t('options_page_admin_h1').'</h2>
		</div>
		
			<form name="form1" method="post" action="">

			<p>
				<input type="text" name="credit_calculator_valuta" id="credit_calculator_valuta" value="'.$credit_calculator_valuta_str.'" size="50" /> <label for="credit_calculator_valuta">'.cc_t('list_currency').'</label>
				<br/>
				<span class="description">'.cc_t('enter_list_currency').'</span>
			</p>
			
			<p>
				<input type="text" name="credit_calculator_count_tabs" id="credit_calculator_count_tabs" value="'.$credit_calculator_count_tabs.'" size="50" /> <label for="credit_calculator_count_tabs">'.cc_t('count_tabs').'</label>
				<br/>
				<span class="description">'.cc_t('enter_count_tabs').'</span>
			</p>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="'.cc_t('save').'" />
			</p>
		</form>';
}

// Функция отображения содержимого вкладки (Function display the contents of the tab)
function credit_calculator_content_tab($init_value = array()) {
	$html = '';
	
	$credit_calculator_valuta = get_option('credit_calculator_valuta'); // Валюты калькулятора из настроек (Currency calculator from the settings)	
	$credit_calculator_valuta_field = '<div>
		<div class="label">'.cc_t('currency_credit').'</div>  
		<div class="field"><select name="credit_calculator_valuta">';
	foreach ($credit_calculator_valuta as $key => $value) {
		$selected = $init_value['credit_calculator_valuta'] == $value? 'selected="selected"' : '';
		$credit_calculator_valuta_field .= '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
	}
	$credit_calculator_valuta_field .= '</select></div></div>';
	
	// Сумма кредита (Sum credit)
	$credit_calculator_sum_credit = '
		<div>
			<div class="label">'.cc_t('sum_credit').'*:</div> 
			<div class="field"><input type="text" name="credit_calculator_sum_credit" size="10" value="'.$init_value['credit_calculator_sum_credit'].'" def_value="'.$init_value['credit_calculator_sum_credit'].'"/></div>
		</div>
	';
	
	// Срок кредита (Credit term)
	$credit_calculator_term_credit = '
		<div>
			<div class="label">'.cc_t('term_credit').'*:</div>
			<div class="field">
				<input type="text" name="credit_calculator_term_credit" size="10" value="'.$init_value['credit_calculator_term_credit'].'" def_value="'.$init_value['credit_calculator_term_credit'].'"/> 
				<select name="credit_calculator_term_credit_unit">
					<option value="month">'.cc_t('months').'</option>
					<option value="year">'.cc_t('ages').'</option>
				</select>
			</div>
		</div>
	';
	
	// Процентная ставка (Percent rate)
	$credit_calculator_percent_rate = '
		<div>
			<div class="label">'.cc_t('percent_rate').'*:</div>
			<div class="field">
				<input type="text" name="credit_calculator_percent_rate" size="10" value="'.$init_value['credit_calculator_percent_rate'].'" def_value="'.$init_value['credit_calculator_percent_rate'].'"/>
				<select name="credit_calculator_percent_rate_unit">
					<option value="year">'.cc_t('percent_year').'</option>
					<option value="month">'.cc_t('percent_month').'</option>
				</select>
			</div>
		</div>
	';
	
	// Первоначальный взнос (Initial Fee)
	$credit_calculator_initial_fee = '
		<div>
			<div class="label">'.cc_t('initial_fee').':</div>
			<div class="field">
				<input type="text" name="credit_calculator_initial_fee" size="10" value="'.$init_value['credit_calculator_initial_fee'].'" def_value="'.$init_value['credit_calculator_initial_fee'].'"/>
				<select name="credit_calculator_initial_fee_unit">
					<option value="percent">'.cc_t('percent_of_sum').'</option>
					<option value="fix">'.cc_t('fix_sum').'</option>
				</select>
			</div>
		</div>
	';
	
	// Единовременная комиссия (One time fee)
	$credit_calculator_one_time_fee = '
		<div>
			<div class="label">'.cc_t('one_time_fee').':</div>
			<div class="field">
				<input type="text" name="credit_calculator_one_time_fee" size="10" value="'.$init_value['credit_calculator_one_time_fee'].'" def_value="'.$init_value['credit_calculator_one_time_fee'].'"/>
				<select name="credit_calculator_one_time_fee_unit">
					<option value="percent">'.cc_t('percent_of_sum').'</option>
					<option value="fix">'.cc_t('fix_sum').'</option>
				</select>
			</div>
		</div>
	';
	
	// Ежемесячная комиссия (Monthly commission)
	$credit_calculator_monthly_commission = '
		<div>
			<div class="label">'.cc_t('monthly_commission').':</div>
			<div class="field">
				<input type="text" name="credit_calculator_monthly_commission" size="10" value="'.$init_value['credit_calculator_monthly_commission'].'" def_value="'.$init_value['credit_calculator_monthly_commission'].'"/>
				<select name="credit_calculator_monthly_commission_unit">
					<option value="percent">'.cc_t('percent_of_sum').'</option>
					<option value="fix">'.cc_t('fix_sum').'</option>
					<option value="percent_ost">'.cc_t('percent_of_rest').'</option>
				</select>
			</div>
		</div>
	';
	
	// Метод погашения (Method_amortization)
	$credit_calculator_method_amortization = '
		<div>
			<div class="label">'.cc_t('method_amortization').':</div>
			<div class="field">
				<select name="credit_calculator_method_amortization">
					<option value="annuity">'.cc_t('annuity').'</option>
					<option value="differentiated">'.cc_t('differentiated').'</option>
				</select>
			</div>
		</div>
	';
	
	// Формируем вкладку (Forming tab)
	$html = '
		'.$credit_calculator_valuta_field.'
		<div class="clear_div"></div>
		'.$credit_calculator_sum_credit.'
		<div class="clear_div"></div>
		'.$credit_calculator_term_credit.'
		<div class="clear_div"></div>
		'.$credit_calculator_percent_rate.'
		<div class="clear_div"></div>
		'.$credit_calculator_initial_fee.'
		<div class="clear_div"></div>
		'.$credit_calculator_one_time_fee.'
		<div class="clear_div"></div>
		'.$credit_calculator_monthly_commission.'
		<div class="clear_div"></div>
		'.$credit_calculator_method_amortization.'
		<div class="clear_div"></div>
		<button class="credit_calculator_submit">'.cc_t('calculate').'</button>
		<button class="credit_calculator_clear">'.cc_t('clear').'</button>
	';
	
	return $html;
}

//Отображаем калькулятор (Show calculator)
function display_credit_calculator($atts, $content="") {
	global $_tabs;
	$result_divs = '';

	// Подключаем javascript плагина (Include javascript and css)
	wp_enqueue_script('credit_calculator_js', CREDIT_CALCULATOR_PLUGIN_URL.'/js/credit_calculator.js', array('jquery-ui-tabs'));
	wp_enqueue_style('jquery-ui-css', CREDIT_CALCULATOR_PLUGIN_URL.'/template/css/jquery-ui.css');
	wp_enqueue_style('credit_calculator_css', CREDIT_CALCULATOR_PLUGIN_URL.'/template/css/credit_calculator.css');
	
	$template = getTemplate('template/template.php');
	
	// Название вкладок из настроек (Get title tabs)
	$i = 1;
	$html .= '<input type="hidden" name="credit_calculator_wp_url" value="'.get_site_url().'">';
	$html .= '<ul>';
	foreach ($_tabs as $key => $value) {
		$html .= '
			<li><a href="#tabs-'.$i.'">'.$value['title'].'</a></li>
		';
		$i++;
	}
	$html .= '</ul>';
	
	// Вкладки из настроек (Tabs from options)
	$i = 1;
	foreach ($_tabs as $key => $value) {
		$html .= '
			<div id="tabs-'.$i.'">
				<form autocomplete="off" onsubmit="return false;">
				'.credit_calculator_content_tab($_tabs[$i]).'
				</form>
			</div>';			
			
		$result_divs .= '<div class="credit_calculator_result" id="credit_calculator_result_'.$i.'" class="ui-widget ui-widget-content ui-corner-all"></div>';
		
		$i++;
	}
	
	$template = applyTemplate('%tabs%', $html, $template);
	$template = applyTemplate('%result_divs%', $result_divs, $template);
	
	return $template;
}

function credit_calculator_result_print() {
	echo '
	<html>
		<head>
			<title>'.cc_t('print_result').'</title>
			<link rel="stylesheet" id="credit_calculator_css-css"  href="http://wordpress/wp-content/plugins/credit-calculator/template/css/credit_calculator.css?ver=3.5" type="text/css" media="all" />

		</head>
		<body>';
		
			credit_calculator_result(true);
	
	echo '
		<br/><div><button id="credit_calculator_printer" onclick="window.print();">'.cc_t('print_this_information').'</button></div>
		</body>
	</html>
	';
	exit();
}

function credit_calculator_result_pdf() {
	include ("dompdf/dompdf_config.inc.php");

	ob_start();
	credit_calculator_result(true);
	$buffer = ob_get_contents();
	ob_end_clean();
	
	$html = '
		<html>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<link rel="stylesheet" href="'.CREDIT_CALCULATOR_PLUGIN_URL.'/template/css/credit_calculator_pdf.css" type="text/css">
		<body>
		<style>
			table.credit_calculator_schedule td{
				border: 1px solid gray;
				text-align: center;
			}
			
			tr.credit_calculator_itogo_row, tr.credit_calculator_header_row {
				background-color: #CCCCCC;
			}
		</style>
		
		'.$buffer.'
		</body></html>
	';
			
	$dompdf = new DOMPDF();// Создаем обьект pdf (Create PDF Object)
	$dompdf->load_html($html); // Загружаем в него наш html код (SET html code)
	$dompdf->render(); // Создаем из HTML PDF (Create pdf from html)
	$dompdf->stream('result_credit.pdf'); // Выводим результат (Put result in file)
	
	exit();
}

// Отображение результата калькулятора (Show credit calculator result)
function credit_calculator_result($flag = false) {
	global $_tabs; 
	
	$request = $_REQUEST; 
	
	$data['credit_calculator_valuta'] = getStringVar($request['credit_calculator_valuta']);
	$data['credit_calculator_sum_credit'] = getStringVar($request['credit_calculator_sum_credit']);
	$data['credit_calculator_term_credit'] = getStringVar($request['credit_calculator_term_credit']);
	$data['credit_calculator_term_credit_unit'] = getStringVar($request['credit_calculator_term_credit_unit']);
	$data['credit_calculator_percent_rate'] = getStringVar($request['credit_calculator_percent_rate']);
	$data['credit_calculator_percent_rate_unit'] = getStringVar($request['credit_calculator_percent_rate_unit']);
	$data['credit_calculator_initial_fee'] = getStringVar($request['credit_calculator_initial_fee']);
	$data['credit_calculator_initial_fee_unit'] = getStringVar($request['credit_calculator_initial_fee_unit']);
	$data['credit_calculator_one_time_fee'] = getStringVar($request['credit_calculator_one_time_fee']);
	$data['credit_calculator_one_time_fee_unit'] = getStringVar($request['credit_calculator_one_time_fee_unit']);
	$data['credit_calculator_monthly_commission'] = getStringVar($request['credit_calculator_monthly_commission']);
	$data['credit_calculator_monthly_commission_unit'] = getStringVar($request['credit_calculator_monthly_commission_unit']);
	$data['credit_calculator_method_amortization'] = getStringVar($request['credit_calculator_method_amortization']);
	$number_tab = getIntVar($request['number_tab']);
	
	// Процентная ставка (Percent rate)
	if ($data['credit_calculator_percent_rate_unit'] == 'month') {
		$data['credit_calculator_percent_rate_month'] = $data['credit_calculator_percent_rate'];
		$data['credit_calculator_percent_rate_year'] = $data['credit_calculator_percent_rate']*12;
	} else {
		$data['credit_calculator_percent_rate_month'] = $data['credit_calculator_percent_rate']/12;
		$data['credit_calculator_percent_rate_year'] = $data['credit_calculator_percent_rate'];
	}
	
	// Срок кредита (Credit term)
	if ($data['credit_calculator_term_credit_unit'] == 'month') {
		$data['credit_calculator_term_credit_month'] = $data['credit_calculator_term_credit'];
	} else {
		$data['credit_calculator_term_credit_month'] = $data['credit_calculator_term_credit']*12;
	}
	
	// Первоначальный взнос (Initial Fee)
	if ($data['credit_calculator_initial_fee']) {
		if ($data['credit_calculator_initial_fee_unit'] == 'fix') {
			$data['credit_calculator_initial_fee_sum'] = $data['credit_calculator_initial_fee'];
		} else {
			$data['credit_calculator_initial_fee_sum'] = $data['credit_calculator_initial_fee']*$data['credit_calculator_sum_credit']/100;
		}
	}
	
	// Единовременная комиссия (One time fee)
	if ($data['credit_calculator_one_time_fee_unit']) {
		if ($data['credit_calculator_one_time_fee_unit'] == 'fix') {
			$data['credit_calculator_one_time_fee_sum'] = $data['credit_calculator_one_time_fee'];
		} else {
			$data['credit_calculator_one_time_fee_sum'] = $data['credit_calculator_one_time_fee']*($data['credit_calculator_sum_credit']-$data['credit_calculator_initial_fee_sum'])/100;
		}
	}
	
	// Ежемесячная комиссия (Monthly commission)
	if ($data['credit_calculator_monthly_commission_unit']) {
		if ($data['credit_calculator_monthly_commission_unit'] == 'fix') {
			$data['credit_calculator_monthly_commission_sum'] = $data['credit_calculator_monthly_commission'];
		} else {
			$data['credit_calculator_monthly_commission_sum'] = $data['credit_calculator_monthly_commission']*($data['credit_calculator_sum_credit']-$data['credit_calculator_initial_fee_sum'])/100;
		}
	}
	
	// Сумма задолженности = Исходная стоимость кредита - Первоначальный взнос
	// (The amount of debt = Original cost of credit - Initial Fee)
	$debt_balance = $data['credit_calculator_sum_credit'] - $data['credit_calculator_initial_fee_sum'];
	
	// Аннуитетный платеж (Annuity payment)
	// *********************************************************************************************************************************************
	if ($data['credit_calculator_method_amortization'] == 'annuity') {
		$data['credit_calculator_method_amortization_title'] = cc_t('annuity_payments');
	
		// Сумма ежемесячного платежа = Сумма задолженности*Месячную процентную ставку/(1-(1+Месячная процентная ставка)^-Срок кредита в месяцах)
		// Amount of monthly payment = Total debt * Monthly interest rate / (1 ​​- (1 + monthly interest rate) ^ Term in months)
		$result['sum_month'] = $debt_balance*$data['credit_calculator_percent_rate_month']/100/(1-pow(1+$data['credit_calculator_percent_rate_month']/100, -1*$data['credit_calculator_term_credit_month']));
		
		// График погашения (Loan repayment schedule)
		$schedule = '<table class="credit_calculator_schedule">
		<tr class="credit_calculator_header_row">
			<td>'.cc_t('period').',<br/>'.cc_t('month').'</td>
			<td>'.cc_t('monthly_payment').'</td>
			<td>'.cc_t('principal').'</td>
			<td>'.cc_t('accrued_interest').'</td>
			<td>'.cc_t('monthly_commission').'</td>
			<td>'.cc_t('remaining_debt').'</td>
		</tr>
		';
		
		// Итоговые значения (Total)
		$itog['principal'] = 0; 									// Основной долг за весь период (The main duty for the entire period)
		$itog['accrued_interest'] = 0; 								// Начисленные проценты за весь период (Accrued interest for the entire period)
		$itog['credit_calculator_monthly_commission_sum']  = 0;
		
		$result['sum_month_min'] = -1;
		$result['sum_month_max'] = -1;
		
		for ($i=1; $i<=$data['credit_calculator_term_credit_month']; $i++) {
			// Если ежемесячная комиссия - это процент от остатка долга: (If the monthly fee - a percentage of the balance of the debt:)
			if ($data['credit_calculator_monthly_commission_unit'] == 'percent_ost') {
				$data['credit_calculator_monthly_commission_sum'] = $debt_balance*$data['credit_calculator_monthly_commission']/100;
			}
			
			$itog['credit_calculator_monthly_commission_sum'] = $itog['credit_calculator_monthly_commission_sum'] + $data['credit_calculator_monthly_commission_sum'];
		
			// Начисленные проценты = остаток задолженности на период * месячную процентную ставку/100
			// Accrued interest = balance due for the period * monthly percent rate/100
			$accrued_interest = $debt_balance * $data['credit_calculator_percent_rate_month']/100;
			
			// Минимальный и максимальный ежемесячный платеж (The minimum and maximum monthly payment)
			$result['sum_month_min'] = ($result['sum_month_min'] == -1)?$result['sum_month']+$data['credit_calculator_monthly_commission_sum']:$result['sum_month_min'];
			$result['sum_month_max'] = ($result['sum_month_max'] == -1)?$result['sum_month']+$data['credit_calculator_monthly_commission_sum']:$result['sum_month_max'];
			
			$result['sum_month_min'] = min($result['sum_month_min'], $result['sum_month']+$data['credit_calculator_monthly_commission_sum']);
			$result['sum_month_max'] = max($result['sum_month_max'], $result['sum_month']+$data['credit_calculator_monthly_commission_sum']);
			
			// Основной долг (Principal)
			$principal = $result['sum_month'] - $accrued_interest;
			
			// Снижаем основной долг (Reduce the principal)
			$debt_balance = abs($debt_balance - $principal);
		
			$schedule .= ' <tr>
				<td>'.$i.'</td>
				<td>'.printMoney($result['sum_month']+$data['credit_calculator_monthly_commission_sum']).'</td>
				<td>'.printMoney($principal).'</td>
				<td>'.printMoney($accrued_interest).'</td>
				<td>'.printMoney($data['credit_calculator_monthly_commission_sum']).'</td>
				<td>'.printMoney($debt_balance).'</td>
			</tr>';		

			$itog['principal'] = $itog['principal']+ printFloat($principal);
			$itog['accrued_interest'] = $itog['accrued_interest']+ printFloat($accrued_interest);
			
			$itog['sum_month'] = $itog['sum_month']+$result['sum_month']+$data['credit_calculator_monthly_commission_sum'];
		}
		
		$schedule .= '
			<tr class="credit_calculator_itogo_row">
				<td>'.cc_t('total').':</td>
				<td>'.printMoney($itog['sum_month']).'</td>
				<td>'.printMoney($itog['principal']).'</td>
				<td>'.printMoney($itog['accrued_interest']).'</td>
				<td>'.printMoney($itog['credit_calculator_monthly_commission_sum']).'</td>
				<td></td>
			</tr>
		</table>';
	}
	
	// Дифференцированный платеж (Differentiated payment)
	if ($data['credit_calculator_method_amortization'] == 'differentiated') {
		$data['credit_calculator_method_amortization_title'] = cc_t('differentiated_payments');
	
		// Основной платеж = размер кредита / количество месяцев
		// Principal payment = loan amount / number of months
		$result['principal_payment'] = $debt_balance/$data['credit_calculator_term_credit_month'];
		
		// График погашения (Loan repayment schedule)
		$schedule = '<table class="credit_calculator_schedule">
		<tr class="credit_calculator_header_row">
			<td>'.cc_t('period').',<br/>'.cc_t('month').'</td>
			<td>'.cc_t('monthly_payment').'</td>
			<td>'.cc_t('principal').'</td>
			<td>'.cc_t('accrued_interest').'</td>
			<td>'.cc_t('monthly_commission').'</td>
			<td>'.cc_t('remaining_debt').'</td>
		</tr>
		';

		$sum_itog = 0;
		
		// Итоговые значения (Total) 
		$itog['sum_month'] = 0;
		$itog['accrued_interest'] = 0;
		$itog['credit_calculator_monthly_commission_sum'] = 0;
		
		$result['sum_month_min'] = -1;
		$result['sum_month_max'] = -1;
		
		for ($i=1; $i<=$data['credit_calculator_term_credit_month']; $i++) {	
			// Если ежемесячная комиссия - это процент от остатка долга:
			// If the monthly fee - a percentage of the balance of the debt:
			if ($data['credit_calculator_monthly_commission_unit'] == 'percent_ost') {
				$data['credit_calculator_monthly_commission_sum'] = $debt_balance*$data['credit_calculator_monthly_commission']/100;
			}
			
			$itog['credit_calculator_monthly_commission_sum'] = $itog['credit_calculator_monthly_commission_sum'] + $data['credit_calculator_monthly_commission_sum'];
		
			// Начисленные проценты = остаток задолженности на период * месячную процентную ставку/100
			// Accrued interest = balance due for the period * monthly percent rate/100
			$accrued_interest = $debt_balance*$data['credit_calculator_percent_rate_month']/100;
			
			// сумма платежа = основной платеж + начисленные проценты + ежемесячная комиссия
			// sum payment = principal payment + accrued interest + monthly commission
			$result['sum_month'] = $result['principal_payment'] + $accrued_interest + $data['credit_calculator_monthly_commission_sum'];	

			
			// Минимальный и максимальный ежемесячный платеж (The minimum and maximum monthly payment)
			$result['sum_month_min'] = ($result['sum_month_min'] == -1)?$result['sum_month']:$result['sum_month_min'];
			$result['sum_month_max'] = ($result['sum_month_max'] == -1)?$result['sum_month']:$result['sum_month_max'];
			
			$result['sum_month_min'] = min($result['sum_month_min'], $result['sum_month']);
			$result['sum_month_max'] = max($result['sum_month_max'], $result['sum_month']);

			// Сколько всего заплатили (How many paid)
			$sum_itog = $sum_itog + $result['sum_month'];
			
			// Остаток задолженности = Остаток задолженности - Платеж основного долга
			// (Balance payable = outstanding balance - Payment of principal)
			$debt_balance = $debt_balance - $result['principal_payment'];
			
			$schedule .= ' <tr>
				<td>'.$i.'</td>
				<td>'.printMoney($result['sum_month']).'</td>
				<td>'.printMoney($result['principal_payment']).'</td>
				<td>'.printMoney($accrued_interest).'</td>
				<td>'.printMoney($data['credit_calculator_monthly_commission_sum']).'</td>
				<td>'.printMoney($debt_balance).'</td>
			</tr>';	
			
			$itog['sum_month'] = $itog['sum_month'] + printFloat($result['sum_month']);
			$itog['accrued_interest'] = $itog['accrued_interest']+ printFloat($accrued_interest);
		}
		$schedule .= '
			<tr class="credit_calculator_itogo_row">
				<td>ИТОГО:</td>
				<td>'.printMoney($itog['sum_month']).'</td>
				<td>'.printMoney(printFloat($result['principal_payment'])*$data['credit_calculator_term_credit_month']).'</td>
				<td>'.printMoney($itog['accrued_interest']).'</td>
				<td>'.printMoney($itog['credit_calculator_monthly_commission_sum']).'</td>
				<td></td>
			</tr>
		</table>';
	}
	
	// Минимальный и максимальный платеж (The minimum and maximum monthly payment)
	if ($result['sum_month_min'] == $result['sum_month_max']) {
		$result['sum_month_text'] = printMoney($result['sum_month_max']);
	} else {
		$result['sum_month_text'] = printMoney($result['sum_month_min']).' - '.printMoney($result['sum_month_max']);
	}
	
		
	$result['overpayment'] = $itog['accrued_interest'] + $data['credit_calculator_one_time_fee_sum']+$itog['credit_calculator_monthly_commission_sum'];
		
	$result['overpayment_percent'] = $result['overpayment']*100/$data['credit_calculator_sum_credit'];
		
	$result['full_cost'] = $result['overpayment']+$data['credit_calculator_sum_credit'];
		
	
	$html = '<h2>Результат ('.$data['credit_calculator_method_amortization_title'].'):</h2>';	
	
	
	$html .= '
		<div class="credit_calculator_result_row">
			<div class="credit_calculator_res_label">'.cc_t('sum_monthly_payment').'</div>
			<div class="credit_calculator_res_val">'.$result['sum_month_text'].' '.$data['credit_calculator_valuta'].'</div>
		</div>
		
		<div class="credit_calculator_result_row">
			<div class="credit_calculator_res_label">'.cc_t('overpayment_loan').'</div>
			<div class="credit_calculator_res_val">'.printFloat($result['overpayment_percent']).' '.cc_t('percent_of_sum').'</div>
		</div>
		
		<div class="credit_calculator_result_row">
			<div class="credit_calculator_res_label">'.cc_t('overpayment_loan').'</div>
			<div class="credit_calculator_res_val">'.printMoney($result['overpayment']).' '.$data['credit_calculator_valuta'].'</div>
		</div>
		
		<div class="credit_calculator_result_row">
			<div class="credit_calculator_res_label">'.cc_t('total_price_loan').'</div>
			<div class="credit_calculator_res_val">'.printMoney($result['full_cost']).' '.$data['credit_calculator_valuta'].'</div>
		</div>';
		
	// Первоначальные платежи (Initial payments)
	if ( ( ($data['credit_calculator_one_time_fee_sum']) && ($data['credit_calculator_one_time_fee_sum'] != '')) || 
	($data['credit_calculator_initial_fee_sum']) ) {
	
		$html .= '<div id="credit_calculator_initial_payments">'.cc_t('in_this_initial_payments').'</div>';
		
		// Первоначальный взнос, сумма (The initial payment, the amount)
		if ($data['credit_calculator_initial_fee_sum']) {
			$html .= '
				<div class="credit_calculator_result_row marginleft">
					<div class="credit_calculator_res_label">'.cc_t('initial_fee').'</div>
					<div class="credit_calculator_res_val">'.printMoney($data['credit_calculator_initial_fee_sum']).' '.$data['credit_calculator_valuta'].'</div>
				</div>
			';
		}
		
		// Единовременная комиссия, сумма (One-time fee, the amount of)
		if ( ($data['credit_calculator_one_time_fee_sum']) && ($data['credit_calculator_one_time_fee_sum'] != '')) {
			$html .= '
				<div class="credit_calculator_result_row marginleft">
					<div class="credit_calculator_res_label">'.cc_t('one_time_fee').'</div>
					<div class="credit_calculator_res_val">'.printMoney($data['credit_calculator_one_time_fee_sum']).' '.$data['credit_calculator_valuta'].'</div>
				</div>
			';
		}		
	
		$result['sum_initial_payments'] = $data['credit_calculator_one_time_fee_sum'] + $data['credit_calculator_initial_fee_sum'];
		$html .= '
			<div class="credit_calculator_result_row marginleft">
				<div class="credit_calculator_res_label">'.cc_t('itog_initial_payments').'</div>
				<div class="credit_calculator_res_val">'.printMoney($result['sum_initial_payments']).' '.$data['credit_calculator_valuta'].'</div>
			</div>
		';
	}
	
	// Дополнительные платежи (Extra charges)
	if (isset($_tabs[$number_tab]['extra_charges'])) {
		$html .= '<div id="credit_calculator_initial_payments">'.cc_t('additional_charges').'</div>';
		
		$sum_extra_charges = 0;
		foreach ($_tabs[$number_tab]['extra_charges'] as $key => $value) {
			$percent_sum = $value['percent_sum']*$data['credit_calculator_sum_credit']/100;
			$sum_all = $percent_sum + $value['fix_sum'];
			$sum_extra_charges += $sum_all;
			
			$html .= '
				<div class="credit_calculator_result_row marginleft">
					<div class="credit_calculator_res_label">'.$value['title'].'</div>
					<div class="credit_calculator_res_val">'.printMoney($sum_all).' '.$data['credit_calculator_valuta'].'</div>
				</div>
			';			
		}
		
		$html .= '
			<div class="credit_calculator_result_row marginleft">
				<div class="credit_calculator_res_label">'.cc_t('total_additional_payments').'</div>
				<div class="credit_calculator_res_val">'.printMoney($sum_extra_charges).' '.$data['credit_calculator_valuta'].'</div>
			</div>
		';
	}
		
		
	$html .='	
		<div id="credit_calculator_shedule">'.cc_t('loan_repayment_schedule').'</div>
		<div>
			'.$schedule.'
		</div>
	';
	
	$request_url = '';
	foreach ($request as $key => $value) {
		if ($key != 'action')
		$request_url .=  $key.'='.$value.'&';
	}
	
	if (!$flag) {
		$html .= '
			<input type="hidden" id="credit_calculator_print_url" value="&'.$request_url.'" />
			<br/><button class="credit_calculator_btn_print">'.cc_t('print').'</button>
			<button class="credit_calculator_btn_pdf">'.cc_t('pdf_export').'</button>
		';
	}
	
	echo $html;
	if (!$flag) exit();
}

// Добавляем shortcode (Add shortcode)
add_shortcode('credit_calculator', 'display_credit_calculator');



// *************************************** UTILS ********************************************

// Функция получения указанного шаблона в переменную (Function obtain the template into a variable)
function getTemplate($path) {
	ob_start();
	include $path;
	$buffer = ob_get_contents();
	ob_end_clean();
	return $buffer;
}

// Функция замены кода в шаблоне (Substitute function code in the template)
function applyTemplate($replace, $code, $text) {
	$text = str_replace($replace, $code, $text);
	return $text;
}

// Функция получения строковой переменной и её обработки (Function to get a string variable and its treatment)
function getStringVar($getvar) {
	$var = null;
	if (isset($getvar)) {
		if ($getvar != '')
			$var = htmlspecialchars(mysql_escape_string($getvar));
	}
	return $var;
}

// Функция получения целочисленной переменной и её обработки (Function to get an integer variable and its treatment)
function getIntVar($getvar) {
	$var = 0;
	if (isset($getvar)) $var = intval($getvar);
	return $var;
}

// Функция форматирования float числа (The formatting function float number)
function printFloat($float) {
	return @sprintf("%.2f", $float);
}

// Функция для вывода денежного формата (Function to display the currency format)
function printMoney($float) {
	return number_format($float, 2, '.', ' ');
}

// Функция для мультиязычности (Function for multilanguage)
function cc_t($name) {
	global $_lang;
	return $_lang[$name];
}

?>