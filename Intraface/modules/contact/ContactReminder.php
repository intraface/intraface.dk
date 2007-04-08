<?php
/**
 * Create and maintain reminders for contacts.
 *
 * @package Contact
 * @author Lars Olesen <lars@legestue.net>
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
	private $contact;
	public $value;

	/**
	 * @param 	object contact: Class contact
	 * @param 	int id: id of reminder.
	 */
	function __construct($contact, $id = 0) {
		$this->contact = $contact;
		$this->id = intval($id);
		$this->value['id'] = $this->id;
	}

	/**
	 * @return boolean true or false
	 */
	public function load() {

	}

	/**
	 * @param 	array $input: array of data to store/update
	 * @return	boolean true or false
	 */
	public function save($input) {

	}

}
?>