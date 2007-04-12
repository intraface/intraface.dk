<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Debtor/Server.php';

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Debtor_Server(), array('prefix' => 'debtor.'));
$server->handleCall();
?>