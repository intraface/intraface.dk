<?php
class Intraface_XMLRPC_OnlinePayment_Controller extends Intraface_XMLRPC_Controller_Server
{
    function getServer()
    {
        $options = array(
            'prefix' => 'onlinepayment.',
            'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_OnlinePayment_Server0002(), $options);
    }
}
