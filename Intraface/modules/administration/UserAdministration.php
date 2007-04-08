<?php

/**
 *
 * Administration of user data and rights
 * Please read in User.php for description of relations
 *
 * @package UserAdministration
 * @author	Sune Jensen <sj@sunet.dk>
 * @author	Lars Olesen <lars@legestue.net>
 * @since	0.1.0
 * @version	@package-version@
 * 
 *
 */

class UserAdministration extends User {


	function UserAdministration(&$kernel,$id) {
		if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
			trigger_error('UserAdministration kræver Kernel', E_USER_ERROR);
		}

		User::User($id);
	}
	
	function update($input) {

		$this->validate($input);
		$validator = new Validator($this->error);
		
		if($this->id == 0) {
			$validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal");
		}
		else {
			$validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal", "allow_empty");
		}
		
		
		$sql = "email = \"".$input["email"]."\"";
		
		if(!empty($input["password"])) {
			if($input["password"] === $input["confirm_password"]) {
				$sql .= ", password = \"".md5($input["password"])."\"";
			}
			else {
				$this->error->set("De to adgangskoder er ikke ens!");
			}
		}

		if($this->error->isError()) {
			return(false);
		}

		if($this->id) {
			$this->db->exec("UPDATE user SET ".$sql." WHERE id = ".$this->id);
			$this->load();
			return($this->id);
		}
		else {
			$this->db->exec("INSERT INTO user SET ".$sql);
			$this->id = $this->db->lastInsertId();
			$this->load();
			return($this->id);
		}
	}
	
	/*
	Moved to user.
	
	function update($input) {

		$this->db = new DB_Sql;

		$input = safeToDb($input);
		$validator = new Validator($this->error);

		$validator->isEmail($input["email"], "Ugyldig E-mail");
		$this->db->query("SELECT id FROM user WHERE email = \"".$input["email"]."\" AND id != ".$this->id);
		if($this->db->nextRecord()) {
			$this->error->set("E-mail-adressen er allerede benyttet");
		}

		$sql = "email = '".$input["email"]."'";

		if($this->error->isError()) {
			return(false);
		}

		if($this->id) {
			$this->db->query("UPDATE user SET ".$sql." WHERE id = ".$this->id);
			$this->load();
			return $this->id;
		}
		else {
			$this->db->query("INSERT INTO user SET ".$sql);
			$this->id = $this->db->insertedId();
			$this->load();
			return($this->id);
		}
	}

	// hvis man ændrer password, skal man have en e-mail, som en sikkerheds foranstaltning.
	/*
	 * This function is moved to User.php
	
	function updatePassword($old_password, $new_password, $repeat_password) {
		if($this->id == 0) {
			return 0;
		}

		$db = new DB_Sql;
		$db->query("SELECT * FROM user WHERE password = '".safeToDb(md5($old_password))."' AND id = " . $this->get('id'));
		if ($db->numRows() < 1) {
			$this->error->set('error in old password');
		}

		$validator = new Validator($this->error);
		$validator->isPassword($new_password, 6, 16, "error in new password");

		if ($new_password != $repeat_password) {
			$this->error->set('error in password');
		}

		if ($this->error->isError()) {
			return false;
		}

		$db->query("UPDATE user SET password = '".safeToDb(md5($new_password))."' WHERE id = " . $this->get('id'));

		return 1;

	}
	*/


}

?>