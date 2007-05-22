<?php
/**
 * CMS-Server
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once '../../common.php';
require_once 'Intraface/XMLRPC/CMS/Server.php';

$options = array(
    'prefix' => 'cms.',
    'encoding' => 'iso-8859-1');


$server = XML_RPC2_Server::create(new Intraface_XMLRPC_CMS_Server(), $options);
$server->handleCall();
?>