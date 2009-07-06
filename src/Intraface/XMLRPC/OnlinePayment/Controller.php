<?php
class Intraface_XMLRPC_OnlinePayment_Controller extends k_Component
{
    function getServer()
    {
        $options = array(
            'prefix' => 'shop.',
            'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_OnlinePayment_Server0002(), $options);
    }

    function getResponse()
    {
        return $this->getServer()->getResponse();
    }

    function renderHtml()
    {
        return $this->getResponse();
    }

    function POST()
    {
        return $this->getResponse();
    }
}
