<?php
/**
 * Denne fil bruges til at tilgå filerne.
 *
 * hvordan får vi puttet den her under unittests?
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
ob_start();
require('../../common.php');

// file should stop if no querystring
if (empty($_SERVER["QUERY_STRING"])) {
    trigger_error('no querystring is given!', E_USER_WARNING);
    exit;
}
$query_parts = explode('/', $_SERVER["QUERY_STRING"]);

$auth_adapter = new Intraface_Auth_PublicKeyLogin(MDB2::singleton(DB_DSN), session_id(), $query_parts[1]);
$weblogin = $auth_adapter->auth();

if (!$weblogin) {
    if (isset($query_parts[1])) {
    	$query = $query_parts[1];
    } else {
    	$query = 'query_parts[1] is empty';
    }
    trigger_error('Error logging in to intranet with public key '.$query, E_USER_WARNING);
    exit;
}

$kernel = new Intraface_Kernel;
$kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
$filehandler_shared = $kernel->useShared('filehandler');
$filehandler_shared->includeFile('FileViewer.php');

$filehandler = FileHandler::factory($kernel, $query_parts[2]);
if (!is_object($filehandler) || $filehandler->get('id') == 0) {

    // require_once 'HTTP/Header.php';
    // $h = new HTTP_Header;
    // $h->sendStatusCode(404);

    header('HTTP/1.0 404 Not Found');
    // header('Status: 404 Not Found');
    // trigger_error('Invalid image: '.$_SERVER['QUERY_STRING'], E_USER_WARNING);
    exit;
}

settype($query_parts[3], 'string');
$fileviewer = new FileViewer($filehandler, $query_parts[3]);

if ($fileviewer->needLogin()) {
    session_start();
    $auth = new Intraface_Auth(session_id());
    if (!$auth->hasIdentity()) {
        trigger_error('You need to be logged in to view the file', E_USER_WARNING);
        exit;
    }

    $user = $auth->getIdentity(MDB2::singleton(DB_DSN));
    $intranet = new Intraface_Intranet($user->getActiveIntranetId());
    if ($intranet->getId() != $kernel->intranet->getId()) {
        trigger_error('You where not logged into the correct intranet to view the file', E_USER_WARNING);
        exit;
    }
}

$fileviewer->out();
exit;
?>