<?php
require_once '../../common.php';
require_once 'Intraface/XMLRPC/Contact/Server.php';

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

$options = array('prefix' => 'contact.', 'encoding' => 'iso-8859-1');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Contact_Server(), $options);
$server->handleCall();