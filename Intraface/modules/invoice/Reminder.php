<?php

class Reminder extends Standard {
	var $id;
	var $kernel;
	var $value;
	var $contact;
	var $db;
	var $item;
	var $error;
	var $allowed_status;
	var $payment_methods;
	var $dbquery;


	function Reminder(&$kernel, $id = 0) {

		$this->id = intval($id);
		$this->kernel = &$kernel;
		$this->db = new Db_sql;
		$this->error = new Error;

		$debtorModule = $this->kernel->getModule('debtor');
		$this->allowed_status = $debtorModule->getSetting('status');
		$this->payment_methods = $debtorModule->getSetting('payment_method');

   	$this->dbquery = new DBQuery($this->kernel, "invoice_reminder", "intranet_id = ".$this->kernel->intranet->get("id")." AND active = 1");
		$this->dbquery->useErrorObject($this->error);

		if($this->id) {
			$this->load();
		}
	}

	function load() {
		if($this->id) {
			$this->db->query("SELECT *,
					DATE_FORMAT(this_date, '%d-%m-%Y') AS dk_this_date,
					DATE_FORMAT(due_date, '%d-%m-%Y') AS dk_due_date,
					DATE_FORMAT(date_sent, '%d-%m-%Y') AS dk_date_sent,
					DATE_FORMAT(date_executed, '%d-%m-%Y') AS dk_date_executed,
					DATE_FORMAT(date_cancelled, '%d-%m-%Y') AS dk_date_cancelled
				FROM invoice_reminder WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));
			if($this->db->nextRecord()) {

				$this->value["id"] = $this->db->f("id");
				$this->value["invoice_id"] = $this->db->f("invoice_id");
				$this->value["intranet_id"] = $this->db->f("intranet_id");
				$this->value["intranet_address_id"] = $this->db->f("intranet_address_id");
				$this->value["contact_id"] = $this->db->f("contact_id");
				$this->value["contact_address_id"] = $this->db->f("contact_address_id");
				$this->value["contact_person_id"] = $this->db->f("contact_person_id");
				$this->value["user_id"] = $this->db->f("user_id");
				$this->value["status"] = $this->allowed_status[$this->db->f("status")]; // skal laves om til db->f('status_key')
				$this->value["status_id"] = $this->db->f("status"); // skal slettes i næste version
				$this->value["status_key"] = $this->db->f("status");
				$this->value["this_date"] = $this->db->f("this_date");
				$this->value["dk_this_date"] = $this->db->f("dk_this_date");
				$this->value["due_date"] = $this->db->f("due_date");
				$this->value["dk_due_date"] = $this->db->f("dk_due_date");
				$this->value["dk_date_sent"] = $this->db->f("dk_date_sent");
				$this->value["dk_date_executed"] = $this->db->f("dk_date_executed");
				$this->value["description"] = $this->db->f("description");
				// $this->value["attention_to"] = $this->db->f("attention_to");
				$this->value["number"] = $this->db->f("number");
				$this->value["payment_method_key"] = $this->db->f("payment_method");
				$this->value["payment_method"] = $this->payment_methods[$this->db->f("payment_method")];
				$this->value["reminder_fee"] = $this->db->f("reminder_fee");
				// Denne skal laves, så den udregner hele værdien af hele rykkeren
				$this->value["total"] = $this->db->f("reminder_fee");
				$this->value["text"] = $this->db->f("text");
				$this->value["girocode"] = $this->db->f("girocode");
				$this->value["send_as"] = $this->db->f("send_as");
				// $this->value["is_send"] = $this->db->f("is_send");
				// $this->value["is_send_date"] = $this->db->f("is_send_date");
				// $this->value["dk_is_send_date"] = $this->db->f("dk_is_send_date");
				// $this->value["payed"] = $this->db->f("payed");
				// $this->value["payed_date"] = $this->db->f("payed_date");
				// $this->value["dk_payed_date"] = $this->db->f("dk_payed_date");
				// $this->value["locked"] = $this->db->f("locked");

				$this->contact = new Contact($this->kernel, $this->db->f("contact_id"), $this->db->f("contact_address_id"));
				if($this->contact->get("type") == "corporation" && $this->db->f("contact_person_id") != 0) {
					$this->contact_person = new ContactPerson($this->contact, $this->db->f("contact_person_id"));
				}


				if($this->get("status") == "executed" || $this->get("status") == "cancelled") {
					$this->value["locked"] = true;
				}
				else {
					$this->value["locked"] = false;
				}

				$payment = new Payment($this);
				$payments = $payment->getList();
				$this->value["payment_total"] = 0;
				foreach($payments AS $pay) {
					$this->value["payment_total"] += $pay['amount'];
				}
				$this->value["arrears"] = $this->value['total'] - $this->value['payment_total'];



				return(true);
			}
		}
		$this->id = 0;
		return(false);
	}

	function loadItem($id = 0) {
		$this->item = New ReminderItem($this, (int)$id);
	}

	function getMaxNumber() {
		$this->db->query("SELECT MAX(number) AS max_number FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id"));
		$this->db->nextRecord(); // Hvis der ikke er nogle poster er dette bare den første
		$number = $this->db->f("max_number") + 1;
		return($number);
	}

	function isNumberFree($number) {
		$sql = "SELECT id FROM invoice_reminder WHERE number = ".intval($number)." AND id != ".$this->id . " AND intranet_id = " . $this->kernel->intranet->get('id');
    $this->db->query($sql);
		if($this->db->nextRecord()) {
    	return(FALSE);
		}
		else {
			return(TRUE);
		}
	}

	function save($input) {
		if($this->get("locked") == 1) {
			return(false);
		}

		if (!isset($input['payment_method_key'])) $input['payment_method_key'] = 0;

		if(!is_array($input)) {
  		trigger_error("Input er ikke et array", E_USER_ERROR);
  	}

		$input = safeToDb($input);

		$validator = new Validator($this->error);

		if($validator->isNumeric($input["number"], "Rykkernummer skal være et tal større end nul", "greater_than_zero")) {
			if(!$this->isNumberFree($input["number"])) {
				$this->error->set("Rykkernummer er allerede brugt");
			}
		}

		if($validator->isNumeric($input["contact_id"], "Du skal angive en kunde", "greater_than_zero")) {
			$contact = new Contact($this->kernel, (int)$input["contact_id"]);
			if(is_object($contact->address)) {
				$contact_id = $contact->get("id");
  			$contact_address_id = $contact->address->get("address_id");
  		}
  		else {
  			$this->error->set("Ugyldig kunde");
  		}
		}

		if($contact->get("type") == "corporation") {
			$validator->isNumeric($input["contact_person_id"], "Der er ikke angivet en kontaktperson");
		}
		else {
			$input["contact_person_id"] = 0;
		}

		// $validator->isString($input["attention_to"], "Fejl i att.", "", "allow_empty");
		$validator->isString($input["description"], "Fejl i beskrivelsen", "", "allow_empty");

		if($validator->isDate($input["this_date"], "Ugyldig dato", "allow_no_year")) {
			$this_date = new Date($input["this_date"]);
			$this_date->convert2db();
		}

		if($validator->isDate($input["due_date"], "Ugyldig forfaldsdato", "allow_no_year")) {
			$due_date = new Date($input["due_date"]);
			$due_date->convert2db();
		}

		$validator->isNumeric($input["reminder_fee"], "Rykkerbebyr skal være et tal");
		$validator->isString($input["text"], "Fejl i teksten", "<b><i>", "allow_empty");
		settype($input['send_as'], 'string');
		$validator->isString($input["send_as"], "Ugyldig måde at sende rykkeren på");

		$validator->isNumeric($input["payment_method_key"], "Du skal angive en betalingsmetode");
		settype($input['girocode'], 'string');
		if($input["payment_method_key"] == 3) {
			$validator->isString($input["girocode"], "Du skal udfylde girokode");
		}
		else {
			$validator->isString($input["girocode"], "Ugyldig girokode", "", "allow_empty");
		}

		if(!is_array($input["checked_invoice"]) || count($input["checked_invoice"]) == 0) {
			$this->error->set("Der er ikke valgt nogle fakturaer til rykkeren");
		}

		if($this->error->isError()) {
			return(false);
		}

		$sql = "intranet_address_id = ".$this->kernel->intranet->address->get("address_id").",
			number = ".$input["number"].",
			contact_id = ".$contact_id.",
			contact_address_id = ".$contact_address_id.",
			contact_person_id = ".$input['contact_person_id'].",
			description = \"".$input["description"]."\",
			this_date = \"".$this_date->get()."\",
   		due_date = \"".$due_date->get()."\",
			reminder_fee = ".$input["reminder_fee"].",
			text = \"".$input["text"]."\",
			send_as = \"".$input["send_as"]."\",
			payment_method = ".$input["payment_method_key"].",
			girocode = \"".$input["girocode"]."\",
			date_changed = NOW()";

		// attention_to = \"".$input["attention_to"]."\",
		if($this->id) {
			$this->db->query("UPDATE invoice_reminder SET ".$sql." WHERE id = ".$this->id);
			$this->load();
		}
		else {
			$this->db->query("INSERT INTO invoice_reminder SET ".$sql.", intranet_id = ".$this->kernel->intranet->get("id").", date_created = NOW(), user_id = ".$this->kernel->user->get("id"));
			$this->id = $this->db->insertedId();
			$this->load();
		}

		$this->loadItem();
		$this->item->clear();

		if(isset($input["checked_invoice"]) && is_array($input["checked_invoice"])) {
			foreach($input["checked_invoice"] AS $invoice_id) {
				$this->loadItem();
				$this->item->save(array("invoice_id" => $invoice_id));
			}
		}

		if(isset($input["checked_reminder"]) && is_array($input["checked_reminder"])) {
			foreach($input["checked_reminder"] AS $reminder_id) {
				$this->loadItem();
				$this->item->save(array("reminder_id" => $reminder_id));
			}
		}

		return true;
	}

	function delete() {
		if($this->locked == 0) {
			$this->db->query("UPDATE invoice_reminder SET active = 0 WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));
			$this->id = 0;
			$this->load();
		}
		else {
			trigger_error("Du kan ikke slette en låst rykker", ERROR);
		}
	}


	/*
	function setLocked() {
		if($this->get("locked") == 0) {

			$this->db->query("UPDATE invoice_reminder SET locked = 1 WHERE id = ".$this->id);
			$this->load();
		}
	}
	*/

	/**
   * Sætter status for rykkeren
   *
   * @return true / false
   */
  function setStatus($status) {

		if(is_string($status)) {
			$status_id = array_search($status, $this->allowed_status);
			if($status_id === false) {
				trigger_error("Reminder->setStatus(): Ugyldig status (streng)", FATAL);
			}
		}
		else{
			$status_id = intval($status);
			if(isset($sthis->allowed_status[$status_id])) {
				$status = $this->allowed_status[$status];
			}
			else {
				trigger_error("Reminder->setStatus(): Ugyldig status (integer)", FATAL);
			}
		}

		if($status_id <= $this->get("status_id")) {
			trigger_error("Du kan ikke sætte status til samme som/lavere end den er i forvejen", ERROR);
		}

		switch($status) {
			case "sent":
				$sql = "date_sent = NOW()";
				break;

			case "executed":
				$sql = "date_executed = NOW()";
				break;

			case "cancelled":
				$sql = "date_cancelled = NOW()";
				break;

			default:
				trigger_error("Dette kan ikke lade sig gøre! Reminder->setStatus()", FATAL);
		}

    $db = new Db_Sql;
    $db->query("UPDATE invoice_reminder SET status = ".$status_id.", ".$sql."  WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
		$this->load();
		return true;
	}

	function updateStatus() {

		$payment = $this->getPayments();
		if($payment["total"] == $this->get("total") && $this->get("status") == "sent") {
			$this->setStatus("executed");
		}
		return true;
	}

	function getPayments() {

		$this->payment = new Payment($this);
		$payments = $this->payment->getList();

		$payment["payment"] = 0;
		$payment["deprication"] = 0;

		for($i = 0, $max = count($payments); $i < $max; $i++) {
			$payment[$payments[$i]["type"]] += $payments[$i]["amount"];
		}

		$payment["total"] = $payment["payment"] + $payment["deprication"];
		return $payment;
	}

	/*
	function setPayed($date, $status = 1) {
		if($this->get("locked") == 1) {
			return(false);
		}

		$this->setIsSend($date);
		$this->setLocked();
		$this->db->query("UPDATE invoice_reminder SET payed = ".(int)$status.", payed_date = \"".$date."\" WHERE id = ".$this->id);
		$this->load();

	}

	function setIsSend($date) {
		if($this->get("is_send") == 0) {
			$this->db->query("UPDATE invoice_reminder SET is_send = 1, is_send_date = \"".$date."\" WHERE id = ".$this->id);
			$this->load();
		}
	}
	*/
	/*
	function getList($listfilter) {
		$db = new DB_Sql;


		if(!is_object($listfilter) || get_class($listfilter) != "listfilter") {
			$listfilter = new ListFilter("reminder");
		}

		$listfilter->useErrorObject($this->error);

		$listfilter->defineConditionField("id", "int", 0);
		$listfilter->defineConditionField("description", "string", "");
		$listfilter->defineConditionField("girocode", "string", "");
		$listfilter->defineConditionField("number", "int", 0);
		$listfilter->defineConditionField("status", "int", -1);
		$listfilter->defineConditionField("this_date", "date", "");
		$listfilter->defineConditionField("due_date", "date", "");
		$listfilter->defineConditionField("contact_id", "int", 0);
		$listfilter->defineConditionField("reminder_fee", "int", 0);

		$listfilter->defineSortingField("number");
		$listfilter->defineSortingField("this_date");

		$listfilter->setDefaultCondition("(status = 0 OR status = 1)");
		$listfilter->setDefaultSorting("this_date DESC");


		$sql = "SELECT id FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id")." ".$listfilter->getSqlString();
		$i = 0;

		$db->query($sql);
		while($db->nextRecord()) {

			$reminder = new Reminder($this->kernel, $db->f("id"));
			$list[$i] = $reminder->get();
			if (is_object($reminder->contact->address)) {
				$list[$i]['contact_id'] = $reminder->contact->get('id');
				$list[$i]['name'] = $reminder->contact->address->get('name');
				$list[$i]['address'] = $reminder->contact->address->get('address');
				$list[$i]['postalcode'] = $reminder->contact->address->get('postcode');
				$list[$i]['city'] = $reminder->contact->address->get('city');
			}
			$i++;
		}
		return $list;
	}
	*/

	function getList() {
		$this->dbquery->setSorting("number DESC, this_date DESC");
		$i = 0;

		if($this->dbquery->checkFilter("contact_id")) {
			$this->dbquery->setCondition("contact_id = ".intval($this->dbquery->getFilter("contact_id")));
		}

		if($this->dbquery->checkFilter("text")) {
			$this->dbquery->setCondition("(description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR girocode = \"".$this->dbquery->getFilter("text")."\" OR number = \"".$this->dbquery->getFilter("text")."\")");
		}

		if($this->dbquery->checkFilter("from_date")) {
			$date = new Date($this->dbquery->getFilter("from_date"));
			if($date->convert2db()) {
				$this->dbquery->setCondition("this_date >= \"".$date->get()."\"");
			}
			else {
				$this->error->set("Fra dato er ikke gyldig");
			}
		}

		// Poster med fakturadato før slutdato.
		if($this->dbquery->checkFilter("to_date")) {
			$date = new Date($this->dbquery->getFilter("to_date"));
			if($date->convert2db()) {
				$this->dbquery->setCondition("this_date <= \"".$date->get()."\"");
			}
			else {
				$this->error->set("Til dato er ikke gyldig");
			}
		}

		if($this->dbquery->checkFilter("status")) {
			if($this->dbquery->getFilter("status") == "-1") {
				// Behøves ikke, den tager alle.
				// $this->dbquery->setCondition("status >= 0");
			}
			elseif($this->dbquery->getFilter("status") == "-2") {
				// Not executed = åbne
				if($this->dbquery->checkFilter("to_date")) {
					$date = new Date($this->dbquery->getFilter("to_date"));
					if($date->convert2db()) {
						// Poster der er executed eller cancelled efter dato, og sikring at executed stadig er det, da faktura kan sættes tilbage.
						$this->dbquery->setCondition("(date_executed >= \"".$date->get()."\" AND status = 2) OR (date_cancelled >= \"".$date->get()."\") OR status < 2");
					}
				}
				else {
					// Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
					$this->dbquery->setCondition("status < 2");
				}

			}
			else {
				switch($this->dbquery->getFilter("status")) {
					case "0":
						$to_date_field = "date_created";
						break;

					case "1":
						$to_date_field = "date_sent";
						break;

					case "2":
						$to_date_field = "date_executed";
						break;

					case "3":
						$to_date_field = "data_caneled";
						break;
				}

				if($this->dbquery->checkFilter("to_date")) {
					$date = new Date($this->dbquery->getFilter("to_date"));
					if($date->convert2db()) {
						$this->dbquery->setCondition($to_date_field." <= \"".$date->get()."\"");
					}
				}
				else {
					// tager dem som på nuværende tidspunkt har den angivet status
					$this->dbquery->setCondition("status = ".intval($this->dbquery->getFilter("status")));
				}
			}
		}

		$this->dbquery->setSorting("number DESC");
		$db = $this->dbquery->getRecordset("id", "", false);

		$list = array();
		while($db->nextRecord()) {
			$reminder = new Reminder($this->kernel, $db->f("id"));
			$list[$i] = $reminder->get();
			if (is_object($reminder->contact->address)) {
				$list[$i]['contact_id'] = $reminder->contact->get('id');
				$list[$i]['name'] = $reminder->contact->address->get('name');
				$list[$i]['address'] = $reminder->contact->address->get('address');
				$list[$i]['postalcode'] = $reminder->contact->address->get('postcode');
				$list[$i]['city'] = $reminder->contact->address->get('city');
			}
			$i++;
		}
		return $list;
	}

	/**
	 * Bruges ift. kontakter
	 */
	function any($contact_id) {
		$contact_id = (int)$contact_id;
		if ($contact_id == 0) {
			return 0;
		}
		$db = new DB_Sql;
		$db->query("SELECT id
			FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND contact_id=" . $contact_id);
		return $db->numRows();
	}

	function isFilledIn() {
		$db = new DB_Sql;
		$db->query("SELECT id FROM invoice_reminder WHERE intranet_id = " . $this->kernel->intranet->get('id'));
		return $db->numRows();
	}

	/**
	 * Til at oprette pdf.
	 */
/*
	function _pdf($type = 'stream', $filename='') {

		if($this->get('id') == 0) {
			trigger_error("Reminder->pdf skal være loaded for at lave pdf", E_USER_ERROR);
		}

		$this->kernel->useShared('filehandler');
		$shared_pdf = $this->kernel->useShared('pdf');
		$shared_pdf->includeFile('PdfMakerDebtor.php');

		$doc = new PdfMakerDebtor($this->kernel);
		$doc->start();


		if($this->kernel->intranet->get("pdf_header_file_id") != 0) {
			$filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
			$doc->addHeader($filehandler->get('file_uri_pdf'));
		}

		$contact["object"] = $this->contact;
		if(isset($this->contact_person) AND get_class($this->contact_person) == "contactperson") {
			$contact["attention_to"] = $reminder->contact_person->get("name");
		}

		$intranet["address_id"] = $this->get("intranet_address_id");
		$intranet["user_id"] = $this->get("user_id");

		$docinfo[0]["label"] = "Dato:";
		$docinfo[0]["value"] = $this->get("dk_this_date");

		$doc->addRecieverAndSender($contact , $intranet, "Påmindelse om betaling", $docinfo);

		$doc->setY('-20'); // mellemrum til vareoversigt

		$text = explode("\r\n", $this->get("text"));
		foreach($text AS $line) {
			if($line == "") {
				$doc->setY('-'.$doc->get('font_spacing'));

				if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
					$doc->nextPage(true);
				}
			}
			else {
				while($line != "") {

					$doc->setY('-'.($doc->get("font_padding_top") + $doc->get("font_size")));
					$line = $doc->addTextWrap($doc->get('x'), $doc->get('y'), $doc->get("right_margin_position") - $doc->get('x'), $doc->get("font_size"), $line); // $doc->get("right_margin_position") - $doc->get('x')
					// $doc->line($doc->get('x'), $doc->get('y'), $doc->get('x') + $doc->get("right_margin_position") - $doc->get('x'), $doc->get('y'));

					$doc->setY('-'.$doc->get("font_padding_bottom"));

					if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
						$doc->nextPage(true);
					}
				}
			}
		}

		// Overskrifter - Vareudskrivning

		$doc->setY('-20'); // mellemrum til vareoversigt

		if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 3) {
			$doc->nextPage(true);
		}

		$apointX["text"] = $doc->get("margin_left");
		$apointX["invoice_date"] = $doc->get("right_margin_position") - 225;
		$apointX["due_date"] = $doc->get("right_margin_position") - 150;
		$apointX["amount"] = $doc->get("right_margin_position");
		$apointX["text_width"] = $doc->get("right_margin_position") - $doc->get("margin_left") - $apointX["text"] - 60;


		$doc->addText($apointX["text"], $doc->get('y'), $doc->get("font_size"), "Beskrivelse");
		//$doc->addText($apointX["tekst"], $doc->get('y'), $doc->get("font_size"), "Tekst");
		$doc->addText($apointX["invoice_date"], $doc->get('y'), $doc->get("font_size"), "Dato");
		$doc->addText($apointX["due_date"], $doc->get('y'), $doc->get("font_size"), "Forfaldsdato");
		$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), "Beløb") -3, $doc->get('y'), $doc->get("font_size"), "Beløb");

		$doc->setY('-'.($doc->get("font_spacing") - $doc->get("font_size")));

		$doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get("right_margin_position"), $doc->get('y'));

		// vareoversigt

		$this->loadItem();
		$items = $this->item->getList("invoice");

		$total = 0;
		$color = 0;

		for($i = 0, $max = count($items); $i < $max; $i++) {

			if($color == 1) {
				$doc->setColor(0.8, 0.8, 0.8);
				$doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get("right_margin_position") - $doc->get("margin_left"), $doc->get("font_spacing"));
				$doc->setColor(0, 0, 0);
				$color = 0;
			}
			else {
				$color = 1;
			}

			$doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));

			$doc->addText($apointX["text"], $doc->get('y'), $doc->get("font_size"), "Faktura nr. ".$items[$i]["number"]);
			$doc->addText($apointX["invoice_date"], $doc->get('y'), $doc->get("font_size"), $items[$i]["dk_this_date"]);
			$doc->addText($apointX["due_date"], $doc->get('y'), $doc->get("font_size"), $items[$i]["dk_due_date"]);
			$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["arrears"], 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($items[$i]["arrears"], 2, ",", "."));
			$doc->setY('-'.$doc->get("font_padding_bottom"));
			$total += $items[$i]["arrears"];

			if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
				$doc->nextPage(true);
			}
		}

		$items = $this->item->getList("reminder");

		for($i = 0, $max = count($items); $i < $max; $i++) {

			if($color == 1) {
				$doc->setColor(0.8, 0.8, 0.8);
				$doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get("right_margin_position") - $doc->get("margin_left"), $doc->get("font_spacing"));
				$doc->setColor(0, 0, 0);
				$color = 0;
			}
			else {
				$color = 1;
			}

			$doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
			$doc->addText($apointX["text"], $doc->get('y'), $doc->get("font_size"), "Rykkkergebyr fra tidligere rykker");
			$doc->addText($apointX["invoice_date"], $doc->get('y'), $doc->get("font_size"), $items[$i]["dk_this_date"]);
			$doc->addText($apointX["due_date"], $doc->get('y'), $doc->get("font_size"), $items[$i]["dk_due_date"]);
			$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", "."));
			$doc->setY('-'.$doc->get("font_padding_bottom"));
			$total += $items[$i]["reminder_fee"];

			if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
				$doc->nextPage(true);
			}
		}


		if($this->get("reminder_fee") > 0) {

			if($color == 1) {
				$doc->setColor(0.8, 0.8, 0.8);
				$doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get("right_margin_position") - $doc->get("margin_left"), $doc->get("font_spacing"));
				$doc->setColor(0, 0, 0);
				$color = 0;
			}
			else {
				$color = 1;
			}


			$doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
			$doc->addText($apointX["text"], $doc->get('y'), $doc->get("font_size"), "Rykkergebyr pålagt denne rykker");
			$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($this->get("reminder_fee"), 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($this->get("reminder_fee"), 2, ",", "."));
			$doc->setY('-'.$doc->get("font_padding_bottom"));
			$total += $this->get("reminder_fee");

			if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
				$doc->nextPage(true);
			}
		}

		$doc->setLineStyle(1);
		$doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get("right_margin_position"), $doc->get('y'));
		$doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
		$doc->addText($apointX["due_date"], $doc->get('y'), $doc->get("font_size"), "<b>Total:</b>");
		$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>"), $doc->get('y'), $doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>");
		$doc->setY('-'.$doc->get("font_padding_bottom"));
		$doc->line($apointX["due_date"], $doc->get('y'), $doc->get("right_margin_position"), $doc->get('y'));


		$parameter = array(
			"contact" => $this->contact,
			"payment_text" => "Kontakt ".$this->contact->get("number"),
			"amount" => $total,
			"payment" => $this->get('payment_total'),
			"due_date" => $this->get("dk_due_date"),
			"girocode" => $this->get("girocode"));


		$doc->addPaymentCondition($this->get("payment_method_key"), $parameter);


		switch ($type) {
			case 'string':
					return $doc->output();
				break;
			case 'file':
					if (empty($filename)) {
						return 0;
					}
					$data = $doc->output();
					return $doc->writeDocument($data, $filename);
				break;
			default:
					return $doc->stream();
				break;
		}

		// $doc->stream();

	}
	*/
	function pdf($type = 'stream', $filename='') {
		if($this->get('id') == 0) {
			trigger_error('Cannot create pdf from debtor without valid id', E_USER_ERROR);
		}

		$shared_pdf = $this->kernel->useShared('pdf');
		$shared_pdf->includeFile('PdfMakerDebtor.php');

		$translation = $this->kernel->getTranslation('debtor');

		require_once PATH_INCLUDE_MODULE . 'invoice/Visitor/Pdf.php';

		$filehandler = '';

		if($this->kernel->intranet->get("pdf_header_file_id") != 0) {
			$filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
		}

		$report = new Reminder_Report_Pdf($translation, $filehandler);
		$report->visit($this);
		return $report->output($type, $filename);
	}
}
?>