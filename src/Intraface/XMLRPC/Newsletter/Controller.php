<?php
class Intraface_XMLRPC_Newsletter_Controller extends Intraface_XMLRPC_Controller_Server
{
    function getServer()
    {
    	$options = array('prefix' => 'newsletter.', 'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_Newsletter_Server0100(), $options);
    }

}
