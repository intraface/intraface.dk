<?php
class Intraface_XMLRPC_Debtor_Controller extends Intraface_XMLRPC_Controller_Server
{
    function getServer()
    {
        $options = array('prefix' => 'debtor.');
        return XML_RPC2_Server::create(new Intraface_XMLRPC_Debtor_Server(), $options);
    }
}
