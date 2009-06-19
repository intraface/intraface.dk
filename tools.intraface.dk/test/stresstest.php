<?php

//die("SUNE ER FÆRDIG");

function getmicrotime2() {
	$mt = explode( ' ', microtime());
	return $mt[1] + $mt[0];
}
$time = getmicrotime2();

//include("../include_first.php");

require_once 'DB/Sql.php';
require('Intraface/config/configuration.php');
/*
require('/home/intraface/deve_intraface/3Party/PEAR/MDB2.php');
set_include_path('/home/intraface/deve_intraface/3Party/PEAR/');
*/


echo "<br>After including first: ".round(getmicrotime2()-$time,4);



$j = 0;

$db = new DB_Sql;

$time = getmicrotime2();
for ($i = 1; $i < 100000; $i += 2) {

	$db->query("SELECT some_value, some_id, id, some_text FROM _sune_test WHERE id = ".$i."");
	$db->nextRecord();

	//print($db->f('some_value'));

	$j++;

	// $db->query("INSERT INTO _sune_test SET some_id = ".$i.", some_text = \"".md5($i)."\", some_value = ".(sqrt($i*4)));
}

echo "<br />After sql with i++".round(getmicrotime2()-$time,4);



$time = getmicrotime2();
for ($i = 1; $i < 100000; $i += 2) {

	$db->query("SELECT some_value, some_id, id, some_text FROM _sune_test WHERE id = ".$i."");
	$db->nextRecord();

	//print($db->f('some_value'));

	++$j;

	// $db->query("INSERT INTO _sune_test SET some_id = ".$i.", some_text = \"".md5($i)."\", some_value = ".(sqrt($i*4)));
}

echo "<br />After sql with ++i".round(getmicrotime2()-$time,4);

$time = getmicrotime2();
$db->query("SELECT some_value, some_id, id, some_text FROM _sune_test");
while($db->nextRecord()) {
	$array[$db->f('some_id')][$db->f('some_text')] = $db->f('some_value').$j." "; // [$db->f('some_text')]
	$j++;
}

for ($i = 1; $i < 100000; $i += 2) {
	//print($array[$i][md5($i)]); // [md5($i)]
}

echo "<br />After array".round(getmicrotime2()-$time,4);
/*
$con = msql_connect();
if (!$con) {
   die('Server connection problem: ' . msql_error());
}

if (!msql_select_db('test', $con)) {
   die('Database connection problem: ' . msql_error());
}

$result = msql_query('SELECT id, name FROM people', $con);
if (!$result) {
   die('Query execution problem: ' . msql_error());
}

while ($row = msql_fetch_array($result, MSQL_ASSOC)) {
   echo $row['id'] . ': ' . $row['name'] . "\n";
}

msql_free_result($result);
*/




/*
echo "<br />".$j;
echo "<br />".round(getmicrotime2()-$time,4);
*/
//echo "<br />".nl2br(file_get_contents('stresstest.php'));

?>
