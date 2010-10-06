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
require_once 'swift_required.php';

$bucket = new bucket_Container(new Intraface_Factory());

$db = $bucket->get('mdb2');
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT DISTINCT(public_key), name FROM intranet INNER JOIN email ON intranet.id = email.intranet_id WHERE email.status = 2");

$mailer = $bucket->get('swift_mailer');

while ($row = $result->fetchRow()) {

    echo "Sending for intranet ".$row['name'].": ";
	
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
    $mails = $gateway->getEmailsToSend();

    echo count($mails);
    
    foreach ($gateway->getEmailsToSend() as $email) {
        $to = $email->getTo();
        if ($to == false) {
            echo "Mail: ".$email->getSubject()." to ".$email->getTo()." has invalid to address\n";
            continue;
        }
        
        $message = $bucket->get('swift_message');
        $message
            ->setSubject($email->getSubject())
            ->setFrom($email->getFrom())
            ->setTo($to)
            ->setBody($email->getBody());
        
        $attachments = $email->getAttachments();
        if (is_array($attachments) AND count($attachments) > 0) {
            $kernel->useModule('filemanager', true);
            foreach ($attachments AS $file) {
                $filehandler = new FileHandler($kernel, $file['id']);
                
                // lille hack med at s�tte uploadpath p�
                $attachment = Swift_Attachment::fromPath($filehandler->getUploadPath() . $filehandler->get('server_file_name'), $filehandler->get('file_type'))->setFilename($file['filename']);
                $message->attach($attachment);
            }
        }    
        
        if ($mailer->send($message)) {
            $email->setIsSent();
        }
    }
    
    echo "\n";
}

$logger = new ErrorHandler_Observer_File(ERROR_LOG);
$logger->update(array(
                'date' => date('r'),
                'type' => 'CronJob',
                'message' => 'Cronjob run successfully!',
                'file' => __FILE__,
                'line' => __LINE__));