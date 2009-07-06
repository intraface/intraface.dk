<?php
class Intraface_XMLRPC_Admin_Controller extends k_Component
{
    function getServer()
    {
        $options = array(
            'prefix' => 'intraface.',
            'encoding' => 'utf-8');

    	return $server = XML_RPC2_Server::create(new Intraface_XMLRPC_Admin_Server(), $options);
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
