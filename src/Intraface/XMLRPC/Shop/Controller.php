<?php
class Intraface_XMLRPC_Shop_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Shop_Server',
        '0.2.0' => 'Intraface_XMLRPC_Shop_Server2',
        '0.4.0' => 'Intraface_XMLRPC_Shop_Server0004'
    );

    function getServer()
    {
        $options = array(
            'prefix' => 'shop.',
            'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server0004(), $options);
    }
}
