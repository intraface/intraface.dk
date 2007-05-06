<?php
require_once dirname(__FILE__) . '/../config.local.php';


class Install {

	function __construct() {
		if (!defined('SERVER_STATUS') OR SERVER_STATUS == 'PRODUCTION') {
			die('Can not be performed on PRODUCTION SERVER');
		}
		elseif ($_SERVER['HTTP_HOST'] == 'www.intraface.dk') {
			die('Can not be performed on www.intraface.dk');
		}

	}

	function dropDatabase() {
		if (!$link = mysql_connect(DB_HOST, DB_USER, DB_PASS)) {
			echo 'could not connect to mysql';
			return false;
		}

		if (!mysql_select_db(DB_NAME, $link)) {
			echo 'could not select mysql db';
			return false;
		}

		// instead of this we want to make a loop through

		$result = mysql_query("SHOW TABLES FROM " . DB_NAME);

		while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table = $line['Tables_in_'.DB_NAME];

			$sql = 'DROP TABLE ' . $table . ';';
			$res = mysql_query($sql, $link);

			if (!$res) {
				echo 'could not do query';
				echo 'mysql error: ' . mysql_error();
				return false;
			}
		}
		return true;

	}

	function createDatabaseSchema() {
		$sql_structure = file_get_contents(dirname(__FILE__) . '/database-structure.sql');
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
		
		$sql_structure = file_get_contents(dirname(__FILE__) . '/database-update.sql');
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
		
		return true;

	}

	function createStartingValues() {
		$sql_values = file_get_contents(dirname(__FILE__) . '/database-values.sql');
		$sql_arr = explode(';',$sql_values);

		foreach($sql_arr as $_sql) {
			$_sql = trim($_sql);
			if(empty($_sql)) { continue; }
			$result = mysql_query(trim($_sql));

			if (!$result) {
				echo 'could not do query';
				echo 'mysql error: ' . mysql_error();
				return false;
			}
		}
		return true;
	}


	function __deconstruct() {
		mysql_close();
	}

	function resetServer() {

		if (!$this->dropDatabase()) {
			trigger_error('could not drop database', E_USER_ERROR);
			exit;
		}
		if (!$this->createDatabaseSchema()) {
			trigger_error('could not create schema', E_USER_ERROR);
			exit;
		}

		if (!$this->createStartingValues()) {
			trigger_error('could not create values', E_USER_ERROR);
			exit;
		}

		return true;

	}
}
/*
 * Klasse! denne bør ikke køres her, men i stedet benyttes resetServer()
$install = new Install;
$install->dropDatabase();
*/
?>