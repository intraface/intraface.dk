<?php
require_once 'MDB2.php"';

class DB_Sql {
	var $db;
	var $row;
	var $result;

	function DB_Sql($dbhost = DB_HOST, $dbuser = DB_USER, $dbpass = DB_PASS, $dbname = DB_NAME) {

		$this->db = MDB2::singleton(DB_DSN);
		if (PEAR::isError($this->db)) {
			die($this->db->getMessage() . ' ' . $this->db->getUserInfo());
		}
	}

	function query($SQL) {
		// Eksekvere SQL sætning
		// $db->Query("SELECT * FROM tabel");

		//echo $SQL . '<br>';

		$this->result = $this->db->query($SQL);
		if (PEAR::isError($this->result)) {
			die($this->result->getMessage() . ' ' . $this->result->getUserInfo());
		}
  	}

	function exec($SQL) {
		// Eksekvere SQL sætning
		// $db->Query("SELECT * FROM tabel");

		//echo $SQL . '<br>';

		$this->result = $this->db->exec($SQL);
		if (PEAR::isError($this->result)) {
			die($this->result->getMessage() . ' ' . $this->result->getUserInfo());
		}

		$this->result->free();
  	}
	function nextRecord() {
		// Gennemsøger recordset.
		// Går videre til næste post hver gang den kaldes.
		// Returnere true så længe der er en post
		// while($db->next_record()) {
		$this->row = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC);
		if (PEAR::isError($this->row)) {
			die($this->row->getMessage() . '' . $this->row->getUserInfo());
		}

		return($this->row);
	}

	function affectedRows() {
		// returnere antallet af berørte rækker ved INSERT, UPDATE, DELETE
		// print($db->affected_rows());

		return($this->db->_affectedRows(NULL));
	}

	function f($name) {
		// Returnere værdien fra feltet med navet som er angivet.
		// Print($db->f("felt"));
		return($this->row[$name]);
	}

	function free() {
		// Frigør hukommelse til resultatet
		// $db->free();
		$this->result->free();
	}

	function insertedId() {
		// Returnere det id som lige er blevet indsat
		// $sidste_id = $db->inserted_id();

		return($this->db->lastInsertID());
	}

	function numRows() {
		// Returnere antallet af rækker
		// print($db->num_rows());

		return($this->result->numRows());
	}

	function escape($value) {
		return mysql_escape_string($value);
	}

	function quote($value, $type) {
		return $this->db->quote($value, $type);


	}
}

/*
class DB_Sql {
	var $conn;
	var $rst;
	var $row;

	function DB_Sql($dbhost = DB_HOST, $dbuser = DB_USER, $dbpass = DB_PASS, $dbname = DB_NAME) {

		if(!($this->conn = mysql_pconnect($dbhost, $dbuser, $dbpass))){
			print("Error connection db");
			exit;
		}

  	if(!mysql_select_db($dbname, $this->conn)) {
			print("Error finding db");
			exit;
		}

		if(defined('TIMEZONE')) {

			mysql_query('SET time_zone=\''.TIMEZONE.'\'');
		}

	}

	function query($SQL) {
		// Eksekvere SQL sætning
		// $db->Query("SELECT * FROM tabel");

		//echo $SQL . '<br>';

		if(!($this->rst = mysql_query($SQL, $this->conn)))	{
			print("Kunne ikke eksekvere SQL sætning<br>".$SQL."<br>");
			print(mysql_error($this->conn));
			exit;
		}
  	}

	function nextRecord() {
		// Gennemsøger recordset.
		// Går videre til næste post hver gang den kaldes.
		// Returnere true så længe der er en post
		// while($db->next_record()) {

		return($this->row = mysql_fetch_object($this->rst));
	}

	function f($name) {
		// Returnere værdien fra feltet med navet som er angivet.
		// Print($db->f("felt"));
		return($this->row->$name);
	}

	function p($name) {
		// Printer værdien fra feltet med navnet som er angivet.
		// $db->p("felt");
		print($this->row->$name);
	}

	function goToRow($number) {
		// Flytter til den angivne post
		// $db->goto_row(0);
		mysql_data_seek($this->rst, $number);
	}

	function fieldArray($name) {
		// Returnere et array med alle poster i den angivne kolonne startende ved 0
		// $felt = $db->field_array("felt");
		// print($felt[0]);
		while($this->nextRecord()) {
			$f_array[] = $this->f($name);
		}
		return($f_array);
	}

	function fieldInfo($property, $field=null) {
		// Returnere info om feltet afhængig af $property.
		// Hvis $field ikke er angivet startes ved første kolonne og går videre til næste hver gang funftionen bliver kaldt.
		// Går videre til næste post hver gang den bliver kaldt.
		// if($db->field_info("blob")) {
		// blob: 					true hvis feltet er blob
		// max_length:		Maximum længde
		// multiple_key:	True hvis feltet er nonunigue key
		// name:					navnet på feltet
		// not_null:			true hvis feltet ikke kan være null
		// numeric:				true hvis feltet er et tal
		// primary_key:		true hvis feltet er primær nøgle
		// table:					navnet på tabellen
		// type:					kolonne typen
		// unique_key:		Unik nøgle
		// unsigned:			true hvis feltet er unsigned
		// zerofill:			true hvis feltet er zero-filled
		if(isset($field)) {
			$field = mysql_fetch_field($this->rst, intval($field));
		}
		else {
			$field = mysql_fetch_field($this->rst);
		}
		return($field->$property);
	}

	function listFields($table) {
		// Returnere et array med navnene på kolonnerne i tabel angivet af table
		$this->rst = mysql_list_fields(DB_DB, $table, $this->conn);
		$field_array = array();

		for($i = 0; $i < $this->nf(); $i++) {
			$field_array[] = mysql_field_name($this->rst, $i);
		}
		return($field_array);
	}

	function free() {
		// Frigør hukommelse til resultatet
		// $db->free();
		mysql_free_result($this->rst);
	}

	function insertedId() {
		// Returnere det id som lige er blevet indsat
		// $sidste_id = $db->inserted_id();

		return(mysql_insert_id($this->conn));
	}

	function numRows() {
		// Returnere antallet af rækker
		// print($db->num_rows());

		return(mysql_num_rows($this->rst));
	}

	function numFields() {
		// Returnere antallet af kolonner
		// print($db->nf());

		return(mysql_num_fields($this->rst));
	}

	function affectedRows() {
		// returnere antallet af berørte rækker ved INSERT, UPDATE, DELETE
		// print($db->affected_rows());

		return(mysql_affected_rows($this->conn));
	}

	function escape($value) {

		return safeToDb($value);

	}

	function quote($value, $type) {
		if ($type == 'integer') {
			return mysql_escape_string($value);
		}
		else {
			return "'" . mysql_escape_string($value) . "'";
		}


	}
}
*/
?>
