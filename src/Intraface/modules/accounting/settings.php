<?php
/**
 * Systemsettings til Accounting
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */

$_setting['accounting.vat_period'] = 0; // halvårlig
$_setting['accounting.credit_account_id'] = 0; // halvårlig


$_setting['accounting.vat_in_account_id'] = 0;
$_setting['accounting.vat_out_account_id'] = 0;
$_setting['accounting.vat_balance_account_id'] = 0;
$_setting['accounting.vat_abroad_account_id'] = 0;
$_setting['accounting.vat_free_account_id'] = 0;
$_setting['accounting.eu_sale_account_id'] = 0;
//$_setting['accounting.eu_buy_account_id'] = 0;
//$_setting['accounting.abroad_buy_account_id'] = 0;
$_setting['accounting.result_account_id'] = 0;
$_setting['accounting.debtor_account_id'] = 0;
$_setting['accounting.balance_accounts'] = serialize(array());
$_setting['accounting.buy_abroad_accounts'] = serialize(array());
$_setting['accounting.buy_eu_accounts'] = serialize(array());

$_setting['accounting.daybook_cheatsheet'] = 'true';
$_setting['accounting.daybook_view'] = 'classic';
$_setting['accounting.daybook.message'] = 'view';
$_setting['accounting.state.message'] = 'view';
$_setting['accounting.state.message2'] = 'view';

$_setting['accounting.active_year'] = 0;

$_setting['accounting.result_account_id_start'] = 0;
$_setting['accounting.result_account_id_end'] = 0;
$_setting['accounting.balance_account_id_start'] = 0;
$_setting['accounting.balance_account_id_end'] = 0;

$_setting['accounting.capital_account_id'] = 0;