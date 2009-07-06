<?php
class Intraface_XMLRPC_CMS_Controller extends k_Component
{
    function getServer()
    {
        $options = array('prefix' => 'cms.',
                 'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_CMS_Server0300(), $options);
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
