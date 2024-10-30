<?php

/*
Plugin Name: credit-calculator
Plugin URI: http://sunidea.ru/wordpress/credit-calculator-plugin/
Description: Плагин для расчета стоимости кредита и процентов / Plugin to calculate the cost of credit and percent
Author: Sunidea.ru
Author URI: http://sunidea.ru/
License: GPL2

Конфигурация вкладок (Configuration tabs)

*/

/********************************************* Настройки вкладок (Settings tab) *********************************************/
$_tabs[1] = array(
	"title" => "Вкладка 1",				// Название вкладки (Tab name)
	"credit_calculator_valuta" => "USD",			// Валюта (Currency)
	"credit_calculator_sum_credit" => "10", 		// Сумма кредита (Sum credit)
	"credit_calculator_term_credit" => "10", 		// Срок кредита (Term credit)
	"credit_calculator_percent_rate" => "10", 		// Процентная ставка (Percent rate)
	"credit_calculator_initial_fee" => "", 			// Первоначальный взнос (Initial Fee)
	"credit_calculator_one_time_fee" => "", 		// Единовременная комиссия (One time fee)
	"credit_calculator_monthly_commission" => "" 	// Ежемесячная комиссия (Monthly commission)
);

$_tabs[2] = array(
	"title" => "Вкладка 2",					// Название вкладки (Tab name)
	"credit_calculator_valuta" => "EUR",			// Валюта (Currency)
	"credit_calculator_sum_credit" => "10", 		// Сумма кредита (Sum credit)
	"credit_calculator_term_credit" => "10", 		// Срок кредита (Term credit)
	"credit_calculator_percent_rate" => "10", 		// Процентная ставка (Percent rate)
	"credit_calculator_initial_fee" => "", 			// Первоначальный взнос (Initial Fee)
	"credit_calculator_one_time_fee" => "", 		// Единовременная комиссия (One time fee)
	"credit_calculator_monthly_commission" => "" 	// Ежемесячная комиссия (Monthly commission)
);

$_tabs[3] = array(
	"title" => "Вкладка 3",						// Название вкладки (Tab name)
	"credit_calculator_valuta" => "RUB",			// Валюта (Currency)
	"credit_calculator_sum_credit" => "", 			// Сумма кредита (Sum credit)
	"credit_calculator_term_credit" => "", 			// Срок кредита (Term credit)
	"credit_calculator_percent_rate" => "", 		// Процентная ставка (Percent rate)
	"credit_calculator_initial_fee" => "", 			// Первоначальный взнос (Initial Fee)
	"credit_calculator_one_time_fee" => "", 		// Единовременная комиссия (One time fee)
	"credit_calculator_monthly_commission" => "", 	// Ежемесячная комиссия (Monthly commission)
	
	"extra_charges" => array(						// Дополнительные платежи (Additional payments)
		0 => array (
			"title" => "Комиссия за ...",
			"percent_sum" => 1,
			"fix_sum" => 10
		),
		
		1 => array (
			"title" => "комиссия от Меня",
			"percent_sum" => 0,
			"fix_sum" => 10
		)
	)
);

?>