<?php
class Intraface_XMLRPC_Shop_Controller extends k_Component
{
    private $available_servers = array(
        '0004' => 'Intraface_XMLRPC_Shop_Server0004'
    );

    function getServer()
    {
        $options = array(
            'prefix' => 'shop.',
            'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server0004(), $options);
    }

    function getResponse()
    {
        return $this->getServer()->getResponse();
    }

    function renderHtml()
    {
        ob_start();
        $this->getServer()->autoDocument();
        $result = ob_get_clean();
        return $result;
    }

    function POST()
    {
        return $this->getResponse();
    }
}
