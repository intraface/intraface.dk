<?php
require_once '../../common.php';

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');
$options = array('prefix' => 'newsletter.', 'encoding' => 'utf-8');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Newsletter_Server0100($bucket->get('Doctrine_Connection_Common')), $options);
$server->handleCall();
