<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Shop/Server.php';

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server(), array('prefix' => 'shop.'));
$server->handleCall();
?>