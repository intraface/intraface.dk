<?php
require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');


$module = $kernel->module("filemanager");



$filemanager = new NewFileManager($kernel);



$db = new DB_Sql;
$i = 0;

$db->query("SELECT * FROM file_handler WHERE intranet_id = ".$kernel->intranet->get('id')." AND active = 1 ORDER BY file_name");
while($db->nextRecord()) {
	$i++;
	print("INSERT INTO images SET file_name = \"".$db->f('file_name')."\", access_key = \"".$db->f('access_key')."\", placement = ".$i.";\n");
}
?>