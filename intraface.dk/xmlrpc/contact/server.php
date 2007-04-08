<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Contact/Server.php';

$server = XML_RPC2_Server::create(new Intraface_Contact(), array('prefix' => 'contact.'));
$server->handleCall();
?>