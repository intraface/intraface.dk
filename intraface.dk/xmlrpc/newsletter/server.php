<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Newsletter/Server.php';

$options = array('prefix' => 'newsletter.', 'encoding' => 'iso-8859-1');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Newsletter_Server(), $options);
$server->handleCall();
?>