<?php
/**
 * cronjob som skal sende e-mails for nyhedsbrevsudsenderen.
 *
 * VIGTIGT:
 * Dreamhost har et maksimalt antal afsendte e-mails på en time på 150.
 * Derfor må der ikke sendes flere end det.
 *
 * Det styres ved at cronjobbet kun sættes i gang en gang i timen - og
 * at der kun sendes 125 e-mails ad gangen.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

session_start();

define(PATH_ROOT, '/home/intraface/');
define(PATH_INCLUDE, '/home/intraface/intraface/');

require_once '../common.php';

$db = MDB2::factory(DB_DSN);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key FROM intranet");

while ($row = $result->fetchRow()) {

	$kernel = new Kernel();
	$kernel->weblogin('public', $row['public_key'], md5(session_id()));
	$kernel->useShared('email');

	if (!$kernel->intranet->hasModuleAccess('contact')) {
		continue;
	}

	//echo '<h1>' . $kernel->intranet->get('name') . ' sender e-mails</h1>';

	$email = new Email($kernel);
	$email->sendAll();

	// $email->error->view();

}

exit;
?>