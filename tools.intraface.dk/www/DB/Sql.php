<?php
class DB_Sql {
    var $conn;
    var $rst;
    var $row;

    function DB_Sql($dbhost = DB_HOST, $dbuser = DB_USER, $dbpass = DB_PASS, $dbname = DB_NAME) {

        if (!($this->conn = mysql_pconnect($dbhost, $dbuser, $dbpass))){
            print("Error connection db");
            exit;
        }

      if (!mysql_select_db($dbname, $this->conn)) {
            print("Error finding db");
            exit;
        }

        if (defined('TIMEZONE')) {

            mysql_query('SET time_zone=\''.TIMEZONE.'\'');
        }

    }

    function query($SQL) {

        if (!($this->rst = mysql_query($SQL, $this->conn)))  {
            print("Kunne ikke eksekvere SQL s�tning<br>".$SQL."<br>");
            print(mysql_error($this->conn));
            exit;
        }
      }

    function nextRecord() {
        return($this->row = mysql_fetch_object($this->rst));
    }

    function f($name) {
        return($this->row->$name);
    }

    function p($name) {
        print($this->row->$name);
    }

    function goToRow($number) {
        mysql_data_seek($this->rst, $number);
    }

    function fieldArray($name) {
        while($this->nextRecord()) {
            $f_array[] = $this->f($name);
        }
        return($f_array);
    }

    function fieldInfo($property, $field=null) {
        if (isset($field)) {
            $field = mysql_fetch_field($this->rst, intval($field));
        }
        else {
            $field = mysql_fetch_field($this->rst);
        }
        return($field->$property);
    }

    function listFields($table) {
        $this->rst = mysql_list_fields(DB_DB, $table, $this->conn);
        $field_array = array();

        for ($i = 0; $i < $this->nf(); $i++) {
            $field_array[] = mysql_field_name($this->rst, $i);
        }
        return($field_array);
    }

    function free() {
        mysql_free_result($this->rst);
    }

    function insertedId() {
        return(mysql_insert_id($this->conn));
    }

    function numRows() {
        return(mysql_num_rows($this->rst));
    }

    function numFields() {
        return(mysql_num_fields($this->rst));
    }

    function affectedRows() {
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

?>