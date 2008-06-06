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

// session_start is only used to create a unique id
session_start();

require_once 'common.php';

$db = MDB2::singleton(DB_DSN);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key FROM intranet");

while ($row = $result->fetchRow()) {

	$auth_adapter = new Intraface_Auth_PublicKeyLogin(MDB2::singleton(DB_DSN), md5(session_id()), $row['public_key']);
	$weblogin = $auth_adapter->auth();
		
	if (!$weblogin) {
	    throw new Exception('Access to the intranet denied. The private key is probably wrong.');
	} 

    $this->kernel = new Intraface_Kernel();
    $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
    $this->kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));

	$kernel->useShared('email');

	if (!$kernel->intranet->hasModuleAccess('contact')) {
		continue;
	}

	//echo '<h1>' . $kernel->intranet->get('name') . ' sender e-mails</h1>';

	$email = new Email($kernel);
	$email->sendAll();

	// $email->error->view();

}

$logger = new ErrorHandler_Observer_File(ERROR_LOG);
$logger->update(array(
                'date' => date('r'),
                'type' => 'CronJob',
                'message' => 'Cronjob run successfully!',
                'file' => __FILE__,
                'line' => __LINE__));
exit;
?>