<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Shop/Server.php';

$options = array(
    'prefix' => 'shop.',
    'encoding' => 'iso-8859-1');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server(), $options);
$server->handleCall();
?>