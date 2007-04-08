<?php

class ReminderItem extends Standard {
	var $reminder;
	var $id;
	var $value;
	var $invoice;
	var $db;
	var $error;
	
	
	function ReminderItem(&$reminder, $id = 0) {
		$this->reminder = &$reminder;
		$this->id  = (int)$id;
		$this->db = new Db_sql;
		
		$this->error = &$this->reminder->error;
		
		if($this->id) {
			$this->load();
		}
	}
	
	function load() {
		die("mangler support for ubetalte reminders");
		if($this->id) {
			$this->db->query("SELECT * FROM invoice_reminder_item WHERE id = ".$this->id." AND invoice_reminder_id = ".$this->reminder->get("id"));
			if($this->db->nextRecord()) {
				$this->value["id"] = $this->db->f("id");
				$this->value["intranet_id"] = $this->db->f("intranet_id");
				$this->value["invoice_reminder_id"] = $this->db->f("invoice_reminder_id");
				$this->value["invoice_id"] = $this->db->f("invoice_id");
				
				$this->invoice = new Invoice($this->kernel, $this->db->f("invoice_id"));
				
				return(1);
			}
		}
		return(0);
	}
	
	function clear() {
		$this->db->query("DELETE FROM invoice_reminder_item WHERE intranet_id = ".$this->reminder->kernel->intranet->get("id")." AND invoice_reminder_id = ".$this->reminder->get("id"));
		$this->db->query("DELETE FROM invoice_reminder_unpaid_reminder WHERE intranet_id = ".$this->reminder->kernel->intranet->get("id")." AND invoice_reminder_id = ".$this->reminder->get("id"));
	}
	
	function save($input) {
		
		$input = safeToDb($input);
		
		if(isset($input["invoice_id"])) {
			
	
			$invoice = new Invoice($this->reminder->kernel, (int)$input["invoice_id"]);
			if($invoice->get("id") == 0) {
				$this->error->set("Ugyldig faktura i ReminderItem->save();");
			}
		
			if($this->error->isError()) {
				return(false);
			}
				
			$sql = "intranet_id = ".$this->reminder->kernel->intranet->get("id").",
				invoice_reminder_id = ".$this->reminder->get("id").",
				invoice_id = ".$invoice->get("id")."";
			
			$this->db->query("INSERT INTO invoice_reminder_item SET ".$sql);
			return(true);
		}
		elseif(isset($input["reminder_id"])) {
			
			$reminder = new Reminder($this->reminder->kernel, (int)$input["reminder_id"]);
			if($reminder->get("id") == 0) {
				$this->error->set("Ugyldig rykker i RemindeItem->save()");
			}
			
			if($this->error->isError()) {
				return(false);
			}
			
			$sql = "intranet_id = ".$this->reminder->kernel->intranet->get("id").",
				invoice_reminder_id = ".$this->reminder->get("id").",
				unpaid_invoice_reminder_id = ".$reminder->get("id")."";
			
			$this->db->query("INSERT INTO invoice_reminder_unpaid_reminder SET ".$sql);
			return(true);
		}
		else {
			$this->error->set("Item er hverken defineret som faktura eller rykker i ReminderItem->save()");
		}
	}
	
	function getList($type) {
		$i = 0;
		$value = array();
		
		
		
		if($type == "invoice") {
			
			$this->db->query("SELECT * FROM invoice_reminder_item WHERE invoice_reminder_id = ".$this->reminder->get("id")." ORDER BY id");
			while($this->db->nextRecord()) {
				$value[$i]["id"] = $this->db->f("id");
				$tmp = new Invoice($this->reminder->kernel, $this->db->f("invoice_id"));
				$value[$i]["invoice_id"] = $tmp->get("id");
				$value[$i]["number"] = $tmp->get("number");
				$value[$i]["description"] = $tmp->get("description");
				$value[$i]["dk_this_date"] = $tmp->get("dk_this_date");
				$value[$i]["dk_due_date"] = $tmp->get("dk_due_date");
				$value[$i]["total"] = $tmp->get("total");
				$value[$i]["arrears"] = $tmp->get("arrears");
				
				$i++;
			}
		}
		elseif($type == "reminder") {
			$this->db->query("SELECT * FROM invoice_reminder_unpaid_reminder WHERE invoice_reminder_id = ".$this->reminder->get("id")." ORDER BY id");
			while($this->db->nextRecord()) {
				$value[$i]["id"] = $this->db->f("id");
				$tmp = new Reminder($this->reminder->kernel, $this->db->f("unpaid_invoice_reminder_id"));
				$value[$i]["reminder_id"] = $tmp->get("id"); 
				$value[$i]["number"] = $tmp->get("number");
				$value[$i]["description"] = $tmp->get("description");
				$value[$i]["dk_this_date"] = $tmp->get("dk_this_date");
				$value[$i]["dk_due_date"] = $tmp->get("dk_due_date");
				$value[$i]["reminder_fee"] = $tmp->get("reminder_fee");
				$i++;
			}
		}
		return $value;
	}
}

?>