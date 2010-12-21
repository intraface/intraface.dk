<?php
/**
 * Observers whether new orders has been placed
 *
 * @author Lars Olesen <lars@legestue.net>
 */

// session_start is only used to create a unique id
session_start();

require_once 'common.php';
require_once 'Intraface/Mail.php';

$db = MDB2::singleton(DB_DSN);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key FROM intranet");

while ($row = $result->fetchRow()) {

	$auth_adapter = new Intraface_Auth_PublicKeyLogin(MDB2::singleton(DB_DSN), md5(session_id()), $row['public_key']);
	$weblogin = $auth_adapter->auth();

	if (!$weblogin) {
	    throw new Exception('Access to the intranet denied. The private key is probably wrong.');
	}

    $kernel = new Intraface_Kernel();
    $kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
    $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));

	if (!$kernel->intranet->hasModuleAccess('order')) {
		continue;
	}

	$gateway = new Intraface_modules_order_OrderGateway($kernel);
	if ($number = $gateway->anyNew()) {
	    //echo $kernel->intranet->get('name') . ' has ' . $number . ' new orders';

	    // @hack
	    if ($kernel->intranet->get('name') == 'Discimport.dk') {
	        mail('mikael@discimport.dk', $number . ' new orders', 'Do not come back complaining I did not warn you about new orders in Intraface.dk.');
	    }
	}

}