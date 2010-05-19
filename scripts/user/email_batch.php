<?php
/**
 * cronjob som skal sende e-mails for nyhedsbrevsudsenderen.
 *
 * VIGTIGT:
 * Dreamhost har et maksimalt antal afsendte e-mails p� en time p� 150.
 * Derfor m� der ikke sendes flere end det.
 *
 * Det styres ved at cronjobbet kun s�ttes i gang en gang i timen - og
 * at der kun sendes 125 e-mails ad gangen.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

session_start();

// define(PATH_ROOT, '/home/intraface/');
// This one should be able to be deleted -if you are testing this, please remove it first
// define(PATH_INCLUDE, '/home/intraface/intraface/');

require('/home/intraface/intraface.dk/config.local.php');

set_include_path(
    PATH_ROOT
    . PATH_SEPARATOR . PATH_ROOT.'pear/php/'
    . PATH_SEPARATOR . get_include_path()
);

require_once dirname(__FILE__) . '/../common.php';

$db = MDB2::singleton(DB_DSN);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key FROM intranet");

while ($row = $result->fetchRow()) {

    $kernel = new Intraface_Kernel();
    $kernel->weblogin('public', $row['public_key'], md5(session_id()));
    $kernel->useShared('email');

    if (!$kernel->intranet->hasModuleAccess('contact')) {
        continue;
    }

    //echo '<h1>' . $kernel->intranet->get('name') . ' sender e-mails</h1>';

    $email = new Email($kernel);
    $email->sendAll(Intraface_Mail::factory());

    // $email->error->view();

}

exit;
?>