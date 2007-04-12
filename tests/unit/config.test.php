<?php
require_once 'MDB2.php';
define('DB_DSN', 'mysql://root:@localhost/test');
define('PATH_ROOT', 'c:\Documents and Settings\lars\workspace\IntrafaceNew\\');
define('PATH_INCLUDE_CONFIG', PATH_ROOT . 'Intraface\config\\');

$db = MDB2::singleton(DB_DSN);
$db->setOption('debug', 1);

if ($db->getOption('debug')) {
	$db->setOption('log_line_break', "\n\n\n\n\t");

	require_once 'MDB2/Debug/ExplainQueries.php';

	$my_debug_handler = new MDB2_Debug_ExplainQueries($db);
	$db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

	register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
	register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
}

?>