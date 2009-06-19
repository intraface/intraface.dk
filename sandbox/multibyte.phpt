--TEST--
Undefined notice error
--FILE--
<?php
error_reporting(E_ALL);
if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}

$table = new Console_Table();
echo $table->getTable();

?>
--EXPECT--
