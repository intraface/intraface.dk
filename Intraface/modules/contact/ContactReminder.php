<?php
/**
 * Create and maintain reminders for contacts.
 *
 * @package ContactReminder
 * @author Sune Jensen <sj@sunet.dk>
 * @since 0.1.0
 * @version @package-version@
 */


/*

Princip:

Det skal bestå af 2 dele:

1) Engangs reminder
2) Tilbagevendende reminder

Engangsreminder sættes med en dato (og klokkesæt) ud i fremtiden. Der kan være et emne og
en beskrivelse af handlingen. Reminderen skal vises på forsiden i en tid (en måned måske)
i forvejen. På dagen kan man bestemme at der bliver sendt en e-mail, og på sigt en
daglig/ugelig/månedlig summary. En reminder kan enten markeres som Set, eller udsættes til
en ny dato (Datoen ændres blot). Samt man kan klikke på Opret ny, på reminderen, når den er
set kan man let oprette en ny, fx et år efter.

På en reminder kan der sættes en faktura-/ordreskabelon (På sigt) (Skal laves som en
del af debtor). Ved et enkelt klik bliver fakturaen oprettet/på sigt automatisk oprettet
og sendt.

Tabelstruktur (udkast):
id
intranet_id
contact_id
[tilbagevendene]_reminder_id
debtor_template_id (kommende)
date
status (created, seen, cancelled)
date_created
date_seen
date_changed
date_cancelled
subject
description
active

Tilbagevendende reminder:
Jeg er lidt i tvivl om hvordan vi lettest laver et tilbagevendende reminder system, som
gør det rimelig let at bestemme en periode, og som ikke kræver alt for meget databasearbejde.
En mulighed er måske at kigge lidt på cron - den er meget fleksibel, men kræver måske lidt
for meget databasearbejde (Det skal gerne kunne lade sig gøre bare med et enkelt databasekald
at hente alle remindere inden for en tidsperiode). Tilbagevende reminder opretter
engangsreminder efterhånden som de bliver efterpurgt (efterhånden som man nærmer sig tiden,
eller man efterspørger remindere ud i fremtiden), både ved natlig kørsel, og ved konkrete
efterspørgsler (fx alle remindere hos en contact det næste år). Engangsreminderne bliver
knyttet til den "tilbagevendende reminder" som har oprettet den. Derved kan engangsreminderne
blive ændret, hvis "Tilbagevende reminder" ændres.

Debtor template kan også tilknyttes tilbagevendende reminder.

Først udkast til hvordan tilbagevende reminder gemmes:
Tabeludkast:
id
intranet_id
contact_id
debtor_template_id
date_created
date_changed
subject
description
active

(Udkast til hvordan gentagende reminder gemmes:)
reminder_day (0: hver dag, >0: dag i måneden hvor reminder aktiveres)
reminder_month (0: hver måned, >0: måned hvor reminder aktiveres)
reminder_week (0: hver uge, >0: uge den skal aktiveres)
date_start
date_end

Denne måde er rimelig let at finde fremtidige poster, men den er ikke særlig fleksibel. Det
kan fx ikke lade sig gøre at lave en reminder det kører både den 1. og 15 i en måned. Det skal
laves som 2 forskellige tilbagevendende remindere. Måske cron metoden er hver at udforske.

Arbejdsgang:
Fordelen ved denne metode er at vi kan starte med at lave en rimelig simpel reminder, blot
med engangsreminder.

1) Engangsreminder med beskrivelse, og mulighed for at markere som set, og udsættelse af reminder.
2) E-mail notification
3) Gengtagende reminder
4) Fakturaskabelon
5) Automatisk udsendelse af faktura.

 */

class ContactReminder extends Standard {

	private $id;
	public $contact;
	private $db;
	private $status_types;
	public $value;

	/**
	 * @param 	object contact: Class contact
	 * @param 	int id: id of reminder.
	 */
	function __construct(&$contact, $id = 0) {
		$this->db = MDB2::singleton(DB_DSN);
		$this->contact = &$contact;
		$this->error = new Error;
		$this->id = intval($id);
		
		if($this->id != 0) {
			$this->load();
		}
	}
	
	public function factory($kernel, $id) {
		if($id == 0) {
			trigger_error("Invalid id in ContactReminder->factory", E_USER_ERROR);
			return false;
		}
		
		if(strtolower(get_class($kernel)) != 'kernel') {
			trigger_error("Kernel is needed in ContactReminder->factory");
		}
		$db = MDB2::singleton(DB_DSN);
		$result = $db->query("SELECT contact_id FROM contact_reminder_single WHERE intranet_id = ".$db->quote($kernel->intranet->get('id'), 'integer')." AND id = ".$db->quote($id, 'integer')."");
		if(PEAR::isError($result)) {
			trigger_error('result is an error in Contact_reminder_single->factory', E_USER_ERROR);
			return false;
		}
		
		$row = $result->fetchRow();
		$contact = new Contact($kernel, $row['contact_id']);
		if($contact->get('id') == 0) {
			trigger_error("Invalid contact id in ContactReminder->factory", E_USER_ERROR);
		}
		
		return new ContactReminder($contact, $id);
		
	}

	/**
	 * @return boolean true or false
	 */
	public function load() {
		
		$result = $this->db->query("SELECT *, DATE_FORMAT(reminder_date, '%d-%m-%Y') AS dk_reminder_date, DATE_FORMAT(date_created, '%d-%m-%Y') AS dk_date_created FROM contact_reminder_single WHERE intranet_id = ".$this->db->quote($this->contact->kernel->intranet->get('id'), 'integer')." AND id = ".$this->db->quote($this->id, 'integer')."");

		if(PEAR::isError($result)) {
			trigger_error('result is an error in Contact_reminder_single->load', E_USER_ERROR);
			return false;
		}
		
		$contact_module = $this->contact->kernel->getModule('contact');
		$this->status_types = $contact_module->getSetting('reminder_status');
		
		$this->value = $result->fetchRow();
		$this->value['status'] = $this->status_types[$this->value['status_key']];
		
		
		return true;
	}
	
	function validate(&$input) {
		$validator = new Validator($this->error);
		
		$validator->isDate($input['reminder_date'], 'Error in date', 'allow_no_year');
		$validator->isString($input['subject'], 'Error in subject', '');
		$validator->isString($input['description'], 'Error in description', '', 'allow_empty');
		
	}

	/**
	 * @param 	array $input: array of data to store/update
	 * @return	boolean true or false
	 */
	public function update($input) {
		
		$this->validate($input);
		
		$date = new Date($input['reminder_date']);
		if(!$date->convert2db()) {
			trigger_error("Was not able to convert date in ContactReminder->update", E_USER_ERROR);
		}
		
		$sql = "reminder_date = ".$this->db->quote($date->get(), 'date')."," .
				"date_changed = NOW()," .
				"subject = ".$this->db->quote($input['subject'], 'text')."," .
				"description = ".$this->db->quote($input['description'], 'text')."";
		
		if($this->error->isError()) {
			return false;
		}
		
		if($this->id != 0) {
			$result = $this->db->exec("UPDATE contact_reminder_single SET ".$sql." WHERE intranet_id = ".$this->db->quote($this->contact->kernel->intranet->get('id'), 'integer')." AND id = ".$this->db->quote($this->id, 'integer'));
			
		}
		else {
			
			$result = $this->db->exec("INSERT INTO contact_reminder_single SET ".$sql.", ".
				"intranet_id = ".$this->db->quote($this->contact->kernel->intranet->get('id'), 'integer')."," .
				"contact_id = ".$this->db->quote($this->contact->get('id'), 'integer')."," .
				"created_by_user_id = ".$this->db->quote($this->contact->kernel->user->get('id'), 'integer')."," .
				"status_key = 1," .
				"date_created = NOW()," .
				"active = 1");
			$this->id = $this->db->lastInsertID();
		}
		
	 	if (PEAR::isError($result)) {
	 		trigger_error('Could not save information in ContactReminder->update' . $result->getUserInfo(), E_USER_ERROR);
	 		return false;
	 	}
	 	return $this->id;
	}
	
	/**
	 * TO BE WRITTEN
	 * postpone the reminder at certain periode. 
	 * 
	 * @param string $periode	periode which to be postponed
	 * @return boolean true or false
	 */
	
	public function postpone($periode) {
		// Please write me.
		// Jeg ved ikke helt hvilket argument den skal tage, men det kunne værer smart at den 
		// kan tage fx '1 day', '2 day', '3 week'. Tror måske der er noget funktionalitet i 
		// php til det, eller er det kun i mysql?
		
	}
	
	/**
	 * TO BE WRITTEN
	 * Return all upcoming reminders on all contacts
	 * 
	 * @return array	with reminders.
	 */
	
	public function upcomingReminders($new_status) {
		// Please write me.
		// Navnet må gerne ændres!
		// Tænker den skal kaldes således
		// $upcomingreminders = ContactReminder::upcomingreminders();
		// da der ikke skal hverken contact eller noget id for at finde dem.
	}
	
	/**
	 * TO BE WRITTEN
	 * Change the status from one to another
	 * 
	 * @param string $new_status	New status as either created, seen, or cancelled
	 * @return boolean true or false
	 */
	
	public function changeStatus($new_status) {
		// Please write me.
	}
	
	public function createDbquery() {
		$this->dbquery = new DBQuery($this->contact->kernel, "contact_reminder_single", "contact_reminder_single.active = 1 AND contact_reminder_single.intranet_id = ".$this->db->quote($this->contact->kernel->intranet->get("id"), 'integer'));
		$this->dbquery->setJoin("INNER", "contact", "contact_reminder_single.contact_id = contact.id", "contact.active = 1 AND contact.intranet_id = ".$this->db->quote($this->contact->kernel->intranet->get("id"), 'integer'));
		$this->dbquery->useErrorObject($this->error);
		
	}
	
	public function getList() {
		
		
		$this->dbquery->setSorting('reminder_date');
		
		$this->dbquery->setCondition('contact_id = '.$this->db->quote($this->contact->get('id'), 'integer'));
		$this->dbquery->setCondition('status_key = '.$this->db->quote(1, 'integer'));
		
		
		$db = $this->dbquery->getRecordset("contact_reminder_single.id, DATE_FORMAT(contact_reminder_single.reminder_date, '%d-%m-%Y') AS dk_reminder_date, contact_reminder_single.reminder_date, contact_reminder_single.subject", "", false);
		$reminders = array();
		$i = 0;
		while ($db->nextRecord()) {
			//
			$reminders[$i]['id'] = $db->f("id");
			$reminders[$i]['reminder_date'] = $db->f("reminder_date");
			$reminders[$i]['dk_reminder_date'] = $db->f("dk_reminder_date");
			$reminders[$i]['subject'] = $db->f("subject");

			$i++;
		}
		return $reminders;
	}
}
?>