<?php
class Intraface_XMLRPC_Debtor_Controller extends k_Component
{
    function getServer()
    {
        $options = array('prefix' => 'debtor.');
        return XML_RPC2_Server::create(new Intraface_XMLRPC_Debtor_Server(), $options);
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
