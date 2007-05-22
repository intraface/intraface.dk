<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Contact/Server.php';

$options = array('prefix' => 'contact.', 'encoding' => 'iso-8859-1');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Contact(), $options);
$server->handleCall();
?>