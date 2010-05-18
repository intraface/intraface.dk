<?php
/**
 * Sends e-mails from queue
 *
 * Important:
 * Dreamhost only allows 150 mails pr. hour to be sent
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require_once 'common.php';
require_once 'Intraface/Mail.php';

$bucket = new bucket_Container(new Intraface_Factory());

$db = $bucket->get('mdb2');

$mailer = $bucket->get('swift_mailer');

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key FROM intranet");

while ($row = $result->fetchRow()) {

	$auth_adapter = new Intraface_Auth_PublicKeyLogin($db, md5(uniqid()), $row['public_key']);
	$weblogin = $auth_adapter->auth();

	if (!$weblogin) {
	    throw new Exception('Access to the intranet denied. The private key is probably wrong.');
	}

    $kernel = new Intraface_Kernel();
    $kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
    $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));

	$kernel->useShared('email');

	if (!$kernel->intranet->hasModuleAccess('contact')) {
		continue;
	}

    $gateway = new Intraface_shared_email_EmailGateway($kernel);
    foreach ($gateway->getEmailsToSend() as $email) {
        $message = $bucket->get('swift_message');
        $message
            ->setSubject($email->getSubject())
            ->setFrom($email->getFrom())
            ->setTo($email->getTo())
            ->setBody($email->getBody());
        $mailer->send($message);
    }
}

$logger = new ErrorHandler_Observer_File(ERROR_LOG);
$logger->update(array(
                'date' => date('r'),
                'type' => 'CronJob',
                'message' => 'Cronjob run successfully!',
                'file' => __FILE__,
                'line' => __LINE__));