<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Newsletter/Server.php';

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Newsletter_Server(), array('prefix' => 'newsletter.'));
$server->handleCall();
?>