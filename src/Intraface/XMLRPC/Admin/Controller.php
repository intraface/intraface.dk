<?php
class Intraface_XMLRPC_Admin_Controller extends Intraface_XMLRPC_Controller_Server
{
    function getServer()
    {
        $options = array(
            'prefix' => 'intraface.',
            'encoding' => 'utf-8');

    	return $server = XML_RPC2_Server::create(new Intraface_XMLRPC_Admin_Server(), $options);
    }
}
