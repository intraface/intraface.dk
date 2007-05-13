<?php
/**
 * Denne fil bruges til at tilgå filerne.
 *
 * hvordan får vi puttet den her under unittests?
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../common.php');

// den her er klodset - ved ikke om den bruges
require(PATH_INCLUDE_CONFIG.'setting_file_type.php');

// file should stop if no querystring
if (empty($_SERVER["QUERY_STRING"])) {
	exit;
}

$query_parts = explode('/', $_SERVER["QUERY_STRING"]);

// safeToDb er forkert at bruge her
$query_parts = safeToDb($query_parts);

$weblogin = new Weblogin();
if (!$intranet_id = $weblogin->auth('public', $query_parts[1])) {
	die('FEJL I LÆSNING AF BILLEDE (0)');
}
if($intranet_id == false) {
	trigger_error("FEJL I LÆSNING AF BILLEDE (1)", E_USER_ERROR);
}

$kernel = new Kernel;
$kernel->intranet = new Intranet($intranet_id);
$filehandler_shared = $kernel->useShared('filehandler');

$filehandler = FileHandler::factory($kernel, $query_parts[2]);
if(!is_object($filehandler)) {
	trigger_error("FEJL I LÆSNING AF BILLEDE (2)", E_USER_ERROR);
}
switch($filehandler->get('accessibility')) {
	case 'personal':
		// Not implemented - continue to next
	case 'intranet':
		// You have to be logged in to access this file
		session_start();
		$auth = new Auth(session_id());

		if (!$user_id = $auth->isLoggedIn()) {
			die("FEJL I LÆSNING AF BILLEDE (4)");
		}

		$user = new User($user_id);
		$intranet = new Intranet($user->getActiveIntranetId());

		if($intranet->get('id') != $intranet_id) {
			die("FEJL I LÆSNING AF BILLEDE (4)");
		}

		break;
	case 'public':
		// public alle må se den
		// vi gør intet
		break;
	default:
		// Dette er en ugyldig type
		trigger_error("FEJL I LÆSNING AF BILLEDE (5)", E_USER_ERROR);
		break;
}



$file_id = $filehandler->get('id');
$file_name = $filehandler->get('file_name');
$mime_type = $_file_type[$filehandler->get('file_type_key')]['mime_type'];
$file_path = $filehandler->get('file_path');

$filehandler_shared->includeFile('InstanceHandler.php');
$instancehandler = new InstanceHandler($filehandler);

if($instancehandler->_checkType($query_parts[3]) !== false) {
	$filehandler->createInstance($query_parts[3]);

	$file_path = $filehandler->instance->get('file_path');
}

$last_modified = filemtime($file_path);

header('Content-Type: '.$mime_type);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified).' GMT');
header('Cache-Control:');
header('Content-Disposition: inline; filename='.$file_name);
header('Pragma:');

readfile($file_path);
exit;
?>