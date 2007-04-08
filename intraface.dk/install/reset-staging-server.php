<?php
/*
 *
 *
 * BEFORE EDITING - MAKE SURE THAT THIS FILE UTILIZE THE
 * CLASS INSTALL!
 *
 *
 *
 *
 * */
/*
require dirname(__FILE__) . '/../config.local.php';

if (!defined('SERVER_STATUS') OR SERVER_STATUS == 'PRODUCTION') {
	die('Can not be performed on PRODUCTION SERVER');
}

if ($_SERVER['HTTP_HOST'] == 'www.intraface.dk') {
	die('Can not be performed on www.intraface.dk');
}

$sql_structure = file_get_contents(dirname(__FILE__) . '/database-structure.sql');
$sql_values = file_get_contents(dirname(__FILE__) . '/database-values.sql');

if (!$link = mysql_connect(DB_HOST, DB_USER, DB_PASS)) {
   echo 'could not connect to mysql';
   exit;
}

if (!mysql_select_db(DB_NAME, $link)) {
   echo 'could not select mysql db';
   exit;
}

$tables    = array(
	'accounting_account',
	'accounting_post',
	'accounting_vat_period',
	'accounting_voucher',
	'accounting_voucher_file',
	'accounting_year',
	'accounting_year_end',
	'accounting_year_end_action',
	'accounting_year_end_statement',
	'address',
	'basket',
	'cms_element',
	'cms_page',
	'cms_parameter',
	'cms_section',
	'cms_site',
	'cms_template',
	'cms_template_section',
	'comment',
	'contact',
	'contact_message',
	'contact_person',
	'core_translation_i18n',
	'core_translation_langs',
	'dbquery_result',
	'debtor',
	'debtor_item',
	'email',
	'email_attachment',
	'filehandler_append_file',
	'file_handler',
	'file_handler_instance',
	'flickr_cache',
	'intranet',
	'invoice_payment',
	'invoice_reminder',
	'invoice_reminder_item',
	'invoice_reminder_unpaid_reminder',
	'kernel_log',
	'keyword',
	'keyword_x_object',
	'lock_post',
	'log_id_seq',
	'log_table',
	'module',
	'module_sub_access',
	'newsletter_archieve',
	'newsletter_list',
	'newsletter_subscriber',
	'onlinepayment',
	'permission',
	'php_sessions',
	'procurement',
	'procurement_item',
	'product',
	'product_detail',
	'product_related',
	'redirect',
	'redirect_parameter',
	'redirect_parameter_value',
	'setting',
	'stock_adaptation',
	'stock_regulation',
	'systemmessage_disturbance',
	'systemmessage_news',
	'todo_contact',
	'todo_item',
	'todo_list',
	'user');

foreach ($tables AS $table) {

	$result = mysql_query("SHOW TABLES LIKE '".$table."'");
	if (mysql_num_rows($result) == 0) {
		continue;
	}

	$sql = 'DROP TABLE ' . $table . ';';
	$result = mysql_query($sql, $link);

	if (!$result) {
		echo 'could not do query';
		echo 'mysql error: ' . mysql_error();
	}
}

$sql_arr = explode(';',$sql_structure);

foreach($sql_arr as $_sql) {
	$_sql = trim($_sql);
	if(empty($_sql)) { continue; }
	$result = mysql_query(trim($_sql));

	if (!$result) {
		echo 'could not do query';
		echo 'mysql error: ' . mysql_error();
	}
}

$sql_arr = explode(';',$sql_values);

foreach($sql_arr as $_sql) {
	$_sql = trim($_sql);
	if(empty($_sql)) { continue; }
	$result = mysql_query(trim($_sql));

	if (!$result) {
		echo 'could not do query';
		echo 'mysql error: ' . mysql_error();
	}
}


mysql_close();
*/

require_once dirname(__FILE__) .  '/Install.php';

$install = new Install;

if ($install->resetServer()) {
	echo 'staging server reset. Go to <a href="/">login</a>.';
}
else {
	echo 'error';
}


?>