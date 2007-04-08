<?php  

class ContactMessage extends Standard {
	
	var $error;
	var $id;
	var $customer;
  var $value;
	
	function ContactMessage(&$contact, $id = 0) {
		if (!is_object($contact) AND strtolower(get_class($contact)) != 'contact') {
			die("CustomerMessage kræver Customer");
		}
		$this->contact = & $contact;
		$this->id = (int)$id;
		$this->error = new Error;
    
    if ($this->id > 0) {
    	$this->load();
    }
	}
	
	function update($var) {
		$var = safeToDb($var);

		$validator = new Validator($this->error);
		$validator->isNumeric($var['important'], "Fejl i important", "allow_empty");
		$validator->isString($var['message'], "Du har brugt ulovlige tegn i beskeden.");
		
		if ($this->error->isError()) {
			return 0;
	  }		
	
		if ($this->id > 0) {
			$sql_type = "UPDATE contact_message";
			$sql_end = " WHERE id = " . $this->id . " AND intranet_id = " . $this->contact->kernel->intranet->get('id');
		}
		else {
			$sql_type = "INSERT INTO contact_message";
			$sql_end = ", date_created=NOW()";
		}
		
		$db = new DB_Sql;
		$db->query($sql_type . " SET message = '".$var['message']."', contact_id = ".$this->contact->get('id').", user_id = ".$this->contact->kernel->user->get('id').", intranet_id = ".$this->contact->kernel->intranet->get('id').", important=".(int)$var['important']."" . $sql_end);
		
		if ($this->id == 0) {
			$this->id = $db->insertedId();
		}
		
		return $this->id;
	}
	
	function getList() {
		$db = new DB_Sql;
		$messages = array();
		$i = 0;
		$db->query("SELECT id, message, important FROM contact_message WHERE contact_id = " . $this->contact->get('id') . " AND intranet_id = " . $this->contact->kernel->intranet->get('id') . " AND active = 1");
		while ($db->nextRecord()) {
			$messages[$i]['id'] = $db->f('id');
			$messages[$i]['message'] = $db->f('message');
			$messages[$i]['important'] = $db->f('important');
     $i++;					
		}
		return $messages;
	}
	
  function load() {
  	$db = new DB_Sql;
    $db->query("SELECT id, message, important FROM contact_message WHERE id = " . $this->id . " AND intranet_id = " . $this->contact->kernel->intranet->get('id'));
    while($db->nextRecord()) {
     $this->value['id'] = $db->f('id');
     $this->value['message'] = $db->f('message');
     $this->value['important'] = $db->f('important');
    }
  }
  
	function anyImportant() {
		$db = new DB_Sql;
		$sql = "SELECT id FROM contact_message WHERE contact_id = " . $this->contact->get('id') . " AND important = 1 AND intranet_id = " . $this->contact->kernel->intranet->get('id') . " AND active = 1";
		$db->query($sql);

		return $db->numRows();
	}
  
  function delete() {
  	$db = new DB_Sql;
		$sql = "UPDATE contact_message SET active = 0 WHERE contact_id = " . $this->contact->get('id') . " AND intranet_id = " . $this->contact->kernel->intranet->get('id') . " AND id = " . $this->id;
		$db->query($sql);
  }
}