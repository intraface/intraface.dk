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

$weblogin = new Weblogin();
if (!$intranet_id = $weblogin->auth('public', $query_parts[1])) {
    trigger_error('Error logging in to intranet with public key '.$query_parts[1], E_USER_WARNING);
    exit;
}
$kernel = new Kernel;
$kernel->intranet = new Intranet($intranet_id);
$filehandler_shared = $kernel->useShared('filehandler');
$filehandler_shared->includeFile('FileViewer.php');

$filehandler = FileHandler::factory($kernel, $query_parts[2]);
if(!is_object($filehandler) || $filehandler->get('id') == 0) {
    
    // require_once 'HTTP/Header.php';
    // $h = new HTTP_Header;
    // $h->sendStatusCode(404);
    
    header('HTTP/1.0 404 Not Found');
    // header('Status: 404 Not Found');
    // print_r(headers_list());
    // trigger_error('Invalid image: '.$_SERVER['QUERY_STRING'], E_USER_WARNING);
    exit;
}

settype($query_parts[3], 'string');
$fileviewer = new FileViewer($filehandler, $query_parts[3]);

if($fileviewer->needLogin()) {
    session_start();
    require('Intraface/Auth.php');
    $auth = new Auth(session_id());
    // the user is logged in but...
    if (!$user_id = $auth->isLoggedIn()) {
        trigger_error('You need to be logged in to view the file', E_USER_WARNING);
        exit;
    }
     
    // ...we need to check that it is the right intranet
    $user = new User($user_id);
    $intranet = new Intranet($user->getActiveIntranetId());
    if($intranet->get('id') != $intranet_id) {
        trigger_error('You where not logged into the correct intranet to view the file', E_USER_WARNING);
        exit;
    }
}

$fileviewer->out();
exit;
?>